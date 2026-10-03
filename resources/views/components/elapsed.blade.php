@props(['since', 'lateAfter', 'prefix' => 'Hace'])

@php
    $minutes = (int) $since->diffInMinutes(now());
    $label = match (true) {
        $minutes < 1 => 'recién',
        $minutes < 60 => "{$minutes} min",
        $minutes < 1440 => intdiv($minutes, 60).' h',
        default => intdiv($minutes, 1440).' d',
    };
@endphp

{{--
    How long ago something happened. It keeps itself current while the screen is
    open (see app.js) and turns into a warning once it passes `lateAfter` minutes.
--}}
<time
    datetime="{{ $since->toIso8601String() }}"
    data-since="{{ $since->timestamp }}"
    data-late-after="{{ $lateAfter }}"
    @if ($minutes >= $lateAfter) data-late @endif
    {{ $attributes->class('inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-0.5 text-sm font-semibold text-muted tabular-nums transition-colors duration-300 data-late:bg-carrot data-late:text-forest') }}
>
    <x-icon name="clock" class="size-3.5" />
    <span class="sr-only">{{ $prefix }}</span>
    <span data-since-label>{{ $label }}</span>
</time>
