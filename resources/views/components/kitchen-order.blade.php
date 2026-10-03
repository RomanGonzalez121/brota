@props(['order'])

@use('App\Enums\OrderStatus')

@php
    $waiting = $order->status === OrderStatus::Received;
    $onTheWay = $order->status === OrderStatus::OnTheWay;

    // How long the order has been in its current state, and when that becomes too long.
    $since = $waiting ? $order->created_at : $order->updated_at;
    $lateAfter = match ($order->status) {
        OrderStatus::Received => 5,
        OrderStatus::Preparing => 30,
        default => 45,
    };
    $minutes = (int) $since->diffInMinutes(now());
    $elapsed = match (true) {
        $minutes < 1 => 'recién',
        $minutes < 60 => "{$minutes} min",
        $minutes < 1440 => intdiv($minutes, 60).' h',
        default => intdiv($minutes, 1440).' d',
    };
@endphp

{{--
    An order as the kitchen reads it: what to prepare first and how long it has
    been waiting; who and how much are secondary. Once it is on the way there is
    nothing left to cook, so the card shrinks to a line.
--}}
<article id="pedido-{{ $order->id }}" data-item {{ $attributes->class(['rounded-media border border-sage/15 bg-surface', $onTheWay ? 'p-3.5' : 'p-4']) }}>
    <div class="flex items-center justify-between gap-3">
        <h3 class="flex min-w-0 items-baseline gap-1.5 font-sans text-sm font-normal tracking-normal text-muted">
            <a href="{{ route('restaurant.orders.show', $order) }}" class="shrink-0 font-display font-bold text-ink tabular-nums hover:text-leaf hover:underline">{{ $order->code }}</a>
            <span class="truncate">{{ $order->customer->name }}</span>
        </h3>

        <time
            datetime="{{ $since->toIso8601String() }}"
            data-since="{{ $since->timestamp }}"
            data-late-after="{{ $lateAfter }}"
            @if ($minutes >= $lateAfter) data-late @endif
            class="inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-0.5 text-sm font-semibold text-muted tabular-nums transition-colors duration-300 data-late:bg-carrot data-late:text-forest"
        >
            <x-icon name="clock" class="size-3.5" />
            <span class="sr-only">{{ $waiting ? 'Esperando hace' : 'Hace' }}</span>
            <span data-since-label>{{ $elapsed }}</span>
        </time>
    </div>

    @if ($onTheWay)
        <p class="mt-1.5 truncate text-[15px]">{{ $order->itemsSummary() }}</p>

        <div class="mt-2 flex items-end gap-3">
            <div class="flex min-w-0 flex-1 flex-wrap gap-1.5">
                @if ($order->returnable_containers)
                    <x-badge tone="sprout"><x-icon name="box" class="size-3.5" />Envases retornables</x-badge>
                @endif
                @if ($order->courier)
                    <x-badge tone="neutral"><x-icon name="bike" class="size-3.5" />{{ $order->courier->firstName() }}</x-badge>
                @endif
            </div>

            <form method="POST" action="{{ route('restaurant.orders.advance', $order) }}" class="shrink-0">
                @csrf
                <x-button variant="secondary" size="sm" class="h-10">
                    <x-icon name="check" class="size-4" />
                    Entregado
                </x-button>
            </form>
        </div>
    @else
        <ul class="mt-3 space-y-1.5">
            @foreach ($order->items as $item)
                <li class="flex items-baseline gap-2 text-[17px] leading-snug">
                    <span class="min-w-6 shrink-0 font-display text-lg font-extrabold tabular-nums">{{ $item->quantity }}×</span>
                    <span class="font-medium">{{ $item->name }}</span>
                </li>
            @endforeach
        </ul>

        <div class="mt-3 flex items-end gap-2">
            <div class="flex min-w-0 flex-1 flex-wrap gap-1.5">
                @if ($order->returnable_containers)
                    <x-badge tone="sprout"><x-icon name="box" class="size-3.5" />Envases retornables</x-badge>
                @endif
                @if ($order->courier)
                    <x-badge tone="neutral"><x-icon name="bike" class="size-3.5" />{{ $order->courier->firstName() }}</x-badge>
                @endif
            </div>
            <span class="shrink-0 text-sm font-medium text-muted tabular-nums">{{ money($order->total) }}</span>
        </div>
    @endif

    @if ($waiting)
        {{-- The one filled button on the card. Rejecting is there, but it does not compete. --}}
        <div class="mt-3.5 flex items-center gap-1">
            <form method="POST" action="{{ route('restaurant.orders.accept', $order) }}" class="flex-1">
                @csrf
                <x-button block>Aceptar</x-button>
            </form>
            <button
                type="button"
                x-data
                @click="$dispatch('open-sheet', { name: 'reject', action: '{{ route('restaurant.orders.reject', $order) }}', code: '{{ $order->code }}' })"
                class="h-11 rounded-full px-4 font-display text-[15px] font-bold text-muted transition-[transform,background-color,color] duration-150 ease-out-strong hover:bg-danger/10 hover:text-danger active:scale-[0.97]"
            >Rechazar</button>
        </div>
    @elseif (! $onTheWay && $order->status->isOpen())
        <form method="POST" action="{{ route('restaurant.orders.advance', $order) }}" class="mt-3.5">
            @csrf
            <x-button block variant="secondary">
                <x-icon name="bike" class="size-4" />
                Salió a reparto
            </x-button>
        </form>
    @endif
</article>
