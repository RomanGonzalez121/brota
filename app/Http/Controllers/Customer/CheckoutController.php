<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\Cart;
use App\Services\OrderWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Two steps: confirm the order (address, payment method, containers), then
 * pay. The payment is simulated and always approved.
 */
class CheckoutController extends Controller
{
    private const SESSION_KEY = 'checkout';

    public function create(Request $request, Cart $cart): View|RedirectResponse
    {
        if ($cart->isEmpty()) {
            return redirect()->route('cart.show');
        }

        return view('customer.checkout', [
            'cart' => $cart,
            'restaurant' => $cart->restaurant(),
            'addresses' => $request->user()->addresses()->orderByDesc('is_default')->get(),
            'checkout' => $request->session()->get(self::SESSION_KEY, []),
            'containerLeaves' => (int) Setting::number(Setting::LEAVES_CONTAINERS),
        ]);
    }

    public function store(Request $request, Cart $cart): RedirectResponse
    {
        if ($cart->isEmpty()) {
            return redirect()->route('cart.show');
        }

        $data = $request->validate([
            'address_id' => ['required', Rule::exists('addresses', 'id')->where('user_id', $request->user()->id)],
            'payment_method' => ['required', Rule::in(['card', 'wallet'])],
            'returnable_containers' => ['nullable', 'boolean'],
        ], [
            'address_id.required' => 'Elegí a qué dirección lo enviamos.',
        ]);

        $request->session()->put(self::SESSION_KEY, [
            'address_id' => (int) $data['address_id'],
            'payment_method' => $data['payment_method'],
            'returnable_containers' => $request->boolean('returnable_containers'),
        ]);

        return redirect()->route('payment.create');
    }

    public function payment(Request $request, Cart $cart): View|RedirectResponse
    {
        $checkout = $request->session()->get(self::SESSION_KEY);

        if ($cart->isEmpty() || ! $checkout) {
            return redirect()->route('cart.show');
        }

        $restaurant = $cart->restaurant();

        return view('customer.payment', [
            'method' => $checkout['payment_method'],
            'wallets' => Payment::WALLETS,
            'restaurant' => $restaurant,
            'total' => $cart->subtotal() + $restaurant->delivery_fee,
        ]);
    }

    public function pay(Request $request, Cart $cart, OrderWorkflow $workflow): RedirectResponse
    {
        $checkout = $request->session()->get(self::SESSION_KEY);

        if ($cart->isEmpty() || ! $checkout) {
            return redirect()->route('cart.show');
        }

        $payment = $checkout['payment_method'] === 'card'
            ? $request->validate([
                'card_number' => ['required', 'string', 'regex:/^[\d ]{13,23}$/'],
                'card_name' => ['required', 'string', 'max:80'],
                'card_expiry' => ['required', 'string', 'regex:/^(0[1-9]|1[0-2])\s?\/\s?\d{2}$/'],
                'card_cvv' => ['required', 'digits_between:3,4'],
            ], [
                'card_number.regex' => 'Revisá el número: tiene que tener entre 13 y 19 dígitos.',
                'card_expiry.regex' => 'Escribí el vencimiento como MM/AA.',
                'card_cvv.digits_between' => 'El código tiene 3 o 4 dígitos.',
            ])
            : $request->validate([
                'wallet' => ['required', Rule::in(Payment::WALLETS)],
            ], [
                'wallet.required' => 'Elegí con qué billetera pagás.',
            ]);

        $order = $workflow->place($request->user(), $cart, $checkout, $payment);

        $cart->clear();
        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('orders.receipt', $order)->with('status', 'Pago aprobado. ¡Ya recibimos tu pedido!');
    }
}
