@php
    $groups = [
        ['title' => 'En Brotá', 'icon' => 'store', 'chip' => 'bg-sprout-soft text-leaf', 'restaurants' => $active, 'on' => true],
        ['title' => 'Dados de baja', 'icon' => 'x', 'chip' => 'bg-sage/12 text-muted', 'restaurants' => $inactive, 'on' => false],
    ];
@endphp

<x-layouts.panel title="Restaurantes" subtitle="Los locales que venden en Brotá.">
    <x-slot:actions>
        <x-button type="button" x-data @click="$dispatch('open-sheet', { name: 'new-restaurant' })">
            <x-icon name="plus" class="size-4" />
            Dar de alta
        </x-button>
    </x-slot:actions>

    <div class="grid grid-cols-3 gap-3 lg:gap-4">
        <x-stat-tile icon="store" label="En Brotá" :value="$active->count()" data-reveal />
        <x-stat-tile icon="receipt" label="Pedidos del mes" :value="$monthOrders" tone="accent" data-reveal style="--i: 1" />
        <x-stat-tile icon="x" label="De baja" :value="$inactive->count()" tone="quiet" data-reveal style="--i: 2" />
    </div>

    @foreach ($groups as $group)
        {{-- Both lists are always there, so a restaurant can glide from one to the other when its switch is flipped. --}}
        <section @class(['mt-8', 'hidden' => $group['restaurants']->isEmpty() && ! $group['on']]) aria-labelledby="group-{{ $loop->index }}">
            <h2 id="group-{{ $loop->index }}" class="flex items-center gap-2.5 text-lg">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-leaf-sm {{ $group['chip'] }}" aria-hidden="true"><x-icon :name="$group['icon']" class="size-4.5" /></span>
                {{ $group['title'] }}
                <span data-pop class="font-sans text-sm font-medium tracking-normal text-muted tabular-nums">{{ $group['restaurants']->count() }}</span>
                <span class="h-px flex-1 bg-sage/20" aria-hidden="true"></span>
            </h2>

            <x-card padding="p-0" class="mt-3">
                <ul>
                    @forelse ($group['restaurants'] as $restaurant)
                        <li id="local-{{ $restaurant->id }}" data-item class="flex flex-wrap items-center gap-x-4 gap-y-3 border-b border-sage/15 p-4 last:border-b-0" data-reveal style="--i: {{ min($loop->index, 5) }}">
                            <x-cover :restaurant="$restaurant" class="size-16 shrink-0 rounded-leaf transition-[opacity,filter] duration-200 {{ $restaurant->is_active ? '' : 'opacity-50 grayscale' }}" />

                            <div class="min-w-0 flex-1 basis-56">
                                <h3 class="text-base {{ $restaurant->is_active ? '' : 'text-muted' }}">{{ $restaurant->name }}</h3>
                                <p class="truncate text-sm text-muted">{{ $restaurant->address }}, {{ $restaurant->neighborhood }}</p>
                                <p class="truncate text-sm text-muted">A cargo de {{ $restaurant->owner->name }}, {{ $restaurant->owner->email }}</p>
                                <div class="mt-1.5 flex flex-wrap gap-1.5">
                                    <x-badge tone="neutral"><x-icon :name="$restaurant->category->icon()" class="size-3.5 text-leaf" />{{ $restaurant->category->name }}</x-badge>
                                    @if ($restaurant->dishes_count === 0)
                                        <x-badge tone="accent">Sin menú cargado</x-badge>
                                    @endif
                                </div>
                            </div>

                            <dl class="flex shrink-0 gap-5 text-sm">
                                <div>
                                    <dt class="text-muted">Platos</dt>
                                    <dd class="font-display text-lg font-extrabold tabular-nums">{{ $restaurant->dishes_count }}</dd>
                                </div>
                                <div>
                                    <dt class="text-muted">Pedidos del mes</dt>
                                    <dd class="font-display text-lg font-extrabold tabular-nums">{{ $restaurant->month_orders }}</dd>
                                </div>
                                <div>
                                    <dt class="text-muted">Puntaje</dt>
                                    <dd class="font-display text-lg font-extrabold tabular-nums">{{ $restaurant->rating > 0 ? number_format($restaurant->rating, 1, ',') : 'Nuevo' }}</dd>
                                </div>
                            </dl>

                            <x-switch-form :action="route('admin.restaurants.toggle', $restaurant)" :on="$restaurant->is_active" :name="$restaurant->name" on-label="En Brotá" class="ml-auto" />
                        </li>
                    @empty
                        <li>
                            <x-empty-state title="Todavía no hay restaurantes">
                                Dá de alta el primero para que los clientes tengan dónde pedir.
                            </x-empty-state>
                        </li>
                    @endforelse
                </ul>
            </x-card>
        </section>
    @endforeach

    <x-sheet name="new-restaurant" title="Dar de alta un restaurante">
        <form method="POST" action="{{ route('admin.restaurants.store') }}" class="space-y-4">
            @csrf

            <x-input label="Nombre del local" name="restaurant_name" placeholder="La Huerta de Palermo" required />
            <x-select label="Tipo de cocina" name="category_id" :options="$categories" placeholder="Elegí una opción" required />
            <x-input label="En una línea, ¿qué ofrece?" name="tagline" placeholder="Cocina de estación con verduras de la huerta" required />
            <div class="grid grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)] gap-3">
                <x-input label="Dirección" name="address" autocomplete="off" required />
                <x-input label="Barrio" name="neighborhood" required />
            </div>

            <p class="flex items-center gap-3 pt-2 text-sm font-semibold text-muted">
                Quién lo maneja
                <span class="h-px flex-1 bg-sage/20" aria-hidden="true"></span>
            </p>
            <x-input label="Nombre y apellido" name="owner_name" autocomplete="off" required />
            <x-input label="Email para ingresar" name="email" type="email" autocomplete="off" required />
            <x-input label="Contraseña inicial" name="password" type="password" autocomplete="new-password" hint="Al menos 8 caracteres. Pasásela a quien maneja el local." required />

            <x-button block size="lg">Dar de alta</x-button>
        </form>
    </x-sheet>
</x-layouts.panel>
