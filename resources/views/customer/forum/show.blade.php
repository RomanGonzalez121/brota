<x-layouts.customer :title="$topic->title">
    <a href="{{ route('forum.index') }}" class="inline-flex h-11 items-center gap-1.5 rounded-md text-sm font-semibold text-leaf hover:underline">
        <x-icon name="arrow-left" class="size-4" />
        Foro de reciclaje
    </a>

    <div class="max-w-3xl" x-data>
        <article>
            <x-badge tone="sprout">{{ $topic->category->name }}</x-badge>
            <h1 class="mt-2 text-2xl lg:text-3xl">{{ $topic->title }}</h1>

            <x-card class="mt-4">
                <div class="flex items-center gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-leaf/12 font-display font-extrabold text-leaf" aria-hidden="true">{{ mb_substr($topic->author->name, 0, 1) }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="font-display font-bold">{{ $topic->author->name }}</p>
                        <p class="text-sm text-sage">{{ $topic->created_at->diffForHumans() }}</p>
                    </div>
                    <button type="button" @click="$dispatch('open-sheet', { name: 'report', type: 'topic', id: {{ $topic->id }}, label: 'este tema' })" class="flex h-11 items-center gap-1.5 rounded-md px-2 text-sm font-medium text-sage transition-colors duration-150 hover:text-danger">
                        <x-icon name="flag" class="size-4" />
                        Denunciar
                    </button>
                </div>
                <div class="mt-4 space-y-3 whitespace-pre-line">{{ $topic->body }}</div>
            </x-card>
        </article>

        <section class="mt-8" aria-labelledby="replies-title">
            <h2 id="replies-title" class="text-xl">{{ $replies->count() === 1 ? '1 respuesta' : $replies->count().' respuestas' }}</h2>

            @if ($replies->isEmpty())
                <p class="mt-2 text-sage">Todavía nadie respondió. Sé la primera persona en ayudar.</p>
            @else
                <ul class="mt-3 space-y-3">
                    @foreach ($replies as $reply)
                        <li id="respuesta-{{ $reply->id }}" class="scroll-mt-24 rounded-2xl border border-sage/20 bg-white p-4 sm:p-5">
                            <div class="flex items-center gap-3">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-sage/15 font-display text-sm font-extrabold" aria-hidden="true">{{ mb_substr($reply->author->name, 0, 1) }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-display text-[15px] font-bold">{{ $reply->author->name }}</p>
                                    <p class="text-sm text-sage">{{ $reply->created_at->diffForHumans() }}</p>
                                </div>
                                <button type="button" @click="$dispatch('open-sheet', { name: 'report', type: 'reply', id: {{ $reply->id }}, label: @js('la respuesta de '.$reply->author->firstName()) })" class="flex size-11 items-center justify-center rounded-full text-sage transition-colors duration-150 hover:text-danger">
                                    <x-icon name="flag" class="size-4" />
                                    <span class="sr-only">Denunciar la respuesta de {{ $reply->author->name }}</span>
                                </button>
                            </div>
                            <p class="mt-3 whitespace-pre-line">{{ $reply->body }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <x-card class="mt-6">
            <form method="POST" action="{{ route('forum.replies.store', $topic) }}" class="space-y-4">
                @csrf
                <x-textarea label="Tu respuesta" name="body" rows="4" placeholder="Compartí lo que sabés o lo que te funcionó" required />
                <x-button>Publicar respuesta</x-button>
            </form>
        </x-card>
    </div>

    <x-sheet name="report" title="Denunciar contenido">
        <form method="POST" action="{{ route('forum.reports.store') }}">
            @csrf
            <input type="hidden" name="type" :value="payload.type">
            <input type="hidden" name="id" :value="payload.id">

            <p class="text-sage">Contanos qué problema tiene <span x-text="payload.label"></span>. Lo revisa el equipo de moderación.</p>

            <fieldset class="mt-4 space-y-2">
                <legend class="sr-only">Motivo</legend>
                @foreach ($reasons as $value => $label)
                    <x-radio-card name="reason" :value="$value" :checked="$loop->first" class="p-3.5">
                        <span class="text-[15px] font-medium">{{ $label }}</span>
                    </x-radio-card>
                @endforeach
            </fieldset>

            <div class="mt-4">
                <x-input label="Detalle" name="details" id="report-details" hint="Opcional." maxlength="255" />
            </div>

            <x-button variant="danger" block class="mt-5">Enviar denuncia</x-button>
        </form>
    </x-sheet>
</x-layouts.customer>
