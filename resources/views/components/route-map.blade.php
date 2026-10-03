@props(['order', 'animated' => false])

@use('App\Enums\OrderStatus')

@php
    /*
     | The map is a drawing, not a map service: a small grid of streets with the
     | restaurant, the customer and the way between them. Each order gets one of
     | four layouts, always the same for that order.
     */
    $route = [
        ['from' => [90, 160], 'to' => [240, 40], 'path' => 'M90 160 H190 V80 H240 V40'],
        ['from' => [240, 160], 'to' => [90, 40], 'path' => 'M240 160 V120 H140 V40 H90'],
        ['from' => [90, 40], 'to' => [240, 160], 'path' => 'M90 40 V80 H190 V160 H240'],
        ['from' => [140, 160], 'to' => [190, 40], 'path' => 'M140 160 V120 H240 V80 H190 V40'],
    ][$order->id % 4];

    $onTheWay = $order->status === OrderStatus::OnTheWay;
    $waiting = $order->status === OrderStatus::Preparing;
    $delivered = $order->status === OrderStatus::Delivered;

    // Where the courier is along the way: there is no GPS, so it follows the time since they left.
    $progress = $onTheWay
        ? (int) round(min(max($order->updated_at->diffInMinutes(now()) / max($order->restaurant->eta_minutes / 2, 1), 0.14), 0.86) * 100)
        : 0;

    $pin = fn (array $point) => 'left: '.round($point[0] / 320 * 100, 2).'%; top: '.round($point[1] / 200 * 100, 2).'%';
@endphp

<div {{ $attributes->class('relative aspect-[16/10] overflow-hidden bg-band') }} role="img" aria-label="Mapa ilustrativo: de {{ $order->restaurant->name }} a {{ $order->delivery_address }}">
    <svg class="absolute inset-0 size-full" viewBox="0 0 320 200" fill="none" aria-hidden="true">
        {{-- Streets, as light lines over the block colour. --}}
        <g class="stroke-surface dark:stroke-cream/10" stroke-width="9" stroke-linecap="round">
            @foreach ([40, 90, 140, 190, 240, 290] as $x)
                <path d="M{{ $x }} -10 V210" />
            @endforeach
            @foreach ([40, 80, 120, 160] as $y)
                <path d="M-10 {{ $y }} H330" />
            @endforeach
        </g>

        {{-- Two squares, because every neighbourhood has one. --}}
        <rect x="246" y="86" width="38" height="28" rx="8" class="fill-sprout/45" />
        <rect x="46" y="86" width="38" height="28" rx="8" class="fill-sprout/45" />
        <path d="M71 93c.3 6-3.200 10.400-9.100 10.400h-1.100c-.9-5.900 3.300-10.400 10.200-10.400Z" class="fill-leaf/60" />
        <path d="M271 93c.3 6-3.200 10.400-9.100 10.400h-1.100c-.9-5.900 3.300-10.400 10.200-10.400Z" class="fill-leaf/60" />

        {{-- The way: a faint line for all of it, a solid one that draws itself over it. --}}
        <path d="{{ $route['path'] }}" class="stroke-leaf/25" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />
        <path
            d="{{ $route['path'] }}"
            pathLength="1"
            @class(['stroke-leaf', '[stroke-dasharray:1]', 'animate-draw [animation-duration:900ms]' => $animated])
            stroke-width="5"
            stroke-linecap="round"
            stroke-linejoin="round"
        />

        @if ($onTheWay)
            {{-- The courier, as a dot that travels from the restaurant to where they are now. --}}
            <g @class(['route-marker', 'route-marker-travels' => $animated]) style="offset-path: path('{{ $route['path'] }}'); offset-distance: {{ $progress }}%">
                <circle r="13" class="origin-center animate-breathe fill-carrot [transform-box:fill-box]" />
                <circle r="8" class="fill-carrot stroke-surface" stroke-width="3" />
            </g>
        @endif
    </svg>

    {{-- The two ends of the trip, as pins over the drawing. --}}
    <span class="absolute size-9 -translate-x-1/2 -translate-y-1/2" style="{{ $pin($route['from']) }}" aria-hidden="true">
        @if ($waiting)
            <span class="absolute inset-0 animate-breathe rounded-leaf-sm bg-leaf"></span>
        @endif
        <span class="relative flex size-9 items-center justify-center rounded-leaf-sm bg-surface text-leaf ring-2 ring-leaf">
            <x-icon name="store" class="size-4.5" />
        </span>
    </span>
    <span class="absolute flex size-9 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-leaf-sm bg-leaf text-on-leaf ring-2 ring-surface" style="{{ $pin($route['to']) }}" aria-hidden="true">
        <x-icon :name="$delivered ? 'check' : 'home'" class="size-4.5" />
    </span>

    <span class="absolute bottom-2 left-2.5 rounded-full bg-surface/85 px-2 py-0.5 text-[11px] font-medium text-muted" aria-hidden="true">Mapa ilustrativo</span>
</div>
