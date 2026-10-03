<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Services\Loyalty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * M9: the administrator sets how Hojitas Verdes are earned, where each level
 * starts and what it gives.
 */
class SettingsController extends Controller
{
    public function edit(Loyalty $loyalty): View
    {
        $balances = User::query()->where('role', UserRole::Customer)->pluck('leaf_balance');

        return view('admin.settings', [
            'values' => Setting::numbers(),
            'levels' => $loyalty->levels(),
            'freeOrderCap' => $loyalty->freeOrderCap(),
            // How many customers stand on each level with the rules as they are saved.
            'customersByLevel' => $balances->countBy(fn (int $leaves) => $loyalty->levelFor($leaves)['key']),
            'customers' => $balances->count(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            Setting::LEAVES_NEARBY => ['required', 'integer', 'min:0', 'max:500'],
            Setting::NEARBY_MAX_KM => ['required', 'numeric', 'min:0.5', 'max:50'],
            Setting::LEAVES_CONTAINERS => ['required', 'integer', 'min:0', 'max:500'],
            Setting::LEVEL_SPROUT_MIN => ['required', 'integer', 'min:1', 'max:100000'],
            Setting::LEVEL_PLANT_MIN => ['required', 'integer', 'gt:'.Setting::LEVEL_SPROUT_MIN, 'max:100000'],
            Setting::LEVEL_TREE_MIN => ['required', 'integer', 'gt:'.Setting::LEVEL_PLANT_MIN, 'max:100000'],
            Setting::SPROUT_FREE_DELIVERIES => ['required', 'integer', 'min:0', 'max:60'],
            Setting::PLANT_FREE_DELIVERIES => ['required', 'integer', 'min:0', 'max:60'],
            Setting::TREE_FREE_DELIVERIES => ['required', 'integer', 'min:0', 'max:60'],
            Setting::TREE_FREE_ORDERS => ['required', 'integer', 'min:0', 'max:31'],
            Setting::FREE_ORDER_CAP => ['required', 'integer', 'min:0', 'max:1000000'],
        ], [
            Setting::LEVEL_PLANT_MIN.'.gt' => 'Planta tiene que pedir más hojitas que Brote.',
            Setting::LEVEL_TREE_MIN.'.gt' => 'Árbol tiene que pedir más hojitas que Planta.',
            '*.required' => 'Completá este valor.',
            '*.integer' => 'Tiene que ser un número entero.',
            '*.min' => 'El valor es demasiado bajo.',
            '*.max' => 'El valor es demasiado alto.',
        ]);

        foreach ($data as $key => $value) {
            Setting::query()->updateOrCreate(['key' => $key], [
                'value' => (string) $value,
                'label' => Setting::DEFINITIONS[$key]['label'],
            ]);
        }

        return back()->with('status', 'Guardamos las reglas. Ya valen para los próximos pedidos.');
    }
}
