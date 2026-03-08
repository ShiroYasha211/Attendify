<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'major_id',
        'level_id',
        'term_id',
        'semester_id',
        'doctor_id',
        'max_absences',
        'lecture_count',
        'allow_delegate_attendance',
    ];

    protected $casts = [
        'allow_delegate_attendance' => 'boolean',
    ];

    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(\App\Models\CourseResource::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(\App\Models\Grade::class);
    }
}
