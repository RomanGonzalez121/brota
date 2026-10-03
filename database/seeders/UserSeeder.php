<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Http\Controllers\Auth\LoginController;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Customers, couriers and the admin. Restaurant owners other than the demo
 * one are created by RestaurantSeeder, next to their restaurant.
 */
class UserSeeder extends Seeder
{
    public function run(): void
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

        // The first six write in the forum, so their order is kept.
        $customers = [
            'Tomás Ibarra', 'Valentina Sosa', 'Joaquín Peralta', 'Camila Rojas', 'Nicolás Ferreyra', 'Martina Quiroga',
            'Agustín Molina', 'Florencia Cabrera', 'Ignacio Paz', 'Julieta Arce',
        ];

        foreach ($customers as $name) {
            User::factory()->create(['name' => $name]);
        }

        User::factory()->courier()->create(['name' => 'Brenda Luna', 'vehicle' => 'Bicicleta eléctrica']);
        User::factory()->courier()->create(['name' => 'Ezequiel Maidana', 'vehicle' => 'Monopatín eléctrico']);
        User::factory()->courier()->create(['name' => 'Lautaro Vega', 'vehicle' => 'Bicicleta']);
        User::factory()->courier()->create(['name' => 'Rocío Benítez', 'vehicle' => 'Bicicleta', 'is_active' => false]);
    }
}
