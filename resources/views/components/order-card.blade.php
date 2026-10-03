@props(['order', 'href'])

@use('App\Enums\OrderStatus')

<a href="{{ $href }}" {{ $attributes->class('block rounded-media border border-sage/20 bg-surface p-4 transition-[transform,border-color] duration-150 ease-out-strong hover:border-leaf/60 active:scale-[0.98]') }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <h3 class="truncate text-base">{{ $order->restaurant->name }}</h3>
            <p class="text-sm text-muted tabular-nums">{{ $order->code }}, {{ $order->created_at->translatedFormat('j \d\e F') }}</p>
        </div>
        <x-badge :tone="$order->status->tone()">{{ $order->status->label() }}</x-badge>
    </div>

    <p class="mt-3 line-clamp-2 text-sm">{{ $order->itemsSummary() }}</p>

    <div class="mt-3 flex items-center justify-between">
        @if ($order->status === OrderStatus::Rejected)
            <span class="text-sm text-muted">No se cobró</span>
        @else
            <x-leaf-progress :status="$order->status" />
        @endif
        <span class="font-display font-bold tabular-nums">{{ money($order->total) }}</span>
    </div>
</a>
