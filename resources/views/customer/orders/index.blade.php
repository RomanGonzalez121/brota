<x-layouts.customer title="Pedidos">
    <h1 class="text-3xl leading-[1.1] lg:text-5xl">Tus pedidos</h1>

    @if ($open->isEmpty() && $past->isEmpty())
        <x-card class="mt-5">
            <x-empty-state title="Todavía no hiciste ningún pedido">
                Cuando pidas, acá vas a poder seguirlo paso a paso.
                <x-slot:action>
                    <x-button :href="route('restaurants.index')">Ver restaurantes</x-button>
                </x-slot:action>
            </x-empty-state>
        </x-card>
    @endif

    @if ($open->isNotEmpty())
        <section class="mt-5" aria-labelledby="open-title">
            <h2 id="open-title" class="text-lg">En curso</h2>
            <ul class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($open as $order)
                    <li data-reveal style="--i: {{ $loop->index % 3 }}"><x-order-card :order="$order" :href="route('orders.show', $order)" class="h-full" /></li>
                @endforeach
            </ul>
        </section>
    @endif

    @if ($past->isNotEmpty())
        <section class="mt-8" aria-labelledby="past-title">
            <h2 id="past-title" class="text-lg">Anteriores</h2>
            <ul class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($past as $order)
                    <li data-reveal style="--i: {{ $loop->index % 3 }}"><x-order-card :order="$order" :href="route('orders.show', $order)" class="h-full" /></li>
                @endforeach
            </ul>
        </section>
    @endif
</x-layouts.customer>
