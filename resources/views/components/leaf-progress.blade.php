@props(['status'])

@use('App\Enums\OrderStatus')

{{-- Compact version of the status stepper: one leaf per step reached. --}}
<span {{ $attributes->class('inline-flex items-center gap-0.5') }} role="img" aria-label="Paso {{ $status->position() + 1 }} de {{ count(OrderStatus::timeline()) }}: {{ $status->label() }}">
    @foreach (OrderStatus::timeline() as $index => $step)
        @if ($index <= $status->position())
            <x-leaf class="size-4 text-leaf" />
        @else
            <x-leaf outline class="size-4 text-muted/50" />
        @endif
    @endforeach
</span>
