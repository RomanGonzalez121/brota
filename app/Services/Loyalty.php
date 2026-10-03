<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Setting;
use App\Models\User;

/**
 * M9: the levels of Hojitas Verdes and what each one gives.
 *
 * A customer climbs by collecting hojitas; they are never spent, so a level is
 * never lost. Each level grants benefits that renew every month: free
 * deliveries from Brote up and, at Árbol, a whole order on the house up to a
 * cap. Brotá covers the benefit: restaurant and courier are paid in full.
 */
class Loyalty
{
    public const FREE_DELIVERY = 'free_delivery';

    public const FREE_ORDER = 'free_order';

    /** @var array<string, float>|null */
    private ?array $settings = null;

    /**
     * The four levels, lowest first.
     *
     * @return list<array{key: string, name: string, icon: string, min: int, free_deliveries: int, free_orders: int}>
     */
    public function levels(): array
    {
        $settings = $this->settings ??= Setting::numbers();

        return [
            ['key' => 'seed', 'name' => 'Semilla', 'icon' => 'seed', 'min' => 0, 'free_deliveries' => 0, 'free_orders' => 0],
            ['key' => 'sprout', 'name' => 'Brote', 'icon' => 'sprout', 'min' => (int) $settings[Setting::LEVEL_SPROUT_MIN],
                'free_deliveries' => (int) $settings[Setting::SPROUT_FREE_DELIVERIES], 'free_orders' => 0],
            ['key' => 'plant', 'name' => 'Planta', 'icon' => 'pot', 'min' => (int) $settings[Setting::LEVEL_PLANT_MIN],
                'free_deliveries' => (int) $settings[Setting::PLANT_FREE_DELIVERIES], 'free_orders' => 0],
            ['key' => 'tree', 'name' => 'Árbol', 'icon' => 'tree', 'min' => (int) $settings[Setting::LEVEL_TREE_MIN],
                'free_deliveries' => (int) $settings[Setting::TREE_FREE_DELIVERIES], 'free_orders' => (int) $settings[Setting::TREE_FREE_ORDERS]],
        ];
    }

    /**
     * The highest level whose threshold the balance reaches.
     *
     * @return array{key: string, name: string, icon: string, min: int, free_deliveries: int, free_orders: int}
     */
    public function levelFor(int $leaves): array
    {
        return collect($this->levels())->last(fn (array $level) => $leaves >= $level['min']);
    }

    /**
     * @return array{key: string, name: string, icon: string, min: int, free_deliveries: int, free_orders: int}|null
     */
    public function nextLevelFor(int $leaves): ?array
    {
        return collect($this->levels())->first(fn (array $level) => $leaves < $level['min']);
    }

    /**
     * How many of each benefit the customer still has this month. An order
     * that was rejected gives its benefit back.
     *
     * @return array{free_delivery: int, free_order: int}
     */
    public function remaining(User $customer): array
    {
        $level = $this->levelFor($customer->leaf_balance);

        $used = $customer->orders()
            ->whereNotNull('benefit')
            ->where('status', '!=', OrderStatus::Rejected)
            ->where('created_at', '>=', now()->startOfMonth())
            ->pluck('benefit')
            ->countBy();

        return [
            self::FREE_DELIVERY => max($level['free_deliveries'] - $used->get(self::FREE_DELIVERY, 0), 0),
            self::FREE_ORDER => max($level['free_orders'] - $used->get(self::FREE_ORDER, 0), 0),
        ];
    }

    public function canUse(User $customer, ?string $benefit): bool
    {
        return $benefit !== null && ($this->remaining($customer)[$benefit] ?? 0) > 0;
    }

    public function freeOrderCap(): int
    {
        return (int) ($this->settings ??= Setting::numbers())[Setting::FREE_ORDER_CAP];
    }

    /**
     * What a benefit takes off an order: the delivery, or the delivery and
     * the dishes up to the cap.
     */
    public function discount(?string $benefit, int $subtotal, int $deliveryFee): int
    {
        return match ($benefit) {
            self::FREE_DELIVERY => $deliveryFee,
            self::FREE_ORDER => $deliveryFee + min($subtotal, $this->freeOrderCap()),
            default => 0,
        };
    }

    /**
     * How each benefit reads in the interface.
     */
    public static function label(?string $benefit): ?string
    {
        return match ($benefit) {
            self::FREE_DELIVERY => 'Envío gratis',
            self::FREE_ORDER => 'Pedido gratis',
            default => null,
        };
    }
}
