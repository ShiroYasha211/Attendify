<?php

namespace App\Models\Clinical;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class CaseAssignment extends Model
{
    protected $fillable = [
        'student_id',
        'clinical_case_id',
        'assigned_by',
        'task_type',
        'instructions',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function clinicalCase()
    {
        return $this->belongsTo(ClinicalCase::class, 'clinical_case_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
