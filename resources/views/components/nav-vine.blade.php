@props(['active' => false])

@php
    $gradient = 'vine-'.\Illuminate\Support\Str::random(6);
@endphp

{{--
    The mark under a top-bar link: one fine stem that fades in from nothing and
    lifts towards a small leaf at its tip. On hover the stem draws itself with a
    slow, even ease and the leaf unfurls when it arrives; on leaving the whole
    thing simply fades. On the current page it is just there, and it travels to
    the next link when the page changes.
--}}
<span
    @class([
        'pointer-events-none absolute inset-x-2.5 -bottom-1.5 h-3.5 text-sprout',
        '[view-transition-name:nav-indicator]' => $active,
        'opacity-0 transition-opacity duration-300 ease-out group-hover/link:opacity-100 group-hover/link:duration-150' => ! $active,
    ])
    aria-hidden="true"
>
    <svg class="size-full overflow-visible" viewBox="0 0 100 14" preserveAspectRatio="none" fill="none">
        <defs>
            <linearGradient id="{{ $gradient }}" x1="0" y1="0" x2="1" y2="0">
                <stop offset="0" stop-color="currentColor" stop-opacity="0" />
                <stop offset="0.4" stop-color="currentColor" stop-opacity="0.85" />
                <stop offset="1" stop-color="currentColor" />
            </linearGradient>
        </defs>
        <path
            d="M1 10.5C34 12 68 10.500 96 5"
            stroke="url(#{{ $gradient }})"
            stroke-width="1.25"
            stroke-linecap="round"
            vector-effect="non-scaling-stroke"
            @unless ($active)
                pathLength="1"
                {{-- Draws in over 650ms; when the pointer leaves it resets only after the fade has hidden it. --}}
                class="transition-[stroke-dashoffset] delay-300 duration-0 ease-[cubic-bezier(0.65,0,0.35,1)] [stroke-dasharray:1] [stroke-dashoffset:1] group-hover/link:delay-0 group-hover/link:duration-[650ms] group-hover/link:[stroke-dashoffset:0]"
            @endunless
        />
    </svg>
    <x-leaf @class([
        'absolute -top-1 -right-1.5 size-3 origin-bottom-left',
        'rotate-[22deg]' => $active,
        'scale-75 -rotate-[10deg] opacity-0 transition-[opacity,scale,rotate] duration-0 ease-out-strong group-hover/link:scale-100 group-hover/link:rotate-[22deg] group-hover/link:opacity-100 group-hover/link:delay-[480ms] group-hover/link:duration-500' => ! $active,
    ]) />
</span>
