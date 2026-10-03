@props(['name', 'title'])

{{--
    Bottom sheet on phones, centred dialog on desktop. Open it from anywhere with
    $dispatch('open-sheet', { name: '…', …data }); the data is available as `payload`.
--}}
<div
    x-data="{ open: false, payload: {} }"
    x-on:open-sheet.window="if ($event.detail.name === '{{ $name }}') { payload = $event.detail; open = true; $nextTick(() => $refs.panel.querySelector('input:not([type=hidden]), textarea, select')?.focus()) }"
    x-on:keydown.escape.window="open = false"
    x-on:smooth-done.window="open = false"
    x-show="open"
    x-cloak
    x-transition:leave="transition-opacity duration-150 ease-out-strong"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-6"
    role="dialog"
    aria-modal="true"
    aria-labelledby="sheet-{{ $name }}-title"
>
    <div
        x-show="open"
        x-transition:enter="transition-opacity duration-200 ease-out-strong"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        @click="open = false"
        class="absolute inset-0 bg-forest/50"
    ></div>

    <div
        x-ref="panel"
        x-show="open"
        x-transition:enter="transition-[opacity,transform] duration-300 ease-drawer"
        x-transition:enter-start="translate-y-full opacity-0 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
        class="relative max-h-[92dvh] w-full max-w-md overflow-y-auto overscroll-contain rounded-t-3xl bg-surface p-5 pb-[calc(1.25rem+env(safe-area-inset-bottom))] shadow-lift sm:rounded-3xl"
    >
        <div class="mb-4 flex items-center justify-between gap-4">
            <h2 id="sheet-{{ $name }}-title" class="text-xl">{{ $title }}</h2>
            <button type="button" @click="open = false" class="-mr-2 flex size-11 items-center justify-center rounded-full text-muted transition-[transform,background-color] duration-150 ease-out-strong hover:bg-page active:scale-[0.97]">
                <x-icon name="x" />
                <span class="sr-only">Cerrar</span>
            </button>
        </div>

        {{ $slot }}
    </div>
</div>
