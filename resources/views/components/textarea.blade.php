@props(['label', 'name', 'value' => null, 'rows' => 4, 'hint' => null])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->first($name);
@endphp

<div>
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium">{{ $label }}</label>
    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->class([
            'block w-full rounded-xl border bg-white px-3.5 py-2.5 text-base placeholder:text-sage/70',
            'transition-[border-color,box-shadow] duration-150 focus:border-leaf focus:ring-3 focus:ring-leaf/15 focus:outline-none',
            'border-sage/30' => ! $error,
            'border-danger' => $error,
        ]) }}
    >{{ old($name, $value) }}</textarea>
    @if ($error)
        <p id="{{ $id }}-error" class="mt-1.5 text-sm text-danger">{{ $error }}</p>
    @elseif ($hint)
        <p class="mt-1.5 text-sm text-sage">{{ $hint }}</p>
    @endif
</div>
