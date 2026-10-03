@php
    $inCart = $cartIsHere ? $cart->lines()->keyBy(fn ($line) => $line->dish->id) : collect();
@endphp

<x-layouts.customer :title="$restaurant->name">
    <a href="{{ route('restaurants.index') }}" class="inline-flex h-11 items-center gap-1.5 rounded-md text-sm font-semibold text-leaf hover:underline">
        <x-icon name="arrow-left" class="size-4" />
        Restaurantes
    </a>

    <x-cover :restaurant="$restaurant" class="mt-1 h-32 rounded-leaf lg:h-44" />

    <div class="mt-5 lg:flex lg:items-end lg:justify-between lg:gap-8">
        <div>
            <h1 class="text-3xl lg:text-4xl">{{ $restaurant->name }}</h1>
            <p class="mt-1 text-lg text-sage">{{ $restaurant->tagline }}</p>
        </div>
        <dl class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-[15px] tabular-nums lg:mt-0 lg:shrink-0">
            <div class="flex items-center gap-1.5">
                <dt><x-icon name="star" class="size-4 text-carrot" /><span class="sr-only">Puntaje</span></dt>
                <dd class="font-semibold">{{ number_format($restaurant->rating, 1, ',') }}</dd>
            </div>
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

    <p class="mt-4 max-w-2xl">{{ $restaurant->description }}</p>
    <p class="mt-1 text-sm text-sage">{{ $restaurant->category->name }}. {{ $restaurant->address }}, {{ $restaurant->neighborhood }}.</p>

    @if ($restaurant->distance_km <= $nearbyKm)
        <p class="mt-4 inline-flex items-center gap-2 rounded-leaf-sm bg-sprout/30 px-3 py-2 text-sm font-medium">
            <x-leaf class="size-4 text-leaf" />
            Está cerca: este pedido suma Hojitas Verdes.
        </p>
    @endif

    <div class="mt-8 max-w-3xl space-y-8">
        @foreach ($sections as $section => $dishes)
            <section aria-labelledby="section-{{ $loop->index }}">
                <h2 id="section-{{ $loop->index }}" class="text-xl">{{ $section }}</h2>
                <x-card padding="p-0" class="mt-3">
                    <ul>
                        @foreach ($dishes as $dish)
                            <li id="plato-{{ $dish->id }}" class="flex scroll-mt-24 items-start gap-4 border-b border-sage/15 p-4 last:border-b-0 {{ $dish->is_available ? '' : 'opacity-60' }}">
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-base">{{ $dish->name }}</h3>
                                    <p class="mt-0.5 text-sm text-sage">{{ $dish->description }}</p>
                                    <p class="mt-2 font-display font-bold tabular-nums">{{ money($dish->price) }}</p>
                                    <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                        @foreach ($dish->tags ?? [] as $tag)
                                            <x-badge tone="neutral">{{ $tag }}</x-badge>
                                        @endforeach
                                        @if ($inCart->has($dish->id))
                                            <x-badge tone="accent">{{ $inCart[$dish->id]->quantity }} en el carrito</x-badge>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex w-28 shrink-0 flex-col items-stretch gap-2">
                                    <x-dish-photo :dish="$dish" class="h-24 rounded-2xl" />
                                    @if ($dish->is_available)
                                        <form method="POST" action="{{ route('cart.store', $dish) }}">
                                            @csrf
                                            <x-button size="sm" variant="secondary" block class="h-11">
                                                <x-icon name="plus" class="size-4" />
                                                Agregar
                                                <span class="sr-only">{{ $dish->name }} al carrito</span>
                                            </x-button>
                                        </form>
                                    @else
                                        <x-badge tone="danger" class="justify-center">Sin stock hoy</x-badge>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </x-card>
            </section>
        @endforeach
    </div>

    @if ($cartIsHere && ! $cart->isEmpty())
        <a href="{{ route('cart.show') }}" class="fixed inset-x-4 bottom-[calc(4.75rem+env(safe-area-inset-bottom))] z-20 flex h-14 items-center justify-between gap-4 rounded-2xl bg-carrot px-5 font-display font-bold text-forest shadow-lift transition-[transform,background-color] duration-150 ease-out-strong hover:bg-carrot-deep active:scale-[0.97] lg:inset-x-auto lg:right-8 lg:bottom-8 lg:min-w-80">
            <span class="flex items-center gap-2.5">
                <x-icon name="cart" />
                Ver carrito ({{ $cart->count() }})
            </span>
            <span class="tabular-nums">{{ money($cart->subtotal()) }}</span>
        </a>
        <div class="h-16 lg:hidden" aria-hidden="true"></div>
    @endif
</x-layouts.customer>
