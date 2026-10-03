{{-- The cart with its sprout drawn apart, so the sprout alone can grow back after a dish is added. --}}
<svg {{ $attributes->class('shrink-0') }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <path d="M3 5h2l2.4 10.2a1 1 0 0 0 1 .8h8.9a1 1 0 0 0 1-.8L20 9H6"/>
    <circle cx="9.5" cy="19.5" r="1.25"/>
    <circle cx="17" cy="19.5" r="1.25"/>
    <g data-cart-sprout style="transform-origin: 13px 9px">
        <path d="M13 9V6.3"/>
        <path d="M13 7c0-1.8-1.4-3.2-3.2-3.2 0 1.8 1.4 3.2 3.2 3.2Z"/>
        <path d="M13 6.3c0-2 1.7-3.7 3.7-3.7 0 2-1.7 3.7-3.7 3.7Z"/>
    </g>
</svg>
