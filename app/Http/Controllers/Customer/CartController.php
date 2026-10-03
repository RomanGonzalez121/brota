<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Services\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function show(Cart $cart): View
    {
        return view('customer.cart', [
            'cart' => $cart,
            'restaurant' => $cart->restaurant(),
        ]);
    }

    public function store(Dish $dish, Cart $cart): RedirectResponse
    {
        abort_unless($dish->is_available, 422);

        $replaced = $cart->add($dish);

        return back()
            ->withFragment("plato-{$dish->id}")
            ->with('status', $replaced
                ? "Empezaste un carrito nuevo en {$dish->restaurant->name}."
                : "Agregaste {$dish->name} al carrito.");
    }

    public function update(Request $request, Dish $dish, Cart $cart): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:20']]);

        $cart->setQuantity($dish->id, $data['quantity']);

        return back();
    }

    public function destroy(Cart $cart): RedirectResponse
    {
        $cart->clear();

        return back()->with('status', 'Vaciaste el carrito.');
    }
}
