@props(['outline' => false])

{{-- The brand's recurring mark: points, badges, steps and empty states. --}}
<svg {{ $attributes->class('shrink-0') }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
    @if ($outline)
        <path d="M20.5 3.5c.4 9.6-5.1 16.6-14.6 16.6-.6 0-1.100 0-1.700-.1C3.200 10.700 9.900 3.400 20.500 3.500Z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
        <path d="M4.2 20 14 10" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
    @else
        <path d="M20.5 3.5c.4 9.6-5.1 16.6-14.6 16.6-.6 0-1.100 0-1.700-.1C3.200 10.700 9.900 3.400 20.500 3.500Z" fill="currentColor"/>
        <path d="M4.2 20 14.500 9.500" class="stroke-white/55" stroke-width="1.25" stroke-linecap="round" vector-effect="non-scaling-stroke"/>
    @endif
</svg>
