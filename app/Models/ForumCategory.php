<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug'])]
class ForumCategory extends Model
{
    public function topics(): HasMany
    {
        return $this->hasMany(ForumTopic::class);
    }

    public function icon(): string
    {
        return match ($this->slug) {
            'envases-y-retornables' => 'box',
            'separacion-de-residuos' => 'refresh',
            'huerta-urbana' => 'pot',
            'trueque-y-reutilizacion' => 'swap',
            default => 'sprout',
        };
    }
}
