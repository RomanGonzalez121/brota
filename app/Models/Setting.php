<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'value', 'label', 'help'])]
class Setting extends Model
{
    public const LEAVES_NEARBY = 'leaves_nearby_delivery';

    public const NEARBY_MAX_KM = 'leaves_nearby_max_km';

    public const LEAVES_CONTAINERS = 'leaves_returnable_containers';

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    public static function number(string $key, float $default = 0): float
    {
        return (float) (static::query()->find($key)?->value ?? $default);
    }
}
