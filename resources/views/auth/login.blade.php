@php
    $demoRoles = [
        ['role' => 'customer', 'label' => 'Entrar como cliente', 'hint' => 'Pedí, pagá y seguí tu pedido', 'icon' => 'cart'],
        ['role' => 'courier', 'label' => 'Entrar como repartidor', 'hint' => 'Retirá y entregá pedidos', 'icon' => 'bike'],
        ['role' => 'restaurant', 'label' => 'Entrar como restaurante', 'hint' => 'Gestioná pedidos, menú y reportes', 'icon' => 'store'],
        ['role' => 'admin', 'label' => 'Entrar como administrador', 'hint' => 'Altas, moderación y parámetros', 'icon' => 'shield'],
    ];
@endphp

<x-layouts.guest title="Ingresar">
    <h1 class="text-3xl">Ingresá a tu cuenta</h1>
    <p class="mt-2 text-sage">Usá tu email y contraseña, o entrá directo con un usuario de prueba.</p>

    <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-4">
        @csrf
        <x-input label="Email" name="email" type="email" autocomplete="email" placeholder="vos@ejemplo.com" required />
        <x-input label="Contraseña" name="password" type="password" autocomplete="current-password" required />
        <x-button block size="lg">Ingresar</x-button>
    </form>

    <div class="my-8 flex items-center gap-4 text-sm text-sage">
        <span class="h-px flex-1 bg-sage/25"></span>
        Usuarios de prueba
        <span class="h-px flex-1 bg-sage/25"></span>
    </div>

    <div class="grid gap-2.5">
        @foreach ($demoRoles as $demo)
            <form method="POST" action="{{ route('login.demo', $demo['role']) }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-2xl border border-sage/25 bg-white p-3 text-left transition-[transform,border-color] duration-150 ease-out-strong hover:border-leaf active:scale-[0.97]">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-leaf-sm bg-leaf/12 text-leaf">
                        <x-icon :name="$demo['icon']" />
                    </span>
                    <span class="min-w-0">
                        <span class="block font-display text-[15px] font-bold">{{ $demo['label'] }}</span>
                        <span class="block text-sm text-sage">{{ $demo['hint'] }}</span>
                    </span>
                </button>
            </form>
        @endforeach
    </div>
</x-layouts.guest>
