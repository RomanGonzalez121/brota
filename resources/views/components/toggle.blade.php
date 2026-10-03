@props(['name', 'checked' => false, 'label' => null])

{{-- A switch backed by a real checkbox, so it works inside any form. --}}
<label {{ $attributes->class('inline-flex cursor-pointer items-center gap-3') }}>
    <input type="checkbox" name="{{ $name }}" value="1" @checked($checked) class="peer sr-only">
    <span class="relative h-6 w-11 shrink-0 rounded-full bg-sage/30 transition-colors duration-150 peer-checked:bg-leaf peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-leaf after:absolute after:top-0.5 after:left-0.5 after:size-5 after:rounded-full after:bg-white after:transition-transform after:duration-200 after:ease-out-strong peer-checked:after:translate-x-5"></span>
    @if ($label)
        <span class="text-sm font-medium">{{ $label }}</span>
    @endif
    {{ $slot }}
</label>
