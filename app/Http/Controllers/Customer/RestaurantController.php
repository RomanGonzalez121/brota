<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Dish;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Services\Cart;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RestaurantController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $category = $request->query('categoria');
        $onlyNearby = $request->boolean('cerca');
        $nearbyKm = Setting::number(Setting::NEARBY_MAX_KM);

        $restaurants = Restaurant::query()
            ->active()
            ->with('category')
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('tagline', 'like', "%{$search}%")
                ->orWhereHas('dishes', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))))
            ->when($category, fn (Builder $query) => $query->whereRelation('category', 'slug', $category))
            ->when($onlyNearby, fn (Builder $query) => $query->where('distance_km', '<=', $nearbyKm))
            ->orderBy('distance_km')
            ->get();

        return view('customer.restaurants.index', [
            'restaurants' => $restaurants,
            'categories' => Category::query()->orderBy('name')->get(),
            'search' => $search,
            'category' => $category,
            'onlyNearby' => $onlyNearby,
            'nearbyKm' => $nearbyKm,
        ]);
    }

    public function show(Restaurant $restaurant, Cart $cart): View
    {
        abort_unless($restaurant->is_active, 404);

        $sections = $restaurant->dishes
            ->groupBy('section')
            ->sortBy(fn ($dishes, string $section) => array_search($section, Dish::SECTIONS, true));

        return view('customer.restaurants.show', [
            'restaurant' => $restaurant->load('category'),
            'sections' => $sections,
            'cart' => $cart,
            'cartIsHere' => $cart->restaurant()?->is($restaurant) ?? false,
            'nearbyKm' => Setting::number(Setting::NEARBY_MAX_KM),
        ]);
    }
}
