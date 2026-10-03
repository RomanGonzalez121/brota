@props(['tone' => 'neutral', 'leaf' => false])

<span {{ $attributes->class([
    'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap',
    'bg-sage/15 text-ink' => $tone === 'neutral',
    'bg-leaf/12 text-leaf' => $tone === 'success',
    'bg-sprout-soft text-ink' => $tone === 'sprout',
    'bg-carrot/20 text-ink' => $tone === 'accent',
    'bg-danger/10 text-danger' => $tone === 'danger',
]) }}>
    @if ($leaf)
        <x-leaf class="size-3.5 text-leaf" />
    @endif
    {{ $slot }}
</span>
