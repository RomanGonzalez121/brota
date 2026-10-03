@props(['once'])

@php
    // Each leaf: horizontal position, size, colour, fall time, start delay, flutter time.
    $leaves = [
        ['14%', 'size-7', 'text-sprout', '4.4s', '0ms', '1.7s'],
        ['34%', 'size-5', 'text-leaf', '3.8s', '380ms', '1.4s'],
        ['52%', 'size-8', 'text-sprout', '4.9s', '160ms', '1.9s'],
        ['71%', 'size-6', 'text-leaf', '4.1s', '620ms', '1.5s'],
        ['88%', 'size-7', 'text-sprout', '4.6s', '300ms', '1.8s'],
    ];
@endphp

{{--
    A handful of leaves falls once across the screen: the reward for earning
    Hojitas Verdes. "once" is the storage key that keeps it a one-time moment.
--}}
<div x-data="leafFall(@js($once))" x-show="playing" x-cloak class="pointer-events-none fixed inset-0 z-40 overflow-hidden" aria-hidden="true">
    @foreach ($leaves as [$left, $size, $tone, $fall, $delay, $flutter])
        <span class="absolute top-0 animate-leaf-fall opacity-0" style="left: {{ $left }}; animation-duration: {{ $fall }}; animation-delay: {{ $delay }}">
            <span class="block animate-leaf-drift" style="animation-duration: {{ $flutter }}; animation-delay: -{{ $loop->index * 400 }}ms">
                <x-leaf class="{{ $size }} {{ $tone }}" />
            </span>
        </span>
    @endforeach
</div>
