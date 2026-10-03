@props(['restaurant'])

@php
    // Restaurants without a photo get a tinted block with a leaf arrangement picked by id.
    $variant = $restaurant->id % 4;
    $background = ['bg-sprout/35', 'bg-leaf/15', 'bg-carrot/25', 'bg-sage/20'][$variant];
    $leaves = [
        ['-right-5 -bottom-8 size-32 rotate-[12deg] text-leaf/30', 'right-16 -bottom-4 size-20 -rotate-[58deg] text-leaf/45'],
        ['-left-6 -bottom-9 size-32 -rotate-[76deg] text-leaf/30', 'left-20 -bottom-5 size-16 -rotate-[20deg] text-sprout'],
        ['-right-4 -top-10 size-32 rotate-[100deg] text-ink/15', 'right-20 -top-4 size-16 rotate-[150deg] text-carrot/60'],
        ['right-6 -bottom-10 size-36 -rotate-[30deg] text-leaf/25', '-right-6 -bottom-2 size-20 rotate-[24deg] text-sprout/80'],
    ][$variant];
@endphp

<div {{ $attributes->class(['relative overflow-hidden', $background]) }} aria-hidden="true">
    @if ($restaurant->image)
        <img src="{{ asset($restaurant->image) }}" alt="" loading="lazy" class="size-full object-cover">
    @else
        @foreach ($leaves as $leaf)
            <x-leaf class="absolute {{ $leaf }}" />
        @endforeach
    @endif
</div>
