@props(['title' => null])

<x-layouts.base :title="$title">
    <div class="grid flex-1 lg:grid-cols-[minmax(0,5fr)_minmax(0,6fr)]">
        {{-- Brand side: a plant growing out of the corner. Desktop only. --}}
        <aside class="relative hidden overflow-hidden bg-deep text-white lg:flex lg:flex-col lg:justify-between lg:p-12">
            <x-logo tone="white" class="text-3xl" />

            <div class="relative z-10 max-w-md">
                <p class="font-display text-4xl leading-tight font-extrabold text-balance">Comida de huerta, de tu barrio a tu mesa.</p>
                <p class="mt-4 text-lg text-white/85">Pedí a restaurantes orgánicos cerca tuyo y sumá Hojitas Verdes cada vez que cuidás el planeta.</p>
            </div>

            <div class="pointer-events-none absolute -right-24 top-24 origin-bottom-left animate-sway" aria-hidden="true">
                <x-leaf class="size-[24rem] rotate-[14deg] text-sprout/30" />
            </div>
            <div class="pointer-events-none absolute top-72 right-52 origin-bottom-right animate-sway [animation-delay:-3s]" aria-hidden="true">
                <x-leaf class="size-44 -rotate-[62deg] text-sprout/50" />
            </div>
        </aside>

        <main class="relative flex flex-col justify-center px-5 py-10 sm:px-10 lg:px-16">
            <x-theme-toggle class="absolute top-3 right-3 text-muted hover:bg-sage/15 hover:text-ink" />
            <div class="mx-auto w-full max-w-md">
                <x-logo class="mb-8 lg:hidden" />
                {{ $slot }}
            </div>
        </main>
    </div>
</x-layouts.base>
