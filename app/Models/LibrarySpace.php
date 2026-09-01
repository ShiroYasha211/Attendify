<?php

namespace App\Models;

use App\Models\Academic\Subject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LibrarySpace extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'subject_id',
        'type',
        'name',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(LibraryNode::class);
    }

    public function contributors(): HasMany
    {
        return $this->hasMany(LibraryContributor::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(LibraryActivityLog::class);
    }
}
