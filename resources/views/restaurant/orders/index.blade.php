@php
    $columns = [
        ['title' => 'Nuevos', 'orders' => $received, 'empty' => 'No hay pedidos esperando respuesta.', 'tone' => 'bg-carrot'],
        ['title' => 'En preparación', 'orders' => $preparing, 'empty' => 'Nada en la cocina por ahora.', 'tone' => 'bg-sprout'],
        ['title' => 'En camino', 'orders' => $onTheWay, 'empty' => 'Ningún pedido en la calle.', 'tone' => 'bg-leaf'],
    ];
@endphp

<x-layouts.panel title="Pedidos" :subtitle="$restaurant->name.': aceptá los nuevos y avanzalos a medida que salen.'">
    {{--
        "Real time" for the mockup: every few seconds the screen asks how many orders
        are waiting, and offers to refresh when one came in.
    --}}
    <div
        x-data="{ fresh: false }"
        x-init="setInterval(async () => {
            const response = await fetch('{{ route('restaurant.orders.pending') }}', { headers: { Accept: 'application/json' } });
            if (response.ok && (await response.json()).count > {{ $received->count() }}) fresh = true;
        }, 8000)"
    >
        <a
            x-show="fresh"
            x-cloak
            x-transition:enter="transition-[opacity,transform] duration-300 ease-out-strong"
            x-transition:enter-start="-translate-y-2 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            href="{{ route('restaurant.orders.index') }}"
            class="mb-5 flex items-center gap-3 rounded-media bg-carrot px-4 py-3 font-display font-bold text-forest transition-transform duration-150 ease-out-strong active:scale-[0.98]"
        >
            <x-icon name="bell" />
            Entró un pedido nuevo. Tocá para verlo.
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3 lg:gap-5">
        @foreach ($columns as $column)
            <section aria-labelledby="column-{{ $loop->index }}">
                <h2 id="column-{{ $loop->index }}" class="flex items-center gap-2 text-lg">
                    <span class="size-2.5 rounded-full {{ $column['tone'] }}" aria-hidden="true"></span>
                    {{ $column['title'] }}
                    <span class="font-sans text-sm font-medium text-muted tabular-nums">{{ $column['orders']->count() }}</span>
                </h2>

                <div class="mt-3 space-y-3">
                    @forelse ($column['orders'] as $order)
                        <x-kitchen-order :order="$order" data-reveal style="--i: {{ min($loop->index, 4) }}" />
                    @empty
                        <p class="rounded-media border border-dashed border-sage/30 px-4 py-6 text-center text-sm text-muted">{{ $column['empty'] }}</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>

    <section class="mt-10" aria-labelledby="finished-title">
        <h2 id="finished-title" class="text-lg">Últimos finalizados</h2>
        <x-card padding="p-0" class="mt-3 overflow-hidden">
            @forelse ($finished as $order)
                <a href="{{ route('restaurant.orders.show', $order) }}" class="flex items-center gap-4 border-b border-sage/15 px-4 py-3 transition-colors duration-150 last:border-b-0 hover:bg-page">
                    <span class="w-20 shrink-0 font-display font-bold tabular-nums">{{ $order->code }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-[15px]">{{ $order->itemsSummary() }}</span>
                        <span class="block text-sm text-muted">{{ $order->customer->name }}, {{ $order->updated_at->translatedFormat('j \d\e F, H:i') }} h</span>
                    </span>
                    <x-badge :tone="$order->status->tone()">{{ $order->status->label() }}</x-badge>
                    <span class="hidden w-24 shrink-0 text-right font-display font-bold tabular-nums sm:block">{{ money($order->total) }}</span>
                </a>
            @empty
                <x-empty-state title="Todavía no cerraste ningún pedido">
                    Acá vas a ver los entregados y los rechazados.
                </x-empty-state>
            @endforelse
        </x-card>
    </section>

    <x-reject-sheet />
</x-layouts.panel>
