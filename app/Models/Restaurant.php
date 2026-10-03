<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'user_id', 'category_id', 'name', 'slug', 'tagline', 'description', 'address', 'neighborhood',
    'distance_km', 'rating', 'delivery_fee', 'eta_minutes', 'image', 'is_featured', 'is_active',
])]
class Restaurant extends Model
{
    protected function casts(): array
    {
        return [
            'distance_km' => 'float',
            'rating' => 'float',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Opens a restaurant for its owner. It starts with sensible defaults for
     * what nobody is asked: fee, time and a made-up distance, since the mockup
     * has no map service.
     *
     * @param  array{restaurant_name: string, category_id: int|string, tagline: string, address: string, neighborhood: string}  $data
     */
    public static function open(User $owner, array $data): self
    {
        $slug = Str::slug($data['restaurant_name']);

        if (static::query()->where('slug', $slug)->exists()) {
            $slug .= '-'.Str::lower(Str::random(4));
        }

        return static::create([
            'user_id' => $owner->id,
            'category_id' => $data['category_id'],
            'name' => $data['restaurant_name'],
            'slug' => $slug,
            'tagline' => $data['tagline'],
            'description' => $data['tagline'],
            'address' => $data['address'],
            'neighborhood' => $data['neighborhood'],
            'distance_km' => random_int(6, 45) / 10,
            'rating' => 0,
            'delivery_fee' => 1500,
            'eta_minutes' => 30,
        ]);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Restaurants customers can order from: enabled and with at least one dish loaded.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->has('dishes');
    }
}
