<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'order_id', 'points', 'reason', 'description', 'created_at', 'updated_at'])]
class LeafTransaction extends Model
{
    public const REASON_NEARBY = 'nearby_delivery';

    public const REASON_CONTAINERS = 'returnable_containers';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
