@use('App\Http\Controllers\Admin\ReportController')
@use('App\Models\ForumTopic')

<x-layouts.panel title="Moderación del foro" subtitle="Lo que la comunidad denunció. Cada decisión vale para todas las denuncias sobre ese mismo contenido.">
    <div class="grid grid-cols-3 gap-3 lg:gap-4">
        <x-stat-tile icon="flag" label="Para revisar" :value="$pending->count()" :tone="$pending->isEmpty() ? 'quiet' : 'accent'" data-reveal />
        <x-stat-tile icon="x" label="Bajas del mes" :value="$removedThisMonth" tone="quiet" data-reveal style="--i: 1" />
        <x-stat-tile icon="check" label="Descartadas del mes" :value="$dismissedThisMonth" data-reveal style="--i: 2" />
    </div>

    <section class="mt-8" aria-labelledby="pending-title">
        <h2 id="pending-title" class="flex items-center gap-2.5 text-lg">
            <span class="flex size-8 shrink-0 items-center justify-center rounded-leaf-sm bg-carrot/25 text-ink" aria-hidden="true"><x-icon name="flag" class="size-4.5" /></span>
            Para revisar
            <span data-pop class="font-sans text-sm font-medium tracking-normal text-muted tabular-nums">{{ $pending->count() }}</span>
            <span class="h-px flex-1 bg-sage/20" aria-hidden="true"></span>
        </h2>

        <div class="mt-3 space-y-3">
            @forelse ($pending as $reports)
                @php
                    $report = $reports->first();
                    $content = $report->reportable;
                    $isTopic = $content instanceof ForumTopic;
                @endphp
                <article id="denuncia-{{ $isTopic ? 'tema' : 'respuesta' }}-{{ $content->id }}" data-item class="rounded-media border border-sage/20 bg-surface p-4 lg:p-5" data-reveal style="--i: {{ min($loop->index, 4) }}">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-badge tone="neutral"><x-icon :name="$isTopic ? 'chat' : 'swap'" class="size-3.5 text-leaf" />{{ $isTopic ? 'Tema' : 'Respuesta' }}</x-badge>
                        <x-badge tone="accent">{{ $reports->count() === 1 ? '1 denuncia' : $reports->count().' denuncias' }}</x-badge>
                        {{-- A report that waits more than a day is late. --}}
                        <x-elapsed :since="$report->created_at" :late-after="1440" prefix="Denunciado hace" class="ml-auto" />
                    </div>

                    {{-- What was reported, quoted as it reads in the forum. --}}
                    <div class="mt-3 rounded-leaf bg-page p-4" x-data="{ whole: false }">
                        @if ($isTopic)
                            <h3 class="text-lg leading-snug">{{ $content->title }}</h3>
                        @else
                            <p class="text-sm text-muted">En el tema <span class="font-semibold text-ink">{{ $content->topic->title }}</span></p>
                        @endif
                        <p class="mt-1.5 text-[15px] whitespace-pre-line" :class="whole || 'line-clamp-3'" x-ref="body">{{ $content->body }}</p>
                        @if (mb_strlen($content->body) > 220)
                            <button type="button" @click="whole = ! whole" class="mt-1 h-9 rounded-md text-sm font-semibold text-leaf hover:underline" x-text="whole ? 'Ver menos' : 'Ver completo'">Ver completo</button>
                        @endif
                        <p class="mt-2 flex items-center gap-2 text-sm text-muted">
                            <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-sage/15 font-display text-xs font-extrabold text-ink" aria-hidden="true">{{ mb_substr($content->author->name, 0, 1) }}</span>
                            {{ $content->author->name }}, {{ $content->created_at->translatedFormat('j \d\e F') }}
                        </p>
                    </div>

                    <ul class="mt-3 space-y-2">
                        @foreach ($reports as $each)
                            <li class="flex items-start gap-2.5 text-[15px]">
                                <x-icon name="flag" class="mt-0.5 size-4 text-carrot" />
                                <p>
                                    <span class="font-semibold">{{ $each->reasonLabel() }}</span>
                                    <span class="text-muted">, según {{ $each->reporter?->firstName() ?? 'alguien' }}</span>
                                    @if ($each->details)
                                        <span class="block text-sm text-muted">«{{ $each->details }}»</span>
                                    @endif
                                </p>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-4 flex flex-wrap gap-2 border-t border-sage/15 pt-4">
                        <form method="POST" action="{{ route('admin.reports.remove', $report) }}">
                            @csrf
                            <x-button variant="danger">
                                <x-icon name="trash" class="size-4" />
                                Dar de baja
                            </x-button>
                        </form>
                        <form method="POST" action="{{ route('admin.reports.dismiss', $report) }}">
                            @csrf
                            <x-button variant="secondary">
                                <x-icon name="check" class="size-4" />
                                Dejar publicado
                            </x-button>
                        </form>
                    </div>
                </article>
            @empty
                <x-card>
                    <x-empty-state title="El foro está en calma">
                        No hay denuncias para revisar. Cuando alguien denuncie un tema o una respuesta, aparece acá.
                    </x-empty-state>
                </x-card>
            @endforelse
        </div>
    </section>

    @if ($resolved->isNotEmpty())
        <section class="mt-10" aria-labelledby="resolved-title">
            <h2 id="resolved-title" class="flex items-center gap-2.5 text-lg">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-leaf-sm bg-sprout-soft text-leaf" aria-hidden="true"><x-icon name="check" class="size-4.5" /></span>
                Ya resueltas
                <span class="h-px flex-1 bg-sage/20" aria-hidden="true"></span>
            </h2>

            <x-card padding="p-0" class="mt-3">
                <ul>
                    @foreach ($resolved as $report)
                        @php
                            $content = $report->reportable;
                            $isTopic = $content instanceof ForumTopic;
                            $removed = $report->status === ReportController::REMOVED;
                        @endphp
                        <li id="resuelta-{{ $report->id }}" data-item class="flex flex-wrap items-center gap-x-4 gap-y-2 border-b border-sage/15 px-4 py-3 last:border-b-0">
                            <div class="min-w-0 flex-1 basis-64">
                                <p class="truncate text-[15px] font-medium">{{ $isTopic ? $content->title : $content->body }}</p>
                                <p class="truncate text-sm text-muted">
                                    {{ $isTopic ? 'Tema' : 'Respuesta' }} de {{ $content->author->name }}.
                                    {{ $report->reasonLabel() }}.
                                    @if ($report->reviewed_at)
                                        Lo resolvió {{ $report->reviewer?->firstName() ?? 'el equipo' }} el {{ $report->reviewed_at->translatedFormat('j \d\e F') }}.
                                    @endif
                                </p>
                            </div>

                            <x-badge :tone="$removed ? 'danger' : 'neutral'">{{ $removed ? 'Dado de baja' : 'Sigue publicado' }}</x-badge>

                            @if ($removed)
                                <form method="POST" action="{{ route('admin.reports.restore', $report) }}">
                                    @csrf
                                    <x-button variant="ghost" size="sm" class="h-10">
                                        <x-icon name="refresh" class="size-4" />
                                        Restaurar
                                    </x-button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </x-card>
        </section>
    @endif
</x-layouts.panel>
