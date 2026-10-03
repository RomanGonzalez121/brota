<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Loyalty;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeafController extends Controller
{
    public function __invoke(Request $request, Loyalty $loyalty): View
    {
        $user = $request->user();

        return view('customer.leaves', [
            'balance' => $user->leaf_balance,
            'levels' => $loyalty->levels(),
            'level' => $loyalty->levelFor($user->leaf_balance),
            'nextLevel' => $loyalty->nextLevelFor($user->leaf_balance),
            'remaining' => $loyalty->remaining($user),
            'freeOrderCap' => $loyalty->freeOrderCap(),
            'transactions' => $user->leafTransactions()->with('order')->latest()->get(),
            'nearbyLeaves' => (int) Setting::number(Setting::LEAVES_NEARBY),
            'nearbyKm' => Setting::number(Setting::NEARBY_MAX_KM),
            'containerLeaves' => (int) Setting::number(Setting::LEAVES_CONTAINERS),
        ]);
    }
}
