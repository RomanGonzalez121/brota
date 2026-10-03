{{--
    Light / dark switch. A flower by day, a moon with a leaf by night. The two
    icons cross with scale, rotation and a little blur; the page itself is
    revealed in a circle that grows from this button (see toggleTheme in app.js).
--}}
<button
    type="button"
    data-theme-toggle
    onclick="window.toggleTheme?.(this)"
    {{ $attributes->class('relative flex size-11 shrink-0 items-center justify-center rounded-full transition-[transform,background-color] duration-150 ease-out-strong active:scale-[0.97]') }}
>
    <span class="absolute transition-[opacity,scale,rotate,filter] duration-300 ease-out-strong dark:scale-50 dark:-rotate-90 dark:opacity-0 dark:blur-[2px]">
        <x-icon name="flower" class="size-6" />
    </span>
    <span class="absolute scale-50 rotate-90 opacity-0 blur-[2px] transition-[opacity,scale,rotate,filter] duration-300 ease-out-strong dark:scale-100 dark:rotate-0 dark:opacity-100 dark:blur-none">
        <x-icon name="moon-leaf" class="size-6" />
    </span>
    <span class="sr-only">Modo oscuro</span>
</button>
