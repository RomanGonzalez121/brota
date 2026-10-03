@php
    // Only the lane that asks for an answer carries the accent; the other two stay quiet.
    $lanes = [
        ['title' => 'Nuevos', 'tab' => 'Nuevos', 'orders' => $received, 'empty' => 'No hay pedidos esperando respuesta.', 'dot' => 'bg-carrot', 'tint' => 'bg-carrot/12', 'urgent' => true],
        ['title' => 'En preparación', 'tab' => 'En cocina', 'orders' => $preparing, 'empty' => 'Nada en la cocina por ahora.', 'dot' => 'bg-sprout', 'tint' => 'bg-sage/10', 'urgent' => false],
        ['title' => 'En camino', 'tab' => 'En camino', 'orders' => $onTheWay, 'empty' => 'Ningún pedido en la calle.', 'dot' => 'bg-leaf', 'tint' => 'bg-sage/10', 'urgent' => false],
    ];

    $counter = fn (array $lane) => $lane['urgent'] && $lane['orders']->isNotEmpty()
        ? 'min-w-5.5 rounded-full bg-carrot px-1.5 text-center text-xs leading-5.5 font-bold text-forest'
        : 'text-sm font-medium text-muted';
@endphp

<x-layouts.panel title="Pedidos" :subtitle="$restaurant->name.': aceptá los nuevos y avanzalos a medida que salen.'">
    {{--
        "Real time" for the mockup: every few seconds the board asks whether anything
        changed and, if so, updates itself in place. A new order appears in its lane.
    --}}
    <div x-data="orderBoard('{{ route('restaurant.orders.pending') }}')" data-stamp="{{ $stamp }}" data-waiting="{{ $received->count() }}">
        {{-- On a phone the three lanes do not fit side by side: one at a time, chosen here. --}}
        <div class="relative mb-3 grid grid-cols-3 rounded-full bg-sage/12 p-1 lg:hidden" role="tablist" aria-label="Estado de los pedidos">
            <span
                class="absolute inset-y-1 left-1 w-[calc((100%-0.5rem)/3)] rounded-full bg-surface transition-transform duration-200 ease-out-strong"
                :style="{ transform: `translateX(${lane * 100}%)` }"
                aria-hidden="true"
            ></span>

            @foreach ($lanes as $lane)
                <button
                    type="button"
                    role="tab"
                    aria-controls="lane-{{ $loop->index }}"
                    aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                    :aria-selected="lane === {{ $loop->index }}"
                    @click="lane = {{ $loop->index }}"
                    class="relative z-10 flex h-10 items-center justify-center gap-1.5 rounded-full text-sm font-semibold tabular-nums transition-colors duration-150 {{ $loop->first ? 'text-ink' : 'text-muted' }}"
                    :class="{ 'text-ink': lane === {{ $loop->index }}, 'text-muted': lane !== {{ $loop->index }} }"
                >
                    {{ $lane['tab'] }}
                    <span data-pop class="{{ $counter($lane) }}">{{ $lane['orders']->count() }}</span>
                </button>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 lg:items-start">
            @foreach ($lanes as $lane)
                <section
                    id="lane-{{ $loop->index }}"
                    aria-labelledby="column-{{ $loop->index }}"
                    class="rounded-media p-2.5 lg:p-3 {{ $lane['tint'] }} {{ $loop->first ? '' : 'max-lg:hidden' }}"
                    :class="{ 'max-lg:hidden': lane !== {{ $loop->index }} }"
                >
                    <h2 id="column-{{ $loop->index }}" class="flex items-center gap-2 px-1.5 pt-1 pb-3 text-base max-lg:sr-only">
                        <span class="size-2.5 rounded-full {{ $lane['dot'] }}" aria-hidden="true"></span>
                        {{ $lane['title'] }}
                        <span data-pop class="font-sans tabular-nums {{ $counter($lane) }}">{{ $lane['orders']->count() }}</span>
                    </h2>

                    <div class="space-y-2.5">
                        @forelse ($lane['orders'] as $order)
                            <x-kitchen-order :order="$order" data-reveal style="--i: {{ min($loop->index, 4) }}" />
                        @empty
                            <p class="rounded-media border border-dashed border-sage/30 px-4 py-6 text-center text-sm text-muted">{{ $lane['empty'] }}</p>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
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
