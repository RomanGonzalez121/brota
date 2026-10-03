<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Auth\LoginController;
use App\Models\Order;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * The notifications each role finds in its bell: order updates for the demo
 * customer, new orders for every restaurant, deliveries for the demo courier.
 */
class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::query()->where('email', LoginController::demoEmail(UserRole::Customer))->first();
        $courier = User::query()->where('email', LoginController::demoEmail(UserRole::Courier))->first();

        $delivered = $customer->orders()->where('status', OrderStatus::Delivered)->latest()->first();
        $this->notify($customer, $delivered->created_at->copy()->addHour(), read: true,
            title: 'Pedido entregado',
            body: "Sumaste {$delivered->leaves_earned} Hojitas Verdes con el pedido {$delivered->code}.",
            url: route('orders.show', $delivered, absolute: false), icon: 'check');

        $preparing = $customer->orders()->where('status', OrderStatus::Preparing)->with('restaurant')->first();
        $this->notify($customer, $preparing->created_at->copy()->addMinutes(3),
            title: 'Tu pedido está en preparación',
            body: "{$preparing->restaurant->name} aceptó el pedido {$preparing->code}.",
            url: route('orders.show', $preparing, absolute: false), icon: 'menu');

        $onTheWay = $customer->orders()->where('status', OrderStatus::OnTheWay)->first();
        $this->notify($customer, $onTheWay->created_at->copy()->addMinutes(19),
            title: 'Tu pedido está en camino',
            body: "{$courier->firstName()} ya salió con el pedido {$onTheWay->code}.",
            url: route('orders.show', $onTheWay, absolute: false), icon: 'bike');

        // Every order still waiting for an answer is a notification for its restaurant.
        Order::query()
            ->where('status', OrderStatus::Received)
            ->with(['restaurant.owner', 'customer', 'items'])
            ->oldest()
            ->get()
            ->each(fn (Order $order) => $this->notify($order->restaurant->owner, $order->created_at,
                title: 'Tenés un pedido nuevo',
                body: "{$order->customer->firstName()} pidió {$order->itemsSummary()}.",
                url: route('restaurant.orders.show', $order, absolute: false), icon: 'receipt'));

        $courier->deliveries()
            ->whereIn('status', [OrderStatus::Preparing, OrderStatus::OnTheWay])
            ->with('restaurant')
            ->oldest()
            ->get()
            ->each(fn (Order $order) => $this->notify($courier, $order->created_at->copy()->addMinutes(3),
                title: 'Tenés una entrega nueva',
                body: "Retirá el pedido {$order->code} en {$order->restaurant->name}.",
                url: route('courier.deliveries.show', $order, absolute: false), icon: 'bike'));
    }

    private function notify(User $user, CarbonInterface $at, string $title, string $body, string $url, string $icon, bool $read = false): void
    {
        // Written straight to the table (not through notify()) so it can be dated in the past.
        $user->notifications()->create([
            'id' => Str::uuid()->toString(),
            'type' => ActivityNotification::class,
            'data' => (new ActivityNotification($title, $body, $url, $icon))->toArray($user),
            'read_at' => $read ? $at : null,
            'created_at' => $at,
            'updated_at' => $at,
        ]);
    }
}
