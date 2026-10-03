<x-layouts.customer title="Créditos de las fotos">
    <h1 class="text-3xl leading-[1.1] lg:text-5xl">Créditos de las fotos</h1>
    <p class="mt-1 max-w-2xl text-muted">
        Las fotos de restaurantes y platos vienen de Wikimedia Commons y se usan bajo licencias libres. Acá figura quién sacó cada una y con qué licencia la compartió.
    </p>

    <x-card padding="p-0" class="mt-5 max-w-3xl">
        <ul>
            @forelse ($credits as $credit)
                <li class="flex items-center gap-3.5 border-b border-sage/15 p-3.5 last:border-b-0">
                    <img src="{{ asset($credit['file']) }}" alt="" loading="lazy" class="size-14 shrink-0 rounded-xl object-cover">
                    <div class="min-w-0 flex-1 text-sm">
                        <p class="font-display text-[15px] font-bold">{{ $credit['subject'] }}</p>
                        <p class="truncate">
                            <a href="{{ $credit['source'] }}" target="_blank" rel="noopener" class="font-medium text-leaf hover:underline">{{ $credit['title'] }}</a>
                        </p>
                        <p class="text-muted">
                            {{ $credit['author'] }},
                            @if ($credit['license_url'])
                                <a href="{{ $credit['license_url'] }}" target="_blank" rel="noopener" class="hover:underline">{{ $credit['license'] }}</a>
                            @else
                                {{ $credit['license'] }}
                            @endif
                        </p>
                    </div>
                </li>
            @empty
                <li>
                    <x-empty-state title="Todavía no hay fotos cargadas" />
                </li>
            @endforelse
        </ul>
    </x-card>
</x-layouts.customer>
