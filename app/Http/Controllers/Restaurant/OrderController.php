<?php

namespace App\Http\Controllers\Restaurant;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use App\Services\OrderWorkflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * M5: the restaurant receives orders, accepts or rejects them and moves them
 * through their states.
 */
class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $restaurant = $request->user()->restaurant;

        $open = $restaurant->orders()
            ->whereIn('status', [OrderStatus::Received, OrderStatus::Preparing, OrderStatus::OnTheWay])
            ->with(['customer', 'items', 'courier'])
            ->oldest()
            ->get()
            ->groupBy(fn (Order $order) => $order->status->value);

        return view('restaurant.orders.index', [
            'restaurant' => $restaurant,
            'stamp' => $this->stamp($restaurant),
            'received' => $open->get(OrderStatus::Received->value, collect()),
            'preparing' => $open->get(OrderStatus::Preparing->value, collect()),
            'onTheWay' => $open->get(OrderStatus::OnTheWay->value, collect()),
            'finished' => $restaurant->orders()
                ->whereIn('status', [OrderStatus::Delivered, OrderStatus::Rejected])
                ->with(['customer', 'items'])
                ->latest('updated_at')
                ->limit(12)
                ->get(),
        ]);
    }

    /**
     * How many orders are waiting for an answer, and a stamp of the board as it
     * is now. The orders screen asks for this every few seconds and brings
     * itself up to date when the stamp is no longer the one it was drawn with.
     */
    public function pending(Request $request): JsonResponse
    {
        $restaurant = $request->user()->restaurant;

        return response()->json([
            'count' => $restaurant->orders()->where('status', OrderStatus::Received)->count(),
            'stamp' => $this->stamp($restaurant),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->ensureOwn($request, $order);

        return view('restaurant.orders.show', [
            'order' => $order->load(['customer', 'items', 'statusLogs', 'courier', 'payment']),
        ]);
    }

    public function accept(Request $request, Order $order, OrderWorkflow $workflow): RedirectResponse
    {
        $this->ensureOwn($request, $order);
        abort_unless($order->status === OrderStatus::Received, 409);

        $workflow->accept($order);

        return back()->with('status', "Aceptaste el pedido {$order->code}. Ya figura en preparación.");
    }

    public function reject(Request $request, Order $order, OrderWorkflow $workflow): RedirectResponse
    {
        $this->ensureOwn($request, $order);
        abort_unless($order->status === OrderStatus::Received, 409);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:200'],
        ], [
            'reason.required' => 'Contale al cliente por qué no podés tomar el pedido.',
            'reason.min' => 'Escribí un motivo un poco más claro.',
        ]);

        $workflow->reject($order, $data['reason']);

        return redirect()->route('restaurant.orders.index')->with('status', "Rechazaste el pedido {$order->code}. Le avisamos al cliente.");
    }

    /**
     * Move the order one step forward: preparing → on the way → delivered.
     */
    public function advance(Request $request, Order $order, OrderWorkflow $workflow): RedirectResponse
    {
        $this->ensureOwn($request, $order);

        $next = $order->status->next();
        abort_if($next === null || $order->status === OrderStatus::Received, 409);

        $workflow->moveTo($order, $next);

        return back()->with('status', "El pedido {$order->code} pasó a «{$next->label()}».");
    }

    /**
     * Changes whenever an order comes in or any of them moves on.
     */
    private function stamp(Restaurant $restaurant): string
    {
        return $restaurant->orders()->count().'-'.$restaurant->orders()->max('updated_at');
    }

    private function ensureOwn(Request $request, Order $order): void
    {
        abort_unless($order->restaurant_id === $request->user()->restaurant->id, 404);
    }
}
