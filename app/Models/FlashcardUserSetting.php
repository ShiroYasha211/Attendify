<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlashcardUserSetting extends Model
{
    protected $fillable = [
        'user_id',
        'smart_review_enabled',
        'active_from_time',
        'active_to_time',
        'quiet_start',
        'quiet_end',
        'daily_card_limit',
        'smart_review_frequency_minutes',
        'auto_restart_enabled',
        'prompt_mode',
    ];

    protected $casts = [
        'smart_review_enabled' => 'boolean',
        'daily_card_limit' => 'integer',
        'smart_review_frequency_minutes' => 'integer',
        'auto_restart_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
