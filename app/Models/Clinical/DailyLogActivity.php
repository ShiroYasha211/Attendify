<?php

namespace App\Models\Clinical;

use Illuminate\Database\Eloquent\Model;

class DailyLogActivity extends Model
{
    protected $fillable = ['daily_log_id', 'activity_type', 'body_system_id', 'case_name', 'notes'];

    public function dailyLog()
    {
        return $this->belongsTo(StudentDailyLog::class, 'daily_log_id');
    }

    public function bodySystem()
    {
        return $this->belongsTo(BodySystem::class, 'body_system_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->activity_type) {
            'history_taking' => 'قصة مرضية',
            'clinical_examination' => 'فحص سريري',
            'round' => 'مرور (Round)',
            default => $this->activity_type,
        };
    }
}
