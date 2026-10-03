@php
    $chip = 'flex h-11 shrink-0 items-center rounded-full border px-4 text-[15px] font-medium transition-[transform,border-color,background-color,color] duration-150 ease-out-strong active:scale-[0.97]';
    $chipOn = 'border-leaf bg-leaf text-white';
    $chipOff = 'border-sage/25 bg-white hover:border-leaf hover:text-leaf';
@endphp

<x-layouts.customer title="Foro de reciclaje">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl leading-[1.1] lg:text-5xl">Foro de reciclaje</h1>
            <p class="mt-1 max-w-xl text-sage">Preguntá, compartí lo que te funciona y ayudá a otros vecinos a generar menos basura.</p>
        </div>
        <x-button :href="route('forum.create')">
            <x-icon name="plus" class="size-4" />
            Crear tema
        </x-button>
    </div>

    <nav class="-mx-4 mt-5 flex gap-2 overflow-x-auto px-4 pb-1 lg:mx-0 lg:flex-wrap lg:px-0" aria-label="Categorías">
        <a href="{{ route('forum.index') }}" @if (! $category) aria-current="true" @endif class="{{ $chip }} {{ $category ? $chipOff : $chipOn }}">Todas</a>
        @foreach ($categories as $item)
            <a href="{{ route('forum.index', ['categoria' => $item->slug]) }}" @if ($category === $item->slug) aria-current="true" @endif class="{{ $chip }} {{ $category === $item->slug ? $chipOn : $chipOff }}">
                {{ $item->name }}
            </a>
        @endforeach
    </nav>

    <x-card padding="p-0" class="mt-5 max-w-3xl">
        @forelse ($topics as $topic)
            <a href="{{ route('forum.show', $topic) }}" class="block border-b border-sage/15 p-4 transition-colors duration-150 last:border-b-0 hover:bg-cream sm:p-5">
                <x-badge tone="sprout">{{ $topic->category->name }}</x-badge>
                <h2 class="mt-2 text-lg leading-snug">{{ $topic->title }}</h2>
                <p class="mt-1 line-clamp-2 text-[15px] text-sage">{{ $topic->body }}</p>
                <p class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-sage">
                    <span>{{ $topic->author->name }}</span>
                    <span>{{ $topic->created_at->diffForHumans() }}</span>
                    <span class="flex items-center gap-1.5 tabular-nums">
                        <x-icon name="chat" class="size-4" />
                        {{ $topic->replies_count === 1 ? '1 respuesta' : $topic->replies_count.' respuestas' }}
                    </span>
                </p>
            </a>
        @empty
            <x-empty-state title="Todavía no hay temas en esta categoría">
                Abrí el primero y empezá la conversación.
                <x-slot:action>
                    <x-button :href="route('forum.create')">Crear tema</x-button>
                </x-slot:action>
            </x-empty-state>
        @endforelse
    </x-card>
</x-layouts.customer>
