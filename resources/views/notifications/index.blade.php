@use('App\Enums\UserRole')

<x-layouts.app title="Notificaciones">
    @if (in_array(auth()->user()->role, [UserRole::Customer, UserRole::Courier]))
        <h1 class="mb-5 text-2xl lg:text-3xl">Notificaciones</h1>
    @endif

    <x-card padding="p-0" class="max-w-2xl overflow-hidden">
        @forelse ($notifications as $notification)
            <a href="{{ $notification->data['url'] }}" class="flex gap-3.5 border-b border-sage/15 px-4 py-4 transition-colors duration-150 last:border-b-0 hover:bg-page sm:px-5">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-full {{ $notification->wasUnread ? 'bg-leaf/12 text-leaf' : 'bg-sage/15 text-muted' }}">
                    <x-icon :name="$notification->data['icon']" />
                </span>
                <span class="min-w-0 flex-1">
                    <span class="flex items-center gap-2">
                        <span class="{{ $notification->wasUnread ? 'font-semibold' : 'font-medium' }}">{{ $notification->data['title'] }}</span>
                        @if ($notification->wasUnread)
                            <x-badge tone="sprout">Nueva</x-badge>
                        @endif
                    </span>
                    <span class="mt-0.5 block text-sm text-muted">{{ $notification->data['body'] }}</span>
                    <span class="mt-1 block text-xs text-muted">{{ $notification->created_at->diffForHumans() }}</span>
                </span>
                <x-icon name="chevron-right" class="mt-2.5 size-4 text-muted" />
            </a>
        @empty
            <x-empty-state title="Todavía no tenés notificaciones">
                Acá te avisamos cada vez que cambie el estado de un pedido.
            </x-empty-state>
        @endforelse
    </x-card>
</x-layouts.app>
