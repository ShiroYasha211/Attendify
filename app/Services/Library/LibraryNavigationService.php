<?php

namespace App\Services\Library;

use App\Models\Academic\Level;
use App\Models\Academic\Major;
use App\Models\Academic\Semester;
use App\Models\Academic\Subject;
use App\Models\Academic\Term;
use App\Models\CourseResource;
use App\Models\LibraryContributor;
use App\Models\LibraryNode;
use App\Models\LibrarySpace;
use App\Models\User;
use App\Support\Library\LibraryContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LibraryNavigationService
{
    public function navigate(
        User $user,
        string $type,
        ?string $nodeKey = null,
        ?string $search = null,
        int $perPage = 20
    ): array {
        $nodeKey = $nodeKey ?: 'root';
        $search = trim((string) $search);

        if ($search !== '') {
            return $this->searchResponse($user, $type, $nodeKey, $search, $perPage);
        }

        $response = match ($this->nodeType($nodeKey)) {
            'root' => $this->rootResponse($user, $type),
            'major' => $this->majorResponse($user, $type, $this->nodeId($nodeKey)),
            'level' => $this->levelResponse($user, $type, $this->nodeId($nodeKey)),
            'term' => $this->termResponse($user, $type, $this->nodeId($nodeKey)),
            'semester' => $this->semesterResponse($user, $type, $this->nodeId($nodeKey)),
            'subject' => $this->subjectResponse($user, $type, $this->nodeId($nodeKey)),
            'section' => $this->sectionResponse($user, $type, $nodeKey, $perPage),
            'custom' => $this->customNodeResponse($user, $type, $this->nodeId($nodeKey), $perPage),
            default => throw new NotFoundHttpException('عنصر المكتبة المطلوب غير موجود.'),
        };

        return $this->baseResponse($type, $nodeKey, $response);
    }

    private function rootResponse(User $user, string $type): array
    {
        if ($user->hasRole('doctor')) {
            $majors = Major::query()
                ->where('college_id', $user->college_id)
                ->whereHas('levels.terms.subjects', function (Builder $query) use ($user, $type) {
                    $this->limitSubjectsForUser($query, $user, $type);
                })
                ->orderBy('name')
                ->get(['id', 'name']);

            return [
                'breadcrumbs' => [],
                'children' => $majors->map(fn (Major $major) => $this->child(
                    "major:{$major->id}",
                    'major',
                    $major->name,
                    'school'
                ))->values(),
                'resources' => null,
                'capabilities' => $this->emptyCapabilities(),
            ];
        }

        $major = Major::find($user->major_id);
        if (! $major) {
            return $this->emptyNodeResponse();
        }

        $levels = Level::query()
            ->where('major_id', $major->id)
            ->when($type === LibraryContract::TYPE_COURSE, fn (Builder $query) => $query->whereKey($user->level_id))
            ->whereHas('terms.subjects', function (Builder $query) use ($user, $type) {
                $this->limitSubjectsForUser($query, $user, $type);
            })
            ->orderBy('id')
            ->get(['id', 'name']);

        return [
            'breadcrumbs' => [],
            'children' => $levels->map(fn (Level $level) => $this->child(
                "level:{$level->id}",
                'level',
                $level->name,
                'layers'
            ))->values(),
            'resources' => null,
            'capabilities' => $this->emptyCapabilities(),
        ];
    }

    private function majorResponse(User $user, string $type, int $majorId): array
    {
        $major = Major::findOrFail($majorId);
        $this->authorizeMajor($user, $major);

        $levels = Level::query()
            ->where('major_id', $major->id)
            ->when(
                ! $user->hasRole('doctor') && $type === LibraryContract::TYPE_COURSE,
                fn (Builder $query) => $query->whereKey($user->level_id)
            )
            ->whereHas('terms.subjects', function (Builder $query) use ($user, $type) {
                $this->limitSubjectsForUser($query, $user, $type);
            })
            ->orderBy('id')
            ->get(['id', 'name']);

        return [
            'breadcrumbs' => [$this->crumb("major:{$major->id}", 'major', $major->name)],
            'children' => $levels->map(fn (Level $level) => $this->child(
                "level:{$level->id}",
                'level',
                $level->name,
                'layers'
            ))->values(),
            'resources' => null,
            'capabilities' => $this->emptyCapabilities(),
        ];
    }

    private function levelResponse(User $user, string $type, int $levelId): array
    {
        $level = Level::with('major')->findOrFail($levelId);
        $this->authorizeLevel($user, $type, $level);

        $terms = Term::query()
            ->where('level_id', $level->id)
            ->whereHas('subjects', function (Builder $query) use ($user, $type) {
                $this->limitSubjectsForUser($query, $user, $type);
            })
            ->orderBy('start_date')
            ->orderBy('id')
            ->get(['id', 'level_id', 'name']);

        return [
            'breadcrumbs' => $this->levelBreadcrumbs($user, $level),
            'children' => $terms->map(fn (Term $term) => $this->child(
                "term:{$term->id}",
                'term',
                $term->name,
                'calendar_month'
            ))->values(),
            'resources' => null,
            'capabilities' => $this->emptyCapabilities(),
        ];
    }

    private function termResponse(User $user, string $type, int $termId): array
    {
        $term = Term::with('level.major')->findOrFail($termId);
        $this->authorizeLevel($user, $type, $term->level);

        $subjectsQuery = Subject::query()->where('term_id', $term->id);
        $this->limitSubjectsForUser($subjectsQuery, $user, $type);

        $children = collect();
        if ($term->level->major->has_semesters) {
            $semesters = Semester::query()
                ->where('term_id', $term->id)
                ->whereHas('subjects', function (Builder $query) use ($user, $type) {
                    $this->limitSubjectsForUser($query, $user, $type);
                })
                ->orderBy('id')
                ->get(['id', 'term_id', 'name']);

            $children = $semesters->map(fn (Semester $semester) => $this->child(
                "semester:{$semester->id}",
                'semester',
                $semester->name,
                'date_range'
            ));

            $subjectsQuery->whereNull('semester_id');
        }

        $subjects = $subjectsQuery->orderBy('name')->get(['id', 'name', 'code']);
        $children = $children->concat($subjects->map(fn (Subject $subject) => $this->subjectChild($subject)))->values();

        return [
            'breadcrumbs' => [
                ...$this->levelBreadcrumbs($user, $term->level),
                $this->crumb("term:{$term->id}", 'term', $term->name),
            ],
            'children' => $children,
            'resources' => null,
            'capabilities' => $this->emptyCapabilities(),
        ];
    }

    private function semesterResponse(User $user, string $type, int $semesterId): array
    {
        $semester = Semester::with('term.level.major')->findOrFail($semesterId);
        $this->authorizeLevel($user, $type, $semester->term->level);

        $subjectsQuery = Subject::query()->where('semester_id', $semester->id);
        $this->limitSubjectsForUser($subjectsQuery, $user, $type);
        $subjects = $subjectsQuery->orderBy('name')->get(['id', 'name', 'code']);

        return [
            'breadcrumbs' => [
                ...$this->levelBreadcrumbs($user, $semester->term->level),
                $this->crumb("term:{$semester->term->id}", 'term', $semester->term->name),
                $this->crumb("semester:{$semester->id}", 'semester', $semester->name),
            ],
            'children' => $subjects->map(fn (Subject $subject) => $this->subjectChild($subject))->values(),
            'resources' => null,
            'capabilities' => $this->emptyCapabilities(),
        ];
    }

    private function subjectResponse(User $user, string $type, int $subjectId): array
    {
        $subject = $this->authorizedSubject($user, $type, $subjectId);
        $space = LibrarySpace::query()
            ->where('subject_id', $subject->id)
            ->where('type', $type)
            ->where('is_active', true)
            ->first();

        $sections = collect();
        if ($space) {
            $nodes = $space->nodes()
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->withCount('children')
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get();

            $sections = $nodes->map(fn (LibraryNode $node) => $this->child(
                "custom:{$node->id}",
                $node->node_type,
                $node->title,
                $node->icon ?: 'folder',
                true,
                $node->is_system,
                $node->sort_order
            ));
        }

        if ($sections->isEmpty()) {
            $sections = collect(LibraryContract::DEFAULT_SECTIONS)
                ->map(fn (array $section, string $slug) => $this->child(
                    "section:{$subject->id}:{$slug}",
                    'section',
                    $section['title'],
                    $section['icon'],
                    true
                ));
        }

        return [
            'context' => [
                'subject_id' => $subject->id,
                'library_space_id' => $space?->id,
                'current_node_id' => null,
                'current_node_is_system' => false,
            ],
            'breadcrumbs' => $this->subjectBreadcrumbs($user, $subject),
            'children' => $sections->values(),
            'resources' => null,
            'capabilities' => $this->capabilities($user, $type, $subject, $space),
        ];
    }

    private function sectionResponse(User $user, string $type, string $nodeKey, int $perPage): array
    {
        [, $subjectId, $slug] = array_pad(explode(':', $nodeKey, 3), 3, null);
        if (! $subjectId || ! isset(LibraryContract::DEFAULT_SECTIONS[$slug])) {
            throw new NotFoundHttpException('قسم المكتبة المطلوب غير موجود.');
        }

        $subject = $this->authorizedSubject($user, $type, (int) $subjectId);
        $aliases = LibraryContract::DEFAULT_SECTIONS[$slug]['aliases'];
        $query = $this->resourceQuery($user, $type, $subject)->whereIn('category', $aliases);
        $resources = $query->latest()->paginate($perPage);

        return [
            'context' => [
                'subject_id' => $subject->id,
                'library_space_id' => null,
                'current_node_id' => null,
                'current_node_is_system' => true,
            ],
            'breadcrumbs' => [
                ...$this->subjectBreadcrumbs($user, $subject),
                $this->crumb($nodeKey, 'section', LibraryContract::DEFAULT_SECTIONS[$slug]['title']),
            ],
            'children' => [],
            'resources' => $this->resourcePage($resources, $type),
            'capabilities' => $this->capabilities($user, $type, $subject),
        ];
    }

    private function customNodeResponse(User $user, string $type, int $nodeId, int $perPage): array
    {
        $node = LibraryNode::with([
            'space.subject.term.level.major',
            'children' => fn ($query) => $query->withCount('children'),
        ])->findOrFail($nodeId);
        if (! $node->is_active || ! $node->space->is_active || $node->space->type !== $type) {
            throw new NotFoundHttpException('قسم المكتبة المطلوب غير متاح.');
        }

        $subject = $this->authorizedSubject($user, $type, $node->space->subject_id);
        $resourcesQuery = $this->resourceQuery($user, $type, $subject);
        if ($node->is_system && isset(LibraryContract::DEFAULT_SECTIONS[$node->slug])) {
            $aliases = LibraryContract::DEFAULT_SECTIONS[$node->slug]['aliases'];
            $resourcesQuery->where(function (Builder $query) use ($node, $aliases) {
                $query->whereIn('category', $aliases)
                    ->orWhereHas('libraryNodes', fn (Builder $nodeQuery) => $nodeQuery->whereKey($node->id));
            });
        } else {
            $resourcesQuery->whereHas('libraryNodes', fn (Builder $query) => $query->whereKey($node->id));
        }
        $resources = $resourcesQuery->latest()->paginate($perPage);

        return [
            'context' => [
                'subject_id' => $subject->id,
                'library_space_id' => $node->space->id,
                'current_node_id' => $node->id,
                'current_node_title' => $node->title,
                'current_node_is_system' => $node->is_system,
                'current_node_parent_id' => $node->parent_id,
            ],
            'breadcrumbs' => [
                ...$this->subjectBreadcrumbs($user, $subject),
                ...$this->customNodeBreadcrumbs($node),
            ],
            'children' => $node->children->map(fn (LibraryNode $child) => $this->child(
                "custom:{$child->id}",
                $child->node_type,
                $child->title,
                $child->icon ?: 'folder',
                $child->children_count > 0,
                false,
                $child->sort_order
            ))->values(),
            'resources' => $this->resourcePage($resources, $type),
            'capabilities' => $this->capabilities($user, $type, $subject, $node->space, $node),
        ];
    }

    private function searchResponse(User $user, string $type, string $nodeKey, string $search, int $perPage): array
    {
        $query = CourseResource::query()
            ->with(['subject:id,name,code,major_id,level_id,term_id,semester_id', 'uploader:id,name,role'])
            ->where(function (Builder $builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('subject', fn (Builder $subjectQuery) => $subjectQuery->where('name', 'like', "%{$search}%"));
            });

        $this->limitResourceQueryForUser($query, $user, $type);
        $this->limitResourceQueryToNode($query, $user, $type, $nodeKey);
        $resources = $query->latest()->paginate($perPage);

        return $this->baseResponse($type, $nodeKey, [
            'breadcrumbs' => $this->breadcrumbsForKey($user, $type, $nodeKey),
            'children' => [],
            'resources' => $this->resourcePage($resources, $type),
            'capabilities' => $this->emptyCapabilities(),
            'search' => $search,
        ]);
    }

    private function authorizedSubject(User $user, string $type, int $subjectId): Subject
    {
        $query = Subject::with(['major', 'level', 'term', 'semester'])->whereKey($subjectId);
        $this->limitSubjectsForUser($query, $user, $type);

        return $query->firstOrFail();
    }

    private function limitSubjectsForUser(Builder $query, User $user, string $type): void
    {
        if ($user->hasRole('doctor')) {
            $query->whereHas('major', fn (Builder $majorQuery) => $majorQuery->where('college_id', $user->college_id));
            if ($type === LibraryContract::TYPE_COURSE) {
                $query->where('doctor_id', $user->id);
            }

            return;
        }

        $query->where('major_id', $user->major_id);
        if ($type === LibraryContract::TYPE_COURSE) {
            $query->where('level_id', $user->level_id);
        }
    }

    private function resourceQuery(User $user, string $type, Subject $subject): Builder
    {
        $query = CourseResource::query()
            ->with(['subject:id,name,code,major_id,level_id,term_id,semester_id', 'uploader:id,name,role'])
            ->where('subject_id', $subject->id);

        $this->limitResourceQueryForUser($query, $user, $type);

        return $query;
    }

    private function limitResourceQueryForUser(Builder $query, User $user, string $type): void
    {
        $query->whereHas('subject', function (Builder $subjectQuery) use ($user, $type) {
            $this->limitSubjectsForUser($subjectQuery, $user, $type);
        });

        if ($type === LibraryContract::TYPE_COURSE) {
            return;
        }

        if ($user->hasRole('doctor')) {
            $query->where(function (Builder $builder) use ($user) {
                $builder->where('visibility', 'everyone')
                    ->orWhere('created_by', $user->id)
                    ->orWhere(function (Builder $visibilityQuery) use ($user) {
                        $visibilityQuery->where('visibility', 'college')
                            ->whereHas('subject.major', fn (Builder $majorQuery) => $majorQuery->where('college_id', $user->college_id));
                    })
                    ->orWhere(function (Builder $visibilityQuery) use ($user) {
                        $visibilityQuery->where('visibility', 'batch')
                            ->whereHas('subject', fn (Builder $subjectQuery) => $subjectQuery->where('doctor_id', $user->id));
                    });
            });

            return;
        }

        $query->where(function (Builder $builder) use ($user) {
            $builder->where('visibility', 'everyone')
                ->orWhere('created_by', $user->id)
                ->orWhere(function (Builder $visibilityQuery) use ($user) {
                    $visibilityQuery->where('visibility', 'college')
                        ->whereHas('subject.major', fn (Builder $majorQuery) => $majorQuery->where('college_id', $user->college_id));
                })
                ->orWhere(function (Builder $visibilityQuery) use ($user) {
                    $visibilityQuery->where('visibility', 'batch')
                        ->whereHas('subject', fn (Builder $subjectQuery) => $subjectQuery
                            ->where('major_id', $user->major_id)
                            ->where('level_id', $user->level_id));
                });
        });
    }

    private function limitResourceQueryToNode(Builder $query, User $user, string $type, string $nodeKey): void
    {
        $nodeType = $this->nodeType($nodeKey);
        if ($nodeType === 'root') {
            return;
        }

        if ($nodeType === 'custom') {
            $node = LibraryNode::with('space')->findOrFail($this->nodeId($nodeKey));
            $this->authorizedSubject($user, $type, $node->space->subject_id);
            if ($node->is_system && isset(LibraryContract::DEFAULT_SECTIONS[$node->slug])) {
                $aliases = LibraryContract::DEFAULT_SECTIONS[$node->slug]['aliases'];
                $query->where(function (Builder $resourceQuery) use ($node, $aliases) {
                    $resourceQuery->whereIn('category', $aliases)
                        ->orWhereHas('libraryNodes', fn (Builder $nodeQuery) => $nodeQuery->whereKey($node->id));
                });
            } else {
                $query->whereHas('libraryNodes', fn (Builder $nodeQuery) => $nodeQuery->whereKey($node->id));
            }

            return;
        }

        if ($nodeType === 'section') {
            [, $subjectId, $slug] = array_pad(explode(':', $nodeKey, 3), 3, null);
            if (! $subjectId || ! isset(LibraryContract::DEFAULT_SECTIONS[$slug])) {
                throw new NotFoundHttpException('قسم المكتبة المطلوب غير موجود.');
            }
            $this->authorizedSubject($user, $type, (int) $subjectId);
            $query->where('subject_id', (int) $subjectId)
                ->whereIn('category', LibraryContract::DEFAULT_SECTIONS[$slug]['aliases']);

            return;
        }

        $id = $this->nodeId($nodeKey);
        $column = match ($nodeType) {
            'major' => 'major_id',
            'level' => 'level_id',
            'term' => 'term_id',
            'semester' => 'semester_id',
            'subject' => 'id',
            default => null,
        };

        if ($column) {
            $query->whereHas('subject', fn (Builder $subjectQuery) => $subjectQuery->where($column, $id));
        }
    }

    private function authorizeMajor(User $user, Major $major): void
    {
        $allowed = $user->hasRole('doctor')
            ? $major->college_id === $user->college_id
            : $major->id === $user->major_id;

        abort_unless($allowed, 403, 'ليس لديك صلاحية للوصول إلى هذا التخصص.');
    }

    private function authorizeLevel(User $user, string $type, Level $level): void
    {
        $this->authorizeMajor($user, $level->major);
        if (! $user->hasRole('doctor') && $type === LibraryContract::TYPE_COURSE) {
            abort_unless($level->id === $user->level_id, 403, 'ليس لديك صلاحية للوصول إلى هذا المستوى.');
        }
    }

    private function capabilities(
        User $user,
        string $type,
        Subject $subject,
        ?LibrarySpace $space = null,
        ?LibraryNode $node = null
    ): array {
        $capabilities = $this->emptyCapabilities();
        $capabilities['can_upload'] = $user->hasRole('doctor')
            ? $subject->doctor_id === $user->id
            : ($type === LibraryContract::TYPE_COURSE && $user->hasRole('delegate'))
                || ($type === LibraryContract::TYPE_SHARED && $user->hasPermission('upload_shared_library'));

        $space ??= LibrarySpace::query()
            ->where('subject_id', $subject->id)
            ->where('type', $type)
            ->where('is_active', true)
            ->first();

        if (! $space) {
            return $capabilities;
        }

        $contributor = LibraryContributor::query()
            ->active()
            ->where('library_space_id', $space->id)
            ->where('user_id', $user->id)
            ->with('scopedNodes:id')
            ->first();

        if (! $contributor) {
            return $capabilities;
        }

        $withinScope = $contributor->scopedNodes->isEmpty()
            || ($node && $this->nodeFallsWithinScopes($node, $contributor->scopedNodes->pluck('id')->all()));
        if ($withinScope) {
            foreach (LibraryContract::CAPABILITIES as $capability) {
                $capabilities[$capability] = $capabilities[$capability] || (bool) $contributor->{$capability};
            }
        }

        $capabilities['scope_limited'] = $contributor->scopedNodes->isNotEmpty();

        return $capabilities;
    }

    private function baseResponse(string $type, string $nodeKey, array $data): array
    {
        return [
            'context' => [
                'library_type' => $type,
                'node' => $nodeKey,
                'hierarchy' => ['level', 'term', 'semester_optional', 'subject', 'section', 'resource'],
                ...($data['context'] ?? []),
            ],
            'breadcrumbs' => $data['breadcrumbs'] ?? [],
            'children' => Collection::make($data['children'] ?? [])->values(),
            'resources' => $data['resources'] ?? null,
            'capabilities' => $data['capabilities'] ?? $this->emptyCapabilities(),
            'search' => $data['search'] ?? null,
        ];
    }

    private function resourcePage(LengthAwarePaginator $paginator, string $type): array
    {
        return [
            'data' => collect($paginator->items())->map(fn (CourseResource $resource) => [
                'id' => $resource->id,
                'title' => $resource->title,
                'description' => $resource->description,
                'file_type' => $resource->file_type,
                'category' => $resource->category,
                'sub_category' => $resource->sub_category,
                'file_path' => $type === LibraryContract::TYPE_COURSE ? $resource->file_path : null,
                'semester_info' => $resource->semester_info,
                'visibility' => $resource->visibility,
                'downloads_count' => (int) $resource->downloads_count,
                'subject' => $resource->subject ? [
                    'id' => $resource->subject->id,
                    'name' => $resource->subject->name,
                    'code' => $resource->subject->code,
                ] : null,
                'uploader' => $resource->uploader ? [
                    'id' => $resource->uploader->id,
                    'name' => $resource->uploader->name,
                    'role' => $this->roleValue($resource->uploader->role),
                ] : null,
                'download_endpoint' => $type === LibraryContract::TYPE_SHARED
                    ? "library/{$resource->id}/download"
                    : null,
                'created_at' => $resource->created_at?->toIso8601String(),
            ])->values(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    private function breadcrumbsForKey(User $user, string $type, string $nodeKey): array
    {
        return match ($this->nodeType($nodeKey)) {
            'root' => [],
            'major' => $this->majorBreadcrumbs($user, $this->nodeId($nodeKey)),
            'level' => $this->levelBreadcrumbsForId($user, $type, $this->nodeId($nodeKey)),
            'term' => $this->termBreadcrumbsForId($user, $type, $this->nodeId($nodeKey)),
            'semester' => $this->semesterBreadcrumbsForId($user, $type, $this->nodeId($nodeKey)),
            'subject' => $this->subjectBreadcrumbs($user, $this->authorizedSubject($user, $type, $this->nodeId($nodeKey))),
            'section' => $this->sectionBreadcrumbsForKey($user, $type, $nodeKey),
            'custom' => $this->customBreadcrumbsForId($user, $type, $this->nodeId($nodeKey)),
            default => [],
        };
    }

    private function majorBreadcrumbs(User $user, int $majorId): array
    {
        $major = Major::findOrFail($majorId);
        $this->authorizeMajor($user, $major);

        return [$this->crumb("major:{$major->id}", 'major', $major->name)];
    }

    private function levelBreadcrumbsForId(User $user, string $type, int $levelId): array
    {
        $level = Level::with('major')->findOrFail($levelId);
        $this->authorizeLevel($user, $type, $level);

        return $this->levelBreadcrumbs($user, $level);
    }

    private function termBreadcrumbsForId(User $user, string $type, int $termId): array
    {
        $term = Term::with('level.major')->findOrFail($termId);
        $this->authorizeLevel($user, $type, $term->level);

        return [
            ...$this->levelBreadcrumbs($user, $term->level),
            $this->crumb("term:{$term->id}", 'term', $term->name),
        ];
    }

    private function semesterBreadcrumbsForId(User $user, string $type, int $semesterId): array
    {
        $semester = Semester::with('term.level.major')->findOrFail($semesterId);
        $this->authorizeLevel($user, $type, $semester->term->level);

        return [
            ...$this->levelBreadcrumbs($user, $semester->term->level),
            $this->crumb("term:{$semester->term->id}", 'term', $semester->term->name),
            $this->crumb("semester:{$semester->id}", 'semester', $semester->name),
        ];
    }

    private function sectionBreadcrumbsForKey(User $user, string $type, string $nodeKey): array
    {
        [, $subjectId, $slug] = array_pad(explode(':', $nodeKey, 3), 3, null);
        if (! $subjectId || ! isset(LibraryContract::DEFAULT_SECTIONS[$slug])) {
            throw new NotFoundHttpException('قسم المكتبة المطلوب غير موجود.');
        }

        $subject = $this->authorizedSubject($user, $type, (int) $subjectId);

        return [
            ...$this->subjectBreadcrumbs($user, $subject),
            $this->crumb($nodeKey, 'section', LibraryContract::DEFAULT_SECTIONS[$slug]['title']),
        ];
    }

    private function customBreadcrumbsForId(User $user, string $type, int $nodeId): array
    {
        $node = LibraryNode::with('space.subject')->findOrFail($nodeId);
        if (! $node->is_active || ! $node->space->is_active || $node->space->type !== $type) {
            throw new NotFoundHttpException('قسم المكتبة المطلوب غير متاح.');
        }

        $subject = $this->authorizedSubject($user, $type, $node->space->subject_id);

        return [
            ...$this->subjectBreadcrumbs($user, $subject),
            ...$this->customNodeBreadcrumbs($node),
        ];
    }

    private function subjectBreadcrumbs(User $user, Subject $subject): array
    {
        $breadcrumbs = $this->levelBreadcrumbs($user, $subject->level);
        if ($subject->term) {
            $breadcrumbs[] = $this->crumb("term:{$subject->term->id}", 'term', $subject->term->name);
        }
        if ($subject->semester) {
            $breadcrumbs[] = $this->crumb("semester:{$subject->semester->id}", 'semester', $subject->semester->name);
        }
        $breadcrumbs[] = $this->crumb("subject:{$subject->id}", 'subject', $subject->name);

        return $breadcrumbs;
    }

    private function levelBreadcrumbs(User $user, Level $level): array
    {
        $breadcrumbs = [];
        if ($user->hasRole('doctor')) {
            $breadcrumbs[] = $this->crumb("major:{$level->major->id}", 'major', $level->major->name);
        }
        $breadcrumbs[] = $this->crumb("level:{$level->id}", 'level', $level->name);

        return $breadcrumbs;
    }

    private function customNodeBreadcrumbs(LibraryNode $node): array
    {
        $nodes = collect([$node]);
        $parent = $node->parent;
        while ($parent) {
            $nodes->prepend($parent);
            $parent = $parent->parent;
        }

        return $nodes->map(fn (LibraryNode $item) => $this->crumb(
            "custom:{$item->id}",
            $item->node_type,
            $item->title
        ))->values()->all();
    }

    private function subjectChild(Subject $subject): array
    {
        $title = $subject->code ? "{$subject->name} ({$subject->code})" : $subject->name;

        return $this->child("subject:{$subject->id}", 'subject', $title, 'menu_book');
    }

    private function child(
        string $key,
        string $type,
        string $title,
        string $icon,
        bool $hasChildren = true,
        bool $isSystem = true,
        int $sortOrder = 0
    ): array {
        return [
            'key' => $key,
            'type' => $type,
            'title' => $title,
            'icon' => $icon,
            'has_children' => $hasChildren,
            'is_system' => $isSystem,
            'sort_order' => $sortOrder,
        ];
    }

    private function crumb(string $key, string $type, string $title): array
    {
        return ['key' => $key, 'type' => $type, 'title' => $title];
    }

    private function emptyCapabilities(): array
    {
        return [
            ...array_fill_keys(LibraryContract::CAPABILITIES, false),
            'scope_limited' => false,
        ];
    }

    private function emptyNodeResponse(): array
    {
        return [
            'breadcrumbs' => [],
            'children' => [],
            'resources' => null,
            'capabilities' => $this->emptyCapabilities(),
        ];
    }

    private function nodeType(string $nodeKey): string
    {
        return $nodeKey === 'root' ? 'root' : explode(':', $nodeKey, 2)[0];
    }

    private function nodeId(string $nodeKey): int
    {
        $parts = explode(':', $nodeKey, 2);
        if (count($parts) !== 2 || ! ctype_digit($parts[1]) || (int) $parts[1] < 1) {
            throw new NotFoundHttpException('معرف عنصر المكتبة غير صالح.');
        }

        return (int) $parts[1];
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

    private function roleValue(mixed $role): string
    {
        return $role instanceof \BackedEnum ? (string) $role->value : (string) $role;
    }
}
