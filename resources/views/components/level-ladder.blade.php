@props(['levels', 'freeOrderCap', 'current' => null, 'counts' => null])

@php
    $currentIndex = $current ? array_search($current, array_column($levels, 'key'), true) : null;

    // How much of the vine is drawn: up to the middle of the customer's card, or all of it at the top level.
    $grown = $currentIndex === null ? 0 : ($currentIndex === count($levels) - 1 ? 1 : (12.5 + 25 * $currentIndex) / 100);
@endphp

{{--
    The four levels side by side, lowest first: a seed that ends up a tree. With
    `current` it is the customer's own ladder (reached, standing on, still
    ahead) and, on a wide screen, a vine grows over the cards up to their level,
    with a leaf on each one reached. Without it, a plain picture of the rules.
    `counts` adds how many customers stand on each level, for the administrator.
--}}
<div {{ $attributes->class('relative') }}>
    @if ($currentIndex !== null)
        {{-- The same vine twice: a faint one for all of it, and a green one on top that is uncovered from the left as far as it has grown. --}}
        <div class="pointer-events-none absolute inset-x-0 top-0 hidden h-7 lg:block" data-reveal aria-hidden="true">
            @foreach (['stroke-sage/30' => '', 'stroke-leaf' => 'vine-draw'] as $stroke => $draw)
                <svg class="absolute inset-0 size-full {{ $stroke }} {{ $draw }}" @if ($draw) style="--vine-rest: {{ round((1 - $grown) * 100, 2) }}%" @endif viewBox="0 0 100 28" preserveAspectRatio="none" fill="none">
                    <path d="M0 14 Q 6.25 4 12.5 14 T 25 14 T 37.5 14 T 50 14 T 62.5 14 T 75 14 T 87.5 14 T 100 14" stroke-width="{{ $draw ? 2.5 : 2 }}" stroke-linecap="round" vector-effect="non-scaling-stroke" />
                </svg>
            @endforeach
        </div>
    @endif

    <ol @class(['grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4', 'lg:pt-10' => $currentIndex !== null])>
        @foreach ($levels as $index => $level)
            @php
                $isCurrent = $currentIndex === $index;
                $ahead = $currentIndex !== null && $index > $currentIndex;

                $perks = array_filter([
                    $level['free_deliveries'] > 0 ? ($level['free_deliveries'] === 1 ? '1 envío gratis por mes' : $level['free_deliveries'].' envíos gratis por mes') : null,
                    $level['free_orders'] > 0 ? ($level['free_orders'] === 1 ? '1 pedido gratis por mes' : $level['free_orders'].' pedidos gratis por mes').', hasta '.money($freeOrderCap) : null,
                ]);
            @endphp
            <li
                @class([
                    'lift relative flex flex-col rounded-media border p-4',
                    'border-leaf bg-surface' => $isCurrent,
                    'border-sage/20 bg-surface' => ! $isCurrent && ! $ahead,
                    'border-dashed border-sage/30' => $ahead,
                ])
                @if ($isCurrent) aria-current="true" @endif
                data-reveal
                style="--i: {{ $index }}"
            >
                @if ($currentIndex !== null)
                    {{-- Where the card hangs from the vine: a leaf if the level was reached, a bud if it is still ahead. --}}
                    <span class="pointer-events-none absolute -top-9 left-1/2 hidden -translate-x-1/2 lg:block" aria-hidden="true">
                        @if ($ahead)
                            <span class="mt-1.5 block size-2.5 rounded-full border-2 border-sage/40 bg-sand"></span>
                        @else
                            <span class="grow-in block"><x-leaf class="size-5 -rotate-12 text-leaf" /></span>
                        @endif
                    </span>
                @endif

                <div class="flex items-center gap-3">
                    {{-- The icon chip grows a little with each level, like the plant it stands for, and they sprout one after another. --}}
                    <span
                        @class([
                            'grow-in relative flex shrink-0 items-center justify-center rounded-leaf-sm',
                            ['size-9', 'size-10', 'size-11', 'size-12'][$index],
                            'bg-leaf text-on-leaf' => $isCurrent,
                            'bg-sprout-soft text-leaf' => ! $isCurrent && ! $ahead,
                            'bg-sage/12 text-muted' => $ahead,
                        ])
                        aria-hidden="true"
                    >
                        @if ($isCurrent)
                            <span class="absolute inset-0 animate-breathe rounded-leaf-sm bg-leaf"></span>
                        @endif
                        <x-icon :name="$level['icon']" :class="'lift-icon relative origin-bottom '.['size-4.5', 'size-5', 'size-5.5', 'size-6'][$index]" />
                    </span>
                    <div class="min-w-0">
                        <p class="font-display text-lg leading-tight font-extrabold {{ $ahead ? 'text-muted' : '' }}">{{ $level['name'] }}</p>
                        <p class="text-sm text-muted tabular-nums">{{ $level['min'] === 0 ? 'Desde el primer día' : 'Desde '.$level['min'].' hojitas' }}</p>
                    </div>
                </div>

                <ul class="mt-3 flex-1 space-y-1.5 text-[15px] {{ $ahead ? 'text-muted' : '' }}">
                    @forelse ($perks as $perk)
                        <li class="flex items-start gap-2">
                            <x-leaf class="mt-1 size-3.5 {{ $ahead ? 'text-sage/60' : 'text-leaf' }}" />
                            <span>{{ $perk }}</span>
                        </li>
                    @empty
                        <li class="text-muted">{{ $level['min'] === 0 ? 'Acá empieza todo: cada pedido cerca o con envases retornables suma.' : 'Sin beneficios cargados.' }}</li>
                    @endforelse
                </ul>

                @if ($isCurrent)
                    <p class="mt-3"><x-badge tone="success" leaf>Tu nivel</x-badge></p>
                @endif

                @if ($counts !== null)
                    @php $count = $counts->get($level['key'], 0); @endphp
                    <p class="mt-3 border-t border-sage/15 pt-3 text-sm text-muted tabular-nums">
                        <span class="font-display text-lg font-extrabold text-ink">{{ $count }}</span>
                        {{ $count === 1 ? 'cliente' : 'clientes' }}
                    </p>
                @endif
            </li>
        @endforeach
    </ol>
</div>
