@props(['title'])

<div {{ $attributes->class('flex flex-col items-center px-6 py-12 text-center') }}>
    <div class="relative mb-5 flex h-20 w-24 items-end justify-center" aria-hidden="true">
        <span class="absolute bottom-3 left-1 origin-bottom-right animate-sprout [animation-delay:120ms]"><x-leaf class="size-9 -rotate-[70deg] text-sprout/60" /></span>
        <span class="absolute bottom-5 origin-bottom animate-sprout"><x-leaf class="size-12 -rotate-12 text-leaf" /></span>
        <span class="absolute right-1 bottom-3 origin-bottom-left animate-sprout [animation-delay:220ms]"><x-leaf class="size-9 rotate-[20deg] text-sprout" /></span>
        <span class="h-1 w-16 rounded-full bg-forest/10"></span>
    </div>
    <h2 class="text-lg">{{ $title }}</h2>
    @if ($slot->isNotEmpty())
        <p class="mt-1.5 max-w-sm text-muted">{{ $slot }}</p>
    @endif
    @isset($action)
        <div class="mt-5">{{ $action }}</div>
    @endisset
</div>
