<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'method', 'card_last4', 'wallet', 'amount', 'status', 'receipt_number', 'created_at', 'updated_at'])]
class Payment extends Model
{
    public const WALLETS = ['Mercado Pago', 'Ualá', 'MODO', 'Naranja X'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function methodLabel(): string
    {
        if ((int) $this->amount === 0) {
            return 'Sin cargo: beneficio de tu nivel';
        }

        return $this->method === 'card'
            ? "Tarjeta terminada en {$this->card_last4}"
            : "Billetera {$this->wallet}";
    }
}
