@props(['variant' => 'primary', 'size' => 'md', 'href' => null, 'type' => 'submit', 'block' => false])

@php
    // Filled buttons answer the pointer with more than a colour change.
    $filled = in_array($variant, ['primary', 'accent']);

    $classes = [
        'inline-flex select-none items-center justify-center gap-2 rounded-full font-display font-bold whitespace-nowrap',
        'transition-[transform,background-color,border-color,color,box-shadow] duration-200 ease-out-strong active:scale-[0.97]',
        'disabled:pointer-events-none disabled:opacity-50',
        'w-full' => $block,

        'h-9 px-3 text-sm' => $size === 'sm',
        'h-11 px-5 text-[15px]' => $size === 'md',
        'h-13 px-6 text-base' => $size === 'lg',

        // On hover a filled button rises a hair and casts a soft shadow of its own colour;
        // pressing it puts it back down.
        'group/btn relative overflow-hidden hover:-translate-y-0.5 active:translate-y-0 active:shadow-none' => $filled,
        'bg-leaf text-on-leaf hover:bg-leaf-deep hover:shadow-[0_10px_24px_-10px_var(--color-leaf)]' => $variant === 'primary',
        'bg-carrot text-forest hover:bg-carrot-deep hover:shadow-[0_10px_24px_-10px_var(--color-carrot)]' => $variant === 'accent',

        'border border-sage/30 bg-surface text-ink hover:border-leaf hover:text-leaf' => $variant === 'secondary',
        'text-leaf hover:bg-leaf/10' => $variant === 'ghost',
        'border border-danger/30 bg-surface text-danger hover:bg-danger/10' => $variant === 'danger',
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($filled)
            <x-button-shine />
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        @if ($filled)
            <x-button-shine />
        @endif
        {{ $slot }}
    </button>
@endif
