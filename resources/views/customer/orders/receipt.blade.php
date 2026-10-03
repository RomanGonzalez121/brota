<x-layouts.customer :title="'Comprobante '.$order->payment->receipt_number">
    <div class="mx-auto max-w-md">
        <div class="rounded-3xl border border-sage/20 bg-white">
            <div class="flex flex-col items-center px-6 pt-8 pb-6 text-center">
                <span class="flex size-16 origin-bottom-left animate-sprout items-center justify-center rounded-full bg-sprout">
                    <x-icon name="check" class="size-8 text-forest" />
                </span>
                <h1 class="mt-4 text-2xl">Pago aprobado</h1>
                <p class="mt-1 font-display text-4xl font-extrabold tabular-nums">{{ money($order->payment->amount) }}</p>
                <p class="mt-1 text-sage">{{ $order->restaurant->name }}</p>
            </div>

            <dl class="space-y-2.5 border-t border-dashed border-sage/40 px-6 py-5 text-[15px] tabular-nums">
                <div class="flex justify-between gap-4">
                    <dt class="text-sage">Comprobante</dt>
                    <dd>{{ $order->payment->receipt_number }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-sage">Fecha</dt>
                    <dd>{{ $order->payment->created_at->format('d/m/Y H:i') }} h</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-sage">Medio de pago</dt>
                    <dd class="text-right">{{ $order->payment->methodLabel() }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-sage">Pedido</dt>
                    <dd>{{ $order->code }}</dd>
                </div>
            </dl>

            <div class="border-t border-dashed border-sage/40 px-6 py-5">
                <ul class="space-y-1.5 text-[15px]">
                    @foreach ($order->items as $item)
                        <li class="flex justify-between gap-3">
                            <span><span class="tabular-nums">{{ $item->quantity }} ×</span> {{ $item->name }}</span>
                            <span class="shrink-0 tabular-nums">{{ money($item->lineTotal()) }}</span>
                        </li>
                    @endforeach
                    <li class="flex justify-between gap-3">
                        <span>Envío</span>
                        <span class="tabular-nums">{{ money($order->delivery_fee) }}</span>
                    </li>
                </ul>
                <p class="mt-3 flex justify-between border-t border-sage/15 pt-3 font-display text-lg font-extrabold tabular-nums">
                    <span>Total pagado</span>
                    <span>{{ money($order->total) }}</span>
                </p>
            </div>
        </div>

        <div class="mt-5 space-y-2.5">
            <x-button :href="route('orders.show', $order)" size="lg" block>Seguir mi pedido</x-button>
            <x-button :href="route('home')" variant="ghost" block>Volver al inicio</x-button>
        </div>
    </div>
</x-layouts.customer>
