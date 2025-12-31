<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Academic\Subject;

class Attendance extends Model
{
    protected $fillable = [
        'student_id',
        'subject_id',
        'status',
        'date',
        'recorded_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
