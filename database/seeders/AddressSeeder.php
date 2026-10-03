<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Http\Controllers\Auth\LoginController;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Saved delivery addresses: two for the demo customer, one for everyone else.
 */
class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $demoEmail = LoginController::demoEmail(UserRole::Customer);

        User::query()->where('email', $demoEmail)->first()->addresses()->createMany([
            ['label' => 'Casa', 'street' => 'Av. Córdoba 5432, 3° B', 'neighborhood' => 'Palermo', 'notes' => 'Timbre 3B. Si no contesto, llamame.', 'is_default' => true],
            ['label' => 'Trabajo', 'street' => 'Humboldt 1550, piso 2', 'neighborhood' => 'Palermo', 'notes' => 'Dejar en recepción.'],
        ]);

        $streets = [
            ['Gurruchaga 1840, PB', 'Palermo'],
            ['Av. Scalabrini Ortiz 620, 5° A', 'Villa Crespo'],
            ['Av. Dorrego 1120', 'Chacarita'],
            ['Zabala 3350, 2° C', 'Colegiales'],
            ['Acevedo 455', 'Villa Crespo'],
            ['Av. Medrano 980, 8° D', 'Almagro'],
            ['Fitz Roy 2240', 'Palermo'],
            ['Conde 1530, 1° B', 'Colegiales'],
            ['Jufré 310', 'Villa Crespo'],
            ['Yatay 760, 4° A', 'Almagro'],
        ];

        $others = User::query()->where('role', UserRole::Customer)->where('email', '!=', $demoEmail)->orderBy('id')->get();

        foreach ($others as $index => $customer) {
            [$street, $neighborhood] = $streets[$index % count($streets)];

            $customer->addresses()->create([
                'label' => 'Casa',
                'street' => $street,
                'neighborhood' => $neighborhood,
                'is_default' => true,
            ]);
        }
    }
}
