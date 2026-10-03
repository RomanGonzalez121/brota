<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * System parameters. For now, the rules that award Hojitas Verdes.
 */
class SettingSeeder extends Seeder
{
    public function run(): void
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
}
