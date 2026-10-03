@props(['label', 'name', 'options' => [], 'value' => null, 'placeholder' => null])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->first($name);
    $selected = old($name, $value);
@endphp

<div>
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium">{{ $label }}</label>
    <div class="relative">
        <select
            id="{{ $id }}"
            name="{{ $name }}"
            @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            {{ $attributes->class([
                'block h-11 w-full appearance-none rounded-xl border bg-white pr-10 pl-3.5 text-base',
                'transition-[border-color,box-shadow] duration-150 focus:border-leaf focus:ring-3 focus:ring-leaf/15 focus:outline-none',
                'border-sage/30' => ! $error,
                'border-danger' => $error,
            ]) }}
        >
            @if ($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) $selected === (string) $optionValue)>{{ $optionLabel }}</option>
            @endforeach
        </select>
        <x-icon name="chevron-down" class="pointer-events-none absolute top-1/2 right-3.5 size-4 -translate-y-1/2 text-sage" />
    </div>
    @if ($error)
        <p id="{{ $id }}-error" class="mt-1.5 text-sm text-danger">{{ $error }}</p>
    @endif
</div>
