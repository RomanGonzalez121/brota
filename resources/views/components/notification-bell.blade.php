@php
    $user = auth()->user();
    $latest = $user->notifications()->limit(6)->get();
    $unread = $user->unreadNotifications()->count();
@endphp

{{-- Loaded from the database on each request; nothing here is real-time. --}}
<div x-data="bell({{ $unread }})" @keydown.escape.window="open = false" @click.outside="open = false">
    <button
        type="button"
        @click="open = ! open"
        :aria-expanded="open"
        aria-haspopup="true"
        class="group/bell relative flex size-11 items-center justify-center rounded-full text-white transition-[transform,background-color] duration-150 ease-out-strong hover:bg-white/15 active:scale-[0.97]"
    >
        <span class="origin-top group-hover/bell:animate-rustle" :class="ring && 'animate-ring'"><x-icon name="bell" class="size-6" /></span>
        @if ($unread > 0)
            <span class="absolute top-0 -right-1 flex h-4.5 min-w-4.5 items-center justify-center rounded-full bg-white px-1 text-[11px] font-bold text-forest tabular-nums">{{ $unread }}</span>
        @endif
        <span class="sr-only">Notificaciones{{ $unread > 0 ? ": {$unread} sin leer" : '' }}</span>
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition-[opacity,transform] duration-200 ease-out-strong"
        x-transition:enter-start="scale-95 opacity-0"
        x-transition:enter-end="scale-100 opacity-100"
        x-transition:leave="transition-[opacity,transform] duration-100 ease-out-strong"
        x-transition:leave-start="scale-100 opacity-100"
        x-transition:leave-end="scale-95 opacity-0"
        class="absolute top-full right-3 z-40 mt-2 w-[min(23rem,calc(100vw-1.5rem))] origin-top-right rounded-2xl border border-sage/20 bg-surface text-ink shadow-lift"
    >
        <div class="flex items-center justify-between px-4 pt-3.5 pb-2">
            <p class="font-display font-bold">Notificaciones</p>
            @if ($unread > 0)
                <form method="POST" action="{{ route('notifications.read') }}">
                    @csrf
                    <button type="submit" class="rounded-md text-sm font-medium text-leaf hover:underline">Marcar como leídas</button>
                </form>
            @endif
        </div>

        @forelse ($latest as $notification)
            <a href="{{ $notification->data['url'] }}" class="flex gap-3 px-4 py-2.5 transition-colors duration-150 hover:bg-page">
                <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full {{ $notification->read_at ? 'bg-sage/15 text-muted' : 'bg-leaf/12 text-leaf' }}">
                    <x-icon :name="$notification->data['icon']" class="size-4" />
                </span>
                <span class="min-w-0">
                    <span class="block text-sm {{ $notification->read_at ? 'font-medium' : 'font-semibold' }}">{{ $notification->data['title'] }}</span>
                    <span class="block truncate text-sm text-muted">{{ $notification->data['body'] }}</span>
                    <span class="block text-xs text-muted">{{ $notification->created_at->diffForHumans() }}</span>
                </span>
            </a>
        @empty
            <p class="px-4 py-6 text-center text-sm text-muted">Todavía no tenés notificaciones.</p>
        @endforelse

        <a href="{{ route('notifications.index') }}" class="block rounded-b-2xl border-t border-sage/15 px-4 py-3 text-center text-sm font-semibold text-leaf hover:bg-page">Ver todas</a>
    </div>
</div>
