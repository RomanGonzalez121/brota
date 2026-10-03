<x-layouts.customer title="Hojitas Verdes">
    <x-slot:hero>
        <div class="relative overflow-hidden pt-3 pb-8 lg:pt-8 lg:pb-12">
            <div class="pointer-events-none absolute right-2 bottom-6 lg:right-10 lg:bottom-10" data-parallax="14" aria-hidden="true"><div class="origin-bottom-left animate-sway"><x-leaf class="size-28 rotate-[10deg] text-sprout/30 lg:size-52" /></div></div>
            <h1 class="relative text-xl font-bold lg:text-2xl">Tus Hojitas Verdes</h1>
            <p class="relative mt-2 flex items-center gap-3 font-display text-7xl leading-none font-extrabold tabular-nums lg:text-8xl">
                <x-leaf class="size-14 origin-bottom-left animate-sprout text-sprout lg:size-20" />
                <span x-data="countUp({{ $balance }})" x-text="value">{{ $balance }}</span>
            </p>
            <p class="relative mt-3 max-w-md text-white/85">Las sumás cuando tu pedido hace menos kilómetros y menos basura.</p>
        </div>
    </x-slot:hero>

    <section class="bleed-sand -mt-5 py-10 lg:-mt-8 lg:py-16" aria-labelledby="rules-title">
        <h2 id="rules-title" class="text-2xl lg:text-4xl" data-reveal>Cómo se ganan</h2>
        <ul class="mt-4 grid gap-3 sm:grid-cols-2 lg:mt-6 lg:gap-6">
            <li class="flex gap-4 rounded-2xl border border-sage/20 bg-surface p-5">
                <span class="flex size-12 shrink-0 items-center justify-center rounded-leaf-sm bg-leaf/12 text-leaf"><x-icon name="pin" class="size-6" /></span>
                <div>
                    <p class="font-display text-lg font-extrabold">+{{ $nearbyLeaves }} por pedir cerca</p>
                    <p class="mt-0.5 text-muted">Cuando te entregan un pedido de un restaurante a {{ number_format($nearbyKm, 0) }} km o menos.</p>
                </div>
            </li>
            <li class="flex gap-4 rounded-2xl border border-sage/20 bg-surface p-5">
                <span class="flex size-12 shrink-0 items-center justify-center rounded-leaf-sm bg-leaf/12 text-leaf"><x-icon name="box" class="size-6" /></span>
                <div>
                    <p class="font-display text-lg font-extrabold">+{{ $containerLeaves }} por envases retornables</p>
                    <p class="mt-0.5 text-muted">Cuando elegís recibir el pedido en envases que después devolvés.</p>
                </div>
            </li>
        </ul>
    </section>

    <section class="mt-10 max-w-2xl lg:mt-16" aria-labelledby="history-title">
        <h2 id="history-title" class="text-2xl lg:text-4xl" data-reveal>Movimientos</h2>
        <x-card padding="p-0" class="mt-3">
            @forelse ($transactions as $transaction)
                <div class="flex items-center gap-3.5 border-b border-sage/15 px-4 py-3.5 last:border-b-0" data-reveal style="--i: {{ min($loop->index, 6) }}">
                    <x-leaf class="size-5 text-leaf" />
                    <div class="min-w-0 flex-1">
                        <p class="text-[15px] font-medium">{{ $transaction->description }}</p>
                        <p class="text-sm text-muted">
                            {{ $transaction->created_at->translatedFormat('j \d\e F') }}
                            @if ($transaction->order)
                                , <a href="{{ route('orders.show', $transaction->order) }}" class="font-medium text-leaf tabular-nums hover:underline">pedido {{ $transaction->order->code }}</a>
                            @endif
                        </p>
                    </div>
                    <span class="font-display text-lg font-extrabold text-leaf tabular-nums">+{{ $transaction->points }}</span>
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
