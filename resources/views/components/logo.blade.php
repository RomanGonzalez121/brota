@props(['tone' => 'leaf'])

{{-- The only place the site name is rendered. Rename it in config/app.php. --}}
<a href="{{ url('/') }}" {{ $attributes->class([
    'group/logo inline-flex items-center gap-1.5 font-display text-2xl font-extrabold tracking-tight',
    'text-white' => $tone === 'white',
    'text-leaf' => $tone === 'leaf',
]) }}>
    {{-- The leaf rustles when touched, and once when a visit starts. --}}
    <x-leaf class="logo-leaf size-6 origin-bottom-left text-sprout group-hover/logo:animate-rustle" />
    <span>{{ config('app.name') }}</span>
</a>
