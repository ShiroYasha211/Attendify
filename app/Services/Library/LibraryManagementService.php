<?php

namespace App\Services\Library;

use App\Models\Academic\Subject;
use App\Models\CourseResource;
use App\Models\LibraryActivityLog;
use App\Models\LibraryContributor;
use App\Models\LibraryNode;
use App\Models\LibraryNodeResource;
use App\Models\LibrarySpace;
use App\Models\User;
use App\Support\Library\LibraryContract;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LibraryManagementService
{
    public function ensureSpace(User $actor, Subject $subject, string $type): LibrarySpace
    {
        $this->assertAdmin($actor);
        $this->assertType($type);

        return DB::transaction(function () use ($actor, $subject, $type) {
            $space = LibrarySpace::firstOrCreate(
                ['subject_id' => $subject->id, 'type' => $type],
                [
                    'name' => $type === LibraryContract::TYPE_SHARED ? 'المكتبة المشتركة' : 'مصادر المقرر',
                    'is_active' => true,
                    'created_by' => $actor->id,
                ]
            );

            foreach (LibraryContract::DEFAULT_SECTIONS as $slug => $section) {
                LibraryNode::firstOrCreate(
                    [
                        'library_space_id' => $space->id,
                        'parent_id' => null,
                        'slug' => $slug,
                    ],
                    [
                        'title' => $section['title'],
                        'node_type' => 'section',
                        'icon' => $section['icon'],
                        'sort_order' => array_search($slug, array_keys(LibraryContract::DEFAULT_SECTIONS), true),
                        'is_system' => true,
                        'is_active' => true,
                        'created_by' => $actor->id,
                        'updated_by' => $actor->id,
                    ]
                );
            }

            if ($space->wasRecentlyCreated) {
                $this->log($actor, 'space_created', $space);
            }

            return $space->fresh(['subject', 'nodes']);
        });
    }

    public function createNode(User $actor, LibrarySpace $space, array $data): LibraryNode
    {
        $parent = isset($data['parent_id'])
            ? LibraryNode::where('library_space_id', $space->id)->findOrFail($data['parent_id'])
            : null;

        $this->assertCapability($actor, $space, 'can_create_sections', $parent);

        return DB::transaction(function () use ($actor, $space, $parent, $data) {
            $node = LibraryNode::create([
                'library_space_id' => $space->id,
                'parent_id' => $parent?->id,
                'title' => trim($data['title']),
                'slug' => $this->uniqueSlug($space, $parent, $data['title']),
                'node_type' => $data['node_type'] ?? 'folder',
                'icon' => $data['icon'] ?? 'folder',
                'sort_order' => $this->nextSortOrder($space, $parent),
                'is_system' => false,
                'is_active' => true,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $this->log($actor, 'node_created', $space, $node, metadata: [
                'title' => $node->title,
                'parent_id' => $node->parent_id,
            ]);

            return $node;
        });
    }

    public function updateNode(User $actor, LibraryNode $node, array $data): LibraryNode
    {
        $node->loadMissing('space');
        $this->assertCapability($actor, $node->space, 'can_update_sections', $node);

        $newParent = array_key_exists('parent_id', $data)
            ? ($data['parent_id'] ? LibraryNode::findOrFail($data['parent_id']) : null)
            : $node->parent;

        if ($newParent && $newParent->library_space_id !== $node->library_space_id) {
            throw ValidationException::withMessages(['parent_id' => 'القسم الأب يجب أن يكون داخل مساحة المكتبة نفسها.']);
        }
        if ($newParent && $this->isDescendantOrSelf($newParent, $node)) {
            throw ValidationException::withMessages(['parent_id' => 'لا يمكن نقل القسم إلى داخل نفسه أو أحد فروعه.']);
        }
        if (array_key_exists('parent_id', $data) && $newParent?->id !== $node->parent_id) {
            $this->assertCapability($actor, $node->space, 'can_update_sections', $newParent);
        }

        return DB::transaction(function () use ($actor, $node, $newParent, $data) {
            $before = $node->only(['title', 'parent_id', 'icon', 'is_active', 'sort_order']);
            $node->fill([
                'title' => isset($data['title']) ? trim($data['title']) : $node->title,
                'parent_id' => $newParent?->id,
                'icon' => $data['icon'] ?? $node->icon,
                'is_active' => $data['is_active'] ?? $node->is_active,
                'sort_order' => $data['sort_order'] ?? $node->sort_order,
                'updated_by' => $actor->id,
            ])->save();

            $this->log($actor, 'node_updated', $node->space, $node, metadata: [
                'before' => $before,
                'after' => $node->only(['title', 'parent_id', 'icon', 'is_active', 'sort_order']),
            ]);

            return $node->fresh();
        });
    }

    public function deleteNode(User $actor, LibraryNode $node): void
    {
        $node->loadMissing('space');
        $this->assertCapability($actor, $node->space, 'can_delete_sections', $node);

        if ($node->is_system) {
            throw ValidationException::withMessages([
                'node' => 'القسم الأساسي لا يحذف، ويمكن تعطيله بدلاً من ذلك.',
            ]);
        }

        DB::transaction(function () use ($actor, $node) {
            $this->softDeleteTree($node, $actor);
            $this->log($actor, 'node_deleted', $node->space, $node, metadata: [
                'title' => $node->title,
            ]);
        });
    }

    public function reorderNodes(User $actor, LibrarySpace $space, array $items): void
    {
        $nodes = LibraryNode::where('library_space_id', $space->id)
            ->whereIn('id', collect($items)->pluck('id'))
            ->get()
            ->keyBy('id');

        if ($nodes->count() !== count($items)) {
            throw ValidationException::withMessages(['items' => 'تحتوي القائمة على أقسام غير صالحة.']);
        }

        $parentIds = $nodes->pluck('parent_id')->unique();
        if ($parentIds->count() !== 1) {
            throw ValidationException::withMessages(['items' => 'يمكن ترتيب الأقسام التي تنتمي إلى المستوى نفسه فقط.']);
        }

        foreach ($nodes as $node) {
            $this->assertCapability($actor, $space, 'can_reorder_sections', $node);
        }

        DB::transaction(function () use ($actor, $space, $items, $nodes) {
            foreach ($items as $item) {
                $nodes[(int) $item['id']]->update([
                    'sort_order' => (int) $item['sort_order'],
                    'updated_by' => $actor->id,
                ]);
            }

            $this->log($actor, 'nodes_reordered', $space, metadata: ['items' => $items]);
        });
    }

    public function uploadResource(
        User $actor,
        LibraryNode $node,
        UploadedFile $file,
        array $data
    ): CourseResource {
        $node->loadMissing('space.subject');
        $this->assertCapability($actor, $node->space, 'can_upload', $node);

        $path = $file->store('course_resources', 'public');

        try {
            return DB::transaction(function () use ($actor, $node, $file, $path, $data) {
                $category = $node->is_system && isset(LibraryContract::DEFAULT_SECTIONS[$node->slug])
                    ? $node->slug
                    : 'other';

                $resource = CourseResource::create([
                    'subject_id' => $node->space->subject_id,
                    'created_by' => $actor->id,
                    'title' => trim($data['title']),
                    'file_path' => $path,
                    'file_type' => strtolower($file->getClientOriginalExtension()),
                    'category' => $category,
                    'description' => $data['description'] ?? null,
                    'semester_info' => $data['semester_info'] ?? $node->space->subject->term?->name,
                    'visibility' => $data['visibility'] ?? 'batch',
                ]);

                LibraryNodeResource::create([
                    'library_node_id' => $node->id,
                    'course_resource_id' => $resource->id,
                    'sort_order' => $node->placements()->max('sort_order') + 1,
                    'attached_by' => $actor->id,
                ]);

                $this->log($actor, 'resource_uploaded', $node->space, $node, $resource);

                return $resource->load(['subject', 'uploader']);
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }
    }

    public function attachResource(User $actor, LibraryNode $node, CourseResource $resource): LibraryNodeResource
    {
        $node->loadMissing('space');
        $this->assertCapability($actor, $node->space, 'can_manage_resources', $node);
        $this->assertResourceMatchesSpace($resource, $node->space);

        return DB::transaction(function () use ($actor, $node, $resource) {
            $placement = LibraryNodeResource::firstOrCreate(
                ['library_node_id' => $node->id, 'course_resource_id' => $resource->id],
                [
                    'sort_order' => $node->placements()->max('sort_order') + 1,
                    'attached_by' => $actor->id,
                ]
            );

            $this->log($actor, 'resource_attached', $node->space, $node, $resource);

            return $placement;
        });
    }

    public function moveResource(
        User $actor,
        LibraryNode $source,
        LibraryNode $target,
        CourseResource $resource
    ): LibraryNodeResource {
        $source->loadMissing('space');
        $target->loadMissing('space');
        if ($source->library_space_id !== $target->library_space_id) {
            throw ValidationException::withMessages(['target_node_id' => 'يجب أن يكون القسمان داخل مساحة المكتبة نفسها.']);
        }

        $this->assertCapability($actor, $source->space, 'can_manage_resources', $source);
        $this->assertCapability($actor, $target->space, 'can_manage_resources', $target);
        $this->assertResourceMatchesSpace($resource, $target->space);

        return DB::transaction(function () use ($actor, $source, $target, $resource) {
            LibraryNodeResource::where('library_node_id', $source->id)
                ->where('course_resource_id', $resource->id)
                ->delete();

            $placement = LibraryNodeResource::updateOrCreate(
                ['library_node_id' => $target->id, 'course_resource_id' => $resource->id],
                [
                    'sort_order' => $target->placements()->max('sort_order') + 1,
                    'attached_by' => $actor->id,
                ]
            );

            $this->log($actor, 'resource_moved', $target->space, $target, $resource, [
                'source_node_id' => $source->id,
            ]);

            return $placement;
        });
    }

    public function grantContributor(
        User $actor,
        LibrarySpace $space,
        User $user,
        array $capabilities,
        array $nodeIds = [],
        ?string $expiresAt = null
    ): LibraryContributor {
        $this->assertAdmin($actor);
        $space->loadMissing('subject.major');
        $user->loadMissing('major');

        if ($user->college_id !== $space->subject->major->college_id) {
            throw ValidationException::withMessages([
                'user_id' => 'يجب أن ينتمي المستخدم إلى كلية المادة نفسها.',
            ]);
        }

        $validNodeIds = LibraryNode::where('library_space_id', $space->id)
            ->whereIn('id', $nodeIds)
            ->pluck('id');
        if ($validNodeIds->count() !== count(array_unique($nodeIds))) {
            throw ValidationException::withMessages(['node_ids' => 'تحتوي النطاقات على أقسام غير صالحة.']);
        }

        return DB::transaction(function () use (
            $actor,
            $space,
            $user,
            $capabilities,
            $validNodeIds,
            $expiresAt
        ) {
            $values = array_fill_keys(LibraryContract::CAPABILITIES, false);
            foreach ($capabilities as $capability) {
                $values[$capability] = true;
            }

            $contributor = LibraryContributor::updateOrCreate(
                ['library_space_id' => $space->id, 'user_id' => $user->id],
                [
                    ...$values,
                    'is_active' => true,
                    'expires_at' => $expiresAt,
                    'granted_by' => $actor->id,
                ]
            );
            $contributor->scopedNodes()->sync($validNodeIds);

            $this->log($actor, 'contributor_granted', $space, metadata: [
                'user_id' => $user->id,
                'capabilities' => $capabilities,
                'node_ids' => $validNodeIds->values()->all(),
                'expires_at' => $expiresAt,
            ]);

            return $contributor->load(['user', 'scopedNodes']);
        });
    }

    public function revokeContributor(User $actor, LibraryContributor $contributor): void
    {
        $this->assertAdmin($actor);
        $contributor->update(['is_active' => false]);
        $this->log($actor, 'contributor_revoked', $contributor->space, metadata: [
            'user_id' => $contributor->user_id,
        ]);
    }

    public function assertAdmin(User $user): void
    {
        abort_unless($user->hasRole('admin') || $user->isSystemOwner(), 403, 'هذه العملية متاحة للأدمن الرئيسي فقط.');
    }

    private function assertCapability(
        User $actor,
        LibrarySpace $space,
        string $capability,
        ?LibraryNode $node = null
    ): void {
        if ($actor->hasRole('admin') || $actor->isSystemOwner()) {
            return;
        }

        if ($capability === 'can_upload' && $this->hasLegacyUploadAccess($actor, $space)) {
            return;
        }

        $contributor = LibraryContributor::active()
            ->where('library_space_id', $space->id)
            ->where('user_id', $actor->id)
            ->where($capability, true)
            ->with('scopedNodes:id')
            ->first();

        abort_unless($contributor, 403, 'ليست لديك الصلاحية المطلوبة لإدارة هذا القسم.');

        if ($contributor->scopedNodes->isEmpty()) {
            return;
        }

        abort_unless(
            $node && $this->nodeFallsWithinScopes($node, $contributor->scopedNodes->pluck('id')->all()),
            403,
            'هذه العملية خارج نطاق الأقسام الممنوحة لك.'
        );
    }

    private function hasLegacyUploadAccess(User $actor, LibrarySpace $space): bool
    {
        $space->loadMissing('subject.major');
        $subject = $space->subject;

        if ($actor->hasRole('doctor')) {
            return $subject->doctor_id === $actor->id;
        }

        if ($space->type === LibraryContract::TYPE_COURSE && $actor->hasRole('delegate')) {
            return $subject->major_id === $actor->major_id && $subject->level_id === $actor->level_id;
        }

        return $space->type === LibraryContract::TYPE_SHARED
            && $subject->major_id === $actor->major_id
            && $actor->hasPermission('upload_shared_library');
    }

    private function nodeFallsWithinScopes(LibraryNode $node, array $scopeIds): bool
    {
        $current = $node;
        while ($current) {
            if (in_array($current->id, $scopeIds, true)) {
                return true;
            }
            $current = $current->parent;
        }

        return false;
    }

    private function isDescendantOrSelf(LibraryNode $candidate, LibraryNode $node): bool
    {
        $current = $candidate;
        while ($current) {
            if ($current->id === $node->id) {
                return true;
            }
            $current = $current->parent;
        }

        return false;
    }

    private function softDeleteTree(LibraryNode $node, User $actor): void
    {
        LibraryNode::where('parent_id', $node->id)->each(function (LibraryNode $child) use ($actor) {
            $this->softDeleteTree($child, $actor);
        });
        $node->update(['updated_by' => $actor->id]);
        $node->delete();
    }

    private function assertResourceMatchesSpace(CourseResource $resource, LibrarySpace $space): void
    {
        if ($resource->subject_id !== $space->subject_id) {
            throw ValidationException::withMessages([
                'resource_id' => 'لا يمكن ربط ملف تابع لمادة مختلفة بهذا القسم.',
            ]);
        }
    }

    private function uniqueSlug(LibrarySpace $space, ?LibraryNode $parent, string $title): string
    {
        $base = Str::slug($title) ?: 'section';
        $slug = $base;
        $counter = 2;

        while (LibraryNode::withTrashed()
            ->where('library_space_id', $space->id)
            ->where('parent_id', $parent?->id)
            ->where('slug', $slug)
            ->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function nextSortOrder(LibrarySpace $space, ?LibraryNode $parent): int
    {
        return (int) LibraryNode::where('library_space_id', $space->id)
            ->where('parent_id', $parent?->id)
            ->max('sort_order') + 1;
    }

    private function assertType(string $type): void
    {
        if (! in_array($type, LibraryContract::TYPES, true)) {
            throw ValidationException::withMessages(['type' => 'نوع مساحة المكتبة غير صالح.']);
        }
    }

    private function log(
        User $actor,
        string $action,
        LibrarySpace $space,
        ?LibraryNode $node = null,
        ?CourseResource $resource = null,
        array $metadata = []
    ): void {
        LibraryActivityLog::create([
            'library_space_id' => $space->id,
            'library_node_id' => $node?->id,
            'course_resource_id' => $resource?->id,
            'actor_id' => $actor->id,
            'action' => $action,
            'metadata' => $metadata ?: null,
        ]);
    }
}
