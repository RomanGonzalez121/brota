<x-layouts.customer title="Inicio">
    <x-slot:hero>
        <div class="relative pt-3 pb-9 lg:pt-12 lg:pb-20">
            {{-- One orchestrated entrance: address, question, search. Leaves sway behind. --}}
            <div class="pointer-events-none absolute right-0 -bottom-6 hidden origin-bottom animate-sway lg:block" aria-hidden="true">
                <x-leaf class="size-96 rotate-[18deg] text-sprout/20" />
            </div>
            <div class="pointer-events-none absolute right-72 bottom-10 hidden origin-bottom animate-sway [animation-delay:-3s] lg:block" aria-hidden="true">
                <x-leaf class="size-44 -rotate-[58deg] text-sprout/35" />
            </div>

            @if ($address)
                <a href="{{ route('profile.edit') }}#direcciones" class="relative inline-flex max-w-full animate-rise items-center gap-1.5 rounded-md text-sm text-white/85 hover:text-white">
                    <x-icon name="pin" class="size-4" />
                    <span class="truncate">Enviar a <span class="font-semibold text-white">{{ $address->label }}</span>, {{ $address->street }}</span>
                    <x-icon name="chevron-down" class="size-3.5" />
                </a>
            @endif

            <h1 class="relative mt-3 max-w-3xl animate-rise text-4xl leading-[1.05] [animation-delay:70ms] lg:mt-5 lg:text-7xl lg:leading-none">¿Qué comemos hoy, {{ auth()->user()->firstName() }}?</h1>

            <form action="{{ route('restaurants.index') }}" role="search" class="relative mt-6 max-w-xl animate-rise [animation-delay:150ms] lg:mt-9">
                <label for="home-search" class="sr-only">Buscar un restaurante o un plato</label>
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-sage" />
                <input
                    id="home-search"
                    name="q"
                    type="search"
                    placeholder="Buscá un restaurante o un plato"
                    class="h-14 w-full rounded-full bg-white pr-28 pl-12 text-base text-forest placeholder:text-sage focus-visible:outline-white"
                >
                <x-button variant="accent" size="sm" class="absolute top-1/2 right-2 h-10 -translate-y-1/2 px-4">Buscar</x-button>
            </form>
        </div>
    </x-slot:hero>

    @if ($activeOrder)
        <a href="{{ route('orders.show', $activeOrder) }}" class="mb-10 flex animate-rise items-center gap-4 rounded-media border border-sage/20 bg-surface p-4 transition-[transform,border-color] duration-150 ease-out-strong [animation-delay:230ms] hover:border-leaf/60 active:scale-[0.98] lg:mb-16">
            <span class="relative flex size-11 shrink-0 items-center justify-center">
                <span class="absolute inset-0 animate-breathe rounded-full bg-sprout" aria-hidden="true"></span>
                <span class="relative flex size-11 items-center justify-center rounded-full bg-sprout">
                    <x-leaf class="size-6 text-forest" />
                </span>
            </span>
            <span class="min-w-0 flex-1">
                <span class="block font-display font-bold">Tu pedido está {{ mb_strtolower($activeOrder->status->label()) }}</span>
                <span class="block truncate text-sm text-muted">{{ $activeOrder->restaurant->name }}, pedido {{ $activeOrder->code }}</span>
            </span>
            <span class="hidden text-sm font-semibold text-leaf sm:block">Seguir pedido</span>
            <x-icon name="chevron-right" class="size-4 text-muted" />
        </a>
    @endif

    <section aria-labelledby="categories-title">
        <h2 id="categories-title" class="text-2xl lg:text-4xl" data-reveal>Elegí por tipo de cocina</h2>
        <ul class="-mx-4 mt-4 flex gap-2 overflow-x-auto overflow-y-hidden px-4 pb-1 contain-paint lg:mx-0 lg:mt-6 lg:flex-wrap lg:px-0">
            @foreach ($categories as $category)
                <li class="shrink-0" data-reveal style="--i: {{ $loop->index }}">
                    <a href="{{ route('restaurants.index', ['categoria' => $category->slug]) }}" class="group flex h-11 items-center gap-2 rounded-full border border-sage/25 bg-surface pr-4 pl-3 text-[15px] font-medium transition-[transform,border-color,color] duration-150 ease-out-strong hover:border-leaf hover:text-leaf active:scale-[0.97]">
                        <x-icon :name="$category->icon()" class="size-5 text-leaf transition-transform duration-200 ease-out-strong group-hover:-rotate-12 group-hover:scale-110" />
                        {{ $category->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="mt-12 lg:mt-24" aria-labelledby="featured-title">
        <div class="flex items-baseline justify-between gap-4" data-reveal>
            <h2 id="featured-title" class="text-2xl lg:text-4xl">Destacados de la semana</h2>
            <a href="{{ route('restaurants.index') }}" class="shrink-0 text-sm font-semibold text-leaf hover:underline">Ver todos</a>
        </div>
        <ul class="-mx-4 mt-4 flex snap-x gap-4 overflow-x-auto overflow-y-hidden px-4 pb-2 contain-paint lg:mx-0 lg:mt-6 lg:grid lg:grid-cols-4 lg:gap-6 lg:overflow-visible lg:px-0">
            @foreach ($featured as $restaurant)
                <li class="w-72 shrink-0 snap-start lg:w-auto" data-reveal style="--i: {{ $loop->index }}">
                    <x-restaurant-card :restaurant="$restaurant" :nearby-km="$nearbyKm" />
                </li>
            @endforeach
        </ul>
    </section>

    {{-- A change of background, not a box, marks where the next topic starts. --}}
    <x-band class="-mb-28 mt-12 pt-12 pb-28 lg:-mb-16 lg:mt-24 lg:py-24">
        <div class="grid grid-cols-1 gap-12 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)] lg:items-start lg:gap-16">
            <div>
                <h2 id="nearby-title" class="text-2xl lg:text-4xl" data-reveal>Los más cercanos</h2>
                <ul class="mt-4 lg:mt-6" aria-labelledby="nearby-title">
                    @foreach ($nearby as $restaurant)
                        <li class="border-b border-ink/10 last:border-b-0" data-reveal style="--i: {{ $loop->index }}">
                            <a href="{{ route('restaurants.show', $restaurant) }}" class="group flex items-center gap-4 py-3 transition-transform duration-150 ease-out-strong active:scale-[0.98]">
                                <x-cover :restaurant="$restaurant" class="photo-zoom size-18 shrink-0 rounded-media" />
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate font-display text-lg font-extrabold transition-colors duration-150 group-hover:text-leaf">{{ $restaurant->name }}</span>
                                    <span class="block truncate text-sm text-ink/70">{{ $restaurant->tagline }}</span>
                                </span>
                                <span class="shrink-0 text-sm font-semibold tabular-nums">{{ number_format($restaurant->distance_km, 1, ',') }} km</span>
                                <x-icon name="chevron-right" class="size-4 text-ink/50 transition-transform duration-200 ease-out-strong group-hover:translate-x-1" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <section class="relative overflow-hidden rounded-leaf bg-deep p-7 text-cream lg:p-9" aria-labelledby="leaves-title" data-reveal>
                <div class="pointer-events-none absolute -right-8 -bottom-10 origin-bottom-left animate-sway" aria-hidden="true">
                    <x-leaf class="size-44 rotate-[10deg] text-sprout/25" />
                </div>
                <h2 id="leaves-title" class="relative text-2xl lg:text-3xl">Tenés {{ auth()->user()->leaf_balance }} Hojitas Verdes</h2>
                <p class="relative mt-2 max-w-sm text-cream/80">Sumás cada vez que pedís a un restaurante cercano o elegís envases retornables.</p>
                <x-button :href="route('leaves.index')" variant="secondary" class="relative mt-6">
                    <x-icon name="sprout" class="size-4" />
                    Ver mis hojitas
                </x-button>
            </section>
        </div>
    </x-band>
</x-layouts.customer>
