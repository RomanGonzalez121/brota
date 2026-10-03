@props(['title' => null])

@php
    $user = auth()->user();
    $cartCount = app(\App\Services\Cart::class)->count();

    $nav = [
        ['label' => 'Inicio', 'route' => 'home', 'icon' => 'home', 'match' => 'home'],
        ['label' => 'Restaurantes', 'route' => 'restaurants.index', 'icon' => 'store', 'match' => 'restaurants.*'],
        ['label' => 'Pedidos', 'route' => 'orders.index', 'icon' => 'receipt', 'match' => 'orders.*'],
        ['label' => 'Foro', 'route' => 'forum.index', 'icon' => 'chat', 'match' => 'forum.*'],
        ['label' => 'Perfil', 'route' => 'profile.edit', 'icon' => 'user', 'match' => 'profile.*'],
    ];
@endphp

<x-layouts.base :title="$title">
    {{-- Named so it stays put while the rest of the page cross-fades between screens. --}}
    <header class="sticky top-0 z-30 bg-bar text-white [view-transition-name:site-header]">
        <div class="relative mx-auto flex h-14 max-w-6xl items-center gap-2 px-4 lg:h-16 lg:px-6">
            <x-logo tone="white" />

            <nav class="ml-8 hidden items-center gap-1 lg:flex" aria-label="Principal">
                @foreach ($nav as $item)
                    @php $active = request()->routeIs($item['match']); @endphp
                    <a
                        href="{{ route($item['route']) }}"
                        @if ($active) aria-current="page" @endif
                        @class([
                            'group/link relative rounded-md px-3 py-2 text-[15px] transition-colors duration-150',
                            'font-semibold text-white' => $active,
                            'font-medium text-white/80 hover:text-white' => ! $active,
                        ])
                    >
                        {{ $item['label'] }}
                        <x-nav-vine :active="$active" />

                    </a>
                @endforeach
            </nav>

            <div class="ml-auto flex items-center gap-1">
                <a href="{{ route('leaves.index') }}" class="mr-1 inline-flex h-8 items-center gap-1 rounded-leaf-sm bg-sprout pr-3 pl-2 font-display text-sm font-extrabold text-forest tabular-nums transition-transform duration-150 ease-out-strong active:scale-[0.97]">
                    <x-leaf class="size-4 text-forest" />
                    {{ $user->leaf_balance }}
                    <span class="sr-only">Hojitas Verdes</span>
                </a>

                <x-theme-toggle class="hover:bg-white/15" />

                <x-notification-bell />

                <a href="{{ route('cart.show') }}" class="relative flex size-11 items-center justify-center rounded-full transition-[transform,background-color] duration-150 ease-out-strong hover:bg-white/15 active:scale-[0.97]">
                    <x-cart-icon class="size-6" :grow="$cartCount > 0 && session()->has('status')" />
                    @if ($cartCount > 0)
                        {{-- Pops only right after something changed (a flash message is showing). --}}
                        <span @class(['absolute top-1.5 right-1.5 flex h-4.5 min-w-4.5 items-center justify-center rounded-full bg-carrot px-1 text-[11px] font-bold text-forest tabular-nums', 'animate-pop [animation-delay:150ms]' => session()->has('status')])>{{ $cartCount }}</span>
                    @endif
                    <span class="sr-only">Carrito{{ $cartCount > 0 ? ": {$cartCount} productos" : '' }}</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="hidden lg:block">
                    @csrf
                    <button type="submit" class="flex size-11 items-center justify-center rounded-full transition-[transform,background-color] duration-150 ease-out-strong hover:bg-white/15 active:scale-[0.97]">
                        <x-icon name="logout" class="size-6" />
                        <span class="sr-only">Cerrar sesión</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- Optional band that continues the header's green behind a screen's opening. --}}
    @isset($hero)
        <div class="overflow-hidden bg-deep text-white">
            <div class="mx-auto max-w-6xl px-4 lg:px-6">{{ $hero }}</div>
        </div>
    @endisset

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 pt-5 pb-28 lg:px-6 lg:pt-8 lg:pb-16">
        {{ $slot }}
    </main>

    <footer class="hidden bg-deep text-cream lg:block">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-8">
            <div>
                <x-logo tone="white" />
                <p class="mt-1 text-sm text-cream/75">Delivery de comida orgánica.</p>
            </div>
            <nav class="flex gap-6 text-sm" aria-label="Pie de página">
                <a href="{{ route('leaves.index') }}" class="underline decoration-transparent decoration-2 underline-offset-4 transition-[text-decoration-color] duration-200 hover:decoration-sprout">Hojitas Verdes</a>
                <a href="{{ route('forum.index') }}" class="underline decoration-transparent decoration-2 underline-offset-4 transition-[text-decoration-color] duration-200 hover:decoration-sprout">Foro de reciclaje</a>
                <a href="{{ route('profile.edit') }}" class="underline decoration-transparent decoration-2 underline-offset-4 transition-[text-decoration-color] duration-200 hover:decoration-sprout">Mi perfil</a>
                <a href="{{ route('credits') }}" class="underline decoration-transparent decoration-2 underline-offset-4 transition-[text-decoration-color] duration-200 hover:decoration-sprout">Créditos de las fotos</a>
            </nav>
        </div>
    </footer>

    <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-sage/20 bg-surface pb-[env(safe-area-inset-bottom)] [view-transition-name:bottom-nav] lg:hidden" aria-label="Principal">
        <ul class="mx-auto grid max-w-md grid-cols-5">
            @foreach ($nav as $item)
                @php $active = request()->routeIs($item['match']); @endphp
                <li>
                    <a
                        href="{{ route($item['route']) }}"
                        @if ($active) aria-current="page" @endif
                        class="relative flex h-16 flex-col items-center justify-center gap-1 text-[11px] transition-[color,transform] duration-150 ease-out-strong active:scale-[0.95] {{ $active ? 'font-semibold text-leaf' : 'font-medium text-muted' }}"
                    >
                        @if ($active)
                            <x-leaf class="absolute top-1 right-1/2 size-3 translate-x-5 origin-bottom-left animate-sprout text-sprout" />
                        @endif
                        <x-icon :name="$item['icon']" class="size-6" />
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
</x-layouts.base>
