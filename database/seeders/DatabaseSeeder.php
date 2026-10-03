<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * The order matters: each seeder builds on the ones before it.
     */
    public function run(): void
    {
        // Same data on every run, so screenshots and the README stay true.
        fake()->seed(2026);
        mt_srand(2026);

        $this->call([
            SettingSeeder::class,
            UserSeeder::class,
            AddressSeeder::class,
            CategorySeeder::class,
            RestaurantSeeder::class,
            OrderSeeder::class,
            NotificationSeeder::class,
            ForumSeeder::class,
        ]);
    }
}
