@props(['title', 'subtitle' => null])

@use('App\Enums\UserRole')

@php
    $user = auth()->user();
    $isAdmin = $user->role === UserRole::Admin;

    // Restaurant and admin share this layout; only the accent and the menu change.
    $nav = $isAdmin
        ? [
            ['label' => 'Restaurantes', 'route' => 'admin.restaurants.index', 'icon' => 'store', 'match' => 'admin.restaurants.*'],
            ['label' => 'Repartidores', 'route' => 'admin.couriers.index', 'icon' => 'bike', 'match' => 'admin.couriers.*'],
            ['label' => 'Moderación del foro', 'route' => 'admin.reports.index', 'icon' => 'flag', 'match' => 'admin.reports.*'],
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
            <span class="rounded-full bg-white/15 px-2.5 py-0.5 text-xs font-semibold">
                {{ $isAdmin ? 'Administración' : $user->restaurant?->name }}
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
            <ul class="flex gap-1 overflow-x-auto overflow-y-hidden px-3 py-2 contain-paint lg:contain-none lg:sticky lg:top-16 lg:flex-col lg:px-3 lg:py-5">
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
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <main class="min-w-0 flex-1 px-4 py-6 lg:px-10 lg:py-8">
            <div class="mx-auto max-w-5xl">
                <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h1 class="text-2xl lg:text-3xl">{{ $title }}</h1>
                        @if ($subtitle)
                            <p class="mt-1 text-muted">{{ $subtitle }}</p>
                        @endif
                    </div>
                    @isset($actions)
                        <div class="flex gap-2">{{ $actions }}</div>
                    @endisset
                </div>

                {{ $slot }}
            </div>
        </main>
    </div>
</x-layouts.base>
