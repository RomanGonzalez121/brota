@props(['name', 'value', 'checked' => false])

<label {{ $attributes->class('flex cursor-pointer items-start gap-3 rounded-2xl border border-sage/25 bg-surface p-4 transition-[transform,border-color,background-color] duration-150 ease-out-strong active:scale-[0.98] has-checked:border-leaf has-checked:bg-leaf/8 has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-leaf') }}>
    <input type="radio" name="{{ $name }}" value="{{ $value }}" @checked($checked) class="mt-0.5 size-5 shrink-0 accent-leaf focus:outline-none">
    <span class="min-w-0 flex-1">{{ $slot }}</span>
</label>
