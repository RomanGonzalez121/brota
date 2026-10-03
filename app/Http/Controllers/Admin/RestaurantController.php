<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * M11: the administrator adds restaurants to Brotá and takes them off.
 */
class RestaurantController extends Controller
{
    public function index(): View
    {
        $restaurants = Restaurant::query()
            ->with(['owner', 'category'])
            ->withCount(['dishes', 'orders as month_orders' => fn ($orders) => $orders->where('created_at', '>=', now()->startOfMonth())])
            ->orderBy('name')
            ->get();

        return view('admin.restaurants', [
            'active' => $restaurants->where('is_active', true)->values(),
            'inactive' => $restaurants->where('is_active', false)->values(),
            'monthOrders' => (int) $restaurants->sum('month_orders'),
            'categories' => Category::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * A new restaurant comes with the account of whoever runs it.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'restaurant_name' => ['required', 'string', 'max:80'],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'tagline' => ['required', 'string', 'max:120'],
            'address' => ['required', 'string', 'max:120'],
            'neighborhood' => ['required', 'string', 'max:60'],
            'owner_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users')],
            'password' => ['required', Password::min(8)],
        ], [
            'restaurant_name.required' => 'Escribí el nombre del local.',
            'category_id.required' => 'Elegí el tipo de cocina.',
            'tagline.required' => 'Contá en una línea qué ofrece el local.',
            'address.required' => 'Escribí la dirección del local.',
            'neighborhood.required' => 'Escribí el barrio del local.',
            'owner_name.required' => 'Escribí quién está a cargo del local.',
            'email.required' => 'Escribí el email con el que va a ingresar.',
            'email.unique' => 'Ya hay una cuenta con ese email.',
            'password.required' => 'Escribí una contraseña inicial.',
            'password.min' => 'La contraseña tiene que tener al menos 8 caracteres.',
        ]);

        $restaurant = DB::transaction(function () use ($data) {
            $owner = User::create([
                'name' => $data['owner_name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => UserRole::Restaurant,
            ]);

            return Restaurant::open($owner, $data);
        });

        return back()->with('status', "Diste de alta a {$restaurant->name}. Va a aparecer para los clientes cuando cargue su menú.");
    }

    /**
     * Off Brotá and back on. While it is off, customers do not see it and
     * cannot order from it; the orders it already has go on.
     */
    public function toggle(Restaurant $restaurant): RedirectResponse
    {
        $restaurant->update(['is_active' => ! $restaurant->is_active]);

        return back()->with('status', $restaurant->is_active
            ? "{$restaurant->name} vuelve a estar en Brotá."
            : "Diste de baja a {$restaurant->name}. Los clientes ya no lo ven.");
    }
}
