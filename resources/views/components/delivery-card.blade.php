@props(['order', 'map' => false])

@use('App\Enums\OrderStatus')

@php
    $onTheWay = $order->status === OrderStatus::OnTheWay;
    $units = $order->items->sum('quantity');
@endphp

{{-- A delivery as the courier reads it on the street: where to pick up, where to take it, and the one thing to press next. --}}
<article id="entrega-{{ $order->id }}" data-item {{ $attributes->class('overflow-hidden rounded-media border border-sage/15 bg-surface') }}>
    @if ($map)
        <a href="{{ route('courier.deliveries.show', $order) }}" class="block" tabindex="-1" aria-hidden="true">
            <x-route-map :order="$order" />
        </a>
    @endif

    <div class="p-4">
        <div class="flex items-center justify-between gap-3">
            <h3 class="flex min-w-0 items-baseline gap-1.5 font-sans text-sm font-normal tracking-normal text-muted">
                <a href="{{ route('courier.deliveries.show', $order) }}" class="shrink-0 font-display font-bold text-ink tabular-nums hover:text-leaf hover:underline">{{ $order->code }}</a>
                <span class="truncate">{{ $units === 1 ? '1 producto' : $units.' productos' }}</span>
            </h3>

            <x-elapsed :since="$order->updated_at" :late-after="$onTheWay ? 30 : 20" :prefix="$onTheWay ? 'En camino hace' : 'Esperando hace'" />
        </div>

        {{-- The two stops, joined by a dotted line. The one that comes next is the one in full colour. --}}
        <ol class="relative mt-3 space-y-3">
            <span class="absolute top-8 bottom-8 left-4 border-l-2 border-dotted border-sage/40" aria-hidden="true"></span>

            <li class="relative flex items-start gap-3">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-leaf-sm {{ $onTheWay ? 'bg-sage/12 text-muted' : 'bg-leaf text-on-leaf' }}" aria-hidden="true">
                    <x-icon :name="$onTheWay ? 'check' : 'store'" class="size-4" />
                </span>
                <div class="min-w-0 {{ $onTheWay ? 'text-muted' : '' }}">
                    <p class="font-display leading-tight font-bold">{{ $order->restaurant->name }}</p>
                    <p class="truncate text-sm text-muted">{{ $order->restaurant->address }}, {{ $order->restaurant->neighborhood }}</p>
                </div>
            </li>

            <li class="relative flex items-start gap-3">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-leaf-sm {{ $onTheWay ? 'bg-leaf text-on-leaf' : 'bg-sage/12 text-muted' }}" aria-hidden="true">
                    <x-icon name="home" class="size-4" />
                </span>
                <div class="min-w-0">
                    <p class="font-display leading-tight font-bold">{{ $order->customer->firstName() }}</p>
                    <p class="truncate text-sm text-muted">{{ $order->delivery_address }}</p>
                </div>
            </li>
        </ol>

        @if ($order->returnable_containers)
            <div class="mt-3">
                <x-badge tone="sprout"><x-icon name="box" class="size-3.5" />Envases retornables</x-badge>
            </div>
        @endif

        <div class="mt-4 flex items-center gap-2">
            <form method="POST" action="{{ route($onTheWay ? 'courier.deliveries.deliver' : 'courier.deliveries.pick-up', $order) }}" class="flex-1">
                @csrf
                <x-button block :variant="$onTheWay ? 'accent' : 'primary'">
                    <x-icon :name="$onTheWay ? 'check' : 'bike'" class="size-4" />
                    {{ $onTheWay ? 'Entregué el pedido' : 'Retiré el pedido' }}
                </x-button>
            </form>
            <x-button :href="route('courier.deliveries.show', $order)" variant="secondary">Ver</x-button>
        </div>
    </div>
</article>
