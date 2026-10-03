@props(['title' => null])

<x-layouts.base :title="$title">
    {{-- On desktop the screen is exactly one viewport tall: no page scroll. --}}
    <div class="grid flex-1 grid-rows-[auto_1fr] lg:h-dvh lg:grid-cols-[minmax(0,5fr)_minmax(0,6fr)] lg:grid-rows-1 lg:overflow-hidden">
        {{-- Brand side: two leaves in a light breeze. Desktop only. --}}
        <aside class="relative hidden overflow-hidden bg-deep text-white lg:flex lg:flex-col lg:justify-between lg:p-12">
            <x-logo tone="white" class="relative z-10 text-3xl" />

            {{-- Sized from the viewport height so the leaves never reach the text below. --}}
            <div class="pointer-events-none absolute top-[11dvh] right-6 size-[min(29rem,54dvh)]" aria-hidden="true">
                <div class="absolute top-0 right-0 size-[80%]" data-parallax="10">
                    <div class="size-full origin-bottom-left animate-sway"><x-leaf class="size-full rotate-[14deg] text-foliage/30" /></div>
                </div>
                <div class="absolute bottom-[6%] left-0 size-[37%]" data-parallax="22">
                    <div class="size-full origin-bottom-right animate-sway [animation-delay:-3s]"><x-leaf class="size-full -rotate-[62deg] text-foliage-light/50" /></div>
                </div>
            </div>

            <div class="relative z-10 max-w-md">
                <p class="font-display text-4xl leading-tight font-extrabold text-balance">Comida de huerta, de tu barrio a tu mesa.</p>
                <p class="mt-4 text-lg text-white/85">Pedí a restaurantes orgánicos cerca tuyo y sumá Hojitas Verdes cada vez que cuidás el planeta.</p>
            </div>
        </aside>

        {{-- On phones the same promise opens the screen, so nobody meets a bare form. --}}
        <header class="relative overflow-hidden bg-deep px-5 pt-4 pb-6 text-white sm:px-10 lg:hidden">
            <div class="pointer-events-none absolute right-2 bottom-3 origin-bottom-left animate-sway" aria-hidden="true">
                <x-leaf class="size-20 rotate-[10deg] text-foliage/30" />
            </div>

            <div class="relative flex items-center justify-between">
                <x-logo tone="white" />
                <x-theme-toggle class="-mr-2 hover:bg-white/15" />
            </div>

            <p class="relative mt-4 max-w-[17rem] font-display text-2xl leading-tight font-extrabold text-balance">Comida de huerta, de tu barrio a tu mesa.</p>
            <p class="relative mt-2 max-w-[16rem] text-sm text-white/85">Restaurantes orgánicos cerca tuyo, y Hojitas Verdes por cuidar el planeta.</p>
        </header>

        <main class="relative flex flex-col px-5 py-7 sm:px-10 lg:justify-center lg:overflow-y-auto lg:px-16 lg:py-8">
            <div class="absolute top-3 right-3 hidden lg:block"><x-theme-toggle class="text-muted hover:bg-sage/15 hover:text-ink" /></div>
            <div class="mx-auto w-full max-w-md lg:max-w-xl">
                {{ $slot }}
            </div>
        </main>
    </div>
</x-layouts.base>
