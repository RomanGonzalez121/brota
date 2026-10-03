<x-layouts.customer title="Inicio">
    {{--
        The first screen has to answer three things at a glance: how you ask for
        food, what makes Brotá different, and what there is to eat. So the hero
        stays short and holds only the search and one line of context, and the
        featured restaurants' photos come right under it, inside the first screen.
    --}}
    <x-slot:hero>
        <div class="relative pt-4 pb-7 lg:py-10">
            {{--
                Reading order, most needed first: an order already on its way, the question
                with the search that answers it, then where it goes and the eco promise on
                one quiet line. Nothing here moves on load: it is what people act on.
            --}}
            <div class="relative z-10 lg:max-w-2xl">
                {{-- An order in progress outranks browsing, so it comes first. --}}
                @if ($activeOrder)
                    <a href="{{ route('orders.show', $activeOrder) }}" class="group mb-5 flex items-center gap-3 rounded-full bg-white/10 py-1.5 pr-4 pl-1.5 transition-[transform,background-color] duration-150 ease-out-strong hover:bg-white/15 active:scale-[0.98]">
                        <span class="relative flex size-9 shrink-0 items-center justify-center">
                            <span class="absolute inset-0 animate-breathe rounded-full bg-sprout" aria-hidden="true"></span>
                            <span class="relative flex size-9 items-center justify-center rounded-full bg-sprout">
                                <x-leaf class="size-5 text-forest" />
                            </span>
                        </span>
                        <span class="min-w-0 flex-1 truncate text-[15px]">
                            <span class="font-display font-bold"><span class="sm:hidden">Pedido</span><span class="hidden sm:inline">Tu pedido está</span> {{ mb_strtolower($activeOrder->status->label()) }}</span>
                            <span class="hidden text-white/75 sm:inline">en {{ $activeOrder->restaurant->name }}</span>
                        </span>
                        <span class="shrink-0 text-sm font-semibold text-sprout">Seguir</span>
                        <x-icon name="chevron-right" class="size-4 shrink-0 text-white/70 transition-transform duration-200 ease-out-strong group-hover:translate-x-0.5" />
                    </a>
                @endif

                <h1 class="text-3xl leading-tight lg:text-[2.75rem] lg:leading-[1.1]">¿Qué comemos hoy, {{ auth()->user()->firstName() }}?</h1>

                {{-- The search is the action of this screen: it is the largest thing in the hero. --}}
                <form action="{{ route('restaurants.index') }}" role="search" class="relative mt-5">
                    <label for="home-search" class="sr-only">Buscar un restaurante o un plato</label>
                    <x-icon name="search" class="pointer-events-none absolute top-1/2 left-5 size-6 -translate-y-1/2 text-sage" />
                    <input
                        id="home-search"
                        name="q"
                        type="search"
                        placeholder="Restaurante o plato"
                        class="h-16 w-full rounded-full bg-white pr-28 pl-14 text-base text-forest placeholder:text-sage focus-visible:outline-white lg:pr-32 lg:text-lg"
                    >
                    <span class="absolute top-1/2 right-2.5 -translate-y-1/2"><x-button variant="accent" class="h-11 px-6">Buscar</x-button></span>
                </form>

                <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-1.5 text-[15px] text-white/85">
                    @if ($address)
                        <a href="{{ route('profile.edit') }}#direcciones" class="inline-flex max-w-full items-center gap-1.5 rounded-md hover:text-white">
                            <x-icon name="pin" class="size-4" />
                            <span class="truncate">Enviar a <span class="font-semibold text-white">{{ $address->label }}</span>, {{ $address->street }}</span>
                            <x-icon name="chevron-down" class="size-3.5" />
                        </a>
                    @endif

                    <a href="{{ route('leaves.index') }}" class="inline-flex items-center gap-1.5 rounded-md hover:text-white">
                        <x-leaf class="size-4 shrink-0 text-sprout" />
                        Pedí cerca y sumá Hojitas Verdes
                    </a>
                </div>
            </div>

            {{-- The two leaves have the right side to themselves: nothing sits on top of them. --}}
            <div class="pointer-events-none absolute right-2 bottom-4 hidden lg:block" data-parallax="12" aria-hidden="true">
                <div class="origin-bottom animate-sway"><x-leaf class="size-60 rotate-[16deg] text-foliage/30" /></div>
            </div>
            <div class="pointer-events-none absolute right-52 bottom-6 hidden lg:block" data-parallax="24" aria-hidden="true">
                <div class="origin-bottom animate-sway [animation-delay:-3s]"><x-leaf class="size-28 -rotate-[58deg] text-foliage-light/45" /></div>
            </div>
        </div>
    </x-slot:hero>

    {{-- A filter, not a section: it gets a quiet label instead of a headline. --}}
    <section aria-labelledby="categories-title">
        <h2 id="categories-title" class="font-sans text-sm font-semibold tracking-normal text-muted">Tipos de cocina</h2>
        <ul class="-mx-4 mt-2.5 flex gap-2 overflow-x-auto overflow-y-hidden px-4 pb-1 contain-paint lg:mx-0 lg:flex-wrap lg:px-0">
            @foreach ($categories as $category)
                <li class="shrink-0">
                    <a href="{{ route('restaurants.index', ['categoria' => $category->slug]) }}" class="group flex h-11 items-center gap-2 rounded-full border border-sage/25 bg-surface pr-4 pl-3 text-[15px] font-medium transition-[transform,border-color,color] duration-150 ease-out-strong hover:border-leaf hover:text-leaf active:scale-[0.97]">
                        <x-icon :name="$category->icon()" class="size-5 text-leaf transition-transform duration-200 ease-out-strong group-hover:-rotate-12 group-hover:scale-110" />
                        {{ $category->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="mt-8 lg:mt-10" aria-labelledby="featured-title">
        <div class="flex items-baseline justify-between gap-4">
            <h2 id="featured-title" class="text-xl lg:text-2xl">Destacados de la semana</h2>
            <a href="{{ route('restaurants.index') }}" class="shrink-0 text-sm font-semibold text-leaf hover:underline">Ver todos</a>
        </div>
        <ul class="-mx-4 mt-4 flex snap-x gap-4 overflow-x-auto overflow-y-hidden px-4 pb-2 contain-paint lg:mx-0 lg:grid lg:grid-cols-4 lg:gap-6 lg:overflow-visible lg:px-0">
            @foreach ($featured as $restaurant)
                <li class="enter-photo w-72 shrink-0 snap-start lg:w-auto" style="--i: {{ $loop->index }}">
                    <x-restaurant-card :restaurant="$restaurant" :nearby-km="$nearbyKm" />
                </li>
            @endforeach
        </ul>
    </section>

    {{-- A change of background, not a box, marks where the next topic starts. --}}
    <x-band class="-mb-28 mt-12 pt-12 pb-28 lg:-mb-16 lg:mt-20 lg:py-20">
        <div class="grid grid-cols-1 gap-12 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)] lg:items-start lg:gap-16">
            <div>
                <h2 id="nearby-title" class="text-xl lg:text-2xl" data-reveal>Los más cercanos</h2>
                <ul class="mt-3 lg:mt-4" aria-labelledby="nearby-title">
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
                <div class="pointer-events-none absolute right-5 bottom-5 origin-bottom-left animate-sway" aria-hidden="true">
                    <x-leaf class="size-20 rotate-[8deg] text-foliage-light/50 lg:size-24" />
                </div>
                <h2 id="leaves-title" class="relative text-xl lg:text-2xl">Tenés {{ auth()->user()->leaf_balance }} Hojitas Verdes</h2>
                <p class="relative mt-2 max-w-sm text-cream/80">Sumás cada vez que pedís a un restaurante cercano o elegís envases retornables.</p>
                <x-button :href="route('leaves.index')" variant="secondary" class="relative mt-6">
                    <x-icon name="sprout" class="size-4" />
                    Ver mis hojitas
                </x-button>
            </section>
        </div>
    </x-band>
</x-layouts.customer>
