<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class QrAttendanceSession extends Model
{
    protected $fillable = [
        'subject_id',
        'delegate_id',
        'date',
        'title',
        'lecture_number',
        'current_token',
        'token_expires_at',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'token_expires_at' => 'datetime',
    ];

    // ────────────── Relationships ──────────────

    public function subject()
    {
        return $this->belongsTo(\App\Models\Academic\Subject::class);
    }

    public function delegate()
    {
        return $this->belongsTo(User::class, 'delegate_id');
    }

    // ────────────── Token Logic ──────────────

    /**
     * Generate a new random token and set its expiry.
     * Token lifetime = 30 seconds (generous enough for 10-second rotation).
     */
    public function rotateToken(): string
    {
        $newToken = Str::random(48) . bin2hex(random_bytes(8));
        $this->current_token = $newToken;
        $this->token_expires_at = Carbon::now()->addSeconds(30);
        $this->save();

        return $newToken;
    }

    /**
     * Check if the given token matches and is still valid.
     */
    public function isTokenValid(string $token): bool
    {
        return $this->current_token === $token
            && $this->token_expires_at->isFuture()
            && $this->status === 'active';
    }

    /**
     * Check if the session is still active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    // ────────────── Scopes ──────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
