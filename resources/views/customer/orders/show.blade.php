@use('App\Enums\OrderStatus')

@php
    $eta = $order->created_at->copy()->addMinutes($order->restaurant->eta_minutes + 10);
@endphp

<x-layouts.customer :title="'Pedido '.$order->code">
    <a href="{{ route('orders.index') }}" class="inline-flex h-11 items-center gap-1.5 rounded-md text-sm font-semibold text-leaf hover:underline">
        <x-icon name="arrow-left" class="size-4" />
        Pedidos
    </a>

    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
        <h1 class="text-3xl leading-[1.1] tabular-nums lg:text-5xl">Pedido {{ $order->code }}</h1>
        <x-badge :tone="$order->status->tone()">{{ $order->status->label() }}</x-badge>
    </div>
    <p class="mt-1 text-muted">
        <a href="{{ route('restaurants.show', $order->restaurant) }}" class="font-semibold text-leaf hover:underline">{{ $order->restaurant->name }}</a>,
        {{ $order->created_at->translatedFormat('j \d\e F \a \l\a\s H:i') }} h
    </p>

    <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:items-start">
        <div class="space-y-5">
            @if ($order->status === OrderStatus::Rejected)
                <div class="rounded-2xl border border-danger/30 bg-danger/10 p-5" role="alert">
                    <h2 class="text-lg text-danger">El restaurante rechazó el pedido</h2>
                    <p class="mt-1">{{ $order->rejection_reason }}</p>
                    <p class="mt-1 text-sm">No se te cobró nada. Podés pedir en otro restaurante.</p>
                    <x-button :href="route('restaurants.index')" variant="secondary" class="mt-4">Ver restaurantes</x-button>
                </div>
            @else
                <x-card>
                    @if ($order->status->isOpen())
                        <p class="text-muted">Llega aproximadamente a las</p>
                        <p class="font-display text-4xl font-extrabold tabular-nums">{{ $eta->format('H:i') }} h</p>
                    @else
                        <p class="font-display text-2xl font-extrabold">Pedido entregado</p>
                    @endif

                    <x-status-stepper :order="$order" class="mt-6" />
                </x-card>

                @if ($order->leaves_earned > 0)
                    <x-leaf-fall :once="'leaf-fall-order-'.$order->id" />
                    <div class="flex items-center gap-3 rounded-leaf bg-sprout-soft p-4">
                        <x-leaf class="size-8 text-leaf" />
                        <p>
                            <span class="font-display font-bold">Sumaste {{ $order->leaves_earned }} Hojitas Verdes</span>
                            <a href="{{ route('leaves.index') }}" class="block text-sm font-semibold text-leaf hover:underline">Ver mis hojitas</a>
                        </p>
                    </div>
                @endif

                @if ($order->courier && in_array($order->status, [OrderStatus::Preparing, OrderStatus::OnTheWay]))
                    <x-card class="flex items-center gap-4">
                        <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-leaf/12 font-display text-lg font-extrabold text-leaf" aria-hidden="true">{{ mb_substr($order->courier->name, 0, 1) }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-muted">{{ $order->status === OrderStatus::OnTheWay ? 'Lo lleva' : 'Lo va a llevar' }}</p>
                            <p class="font-display font-bold">{{ $order->courier->name }}</p>
                            <p class="text-sm text-muted">{{ $order->courier->vehicle }}</p>
                        </div>
                        <a href="tel:{{ preg_replace('/\D/', '', $order->courier->phone) }}" class="flex size-11 shrink-0 items-center justify-center rounded-full border border-sage/30 text-leaf transition-[transform,border-color] duration-150 ease-out-strong hover:border-leaf active:scale-[0.97]">
                            <x-icon name="phone" />
                            <span class="sr-only">Llamar a {{ $order->courier->firstName() }}</span>
                        </a>
                    </x-card>
                @endif
            @endif
        </div>

        <x-card>
            <h2 class="text-lg">Detalle</h2>
            <ul class="mt-3 space-y-1.5">
                @foreach ($order->items as $item)
                    <li class="flex justify-between gap-3">
                        <span><span class="tabular-nums">{{ $item->quantity }} ×</span> {{ $item->name }}</span>
                        <span class="shrink-0 tabular-nums">{{ money($item->lineTotal()) }}</span>
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
                @if ($order->discount > 0)
                    <div class="flex justify-between font-semibold text-leaf">
                        <dt class="flex items-center gap-1.5"><x-leaf class="size-4" /> {{ \App\Services\Loyalty::label($order->benefit) }} por tu nivel</dt>
                        <dd>− {{ money($order->discount) }}</dd>
                    </div>
                @endif
                <div class="flex justify-between border-t border-sage/15 pt-3 font-display text-lg font-extrabold">
                    <dt>Total</dt>
                    <dd>{{ money($order->total) }}</dd>
                </div>
            </dl>

            <dl class="mt-5 space-y-3 border-t border-sage/15 pt-4 text-[15px]">
                <div class="flex gap-3">
                    <dt><x-icon name="pin" class="mt-0.5 text-muted" /><span class="sr-only">Dirección</span></dt>
                    <dd>
                        {{ $order->delivery_address }}
                        @if ($order->delivery_notes)
                            <span class="block text-sm text-muted">{{ $order->delivery_notes }}</span>
                        @endif
                    </dd>
                </div>
                <div class="flex gap-3">
                    <dt><x-icon name="box" class="mt-0.5 text-muted" /><span class="sr-only">Envases</span></dt>
                    <dd>{{ $order->returnable_containers ? 'Envases retornables' : 'Envases descartables compostables' }}</dd>
                </div>
                @if ($order->payment)
                    <div class="flex gap-3">
                        <dt><x-icon :name="$order->payment->method === 'card' ? 'card' : 'wallet'" class="mt-0.5 text-muted" /><span class="sr-only">Pago</span></dt>
                        <dd>
                            {{ $order->payment->methodLabel() }}
                            <a href="{{ route('orders.receipt', $order) }}" class="block text-sm font-semibold text-leaf hover:underline">Ver comprobante</a>
                        </dd>
                    </div>
                @endif
            </dl>
        </x-card>
    </div>
</x-layouts.customer>
