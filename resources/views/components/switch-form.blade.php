@props(['action', 'on', 'name', 'onLabel' => 'Activo', 'offLabel' => 'De baja'])

{{-- The switch is the form: one tap turns the thing on or off, and the thumb slides to say so. --}}
<form method="POST" action="{{ $action }}" {{ $attributes->class('shrink-0') }}>
    @csrf
    @method('PATCH')
    <button type="submit" role="switch" aria-checked="{{ $on ? 'true' : 'false' }}" class="group flex items-center gap-2.5 rounded-full py-1 pr-1 text-sm font-medium">
        <span class="w-16 text-right {{ $on ? 'text-leaf' : 'text-muted' }}">{{ $on ? $onLabel : $offLabel }}</span>
        <span class="relative h-6 w-11 rounded-full transition-colors duration-150 {{ $on ? 'bg-leaf' : 'bg-sage/30' }}">
            <span class="absolute top-0.5 left-0.5 size-5 rounded-full bg-white transition-transform duration-200 ease-out-strong group-active:scale-90 {{ $on ? 'translate-x-5' : '' }}"></span>
        </span>
        <span class="sr-only">{{ $name }}</span>
    </button>
</form>
