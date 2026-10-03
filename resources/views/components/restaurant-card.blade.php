@props(['restaurant', 'nearbyKm' => 3])

{{-- The photo is the card: no border or box, the text sits on the page. --}}
<a href="{{ route('restaurants.show', $restaurant) }}" {{ $attributes->class('group block rounded-media transition-transform duration-150 ease-out-strong active:scale-[0.98]') }}>
    <div class="relative">
        <x-cover :restaurant="$restaurant" class="photo-zoom aspect-[4/3] rounded-media" style="view-transition-name: cover-{{ $restaurant->id }}" />
        @if ($restaurant->distance_km <= $nearbyKm)
            <span class="absolute top-3 left-3 inline-flex items-center gap-1 rounded-leaf-sm bg-surface px-2.5 py-1 text-xs font-semibold">
                <x-leaf class="size-3.5 text-leaf" />
                Suma hojitas
            </span>
        @endif
    </div>

    <div class="mt-3 flex items-baseline justify-between gap-3">
        <h3 class="text-lg leading-snug transition-colors duration-150 group-hover:text-leaf">{{ $restaurant->name }}</h3>
        <span class="flex shrink-0 items-center gap-1 text-sm font-semibold tabular-nums">
            <x-icon name="star" class="size-3.5 text-leaf" />
            {{ $restaurant->rating > 0 ? number_format($restaurant->rating, 1, ',') : 'Nuevo' }}
        </span>
    </div>
    <p class="text-sm text-muted">{{ $restaurant->category->name }} en {{ $restaurant->neighborhood }}</p>

    <dl class="mt-2 flex flex-wrap gap-x-3.5 gap-y-1 text-sm tabular-nums">
        <div class="flex items-center gap-1.5">
            <dt><x-icon name="clock" class="size-4 text-muted" /><span class="sr-only">Demora</span></dt>
            <dd>{{ $restaurant->eta_minutes }} min</dd>
        </div>
        <div class="flex items-center gap-1.5">
            <dt><x-icon name="pin" class="size-4 text-muted" /><span class="sr-only">Distancia</span></dt>
            <dd>{{ number_format($restaurant->distance_km, 1, ',') }} km</dd>
        </div>
        <div class="flex items-center gap-1.5">
            <dt class="text-muted">Envío</dt>
            <dd>{{ money($restaurant->delivery_fee) }}</dd>
        </div>
    </dl>
</a>
