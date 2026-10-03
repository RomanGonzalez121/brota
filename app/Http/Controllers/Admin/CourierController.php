<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * M11: the administrator adds couriers to Brotá and takes them off.
 */
class CourierController extends Controller
{
    private const OPEN = [OrderStatus::Preparing, OrderStatus::OnTheWay];

    public function index(): View
    {
        $couriers = User::query()
            ->where('role', UserRole::Courier)
            ->withCount([
                'deliveries as open_deliveries' => fn ($orders) => $orders->whereIn('status', self::OPEN),
                'deliveries as month_deliveries' => fn ($orders) => $orders->deliveredSince(now()->startOfMonth()),
            ])
            ->orderBy('name')
            ->get();

        return view('admin.couriers', [
            'active' => $couriers->where('is_active', true)->values(),
            'inactive' => $couriers->where('is_active', false)->values(),
            'onTheStreet' => $couriers->where('open_deliveries', '>', 0)->count(),
            'vehicles' => array_combine(RegisterController::VEHICLES, RegisterController::VEHICLES),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users')],
            'phone' => ['required', 'string', 'max:30'],
            'vehicle' => ['required', Rule::in(RegisterController::VEHICLES)],
            'password' => ['required', Password::min(8)],
        ], [
            'name.required' => 'Escribí el nombre y apellido.',
            'email.required' => 'Escribí el email con el que va a ingresar.',
            'email.unique' => 'Ya hay una cuenta con ese email.',
            'phone.required' => 'Escribí un teléfono para que lo ubiquen.',
            'vehicle.required' => 'Elegí con qué va a repartir.',
            'password.required' => 'Escribí una contraseña inicial.',
            'password.min' => 'La contraseña tiene que tener al menos 8 caracteres.',
        ]);

        $courier = User::create([...$data, 'role' => UserRole::Courier]);

        return back()->with('status', "Diste de alta a {$courier->name}. Ya puede recibir entregas.");
    }

    /**
     * Off Brotá and back on. A courier who is off cannot sign in and gets no
     * new deliveries, so one who still has orders in hand has to finish first.
     */
    public function toggle(User $courier): RedirectResponse
    {
        abort_unless($courier->role === UserRole::Courier, 404);

        if ($courier->is_active) {
            $open = $courier->deliveries()->whereIn('status', self::OPEN)->count();

            if ($open > 0) {
                return back()->with('status_tone', 'danger')->with('status', $courier->firstName().' todavía tiene '
                    .($open === 1 ? '1 entrega en curso' : "{$open} entregas en curso").'. Esperá a que termine para darle la baja.');
            }
        }

        $courier->update(['is_active' => ! $courier->is_active]);

        return back()->with('status', $courier->is_active
            ? "{$courier->name} vuelve a recibir entregas."
            : "Diste de baja a {$courier->name}. Ya no recibe entregas ni puede ingresar.");
    }
}
