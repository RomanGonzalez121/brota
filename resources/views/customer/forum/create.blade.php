<x-layouts.customer title="Crear tema">
    <a href="{{ route('forum.index') }}" class="inline-flex h-11 items-center gap-1.5 rounded-md text-sm font-semibold text-leaf hover:underline">
        <x-icon name="arrow-left" class="size-4" />
        Foro de reciclaje
    </a>
    <h1 class="text-3xl leading-[1.1] lg:text-5xl">Crear tema</h1>

    <x-card class="mt-5 max-w-2xl">
        <form method="POST" action="{{ route('forum.store') }}" data-smooth="off" class="space-y-4">
            @csrf
            <x-input label="Título" name="title" placeholder="¿Qué querés preguntar o contar?" maxlength="120" required />
            <x-select label="Categoría" name="forum_category_id" :options="$categories" placeholder="Elegí una categoría" required />
            <x-textarea label="Mensaje" name="body" rows="7" hint="Contá el contexto: qué probaste, en qué barrio estás, qué necesitás." required />
            <div class="flex flex-wrap gap-2">
                <x-button>Publicar tema</x-button>
                <x-button :href="route('forum.index')" variant="ghost">Cancelar</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.customer>
