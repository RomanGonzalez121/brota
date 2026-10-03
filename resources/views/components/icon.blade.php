@props(['name'])

@php
    // Everyday icons keep their familiar outline and carry a leaf or a sprout.
    $paths = [
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.6-3.6"/><path d="M8.7 13.3c0-2.5 2.1-4.6 4.6-4.6 0 2.5-2.1 4.6-4.6 4.6Z"/>',
        'cart' => '<path d="M3 5h2l2.4 10.2a1 1 0 0 0 1 .8h8.9a1 1 0 0 0 1-.8L20 9H6"/><circle cx="9.5" cy="19.5" r="1.25"/><circle cx="17" cy="19.5" r="1.25"/><path d="M13 9V6.3"/><path d="M13 7c0-1.8-1.4-3.2-3.2-3.2 0 1.8 1.4 3.2 3.2 3.2Z"/><path d="M13 6.3c0-2 1.7-3.7 3.7-3.7 0 2-1.7 3.7-3.7 3.7Z"/>',
        'bell' => '<path d="M5 16c1.5-1.5 2-3.5 2-6.5a5 5 0 0 1 10 0c0 3 .5 5 2 6.5q-2.33 3-4.67 0q-2.33 3-4.66 0q-2.34 3-4.67 0Z"/><path d="M12 18.3v2.2"/><path d="M12 4.5V3.2"/><path d="M12 3.6c1-1.5 2.9-2 4.6-1.4-.6 1.7-2.4 2.5-4.6 1.4Z"/>',
        'home' => '<path d="m4 11 8-7 8 7"/><path d="M6 10v9h12v-9"/><path d="M12 19v-4.5"/><path d="M12 16c0-1.8-1.4-3.2-3.2-3.2 0 1.8 1.4 3.2 3.2 3.2Z"/><path d="M12 14.5c0-1.8 1.4-3.2 3.2-3.2 0 1.8-1.4 3.2-3.2 3.2Z"/>',
        'receipt' => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"/><path d="M9 8h6M9 12h6"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4.5 20a7.5 7.5 0 0 1 15 0"/>',
        'users' => '<circle cx="9" cy="8.5" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 5.2a3.5 3.5 0 0 1 0 6.6M18 14.2a6.5 6.5 0 0 1 3.5 5.8"/>',
        'chat' => '<path d="M4 5h16v11H9l-5 4V5Z"/><path d="M9.3 13.2c0-3 2.5-5.4 5.5-5.4 0 3-2.5 5.4-5.5 5.4Z"/><path d="m9.3 13.2 3-3"/>',
        'pin' => '<path d="M12 21s7-6.1 7-11.5a7 7 0 1 0-14 0C5 14.9 12 21 12 21Z"/><path d="M9.7 11.8c0-2.5 2.1-4.6 4.6-4.6 0 2.5-2.1 4.6-4.6 4.6Z"/>',
        'clock' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
        'star' => '<path fill="currentColor" d="m12 3.5 2.6 5.4 5.9.8-4.3 4.1 1 5.8L12 16.9l-5.2 2.7 1-5.8L3.5 9.7l5.9-.8L12 3.5Z"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'minus' => '<path d="M5 12h14"/>',
        'trash' => '<path d="M4 7h16M9 7V4h6v3M6.5 7l1 13h9l1-13M10 11v5M14 11v5"/>',
        'check' => '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
        'x' => '<path d="m6 6 12 12M18 6 6 18"/>',
        'chevron-right' => '<path d="m9 5 7 7-7 7"/>',
        'chevron-left' => '<path d="m15 5-7 7 7 7"/>',
        'chevron-down' => '<path d="m5 9 7 7 7-7"/>',
        'store' => '<path d="M4 10v10h16V10"/><path d="m3 10 2-6h14l2 6a3 3 0 0 1-6 0 3 3 0 0 1-6 0 3 3 0 0 1-6 0Z"/><path d="M10 20v-5h4v5"/>',
        'bike' => '<circle cx="6" cy="16.5" r="3.5"/><circle cx="18" cy="16.5" r="3.5"/><path d="m6 16.5 4-8.5h4l4 8.5M10 8 8.5 5H6M14 8l-2.5 8.5"/>',
        'chart' => '<path d="M4 20V4M4 20h16M8.5 16v-5M13 16V8M17.5 16v-8"/>',
        'sliders' => '<path d="M4 7h10M18 7h2M4 17h2M10 17h10"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/>',
        'flag' => '<path d="M5 21V4M5 4h12l-2 4.5 2 4.5H5"/>',
        'logout' => '<path d="M10 4H5v16h5M15 8l4 4-4 4M19 12H9"/>',
        'card' => '<rect x="3" y="5.5" width="18" height="13" rx="2"/><path d="M3 10h18M7 15h3"/>',
        'wallet' => '<path d="M4 6.5V18a1.5 1.5 0 0 0 1.5 1.5h13A1.5 1.5 0 0 0 20 18V9.5A1.5 1.5 0 0 0 18.500 8h-13A1.5 1.5 0 0 1 4 6.5 1.5 1.5 0 0 1 5.500 5H17"/><circle cx="16" cy="13.75" r="1"/>',
        'edit' => '<path d="M4 20h4L19 9l-4-4L4 16v4Z"/><path d="m13 7 4 4"/>',
        'menu' => '<path d="M7 3v8M5 3v4a2 2 0 0 0 4 0V3M7 11v10M17 3c-2 1.500-3 4-3 7h3v11"/>',
        'shield' => '<path d="m12 3-7 3v6c0 4.500 3 7.500 7 9 4-1.500 7-4.500 7-9V6l-7-3Z"/>',
        'box' => '<path d="m4 8 8-4 8 4v8l-8 4-8-4V8Z"/><path d="m4 8 8 4 8-4M12 12v8"/>',
        'filter' => '<path d="M4 5h16l-6 7.500V19l-4-2v-4.500L4 5Z"/>',
        'phone' => '<path d="M6 3h4l1.500 5L9 9.500a11 11 0 0 0 5.500 5.500L16 12.500l5 1.500v4a2 2 0 0 1-2 2A16 16 0 0 1 3 5a2 2 0 0 1 2-2Z"/>',
        'arrow-left' => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
        'refresh' => '<path d="M4 12a8 8 0 0 1 13.5-5.8M20 4v4.5h-4.5M20 12a8 8 0 0 1-13.5 5.8M4 20v-4.5h4.5"/>',
        'swap' => '<path d="M4 8h14l-3-3M20 16H6l3 3"/>',

        // Botanical set: the brand speaks in plants wherever an icon can.
        'sprout' => '<path d="M12 21v-9"/><path d="M12 14c0-3.3-2.7-6-6-6 0 3.3 2.7 6 6 6Z"/><path d="M12 12c0-3.9 3.1-7 7-7 0 3.9-3.1 7-7 7Z"/>',
        'flower' => '<circle cx="12" cy="12" r="2.6"/><ellipse cx="12" cy="5.2" rx="1.9" ry="2.7"/><ellipse cx="12" cy="5.2" rx="1.9" ry="2.7" transform="rotate(60 12 12)"/><ellipse cx="12" cy="5.2" rx="1.9" ry="2.7" transform="rotate(120 12 12)"/><ellipse cx="12" cy="5.2" rx="1.9" ry="2.7" transform="rotate(180 12 12)"/><ellipse cx="12" cy="5.2" rx="1.9" ry="2.7" transform="rotate(240 12 12)"/><ellipse cx="12" cy="5.2" rx="1.9" ry="2.7" transform="rotate(300 12 12)"/>',
        'moon-leaf' => '<path d="M19.5 14.8A8 8 0 0 1 9.2 4.5a8 8 0 1 0 10.300 10.300Z"/><path d="M14.5 4.500c2.800 0 5 2.200 5 5-2.800 0-5-2.200-5-5Z"/><path d="m14.500 4.500 3 3"/>',
        'wheat' => '<path d="M12 21V8"/><path d="M12 8c-1.600-1.200-1.600-3.600 0-5 1.600 1.400 1.600 3.800 0 5Z"/><path d="M12 13c-2.200 0-4-1.500-4-3.500 2.200 0 4 1.500 4 3.500ZM12 13c2.200 0 4-1.500 4-3.500-2.200 0-4 1.500-4 3.500Z"/><path d="M12 17.500c-2.200 0-4-1.500-4-3.500 2.200 0 4 1.500 4 3.500ZM12 17.500c2.200 0 4-1.500 4-3.500-2.200 0-4 1.500-4 3.500Z"/>',
        'wheat-off' => '<path d="M12 21V8"/><path d="M12 8c-1.600-1.200-1.600-3.600 0-5 1.600 1.400 1.600 3.800 0 5Z"/><path d="M12 13c-2.200 0-4-1.500-4-3.500 2.200 0 4 1.500 4 3.500ZM12 13c2.200 0 4-1.500 4-3.500-2.200 0-4 1.500-4 3.500Z"/><path d="M12 17.500c-2.200 0-4-1.500-4-3.500 2.200 0 4 1.500 4 3.500ZM12 17.500c2.200 0 4-1.500 4-3.500-2.200 0-4 1.500-4 3.500Z"/><path d="M4 4l16 16"/>',
        'carrot' => '<path d="M9.500 8.500 4 20l11.500-5.500a4.200 4.200 0 1 0-6-6Z"/><path d="M15.500 8.500 19 5M17 3.500V7M20.500 7H17"/><path d="m8 13 2 2M11 10.500l2 2"/>',
        'citrus' => '<circle cx="12" cy="12" r="8.500"/><circle cx="12" cy="12" r="1"/><path d="M12 5.500v4M12 14.500v4M5.500 12h4M14.500 12h4M7.400 7.400l2.800 2.800M13.800 13.800l2.800 2.800M16.600 7.400l-2.800 2.800M10.200 13.800l-2.800 2.800"/>',
        'bowl' => '<path d="M3 12h18a9 9 0 0 1-18 0Z"/><path d="M11 9c0-2.800 2.200-5 5-5 0 2.800-2.200 5-5 5Z"/><path d="M7.500 9c0-1.700-1.300-3-3-3"/>',
        'pot' => '<path d="M7 14h10l-1.200 7H8.200L7 14Z"/><path d="M12 14V9"/><path d="M12 10.500c0-2.500-2-4.500-4.500-4.500 0 2.500 2 4.500 4.500 4.500Z"/><path d="M12 9c0-3 2.500-5.500 5.500-5.500 0 3-2.500 5.500-5.500 5.500Z"/>',
    ];

    $sized = str_contains($attributes->get('class', ''), 'size-');
@endphp

<svg {{ $attributes->class(['shrink-0', 'size-5' => ! $sized]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $paths[$name] ?? '' !!}</svg>
