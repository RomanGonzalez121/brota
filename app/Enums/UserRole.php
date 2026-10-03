<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Courier = 'courier';
    case Restaurant = 'restaurant';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Cliente',
            self::Courier => 'Repartidor',
            self::Restaurant => 'Restaurante',
            self::Admin => 'Administrador',
        };
    }

    /**
     * Route name of the first screen each role lands on.
     */
    public function homeRoute(): string
    {
        return match ($this) {
            self::Customer => 'home',
            self::Courier => 'courier.deliveries.index',
            self::Restaurant => 'restaurant.orders.index',
            self::Admin => 'admin.restaurants.index',
        };
    }
}
