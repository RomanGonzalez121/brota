@props(['title' => null])

@use('App\Enums\UserRole')

{{-- For screens every role shares: picks the layout of whoever is signed in. --}}
@php
    $layout = match (auth()->user()->role) {
        UserRole::Customer => 'layouts.customer',
        UserRole::Courier => 'layouts.courier',
        default => 'layouts.panel',
    };
@endphp

<x-dynamic-component :component="$layout" :title="$title">
    {{ $slot }}
</x-dynamic-component>
