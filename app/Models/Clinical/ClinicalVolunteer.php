<?php

namespace App\Models\Clinical;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class ClinicalVolunteer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'doctor_id',
        'name',
        'contact_info',
        'phone_secondary',
        'email',
        'diagnosis',
        'clinical_signs',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    /**
     * Get the doctor who added the volunteer.
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
