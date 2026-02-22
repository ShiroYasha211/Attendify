<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Mark as read.
     */
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Get icon based on type.
     */
    public function getIconAttribute()
    {
        return match ($this->type) {
            'exam' => 'calendar-check',
            'assignment' => 'file-text',
            'resource' => 'folder',
            'announcement' => 'megaphone',
            'attendance' => 'user-x',
            default => 'bell',
        };
    }

    /**
     * Get color based on type.
     */
    public function getColorAttribute()
    {
        return match ($this->type) {
            'exam' => '#ef4444',
            'assignment' => '#f59e0b',
            'resource' => '#3b82f6',
            'announcement' => '#10b981',
            'attendance' => '#ef4444',
            default => '#6366f1',
        };
    }
}
