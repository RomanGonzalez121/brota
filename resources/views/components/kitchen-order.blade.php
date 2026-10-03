@props(['order'])

@use('App\Enums\OrderStatus')

{{-- An order as the restaurant sees it on its board: who, what, how much, and the one action that moves it on. --}}
<article id="pedido-{{ $order->id }}" data-item {{ $attributes->class('rounded-media border border-sage/20 bg-surface p-4') }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <h3 class="text-base tabular-nums">
                <a href="{{ route('restaurant.orders.show', $order) }}" class="hover:text-leaf hover:underline">{{ $order->code }}</a>
            </h3>
            <p class="flex gap-1.5 text-sm text-muted">
                <span class="truncate">{{ $order->customer->name }}</span>
                <span class="shrink-0">{{ $order->created_at->diffForHumans(short: true) }}</span>
            </p>
        </div>
        <span class="shrink-0 font-display font-extrabold tabular-nums">{{ money($order->total) }}</span>
    </div>

    <ul class="mt-3 space-y-1 text-[15px]">
        @foreach ($order->items as $item)
            <li><span class="font-semibold tabular-nums">{{ $item->quantity }} ×</span> {{ $item->name }}</li>
        @endforeach
    </ul>

    @if ($order->returnable_containers || $order->courier)
        <div class="mt-3 flex flex-wrap gap-1.5">
            @if ($order->returnable_containers)
                <x-badge tone="sprout"><x-icon name="box" class="size-3.5" />Envases retornables</x-badge>
            @endif
            @if ($order->courier)
                <x-badge tone="neutral"><x-icon name="bike" class="size-3.5" />{{ $order->courier->firstName() }}</x-badge>
            @endif
        </div>
    @endif

    <div class="mt-4 flex flex-wrap gap-2">
        @if ($order->status === OrderStatus::Received)
            <form method="POST" action="{{ route('restaurant.orders.accept', $order) }}" class="flex-1">
                @csrf
                <x-button block>Aceptar</x-button>
            </form>
            <x-button
                type="button"
                variant="danger"
                x-data
                @click="$dispatch('open-sheet', { name: 'reject', action: '{{ route('restaurant.orders.reject', $order) }}', code: '{{ $order->code }}' })"
            >Rechazar</x-button>
        @elseif ($order->status->isOpen())
            <form method="POST" action="{{ route('restaurant.orders.advance', $order) }}" class="flex-1">
                @csrf
                <x-button block variant="secondary">
                    <x-icon :name="$order->status === OrderStatus::Preparing ? 'bike' : 'check'" class="size-4" />
                    {{ $order->status === OrderStatus::Preparing ? 'Salió a reparto' : 'Marcar entregado' }}
                </x-button>
            </form>
        @endif
    </div>
</article>
