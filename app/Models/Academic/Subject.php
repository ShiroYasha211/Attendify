<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'major_id',
        'level_id',
        'term_id',
        'doctor_id',
    ];

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function resources()
    {
        return $this->hasMany(\App\Models\CourseResource::class);
    }

    public function grades()
    {
        return $this->hasMany(\App\Models\Grade::class);
    }
}
