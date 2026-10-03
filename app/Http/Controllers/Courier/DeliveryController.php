<?php

namespace App\Http\Controllers\Courier;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderWorkflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * M7: the courier sees the orders assigned to them, where to pick each one up
 * and where to take it, and reports each step of the way.
 */
class DeliveryController extends Controller
{
    public function index(Request $request): View
    {
        $courier = $request->user();

        $open = $courier->deliveries()
            ->whereIn('status', [OrderStatus::Preparing, OrderStatus::OnTheWay])
            ->with(['restaurant', 'customer', 'items'])
            ->oldest('updated_at')
            ->get();

        $today = $courier->deliveries()->deliveredSince(today())->get();

        return view('courier.deliveries.index', [
            'courier' => $courier,
            'onTheWay' => $open->where('status', OrderStatus::OnTheWay)->values(),
            'toPickUp' => $open->where('status', OrderStatus::Preparing)->values(),
            'latest' => $courier->deliveries()
                ->where('status', OrderStatus::Delivered)
                ->withDeliveredAt()
                ->with(['restaurant', 'customer'])
                ->orderByDesc('delivered_at')
                ->limit(5)
                ->get(),
            'deliveredToday' => $today->count(),
            // The delivery fee is what the courier keeps.
            'earnedToday' => (int) $today->sum('delivery_fee'),
            'stamp' => $this->stamp($courier),
        ]);
    }

    /**
     * How many deliveries are open, and a stamp of the list as it is now. The
     * screen asks for this every few seconds and updates itself when a
     * restaurant hands over a new order.
     */
    public function pending(Request $request): JsonResponse
    {
        $courier = $request->user();

        return response()->json([
            'count' => $courier->deliveries()->whereIn('status', [OrderStatus::Preparing, OrderStatus::OnTheWay])->count(),
            'stamp' => $this->stamp($courier),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->ensureOwn($request, $order);

        return view('courier.deliveries.show', [
            'order' => $order->load(['restaurant', 'customer', 'items', 'payment', 'statusLogs']),
        ]);
    }

    /**
     * The courier has the order in hand and is leaving the restaurant.
     */
    public function pickUp(Request $request, Order $order, OrderWorkflow $workflow): RedirectResponse
    {
        $this->ensureOwn($request, $order);
        abort_unless($order->status === OrderStatus::Preparing, 409);

        $workflow->moveTo($order, OrderStatus::OnTheWay);

        return back()->with('status', "Retiraste el pedido {$order->code}. Llevalo a {$order->customer->firstName()}.");
    }

    public function deliver(Request $request, Order $order, OrderWorkflow $workflow): RedirectResponse
    {
        $this->ensureOwn($request, $order);
        abort_unless($order->status === OrderStatus::OnTheWay, 409);

        $workflow->moveTo($order, OrderStatus::Delivered);

        return back()->with('status', "Entregaste el pedido {$order->code}. ¡Buen trabajo!");
    }

    /**
     * Changes whenever a delivery is assigned or any of them moves on.
     */
    private function stamp(User $courier): string
    {
        return $courier->deliveries()->count().'-'.$courier->deliveries()->max('updated_at');
    }

    private function ensureOwn(Request $request, Order $order): void
    {
        abort_unless($order->courier_id === $request->user()->id, 404);
    }
}
