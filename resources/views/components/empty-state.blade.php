@props(['title'])

<div {{ $attributes->class('flex flex-col items-center px-6 py-12 text-center') }}>
    {{-- A seedling grows where there is nothing yet: stem first, then each leaf, then a light breeze. --}}
    <x-seedling class="mb-5" />

    <h2 class="text-lg">{{ $title }}</h2>
    @if ($slot->isNotEmpty())
        <p class="mt-1.5 max-w-sm text-muted">{{ $slot }}</p>
    @endif
    @isset($action)
        <div class="mt-5">{{ $action }}</div>
    @endisset
</div>
