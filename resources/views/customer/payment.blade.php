<x-layouts.customer title="Pago">
    <a href="{{ route('checkout.create') }}" class="inline-flex h-11 items-center gap-1.5 rounded-md text-sm font-semibold text-leaf hover:underline">
        <x-icon name="arrow-left" class="size-4" />
        Confirmar pedido
    </a>
    <h1 class="text-3xl leading-[1.1] lg:text-5xl">Pagá tu pedido</h1>
    <p class="mt-1 text-muted">{{ $restaurant->name }}</p>

    <form method="POST" action="{{ route('payment.store') }}" class="mt-5 max-w-xl">
        @csrf

        @if ($method === 'card')
            <div
                x-data="{ number: @js(old('card_number', '')), name: @js(old('card_name', '')), expiry: @js(old('card_expiry', '')) }"
                class="space-y-4"
            >
                {{-- A live preview of the card, so the form reads as one object and not four loose fields. --}}
                <div class="relative overflow-hidden rounded-leaf bg-deep p-5 text-cream" aria-hidden="true">
                    <x-leaf class="absolute -right-6 -bottom-8 size-36 rotate-[10deg] text-sprout/25" />
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

        <p class="mt-5 flex items-start gap-2 text-sm text-muted">
            <x-icon name="shield" class="mt-0.5 size-4" />
            Esto es una maqueta: el pago es simulado, no se cobra nada y siempre se aprueba.
        </p>

        <x-button variant="accent" size="lg" block class="mt-5">Pagar {{ money($total) }}</x-button>
    </form>
</x-layouts.customer>
