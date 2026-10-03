@php
    $editing = $dish->exists;
    $sections = array_combine(\App\Models\Dish::SECTIONS, \App\Models\Dish::SECTIONS);
    $selectedTags = old('tags', $dish->tags ?? []);
@endphp

<x-layouts.panel :title="$editing ? 'Editar plato' : 'Agregar plato'" :subtitle="$editing ? $dish->name : 'Así lo van a ver los clientes en tu menú.'">
    <x-slot:actions>
        <x-button :href="route('restaurant.menu.index')" variant="secondary">
            <x-icon name="arrow-left" class="size-4" />
            Menú
        </x-button>
    </x-slot:actions>

    <x-card class="max-w-2xl">
        <form method="POST" action="{{ $editing ? route('restaurant.menu.update', $dish) : route('restaurant.menu.store') }}" data-smooth="off" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_12rem]">
                <x-input label="Nombre del plato" name="name" :value="$dish->name" placeholder="Ñoquis de calabaza" required />
                <x-select label="Sección" name="section" :options="$sections" :value="$dish->section" placeholder="Elegí una sección" required />
            </div>

            <x-textarea label="Descripción" name="description" rows="3" :value="$dish->description" hint="Ingredientes principales y tamaño de la porción." maxlength="200" required />

            <div class="max-w-48">
                <x-input label="Precio en pesos" name="price" type="number" inputmode="numeric" min="100" step="50" :value="$dish->price" placeholder="12500" required />
            </div>

            <fieldset>
                <legend class="mb-1.5 text-sm font-medium">Etiquetas</legend>
                <div class="flex flex-wrap gap-2">
                    @foreach (\App\Models\Dish::TAGS as $tag)
                        <label class="flex h-11 cursor-pointer items-center gap-2 rounded-full border border-sage/25 bg-surface pr-4 pl-3 text-[15px] font-medium transition-[transform,border-color,background-color,color] duration-150 ease-out-strong active:scale-[0.97] has-checked:border-leaf has-checked:bg-leaf/10 has-checked:text-leaf has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-leaf">
                            <input type="checkbox" name="tags[]" value="{{ $tag }}" @checked(in_array($tag, $selectedTags)) class="sr-only">
                            <x-icon :name="\App\Models\Dish::TAG_ICONS[$tag]" class="size-4.5" />
                            {{ $tag }}
                        </label>
                    @endforeach
                </div>
                @error('tags.*')
                    <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                @enderror
            </fieldset>

            <div class="rounded-media bg-page p-4">
                <x-toggle name="is_available" :checked="(bool) old('is_available', $dish->is_available)" label="Disponible para pedir" />
                <p class="mt-1.5 text-sm text-muted">Apagalo cuando se termine: el plato sigue en el menú, marcado como sin stock.</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-button>{{ $editing ? 'Guardar cambios' : 'Agregar al menú' }}</x-button>
                <x-button :href="route('restaurant.menu.index')" variant="ghost">Cancelar</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.panel>
