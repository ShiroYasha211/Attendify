<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Excuse extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'student_id',
        'reason',
        'attachment',
        'status',
        'doctor_comment',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
