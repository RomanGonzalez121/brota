<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

/*
 | URLs are in Spanish because people read them; route names and code stay
 | in English.
 */

Route::middleware('guest')->group(function () {
    Route::get('/ingresar', [LoginController::class, 'create'])->name('login');
    Route::post('/ingresar', [LoginController::class, 'store'])->name('login.store');
    Route::post('/ingresar/demo/{role}', [LoginController::class, 'demo'])->name('login.demo');
});

Route::middleware('auth')->group(function () {
    Route::post('/salir', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/notificaciones', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notificaciones/leer', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::middleware('role:customer')->group(function () {
        Route::view('/', 'placeholder')->name('home');
        Route::view('/restaurantes', 'placeholder')->name('restaurants.index');
        Route::view('/carrito', 'placeholder')->name('cart.show');
        Route::view('/pedidos', 'placeholder')->name('orders.index');
        Route::view('/pedidos/{order}', 'placeholder')->name('orders.show');
        Route::view('/perfil', 'placeholder')->name('profile.edit');
        Route::view('/hojitas', 'placeholder')->name('leaves.index');
        Route::view('/foro', 'placeholder')->name('forum.index');
    });

    Route::middleware('role:restaurant')->prefix('restaurante')->name('restaurant.')->group(function () {
        Route::view('/pedidos', 'placeholder')->name('orders.index');
        Route::view('/pedidos/{order}', 'placeholder')->name('orders.show');
        Route::view('/menu', 'placeholder')->name('menu.index');
        Route::view('/reportes', 'placeholder')->name('reports.index');
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
