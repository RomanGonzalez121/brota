@php
    $groups = [
        ['title' => 'Repartiendo en Brotá', 'icon' => 'bike', 'chip' => 'bg-sprout-soft text-leaf', 'couriers' => $active, 'on' => true],
        ['title' => 'Dados de baja', 'icon' => 'x', 'chip' => 'bg-sage/12 text-muted', 'couriers' => $inactive, 'on' => false],
    ];
@endphp

<x-layouts.panel title="Repartidores" subtitle="Quiénes llevan los pedidos.">
    <x-slot:actions>
        <x-button type="button" x-data @click="$dispatch('open-sheet', { name: 'new-courier' })">
            <x-icon name="plus" class="size-4" />
            Dar de alta
        </x-button>
    </x-slot:actions>

    <div class="grid grid-cols-3 gap-3 lg:gap-4">
        <x-stat-tile icon="users" label="Activos" :value="$active->count()" data-reveal />
        <x-stat-tile icon="bike" label="En la calle ahora" :value="$onTheStreet" tone="accent" data-reveal style="--i: 1" />
        <x-stat-tile icon="x" label="De baja" :value="$inactive->count()" tone="quiet" data-reveal style="--i: 2" />
    </div>

    @foreach ($groups as $group)
        {{-- Both lists are always there, so a courier can glide from one to the other when the switch is flipped. --}}
        <section @class(['mt-8', 'hidden' => $group['couriers']->isEmpty() && ! $group['on']]) aria-labelledby="group-{{ $loop->index }}">
            <h2 id="group-{{ $loop->index }}" class="flex items-center gap-2.5 text-lg">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-leaf-sm {{ $group['chip'] }}" aria-hidden="true"><x-icon :name="$group['icon']" class="size-4.5" /></span>
                {{ $group['title'] }}
                <span data-pop class="font-sans text-sm font-medium tracking-normal text-muted tabular-nums">{{ $group['couriers']->count() }}</span>
                <span class="h-px flex-1 bg-sage/20" aria-hidden="true"></span>
            </h2>

            <x-card padding="p-0" class="mt-3">
                <ul>
                    @forelse ($group['couriers'] as $courier)
                        <li id="repartidor-{{ $courier->id }}" data-item class="flex flex-wrap items-center gap-x-4 gap-y-3 border-b border-sage/15 p-4 last:border-b-0" data-reveal style="--i: {{ min($loop->index, 5) }}">
                            <span class="flex size-12 shrink-0 items-center justify-center rounded-leaf-sm font-display text-lg font-extrabold {{ $courier->is_active ? 'bg-leaf/12 text-leaf' : 'bg-sage/12 text-muted' }}" aria-hidden="true">{{ mb_substr($courier->name, 0, 1) }}</span>

                            <div class="min-w-0 flex-1 basis-56">
                                <h3 class="text-base {{ $courier->is_active ? '' : 'text-muted' }}">{{ $courier->name }}</h3>
                                <p class="truncate text-sm text-muted">{{ collect([$courier->phone, $courier->email])->filter()->join(', ') }}</p>
                                <div class="mt-1.5 flex flex-wrap gap-1.5">
                                    @if ($courier->vehicle)
                                        <x-badge tone="neutral"><x-icon name="bike" class="size-3.5 text-leaf" />{{ $courier->vehicle }}</x-badge>
                                    @endif
                                    @if ($courier->open_deliveries > 0)
                                        <x-badge tone="accent">{{ $courier->open_deliveries === 1 ? '1 entrega en curso' : $courier->open_deliveries.' entregas en curso' }}</x-badge>
                                    @endif
                                </div>
                            </div>

                            <dl class="shrink-0 text-sm">
                                <dt class="text-muted">Entregas del mes</dt>
                                <dd class="font-display text-lg font-extrabold tabular-nums">{{ $courier->month_deliveries }}</dd>
                            </dl>

                            <x-switch-form :action="route('admin.couriers.toggle', $courier)" :on="$courier->is_active" :name="$courier->name" class="ml-auto" />
                        </li>
                    @empty
                        <li>
                            <x-empty-state title="Todavía no hay repartidores">
                                Dá de alta el primero para que los pedidos tengan quién los lleve.
                            </x-empty-state>
                        </li>
                    @endforelse
                </ul>
            </x-card>
        </section>
    @endforeach

    <x-sheet name="new-courier" title="Dar de alta un repartidor">
        <form method="POST" action="{{ route('admin.couriers.store') }}" class="space-y-4">
            @csrf

            <x-input label="Nombre y apellido" name="name" autocomplete="off" required />
            <x-input label="Teléfono" name="phone" type="tel" autocomplete="off" placeholder="11 5555-1234" required />
            <x-select label="Con qué reparte" name="vehicle" :options="$vehicles" placeholder="Elegí una opción" required />
            <x-input label="Email para ingresar" name="email" type="email" autocomplete="off" required />
            <x-input label="Contraseña inicial" name="password" type="password" autocomplete="new-password" hint="Al menos 8 caracteres. Pasásela al repartidor." required />

            <x-button block size="lg">Dar de alta</x-button>
        </form>
    </x-sheet>
</x-layouts.panel>
