<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LibraryNode extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'library_space_id',
        'parent_id',
        'title',
        'slug',
        'node_type',
        'icon',
        'sort_order',
        'is_system',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function space(): BelongsTo
    {
        return $this->belongsTo(LibrarySpace::class, 'library_space_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title');
    }

    public function resources(): BelongsToMany
    {
        return $this->belongsToMany(
            CourseResource::class,
            'library_node_resources',
            'library_node_id',
            'course_resource_id'
        )->withPivot(['sort_order', 'attached_by'])->withTimestamps();
    }

    public function placements(): HasMany
    {
        return $this->hasMany(LibraryNodeResource::class);
    }
}
