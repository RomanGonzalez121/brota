@php
    // Every filter link keeps the other filters that are already on.
    $filterUrl = fn (array $changes) => route('restaurants.index', array_filter([
        'q' => $search,
        'categoria' => $category,
        'cerca' => $onlyNearby ? 1 : null,
        ...$changes,
    ]));

    $chip = 'flex h-11 shrink-0 items-center gap-1.5 rounded-full border px-4 text-[15px] font-medium transition-[transform,border-color,background-color,color] duration-150 ease-out-strong active:scale-[0.97]';
    $chipOn = 'border-leaf bg-leaf text-white';
    $chipOff = 'border-sage/25 bg-white hover:border-leaf hover:text-leaf';
@endphp

<x-layouts.customer title="Restaurantes">
    <h1 class="text-2xl lg:text-3xl">Restaurantes</h1>

    <form action="{{ route('restaurants.index') }}" role="search" class="relative mt-4 max-w-xl">
        @if ($category)
            <input type="hidden" name="categoria" value="{{ $category }}">
        @endif
        @if ($onlyNearby)
            <input type="hidden" name="cerca" value="1">
        @endif
        <label for="search" class="sr-only">Buscar un restaurante o un plato</label>
        <x-icon name="search" class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-sage" />
        <input
            id="search"
            name="q"
            type="search"
            value="{{ $search }}"
            placeholder="Buscá un restaurante o un plato"
            class="h-12 w-full rounded-2xl border border-sage/30 bg-white pr-28 pl-12 text-base placeholder:text-sage/70 transition-[border-color,box-shadow] duration-150 focus:border-leaf focus:ring-3 focus:ring-leaf/15 focus:outline-none"
        >
        <x-button size="sm" class="absolute top-1/2 right-1.5 -translate-y-1/2">Buscar</x-button>
    </form>

    <nav class="-mx-4 mt-4 flex gap-2 overflow-x-auto px-4 pb-1 lg:mx-0 lg:flex-wrap lg:px-0" aria-label="Filtros">
        <a href="{{ $filterUrl(['cerca' => $onlyNearby ? null : 1]) }}" @if ($onlyNearby) aria-current="true" @endif class="{{ $chip }} {{ $onlyNearby ? $chipOn : $chipOff }}">
            <x-icon name="pin" class="size-4" />
            Hasta {{ number_format($nearbyKm, 0) }} km
        </a>
        <a href="{{ $filterUrl(['categoria' => null]) }}" @if (! $category) aria-current="true" @endif class="{{ $chip }} {{ $category ? $chipOff : $chipOn }}">Todas</a>
        @foreach ($categories as $item)
            <a href="{{ $filterUrl(['categoria' => $item->slug]) }}" @if ($category === $item->slug) aria-current="true" @endif class="{{ $chip }} {{ $category === $item->slug ? $chipOn : $chipOff }}">
                {{ $item->name }}
            </a>
        @endforeach
    </nav>

    <p class="mt-5 text-sm text-sage" role="status">
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
        <ul class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($restaurants as $restaurant)
                <li><x-restaurant-card :restaurant="$restaurant" :nearby-km="$nearbyKm" class="h-full" /></li>
            @endforeach
        </ul>
    @endif
</x-layouts.customer>
