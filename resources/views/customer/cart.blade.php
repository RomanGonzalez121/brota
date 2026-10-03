<x-layouts.customer title="Carrito">
    <h1 class="text-3xl leading-[1.1] lg:text-5xl">Tu carrito</h1>

    @if ($cart->isEmpty())
        <x-card class="mt-5">
            <x-empty-state title="Tu carrito está vacío">
                Elegí un restaurante y agregá los platos que quieras pedir.
                <x-slot:action>
                    <x-button :href="route('restaurants.index')">Ver restaurantes</x-button>
                </x-slot:action>
            </x-empty-state>
        </x-card>
    @else
        <p class="mt-1 text-muted">
            Pedido a <a href="{{ route('restaurants.show', $restaurant) }}" class="font-semibold text-leaf hover:underline">{{ $restaurant->name }}</a>
        </p>

        <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-start">
            <x-card padding="p-0">
                <ul>
                    @foreach ($cart->lines() as $line)
                        <li class="flex items-center gap-3 border-b border-sage/15 p-4 last:border-b-0">
                            <x-dish-photo :dish="$line->dish" class="hidden size-14 rounded-xl sm:block" />
                            <div class="min-w-0 flex-1">
                                <h2 class="font-sans text-base font-semibold tracking-normal">{{ $line->dish->name }}</h2>
                                <p class="text-sm text-muted tabular-nums">{{ money($line->dish->price) }} cada uno</p>
                            </div>

                            <div class="flex shrink-0 items-center rounded-xl border border-sage/25">
                                <form method="POST" action="{{ route('cart.update', $line->dish) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="quantity" value="{{ $line->quantity - 1 }}">
                                    <button type="submit" class="flex size-11 items-center justify-center rounded-l-xl transition-[transform,background-color] duration-150 ease-out-strong hover:bg-page active:scale-[0.97] {{ $line->quantity === 1 ? 'text-danger' : '' }}">
                                        <x-icon :name="$line->quantity === 1 ? 'trash' : 'minus'" class="size-4" />
                                        <span class="sr-only">{{ $line->quantity === 1 ? 'Quitar' : 'Restar uno de' }} {{ $line->dish->name }}</span>
                                    </button>
                                </form>
                                <span class="w-7 text-center font-display font-bold tabular-nums" aria-label="Cantidad">{{ $line->quantity }}</span>
                                <form method="POST" action="{{ route('cart.update', $line->dish) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="quantity" value="{{ $line->quantity + 1 }}">
                                    <button type="submit" class="flex size-11 items-center justify-center rounded-r-xl transition-[transform,background-color] duration-150 ease-out-strong hover:bg-page active:scale-[0.97]">
                                        <x-icon name="plus" class="size-4" />
                                        <span class="sr-only">Sumar uno de {{ $line->dish->name }}</span>
                                    </button>
                                </form>
                            </div>

                            <span class="w-20 shrink-0 text-right font-display font-bold tabular-nums">{{ money($line->total) }}</span>
                        </li>
                    @endforeach
                </ul>

                <div class="flex items-center justify-between border-t border-sage/15 px-4 py-2">
                    <a href="{{ route('restaurants.show', $restaurant) }}" class="inline-flex h-11 items-center gap-1.5 text-sm font-semibold text-leaf hover:underline">
                        <x-icon name="plus" class="size-4" />
                        Agregar más platos
                    </a>
                    <form method="POST" action="{{ route('cart.destroy') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="h-11 rounded-md text-sm font-medium text-muted hover:text-danger">Vaciar carrito</button>
                    </form>
                </div>
            </x-card>

            <x-card class="lg:sticky lg:top-24">
                <h2 class="text-lg">Resumen</h2>
                <dl class="mt-3 space-y-2 tabular-nums">
                    <div class="flex justify-between">
                        <dt class="text-muted">Subtotal</dt>
                        <dd>{{ money($cart->subtotal()) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-muted">Envío</dt>
                        <dd>{{ money($restaurant->delivery_fee) }}</dd>
                    </div>
                    <div class="flex justify-between border-t border-sage/15 pt-3 font-display text-lg font-extrabold">
                        <dt>Total</dt>
                        <dd>{{ money($cart->subtotal() + $restaurant->delivery_fee) }}</dd>
                    </div>
                </dl>
                <x-button :href="route('checkout.create')" variant="accent" size="lg" block class="mt-5">Continuar con el pedido</x-button>
            </x-card>
        </div>
    @endif
</x-layouts.customer>
