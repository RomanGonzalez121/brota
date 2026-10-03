@props(['padding' => 'p-5', 'as' => 'div'])

<{{ $as }} {{ $attributes->class(['rounded-2xl border border-sage/20 bg-white', $padding]) }}>
    {{ $slot }}
</{{ $as }}>
