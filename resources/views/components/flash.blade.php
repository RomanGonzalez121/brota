@php
    $message = session('status');
    $isError = session('status_tone') === 'danger';
@endphp

@if ($message)
    <div
        x-data="{ show: false }"
        x-init="$nextTick(() => show = true); setTimeout(() => show = false, 4500)"
        x-show="show"
        x-cloak
        x-transition:enter="transition-[opacity,transform] duration-300 ease-out-strong"
        x-transition:enter-start="-translate-y-3 opacity-0 lg:translate-y-3"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition-[opacity,transform] duration-150 ease-out-strong"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="-translate-y-3 opacity-0 lg:translate-y-3"
        role="status"
        class="fixed inset-x-4 top-[4.25rem] z-50 mx-auto flex max-w-sm items-center gap-3 rounded-2xl bg-forest px-4 py-3 text-cream shadow-lift lg:top-auto lg:bottom-6"
    >
        <span class="flex size-7 shrink-0 items-center justify-center rounded-full {{ $isError ? 'bg-danger text-white' : 'bg-sprout text-forest' }}">
            <x-icon :name="$isError ? 'x' : 'check'" class="size-4" />
        </span>
        <p class="text-sm font-medium">{{ $message }}</p>
    </div>
@endif
