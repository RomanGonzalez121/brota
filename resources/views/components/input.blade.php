@props(['label', 'name', 'type' => 'text', 'value' => null, 'hint' => null])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->first($name);
    $isPassword = $type === 'password';

    $classes = [
        'block h-11 w-full rounded-xl border bg-surface px-3.5 text-base placeholder:text-muted/70',
        'transition-[border-color,box-shadow] duration-150 focus:border-leaf focus:ring-3 focus:ring-leaf/15 focus:outline-none',
        'pr-12' => $isPassword,
        'border-sage/30' => ! $error,
        'border-danger' => $error,
    ];
@endphp

<div>
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium">{{ $label }}</label>

    @if ($isPassword)
        {{-- Passwords can be revealed: the eye is a leaf that opens, and closes back into a bud. --}}
        <div class="relative" x-data="{ shown: false }">
            <input
                id="{{ $id }}"
                name="{{ $name }}"
                type="password"
                :type="shown ? 'text' : 'password'"
                @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
                {{ $attributes->class($classes) }}
            >
            <button
                type="button"
                @click="shown = ! shown"
                :aria-pressed="shown"
                :aria-label="shown ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                aria-label="Mostrar contraseña"
                class="absolute top-1/2 right-1 flex size-9 -translate-y-1/2 items-center justify-center rounded-full text-muted transition-[transform,color,background-color] duration-150 ease-out-strong hover:bg-leaf/10 hover:text-leaf active:scale-[0.94]"
            >
                <span class="absolute transition-[opacity,scale,filter] duration-200 ease-out-strong" :class="shown ? 'scale-75 opacity-0 blur-[2px]' : 'scale-100 opacity-100 blur-none'">
                    <x-icon name="eye-bud" />
                </span>
                <span class="absolute scale-75 opacity-0 blur-[2px] transition-[opacity,scale,filter] duration-200 ease-out-strong" :class="shown ? 'scale-100 opacity-100 blur-none' : 'scale-75 opacity-0 blur-[2px]'">
                    <x-icon name="eye-leaf" />
                </span>
            </button>
        </div>
    @else
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="{{ $type }}"
            value="{{ old($name, $value) }}"
            @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            {{ $attributes->class($classes) }}
        >
    @endif

    @if ($error)
        <p id="{{ $id }}-error" class="mt-1.5 text-sm text-danger">{{ $error }}</p>
    @elseif ($hint)
        <p class="mt-1.5 text-sm text-muted">{{ $hint }}</p>
    @endif
</div>
