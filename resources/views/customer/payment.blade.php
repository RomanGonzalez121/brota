<x-layouts.customer title="Pago">
    <a href="{{ route('checkout.create') }}" class="inline-flex h-11 items-center gap-1.5 rounded-md text-sm font-semibold text-leaf hover:underline">
        <x-icon name="arrow-left" class="size-4" />
        Confirmar pedido
    </a>
    <h1 class="text-3xl leading-[1.1] lg:text-5xl">Pagá tu pedido</h1>
    <p class="mt-1 text-muted">{{ $restaurant->name }}</p>

    <form method="POST" action="{{ route('payment.store') }}" data-smooth="off" class="mt-5 max-w-xl">
        @csrf

        @if ($benefit && $total > 0)
            <p class="mb-5 flex items-center gap-2.5 rounded-leaf-sm bg-sprout-soft px-4 py-3 text-[15px]">
                <x-leaf class="size-5 text-leaf" />
                <span><span class="font-semibold">{{ $benefit }}</span> por tu nivel: ya está descontado del total.</span>
            </p>
        @endif

        @if ($total === 0)
            {{-- The benefit covers all of it: there is nothing to charge, so nothing is asked. --}}
            <div class="relative rounded-leaf bg-deep p-6 text-cream">
                <div class="pointer-events-none absolute top-5 right-5 origin-bottom-left animate-sway" aria-hidden="true">
                    <x-leaf class="size-16 rotate-[10deg] text-foliage/40" />
                </div>
                <p class="relative pr-20 font-display text-2xl font-extrabold text-white">Este pedido va por cuenta de Brotá</p>
                <p class="relative mt-2 max-w-sm pr-20 text-cream/85">Usás el pedido gratis de tu nivel. No tenés que cargar ningún medio de pago.</p>
            </div>
        @elseif ($method === 'card')
            <div
                x-data="{ number: @js(old('card_number', '')), name: @js(old('card_name', '')), expiry: @js(old('card_expiry', '')) }"
                class="space-y-4"
            >
                {{-- A live preview of the card, so the form reads as one object and not four loose fields. --}}
                <div class="relative overflow-hidden rounded-leaf bg-deep p-5 text-cream" aria-hidden="true">
                    <x-leaf class="absolute -right-6 -bottom-8 size-36 rotate-[10deg] text-foliage/25" />
                    <x-icon name="card" class="size-7 text-sprout" />
                    <p class="relative mt-6 font-display text-xl font-bold tracking-widest tabular-nums" x-text="number || '•••• •••• •••• ••••'"></p>
                    <div class="relative mt-4 flex justify-between gap-4 text-sm">
                        <span class="truncate uppercase" x-text="name || 'Nombre y apellido'"></span>
                        <span class="tabular-nums" x-text="expiry || 'MM/AA'"></span>
                    </div>
                </div>

                <x-input
                    label="Número de tarjeta" name="card_number" inputmode="numeric" autocomplete="cc-number" placeholder="4509 9535 6623 3704" required
                    x-model="number"
                    x-on:input="number = $event.target.value.replace(/\D/g, '').slice(0, 19).replace(/(.{4})/g, '$1 ').trim()"
                />
                <x-input label="Nombre como figura en la tarjeta" name="card_name" autocomplete="cc-name" required x-model="name" />
                <div class="grid grid-cols-2 gap-4">
                    <x-input
                        label="Vencimiento" name="card_expiry" inputmode="numeric" autocomplete="cc-exp" placeholder="MM/AA" required
                        x-model="expiry"
                        x-on:input="expiry = $event.target.value.replace(/\D/g, '').slice(0, 4).replace(/^(\d{2})(\d)/, '$1/$2')"
                    />
                    <x-input label="Código de seguridad" name="card_cvv" type="password" inputmode="numeric" autocomplete="cc-csc" maxlength="4" placeholder="CVV" required />
                </div>
            </div>
        @else
            <fieldset>
                <legend class="font-display text-lg font-extrabold">Elegí tu billetera</legend>
                @error('wallet')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
                <div class="mt-3 grid gap-2.5 sm:grid-cols-2">
                    @foreach ($wallets as $wallet)
                        <x-radio-card name="wallet" :value="$wallet" :checked="old('wallet', $wallets[0]) === $wallet">
                            <span class="flex items-center gap-2 font-display font-bold"><x-icon name="wallet" /> {{ $wallet }}</span>
                        </x-radio-card>
                    @endforeach
                </div>
            </fieldset>
        @endif

        <p class="mt-5 flex items-start gap-2 text-sm text-muted {{ $total === 0 ? 'hidden' : '' }}">
            <x-icon name="shield" class="mt-0.5 size-4" />
            Esto es una maqueta: el pago es simulado, no se cobra nada y siempre se aprueba.
        </p>

        <x-button variant="accent" size="lg" block class="mt-5">{{ $total === 0 ? 'Confirmar pedido gratis' : 'Pagar '.money($total) }}</x-button>
    </form>
</x-layouts.customer>
