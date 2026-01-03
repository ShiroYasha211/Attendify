<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\UserRole;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'student_number',
        'university_id',
        'college_id',
        'major_id',
        'level_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int,string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        // تحويل النص إلى enum تلقائيًا عند الوصول للخاصية
        'role' => UserRole::class,
    ];

    /**
     * Check if the user has a given role.
     */
    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }
    public function university()
    {
        return $this->belongsTo(\App\Models\Academic\University::class);
    }

    public function college()
    {
        return $this->belongsTo(\App\Models\Academic\College::class);
    }

    public function major()
    {
        return $this->belongsTo(\App\Models\Academic\Major::class);
    }

    public function level()
    {
        return $this->belongsTo(\App\Models\Academic\Level::class);
    }

    /**
     * المواد التي يدرسها الدكتور.
     */
    public function subjects()
    {
        return $this->hasMany(\App\Models\Academic\Subject::class, 'doctor_id');
    }

    /**
     * Get the attendances for the student.
     */
    public function attendances()
    {
        return $this->hasMany(\App\Models\Attendance::class, 'student_id');
    }
}
