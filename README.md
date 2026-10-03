# Brotá

Maqueta navegable de una aplicación de delivery de comida orgánica. Tiene cuatro roles (cliente, repartidor, restaurante y administrador) y datos de prueba cargados, para recorrer el flujo completo de un pedido de punta a punta.

Hecha con Laravel, Blade, Tailwind CSS y Alpine.js.

## Usuarios de prueba

En la pantalla de ingreso hay cuatro botones "Entrar como…" que entran directo, sin escribir nada. Si preferís escribir, todos usan la contraseña `brota1234`:

| Rol | Email | Dónde empieza |
| --- | --- | --- |
| Cliente | `cliente@brota.test` | Inicio, con restaurantes y pedidos |
| Repartidor | `repartidor@brota.test` | Sus entregas |
| Restaurante | `restaurante@brota.test` | Tablero de pedidos de La Huerta de Palermo |
| Administrador | `admin@brota.test` | Restaurantes |

También se puede crear una cuenta nueva como cliente, repartidor o tienda desde "Registrate".

### Un recorrido para probar

1. Como **cliente**, elegí un restaurante, agregá platos, confirmá el pedido y pagá.
2. Como **restaurante**, aceptá el pedido en el tablero y marcá "Salió a reparto". Si entrás como La Huerta de Palermo, pedí ahí en el paso 1.
3. Como **repartidor**, abrí la entrega, mirá la ruta y marcá "Entregué el pedido".
4. Volvé al **cliente**: el pedido figura entregado y sumó Hojitas Verdes.
5. Como **administrador**, revisá las denuncias del foro y cambiá las reglas de las Hojitas Verdes.

## Cómo levantarlo en tu compu

Necesitás PHP 8.3 o más nuevo, Composer, Node.js y una base MySQL o MariaDB (por ejemplo, la de XAMPP).

```sh
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Creá una base vacía llamada `brota`. El archivo `.env` ya viene apuntando a ella con el usuario `root` sin contraseña, que es lo que trae XAMPP; si la tuya es distinta, cambiá las líneas `DB_` del `.env`.

```sh
php artisan migrate --seed
npm run build
php artisan serve
```

Abrí <http://localhost:8000>.

Para volver a dejar los datos como al principio:

```sh
php artisan migrate:fresh --seed
```

Si no querés instalar MySQL, también anda con SQLite: en el `.env` poné `DB_CONNECTION=sqlite`, borrá las demás líneas `DB_` y corré `php artisan migrate --seed` (crea el archivo de la base solo).

### Tests

```sh
php artisan test
```

Abren cada pantalla de cada rol y recorren los flujos principales. Usan una base en memoria: no tocan la tuya.

## Qué incluye

| Módulo | Qué hace |
| --- | --- |
| M1 | Registro, ingreso y roles; perfil y direcciones del cliente |
| M2 | Catálogo de restaurantes y menús, con búsqueda y filtros |
| M3 | Carrito, confirmación, estados e historial de pedidos |
| M4 | Pago con tarjeta o billetera y comprobante |
| M5 | Tablero del restaurante y edición del menú |
| M6 | Notificaciones de pedidos nuevos y cambios de estado |
| M7 | Entregas del repartidor, con la ruta en un mapa |
| M8 | Reportes del restaurante: ganancias, ventas por día y platos más pedidos |
| M9 | Hojitas Verdes: puntos, niveles con beneficios y reglas configurables |
| M10 | Foro de reciclaje, con denuncias y moderación |
| M11 | Administración: altas y bajas de restaurantes y repartidores |

### Qué está simulado

Es una maqueta, así que algunas partes imitan lo que haría un servicio real:

- **Pago:** siempre se aprueba y no cobra nada.
- **Mapa:** es un dibujo, sin GPS ni servicio de mapas. La posición del repartidor se calcula por el tiempo desde que retiró el pedido.
- **Tiempo real:** las pantallas consultan cada pocos segundos si hay novedades.
- **Notificaciones:** solo dentro de la aplicación; no se mandan emails ni avisos al celular.
- **Distancias:** la distancia de cada restaurante al cliente es un dato cargado, no se calcula.

## Publicarlo con un link

El repositorio trae lo necesario para subirlo a [Render](https://render.com) en su plan gratuito:

1. Subí el proyecto a un repositorio de GitHub.
2. En Render, elegí **New > Blueprint** y seleccioná el repositorio. Render lee `render.yaml` y arma el servicio solo.
3. Cuando termina, la aplicación queda en una dirección del estilo `https://brota.onrender.com`.

Dos cosas a saber de esa versión publicada:

- **Se duerme** si nadie entra por un rato. La primera visita después tarda alrededor de un minuto.
- **Los datos se reinician** cada vez que arranca: vuelve a cargar los datos de prueba, y lo que alguien haya pedido o registrado se pierde. Para una demo conviene, porque siempre abre limpia.

Es una configuración para mostrar la maqueta, no para producción: usa SQLite y el servidor incorporado de PHP.

## Créditos de las fotos

Las fotos de restaurantes y platos vienen de Wikimedia Commons. La autoría y la licencia de cada una están en la página "Créditos de las fotos" de la aplicación y en `public/images/credits.json`.
