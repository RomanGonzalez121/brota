<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>

## Alcance funcional y módulos

### Fase 1: Núcleo (urgente)

**M1. Gestión de usuarios y autenticación** (transversal)
- Registro e inicio de sesión
- Roles: cliente, repartidor, dueño de restaurante, administrador
- Perfil y direcciones del cliente

**M2. Catálogo de restaurantes y menús** (lado cliente)
- Listado y búsqueda de restaurantes de comida orgánica
- Visualización de platos, precios y detalles
- Filtros (categoría, cercanía, etc.)

**M3. Pedidos**
- Carrito y selección de platos
- Confirmación del pedido
- Estados del pedido: recibido, en preparación, en camino, entregado
- Historial de pedidos

**M4. Pagos**
- Pago con tarjeta
- Pago con billeteras virtuales
- Confirmación y comprobante
- Integración con pasarela de pago externa

**M5. Gestión de comercios** (lado restaurante)
- Recepción de pedidos en tiempo real
- Aceptar o rechazar pedidos y actualizar su estado
- Carga y edición del menú (platos, precios, disponibilidad)

**M6. Notificaciones** (transversal)
- Avisos de nuevo pedido al comercio
- Avisos de cambio de estado al cliente

### Fase 2: Operación completa

**M7. Logística y reparto**
- Asignación de pedidos a repartidores
- Mapa con la ubicación de los pedidos y la ruta hacia ellos
- Actualización del estado de entrega
- Integración con servicio de mapas

**M8. Reportes para restaurantes**
- Ganancias del mes
- Detalle de ventas y platos más pedidos

### Fase 3: Diferenciadores "Eco"

**M9. Sistema de puntos "Hojitas Verdes"**
- Reglas de asignación: pedido entregado cerca, o pedido con envases retornables
- Saldo e historial de puntos por cliente
- Configuración de reglas por el administrador
- (Opcional a futuro) canje de puntos

**M10. Foro de reciclaje**
- Creación de temas y respuestas
- Moderación (denuncias, bajas de contenido)
- Categorías o etiquetas

### Módulo transversal

**M11. Administración**
- Alta y baja de restaurantes y repartidores
- Moderación del foro
- Parámetros del sistema (como las reglas de puntos)

### Resumen de prioridades

| Fase | Módulos | Objetivo |
| --- | --- | --- |
| 1 | M1, M2, M3, M4, M5, M6 | Que se pueda pedir y los comercios reciban |
| 2 | M7, M8 | Reparto con mapa y reportes |
| 3 | M9, M10, M11 | Puntos y foro |

### Cómo usar esta lista

Esta lista es la referencia del alcance. Al trabajar, nombrar los módulos por su código (M1 a M11) y respetar el orden de las fases, salvo que Roman pida otra cosa.
