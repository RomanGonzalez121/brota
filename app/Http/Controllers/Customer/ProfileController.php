<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('customer.profile', [
            'user' => $request->user(),
            'addresses' => $request->user()->addresses()->orderByDesc('is_default')->orderBy('id')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $user->update($request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
        ]));

        return back()->with('status', 'Guardamos tus datos.');
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $this->validateAddress($request);

        $address = $user->addresses()->create([...$data, 'is_default' => $user->addresses()->doesntExist()]);
        $this->applyDefault($request, $address);

        return back()->with('status', "Agregaste la dirección {$address->label}.");
    }

    public function updateAddress(Request $request, Address $address): RedirectResponse
    {
        $this->ensureOwner($request, $address);

        $address->update($this->validateAddress($request));
        $this->applyDefault($request, $address);

        return back()->with('status', "Actualizaste la dirección {$address->label}.");
    }

    public function destroyAddress(Request $request, Address $address): RedirectResponse
    {
        $this->ensureOwner($request, $address);

        $address->delete();

        // Never leave the customer without a default address.
        if ($address->is_default) {
            $request->user()->addresses()->oldest('id')->first()?->update(['is_default' => true]);
        }

        return back()->with('status', "Borraste la dirección {$address->label}.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'label' => ['required', 'string', 'max:30'],
            'street' => ['required', 'string', 'max:120'],
            'neighborhood' => ['required', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:160'],
        ]);
    }

    private function applyDefault(Request $request, Address $address): void
    {
        if ($request->boolean('is_default')) {
            $request->user()->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        }
    }

    private function ensureOwner(Request $request, Address $address): void
    {
        abort_unless($address->user_id === $request->user()->id, 404);
    }
}
