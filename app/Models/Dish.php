<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['restaurant_id', 'section', 'name', 'description', 'price', 'tags', 'image', 'is_available'])]
class Dish extends Model
{
    /**
     * Menu sections, in the order they are listed.
     */
    public const SECTIONS = ['Entradas', 'Principales', 'Panadería', 'Postres', 'Bebidas'];

    public const TAGS = ['Vegano', 'Vegetariano', 'Sin TACC'];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_available' => 'boolean',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
