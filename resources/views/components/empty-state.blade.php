@props(['title'])

<div {{ $attributes->class('flex flex-col items-center px-6 py-12 text-center') }}>
    {{-- A seedling grows where there is nothing yet: stem first, then each leaf, then a light breeze. --}}
    <div class="relative mb-5 h-24 w-24" aria-hidden="true">
        <div class="absolute inset-0 origin-bottom animate-sway">
            <span class="absolute bottom-2 left-1/2 h-14 w-0.5 origin-bottom -translate-x-1/2 animate-grow rounded-full bg-leaf"></span>

            <span class="absolute right-1/2 bottom-6 origin-bottom-right animate-sprout [animation-delay:330ms]">
                <x-leaf class="size-7 -scale-x-100 rotate-[14deg] text-foliage-light" />
            </span>
            <span class="absolute bottom-9 left-1/2 origin-bottom-left animate-sprout [animation-delay:460ms]">
                <x-leaf class="size-8 rotate-[12deg] text-leaf" />
            </span>
            <span class="absolute bottom-[3.6rem] left-1/2 origin-bottom-left animate-sprout [animation-delay:600ms]">
                <x-leaf class="size-6 -translate-x-px -rotate-[36deg] text-foliage-light" />
            </span>
        </div>
        <span class="absolute bottom-1 left-1/2 h-1 w-14 -translate-x-1/2 rounded-full bg-ink/12"></span>
    </div>

    <h2 class="text-lg">{{ $title }}</h2>
    @if ($slot->isNotEmpty())
        <p class="mt-1.5 max-w-sm text-muted">{{ $slot }}</p>
    @endif
    @isset($action)
        <div class="mt-5">{{ $action }}</div>
    @endisset
</div>
