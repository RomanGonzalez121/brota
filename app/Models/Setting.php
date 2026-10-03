<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'value', 'label', 'help'])]
class Setting extends Model
{
    public const LEAVES_NEARBY = 'leaves_nearby_delivery';

    public const NEARBY_MAX_KM = 'leaves_nearby_max_km';

    public const LEAVES_CONTAINERS = 'leaves_returnable_containers';

    public const LEVEL_SPROUT_MIN = 'level_sprout_min';

    public const LEVEL_PLANT_MIN = 'level_plant_min';

    public const LEVEL_TREE_MIN = 'level_tree_min';

    public const SPROUT_FREE_DELIVERIES = 'level_sprout_free_deliveries';

    public const PLANT_FREE_DELIVERIES = 'level_plant_free_deliveries';

    public const TREE_FREE_DELIVERIES = 'level_tree_free_deliveries';

    public const TREE_FREE_ORDERS = 'level_tree_free_orders';

    public const FREE_ORDER_CAP = 'free_order_cap';

    /**
     * Every parameter the administrator can change, with what it is called
     * and the value it has until someone changes it.
     *
     * @var array<string, array{label: string, default: string}>
     */
    public const DEFINITIONS = [
        self::LEAVES_NEARBY => ['label' => 'Hojitas por pedido cercano', 'default' => '10'],
        self::NEARBY_MAX_KM => ['label' => 'Distancia máxima para considerar un pedido cercano (km)', 'default' => '3'],
        self::LEAVES_CONTAINERS => ['label' => 'Hojitas por envases retornables', 'default' => '15'],
        self::LEVEL_SPROUT_MIN => ['label' => 'Hojitas para llegar a Brote', 'default' => '40'],
        self::LEVEL_PLANT_MIN => ['label' => 'Hojitas para llegar a Planta', 'default' => '120'],
        self::LEVEL_TREE_MIN => ['label' => 'Hojitas para llegar a Árbol', 'default' => '300'],
        self::SPROUT_FREE_DELIVERIES => ['label' => 'Envíos gratis por mes en Brote', 'default' => '1'],
        self::PLANT_FREE_DELIVERIES => ['label' => 'Envíos gratis por mes en Planta', 'default' => '3'],
        self::TREE_FREE_DELIVERIES => ['label' => 'Envíos gratis por mes en Árbol', 'default' => '5'],
        self::TREE_FREE_ORDERS => ['label' => 'Pedidos gratis por mes en Árbol', 'default' => '1'],
        self::FREE_ORDER_CAP => ['label' => 'Tope del pedido gratis ($)', 'default' => '20000'],
    ];

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The stored value, or the parameter's default while nobody has set it.
     */
    public static function number(string $key, ?float $default = null): float
    {
        return (float) (static::query()->find($key)?->value ?? $default ?? self::DEFINITIONS[$key]['default'] ?? 0);
    }

    /**
     * All the parameters at once, as numbers.
     *
     * @return array<string, float>
     */
    public static function numbers(): array
    {
        $stored = static::query()->pluck('value', 'key');

        return collect(self::DEFINITIONS)
            ->map(fn (array $definition, string $key) => (float) ($stored[$key] ?? $definition['default']))
            ->all();
    }
}
