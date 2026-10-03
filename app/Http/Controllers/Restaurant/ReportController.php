<?php

namespace App\Http\Controllers\Restaurant;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * M8: what the restaurant earned in a month, how the sales went day by day
 * and which dishes were ordered the most.
 */
class ReportController extends Controller
{
    /**
     * How many months back the report can look.
     */
    private const MONTHS = 3;

    public function index(Request $request): View
    {
        $restaurant = $request->user()->restaurant;

        $months = collect(range(0, self::MONTHS - 1))->map(fn (int $back) => now()->startOfMonth()->subMonthsNoOverflow($back));
        $month = $months->first(fn (CarbonInterface $option) => $option->format('Y-m') === $request->query('mes')) ?? $months->first();

        // The current month is still running, so it is compared with the same stretch of the previous one.
        $isCurrent = $month->isSameMonth(now());
        $until = $isCurrent ? now() : $month->copy()->endOfMonth();
        $previous = $month->copy()->subMonthNoOverflow();
        $previousUntil = $isCurrent
            ? $previous->copy()->addSeconds((int) $month->diffInSeconds($until))->min($previous->copy()->endOfMonth())
            : $previous->copy()->endOfMonth();

        $sales = $restaurant->orders()
            ->where('status', OrderStatus::Delivered)
            ->whereBetween('created_at', [$month, $until])
            ->with(['items.dish', 'customer'])
            ->latest()
            ->get();

        // What the restaurant keeps: the dishes, without the delivery fee.
        $earnings = (int) $sales->sum('subtotal');
        $before = (int) $restaurant->orders()
            ->where('status', OrderStatus::Delivered)
            ->whereBetween('created_at', [$previous, $previousUntil])
            ->sum('subtotal');

        $byDay = $sales->groupBy(fn (Order $order) => $order->created_at->day)->map(fn (Collection $orders) => (int) $orders->sum('subtotal'));
        $days = collect(range(1, $month->daysInMonth))->map(fn (int $day) => [
            'day' => $day,
            'total' => $byDay->get($day, 0),
            'orders' => $sales->filter(fn (Order $order) => $order->created_at->day === $day)->count(),
            'future' => $isCurrent && $day > now()->day,
        ]);

        return view('restaurant.reports.index', [
            'restaurant' => $restaurant,
            'months' => $months,
            'month' => $month,
            'previous' => $previous,
            'isCurrent' => $isCurrent,
            'sales' => $sales,
            'earnings' => $earnings,
            'change' => $before > 0 ? (int) round(($earnings - $before) / $before * 100) : null,
            'averageTicket' => $sales->isEmpty() ? 0 : (int) round($earnings / $sales->count()),
            'rejected' => $restaurant->orders()->where('status', OrderStatus::Rejected)->whereBetween('created_at', [$month, $until])->count(),
            'returnableShare' => $sales->isEmpty() ? 0 : (int) round($sales->where('returnable_containers', true)->count() / $sales->count() * 100),
            'days' => $days,
            'bestDay' => $days->sortByDesc('total')->first(),
            'topDishes' => $this->topDishes($sales),
        ]);
    }

    /**
     * The five dishes with the most units sold, with what each one brought in.
     *
     * @param  Collection<int, Order>  $sales
     * @return Collection<int, array{name: string, dish: mixed, units: int, revenue: int}>
     */
    private function topDishes(Collection $sales): Collection
    {
        return $sales
            ->flatMap(fn (Order $order) => $order->items)
            ->groupBy('name')
            ->map(fn (Collection $items, string $name) => [
                'name' => $name,
                'dish' => $items->first()->dish,
                'units' => (int) $items->sum('quantity'),
                'revenue' => (int) $items->sum(fn (OrderItem $item) => $item->lineTotal()),
            ])
            ->sortBy([['units', 'desc'], ['revenue', 'desc']])
            ->take(5)
            ->values();
    }
}
