<?php

namespace App\Http\Controllers\Customer;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Restaurant;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('customer.home', [
            'address' => $user->addresses()->orderByDesc('is_default')->first(),
            'activeOrder' => $user->orders()
                ->whereIn('status', [OrderStatus::Received, OrderStatus::Preparing, OrderStatus::OnTheWay])
                ->with('restaurant')
                ->latest()
                ->first(),
            'categories' => Category::query()->orderBy('name')->get(),
            'featured' => Restaurant::query()->active()->where('is_featured', true)->with('category')->orderBy('distance_km')->get(),
            'nearby' => Restaurant::query()->active()->with('category')->orderBy('distance_km')->limit(4)->get(),
            'nearbyKm' => Setting::number(Setting::NEARBY_MAX_KM),
        ]);
    }
}
