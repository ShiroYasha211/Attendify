<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class College extends Model
{
    protected $fillable = ['university_id', 'name'];

    /**
     * College belongs to a University.
     */
    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    /**
     * College has many Majors.
     */
    public function majors(): HasMany
    {
        return $this->hasMany(Major::class);
    }
}
