<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LibraryContributor extends Model
{
    protected $fillable = [
        'library_space_id',
        'user_id',
        'can_upload',
        'can_create_sections',
        'can_update_sections',
        'can_delete_sections',
        'can_reorder_sections',
        'can_manage_resources',
        'is_active',
        'expires_at',
        'granted_by',
    ];

    protected $casts = [
        'can_upload' => 'boolean',
        'can_create_sections' => 'boolean',
        'can_update_sections' => 'boolean',
        'can_delete_sections' => 'boolean',
        'can_reorder_sections' => 'boolean',
        'can_manage_resources' => 'boolean',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function (Builder $builder) {
                $builder->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(LibrarySpace::class, 'library_space_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by')->withTrashed();
    }

    public function scopedNodes(): BelongsToMany
    {
        return $this->belongsToMany(
            LibraryNode::class,
            'library_contributor_scopes',
            'library_contributor_id',
            'library_node_id'
        )->withTimestamps();
    }
}
