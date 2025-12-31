<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Major extends Model
{
    protected $fillable = ['college_id', 'name'];

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
}
