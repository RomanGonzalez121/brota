<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()->orders()->with(['restaurant', 'items'])->latest()->get();

        [$open, $past] = $orders->partition(fn (Order $order) => $order->status->isOpen());

        return view('customer.orders.index', ['open' => $open, 'past' => $past]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->ensureOwner($request, $order);

        return view('customer.orders.show', [
            'order' => $order->load(['restaurant', 'items', 'statusLogs', 'courier', 'payment']),
        ]);
    }

    public function receipt(Request $request, Order $order): View
    {
        $this->ensureOwner($request, $order);

        return view('customer.orders.receipt', [
            'order' => $order->load(['restaurant', 'items', 'payment']),
        ]);
    }

    private function ensureOwner(Request $request, Order $order): void
    {
        abort_unless($order->customer_id === $request->user()->id, 404);
    }
}
