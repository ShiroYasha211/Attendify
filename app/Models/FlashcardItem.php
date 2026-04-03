<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlashcardItem extends Model
{
    protected $fillable = [
        'pack_id',
        'front_content',
        'back_content',
        'options',
        'correct_option',
        'priority',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'correct_option' => 'integer',
        'sort_order' => 'integer',
    ];

    // ── Relationships ──

    public function pack(): BelongsTo
    {
        return $this->belongsTo(FlashcardPack::class, 'pack_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(FlashcardProgress::class, 'item_id');
    }

    // ── Helpers ──

    /**
     * Get priority text in Arabic.
     */
    public function getPriorityTextAttribute(): string
    {
        return match ($this->priority) {
            'normal' => 'عادية',
            'high' => 'عالية',
            'critical' => 'حرجة',
            default => $this->priority,
        };
    }

    /**
     * Get the user's progress for this item.
     */
    public function userProgress(int $userId)
    {
        return $this->progress()->where('user_id', $userId)->first();
    }
}
