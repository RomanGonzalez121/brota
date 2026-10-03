@php
    $roles = [
        ['value' => 'customer', 'label' => 'Cliente', 'hint' => 'Quiero pedir', 'icon' => 'cart'],
        ['value' => 'courier', 'label' => 'Repartidor', 'hint' => 'Quiero repartir', 'icon' => 'bike'],
        ['value' => 'restaurant', 'label' => 'Tienda', 'hint' => 'Tengo un local', 'icon' => 'store'],
    ];

    // If the server sent back an error on the second step, open the form there.
    $startOnDetails = $errors->hasAny(['vehicle', 'restaurant_name', 'category_id', 'tagline', 'address', 'neighborhood']);
@endphp

<x-layouts.guest title="Crear cuenta">
    {{--
        Two short steps so the screen never scrolls: the account first, then what a
        courier or a restaurant has to add. Customers finish on the first step.
    --}}
    <form
        method="POST"
        action="{{ route('register.store') }}"
        x-data="{
            role: @js(old('role', 'customer')),
            step: @js($startOnDetails ? 2 : 1),
            next() {
                for (const field of this.$refs.account.querySelectorAll('input')) {
                    if (! field.reportValidity()) return;
                }
                this.step = 2;
            },
        }"
        class="animate-rise"
    >
        @csrf

        <div class="flex items-end justify-between gap-4">
            <h1 class="text-3xl lg:text-4xl" x-text="step === 1 ? 'Creá tu cuenta' : (role === 'courier' ? 'Tu reparto' : 'Tu local')">Creá tu cuenta</h1>
            <p class="shrink-0 pb-1 text-sm font-medium text-muted tabular-nums" x-show="role !== 'customer'" x-cloak>Paso <span x-text="step"></span> de 2</p>
        </div>
        <p class="mt-1.5 text-muted" x-show="step === 1">
            ¿Ya tenés una?
            <a href="{{ route('login') }}" class="font-semibold text-leaf hover:underline">Ingresá</a>
        </p>

        {{-- Step 1: who you are. --}}
        <div x-ref="account" x-show="step === 1" class="mt-5 space-y-4">
            <fieldset>
                <legend class="mb-1.5 text-sm font-medium">¿Cómo vas a usar Brotá?</legend>
                @error('role')
                    <p class="mb-1.5 text-sm text-danger">{{ $message }}</p>
                @enderror
                <div class="grid grid-cols-3 gap-2">
                    @foreach ($roles as $option)
                        <label class="group flex cursor-pointer flex-col items-center gap-1 rounded-media border border-sage/25 bg-surface px-2 py-2.5 text-center transition-[transform,border-color,background-color] duration-150 ease-out-strong active:scale-[0.97] has-checked:border-leaf has-checked:bg-leaf/8 has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-leaf sm:flex-row sm:gap-2.5 sm:px-3 sm:text-left">
                            <input type="radio" name="role" value="{{ $option['value'] }}" x-model="role" class="sr-only">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-leaf-sm bg-leaf/12 text-leaf">
                                <x-icon :name="$option['icon']" class="transition-transform duration-200 ease-out-strong group-hover:-rotate-12 group-hover:scale-110" />
                            </span>
                            <span class="min-w-0">
                                <span class="block font-display text-[15px] leading-tight font-bold">{{ $option['label'] }}</span>
                                <span class="block text-xs text-muted">{{ $option['hint'] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div class="grid gap-x-4 gap-y-3 sm:grid-cols-2">
                <x-input label="Nombre y apellido" name="name" autocomplete="name" required />
                <x-input label="Teléfono" name="phone" type="tel" autocomplete="tel" placeholder="11 5555-5555" required />
                <div class="sm:col-span-2">
                    <x-input label="Email" name="email" type="email" autocomplete="email" placeholder="vos@ejemplo.com" required />
                </div>
                <x-input label="Contraseña" name="password" type="password" autocomplete="new-password" minlength="8" placeholder="Al menos 8 caracteres" required />
                <x-input label="Repetí la contraseña" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required />
            </div>

            <x-button block size="lg" x-show="role === 'customer'">Crear cuenta</x-button>
            <x-button block size="lg" type="button" x-show="role !== 'customer'" x-cloak @click="next()">
                Continuar
                <x-icon name="chevron-right" class="size-4" />
            </x-button>
        </div>

        {{-- Step 2: what the role adds. Fields are required only for the role that shows them. --}}
        <div
            x-show="step === 2"
            x-cloak
            x-transition:enter="transition-[opacity,transform] duration-300 ease-out-strong"
            x-transition:enter-start="translate-x-3 opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            class="mt-5 space-y-4"
        >
            <div x-show="role === 'courier'" class="space-y-3">
                <p class="text-muted">Contanos con qué vas a hacer las entregas. En Brotá el reparto es de bajo impacto.</p>
                <x-select label="¿Con qué vas a repartir?" name="vehicle" :options="$vehicles" placeholder="Elegí un vehículo" x-bind:required="role === 'courier'" />
            </div>

            <div x-show="role === 'restaurant'" class="grid gap-x-4 gap-y-3 sm:grid-cols-2">
                <x-input label="Nombre del local" name="restaurant_name" x-bind:required="role === 'restaurant'" />
                <x-select label="Tipo de cocina" name="category_id" :options="$categories" placeholder="Elegí una categoría" x-bind:required="role === 'restaurant'" />
                <div class="sm:col-span-2">
                    <x-input label="Qué ofrecés, en una línea" name="tagline" placeholder="Pastas caseras con verduras de estación" x-bind:required="role === 'restaurant'" />
                </div>
                <x-input label="Dirección" name="address" autocomplete="street-address" x-bind:required="role === 'restaurant'" />
                <x-input label="Barrio" name="neighborhood" x-bind:required="role === 'restaurant'" />
            </div>

            <div class="flex gap-2">
                <x-button type="button" variant="secondary" size="lg" @click="step = 1">
                    <x-icon name="arrow-left" class="size-4" />
                    Volver
                </x-button>
                <x-button size="lg" class="flex-1">Crear cuenta</x-button>
            </div>
        </div>
    </form>
</x-layouts.guest>
