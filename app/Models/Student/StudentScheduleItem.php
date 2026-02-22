<?php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StudentScheduleItem extends Model
{
    protected $fillable = [
        'user_id',
        'referenceable_type',
        'referenceable_id',
        'title',
        'scheduled_date',
        'is_completed',
        'completed_at',
        'sort_order',
        'note',
        'priority',
        'status',
        'item_type',
        'category_tag',
        'repeat_type',
        'reminder_at',
        'reminder_sent',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'reminder_at' => 'datetime',
        'reminder_sent' => 'boolean',
    ];

    // ─── Relationships ───

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function referenceable()
    {
        return $this->morphTo();
    }

    // ─── Scopes ───

    /** Study schedule items only */
    public function scopeStudyItems($query)
    {
        return $query->where('item_type', 'study');
    }

    /** Reminder items only */
    public function scopeReminders($query)
    {
        return $query->where('item_type', 'reminder');
    }

    /** Bookmarked resource items only */
    public function scopeMyResources($query)
    {
        return $query->where('item_type', 'resource');
    }

    /** Items due today */
    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_date', Carbon::today());
    }

    /** Overdue items (past date, not completed) */
    public function scopeOverdue($query)
    {
        return $query->where('is_completed', false)
            ->whereNotNull('scheduled_date')
            ->whereDate('scheduled_date', '<', Carbon::today());
    }

    /** High-priority items */
    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    /** Pending items (not completed) */
    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }

    /** Completed items */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    // ─── Accessors ───

    /** Check if this item is overdue */
    public function getIsOverdueAttribute(): bool
    {
        return !$this->is_completed
            && $this->scheduled_date
            && $this->scheduled_date->lt(Carbon::today());
    }

    /** Arabic priority label */
    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'high' => 'عاجل',
            'medium' => 'مهم',
            'low' => 'عادي',
            default => 'عادي',
        };
    }

    /** Priority color */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'high' => '#ef4444',
            'medium' => '#f59e0b',
            'low' => '#10b981',
            default => '#64748b',
        };
    }

    /** Status color */
    public function getStatusColorAttribute(): string
    {
        if ($this->is_overdue) return '#ef4444'; // Red for overdue
        return match ($this->status) {
            'in_progress' => '#3b82f6',
            'completed' => '#10b981',
            'overdue' => '#ef4444',
            default => '#64748b', // pending
        };
    }

    /** Arabic status label */
    public function getStatusLabelAttribute(): string
    {
        if ($this->is_overdue) return 'متأخر';
        return match ($this->status) {
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتمل',
            'overdue' => 'متأخر',
            default => 'قيد الانتظار',
        };
    }

    /** Arabic item type label */
    public function getItemTypeLabelAttribute(): string
    {
        return match ($this->item_type) {
            'study' => 'مذاكرة',
            'reminder' => 'تنبيه',
            'resource' => 'مصدر',
            default => 'مذاكرة',
        };
    }

    /** Display title: custom title or original content title */
    public function getDisplayTitleAttribute(): string
    {
        if ($this->title) return $this->title;
        if ($this->referenceable) return $this->referenceable->title ?? 'عنصر بدون عنوان';
        return 'عنصر محذوف';
    }
}
