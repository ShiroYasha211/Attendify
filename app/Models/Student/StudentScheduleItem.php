<?php

namespace App\Models\Student;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referenceable()
    {
        return $this->morphTo();
    }

    public function scopeStudyItems($query)
    {
        return $query->where('item_type', 'study');
    }

    public function scopeReminders($query)
    {
        return $query->where('item_type', 'reminder');
    }

    public function scopeMyResources($query)
    {
        return $query->where('item_type', 'resource');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_date', Carbon::today());
    }

    public function scopeOverdue($query)
    {
        return $query->where('is_completed', false)
            ->whereNotNull('scheduled_date')
            ->whereDate('scheduled_date', '<', Carbon::today());
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function getIsOverdueAttribute(): bool
    {
        return !$this->is_completed
            && $this->scheduled_date
            && $this->scheduled_date->lt(Carbon::today());
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'high' => 'Urgent',
            'medium' => 'Important',
            'low' => 'Normal',
            default => 'Normal',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'high' => '#ef4444',
            'medium' => '#f59e0b',
            'low' => '#10b981',
            default => '#64748b',
        };
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->is_overdue) {
            return '#ef4444';
        }

        return match ($this->status) {
            'in_progress' => '#3b82f6',
            'completed' => '#10b981',
            'overdue' => '#ef4444',
            default => '#64748b',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_overdue) {
            return 'Overdue';
        }

        return match ($this->status) {
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'overdue' => 'Overdue',
            default => 'Pending',
        };
    }

    public function getItemTypeLabelAttribute(): string
    {
        return match ($this->item_type) {
            'study' => 'Study Task',
            'reminder' => 'Reminder',
            'resource' => 'Saved Resource',
            'assignment' => 'Assignment',
            default => 'Study Task',
        };
    }

    public function getDisplayTitleAttribute(): string
    {
        if ($this->title) {
            return $this->title;
        }

        if ($this->referenceable) {
            return $this->referenceable->title ?? 'Untitled Item';
        }

        return 'Deleted Item';
    }
}
