<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['reportable_type', 'reportable_id', 'user_id', 'reason', 'details', 'status', 'reviewed_by', 'reviewed_at', 'created_at', 'updated_at'])]
class ForumReport extends Model
{
    public const REASONS = [
        'spam' => 'Es spam o publicidad',
        'offensive' => 'Es ofensivo o agresivo',
        'off_topic' => 'No tiene que ver con el reciclaje',
        'misinformation' => 'Da información falsa',
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function reasonLabel(): string
    {
        return self::REASONS[$this->reason] ?? $this->reason;
    }
}
