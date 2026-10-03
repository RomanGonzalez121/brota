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
    <header class="sticky top-0 z-30 bg-leaf text-white">
        <div class="relative mx-auto flex h-14 max-w-6xl items-center gap-2 px-4 lg:h-16 lg:px-6">
            <x-logo tone="white" />

            <nav class="ml-8 hidden items-center gap-1 lg:flex" aria-label="Principal">
                @foreach ($nav as $item)
                    @php $active = request()->routeIs($item['match']); @endphp
                    <a
                        href="{{ route($item['route']) }}"
                        @if ($active) aria-current="page" @endif
                        class="rounded-lg px-3 py-2 text-[15px] font-medium transition-colors duration-150 {{ $active ? 'bg-white/15' : 'text-white/85 hover:bg-white/10 hover:text-white' }}"
                    >{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <div class="ml-auto flex items-center gap-1">
                <a href="{{ route('leaves.index') }}" class="mr-1 inline-flex h-8 items-center gap-1 rounded-leaf-sm bg-sprout pr-3 pl-2 font-display text-sm font-extrabold text-forest tabular-nums transition-transform duration-150 ease-out-strong active:scale-[0.97]">
                    <x-leaf class="size-4 text-leaf" />
                    {{ $user->leaf_balance }}
                    <span class="sr-only">Hojitas Verdes</span>
                </a>

                <x-notification-bell />

                <a href="{{ route('cart.show') }}" class="relative flex size-11 items-center justify-center rounded-full transition-[transform,background-color] duration-150 ease-out-strong hover:bg-white/15 active:scale-[0.97]">
                    <x-icon name="cart" class="size-6" />
                    @if ($cartCount > 0)
                        <span class="absolute top-1.5 right-1.5 flex h-4.5 min-w-4.5 items-center justify-center rounded-full bg-carrot px-1 text-[11px] font-bold text-forest tabular-nums">{{ $cartCount }}</span>
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

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 pt-5 pb-28 lg:px-6 lg:pt-8 lg:pb-16">
        {{ $slot }}
    </main>

    <footer class="hidden bg-forest text-cream lg:block">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-8">
            <div>
                <x-logo tone="white" />
                <p class="mt-1 text-sm text-cream/75">Delivery de comida orgánica.</p>
            </div>
            <nav class="flex gap-6 text-sm" aria-label="Pie de página">
                <a href="{{ route('leaves.index') }}" class="hover:underline">Hojitas Verdes</a>
                <a href="{{ route('forum.index') }}" class="hover:underline">Foro de reciclaje</a>
                <a href="{{ route('profile.edit') }}" class="hover:underline">Mi perfil</a>
            </nav>
        </div>
    </footer>

    <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-sage/20 bg-white pb-[env(safe-area-inset-bottom)] lg:hidden" aria-label="Principal">
        <ul class="mx-auto grid max-w-md grid-cols-5">
            @foreach ($nav as $item)
                @php $active = request()->routeIs($item['match']); @endphp
                <li>
                    <a
                        href="{{ route($item['route']) }}"
                        @if ($active) aria-current="page" @endif
                        class="flex h-16 flex-col items-center justify-center gap-1 text-[11px] transition-colors duration-150 {{ $active ? 'font-semibold text-leaf' : 'font-medium text-sage' }}"
                    >
                        <x-icon :name="$item['icon']" class="size-6" />
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
</x-layouts.base>
