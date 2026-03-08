<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Major extends Model
{
    protected $fillable = ['college_id', 'name', 'has_clinical', 'has_semesters'];

    protected $casts = [
        'has_clinical' => 'boolean',
    ];

    /**
     * Major belongs to a College.
     */
    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    /**
     * Major has many Levels.
     */
    public function levels(): HasMany
    {
        return $this->hasMany(Level::class);
    }

    /**
     * Major has one clinical delegate.
     */
    public function clinicalDelegate(): HasOne
    {
        return $this->hasOne(\App\Models\ClinicalDelegate::class);
    }
}
