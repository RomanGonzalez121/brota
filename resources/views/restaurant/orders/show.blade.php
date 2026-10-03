@use('App\Enums\OrderStatus')

<x-layouts.panel :title="'Pedido '.$order->code" :subtitle="'Recibido el '.$order->created_at->translatedFormat('j \d\e F \a \l\a\s H:i').' h'">
    <x-slot:actions>
        <x-button :href="route('restaurant.orders.index')" variant="secondary">
            <x-icon name="arrow-left" class="size-4" />
            Todos los pedidos
        </x-button>
    </x-slot:actions>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)] lg:items-start">
        <div class="space-y-5">
            <x-card>
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg">Qué pidió</h2>
                    <x-badge :tone="$order->status->tone()">{{ $order->status->label() }}</x-badge>
                </div>

                <ul class="mt-3 space-y-2">
                    @foreach ($order->items as $item)
                        <li class="flex justify-between gap-3 text-[17px]">
                            <span><span class="font-display font-extrabold tabular-nums">{{ $item->quantity }} ×</span> {{ $item->name }}</span>
                            <span class="shrink-0 text-muted tabular-nums">{{ money($item->lineTotal()) }}</span>
                        </li>
                    @endforeach
                </ul>

                <dl class="mt-4 space-y-2 border-t border-sage/15 pt-4 tabular-nums">
                    <div class="flex justify-between">
                        <dt class="text-muted">Subtotal</dt>
                        <dd>{{ money($order->subtotal) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-muted">Envío</dt>
                        <dd>{{ money($order->delivery_fee) }}</dd>
                    </div>
                    <div class="flex justify-between border-t border-sage/15 pt-3 font-display text-lg font-extrabold">
                        <dt>Total</dt>
                        <dd>{{ money($order->total) }}</dd>
                    </div>
                </dl>

                @if ($order->returnable_containers)
                    <p class="mt-4 flex items-center gap-2 rounded-leaf-sm bg-sprout-soft px-3 py-2 text-sm font-medium">
                        <x-icon name="box" class="size-4" />
                        Va en envases retornables.
                    </p>
                @endif
            </x-card>

            <x-card>
                <h2 class="text-lg">Entrega</h2>
                <dl class="mt-3 space-y-3 text-[15px]">
                    <div class="flex gap-3">
                        <dt><x-icon name="user" class="mt-0.5 text-muted" /><span class="sr-only">Cliente</span></dt>
                        <dd>
                            {{ $order->customer->name }}
                            <span class="block text-sm text-muted">{{ $order->customer->phone }}</span>
                        </dd>
                    </div>
                    <div class="flex gap-3">
                        <dt><x-icon name="pin" class="mt-0.5 text-muted" /><span class="sr-only">Dirección</span></dt>
                        <dd>
                            {{ $order->delivery_address }}
                            @if ($order->delivery_notes)
                                <span class="block text-sm text-muted">{{ $order->delivery_notes }}</span>
                            @endif
                        </dd>
                    </div>
                    @if ($order->courier)
                        <div class="flex gap-3">
                            <dt><x-icon name="bike" class="mt-0.5 text-muted" /><span class="sr-only">Repartidor</span></dt>
                            <dd>
                                {{ $order->courier->name }}
                                <span class="block text-sm text-muted">{{ $order->courier->vehicle }}, {{ $order->courier->phone }}</span>
                            </dd>
                        </div>
                    @endif
                    @if ($order->payment)
                        <div class="flex gap-3">
                            <dt><x-icon :name="$order->payment->method === 'card' ? 'card' : 'wallet'" class="mt-0.5 text-muted" /><span class="sr-only">Pago</span></dt>
                            <dd>Pagado: {{ $order->payment->methodLabel() }}</dd>
                        </div>
                    @endif
                </dl>
            </x-card>
        </div>

        <div class="space-y-5">
            @if ($order->status === OrderStatus::Rejected)
                <div class="rounded-media border border-danger/30 bg-danger/10 p-5">
                    <h2 class="text-lg text-danger">Rechazaste este pedido</h2>
                    <p class="mt-1">{{ $order->rejection_reason }}</p>
                </div>
            @else
                <x-card>
                    <h2 class="text-lg">Estado</h2>
                    <x-status-stepper :order="$order" class="mt-4" />

                    <div class="mt-6 flex flex-wrap gap-2 border-t border-sage/15 pt-5">
                        @if ($order->status === OrderStatus::Received)
                            <form method="POST" action="{{ route('restaurant.orders.accept', $order) }}" class="flex-1">
                                @csrf
                                <x-button block size="lg">Aceptar pedido</x-button>
                            </form>
                            <x-button
                                type="button"
                                size="lg"
                                variant="danger"
                                x-data
                                @click="$dispatch('open-sheet', { name: 'reject', action: '{{ route('restaurant.orders.reject', $order) }}', code: '{{ $order->code }}' })"
                            >Rechazar</x-button>
                        @elseif ($order->status->isOpen())
                            <form method="POST" action="{{ route('restaurant.orders.advance', $order) }}" class="flex-1">
                                @csrf
                                <x-button block size="lg" variant="accent">
                                    {{ $order->status === OrderStatus::Preparing ? 'Salió a reparto' : 'Marcar entregado' }}
                                </x-button>
                            </form>
                        @else
                            <p class="text-muted">Pedido cerrado. No quedan acciones.</p>
                        @endif
                    </div>
                </x-card>
            @endif
        </div>
    </div>

    <x-reject-sheet />
</x-layouts.panel>
