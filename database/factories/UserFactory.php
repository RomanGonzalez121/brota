<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Every seeded user shares this password.
     */
    public const PASSWORD = 'brota1234';

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->firstName().' '.fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make(self::PASSWORD),
            'role' => UserRole::Customer,
            'phone' => '11 '.fake()->numerify('####-####'),
            'remember_token' => Str::random(10),
        ];
    }

    public function courier(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::Courier,
            'vehicle' => fake()->randomElement(['Bicicleta', 'Bicicleta eléctrica', 'Monopatín eléctrico']),
        ]);
    }

    public function restaurantOwner(): static
    {
        return $this->state(fn () => ['role' => UserRole::Restaurant]);
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => UserRole::Admin]);
    }
}
