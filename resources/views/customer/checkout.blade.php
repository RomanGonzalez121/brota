@php
    $selectedAddress = old('address_id', $checkout['address_id'] ?? $addresses->first()?->id);
    $selectedMethod = old('payment_method', $checkout['payment_method'] ?? 'card');
    $containers = old('returnable_containers', $checkout['returnable_containers'] ?? true);
@endphp

<x-layouts.customer title="Confirmar pedido">
    <a href="{{ route('cart.show') }}" class="inline-flex h-11 items-center gap-1.5 rounded-md text-sm font-semibold text-leaf hover:underline">
        <x-icon name="arrow-left" class="size-4" />
        Carrito
    </a>
    <h1 class="text-3xl leading-[1.1] lg:text-5xl">Confirmá tu pedido</h1>

    <form method="POST" action="{{ route('checkout.store') }}" data-smooth="off" class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-start">
        @csrf

        <div class="space-y-7">
            <fieldset>
                <legend class="font-display text-lg font-extrabold">¿A dónde lo enviamos?</legend>
                @error('address_id')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror

                @if ($addresses->isEmpty())
                    <p class="mt-2 text-muted">Todavía no guardaste ninguna dirección.</p>
                @else
                    <div class="mt-3 grid gap-2.5 sm:grid-cols-2">
                        @foreach ($addresses as $address)
                            <x-radio-card name="address_id" :value="$address->id" :checked="(int) $selectedAddress === $address->id">
                                <span class="block font-display font-bold">{{ $address->label }}</span>
                                <span class="block text-sm">{{ $address->fullLine() }}</span>
                                @if ($address->notes)
                                    <span class="block text-sm text-muted">{{ $address->notes }}</span>
                                @endif
                            </x-radio-card>
                        @endforeach
                    </div>
                @endif
                <a href="{{ route('profile.edit') }}#direcciones" class="mt-2 inline-flex h-11 items-center gap-1.5 text-sm font-semibold text-leaf hover:underline">
                    <x-icon name="plus" class="size-4" />
                    Agregar otra dirección
                </a>
            </fieldset>

            <fieldset>
                <legend class="font-display text-lg font-extrabold">¿Cómo pagás?</legend>
                <div class="mt-3 grid gap-2.5 sm:grid-cols-2">
                    <x-radio-card name="payment_method" value="card" :checked="$selectedMethod === 'card'">
                        <span class="flex items-center gap-2 font-display font-bold"><x-icon name="card" /> Tarjeta</span>
                        <span class="block text-sm text-muted">Crédito o débito</span>
                    </x-radio-card>
                    <x-radio-card name="payment_method" value="wallet" :checked="$selectedMethod === 'wallet'">
                        <span class="flex items-center gap-2 font-display font-bold"><x-icon name="wallet" /> Billetera virtual</span>
                        <span class="block text-sm text-muted">Mercado Pago, Ualá, MODO y otras</span>
                    </x-radio-card>
                </div>
            </fieldset>

            <div class="rounded-leaf bg-sprout-soft p-5">
                <x-toggle name="returnable_containers" :checked="(bool) $containers" class="w-full flex-row-reverse justify-between">
                    <span>
                        <span class="flex items-center gap-2 font-display text-lg font-extrabold">
                            <x-icon name="box" />
                            Envases retornables
                        </span>
                        <span class="mt-1 block text-sm">Te llega en envases de vidrio que devolvés en tu próximo pedido. Sumás {{ $containerLeaves }} Hojitas Verdes.</span>
                    </span>
                </x-toggle>
            </div>
        </div>

        <x-card class="lg:sticky lg:top-24">
            <h2 class="text-lg">{{ $restaurant->name }}</h2>
            <ul class="mt-3 space-y-1.5 text-sm">
                @foreach ($cart->lines() as $line)
                    <li class="flex justify-between gap-3">
                        <span><span class="tabular-nums">{{ $line->quantity }} ×</span> {{ $line->dish->name }}</span>
                        <span class="shrink-0 tabular-nums">{{ money($line->total) }}</span>
                    </li>
                @endforeach
            </ul>
            <dl class="mt-4 space-y-2 border-t border-sage/15 pt-4 tabular-nums">
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
            <x-button variant="accent" size="lg" block class="mt-5" :disabled="$addresses->isEmpty()">Ir a pagar</x-button>
        </x-card>
    </form>
</x-layouts.customer>
