@props(['title' => null, 'accent' => 'carrot'])

<!DOCTYPE html>
<html lang="es-AR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ? "{$title} | " : '' }}{{ config('app.name') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body data-accent="{{ $accent }}" class="flex min-h-dvh flex-col">
        {{ $slot }}

        <x-flash />
    </body>
</html>
