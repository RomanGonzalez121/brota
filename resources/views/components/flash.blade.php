{{--
    The toast. It shows the message a redirect left in the session, and any
    message the page sends later without reloading:
    $dispatch('toast', { message: '…', error: false }).
--}}
<div
    x-data="toast(@js(session('status')), @js(session('status_tone') === 'danger'))"
    @toast.window="show($event.detail)"
    data-flash="{{ session('status') }}"
    data-flash-error="{{ session('status_tone') === 'danger' ? '1' : '0' }}"
    x-show="visible"
    x-cloak
    x-transition:enter="transition-[opacity,transform] duration-300 ease-out-strong"
    x-transition:enter-start="-translate-y-3 opacity-0 lg:translate-y-3"
    x-transition:enter-end="translate-y-0 opacity-100"
    x-transition:leave="transition-[opacity,transform] duration-150 ease-out-strong"
    x-transition:leave-start="translate-y-0 opacity-100"
    x-transition:leave-end="-translate-y-3 opacity-0 lg:translate-y-3"
    role="status"
    class="fixed inset-x-4 top-[4.25rem] z-50 mx-auto flex max-w-sm items-center gap-3 rounded-2xl bg-deep px-4 py-3 text-cream shadow-lift lg:top-auto lg:bottom-6"
>
    <span class="flex size-7 shrink-0 items-center justify-center rounded-full" :class="error ? 'bg-danger text-white' : 'bg-sprout text-forest'">
        <x-icon name="x" class="size-4" x-show="error" />
        <x-icon name="check" class="size-4" x-show="! error" />
    </span>
    <p class="text-sm font-medium" x-text="message"></p>
</div>
