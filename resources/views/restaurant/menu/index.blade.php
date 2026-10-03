@php
    $total = $sections->flatten()->count();
    $unavailable = $sections->flatten()->where('is_available', false)->count();
@endphp

<x-layouts.panel title="Menú" :subtitle="$total === 0 ? 'Cargá los platos que ofrece tu local.' : $total.' platos, '.($unavailable === 0 ? 'todos disponibles.' : $unavailable.' sin stock.')">
    <x-slot:actions>
        <x-button :href="route('restaurant.menu.create')">
            <x-icon name="plus" class="size-4" />
            Agregar plato
        </x-button>
    </x-slot:actions>

    @if ($total === 0)
        <x-card>
            <x-empty-state title="Tu menú está vacío">
                Los clientes van a ver tu local cuando tenga al menos un plato.
                <x-slot:action>
                    <x-button :href="route('restaurant.menu.create')">Agregar el primer plato</x-button>
                </x-slot:action>
            </x-empty-state>
        </x-card>
    @endif

    <div class="space-y-8">
        @foreach ($sections as $section => $dishes)
            <section aria-labelledby="menu-section-{{ $loop->index }}">
                <h2 id="menu-section-{{ $loop->index }}" class="flex items-center gap-2.5 text-lg">
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-leaf-sm bg-sprout-soft text-leaf" aria-hidden="true">
                        <x-icon :name="\App\Models\Dish::SECTION_ICONS[$section] ?? 'sprout'" class="size-4.5" />
                    </span>
                    {{ $section }}
                    <span class="font-sans text-sm font-medium tracking-normal text-muted tabular-nums">{{ $dishes->count() }}</span>
                    <span class="h-px flex-1 bg-sage/20" aria-hidden="true"></span>
                </h2>

                <x-card padding="p-0" class="mt-3">
                    <ul>
                        @foreach ($dishes as $dish)
                            <li id="menu-plato-{{ $dish->id }}" data-item class="flex flex-wrap items-center gap-x-4 gap-y-3 border-b border-sage/15 p-4 last:border-b-0" data-reveal style="--i: {{ min($loop->index, 5) }}">
                                <x-dish-photo :dish="$dish" class="size-16 rounded-leaf transition-[opacity,filter] duration-200 {{ $dish->is_available ? '' : 'opacity-50 grayscale' }}" />

                                <div class="min-w-0 flex-1 basis-56">
                                    <h3 class="text-base {{ $dish->is_available ? '' : 'text-muted' }}">{{ $dish->name }}</h3>
                                    <p class="truncate text-sm text-muted">{{ $dish->description }}</p>
                                    @if ($dish->tags)
                                        <div class="mt-1.5 flex flex-wrap gap-1.5">
                                            @foreach ($dish->tags as $tag)
                                                <x-badge tone="neutral"><x-icon :name="\App\Models\Dish::TAG_ICONS[$tag] ?? 'sprout'" class="size-3.5 text-leaf" />{{ $tag }}</x-badge>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <span class="w-24 shrink-0 text-right font-display font-extrabold tabular-nums">{{ money($dish->price) }}</span>

                                {{-- The switch is the form: one tap marks the dish in or out of stock. --}}
                                <form method="POST" action="{{ route('restaurant.menu.toggle', $dish) }}" class="shrink-0">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" role="switch" aria-checked="{{ $dish->is_available ? 'true' : 'false' }}" class="group flex items-center gap-2.5 rounded-full py-1 pr-1 text-sm font-medium">
                                        <span class="w-20 text-right {{ $dish->is_available ? 'text-leaf' : 'text-muted' }}">{{ $dish->is_available ? 'Disponible' : 'Sin stock' }}</span>
                                        <span class="relative h-6 w-11 rounded-full transition-colors duration-150 {{ $dish->is_available ? 'bg-leaf' : 'bg-sage/30' }}">
                                            <span class="absolute top-0.5 left-0.5 size-5 rounded-full bg-white transition-transform duration-200 ease-out-strong group-active:scale-90 {{ $dish->is_available ? 'translate-x-5' : '' }}"></span>
                                        </span>
                                        <span class="sr-only">{{ $dish->name }}</span>
                                    </button>
                                </form>

                                <div class="flex shrink-0 items-center gap-1">
                                    <a href="{{ route('restaurant.menu.edit', $dish) }}" class="flex size-11 items-center justify-center rounded-full text-muted transition-[transform,background-color,color] duration-150 ease-out-strong hover:bg-page hover:text-leaf active:scale-[0.97]">
                                        <x-icon name="edit" />
                                        <span class="sr-only">Editar {{ $dish->name }}</span>
                                    </a>
                                    <button
                                        type="button"
                                        x-data
                                        @click="$dispatch('open-sheet', { name: 'remove-dish', action: '{{ route('restaurant.menu.destroy', $dish) }}', dish: @js($dish->name) })"
                                        class="flex size-11 items-center justify-center rounded-full text-muted transition-[transform,background-color,color] duration-150 ease-out-strong hover:bg-danger/10 hover:text-danger active:scale-[0.97]"
                                    >
                                        <x-icon name="trash" />
                                        <span class="sr-only">Quitar {{ $dish->name }}</span>
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </x-card>
            </section>
        @endforeach
    </div>

    <x-sheet name="remove-dish" title="Quitar plato">
        <form method="POST" :action="payload.action">
            @csrf
            @method('DELETE')
            <p>Vas a quitar <span class="font-semibold" x-text="payload.dish"></span> del menú. Los pedidos anteriores lo conservan en su detalle.</p>
            <p class="mt-2 text-sm text-muted">Si solo se terminó por hoy, marcalo como sin stock en vez de quitarlo.</p>
            <x-button variant="danger" block class="mt-5">Quitar del menú</x-button>
        </form>
    </x-sheet>
</x-layouts.panel>
