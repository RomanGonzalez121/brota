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

    /**
     * Icon that heads each section in the restaurant's menu.
     */
    public const SECTION_ICONS = [
        'Entradas' => 'pot', 'Principales' => 'bowl', 'Panadería' => 'wheat', 'Postres' => 'flower', 'Bebidas' => 'citrus',
    ];

    public const TAGS =['Vegano', 'Vegetariano', 'Sin TACC'];

    /**
     * Botanical icon for each dietary tag.
     */
    public const TAG_ICONS = ['Vegano' => 'sprout', 'Vegetariano' => 'carrot', 'Sin TACC' => 'wheat-off'];

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
