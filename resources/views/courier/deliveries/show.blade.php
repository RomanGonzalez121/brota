@use('App\Enums\OrderStatus')

@php
    $waiting = $order->status === OrderStatus::Preparing;
    $onTheWay = $order->status === OrderStatus::OnTheWay;
    $delivered = $order->status === OrderStatus::Delivered;
    $pickedUp = $onTheWay || $delivered;
    $phone = preg_replace('/\D/', '', (string) $order->customer->phone);
    $deliveredAt = $order->statusLogs->firstWhere('status', OrderStatus::Delivered)?->created_at ?? $order->updated_at;
@endphp

<x-layouts.courier :title="'Entrega '.$order->code" :back="route('courier.deliveries.index')">
    <h1 class="text-3xl leading-[1.1] tabular-nums">Entrega {{ $order->code }}</h1>
    <div class="mt-2 flex items-center gap-2">
        <x-badge :tone="$order->status->tone()">{{ $waiting ? 'Para retirar' : $order->status->label() }}</x-badge>
        @unless ($delivered)
            <x-elapsed :since="$order->updated_at" :late-after="$onTheWay ? 30 : 20" :prefix="$onTheWay ? 'En camino hace' : 'Esperando hace'" />
        @endunless
    </div>

    @if ($delivered)
        {{-- The trip is over: say so, and say what it left. --}}
        <section class="mt-4 flex items-center gap-4 rounded-leaf bg-sprout-soft p-5" aria-labelledby="done-title">
            <x-seedling class="shrink-0" />
            <div>
                <h2 id="done-title" class="text-xl">Entrega completada</h2>
                <p class="mt-1">
                    Se la dejaste a {{ $order->customer->firstName() }} a las <span class="tabular-nums">{{ $deliveredAt->format('H:i') }} h</span>.
                    Sumaste <span class="font-semibold tabular-nums">{{ money($order->delivery_fee) }}</span>.
                </p>
                <x-button :href="route('courier.deliveries.index')" variant="secondary" size="sm" class="mt-3 h-10">Volver a mis entregas</x-button>
            </div>
        </section>
    @endif

    <x-route-map :order="$order" animated class="mt-4 rounded-leaf" />

    {{-- The two stops. The one that comes next is in full colour; the one already done is ticked. --}}
    <x-card class="mt-4">
        <ol class="relative space-y-6">
            <span class="absolute top-10 bottom-16 left-[1.1rem] border-l-2 border-dotted border-sage/40" aria-hidden="true"></span>

            <li class="relative flex items-start gap-3.5" @if ($waiting) aria-current="step" @endif>
                <span class="relative flex size-9 shrink-0 items-center justify-center" aria-hidden="true">
                    @if ($waiting)
                        <span class="absolute inset-0 animate-breathe rounded-leaf-sm bg-leaf"></span>
                    @endif
                    <span class="relative flex size-9 items-center justify-center rounded-leaf-sm {{ $pickedUp ? 'bg-sage/12 text-muted' : 'bg-leaf text-on-leaf' }}">
                        <x-icon :name="$pickedUp ? 'check' : 'store'" class="size-4.5" />
                    </span>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm text-muted">{{ $pickedUp ? 'Retiraste en' : 'Retirá en' }}</p>
                    <p class="font-display text-lg leading-tight font-bold">{{ $order->restaurant->name }}</p>
                    <p class="text-[15px]">{{ $order->restaurant->address }}, {{ $order->restaurant->neighborhood }}</p>
                    @if ($waiting)
                        <p class="mt-1 text-sm text-muted">Pedí el pedido <span class="font-semibold tabular-nums">{{ $order->code }}</span> en el mostrador.</p>
                    @endif
                </div>
            </li>

            <li class="relative flex items-start gap-3.5" @if ($onTheWay) aria-current="step" @endif>
                <span class="relative flex size-9 shrink-0 items-center justify-center" aria-hidden="true">
                    @if ($onTheWay)
                        <span class="absolute inset-0 animate-breathe rounded-leaf-sm bg-leaf"></span>
                    @endif
                    <span class="relative flex size-9 items-center justify-center rounded-leaf-sm {{ $onTheWay ? 'bg-leaf text-on-leaf' : 'bg-sage/12 text-muted' }}">
                        <x-icon :name="$delivered ? 'check' : 'home'" class="size-4.5" />
                    </span>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm text-muted">{{ $delivered ? 'Entregaste a' : 'Entregá a' }}</p>
                    <p class="font-display text-lg leading-tight font-bold">{{ $order->customer->name }}</p>
                    <p class="text-[15px]">{{ $order->delivery_address }}</p>
                    @if ($order->delivery_notes)
                        <p class="mt-1 text-sm text-muted">{{ $order->delivery_notes }}</p>
                    @endif
                </div>
                @if ($phone && ! $delivered)
                    <a href="tel:{{ $phone }}" class="flex size-11 shrink-0 items-center justify-center rounded-full border border-sage/30 text-leaf transition-[transform,border-color] duration-150 ease-out-strong hover:border-leaf active:scale-[0.97]">
                        <x-icon name="phone" />
                        <span class="sr-only">Llamar a {{ $order->customer->firstName() }}</span>
                    </a>
                @endif
            </li>
        </ol>
    </x-card>

    <x-card class="mt-4">
        <h2 class="text-lg">Qué llevás</h2>
        <ul class="mt-3 space-y-1.5">
            @foreach ($order->items as $item)
                <li class="flex items-baseline gap-2">
                    <span class="min-w-6 shrink-0 font-display font-extrabold tabular-nums">{{ $item->quantity }}×</span>
                    <span>{{ $item->name }}</span>
                </li>
            @endforeach
        </ul>

        <dl class="mt-4 space-y-3 border-t border-sage/15 pt-4 text-[15px]">
            <div class="flex gap-3">
                <dt><x-icon name="box" class="mt-0.5 text-muted" /><span class="sr-only">Envases</span></dt>
                <dd>
                    {{ $order->returnable_containers ? 'Envases retornables' : 'Envases descartables compostables' }}
                    @if ($order->returnable_containers)
                        <span class="block text-sm text-muted">Son del local: cuidalos en el viaje.</span>
                    @endif
                </dd>
            </div>
            @if ($order->payment)
                <div class="flex gap-3">
                    <dt><x-icon :name="$order->payment->method === 'card' ? 'card' : 'wallet'" class="mt-0.5 text-muted" /><span class="sr-only">Pago</span></dt>
                    <dd>
                        Ya está pago
                        <span class="block text-sm text-muted">{{ $order->payment->methodLabel() }}. No tenés que cobrar nada.</span>
                    </dd>
                </div>
            @endif
            <div class="flex gap-3">
                <dt><x-icon name="bike" class="mt-0.5 text-muted" /><span class="sr-only">Tu ganancia</span></dt>
                <dd>
                    {{ $delivered ? 'Ganaste' : 'Ganás' }} <span class="font-semibold tabular-nums">{{ money($order->delivery_fee) }}</span> por este envío
                </dd>
            </div>
        </dl>
    </x-card>

    @unless ($delivered)
        {{-- The next step stays within reach of the thumb while the rest of the screen scrolls. --}}
        <form method="POST" action="{{ route($onTheWay ? 'courier.deliveries.deliver' : 'courier.deliveries.pick-up', $order) }}" class="sticky bottom-4 z-10 mt-6">
            @csrf
            <x-button block size="lg" :variant="$onTheWay ? 'accent' : 'primary'" class="shadow-lift">
                <x-icon :name="$onTheWay ? 'check' : 'bike'" />
                {{ $onTheWay ? 'Entregué el pedido' : 'Retiré el pedido' }}
            </x-button>
        </form>
    @endunless
</x-layouts.courier>
