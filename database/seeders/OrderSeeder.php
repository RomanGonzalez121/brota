<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Auth\LoginController;
use App\Models\LeafTransaction;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    private User $customer;

    private User $courier;

    private Collection $otherCustomers;

    private Collection $otherCouriers;

    public function run(): void
    {
        $this->customer = User::query()->where('email', LoginController::demoEmail(UserRole::Customer))->first();
        $this->courier = User::query()->where('email', LoginController::demoEmail(UserRole::Courier))->first();
        $this->otherCustomers = User::query()->where('role', UserRole::Customer)->whereKeyNot($this->customer->id)->get();
        $this->otherCouriers = User::query()->where('role', UserRole::Courier)->where('is_active', true)->whereKeyNot($this->courier->id)->get();

        $restaurants = Restaurant::query()->with('dishes')->get()->keyBy('slug');
        $huerta = $restaurants['la-huerta-de-palermo'];

        // Two months of delivered orders, so the restaurant reports have something to chart.
        for ($daysAgo = 58; $daysAgo >= 1; $daysAgo--) {
            for ($count = fake()->numberBetween(0, 3); $count > 0; $count--) {
                $this->order($huerta, $this->otherCustomers->random(), OrderStatus::Delivered,
                    now()->subDays($daysAgo)->setTime(fake()->numberBetween(12, 22), fake()->numberBetween(0, 59)));
            }
        }

        foreach ($restaurants->except('la-huerta-de-palermo') as $restaurant) {
            for ($count = 4; $count > 0; $count--) {
                $this->order($restaurant, $this->otherCustomers->random(), OrderStatus::Delivered,
                    now()->subDays(fake()->numberBetween(2, 40))->setTime(fake()->numberBetween(12, 22), fake()->numberBetween(0, 59)));
            }
        }

        // The demo customer's history: one order per state, plus past deliveries.
        $this->order($restaurants['verde-que-te-quiero'], $this->customer, OrderStatus::Delivered, now()->subDays(3)->setTime(13, 10), containers: true);
        $this->order($huerta, $this->customer, OrderStatus::Delivered, now()->subDays(9)->setTime(21, 5), containers: true);
        $this->order($restaurants['masa-madre-chacarita'], $this->customer, OrderStatus::Delivered, now()->subDays(16)->setTime(9, 40), containers: false);
        $this->order($restaurants['raiz-cocina-viva'], $this->customer, OrderStatus::Delivered, now()->subDays(24)->setTime(20, 30), containers: true);
        $this->order($restaurants['tierra-adentro'], $this->customer, OrderStatus::Rejected, now()->subDays(12)->setTime(22, 50));
        $preparing = $this->order($restaurants['verde-que-te-quiero'], $this->customer, OrderStatus::Preparing, now()->subMinutes(14), containers: true);
        $onTheWay = $this->order($huerta, $this->customer, OrderStatus::OnTheWay, now()->subMinutes(31), containers: true);

        // What the demo restaurant finds waiting when it opens the panel.
        $incoming = [
            $this->order($huerta, $this->otherCustomers[0], OrderStatus::Received, now()->subMinutes(2)),
            $this->order($huerta, $this->otherCustomers[1], OrderStatus::Received, now()->subMinutes(6)),
            $this->order($huerta, $this->otherCustomers[2], OrderStatus::Received, now()->subMinutes(11)),
        ];
        $kitchen = $this->order($huerta, $this->otherCustomers[3], OrderStatus::Preparing, now()->subMinutes(18));
        $this->order($huerta, $this->otherCustomers[4], OrderStatus::Rejected, now()->subDays(1)->setTime(23, 20));

        $this->seedNotifications($preparing, $onTheWay, $incoming, $kitchen);
    }

    private function order(Restaurant $restaurant, User $customer, OrderStatus $status, CarbonInterface $placedAt, ?bool $containers = null): Order
    {
        $dishes = $restaurant->dishes->where('is_available', true)->random(fake()->numberBetween(1, 3));
        $lines = $dishes->map(fn ($dish) => ['dish' => $dish, 'quantity' => fake()->randomElement([1, 1, 1, 2])]);
        $subtotal = $lines->sum(fn ($line) => $line['dish']->price * $line['quantity']);

        $hasCourier = in_array($status, [OrderStatus::Preparing, OrderStatus::OnTheWay, OrderStatus::Delivered], true);
        $isDemoCustomer = $customer->is($this->customer);
        $courier = match (true) {
            ! $hasCourier => null,
            $status !== OrderStatus::Delivered, $isDemoCustomer => $this->courier,
            default => fake()->boolean(45) ? $this->courier : $this->otherCouriers->random(),
        };

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'courier_id' => $courier?->id,
            'status' => $status,
            'subtotal' => $subtotal,
            'delivery_fee' => $restaurant->delivery_fee,
            'total' => $subtotal + $restaurant->delivery_fee,
            'rejection_reason' => $status === OrderStatus::Rejected ? 'Nos quedamos sin stock de un plato del pedido.' : null,
            'created_at' => $placedAt,
            'updated_at' => $placedAt,
            ...($containers === null ? [] : ['returnable_containers' => $containers]),
            ...($isDemoCustomer ? [
                'delivery_address' => $customer->addresses->first()->fullLine(),
                'delivery_notes' => $customer->addresses->first()->notes,
            ] : []),
        ]);

        foreach ($lines as $line) {
            $order->items()->create([
                'dish_id' => $line['dish']->id,
                'name' => $line['dish']->name,
                'unit_price' => $line['dish']->price,
                'quantity' => $line['quantity'],
            ]);
        }

        $this->logs($order, $placedAt);

        $order->payment()->create([
            'method' => $order->payment_method,
            'card_last4' => $order->payment_method === 'card' ? fake()->numerify('####') : null,
            'wallet' => $order->payment_method === 'wallet' ? fake()->randomElement(['Mercado Pago', 'Ualá', 'MODO']) : null,
            'amount' => $order->total,
            'status' => 'approved',
            'receipt_number' => 'C-'.str_pad((string) $order->id, 8, '0', STR_PAD_LEFT),
            'created_at' => $placedAt,
            'updated_at' => $placedAt,
        ]);

        if ($status === OrderStatus::Delivered) {
            $this->leaves($order, $restaurant, $customer, $placedAt);
        }

        return $order;
    }

    private function logs(Order $order, CarbonInterface $placedAt): void
    {
        $minutes = match ($order->status) {
            OrderStatus::Received => ['received' => 0],
            OrderStatus::Rejected => ['received' => 0, 'rejected' => 5],
            OrderStatus::Preparing => ['received' => 0, 'preparing' => 3],
            OrderStatus::OnTheWay => ['received' => 0, 'preparing' => 3, 'on_the_way' => 19],
            OrderStatus::Delivered => ['received' => 0, 'preparing' => 4, 'on_the_way' => 21, 'delivered' => 21 + (int) ($order->restaurant->eta_minutes / 2)],
        };

        foreach ($minutes as $status => $offset) {
            $order->statusLogs()->create(['status' => $status, 'created_at' => $placedAt->copy()->addMinutes($offset)]);
        }
    }

    /**
     * Same rules the workflow applies when an order is delivered.
     */
    private function leaves(Order $order, Restaurant $restaurant, User $customer, CarbonInterface $placedAt): void
    {
        $credits = [];

        if ($restaurant->distance_km <= Setting::number(Setting::NEARBY_MAX_KM)) {
            $credits[] = [LeafTransaction::REASON_NEARBY, (int) Setting::number(Setting::LEAVES_NEARBY), "Pedido cercano en {$restaurant->name}"];
        }

        if ($order->returnable_containers) {
            $credits[] = [LeafTransaction::REASON_CONTAINERS, (int) Setting::number(Setting::LEAVES_CONTAINERS), "Envases retornables en {$restaurant->name}"];
        }

        foreach ($credits as [$reason, $points, $description]) {
            $customer->leafTransactions()->create([
                'order_id' => $order->id,
                'points' => $points,
                'reason' => $reason,
                'description' => $description,
                'created_at' => $placedAt->copy()->addHour(),
                'updated_at' => $placedAt->copy()->addHour(),
            ]);
        }

        $earned = array_sum(array_column($credits, 1));
        $order->update(['leaves_earned' => $earned]);
        $customer->increment('leaf_balance', $earned);
    }

    private function seedNotifications(Order $preparing, Order $onTheWay, array $incoming, Order $kitchen): void
    {
        $lastDelivered = $this->customer->orders()->where('status', OrderStatus::Delivered)->latest()->first();

        $this->notify($this->customer, now()->subDays(3), read: true,
            title: 'Pedido entregado',
            body: "Sumaste {$lastDelivered->leaves_earned} Hojitas Verdes con el pedido {$lastDelivered->code}.",
            url: route('orders.show', $lastDelivered, absolute: false), icon: 'check');

        $this->notify($this->customer, now()->subMinutes(11),
            title: 'Tu pedido está en preparación',
            body: "{$preparing->restaurant->name} aceptó el pedido {$preparing->code}.",
            url: route('orders.show', $preparing, absolute: false), icon: 'menu');

        $this->notify($this->customer, now()->subMinutes(9),
            title: 'Tu pedido está en camino',
            body: "{$this->courier->firstName()} ya salió con el pedido {$onTheWay->code}.",
            url: route('orders.show', $onTheWay, absolute: false), icon: 'bike');

        $owner = $onTheWay->restaurant->owner;

        foreach (array_reverse($incoming) as $order) {
            $this->notify($owner, $order->created_at,
                title: 'Tenés un pedido nuevo',
                body: "{$order->customer->firstName()} pidió {$order->itemsSummary()}.",
                url: route('restaurant.orders.show', $order, absolute: false), icon: 'receipt');
        }

        foreach ([$kitchen, $preparing] as $order) {
            $this->notify($this->courier, $order->created_at->copy()->addMinutes(3),
                title: 'Tenés una entrega nueva',
                body: "Retirá el pedido {$order->code} en {$order->restaurant->name}.",
                url: route('courier.deliveries.show', $order, absolute: false), icon: 'bike');
        }
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
