<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([...$credentials, 'is_active' => true], remember: true)) {
            return back()
                ->withErrors(['email' => 'El email o la contraseña no coinciden. Revisalos y probá de nuevo.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route($request->user()->role->homeRoute());
    }

    /**
     * Mockup shortcut: sign in as the seeded demo user of a role.
     */
    public function demo(Request $request, UserRole $role): RedirectResponse
    {
        $user = User::query()->where('email', self::demoEmail($role))->firstOrFail();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route($role->homeRoute());
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public static function demoEmail(UserRole $role): string
    {
        return match ($role) {
            UserRole::Customer => 'cliente@brota.test',
            UserRole::Courier => 'repartidor@brota.test',
            UserRole::Restaurant => 'restaurante@brota.test',
            UserRole::Admin => 'admin@brota.test',
        };
    }
}
