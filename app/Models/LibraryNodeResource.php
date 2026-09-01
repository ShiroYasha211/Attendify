<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryNodeResource extends Model
{
    protected $fillable = [
        'library_node_id',
        'course_resource_id',
        'sort_order',
        'attached_by',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function node(): BelongsTo
    {
        return $this->belongsTo(LibraryNode::class, 'library_node_id');
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(CourseResource::class, 'course_resource_id');
    }

    public function attachedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attached_by')->withTrashed();
    }
}
