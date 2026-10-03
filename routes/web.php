<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\ForumController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\LeafController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\RestaurantController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Restaurant\MenuController;
use App\Http\Controllers\Restaurant\OrderController as RestaurantOrderController;
use App\Http\Controllers\Restaurant\ReportController;
use Illuminate\Support\Facades\Route;

/*
 | URLs are in Spanish because people read them; route names and code stay
 | in English.
 */

Route::middleware('guest')->group(function () {
    Route::get('/ingresar', [LoginController::class, 'create'])->name('login');
    Route::post('/ingresar', [LoginController::class, 'store'])->name('login.store');
    Route::post('/ingresar/demo/{role}', [LoginController::class, 'demo'])->name('login.demo');

    Route::get('/registrarse', [RegisterController::class, 'create'])->name('register');
    Route::post('/registrarse', [RegisterController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/salir', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/notificaciones', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notificaciones/leer', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::get('/notificaciones/{notification}/abrir', [NotificationController::class, 'open'])->name('notifications.open');

    Route::middleware('role:customer')->group(function () {
        Route::get('/', HomeController::class)->name('home');
        Route::get('/restaurantes', [RestaurantController::class, 'index'])->name('restaurants.index');
        Route::get('/restaurantes/{restaurant}', [RestaurantController::class, 'show'])->name('restaurants.show');

        Route::get('/carrito', [CartController::class, 'show'])->name('cart.show');
        Route::delete('/carrito', [CartController::class, 'destroy'])->name('cart.destroy');
        Route::post('/carrito/platos/{dish}', [CartController::class, 'store'])->name('cart.store');
        Route::patch('/carrito/platos/{dish}', [CartController::class, 'update'])->name('cart.update');

        Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
        Route::get('/pago', [CheckoutController::class, 'payment'])->name('payment.create');
        Route::post('/pago', [CheckoutController::class, 'pay'])->name('payment.store');

        Route::get('/pedidos', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/pedidos/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('/pedidos/{order}/comprobante', [OrderController::class, 'receipt'])->name('orders.receipt');

        Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/perfil', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/perfil/direcciones', [ProfileController::class, 'storeAddress'])->name('profile.addresses.store');
        Route::put('/perfil/direcciones/{address}', [ProfileController::class, 'updateAddress'])->name('profile.addresses.update');
        Route::delete('/perfil/direcciones/{address}', [ProfileController::class, 'destroyAddress'])->name('profile.addresses.destroy');

        Route::get('/hojitas', LeafController::class)->name('leaves.index');

        Route::get('/creditos', function () {
            $file = public_path('images/credits.json');

            return view('customer.credits', [
                'credits' => file_exists($file) ? json_decode(file_get_contents($file), true) : [],
            ]);
        })->name('credits');

        Route::get('/foro', [ForumController::class, 'index'])->name('forum.index');
        Route::get('/foro/crear', [ForumController::class, 'create'])->name('forum.create');
        Route::post('/foro', [ForumController::class, 'store'])->name('forum.store');
        Route::post('/foro/denuncias', [ForumController::class, 'report'])->name('forum.reports.store');
        Route::get('/foro/{topic}', [ForumController::class, 'show'])->name('forum.show');
        Route::post('/foro/{topic}/respuestas', [ForumController::class, 'reply'])->name('forum.replies.store');
    });

    Route::middleware('role:restaurant')->prefix('restaurante')->name('restaurant.')->group(function () {
        Route::get('/pedidos', [RestaurantOrderController::class, 'index'])->name('orders.index');
        Route::get('/pedidos/pendientes', [RestaurantOrderController::class, 'pending'])->name('orders.pending');
        Route::get('/pedidos/{order}', [RestaurantOrderController::class, 'show'])->name('orders.show');
        Route::post('/pedidos/{order}/aceptar', [RestaurantOrderController::class, 'accept'])->name('orders.accept');
        Route::post('/pedidos/{order}/rechazar', [RestaurantOrderController::class, 'reject'])->name('orders.reject');
        Route::post('/pedidos/{order}/avanzar', [RestaurantOrderController::class, 'advance'])->name('orders.advance');

        Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
        Route::get('/menu/crear', [MenuController::class, 'create'])->name('menu.create');
        Route::post('/menu', [MenuController::class, 'store'])->name('menu.store');
        Route::get('/menu/{dish}/editar', [MenuController::class, 'edit'])->name('menu.edit');
        Route::put('/menu/{dish}', [MenuController::class, 'update'])->name('menu.update');
        Route::patch('/menu/{dish}/disponibilidad', [MenuController::class, 'toggle'])->name('menu.toggle');
        Route::delete('/menu/{dish}', [MenuController::class, 'destroy'])->name('menu.destroy');
        Route::get('/reportes', [ReportController::class, 'index'])->name('reports.index');
    });

    Route::middleware('role:courier')->prefix('repartidor')->name('courier.')->group(function () {
        Route::view('/entregas', 'placeholder')->name('deliveries.index');
        Route::view('/entregas/{order}', 'placeholder')->name('deliveries.show');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::view('/restaurantes', 'placeholder')->name('restaurants.index');
        Route::view('/repartidores', 'placeholder')->name('couriers.index');
        Route::view('/foro/denuncias', 'placeholder')->name('reports.index');
        Route::view('/parametros', 'placeholder')->name('settings.edit');
    });
});
