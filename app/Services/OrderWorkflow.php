<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\LeafTransaction;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Moves an order through its states and fires everything that hangs off a
 * change: the status log, the notifications and the Hojitas Verdes.
 */
class OrderWorkflow
{
    /**
     * Turn the cart into a paid order. The payment is simulated: it is always approved.
     *
     * @param  array{address_id: int, payment_method: string, returnable_containers: bool}  $checkout
     * @param  array<string, string>  $payment  validated card or wallet fields
     */
    public function place(User $customer, Cart $cart, array $checkout, array $payment): Order
    {
        $restaurant = $cart->restaurant();
        $address = $customer->addresses()->findOrFail($checkout['address_id']);
        $subtotal = $cart->subtotal();

        $order = DB::transaction(function () use ($customer, $cart, $checkout, $payment, $restaurant, $address, $subtotal) {
            $order = Order::create([
                'code' => 'BR-'.(Order::query()->max('id') + 1001),
                'customer_id' => $customer->id,
                'restaurant_id' => $restaurant->id,
                'status' => OrderStatus::Received,
                'delivery_address' => $address->fullLine(),
                'delivery_notes' => $address->notes,
                'payment_method' => $checkout['payment_method'],
                'subtotal' => $subtotal,
                'delivery_fee' => $restaurant->delivery_fee,
                'total' => $subtotal + $restaurant->delivery_fee,
                'returnable_containers' => $checkout['returnable_containers'],
            ]);

            foreach ($cart->lines() as $line) {
                $order->items()->create([
                    'dish_id' => $line->dish->id,
                    'name' => $line->dish->name,
                    'unit_price' => $line->dish->price,
                    'quantity' => $line->quantity,
                ]);
            }

            $order->statusLogs()->create(['status' => OrderStatus::Received, 'created_at' => now()]);

            $order->payment()->create([
                'method' => $checkout['payment_method'],
                'card_last4' => isset($payment['card_number']) ? substr(preg_replace('/\D/', '', $payment['card_number']), -4) : null,
                'wallet' => $payment['wallet'] ?? null,
                'amount' => $order->total,
                'status' => 'approved',
                'receipt_number' => 'C-'.str_pad((string) $order->id, 8, '0', STR_PAD_LEFT),
            ]);

            return $order;
        });

        $restaurant->owner->notify(new ActivityNotification(
            title: 'Tenés un pedido nuevo',
            body: "{$customer->firstName()} pidió {$order->itemsSummary()}.",
            url: route('restaurant.orders.show', $order, absolute: false),
            icon: 'receipt',
        ));

        return $order;
    }

    public function accept(Order $order): void
    {
        $this->moveTo($order, OrderStatus::Preparing);
    }

    public function reject(Order $order, string $reason): void
    {
        if ($order->status !== OrderStatus::Received) {
            throw new InvalidArgumentException('Only a received order can be rejected.');
        }

        DB::transaction(function () use ($order, $reason) {
            $order->update(['status' => OrderStatus::Rejected, 'rejection_reason' => $reason]);
            $order->statusLogs()->create(['status' => OrderStatus::Rejected, 'created_at' => now()]);
        });

        $order->customer->notify(new ActivityNotification(
            title: 'Tu pedido fue rechazado',
            body: "{$order->restaurant->name}: {$reason}",
            url: route('orders.show', $order, absolute: false),
            icon: 'x',
        ));
    }

    public function moveTo(Order $order, OrderStatus $status): void
    {
        if ($order->status->next() !== $status) {
            throw new InvalidArgumentException("Order {$order->code} cannot move to {$status->value}.");
        }

        DB::transaction(function () use ($order, $status) {
            $order->status = $status;

            if ($status === OrderStatus::Preparing && $order->courier_id === null) {
                $order->courier_id = $this->pickCourier()?->id;
            }

            $order->save();
            $order->statusLogs()->create(['status' => $status, 'created_at' => now()]);

            if ($status === OrderStatus::Delivered) {
                $this->awardLeaves($order);
            }
        });

        $this->notify($order, $status);
    }

    /**
     * The courier with the fewest open deliveries.
     */
    private function pickCourier(): ?User
    {
        return User::query()
            ->where('role', UserRole::Courier)
            ->where('is_active', true)
            ->withCount(['deliveries as open_deliveries' => fn ($query) => $query->whereIn('status', [
                OrderStatus::Preparing, OrderStatus::OnTheWay,
            ])])
            ->orderBy('open_deliveries')
            ->orderBy('id')
            ->first();
    }

    private function awardLeaves(Order $order): void
    {
        $earned = 0;

        if ($order->restaurant->distance_km <= Setting::number(Setting::NEARBY_MAX_KM)) {
            $earned += $this->credit($order, LeafTransaction::REASON_NEARBY,
                (int) Setting::number(Setting::LEAVES_NEARBY),
                "Pedido cercano en {$order->restaurant->name}");
        }

        if ($order->returnable_containers) {
            $earned += $this->credit($order, LeafTransaction::REASON_CONTAINERS,
                (int) Setting::number(Setting::LEAVES_CONTAINERS),
                "Envases retornables en {$order->restaurant->name}");
        }

        $order->update(['leaves_earned' => $earned]);
    }

    private function credit(Order $order, string $reason, int $points, string $description): int
    {
        if ($points <= 0) {
            return 0;
        }

        $order->customer->leafTransactions()->create([
            'order_id' => $order->id,
            'points' => $points,
            'reason' => $reason,
            'description' => $description,
        ]);

        $order->customer->increment('leaf_balance', $points);

        return $points;
    }

    private function notify(Order $order, OrderStatus $status): void
    {
        $restaurant = $order->restaurant->name;

        [$title, $body, $icon] = match ($status) {
            OrderStatus::Preparing => ['Tu pedido está en preparación', "{$restaurant} aceptó el pedido {$order->code}.", 'menu'],
            OrderStatus::OnTheWay => ['Tu pedido está en camino', "{$order->courier?->firstName()} ya salió con el pedido {$order->code}.", 'bike'],
            OrderStatus::Delivered => ['Pedido entregado', $order->leaves_earned > 0
                ? "Sumaste {$order->leaves_earned} Hojitas Verdes con el pedido {$order->code}."
                : "El pedido {$order->code} llegó a tu dirección.", 'check'],
            default => [null, null, null],
        };

        if ($title) {
            $order->customer->notify(new ActivityNotification($title, $body, route('orders.show', $order, absolute: false), $icon));
        }

        if ($status === OrderStatus::Preparing && $order->courier) {
            $order->courier->notify(new ActivityNotification(
                title: 'Tenés una entrega nueva',
                body: "Retirá el pedido {$order->code} en {$restaurant}.",
                url: route('courier.deliveries.show', $order, absolute: false),
                icon: 'bike',
            ));
        }
    }
}
