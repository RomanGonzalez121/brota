<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug'])]
class Category extends Model
{
    public function restaurants(): HasMany
    {
        return $this->hasMany(Restaurant::class);
    }

    /**
     * Name of the botanical icon shown next to the category.
     */
    public function icon(): string
    {
        return match ($this->slug) {
            'bowls-y-ensaladas' => 'bowl',
            'cocina-de-huerta' => 'pot',
            'jugos-y-licuados' => 'citrus',
            'panaderia-y-cafe' => 'wheat',
            'vegetariano' => 'carrot',
            default => 'sprout',
        };
    }
}
