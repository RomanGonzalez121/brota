@props(['icon', 'label', 'value', 'tone' => 'leaf', 'hint' => null])

{{-- One number of a summary, with its icon. The number rolls when it changes in place, and the tile answers the pointer. --}}
<div {{ $attributes->class('lift rounded-media border border-sage/20 bg-surface p-4') }}>
    <span
        @class([
            'flex size-9 items-center justify-center rounded-leaf-sm',
            'bg-sprout-soft text-leaf' => $tone === 'leaf',
            'bg-carrot/25 text-ink' => $tone === 'accent',
            'bg-sage/12 text-muted' => $tone === 'quiet',
        ])
        aria-hidden="true"
    >
        <x-icon :name="$icon" class="lift-icon size-5 origin-bottom" />
    </span>
    <p class="mt-3 text-sm text-muted">{{ $label }}</p>
    <p data-pop class="font-display text-3xl leading-tight font-extrabold tabular-nums">{{ $value }}</p>
    @if ($hint)
        <p class="mt-0.5 text-sm text-muted">{{ $hint }}</p>
    @endif
</div>
