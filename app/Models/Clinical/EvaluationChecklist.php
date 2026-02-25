<?php

namespace App\Models\Clinical;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class EvaluationChecklist extends Model
{
    protected $fillable = [
        'title',
        'description',
        'doctor_id',
        'skill_type',
        'time_limit_minutes',
        'total_marks',
        'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function items()
    {
        return $this->hasMany(ChecklistItem::class, 'checklist_id')->orderBy('sort_order');
    }

    public function evaluations()
    {
        return $this->hasMany(StudentEvaluation::class, 'checklist_id');
    }

    public function getSkillLabelAttribute(): string
    {
        return match ($this->skill_type) {
            'history_taking' => 'أخذ قصة مرضية',
            'clinical_examination' => 'فحص سريري',
            'procedure' => 'إجراء طبي',
            'communication' => 'مهارات تواصل',
            default => $this->skill_type,
        };
    }
}
