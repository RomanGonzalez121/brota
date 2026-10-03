<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * System parameters: the rules that award Hojitas Verdes, where each level
 * starts and what it gives. They start at their defaults and the
 * administrator changes them from the panel.
 */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $help = [
            Setting::LEAVES_NEARBY => 'Se acreditan cuando se entrega un pedido de un restaurante cercano.',
            Setting::NEARBY_MAX_KM => 'Los restaurantes a esta distancia o menos suman hojitas.',
            Setting::LEAVES_CONTAINERS => 'Se acreditan cuando el pedido se entrega en envases retornables.',
            Setting::FREE_ORDER_CAP => 'Lo que Brotá paga de platos en un pedido gratis; el resto lo paga el cliente.',
        ];

        foreach (Setting::DEFINITIONS as $key => $definition) {
            Setting::create([
                'key' => $key,
                'value' => $definition['default'],
                'label' => $definition['label'],
                'help' => $help[$key] ?? null,
            ]);
        }
    }
}
