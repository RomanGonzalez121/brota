@use('App\Enums\UserRole')

@php
    $inPanel = ! in_array(auth()->user()->role, [UserRole::Customer, UserRole::Courier]);

    // The icon says what kind of news it is; the colour says how much it needs you.
    $tones = [
        'receipt' => 'bg-carrot/20 text-ink',
        'flag' => 'bg-carrot/20 text-ink',
        'bike' => 'bg-sprout-soft text-ink',
        'menu' => 'bg-sprout-soft text-ink',
        'check' => 'bg-leaf/12 text-leaf',
        'x' => 'bg-danger/10 text-danger',
    ];

    [$shortcutRoute, $shortcutLabel, $shortcutIcon] = match (auth()->user()->role) {
        UserRole::Customer => ['orders.index', 'Ver mis pedidos', 'receipt'],
        UserRole::Restaurant => ['restaurant.orders.index', 'Ir al tablero de pedidos', 'receipt'],
        UserRole::Courier => ['courier.deliveries.index', 'Ver mis entregas', 'bike'],
        UserRole::Admin => ['admin.reports.index', 'Ver denuncias', 'flag'],
    };

    $chip = 'flex h-10 items-center gap-2 rounded-full border px-4 text-[15px] font-medium transition-[transform,border-color,background-color,color] duration-150 ease-out-strong active:scale-[0.97]';
    $chipOn = 'border-leaf bg-leaf text-on-leaf';
    $chipOff = 'border-sage/25 bg-surface hover:border-leaf hover:text-leaf';
@endphp

<x-layouts.app title="Notificaciones">
    @unless ($inPanel)
        <h1 class="text-3xl leading-[1.1] lg:text-5xl">Notificaciones</h1>
    @endunless

    {{-- The list on the left, a summary on the right: the whole width is put to work. --}}
    <div class="{{ $inPanel ? '' : 'mt-5' }} grid grid-cols-1 gap-8 lg:grid-cols-[minmax(0,1fr)_21rem] lg:items-start lg:gap-10">
    <div>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <nav class="flex gap-2" aria-label="Filtro">
                <a href="{{ route('notifications.index') }}" @if (! $onlyUnread) aria-current="true" @endif class="{{ $chip }} {{ $onlyUnread ? $chipOff : $chipOn }}">
                    Todas
                    <span class="text-sm tabular-nums opacity-75">{{ $total }}</span>
                </a>
                <a href="{{ route('notifications.index', ['ver' => 'sin-leer']) }}" @if ($onlyUnread) aria-current="true" @endif class="{{ $chip }} {{ $onlyUnread ? $chipOn : $chipOff }}">
                    Sin leer
                    <span class="text-sm tabular-nums opacity-75">{{ $unread }}</span>
                </a>
            </nav>

            @if ($unread > 0)
                <form method="POST" action="{{ route('notifications.read') }}">
                    @csrf
                    <x-button variant="ghost" size="sm" class="h-10">
                        <x-icon name="check" class="size-4" />
                        Marcar todas como leídas
                    </x-button>
                </form>
            @endif
        </div>

        @forelse ($groups as $day => $notifications)
            <section class="mt-8 first-of-type:mt-6" aria-labelledby="day-{{ $loop->index }}">
                <h2 id="day-{{ $loop->index }}" class="flex items-center gap-3 font-sans text-sm font-semibold tracking-normal text-muted" data-reveal>
                    {{ $day }}
                    <span class="h-px flex-1 bg-sage/20" aria-hidden="true"></span>
                </h2>

                <ul class="mt-3 space-y-2">
                    @foreach ($notifications as $notification)
                        @php $isUnread = $notification->read_at === null; @endphp
                        <li data-reveal style="--i: {{ min($loop->index, 6) }}">
                            <a
                                href="{{ route('notifications.open', $notification->id) }}"
                                @class([
                                    'group relative flex items-start gap-4 overflow-hidden rounded-media border p-4 transition-[transform,border-color,background-color] duration-150 ease-out-strong active:scale-[0.99] sm:p-5',
                                    'border-leaf/25 bg-surface hover:border-leaf/60' => $isUnread,
                                    'border-transparent hover:border-sage/25 hover:bg-surface' => ! $isUnread,
                                ])
                            >
                                @if ($isUnread)
                                    <span class="absolute inset-y-0 left-0 w-1 bg-leaf" aria-hidden="true"></span>
                                @endif

                                <span class="flex size-11 shrink-0 items-center justify-center rounded-leaf-sm {{ $isUnread ? ($tones[$notification->data['icon']] ?? 'bg-leaf/12 text-leaf') : 'bg-sage/12 text-muted' }}">
                                    <x-icon :name="$notification->data['icon']" class="transition-transform duration-200 ease-out-strong group-hover:-rotate-6 group-hover:scale-110" />
                                </span>

                                <span class="min-w-0 flex-1">
                                    <span class="flex flex-wrap items-baseline justify-between gap-x-3">
                                        <span class="font-display text-[17px] {{ $isUnread ? 'font-extrabold' : 'font-bold text-ink/80' }}">{{ $notification->data['title'] }}</span>
                                        <time datetime="{{ $notification->created_at->toIso8601String() }}" class="shrink-0 text-sm text-muted tabular-nums">
                                            {{ $notification->created_at->isToday() ? $notification->created_at->diffForHumans() : $notification->created_at->translatedFormat('j \d\e F, H:i').' h' }}
                                        </time>
                                    </span>
                                    <span class="mt-0.5 block text-[15px] {{ $isUnread ? '' : 'text-muted' }}">{{ $notification->data['body'] }}</span>
                                </span>

                                <span class="flex shrink-0 items-center gap-2 self-center">
                                    @if ($isUnread)
                                        <span class="size-2.5 rounded-full bg-carrot" aria-hidden="true"></span>
                                        <span class="sr-only">Sin leer</span>
                                    @endif
                                    <x-icon name="chevron-right" class="size-4 text-muted transition-transform duration-200 ease-out-strong group-hover:translate-x-1" />
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <x-card class="mt-6">
                <x-empty-state :title="$onlyUnread ? 'Estás al día' : 'Todavía no tenés notificaciones'">
                    {{ $onlyUnread ? 'No te queda ninguna notificación sin leer.' : 'Acá te avisamos cada vez que pase algo con un pedido.' }}
                    @if ($onlyUnread)
                        <x-slot:action>
                            <x-button :href="route('notifications.index')" variant="secondary">Ver todas</x-button>
                        </x-slot:action>
                    @endif
                </x-empty-state>
            </x-card>
        @endforelse
    </div>

    <aside class="relative overflow-hidden rounded-leaf bg-deep p-6 text-cream lg:sticky lg:top-24" aria-labelledby="summary-title" data-reveal>
        <div class="pointer-events-none absolute right-4 bottom-4 origin-bottom-left animate-sway" aria-hidden="true">
            <x-leaf class="size-16 rotate-[8deg] text-foliage-light/45" />
        </div>

        <h2 id="summary-title" class="relative font-sans text-sm font-semibold tracking-normal text-cream/75">Sin leer</h2>
        <p class="relative mt-1 font-display text-6xl leading-none font-extrabold tabular-nums">
            <span x-data="countUp({{ $unread }})" x-text="value">{{ $unread }}</span>
        </p>

        @if ($kinds->isNotEmpty())
            <dl class="relative mt-5 space-y-2 border-t border-cream/15 pt-4 text-[15px]">
                @foreach ($kinds as $kind => $count)
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-cream/80">{{ $kind }}</dt>
                        <dd class="font-display font-extrabold tabular-nums">{{ $count }}</dd>
                    </div>
                @endforeach
            </dl>
        @else
            <p class="relative mt-4 max-w-[15rem] text-cream/80">Estás al día. Cuando pase algo nuevo lo vas a ver acá y en la campanita.</p>
        @endif

        <x-button :href="route($shortcutRoute)" variant="secondary" class="relative mt-6">
            <x-icon :name="$shortcutIcon" class="size-4" />
            {{ $shortcutLabel }}
        </x-button>
    </aside>
    </div>
</x-layouts.app>
