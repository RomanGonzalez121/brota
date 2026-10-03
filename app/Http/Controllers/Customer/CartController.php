<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Services\Cart;
use Illuminate\Http\JsonResponse;
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

    /**
     * The menu adds dishes in the background (JSON), so the page does not
     * reload; a plain form post still works and redirects back.
     */
    public function store(Request $request, Dish $dish, Cart $cart): RedirectResponse|JsonResponse
    {
        abort_unless($dish->is_available, 422);

        $replaced = $cart->add($dish);
        $message = $replaced
            ? "Empezaste un carrito nuevo en {$dish->restaurant->name}."
            : "Agregaste {$dish->name} al carrito.";

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'count' => $cart->count(),
                'subtotal' => money($cart->subtotal()),
                'dish' => $dish->id,
                'quantity' => $cart->lines()->firstWhere(fn ($line) => $line->dish->id === $dish->id)->quantity,
                'replaced' => $replaced,
            ]);
        }

        return back()->withFragment("plato-{$dish->id}")->with('status', $message);
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
