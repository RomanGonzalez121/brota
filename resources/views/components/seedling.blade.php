@props(['grow' => true])

{{--
    The little plant of the empty states. With `grow` it comes up stem first,
    then each leaf; without it the plant is simply there, moving in the breeze,
    for places that are seen on every screen.
--}}
<div {{ $attributes->class('relative h-24 w-24') }} aria-hidden="true">
    <div class="absolute inset-0 origin-bottom animate-sway">
        <span @class(['absolute bottom-2 left-1/2 h-14 w-0.5 origin-bottom -translate-x-1/2 rounded-full bg-leaf', 'animate-grow' => $grow])></span>

        <span @class(['absolute right-1/2 bottom-6 origin-bottom-right', 'animate-sprout [animation-delay:330ms]' => $grow])>
            <x-leaf class="size-7 -scale-x-100 rotate-[14deg] text-foliage-light" />
        </span>
        <span @class(['absolute bottom-9 left-1/2 origin-bottom-left', 'animate-sprout [animation-delay:460ms]' => $grow])>
            <x-leaf class="size-8 rotate-[12deg] text-leaf" />
        </span>
        <span @class(['absolute bottom-[3.6rem] left-1/2 origin-bottom-left', 'animate-sprout [animation-delay:600ms]' => $grow])>
            <x-leaf class="size-6 -translate-x-px -rotate-[36deg] text-foliage-light" />
        </span>
    </div>
    <span class="absolute bottom-1 left-1/2 h-1 w-14 -translate-x-1/2 rounded-full bg-ink/12"></span>
</div>
