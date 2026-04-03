<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FlashcardPack extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'color',
        'icon',
        'display_mode',
        'notifications_enabled',
        'daily_notification_count',
        'repeat_cycle',
        'quiet_start',
        'quiet_end',
        'is_active',
        'is_public',
        'source_pack_id',
    ];

    protected $casts = [
        'notifications_enabled' => 'boolean',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'daily_notification_count' => 'integer',
    ];

    protected $appends = ['is_assigned'];

    // ── Relationships ──

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(FlashcardItem::class, 'pack_id');
    }

    /**
     * Returns items from this pack, or from the source pack if this is a clone.
     */
    public function effectiveItems()
    {
        if ($this->source_pack_id) {
            return FlashcardItem::where('pack_id', $this->source_pack_id);
        }
        return $this->items();
    }

    public function sourcePack(): BelongsTo
    {
        return $this->belongsTo(FlashcardPack::class, 'source_pack_id');
    }

    public function storeEntry(): HasOne
    {
        return $this->hasOne(PublicPackStore::class, 'pack_id');
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(FlashcardNotificationLog::class, 'pack_id');
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ── Helpers ──

    public function cardsCount(): int
    {
        return $this->effectiveItems()->count();
    }

    public function highPriorityCount(): int
    {
        return $this->effectiveItems()->whereIn('priority', ['high', 'critical'])->count();
    }

    /**
     * Get display mode text in Arabic.
     */
    public function getDisplayModeTextAttribute(): string
    {
        return match ($this->display_mode) {
            'flash_card' => 'بطاقة تعليمية',
            'one_line' => 'رسالة نصية',
            'qa' => 'سؤال وجواب',
            'mcq' => 'اختيارات',
            default => $this->display_mode,
        };
    }

    /**
     * Get repeat cycle text in Arabic.
     */
    public function getRepeatCycleTextAttribute(): string
    {
        return match ($this->repeat_cycle) {
            'daily' => 'يومي',
            'weekly' => 'أسبوعي',
            'monthly' => 'شهري',
            default => $this->repeat_cycle,
        };
    }

    /**
     * Determine if a pack was assigned by an admin rather than cloned.
     * Assigned packs have a source_pack_id but don't duplicate items locally.
     */
    public function getIsAssignedAttribute(): bool
    {
        if ($this->source_pack_id === null) {
            return false;
        }

        if ($this->relationLoaded('items')) {
            return $this->items->isEmpty();
        }

        // Check if any physical items exist for this pack ID
        return !$this->items()->exists();
    }
}
