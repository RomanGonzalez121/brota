@props(['tone' => 'leaf'])

{{-- The only place the site name is rendered. Rename it in config/app.php. --}}
<a href="{{ url('/') }}" {{ $attributes->class([
    'inline-flex items-center gap-1.5 font-display text-2xl font-extrabold tracking-tight',
    'text-white' => $tone === 'white',
    'text-leaf' => $tone === 'leaf',
]) }}>
    <x-leaf class="size-6 text-sprout" />
    <span>{{ config('app.name') }}</span>
</a>
