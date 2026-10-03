@php
    // How far the customer is between the level they stand on and the next one.
    $progress = $nextLevel
        ? (int) round(($balance - $level['min']) / max($nextLevel['min'] - $level['min'], 1) * 100)
        : 100;

    $perksLeft = array_filter([
        $remaining['free_delivery'] > 0 ? ($remaining['free_delivery'] === 1 ? '1 envío gratis' : $remaining['free_delivery'].' envíos gratis') : null,
        $remaining['free_order'] > 0 ? ($remaining['free_order'] === 1 ? '1 pedido gratis' : $remaining['free_order'].' pedidos gratis') : null,
    ]);
    $hasPerks = $level['free_deliveries'] + $level['free_orders'] > 0;

    // Reaching a level is celebrated once: the first time the screen is opened on it.
    $celebrates = $level['min'] > 0;
@endphp

<x-layouts.customer title="Hojitas Verdes">
    @if ($celebrates)
        <x-leaf-fall :once="'leaf-fall-level-'.$level['key']" />
    @endif

    <x-slot:hero>
        <div class="relative overflow-hidden pt-3 pb-8 lg:pt-8 lg:pb-12">
            {{-- Whole and clear of the text: beside the number on a phone, in the free corner on a wide screen. --}}
            <div class="pointer-events-none absolute top-5 right-2 lg:top-auto lg:right-10 lg:bottom-10" data-parallax="14" aria-hidden="true"><div class="origin-bottom-left animate-sway"><x-leaf class="size-24 rotate-[10deg] text-foliage/30 lg:size-52" /></div></div>
            <h1 class="relative text-xl font-bold lg:text-2xl">Tus Hojitas Verdes</h1>
            <p class="relative mt-2 flex items-center gap-3 font-display text-7xl leading-none font-extrabold tabular-nums lg:text-8xl">
                <x-leaf class="size-14 origin-bottom-left animate-sprout text-sprout lg:size-20" />
                <span x-data="countUp({{ $balance }})" x-text="value">{{ $balance }}</span>
            </p>
            {{-- On that first time the level's name also arrives with a small pop, once the number has finished counting. --}}
            <p
                class="relative mt-4 inline-flex items-center gap-2 rounded-leaf-sm bg-white/12 py-1.5 pr-3.5 pl-2.5 font-display font-bold"
                @if ($celebrates)
                    x-data="leafFall(@js('level-name-'.$level['key']))"
                    :class="playing && 'animate-pop [animation-delay:1100ms]'"
                @endif
            >
                <x-icon :name="$level['icon']" class="size-5 text-sprout" />
                Nivel {{ $level['name'] }}
            </p>
            <p class="relative mt-3 max-w-md text-white/85">Las sumás cuando tu pedido hace menos kilómetros y menos basura. No se gastan: con ellas subís de nivel.</p>
        </div>
    </x-slot:hero>

    <section class="bleed-sand -mt-5 py-10 lg:-mt-8 lg:py-16" aria-labelledby="level-title">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)] lg:items-center lg:gap-10" data-reveal>
            <div>
                <h2 id="level-title" class="text-xl lg:text-2xl">
                    @if ($nextLevel)
                        Te faltan <span class="tabular-nums">{{ $nextLevel['min'] - $balance }}</span> hojitas para llegar a {{ $nextLevel['name'] }}
                    @else
                        Llegaste a lo más alto: sos {{ $level['name'] }}
                    @endif
                </h2>

                {{-- The stretch between two levels, with a leaf standing where the customer is. --}}
                <div class="mt-5 flex items-center gap-3" role="img" aria-label="{{ $nextLevel ? $balance.' de '.$nextLevel['min'].' hojitas para '.$nextLevel['name'] : 'Nivel más alto alcanzado' }}">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-leaf-sm bg-leaf text-on-leaf"><x-icon :name="$level['icon']" /></span>
                    <div class="relative mx-2 h-2.5 flex-1 rounded-full bg-sage/20">
                        <div class="chart-fill h-full rounded-full bg-leaf" style="width: {{ max($progress, 4) }}%"></div>
                        {{-- A leaf rides the tip of the bar from the level's icon to where the customer is. --}}
                        <div class="progress-leaf absolute inset-0" style="--to: {{ max($progress, 4) }}%">
                            <span class="absolute top-1/2 left-0 flex size-7 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-surface ring-2 ring-leaf">
                                <x-leaf class="size-4 text-leaf" />
                            </span>
                        </div>
                    </div>
                    @if ($nextLevel)
                        {{-- The next level stirs when the leaf stops: that is where it is headed. --}}
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-leaf-sm border border-dashed border-sage/50 text-muted"><x-icon :name="$nextLevel['icon']" class="rustle-after origin-bottom" /></span>
                    @endif
                </div>
                @if ($nextLevel)
                    <p class="mt-2 flex justify-between text-sm text-muted tabular-nums">
                        <span>{{ $level['name'] }}, {{ $level['min'] }}</span>
                        <span>{{ $nextLevel['name'] }}, {{ $nextLevel['min'] }}</span>
                    </p>
                @endif
            </div>

            <div class="rounded-leaf bg-surface p-5">
                <p class="text-sm font-semibold text-muted">Este mes te quedan</p>
                @if ($perksLeft)
                    <ul class="mt-2 flex flex-wrap gap-2">
                        @foreach ($perksLeft as $perk)
                            <li class="group/perk inline-flex items-center gap-2 rounded-leaf-sm bg-sprout-soft py-2 pr-3.5 pl-3 font-display text-lg font-extrabold">
                                <span class="origin-bottom-left animate-sprout" style="animation-delay: {{ 300 + $loop->index * 120 }}ms">
                                    <x-leaf class="size-5 origin-bottom-left text-leaf group-hover/perk:animate-rustle" />
                                </span>
                                {{ $perk }}
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-3 text-[15px]">Los elegís al confirmar un pedido. Se renuevan el primer día de cada mes.</p>
                    <x-button :href="route('restaurants.index')" size="sm" class="mt-4 h-10">Pedir ahora</x-button>
                @elseif ($hasPerks)
                    <p class="mt-1 font-display text-lg font-extrabold">Ya usaste los de este mes</p>
                    <p class="mt-1 text-[15px]">Vuelven el primer día del mes que viene.</p>
                @else
                    <p class="mt-1 font-display text-lg font-extrabold">Todavía ninguno</p>
                    <p class="mt-1 text-[15px]">
                        @if ($nextLevel)
                            Con {{ $nextLevel['min'] }} hojitas llegás a {{ $nextLevel['name'] }} y empezás a tener envíos gratis.
                        @else
                            Tu nivel no tiene beneficios cargados por ahora.
                        @endif
                    </p>
                @endif
            </div>
        </div>

        <h2 class="mt-10 text-xl lg:mt-14 lg:text-2xl" data-reveal>Los niveles</h2>
        <x-level-ladder :levels="$levels" :current="$level['key']" :free-order-cap="$freeOrderCap" class="mt-4 lg:mt-6" />
    </section>

    <section class="mt-10 lg:mt-16" aria-labelledby="rules-title">
        <h2 id="rules-title" class="text-xl lg:text-2xl" data-reveal>Cómo se ganan</h2>
        <ul class="mt-4 grid gap-3 sm:grid-cols-2 lg:mt-6 lg:gap-6">
            <li class="lift flex gap-4 rounded-2xl border border-sage/20 bg-surface p-5">
                <span class="flex size-12 shrink-0 items-center justify-center rounded-leaf-sm bg-leaf/12 text-leaf"><x-icon name="pin" class="lift-icon size-6 origin-bottom" /></span>
                <div>
                    <p class="font-display text-lg font-extrabold">+{{ $nearbyLeaves }} por pedir cerca</p>
                    <p class="mt-0.5 text-muted">Cuando te entregan un pedido de un restaurante a {{ number_format($nearbyKm, 0) }} km o menos.</p>
                </div>
            </li>
            <li class="lift flex gap-4 rounded-2xl border border-sage/20 bg-surface p-5">
                <span class="flex size-12 shrink-0 items-center justify-center rounded-leaf-sm bg-leaf/12 text-leaf"><x-icon name="box" class="lift-icon size-6 origin-bottom" /></span>
                <div>
                    <p class="font-display text-lg font-extrabold">+{{ $containerLeaves }} por envases retornables</p>
                    <p class="mt-0.5 text-muted">Cuando elegís recibir el pedido en envases que después devolvés.</p>
                </div>
            </li>
        </ul>
    </section>

    <section class="mt-10 max-w-2xl lg:mt-16" aria-labelledby="history-title">
        <h2 id="history-title" class="text-xl lg:text-2xl" data-reveal>Movimientos</h2>
        <x-card padding="p-0" class="mt-3" style="--grow-step: 55ms; --grow-wait: 280ms">
            @forelse ($transactions as $transaction)
                <div class="flex items-center gap-3.5 border-b border-sage/15 px-4 py-3.5 last:border-b-0" data-reveal style="--i: {{ min($loop->index, 6) }}">
                    <span class="grow-in shrink-0"><x-leaf class="size-5 text-leaf" /></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[15px] font-medium">{{ $transaction->description }}</p>
                        <p class="text-sm text-muted">
                            {{ $transaction->created_at->translatedFormat('j \d\e F') }}
                            @if ($transaction->order)
                                , <a href="{{ route('orders.show', $transaction->order) }}" class="font-medium text-leaf tabular-nums hover:underline">pedido {{ $transaction->order->code }}</a>
                            @endif
                        </p>
                    </div>
                    <span class="rise-in font-display text-lg font-extrabold text-leaf tabular-nums">+{{ $transaction->points }}</span>
                </div>
            @empty
                <x-empty-state title="Todavía no sumaste hojitas">
                    Pedí a un restaurante cercano o elegí envases retornables para empezar.
                    <x-slot:action>
                        <x-button :href="route('restaurants.index', ['cerca' => 1])">Ver restaurantes cercanos</x-button>
                    </x-slot:action>
                </x-empty-state>
            @endforelse
        </x-card>
    </section>
</x-layouts.customer>
