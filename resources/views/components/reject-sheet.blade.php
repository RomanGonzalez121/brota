@php
    $reasons = [
        'Nos quedamos sin stock de un plato del pedido.',
        'Tenemos demasiada demora en la cocina.',
        'Estamos por cerrar.',
    ];
@endphp

{{-- Asks why before rejecting: the customer reads this reason on their order. --}}
<x-sheet name="reject" title="Rechazar pedido">
    <form method="POST" :action="payload.action" x-data="{ reason: '' }">
        @csrf
        <p class="text-muted">El cliente va a leer el motivo en su pedido <span class="font-semibold tabular-nums" x-text="payload.code"></span>. No se le cobra nada.</p>

        <div class="mt-4 flex flex-wrap gap-2">
            @foreach ($reasons as $reason)
                <button type="button" @click="reason = @js($reason)" class="rounded-full border border-sage/25 bg-surface px-3 py-1.5 text-left text-sm font-medium transition-[transform,border-color] duration-150 ease-out-strong hover:border-leaf active:scale-[0.97]">{{ $reason }}</button>
            @endforeach
        </div>

        <div class="mt-4">
            <x-textarea label="Motivo" name="reason" rows="3" x-model="reason" required />
        </div>

        <x-button variant="danger" block class="mt-5">Rechazar pedido</x-button>
    </form>
</x-sheet>
