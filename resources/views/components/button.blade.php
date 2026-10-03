@props(['variant' => 'primary', 'size' => 'md', 'href' => null, 'type' => 'submit', 'block' => false])

@php
    $classes = [
        'inline-flex select-none items-center justify-center gap-2 rounded-full font-display font-bold whitespace-nowrap',
        'transition-[transform,background-color,border-color,color] duration-150 ease-out-strong active:scale-[0.97]',
        'disabled:pointer-events-none disabled:opacity-50',
        'w-full' => $block,

        'h-9 px-3 text-sm' => $size === 'sm',
        'h-11 px-5 text-[15px]' => $size === 'md',
        'h-13 px-6 text-base' => $size === 'lg',

        'bg-leaf text-on-leaf hover:bg-leaf-deep' => $variant === 'primary',
        'bg-carrot text-forest hover:bg-carrot-deep' => $variant === 'accent',
        'border border-sage/30 bg-surface text-ink hover:border-leaf hover:text-leaf' => $variant === 'secondary',
        'text-leaf hover:bg-leaf/10' => $variant === 'ghost',
        'border border-danger/30 bg-surface text-danger hover:bg-danger/10' => $variant === 'danger',
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
