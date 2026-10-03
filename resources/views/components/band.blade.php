{{--
    A full-width colour band behind a section. Topics are separated by a change
    of background and by air, not by boxes around everything. On desktop one
    faint leaf drifts in the empty corner, whole, slow enough to be felt more
    than seen.
--}}
<section {{ $attributes->class('bleed-sprout relative') }}>
    <div class="pointer-events-none absolute right-10 bottom-10 hidden origin-bottom-left animate-sway-slow lg:block" aria-hidden="true">
        <x-leaf class="size-36 rotate-[6deg] text-foliage-faint opacity-15" />
    </div>

    <div class="relative">{{ $slot }}</div>
</section>
