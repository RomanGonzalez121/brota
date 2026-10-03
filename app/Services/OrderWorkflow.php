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
