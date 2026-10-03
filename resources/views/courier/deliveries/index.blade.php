@php
    $moving = $onTheWay->count();
    $pending = $toPickUp->count();

    $summary = match (true) {
        $moving === 0 && $pending === 0 => 'No tenés entregas asignadas ahora.',
        $pending === 0 => $moving === 1 ? 'Tenés 1 pedido en camino.' : "Tenés {$moving} pedidos en camino.",
        $moving === 0 => $pending === 1 ? 'Tenés 1 pedido para retirar.' : "Tenés {$pending} pedidos para retirar.",
        default => 'Tenés '.($moving === 1 ? '1 pedido' : "{$moving} pedidos").' en camino y '.$pending.' para retirar.',
    };

    // The map goes on the delivery that comes first: the one already on the street, or the next pick-up.
    $featured = $onTheWay->first() ?? $toPickUp->first();
@endphp

<x-layouts.courier title="Entregas">
    {{--
        "Real time" for the mockup: every few seconds the screen asks whether anything
        changed and, if so, updates itself in place. A new delivery appears on its own.
    --}}
    <div
        x-data="orderBoard('{{ route('courier.deliveries.pending') }}', 'Tenés una entrega nueva.')"
        data-stamp="{{ $stamp }}"
        data-waiting="{{ $moving + $pending }}"
    >
        <section class="relative rounded-leaf bg-deep p-5 text-cream" aria-labelledby="greeting">
            <div class="pointer-events-none absolute top-4 right-4" aria-hidden="true">
                <div class="flex items-end gap-1">
                    <div class="origin-bottom animate-sway [animation-delay:-3s]"><x-leaf class="size-7 -rotate-[58deg] text-foliage-light/45" /></div>
                    <div class="origin-bottom-left animate-sway"><x-leaf class="size-16 rotate-[12deg] text-foliage/40" /></div>
                </div>
            </div>

            <h1 id="greeting" class="relative pr-28 text-2xl text-white">Hola, {{ $courier->firstName() }}</h1>
            <p class="relative mt-1 pr-28 text-cream/85">{{ $summary }}</p>

            <dl class="relative mt-5 grid grid-cols-2 gap-3">
                <div class="rounded-2xl bg-white/8 px-4 py-3">
                    <dt class="text-sm text-cream/75">Entregas de hoy</dt>
                    <dd data-pop class="font-display text-2xl font-extrabold tabular-nums">{{ $deliveredToday }}</dd>
                </div>
                <div class="rounded-2xl bg-white/8 px-4 py-3">
                    <dt class="text-sm text-cream/75">Ganaste hoy</dt>
                    <dd data-pop class="font-display text-2xl font-extrabold tabular-nums">{{ money($earnedToday) }}</dd>
                </div>
            </dl>
        </section>

        @if ($moving === 0 && $pending === 0)
            <x-card class="mt-5">
                <x-empty-state title="Por ahora, a descansar">
                    Cuando un restaurante acepte un pedido y te lo asignemos, lo vas a ver acá con su ruta.
                </x-empty-state>
            </x-card>
        @endif

        @foreach ([
            ['title' => 'En camino', 'icon' => 'bike', 'chip' => 'bg-carrot/25 text-ink', 'orders' => $onTheWay],
            ['title' => 'Para retirar', 'icon' => 'store', 'chip' => 'bg-sprout/30 text-ink', 'orders' => $toPickUp],
        ] as $group)
            <section @class(['mt-7', 'hidden' => $group['orders']->isEmpty()]) aria-labelledby="group-{{ $loop->index }}">
                <h2 id="group-{{ $loop->index }}" class="flex items-center gap-2 text-lg">
                    <span class="flex size-7 shrink-0 items-center justify-center rounded-leaf-sm {{ $group['chip'] }}" aria-hidden="true">
                        <x-icon :name="$group['icon']" class="size-4" />
                    </span>
                    {{ $group['title'] }}
                    <span data-pop class="font-sans text-sm font-medium text-muted tabular-nums">{{ $group['orders']->count() }}</span>
                </h2>

                <div class="mt-3 space-y-3">
                    @foreach ($group['orders'] as $order)
                        <x-delivery-card :order="$order" :map="$order->is($featured)" data-reveal style="--i: {{ min($loop->index, 3) }}" />
                    @endforeach
                </div>
            </section>
        @endforeach

        @if ($latest->isNotEmpty())
            <section class="mt-9" aria-labelledby="latest-title">
                <h2 id="latest-title" class="flex items-center gap-2 text-lg">
                    <span class="flex size-7 shrink-0 items-center justify-center rounded-leaf-sm bg-leaf/15 text-leaf" aria-hidden="true">
                        <x-icon name="check" class="size-4" />
                    </span>
                    Últimas entregas
                </h2>

                <x-card padding="p-0" class="mt-3 overflow-hidden">
                    @foreach ($latest as $order)
                        <a
                            id="entregada-{{ $order->id }}"
                            data-item
                            href="{{ route('courier.deliveries.show', $order) }}"
                            class="flex items-center gap-3 border-b border-sage/15 px-4 py-3 transition-colors duration-150 last:border-b-0 hover:bg-page"
                        >
                            <span class="min-w-0 flex-1">
                                <span class="block truncate font-display font-bold">{{ $order->restaurant->name }}</span>
                                <span class="block truncate text-sm text-muted">
                                    <span class="tabular-nums">{{ $order->code }}</span>, a {{ $order->customer->firstName() }},
                                    {{ $order->delivered_at->isToday() ? 'hoy '.$order->delivered_at->format('H:i').' h' : $order->delivered_at->translatedFormat('j \d\e F') }}
                                </span>
                            </span>
                            <span class="shrink-0 font-display font-bold tabular-nums">{{ money($order->delivery_fee) }}</span>
                            <x-icon name="chevron-right" class="size-4 text-muted" />
                        </a>
                    @endforeach
                </x-card>
            </section>
        @endif
    </div>
</x-layouts.courier>
