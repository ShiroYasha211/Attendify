<?php

namespace App\Models\Clinical;

use Illuminate\Database\Eloquent\Model;

class ClinicalDepartment extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function cases()
    {
        return $this->hasMany(ClinicalCase::class);
    }
}
