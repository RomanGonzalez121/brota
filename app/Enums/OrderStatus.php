<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Received = 'received';
    case Preparing = 'preparing';
    case OnTheWay = 'on_the_way';
    case Delivered = 'delivered';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Received => 'Recibido',
            self::Preparing => 'En preparación',
            self::OnTheWay => 'En camino',
            self::Delivered => 'Entregado',
            self::Rejected => 'Rechazado',
        };
    }

    /**
     * What the customer reads under each step of the tracking screen.
     */
    public function hint(): string
    {
        return match ($this) {
            self::Received => 'El restaurante ya tiene tu pedido.',
            self::Preparing => 'Lo están cocinando.',
            self::OnTheWay => 'El repartidor va hacia tu dirección.',
            self::Delivered => '¡Buen provecho!',
            self::Rejected => 'El restaurante no pudo tomar el pedido.',
        };
    }

    /**
     * Badge tone, matching the tones of the badge component.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Received => 'accent',
            self::Preparing, self::OnTheWay => 'sprout',
            self::Delivered => 'success',
            self::Rejected => 'danger',
        };
    }

    /**
     * The happy path, in the order the customer sees it.
     *
     * @return list<self>
     */
    public static function timeline(): array
    {
        return [self::Received, self::Preparing, self::OnTheWay, self::Delivered];
    }

    public function position(): int
    {
        return (int) array_search($this, self::timeline(), true);
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::Received, self::Preparing, self::OnTheWay], true);
    }

    public function next(): ?self
    {
        return match ($this) {
            self::Received => self::Preparing,
            self::Preparing => self::OnTheWay,
            self::OnTheWay => self::Delivered,
            default => null,
        };
    }
}
