<x-layouts.customer title="Perfil">
    <h1 class="text-2xl lg:text-3xl">Tu perfil</h1>

    <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)] lg:items-start">
        <div class="space-y-5">
            <x-card>
                <h2 class="text-lg">Tus datos</h2>
                <form method="POST" action="{{ route('profile.update') }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')
                    <x-input label="Nombre y apellido" name="name" :value="$user->name" autocomplete="name" required />
                    <x-input label="Email" name="email" type="email" :value="$user->email" autocomplete="email" required />
                    <x-input label="Teléfono" name="phone" type="tel" :value="$user->phone" autocomplete="tel" hint="Lo usa el repartidor si no te encuentra." />
                    <x-button>Guardar datos</x-button>
                </form>
            </x-card>

            <a href="{{ route('leaves.index') }}" class="flex items-center gap-3 rounded-leaf bg-sprout/30 p-4 transition-transform duration-150 ease-out-strong active:scale-[0.98]">
                <x-leaf class="size-8 text-leaf" />
                <span class="flex-1">
                    <span class="block font-display font-bold">{{ $user->leaf_balance }} Hojitas Verdes</span>
                    <span class="block text-sm">Mirá cómo las ganaste</span>
                </span>
                <x-icon name="chevron-right" class="size-4" />
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-button variant="secondary" block>
                    <x-icon name="logout" />
                    Cerrar sesión
                </x-button>
            </form>

            <p class="text-center text-sm lg:hidden">
                <a href="{{ route('credits') }}" class="inline-flex h-11 items-center font-medium text-sage hover:text-leaf hover:underline">Créditos de las fotos</a>
            </p>
        </div>

        <section id="direcciones" class="scroll-mt-24" x-data="{ editing: null, adding: @js($errors->hasAny(['label', 'street', 'neighborhood'])) }">
            <x-card>
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-lg">Direcciones guardadas</h2>
                    <x-button type="button" variant="ghost" size="sm" class="h-11" @click="adding = ! adding; editing = null" x-bind:aria-expanded="adding">
                        <x-icon name="plus" class="size-4" />
                        Agregar
                    </x-button>
                </div>

                <form x-show="adding" x-cloak method="POST" action="{{ route('profile.addresses.store') }}" class="mt-4 space-y-4 rounded-2xl bg-cream p-4">
                    @csrf
                    <x-input label="Nombre" name="label" id="new-label" placeholder="Casa, trabajo, lo de mamá…" required />
                    <x-input label="Calle, número y piso" name="street" id="new-street" autocomplete="street-address" required />
                    <x-input label="Barrio" name="neighborhood" id="new-neighborhood" required />
                    <x-input label="Indicaciones para el repartidor" name="notes" id="new-notes" hint="Opcional. Timbre, portería, referencias." />
                    <x-toggle name="is_default" label="Usarla como dirección principal" />
                    <div class="flex gap-2">
                        <x-button>Guardar dirección</x-button>
                        <x-button type="button" variant="ghost" @click="adding = false">Cancelar</x-button>
                    </div>
                </form>

                @if ($addresses->isEmpty())
                    <x-empty-state title="Todavía no guardaste direcciones">
                        Agregá una para pedir más rápido.
                    </x-empty-state>
                @else
                    <ul class="mt-2">
                        @foreach ($addresses as $address)
                            <li class="border-b border-sage/15 py-4 last:border-b-0 last:pb-0">
                                <div class="flex items-start gap-3" x-show="editing !== {{ $address->id }}">
                                    <x-icon name="pin" class="mt-0.5 text-sage" />
                                    <div class="min-w-0 flex-1">
                                        <p class="flex flex-wrap items-center gap-2 font-display font-bold">
                                            {{ $address->label }}
                                            @if ($address->is_default)
                                                <x-badge tone="sprout" leaf>Principal</x-badge>
                                            @endif
                                        </p>
                                        <p class="text-[15px]">{{ $address->fullLine() }}</p>
                                        @if ($address->notes)
                                            <p class="text-sm text-sage">{{ $address->notes }}</p>
                                        @endif
                                    </div>
                                    <button type="button" @click="editing = {{ $address->id }}; adding = false" class="flex size-11 shrink-0 items-center justify-center rounded-full text-sage transition-[transform,background-color,color] duration-150 ease-out-strong hover:bg-cream hover:text-leaf active:scale-[0.97]">
                                        <x-icon name="edit" />
                                        <span class="sr-only">Editar {{ $address->label }}</span>
                                    </button>
                                </div>

                                <div x-show="editing === {{ $address->id }}" x-cloak class="rounded-2xl bg-cream p-4">
                                    <form method="POST" action="{{ route('profile.addresses.update', $address) }}" class="space-y-4">
                                        @csrf
                                        @method('PUT')
                                        <x-input label="Nombre" name="label" id="label-{{ $address->id }}" :value="$address->label" required />
                                        <x-input label="Calle, número y piso" name="street" id="street-{{ $address->id }}" :value="$address->street" required />
                                        <x-input label="Barrio" name="neighborhood" id="neighborhood-{{ $address->id }}" :value="$address->neighborhood" required />
                                        <x-input label="Indicaciones para el repartidor" name="notes" id="notes-{{ $address->id }}" :value="$address->notes" />
                                        <x-toggle name="is_default" :checked="$address->is_default" label="Usarla como dirección principal" />
                                        <div class="flex flex-wrap gap-2">
                                            <x-button>Guardar cambios</x-button>
                                            <x-button type="button" variant="ghost" @click="editing = null">Cancelar</x-button>
                                        </div>
                                    </form>
                                    <form method="POST" action="{{ route('profile.addresses.destroy', $address) }}" class="mt-3 border-t border-sage/20 pt-3">
                                        @csrf
                                        @method('DELETE')
                                        <x-button variant="danger" size="sm" class="h-11">
                                            <x-icon name="trash" class="size-4" />
                                            Borrar dirección
                                        </x-button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>
        </section>
    </div>
</x-layouts.customer>
