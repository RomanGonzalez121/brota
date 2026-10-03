@props(['order'])

@use('App\Enums\OrderStatus')

@php
    $steps = OrderStatus::timeline();
    $current = $order->status->position();
    $times = $order->statusLogs->keyBy(fn ($log) => $log->status->value);
@endphp

{{-- The order grows like a plant: each step reached sprouts a leaf from the stem. --}}
<ol {{ $attributes->class('relative') }}>
    @foreach ($steps as $index => $step)
        @php
            $reached = $index <= $current;
            $isCurrent = $index === $current && $order->status->isOpen();
        @endphp
        <li class="relative flex gap-4 pb-7 last:pb-0" @if ($isCurrent) aria-current="step" @endif>
            @unless ($loop->last)
                <span class="absolute top-9 bottom-0 left-[1.0625rem] w-0.5 rounded-full {{ $index < $current ? 'bg-leaf' : 'bg-sage/25' }}" aria-hidden="true"></span>
            @endunless

            <span class="relative flex size-9 shrink-0 items-center justify-center">
                @if ($isCurrent)
                    <span class="absolute inset-0 animate-breathe rounded-full bg-sprout" aria-hidden="true"></span>
                @endif
                @if ($reached)
                    <span class="relative flex size-9 origin-bottom-left animate-sprout items-center justify-center rounded-full {{ $isCurrent ? 'bg-sprout' : 'bg-leaf/12' }}" style="animation-delay: {{ $index * 70 }}ms">
                        <x-leaf class="size-5 {{ $isCurrent ? 'text-forest' : 'text-leaf' }}" />
                    </span>
                @else
                    <span class="size-3 rounded-full border-2 border-sage/40 bg-cream"></span>
                @endif
            </span>

            <div class="min-w-0 pt-1.5">
                <p class="font-display font-bold {{ $reached ? '' : 'text-sage' }}">{{ $step->label() }}</p>
                @if ($reached)
                    <p class="text-sm text-sage">
                        {{ $step->hint() }}
                        @if ($times->has($step->value))
                            <time datetime="{{ $times[$step->value]->created_at->toIso8601String() }}" class="whitespace-nowrap tabular-nums">{{ $times[$step->value]->created_at->format('H:i') }} h</time>
                        @endif
                    </p>
                @endif
            </div>
        </li>
    @endforeach
</ol>
