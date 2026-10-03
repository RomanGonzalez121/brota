@php
    // Every filter link keeps the other filters that are already on.
    $filterUrl = fn (array $changes) => route('restaurants.index', array_filter([
        'q' => $search,
        'categoria' => $category,
        'cerca' => $onlyNearby ? 1 : null,
        ...$changes,
    ]));

    $chip = 'flex h-11 shrink-0 items-center gap-1.5 rounded-full border px-4 text-[15px] font-medium transition-[transform,border-color,background-color,color] duration-150 ease-out-strong active:scale-[0.97]';
    $chipOn = 'border-leaf bg-leaf text-on-leaf';
    $chipOff = 'border-sage/25 bg-surface hover:border-leaf hover:text-leaf';
@endphp

<x-layouts.customer title="Restaurantes">
    <h1 class="text-4xl leading-[1.05] lg:text-6xl lg:leading-none">Restaurantes</h1>

    <form action="{{ route('restaurants.index') }}" role="search" class="relative mt-5 max-w-xl lg:mt-8">
        @if ($category)
            <input type="hidden" name="categoria" value="{{ $category }}">
        @endif
        @if ($onlyNearby)
            <input type="hidden" name="cerca" value="1">
        @endif
        <label for="search" class="sr-only">Buscar un restaurante o un plato</label>
        <x-icon name="search" class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-muted" />
        <input
            id="search"
            name="q"
            type="search"
            value="{{ $search }}"
            placeholder="Buscá un restaurante o un plato"
            class="h-13 w-full rounded-full border border-sage/30 bg-surface pr-28 pl-12 text-base placeholder:text-muted/70 transition-[border-color,box-shadow] duration-150 focus:border-leaf focus:ring-3 focus:ring-leaf/15 focus:outline-none"
        >
        <span class="absolute top-1/2 right-1.5 -translate-y-1/2"><x-button size="sm" class="h-10 px-4">Buscar</x-button></span>
    </form>

    <nav class="-mx-4 mt-4 flex gap-2 overflow-x-auto overflow-y-hidden px-4 pb-1 contain-paint lg:mx-0 lg:flex-wrap lg:px-0" aria-label="Filtros">
        <a href="{{ $filterUrl(['cerca' => $onlyNearby ? null : 1]) }}" @if ($onlyNearby) aria-current="true" @endif class="{{ $chip }} {{ $onlyNearby ? $chipOn : $chipOff }}">
            <x-icon name="pin" class="size-4" />
            Hasta {{ number_format($nearbyKm, 0) }} km
        </a>
        <a href="{{ $filterUrl(['categoria' => null]) }}" @if (! $category) aria-current="true" @endif class="{{ $chip }} {{ $category ? $chipOff : $chipOn }}">Todas</a>
        @foreach ($categories as $item)
            <a href="{{ $filterUrl(['categoria' => $item->slug]) }}" @if ($category === $item->slug) aria-current="true" @endif class="{{ $chip }} {{ $category === $item->slug ? $chipOn : $chipOff }}">
                <x-icon :name="$item->icon()" class="size-4.5" />
                {{ $item->name }}
            </a>
        @endforeach
    </nav>

    <p class="mt-5 text-sm text-muted" role="status">
        {{ $restaurants->count() === 1 ? '1 restaurante' : $restaurants->count().' restaurantes' }}
        @if ($search !== '')
            para «{{ $search }}»
        @endif
        , ordenados por cercanía.
    </p>

    @if ($restaurants->isEmpty())
        <x-card class="mt-4">
            <x-empty-state title="No encontramos restaurantes con esos filtros">
                Probá con otra palabra o quitá algún filtro.
                <x-slot:action>
                    <x-button :href="route('restaurants.index')" variant="secondary">Quitar filtros</x-button>
                </x-slot:action>
            </x-empty-state>
        </x-card>
    @else
        <ul class="mt-5 grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 lg:gap-y-12">
            @foreach ($restaurants as $restaurant)
                <li data-reveal style="--i: {{ $loop->index % 3 }}"><x-restaurant-card :restaurant="$restaurant" :nearby-km="$nearbyKm" /></li>
            @endforeach
        </ul>
    @endif
</x-layouts.customer>
