@props(['title', 'subtitle' => null])

@use('App\Enums\UserRole')

@php
    $user = auth()->user();
    $isAdmin = $user->role === UserRole::Admin;
    $restaurant = $isAdmin ? null : $user->restaurant;

    // What is waiting for the administrator, shown next to the menu entry that leads to it:
    // reported contents, counted once however many people reported each.
    $waiting = $isAdmin ? ['admin.reports.index' => \App\Models\ForumReport::query()->where('status', 'pending')->get(['reportable_type', 'reportable_id'])->unique(fn ($report) => $report->reportable_type.$report->reportable_id)->count()] : [];

    // Restaurant and admin share this layout; only the accent and the menu change.
    $nav = $isAdmin
        ? [
            ['label' => 'Restaurantes', 'route' => 'admin.restaurants.index', 'icon' => 'store', 'match' => 'admin.restaurants.*'],
            ['label' => 'Repartidores', 'route' => 'admin.couriers.index', 'icon' => 'bike', 'match' => 'admin.couriers.*'],
            ['label' => 'Moderación', 'route' => 'admin.reports.index', 'icon' => 'flag', 'match' => 'admin.reports.*'],
            ['label' => 'Hojitas Verdes', 'route' => 'admin.settings.edit', 'icon' => 'sliders', 'match' => 'admin.settings.*'],
        ]
        : [
            ['label' => 'Pedidos', 'route' => 'restaurant.orders.index', 'icon' => 'receipt', 'match' => 'restaurant.orders.*'],
            ['label' => 'Menú', 'route' => 'restaurant.menu.index', 'icon' => 'menu', 'match' => 'restaurant.menu.*'],
            ['label' => 'Reportes', 'route' => 'restaurant.reports.index', 'icon' => 'chart', 'match' => 'restaurant.reports.*'],
        ];
@endphp

<x-layouts.base :title="$title" :accent="$isAdmin ? 'sage' : 'carrot'">
    <header class="sticky top-0 z-30 bg-bar text-white [view-transition-name:site-header]">
        <div class="relative flex h-14 items-center gap-3 px-4 lg:h-16 lg:px-6">
            <x-logo tone="white" />
            {{-- On desktop the restaurant introduces itself in the sidebar, so the chip is only for phones. --}}
            <span @class(['rounded-full bg-white/15 px-2.5 py-0.5 text-xs font-semibold', 'lg:hidden' => $restaurant])>
                {{ $isAdmin ? 'Administración' : $restaurant?->name }}
            </span>

            <div class="ml-auto flex items-center gap-1">
                <span class="mr-2 hidden text-sm text-white/85 sm:block">{{ $user->name }}</span>
                <x-theme-toggle class="hover:bg-white/15" />
                <x-notification-bell />
                <form method="POST" action="{{ route('logout') }}" data-smooth="off">
                    @csrf
                    <button type="submit" class="flex size-11 items-center justify-center rounded-full transition-[transform,background-color] duration-150 ease-out-strong hover:bg-white/15 active:scale-[0.97]">
                        <x-icon name="logout" class="size-6" />
                        <span class="sr-only">Cerrar sesión</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="flex flex-1 flex-col lg:flex-row">
        <nav class="border-b border-sage/20 bg-surface lg:w-60 lg:shrink-0 lg:border-r lg:border-b-0" aria-label="Panel">
            <div class="lg:sticky lg:top-16 lg:flex lg:h-[calc(100dvh-4rem)] lg:flex-col">
            @if ($restaurant)
                <div class="hidden px-6 pt-6 lg:block">
                    <p class="font-display leading-tight font-bold">{{ $restaurant->name }}</p>
                    <p class="text-sm text-muted">{{ $restaurant->neighborhood }}</p>
                </div>
            @endif

            {{-- Live: the count beside an entry follows what is done on the screen without a reload. --}}
            <ul id="panel-nav" data-live class="flex gap-1 overflow-x-auto overflow-y-hidden px-3 py-2 contain-paint lg:contain-none lg:flex-col lg:px-3 lg:py-4">
                @foreach ($nav as $item)
                    @php $active = request()->routeIs($item['match']); @endphp
                    <li class="shrink-0">
                        <a
                            href="{{ route($item['route']) }}"
                            @if ($active) aria-current="page" @endif
                            class="group/link relative flex h-11 items-center gap-3 rounded-xl px-3 text-[15px] transition-colors duration-150 {{ $active ? 'bg-accent/15 font-semibold' : 'font-medium text-muted hover:bg-page hover:text-ink' }}"
                        >
                            @if ($active)
                                <span class="absolute inset-y-2.5 left-0 hidden w-1 rounded-full bg-accent lg:block" aria-hidden="true"></span>
                            @endif
                            <x-icon :name="$item['icon']" class="transition-transform duration-200 ease-out-strong group-hover/link:-rotate-6 group-hover/link:scale-110" />
                            {{ $item['label'] }}
                            @if (($waiting[$item['route']] ?? 0) > 0)
                                <span class="ml-auto min-w-5.5 rounded-full bg-carrot px-1.5 text-center text-xs leading-5.5 font-bold text-forest tabular-nums">{{ $waiting[$item['route']] }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>

            {{-- The plant in the corner of the office. It is on every screen, so it does not grow each time: it is just there. --}}
            <div class="mt-auto hidden px-4 pb-4 lg:block">
                <x-seedling :grow="false" />
            </div>
            </div>
        </nav>

        <main class="min-w-0 flex-1 px-4 py-5 lg:px-10 lg:py-8">
            <div class="mx-auto max-w-5xl">
                {{--
                    Every screen opens on the same green band with the leaf-cornered shape.
                    Its leaves are whole, off to the side of what is read, and they are the
                    only thing that moves on a work screen.
                --}}
                <div class="relative mb-6 rounded-leaf bg-band px-5 py-5 lg:px-7 lg:py-6">
                    <div class="pointer-events-none absolute top-4 right-4 sm:top-1/2 sm:right-6 sm:-translate-y-1/2" aria-hidden="true">
                        <div class="flex items-end gap-1">
                            <div class="hidden origin-bottom animate-sway [animation-delay:-3s] sm:block"><x-leaf class="size-9 -rotate-[58deg] text-foliage-faint opacity-20" /></div>
                            <div class="origin-bottom-left animate-sway"><x-leaf class="size-10 rotate-[10deg] text-foliage-faint opacity-30 sm:size-20" /></div>
                        </div>
                    </div>

                    <div class="relative flex flex-wrap items-end justify-between gap-4 sm:pr-32">
                        <div>
                            <h1 class="text-2xl max-sm:pr-12 lg:text-3xl">{{ $title }}</h1>
                            @if ($subtitle)
                                <p class="mt-1 text-muted">{{ $subtitle }}</p>
                            @endif
                        </div>
                        @isset($actions)
                            <div class="flex gap-2">{{ $actions }}</div>
                        @endisset
                    </div>
                </div>

                {{ $slot }}
            </div>
        </main>
    </div>
</x-layouts.base>
