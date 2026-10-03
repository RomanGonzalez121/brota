@use('App\Models\Setting')

@php
    $number = fn (string $key) => rtrim(rtrim(number_format($values[$key], 1, '.', ''), '0'), '.');

    // The three levels that are reached by collecting: where each starts and what it gives.
    $rows = [
        ['level' => $levels[1], 'min' => Setting::LEVEL_SPROUT_MIN, 'deliveries' => Setting::SPROUT_FREE_DELIVERIES],
        ['level' => $levels[2], 'min' => Setting::LEVEL_PLANT_MIN, 'deliveries' => Setting::PLANT_FREE_DELIVERIES],
        ['level' => $levels[3], 'min' => Setting::LEVEL_TREE_MIN, 'deliveries' => Setting::TREE_FREE_DELIVERIES],
    ];
@endphp

<x-layouts.panel title="Hojitas Verdes" subtitle="Cómo se ganan, dónde empieza cada nivel y qué da cada uno.">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        <x-card as="section" aria-labelledby="earn-title" data-reveal>
            <h2 id="earn-title" class="flex items-center gap-2.5 text-lg">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-leaf-sm bg-sprout-soft text-leaf" aria-hidden="true"><x-leaf class="size-4.5" /></span>
                Cómo se ganan
            </h2>
            <p class="mt-1 text-muted">Se acreditan cuando el pedido se entrega.</p>

            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <x-input label="Por pedido cercano" :name="Setting::LEAVES_NEARBY" type="number" min="0" inputmode="numeric" :value="$number(Setting::LEAVES_NEARBY)" hint="Hojitas por pedido." required />
                <x-input label="Hasta qué distancia es cerca" :name="Setting::NEARBY_MAX_KM" type="number" min="0.5" step="0.5" inputmode="decimal" :value="$number(Setting::NEARBY_MAX_KM)" hint="En kilómetros." required />
                <x-input label="Por envases retornables" :name="Setting::LEAVES_CONTAINERS" type="number" min="0" inputmode="numeric" :value="$number(Setting::LEAVES_CONTAINERS)" hint="Hojitas por pedido." required />
            </div>
        </x-card>

        <x-card as="section" aria-labelledby="levels-title" data-reveal style="--i: 1">
            <h2 id="levels-title" class="flex items-center gap-2.5 text-lg">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-leaf-sm bg-sprout-soft text-leaf" aria-hidden="true"><x-icon name="tree" class="size-4.5" /></span>
                Niveles
            </h2>
            <p class="mt-1 text-muted">Las hojitas no se gastan, así que nadie baja de nivel. Los envíos gratis se renuevan cada mes.</p>

            <ul class="mt-2">
                <li class="flex items-center gap-3 border-b border-sage/15 py-4">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-leaf-sm bg-sage/12 text-muted" aria-hidden="true"><x-icon :name="$levels[0]['icon']" /></span>
                    <div>
                        <p class="font-display text-lg leading-tight font-extrabold">{{ $levels[0]['name'] }}</p>
                        <p class="text-sm text-muted">Donde empieza todo cliente. No tiene beneficios.</p>
                    </div>
                </li>

                @foreach ($rows as $row)
                    <li class="grid grid-cols-1 gap-x-4 gap-y-3 border-b border-sage/15 py-4 last:border-b-0 last:pb-0 sm:grid-cols-[minmax(0,1fr)_10rem_10rem] sm:items-start">
                        <div class="flex items-center gap-3 sm:pt-6">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-leaf-sm bg-sprout-soft text-leaf" aria-hidden="true"><x-icon :name="$row['level']['icon']" /></span>
                            <p class="font-display text-lg leading-tight font-extrabold">{{ $row['level']['name'] }}</p>
                        </div>
                        <x-input :label="'Desde cuántas hojitas'" :name="$row['min']" type="number" min="1" inputmode="numeric" :value="$number($row['min'])" :aria-label="'Hojitas para llegar a '.$row['level']['name']" required />
                        <x-input :label="'Envíos gratis por mes'" :name="$row['deliveries']" type="number" min="0" inputmode="numeric" :value="$number($row['deliveries'])" :aria-label="'Envíos gratis por mes en '.$row['level']['name']" required />
                    </li>
                @endforeach
            </ul>
        </x-card>

        <x-card as="section" aria-labelledby="free-order-title" data-reveal style="--i: 2">
            <h2 id="free-order-title" class="flex items-center gap-2.5 text-lg">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-leaf-sm bg-carrot/25 text-ink" aria-hidden="true"><x-icon name="receipt" class="size-4.5" /></span>
                Pedido gratis de {{ $levels[3]['name'] }}
            </h2>
            <p class="mt-1 text-muted">El beneficio del nivel más alto: Brotá paga los platos hasta el tope y el envío. El restaurante y el repartidor cobran igual.</p>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-input label="Pedidos gratis por mes" :name="Setting::TREE_FREE_ORDERS" type="number" min="0" inputmode="numeric" :value="$number(Setting::TREE_FREE_ORDERS)" hint="Poné 0 para no darlo." required />
                <x-input label="Tope de platos por pedido" :name="Setting::FREE_ORDER_CAP" type="number" min="0" step="500" inputmode="numeric" :value="$number(Setting::FREE_ORDER_CAP)" hint="En pesos. Lo que pase del tope lo paga el cliente." required />
            </div>
        </x-card>

        <div class="flex justify-end">
            <x-button size="lg">
                <x-icon name="check" />
                Guardar reglas
            </x-button>
        </div>
    </form>

    <section class="mt-10" aria-labelledby="today-title">
        <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1" data-reveal>
            <h2 id="today-title" class="text-lg">Así están hoy</h2>
            <p class="text-sm text-muted tabular-nums">{{ $customers === 1 ? '1 cliente' : $customers.' clientes' }} en total, con las reglas guardadas</p>
        </div>
        <x-level-ladder :levels="$levels" :free-order-cap="$freeOrderCap" :counts="$customersByLevel" class="mt-4" />
    </section>
</x-layouts.panel>
