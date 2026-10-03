@props(['title' => null, 'back' => null])

<x-layouts.base :title="$title" accent="sprout">
    <header class="sticky top-0 z-30 bg-bar text-white [view-transition-name:site-header]">
        <div class="relative mx-auto flex h-14 max-w-xl items-center gap-2 px-4">
            @if ($back)
                <a href="{{ $back }}" class="-ml-2 flex size-11 items-center justify-center rounded-full transition-[transform,background-color] duration-150 ease-out-strong hover:bg-white/15 active:scale-[0.97]">
                    <x-icon name="arrow-left" class="size-6" />
                    <span class="sr-only">Volver</span>
                </a>
            @else
                <x-logo tone="white" />
            @endif
            <span class="rounded-full bg-accent px-2.5 py-0.5 text-xs font-bold text-forest">Repartidor</span>

            <div class="ml-auto flex items-center gap-1">
                <x-theme-toggle class="hover:bg-white/15" />
                <x-notification-bell />
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex size-11 items-center justify-center rounded-full transition-[transform,background-color] duration-150 ease-out-strong hover:bg-white/15 active:scale-[0.97]">
                        <x-icon name="logout" class="size-6" />
                        <span class="sr-only">Cerrar sesión</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-xl flex-1 px-4 pt-5 pb-12">
        {{ $slot }}
    </main>
</x-layouts.base>
