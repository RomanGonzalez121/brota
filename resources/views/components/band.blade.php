{{--
    A full-width colour band behind a section. Topics are separated by a change
    of background and by air, not by boxes around everything. One very faint
    leaf drifts behind the content, slow enough to be felt more than seen.
--}}
<section {{ $attributes->class('bleed-sprout relative') }}>
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute top-6 -left-14 origin-bottom-right animate-sway-slow">
            <x-leaf class="size-56 -rotate-[78deg] text-leaf/8" />
        </div>

    </div>

    <div class="relative">{{ $slot }}</div>
</section>
