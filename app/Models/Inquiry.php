<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Academic\Subject;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'delegate_id',
        'title',
        'question',
        'answer',
        'status',
        'answered_at',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
    ];

    /**
     * Get the student.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the subject.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the delegate.
     */
    public function delegate()
    {
        return $this->belongsTo(User::class, 'delegate_id');
    }

    /**
     * Scope for pending inquiries.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for answered inquiries.
     */
    public function scopeAnswered($query)
    {
        return $query->where('status', 'answered');
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'pending' => 'قيد الانتظار',
            'forwarded' => 'تم التحويل للدكتور',
            'answered' => 'تم الرد',
            'closed' => 'مغلق',
            default => $this->status,
        };
    }

    /**
     * Get status color.
     */
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'pending' => '#f59e0b',
            'forwarded' => '#3b82f6',
            'answered' => '#10b981',
            'closed' => '#6b7280',
            default => '#64748b',
        };
    }
}
