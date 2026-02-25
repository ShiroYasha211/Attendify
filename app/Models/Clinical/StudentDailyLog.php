<?php

namespace App\Models\Clinical;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Support\Str;

class StudentDailyLog extends Model
{
    protected $fillable = [
        'student_id',
        'training_center_id',
        'department_id',
        'doctor_id',
        'history_count',
        'exam_count',
        'did_round',
        'round_notes',
        'qr_token',
        'status',
        'confirmed_by',
        'confirmed_at',
        'doctor_notes',
        'log_date',
        'log_time',
    ];

    protected $casts = [
        'did_round' => 'boolean',
        'confirmed_at' => 'datetime',
        'log_date' => 'date',
    ];

    // ─── Relationships ───

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function trainingCenter()
    {
        return $this->belongsTo(TrainingCenter::class, 'training_center_id');
    }

    public function department()
    {
        return $this->belongsTo(ClinicalDepartment::class, 'department_id');
    }

    public function activities()
    {
        return $this->hasMany(DailyLogActivity::class, 'daily_log_id');
    }

    // ─── Scopes ───

    public function scopePending($q)
    {
        return $q->where('status', 'pending');
    }
    public function scopeConfirmed($q)
    {
        return $q->where('status', 'confirmed');
    }

    // ─── Helpers ───

    public static function generateToken(): string
    {
        return 'DL-' . strtoupper(Str::random(32));
    }

    public function isExpired(): bool
    {
        return $this->created_at->diffInMinutes(now()) > 30;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'بانتظار التأكيد',
            'confirmed' => 'مؤكد ✅',
            'rejected' => 'مرفوض ❌',
            default => $this->status,
        };
    }
}
