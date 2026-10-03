@php
    $monthName = $month->translatedFormat('F');
    $previousName = $previous->translatedFormat('F');
    $peak = max($bestDay['total'], 1);
    $mostUnits = max($topDishes->max('units') ?? 1, 1);

    // What the chart says about a day when it is pointed at.
    $readouts = $days->map(fn (array $day) => $day['day'].' de '.$monthName.': '.match (true) {
        $day['future'] => 'todavía no llegó',
        $day['total'] === 0 => 'sin ventas',
        default => money($day['total']).' en '.($day['orders'] === 1 ? '1 pedido' : $day['orders'].' pedidos'),
    })->values();

    $chip = 'flex h-10 items-center rounded-full border px-4 text-[15px] font-medium capitalize transition-[transform,border-color,background-color,color] duration-150 ease-out-strong active:scale-[0.97]';
@endphp

<x-layouts.panel title="Reportes" :subtitle="'Cómo le fue a '.$restaurant->name.' en '.$monthName.'.'">
    <x-slot:actions>
        <nav class="flex flex-wrap gap-2" aria-label="Mes">
            @foreach ($months->reverse() as $option)
                @php $selected = $option->isSameMonth($month); @endphp
                <a
                    href="{{ route('restaurant.reports.index', ['mes' => $option->format('Y-m')]) }}"
                    @if ($selected) aria-current="true" @endif
                    class="{{ $chip }} {{ $selected ? 'border-leaf bg-leaf text-on-leaf' : 'border-sage/25 bg-surface hover:border-leaf hover:text-leaf' }}"
                >{{ $option->translatedFormat('F') }}</a>
            @endforeach
        </nav>
    </x-slot:actions>

    @if ($sales->isEmpty())
        <x-card>
            <x-empty-state :title="'Todavía no hay ventas en '.$monthName">
                Cuando entregues el primer pedido del mes, acá vas a ver cuánto ganaste y qué platos salieron más.
            </x-empty-state>
        </x-card>
    @else
        {{-- The month in four numbers. Only the earnings are loud; the rest support them. --}}
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-[minmax(0,1.5fr)_repeat(3,minmax(0,1fr))] lg:gap-4">
            <section class="col-span-2 flex flex-col justify-between rounded-leaf bg-deep p-5 text-cream lg:col-span-1 lg:p-6" aria-labelledby="earnings-title" data-reveal>
                <h2 id="earnings-title" class="font-sans text-sm font-semibold tracking-normal text-cream/75">Ganancias de {{ $monthName }}</h2>
                <p class="mt-2 font-display text-4xl leading-none font-extrabold tabular-nums lg:text-5xl">
                    <span x-data="countUp({{ $earnings }})" x-text="'$ ' + value.toLocaleString('es-AR')">{{ money($earnings) }}</span>
                </p>
                <p class="mt-4 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-cream/80">
                    @if ($change !== null)
                        <span class="rounded-full px-2.5 py-0.5 font-semibold tabular-nums {{ $change >= 0 ? 'bg-sprout text-forest' : 'bg-cream/15 text-cream' }}">{{ $change >= 0 ? '+' : '−' }}{{ abs($change) }} %</span>
                        {{ $isCurrent ? 'frente a los mismos días de '.$previousName : 'frente a '.$previousName }}
                    @else
                        Sin el costo de envío, que es del repartidor.
                    @endif
                </p>
            </section>

            <x-card data-reveal style="--i: 1">
                <span class="flex size-9 items-center justify-center rounded-leaf-sm bg-sprout-soft text-leaf" aria-hidden="true"><x-icon name="receipt" class="size-5" /></span>
                <p class="mt-3 text-sm text-muted">Pedidos entregados</p>
                <p class="font-display text-3xl font-extrabold tabular-nums">{{ $sales->count() }}</p>
                <p class="mt-1 text-sm text-muted tabular-nums">{{ $rejected === 0 ? 'Ninguno rechazado' : ($rejected === 1 ? '1 rechazado' : $rejected.' rechazados') }}</p>
            </x-card>

            <x-card data-reveal style="--i: 2">
                <span class="flex size-9 items-center justify-center rounded-leaf-sm bg-sprout-soft text-leaf" aria-hidden="true"><x-icon name="wallet" class="size-5" /></span>
                <p class="mt-3 text-sm text-muted">Ticket promedio</p>
                <p class="font-display text-3xl font-extrabold tabular-nums">{{ money($averageTicket) }}</p>
                <p class="mt-1 text-sm text-muted">Por pedido, sin envío</p>
            </x-card>

            <x-card class="col-span-2 lg:col-span-1" data-reveal style="--i: 3">
                <span class="flex size-9 items-center justify-center rounded-leaf-sm bg-sprout-soft text-leaf" aria-hidden="true"><x-icon name="box" class="size-5" /></span>
                <p class="mt-3 text-sm text-muted">Con envases retornables</p>
                <p class="font-display text-3xl font-extrabold tabular-nums">{{ $returnableShare }} %</p>
                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-sage/15" aria-hidden="true">
                    <div class="chart-fill h-full rounded-full bg-sprout" style="width: {{ $returnableShare }}%"></div>
                </div>
            </x-card>
        </div>

        {{-- Sales day by day. The best day is the only bar in the accent colour. --}}
        {{-- Pointing at a bar (or tapping it) reads that day out in the corner, where the best day normally is. --}}
        <x-card as="section" class="mt-4 lg:mt-5" aria-labelledby="daily-title" data-reveal x-data="{ shown: null, days: {{ Js::from($readouts) }} }">
            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                <h2 id="daily-title" class="text-lg">Ventas por día</h2>
                <p class="text-sm text-muted">
                    <span x-show="shown === null">Mejor día: <span class="font-semibold text-ink tabular-nums">{{ $bestDay['day'] }} de {{ $monthName }}, {{ money($bestDay['total']) }}</span></span>
                    <span x-show="shown !== null" x-cloak x-text="days[shown]" class="font-semibold text-ink tabular-nums"></span>
                </p>
            </div>

            <div class="relative mt-5">
                <div class="pointer-events-none absolute inset-x-0 top-0 border-t border-dashed border-sage/25" aria-hidden="true"></div>
                <div class="pointer-events-none absolute inset-x-0 top-1/2 border-t border-dashed border-sage/15" aria-hidden="true"></div>

                <ol class="relative flex h-44 items-end gap-[3px] border-b border-sage/25 sm:gap-1.5 lg:h-52" aria-label="Ventas de cada día de {{ $monthName }}">
                    @foreach ($days as $day)
                        @php
                            $isBest = $day['total'] > 0 && $day['day'] === $bestDay['day'];
                            $height = $day['total'] > 0 ? max($day['total'] / $peak * 100, 3) : 0;
                        @endphp
                        <li
                            class="group relative flex h-full flex-1 items-end rounded-t-md outline-none focus-visible:bg-sage/10"
                            tabindex="0"
                            aria-label="{{ $readouts[$loop->index] }}"
                            @mouseenter="shown = {{ $loop->index }}"
                            @mouseleave="shown = null"
                            @focus="shown = {{ $loop->index }}"
                            @blur="shown = null"
                        >
                            @if ($day['total'] > 0)
                                <span
                                    class="chart-bar block w-full rounded-[0.45rem_0.15rem_0_0] transition-colors duration-150 {{ $isBest ? 'bg-carrot' : 'bg-leaf group-hover:bg-leaf-deep group-focus-visible:bg-leaf-deep' }}"
                                    style="height: {{ round($height, 1) }}%; --i: {{ $loop->index }}"
                                ></span>
                            @elseif (! $day['future'])
                                <span class="block h-0.5 w-full rounded-full bg-sage/30"></span>
                            @endif
                        </li>
                    @endforeach
                </ol>

                <div class="mt-1.5 flex gap-[3px] text-[11px] text-muted tabular-nums sm:gap-1.5" aria-hidden="true">
                    @foreach ($days as $day)
                        <span class="flex-1 text-center">{{ $day['day'] === 1 || $day['day'] % 5 === 0 ? $day['day'] : '' }}</span>
                    @endforeach
                </div>
            </div>
        </x-card>

        <div class="mt-4 grid grid-cols-1 gap-4 lg:mt-5 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,1fr)] lg:items-start lg:gap-5">
            <x-card as="section" aria-labelledby="top-title" data-reveal>
                <h2 id="top-title" class="text-lg">Platos más pedidos</h2>

                <ol class="mt-4 space-y-4">
                    @foreach ($topDishes as $entry)
                        <li class="flex items-center gap-3">
                            <span class="w-5 shrink-0 text-center font-display text-lg font-extrabold tabular-nums {{ $loop->first ? 'text-leaf' : 'text-muted' }}">{{ $loop->iteration }}</span>

                            @if ($entry['dish'])
                                <x-dish-photo :dish="$entry['dish']" class="size-12 rounded-leaf-sm" />
                            @else
                                <span class="flex size-12 shrink-0 items-center justify-center rounded-leaf-sm bg-sprout-soft"><x-leaf class="size-6 text-leaf/50" /></span>
                            @endif

                            <div class="min-w-0 flex-1">
                                <div class="flex items-baseline justify-between gap-3">
                                    <p class="min-w-0 font-display leading-tight font-bold">{{ $entry['name'] }}</p>
                                    <p class="shrink-0 text-sm font-semibold tabular-nums">{{ $entry['units'] }} <span class="font-normal text-muted">{{ $entry['units'] === 1 ? 'unidad' : 'unidades' }}</span></p>
                                </div>
                                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-sage/15" aria-hidden="true">
                                    <div class="chart-fill h-full rounded-full {{ $loop->first ? 'bg-leaf' : 'bg-sprout' }}" style="width: {{ round($entry['units'] / $mostUnits * 100) }}%; --i: {{ $loop->index }}"></div>
                                </div>
                                <p class="mt-1 text-sm text-muted tabular-nums">{{ money($entry['revenue']) }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </x-card>

            <section aria-labelledby="latest-title" data-reveal style="--i: 1">
                <x-card padding="p-0" class="overflow-hidden">
                    <h2 id="latest-title" class="px-5 pt-5 pb-3 text-lg">Últimas ventas</h2>

                    @foreach ($sales->take(6) as $order)
                        <a href="{{ route('restaurant.orders.show', $order) }}" class="flex items-center gap-3 border-t border-sage/15 px-5 py-3 transition-colors duration-150 hover:bg-page">
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-[15px]">{{ $order->itemsSummary() }}</span>
                                <span class="block text-sm text-muted">
                                    <span class="font-semibold text-ink tabular-nums">{{ $order->code }}</span>,
                                    {{ $order->created_at->translatedFormat('j \d\e F, H:i') }} h
                                </span>
                            </span>
                            <span class="shrink-0 font-display font-bold tabular-nums">{{ money($order->subtotal) }}</span>
                        </a>
                    @endforeach
                </x-card>
            </section>
        </div>
    @endif
</x-layouts.panel>
