<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Term extends Model
{
    protected $fillable = ['level_id', 'name', 'start_date', 'end_date'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Term belongs to a Level.
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
}
