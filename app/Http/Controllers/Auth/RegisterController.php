<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * M1: sign-up. A person registers as a customer, a courier or a restaurant;
 * administrators are never self-registered.
 */
class RegisterController extends Controller
{
    public const VEHICLES = ['Bicicleta', 'Bicicleta eléctrica', 'Monopatín eléctrico', 'Moto'];

    public function create(): View
    {
        return view('auth.register', [
            'categories' => Category::query()->orderBy('name')->pluck('name', 'id'),
            'vehicles' => array_combine(self::VEHICLES, self::VEHICLES),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $role = UserRole::tryFrom((string) $request->input('role'));

        $data = $request->validate([
            'role' => ['required', Rule::in([UserRole::Customer->value, UserRole::Courier->value, UserRole::Restaurant->value])],
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users')],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(8)],

            'vehicle' => [Rule::requiredIf($role === UserRole::Courier), 'nullable', Rule::in(self::VEHICLES)],

            'restaurant_name' => [Rule::requiredIf($role === UserRole::Restaurant), 'nullable', 'string', 'max:80'],
            'category_id' => [Rule::requiredIf($role === UserRole::Restaurant), 'nullable', Rule::exists('categories', 'id')],
            'tagline' => [Rule::requiredIf($role === UserRole::Restaurant), 'nullable', 'string', 'max:120'],
            'address' => [Rule::requiredIf($role === UserRole::Restaurant), 'nullable', 'string', 'max:120'],
            'neighborhood' => [Rule::requiredIf($role === UserRole::Restaurant), 'nullable', 'string', 'max:60'],
        ], [
            'role.required' => 'Elegí cómo vas a usar Brotá.',
            'email.unique' => 'Ya hay una cuenta con ese email. Probá ingresar.',
            'password.confirmed' => 'Las dos contraseñas no coinciden.',
            'password.min' => 'La contraseña tiene que tener al menos 8 caracteres.',
            'vehicle.required' => 'Elegí con qué vas a repartir.',
            'restaurant_name.required' => 'Escribí el nombre de tu local.',
            'category_id.required' => 'Elegí el tipo de cocina.',
            'tagline.required' => 'Contá en una línea qué ofrece tu local.',
            'address.required' => 'Escribí la dirección del local.',
            'neighborhood.required' => 'Escribí el barrio del local.',
        ]);

        $user = DB::transaction(function () use ($data, $role) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => $data['password'],
                'role' => $role,
                'vehicle' => $role === UserRole::Courier ? $data['vehicle'] : null,
            ]);

            if ($role === UserRole::Restaurant) {
                $this->openRestaurant($user, $data);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route($role->homeRoute())->with('status', match ($role) {
            UserRole::Restaurant => 'Tu local ya está en Brotá. Cargá el menú para empezar a recibir pedidos.',
            UserRole::Courier => 'Listo, ya podés recibir entregas.',
            default => "¡Bienvenido a Brotá, {$user->firstName()}!",
        });
    }

    /**
     * A new restaurant starts with sensible defaults for what the form does not ask.
     *
     * @param  array<string, mixed>  $data
     */
    private function openRestaurant(User $owner, array $data): void
    {
        $slug = Str::slug($data['restaurant_name']);

        if (Restaurant::query()->where('slug', $slug)->exists()) {
            $slug .= '-'.Str::lower(Str::random(4));
        }

        Restaurant::create([
            'user_id' => $owner->id,
            'category_id' => $data['category_id'],
            'name' => $data['restaurant_name'],
            'slug' => $slug,
            'tagline' => $data['tagline'],
            'description' => $data['tagline'],
            'address' => $data['address'],
            'neighborhood' => $data['neighborhood'],
            // No map service in the mockup: the distance to the customer is made up.
            'distance_km' => fake()->randomFloat(1, 0.6, 4.5),
            'rating' => 0,
            'delivery_fee' => 1500,
            'eta_minutes' => 30,
        ]);
    }
}
