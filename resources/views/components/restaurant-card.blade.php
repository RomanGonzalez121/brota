@props(['restaurant', 'nearbyKm' => 3])

<a href="{{ route('restaurants.show', $restaurant) }}" {{ $attributes->class('block overflow-hidden rounded-2xl border border-sage/20 bg-white transition-[transform,border-color] duration-150 ease-out-strong hover:border-leaf/60 active:scale-[0.98]') }}>
    <div class="relative">
        <x-cover :restaurant="$restaurant" class="h-28" />
        @if ($restaurant->distance_km <= $nearbyKm)
            <span class="absolute top-3 left-3 inline-flex items-center gap-1 rounded-leaf-sm bg-white px-2.5 py-1 text-xs font-semibold">
                <x-leaf class="size-3.5 text-leaf" />
                Suma hojitas
            </span>
        @endif
    </div>

    <div class="p-4">
        <div class="flex items-start justify-between gap-3">
            <h3 class="text-lg leading-snug">{{ $restaurant->name }}</h3>
            <span class="mt-0.5 flex shrink-0 items-center gap-1 text-sm font-semibold tabular-nums">
                <x-icon name="star" class="size-4 text-carrot" />
                {{ number_format($restaurant->rating, 1, ',') }}
            </span>
        </div>
        <p class="mt-0.5 text-sm text-sage">{{ $restaurant->category->name }} en {{ $restaurant->neighborhood }}</p>

        <dl class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-sm tabular-nums">
            <div class="flex items-center gap-1.5">
                <dt><x-icon name="clock" class="size-4 text-sage" /><span class="sr-only">Demora</span></dt>
                <dd>{{ $restaurant->eta_minutes }} min</dd>
            </div>
            <div class="flex items-center gap-1.5">
                <dt><x-icon name="pin" class="size-4 text-sage" /><span class="sr-only">Distancia</span></dt>
                <dd>{{ number_format($restaurant->distance_km, 1, ',') }} km</dd>
            </div>
            <div class="flex items-center gap-1.5">
                <dt class="text-sage">Envío</dt>
                <dd>{{ money($restaurant->delivery_fee) }}</dd>
            </div>
        </dl>
    </div>
</a>
