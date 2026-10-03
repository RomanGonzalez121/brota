@php
    $demoRoles = [
        ['role' => 'customer', 'label' => 'Entrar como cliente', 'hint' => 'Pedí y seguí tu pedido', 'icon' => 'cart'],
        ['role' => 'courier', 'label' => 'Entrar como repartidor', 'hint' => 'Retirá y entregá', 'icon' => 'bike'],
        ['role' => 'restaurant', 'label' => 'Entrar como restaurante', 'hint' => 'Pedidos, menú y reportes', 'icon' => 'store'],
        ['role' => 'admin', 'label' => 'Entrar como administrador', 'hint' => 'Altas y moderación', 'icon' => 'shield'],
    ];
@endphp

<x-layouts.guest title="Ingresar">
    <h1 class="animate-rise text-3xl lg:text-4xl">Ingresá a tu cuenta</h1>
    <p class="mt-1.5 text-muted">Usá tu email y contraseña, o entrá directo con un usuario de prueba.</p>

    <form method="POST" action="{{ route('login.store') }}" class="mt-6 animate-rise space-y-4 [animation-delay:80ms]">
        @csrf
        <x-input label="Email" name="email" type="email" autocomplete="email" placeholder="vos@ejemplo.com" required />
        <x-input label="Contraseña" name="password" type="password" autocomplete="current-password" required />
        <x-button block size="lg">Ingresar</x-button>
        <p class="text-center text-sm text-muted">
            ¿Todavía no tenés cuenta?
            <a href="{{ route('register') }}" class="font-semibold text-leaf hover:underline">Registrate como cliente, repartidor o tienda</a>
        </p>
    </form>

    <div class="my-6 flex items-center gap-4 text-sm text-muted">
        <span class="h-px flex-1 bg-sage/25"></span>
        Usuarios de prueba
        <span class="h-px flex-1 bg-sage/25"></span>
    </div>

    <div class="grid gap-2 lg:grid-cols-2">
        @foreach ($demoRoles as $demo)
            <form method="POST" action="{{ route('login.demo', $demo['role']) }}" class="animate-rise" style="animation-delay: {{ 160 + $loop->index * 55 }}ms">
                @csrf
                <button type="submit" class="group flex w-full items-center gap-3 rounded-media border border-sage/25 bg-surface p-2.5 text-left transition-[transform,border-color] duration-150 ease-out-strong hover:border-leaf active:scale-[0.97]">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-leaf-sm bg-leaf/12 text-leaf">
                        <x-icon :name="$demo['icon']" class="transition-transform duration-200 ease-out-strong group-hover:-rotate-12 group-hover:scale-110" />
                    </span>
                    <span class="min-w-0">
                        <span class="block font-display text-[15px] font-bold">{{ $demo['label'] }}</span>
                        <span class="block text-sm text-muted">{{ $demo['hint'] }}</span>
                    </span>
                </button>
            </form>
        @endforeach
    </div>
</x-layouts.guest>
