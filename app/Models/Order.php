<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'code', 'customer_id', 'restaurant_id', 'courier_id', 'status', 'delivery_address', 'delivery_notes',
    'payment_method', 'subtotal', 'delivery_fee', 'total', 'returnable_containers', 'leaves_earned',
    'rejection_reason', 'created_at', 'updated_at',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'returnable_containers' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class)->orderBy('created_at');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Adds `delivered_at`: the moment the order reached the customer, taken
     * from its status history. `updated_at` will not do, because the row is
     * touched again when the Hojitas Verdes are credited.
     */
    public function scopeWithDeliveredAt(Builder $query): void
    {
        $query
            ->addSelect(['delivered_at' => OrderStatusLog::query()
                ->select('created_at')
                ->whereColumn('order_id', 'orders.id')
                ->where('status', OrderStatus::Delivered)
                ->limit(1)])
            ->withCasts(['delivered_at' => 'datetime']);
    }

    public function scopeDeliveredSince(Builder $query, DateTimeInterface $moment): void
    {
        $query
            ->where('status', OrderStatus::Delivered)
            ->whereHas('statusLogs', fn (Builder $logs) => $logs->where('status', OrderStatus::Delivered)->where('created_at', '>=', $moment));
    }

    public function itemsSummary(): string
    {
        return $this->items->map(fn (OrderItem $item) => "{$item->quantity} × {$item->name}")->join(', ');
    }
}
