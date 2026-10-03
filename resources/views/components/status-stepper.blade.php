@props(['order'])

@use('App\Enums\OrderStatus')

@php
    $steps = OrderStatus::timeline();
    $current = $order->status->position();
    $times = $order->statusLogs->keyBy(fn ($log) => $log->status->value);
@endphp

{{-- The order grows like a vine: the stem draws itself down to each step reached and sprouts a leaf there. --}}
<ol {{ $attributes->class('relative') }}>
    @foreach ($steps as $index => $step)
        @php
            $reached = $index <= $current;
            $isCurrent = $index === $current && $order->status->isOpen();
            $grown = $index < $current;
        @endphp
        <li class="relative flex gap-4 pb-8 last:pb-0" @if ($isCurrent) aria-current="step" @endif>
            @unless ($loop->last)
                {{-- A soft S-curve instead of a ruler line; stretched to the row, the stroke keeps its width. --}}
                <svg class="absolute top-9 bottom-0 left-0 h-[calc(100%-2.25rem)] w-9 {{ $grown ? 'text-leaf' : 'text-sage/30' }}" viewBox="0 0 36 100" preserveAspectRatio="none" fill="none" aria-hidden="true">
                    <path
                        d="M18 0C27 28 9 68 18 100"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        vector-effect="non-scaling-stroke"
                        @if ($grown)
                            pathLength="1"
                            class="animate-draw [stroke-dasharray:1]"
                            style="animation-delay: {{ $index * 160 + 200 }}ms; animation-duration: 620ms"
                        @endif
                    />
                </svg>

                @if ($grown)
                    <span class="absolute top-[calc(50%+0.75rem)] left-[1.35rem] origin-bottom-left animate-sprout" style="animation-delay: {{ $index * 160 + 520 }}ms" aria-hidden="true">
                        <x-leaf class="size-3.5 rotate-[18deg] text-sprout" />
                    </span>
                @endif
            @endunless

            <span class="relative flex size-9 shrink-0 items-center justify-center">
                @if ($isCurrent)
                    <span class="absolute inset-0 animate-breathe rounded-full bg-sprout" aria-hidden="true"></span>
                @endif
                @if ($reached)
                    <span class="relative flex size-9 origin-bottom-left animate-sprout items-center justify-center rounded-full {{ $isCurrent ? 'bg-sprout' : 'bg-leaf/12' }}" style="animation-delay: {{ $index * 160 }}ms">
                        {{-- The leaf of the step in progress keeps moving, like the rest of the plant. --}}
                        <span @class(['origin-bottom-left', 'animate-sway' => $isCurrent])>
                            <x-leaf class="size-5 {{ $isCurrent ? 'text-forest' : 'text-leaf' }}" />
                        </span>
                    </span>
                @else
                    <span class="size-3 rounded-full border-2 border-sage/40 bg-page"></span>
                @endif
            </span>

            <div class="min-w-0 pt-1.5">
                <p class="font-display font-bold {{ $reached ? '' : 'text-muted' }}">{{ $step->label() }}</p>
                @if ($reached)
                    <p class="text-sm text-muted">
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
