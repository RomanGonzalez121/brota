<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\Cart;
use App\Services\Loyalty;
use App\Services\OrderWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Two steps: confirm the order (address, payment method, containers, level
 * benefit), then pay. The payment is simulated and always approved.
 */
class CheckoutController extends Controller
{
    private const SESSION_KEY = 'checkout';

    public function create(Request $request, Cart $cart, Loyalty $loyalty): View|RedirectResponse
    {
        if ($cart->isEmpty()) {
            return redirect()->route('cart.show');
        }

        $customer = $request->user();
        $restaurant = $cart->restaurant();

        return view('customer.checkout', [
            'cart' => $cart,
            'restaurant' => $restaurant,
            'addresses' => $customer->addresses()->orderByDesc('is_default')->get(),
            'checkout' => $request->session()->get(self::SESSION_KEY, []),
            'containerLeaves' => (int) Setting::number(Setting::LEAVES_CONTAINERS),
            'level' => $loyalty->levelFor($customer->leaf_balance),
            'nextLevel' => $loyalty->nextLevelFor($customer->leaf_balance),
            'remaining' => $loyalty->remaining($customer),
            'freeOrderCap' => $loyalty->freeOrderCap(),
            // What each benefit would take off this cart, so the summary can follow the choice.
            'discounts' => [
                Loyalty::FREE_DELIVERY => $loyalty->discount(Loyalty::FREE_DELIVERY, $cart->subtotal(), $restaurant->delivery_fee),
                Loyalty::FREE_ORDER => $loyalty->discount(Loyalty::FREE_ORDER, $cart->subtotal(), $restaurant->delivery_fee),
            ],
        ]);
    }

    public function store(Request $request, Cart $cart, Loyalty $loyalty): RedirectResponse
    {
        if ($cart->isEmpty()) {
            return redirect()->route('cart.show');
        }

        $data = $request->validate([
            'address_id' => ['required', Rule::exists('addresses', 'id')->where('user_id', $request->user()->id)],
            'payment_method' => ['required', Rule::in(['card', 'wallet'])],
            'returnable_containers' => ['nullable', 'boolean'],
            'benefit' => ['nullable', Rule::in([Loyalty::FREE_DELIVERY, Loyalty::FREE_ORDER])],
        ], [
            'address_id.required' => 'Elegí a qué dirección lo enviamos.',
        ]);

        $benefit = $data['benefit'] ?? null;

        $request->session()->put(self::SESSION_KEY, [
            'address_id' => (int) $data['address_id'],
            'payment_method' => $data['payment_method'],
            'returnable_containers' => $request->boolean('returnable_containers'),
            'benefit' => $loyalty->canUse($request->user(), $benefit) ? $benefit : null,
        ]);

        return redirect()->route('payment.create');
    }

    public function payment(Request $request, Cart $cart, Loyalty $loyalty): View|RedirectResponse
    {
        $checkout = $request->session()->get(self::SESSION_KEY);

        if ($cart->isEmpty() || ! $checkout) {
            return redirect()->route('cart.show');
        }

        $restaurant = $cart->restaurant();
        $benefit = $checkout['benefit'] ?? null;

        return view('customer.payment', [
            'method' => $checkout['payment_method'],
            'wallets' => Payment::WALLETS,
            'restaurant' => $restaurant,
            'benefit' => Loyalty::label($benefit),
            'total' => $this->due($cart, $loyalty, $benefit),
        ]);
    }

    public function pay(Request $request, Cart $cart, OrderWorkflow $workflow, Loyalty $loyalty): RedirectResponse
    {
        $checkout = $request->session()->get(self::SESSION_KEY);

        if ($cart->isEmpty() || ! $checkout) {
            return redirect()->route('cart.show');
        }

        // An order a benefit covers in full has nothing to charge, so it asks for no payment details.
        $payment = match (true) {
            $this->due($cart, $loyalty, $checkout['benefit'] ?? null) === 0 => [],
            $checkout['payment_method'] === 'card' => $request->validate([
                'card_number' => ['required', 'string', 'regex:/^[\d ]{13,23}$/'],
                'card_name' => ['required', 'string', 'max:80'],
                'card_expiry' => ['required', 'string', 'regex:/^(0[1-9]|1[0-2])\s?\/\s?\d{2}$/'],
                'card_cvv' => ['required', 'digits_between:3,4'],
            ], [
                'card_number.regex' => 'Revisá el número: tiene que tener entre 13 y 19 dígitos.',
                'card_expiry.regex' => 'Escribí el vencimiento como MM/AA.',
                'card_cvv.digits_between' => 'El código tiene 3 o 4 dígitos.',
            ]),
            default => $request->validate([
                'wallet' => ['required', Rule::in(Payment::WALLETS)],
            ], [
                'wallet.required' => 'Elegí con qué billetera pagás.',
            ]),
        };

        $order = $workflow->place($request->user(), $cart, $checkout, $payment);

        $cart->clear();
        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('orders.receipt', $order)->with('status', $order->total === 0
            ? '¡Ya recibimos tu pedido! Este va por cuenta de Brotá.'
            : 'Pago aprobado. ¡Ya recibimos tu pedido!');
    }

    /**
     * What the customer has to pay for the cart once the chosen benefit is taken off.
     */
    private function due(Cart $cart, Loyalty $loyalty, ?string $benefit): int
    {
        $fee = $cart->restaurant()->delivery_fee;

        return $cart->subtotal() + $fee - $loyalty->discount($benefit, $cart->subtotal(), $fee);
    }
}
