<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'library_space_id',
        'library_node_id',
        'course_resource_id',
        'actor_id',
        'action',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function space(): BelongsTo
    {
        return $this->belongsTo(LibrarySpace::class, 'library_space_id');
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(LibraryNode::class, 'library_node_id');
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(CourseResource::class, 'course_resource_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id')->withTrashed();
    }
}
