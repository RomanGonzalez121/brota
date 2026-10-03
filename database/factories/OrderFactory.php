<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    private static int $sequence = 1000;

    /**
     * Customer, restaurant and totals are set by the seeder, which also adds the items.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'BR-'.(++self::$sequence),
            'status' => OrderStatus::Delivered,
            'delivery_address' => fake()->streetName().' '.fake()->numberBetween(100, 5900).', '.fake()->randomElement([
                'Palermo', 'Villa Crespo', 'Chacarita', 'Colegiales', 'Almagro',
            ]),
            'payment_method' => fake()->randomElement(['card', 'card', 'wallet']),
            'subtotal' => 0,
            'delivery_fee' => 0,
            'total' => 0,
            'returnable_containers' => fake()->boolean(40),
        ];
    }
}
