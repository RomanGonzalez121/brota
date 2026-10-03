@props(['dish'])

{{-- Dishes added without a photo fall back to a leaf tile. --}}
<div {{ $attributes->class('relative shrink-0 overflow-hidden bg-sprout-soft') }}>
    @if ($dish->image)
        <img src="{{ asset($dish->image) }}" alt="" loading="lazy" class="size-full object-cover">
    @else
        <x-leaf class="absolute inset-0 m-auto size-1/2 text-leaf/50" />
    @endif
</div>
