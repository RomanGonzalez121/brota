@props(['title' => null, 'accent' => 'carrot'])

<!DOCTYPE html>
<html lang="es-AR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ? "{$title} | " : '' }}{{ config('app.name') }}</title>

        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48">
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

        {{-- Before first paint: mark that JS runs (for scroll reveals) and restore the saved theme. --}}
        <script>
            document.documentElement.classList.add('js');
            try {
                if (! sessionStorage.getItem('visited')) {
                    document.documentElement.classList.add('first-visit');
                    sessionStorage.setItem('visited', '1');
                }
            } catch (e) {}
            try {
                if (localStorage.getItem('theme') === 'dark') document.documentElement.dataset.theme = 'dark';
            } catch (e) {}
        </script>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body data-accent="{{ $accent }}" class="flex min-h-dvh flex-col">
        {{ $slot }}

        <x-flash />
    </body>
</html>
