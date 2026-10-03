@props(['title' => null])

<x-layouts.base :title="$title">
    {{-- On desktop the screen is exactly one viewport tall: no page scroll. --}}
    <div class="grid flex-1 lg:h-dvh lg:grid-cols-[minmax(0,5fr)_minmax(0,6fr)] lg:overflow-hidden">
        {{-- Brand side: two leaves in a light breeze. Desktop only. --}}
        <aside class="relative hidden overflow-hidden bg-deep text-white lg:flex lg:flex-col lg:justify-between lg:p-12">
            <x-logo tone="white" class="relative z-10 text-3xl" />

            {{-- Sized from the viewport height so the leaves never reach the text below. --}}
            <div class="pointer-events-none absolute top-[11dvh] right-6 size-[min(29rem,54dvh)]" aria-hidden="true">
                <div class="absolute top-0 right-0 size-[80%] origin-bottom-left animate-sway">
                    <x-leaf class="size-full rotate-[14deg] text-sprout/30" />
                </div>
                <div class="absolute bottom-[6%] left-0 size-[37%] origin-bottom-right animate-sway [animation-delay:-3s]">
                    <x-leaf class="size-full -rotate-[62deg] text-sprout/50" />
                </div>
            </div>

            <div class="relative z-10 max-w-md">
                <p class="font-display text-4xl leading-tight font-extrabold text-balance">Comida de huerta, de tu barrio a tu mesa.</p>
                <p class="mt-4 text-lg text-white/85">Pedí a restaurantes orgánicos cerca tuyo y sumá Hojitas Verdes cada vez que cuidás el planeta.</p>
            </div>
        </aside>

        <main class="relative flex flex-col justify-center px-5 py-8 sm:px-10 lg:overflow-y-auto lg:px-16">
            <div class="absolute top-3 right-3"><x-theme-toggle class="text-muted hover:bg-sage/15 hover:text-ink" /></div>
            <div class="mx-auto w-full max-w-md lg:max-w-xl">
                <x-logo class="mb-8 lg:hidden" />
                {{ $slot }}
            </div>
        </main>
    </div>
</x-layouts.base>
