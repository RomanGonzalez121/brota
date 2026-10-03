<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'dish_id', 'name', 'unit_price', 'quantity'])]
class OrderItem extends Model
{
    public $timestamps = false;

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * The dish as it is on the menu today. The line keeps its own name and
     * price, so it still reads right if the dish was later removed.
     */
    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }

    public function lineTotal(): int
    {
        return $this->unit_price * $this->quantity;
    }
}
