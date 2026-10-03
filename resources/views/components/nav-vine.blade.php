@props(['active' => false])

{{--
    The mark under a top-bar link: a wavy stem with a leaf at its tip. On hover
    the stem draws itself left to right and the leaf sprouts once it arrives;
    leaving is quicker than arriving. On the current page it is simply there,
    and it travels to the next link when the page changes.
--}}
<span @class(['pointer-events-none absolute inset-x-2.5 -bottom-1 h-3 text-sprout', '[view-transition-name:nav-indicator]' => $active]) aria-hidden="true">
    <svg class="size-full overflow-visible" viewBox="0 0 100 12" preserveAspectRatio="none" fill="none">
        <path
            d="M1 8C20 2 35 12 52 7S82 3 96 7"
            stroke="currentColor"
            stroke-width="1.75"
            stroke-linecap="round"
            vector-effect="non-scaling-stroke"
            @unless ($active)
                pathLength="1"
                class="transition-[stroke-dashoffset] duration-200 ease-out-strong [stroke-dasharray:1] [stroke-dashoffset:1] group-hover/link:duration-500 group-hover/link:[stroke-dashoffset:0]"
            @endunless
        />
    </svg>
    <x-leaf @class([
        'absolute -top-1.5 -right-2 size-3.5 origin-bottom-left',
        'scale-50 opacity-0 transition-[opacity,scale] duration-150 ease-out-strong group-hover/link:scale-100 group-hover/link:opacity-100 group-hover/link:delay-300 group-hover/link:duration-300' => ! $active,
    ]) />
</span>
