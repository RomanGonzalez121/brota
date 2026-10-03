<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Http\Controllers\Auth\LoginController;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Same data on every run, so screenshots and the README stay true.
        fake()->seed(2026);
        mt_srand(2026);

        $this->seedSettings();
        $this->seedUsers();

        $this->call([
            CatalogSeeder::class,
            OrderSeeder::class,
            ForumSeeder::class,
        ]);
    }

    private function seedSettings(): void
    {
        Setting::create([
            'key' => Setting::LEAVES_NEARBY,
            'value' => '10',
            'label' => 'Hojitas por pedido cercano',
            'help' => 'Se acreditan cuando se entrega un pedido de un restaurante cercano.',
        ]);

        Setting::create([
            'key' => Setting::NEARBY_MAX_KM,
            'value' => '3',
            'label' => 'Distancia máxima para considerar un pedido cercano (km)',
            'help' => 'Los restaurantes a esta distancia o menos suman hojitas.',
        ]);

        Setting::create([
            'key' => Setting::LEAVES_CONTAINERS,
            'value' => '15',
            'label' => 'Hojitas por envases retornables',
            'help' => 'Se acreditan cuando el pedido se entrega en envases retornables.',
        ]);
    }

    private function seedUsers(): void
    {
        // The four users behind the "Entrar como…" buttons of the login screen.
        User::factory()->create([
            'name' => 'Lucía Fernández',
            'email' => LoginController::demoEmail(UserRole::Customer),
            'phone' => '11 5523-8841',
        ]);

        User::factory()->courier()->create([
            'name' => 'Matías Acosta',
            'email' => LoginController::demoEmail(UserRole::Courier),
            'vehicle' => 'Bicicleta',
        ]);

        User::factory()->restaurantOwner()->create([
            'name' => 'Sofía Giménez',
            'email' => LoginController::demoEmail(UserRole::Restaurant),
        ]);

        User::factory()->admin()->create([
            'name' => 'Carla Méndez',
            'email' => LoginController::demoEmail(UserRole::Admin),
        ]);

        foreach (['Tomás Ibarra', 'Valentina Sosa', 'Joaquín Peralta', 'Camila Rojas', 'Nicolás Ferreyra', 'Martina Quiroga'] as $name) {
            User::factory()->create(['name' => $name]);
        }

        User::factory()->courier()->create(['name' => 'Brenda Luna', 'vehicle' => 'Bicicleta eléctrica']);
        User::factory()->courier()->create(['name' => 'Ezequiel Maidana', 'vehicle' => 'Monopatín eléctrico']);
        User::factory()->courier()->create(['name' => 'Rocío Benítez', 'vehicle' => 'Bicicleta', 'is_active' => false]);
    }
}
