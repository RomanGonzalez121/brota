<x-layouts.customer title="Inicio">
    <x-slot:hero>
        <div class="pt-2 pb-7 lg:pt-8 lg:pb-12">
            @if ($address)
                <a href="{{ route('profile.edit') }}#direcciones" class="inline-flex max-w-full items-center gap-1.5 rounded-md text-sm text-white/85 hover:text-white">
                    <x-icon name="pin" class="size-4" />
                    <span class="truncate">Enviar a <span class="font-semibold text-white">{{ $address->label }}</span>, {{ $address->street }}</span>
                    <x-icon name="chevron-down" class="size-3.5" />
                </a>
            @endif

            <h1 class="mt-3 max-w-2xl text-3xl lg:text-5xl">¿Qué comemos hoy, {{ auth()->user()->firstName() }}?</h1>

            <form action="{{ route('restaurants.index') }}" role="search" class="relative mt-5 max-w-xl">
                <label for="home-search" class="sr-only">Buscar un restaurante o un plato</label>
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-sage" />
                <input
                    id="home-search"
                    name="q"
                    type="search"
                    placeholder="Buscá un restaurante o un plato"
                    class="h-13 w-full rounded-2xl bg-white pr-28 pl-12 text-base text-forest placeholder:text-sage focus-visible:outline-white"
                >
                <x-button variant="accent" size="sm" class="absolute top-1/2 right-2 -translate-y-1/2">Buscar</x-button>
            </form>
        </div>
    </x-slot:hero>

    @if ($activeOrder)
        <a href="{{ route('orders.show', $activeOrder) }}" class="mb-8 flex items-center gap-4 rounded-2xl border border-sage/20 bg-white p-4 transition-[transform,border-color] duration-150 ease-out-strong hover:border-leaf/60 active:scale-[0.98]">
            <span class="relative flex size-11 shrink-0 items-center justify-center">
                <span class="absolute inset-0 animate-breathe rounded-full bg-sprout" aria-hidden="true"></span>
                <span class="relative flex size-11 items-center justify-center rounded-full bg-sprout">
                    <x-leaf class="size-6 text-forest" />
                </span>
            </span>
            <span class="min-w-0 flex-1">
                <span class="block font-display font-bold">Tu pedido está {{ mb_strtolower($activeOrder->status->label()) }}</span>
                <span class="block truncate text-sm text-sage">{{ $activeOrder->restaurant->name }}, pedido {{ $activeOrder->code }}</span>
            </span>
            <span class="hidden text-sm font-semibold text-leaf sm:block">Seguir pedido</span>
            <x-icon name="chevron-right" class="size-4 text-sage" />
        </a>
    @endif

    <section aria-labelledby="categories-title">
        <h2 id="categories-title" class="text-xl">Elegí por tipo de cocina</h2>
        <ul class="-mx-4 mt-3 flex gap-2 overflow-x-auto px-4 pb-1 lg:mx-0 lg:flex-wrap lg:px-0">
            @foreach ($categories as $category)
                <li class="shrink-0">
                    <a href="{{ route('restaurants.index', ['categoria' => $category->slug]) }}" class="flex h-11 items-center rounded-full border border-sage/25 bg-white px-4 text-[15px] font-medium transition-[transform,border-color,color] duration-150 ease-out-strong hover:border-leaf hover:text-leaf active:scale-[0.97]">
                        {{ $category->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="mt-9" aria-labelledby="featured-title">
        <div class="flex items-baseline justify-between gap-4">
            <h2 id="featured-title" class="text-xl">Destacados de la semana</h2>
            <a href="{{ route('restaurants.index') }}" class="shrink-0 text-sm font-semibold text-leaf hover:underline">Ver todos</a>
        </div>
        <ul class="-mx-4 mt-3 flex snap-x gap-4 overflow-x-auto px-4 pb-2 lg:mx-0 lg:grid lg:grid-cols-4 lg:overflow-visible lg:px-0">
            @foreach ($featured as $restaurant)
                <li class="w-72 shrink-0 snap-start lg:w-auto">
                    <x-restaurant-card :restaurant="$restaurant" :nearby-km="$nearbyKm" class="h-full" />
                </li>
            @endforeach
        </ul>
    </section>

    <div class="mt-9 grid grid-cols-1 gap-9 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)] lg:items-start">
        <section aria-labelledby="nearby-title">
            <h2 id="nearby-title" class="text-xl">Los más cercanos</h2>
            <x-card padding="p-0" class="mt-3 overflow-hidden">
                <ul>
                    @foreach ($nearby as $restaurant)
                        <li class="border-b border-sage/15 last:border-b-0">
                            <a href="{{ route('restaurants.show', $restaurant) }}" class="flex items-center gap-3.5 p-3.5 transition-colors duration-150 hover:bg-cream">
                                <x-cover :restaurant="$restaurant" class="size-14 shrink-0 rounded-leaf-sm" />
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate font-display font-bold">{{ $restaurant->name }}</span>
                                    <span class="block truncate text-sm text-sage">{{ $restaurant->tagline }}</span>
                                </span>
                                <span class="shrink-0 text-sm font-semibold tabular-nums">{{ number_format($restaurant->distance_km, 1, ',') }} km</span>
                                <x-icon name="chevron-right" class="size-4 text-sage" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </x-card>
        </section>

        <section class="relative overflow-hidden rounded-leaf bg-forest p-6 text-cream lg:mt-10" aria-labelledby="leaves-title">
            <x-leaf class="pointer-events-none absolute -right-8 -bottom-10 size-40 rotate-[10deg] text-sprout/25" />
            <h2 id="leaves-title" class="relative text-xl">Tenés {{ auth()->user()->leaf_balance }} Hojitas Verdes</h2>
            <p class="relative mt-2 max-w-sm text-cream/80">Sumás cada vez que pedís a un restaurante cercano o elegís envases retornables.</p>
            <x-button :href="route('leaves.index')" variant="secondary" class="relative mt-5">Ver mis hojitas</x-button>
        </section>
    </div>
</x-layouts.customer>
