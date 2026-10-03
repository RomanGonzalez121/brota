@php
    $inCart = $cartIsHere ? $cart->lines()->keyBy(fn ($line) => $line->dish->id) : collect();
@endphp

<x-layouts.customer :title="$restaurant->name">
    <a href="{{ route('restaurants.index') }}" class="inline-flex h-11 items-center gap-1.5 rounded-md text-sm font-semibold text-leaf hover:underline">
        <x-icon name="arrow-left" class="size-4" />
        Restaurantes
    </a>

    <x-cover :restaurant="$restaurant" class="mt-1 h-44 rounded-media lg:h-80" style="view-transition-name: cover-{{ $restaurant->id }}" />

    <div class="mt-6 lg:mt-10 lg:flex lg:items-end lg:justify-between lg:gap-8">
        <div>
            <h1 class="animate-rise text-4xl leading-[1.05] lg:text-6xl lg:leading-none">{{ $restaurant->name }}</h1>
            <p class="mt-2 animate-rise text-lg text-muted [animation-delay:70ms] lg:text-xl">{{ $restaurant->tagline }}</p>
        </div>
        <dl class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-[15px] tabular-nums lg:mt-0 lg:shrink-0">
            <div class="flex items-center gap-1.5">
                <dt><x-icon name="star" class="size-4 text-leaf" /><span class="sr-only">Puntaje</span></dt>
                <dd class="font-semibold">{{ $restaurant->rating > 0 ? number_format($restaurant->rating, 1, ',') : 'Nuevo' }}</dd>
            </div>
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
    </div>

    <p class="mt-4 max-w-2xl">{{ $restaurant->description }}</p>
    <p class="mt-1 text-sm text-muted">{{ $restaurant->category->name }}. {{ $restaurant->address }}, {{ $restaurant->neighborhood }}.</p>

    @if ($restaurant->distance_km <= $nearbyKm)
        <p class="mt-4 inline-flex items-center gap-2 rounded-leaf-sm bg-sprout-soft px-3 py-2 text-sm font-medium">
            <x-leaf class="size-4 text-leaf" />
            Está cerca: este pedido suma Hojitas Verdes.
        </p>
    @endif

    @if ($sections->isEmpty())
        <x-card class="mt-10">
            <x-empty-state title="Este local todavía está armando su menú">
                Volvé en un rato: están cargando los platos.
            </x-empty-state>
        </x-card>
    @endif

    <div class="mt-12 space-y-12 lg:mt-20 lg:space-y-20">
        @foreach ($sections as $section => $dishes)
            <section aria-labelledby="section-{{ $loop->index }}">
                <h2 id="section-{{ $loop->index }}" class="text-2xl lg:text-4xl" data-reveal>{{ $section }}</h2>

                {{-- The photo is the card: a row on phones, a tile with the photo on top on desktop. --}}
                <ul class="mt-5 grid grid-cols-1 gap-y-8 lg:mt-8 lg:grid-cols-3 lg:gap-x-6 lg:gap-y-12">
                    @foreach ($dishes as $dish)
                        <li id="plato-{{ $dish->id }}" data-reveal style="--i: {{ $loop->index % 3 }}" class="group flex scroll-mt-24 flex-row-reverse items-start gap-4 lg:flex-col lg:gap-3 {{ $dish->is_available ? '' : 'opacity-60' }}">
                            <x-dish-photo :dish="$dish" class="photo-zoom size-28 rounded-media lg:aspect-[4/3] lg:h-auto lg:w-full" />

                            <div class="min-w-0 flex-1 lg:w-full">
                                <h3 class="text-lg leading-snug">{{ $dish->name }}</h3>
                                <p class="mt-0.5 text-sm text-muted">{{ $dish->description }}</p>

                                @if ($dish->tags || $inCart->has($dish->id))
                                    <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                        @foreach ($dish->tags ?? [] as $tag)
                                            <x-badge tone="neutral"><x-icon :name="\App\Models\Dish::TAG_ICONS[$tag] ?? 'sprout'" class="size-3.5 text-leaf" />{{ $tag }}</x-badge>
                                        @endforeach
                                        @if ($inCart->has($dish->id))
                                            <x-badge tone="sprout" :class="session()->has('status') ? 'animate-pop' : ''">{{ $inCart[$dish->id]->quantity }} en el carrito</x-badge>
                                        @endif
                                    </div>
                                @endif

                                <div class="mt-3 flex items-center justify-between gap-3">
                                    <span class="font-display text-lg font-extrabold tabular-nums">{{ money($dish->price) }}</span>
                                    @if ($dish->is_available)
                                        <form method="POST" action="{{ route('cart.store', $dish) }}">
                                            @csrf
                                            <x-button size="sm" variant="secondary" class="h-11 px-4">
                                                <x-icon name="plus" class="size-4" />
                                                Agregar
                                                <span class="sr-only">{{ $dish->name }} al carrito</span>
                                            </x-button>
                                        </form>
                                    @else
                                        <x-badge tone="danger">Sin stock hoy</x-badge>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach
    </div>

    @if ($cartIsHere && ! $cart->isEmpty())
        <a href="{{ route('cart.show') }}" class="fixed inset-x-4 bottom-[calc(4.75rem+env(safe-area-inset-bottom))] z-20 flex h-14 animate-slide-up items-center justify-between gap-4 rounded-full bg-carrot px-6 font-display font-bold text-forest shadow-lift transition-[transform,background-color] duration-150 ease-out-strong hover:bg-carrot-deep active:scale-[0.97] lg:inset-x-auto lg:right-8 lg:bottom-8 lg:min-w-80">
            <span class="flex items-center gap-2.5">
                <x-icon name="cart" />
                Ver carrito ({{ $cart->count() }})
            </span>
            <span class="tabular-nums">{{ money($cart->subtotal()) }}</span>
        </a>
        <div class="h-16 lg:hidden" aria-hidden="true"></div>
    @endif
</x-layouts.customer>
