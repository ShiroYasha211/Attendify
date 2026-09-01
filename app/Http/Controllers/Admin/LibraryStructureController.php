<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Academic\Subject;
use App\Models\CourseResource;
use App\Models\LibraryActivityLog;
use App\Models\LibraryContributor;
use App\Models\LibraryNode;
use App\Models\LibrarySpace;
use App\Models\User;
use App\Services\Library\LibraryManagementService;
use App\Support\Library\LibraryContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LibraryStructureController extends Controller
{
    public function __construct(private readonly LibraryManagementService $management) {}

    public function index(Request $request): View
    {
        $this->management->assertAdmin($request->user());
        $type = in_array($request->string('type')->toString(), LibraryContract::TYPES, true)
            ? $request->string('type')->toString()
            : LibraryContract::TYPE_SHARED;

        $subject = null;
        $space = null;
        $nodeTree = collect();
        $flatNodes = collect();
        $contributors = collect();
        $activities = collect();
        $stats = ['nodes' => 0, 'resources' => 0, 'contributors' => 0, 'active_nodes' => 0];

        if ($request->filled('subject_id')) {
            $subject = Subject::with(['major.college.university', 'level', 'term', 'semester'])
                ->findOrFail($request->integer('subject_id'));
            $space = $this->management->ensureSpace($request->user(), $subject, $type);

            $nodes = LibraryNode::where('library_space_id', $space->id)
                ->withCount('placements')
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get();
            $flatNodes = $nodes;
            $nodeTree = $this->buildNodeTree($nodes);

            $contributors = LibraryContributor::where('library_space_id', $space->id)
                ->with(['user:id,name,email,role', 'grantedBy:id,name', 'scopedNodes:id,title'])
                ->latest()
                ->get();
            $activities = LibraryActivityLog::where('library_space_id', $space->id)
                ->with(['actor:id,name', 'node:id,title', 'resource:id,title'])
                ->latest()
                ->limit(20)
                ->get();

            $stats = [
                'nodes' => $nodes->count(),
                'active_nodes' => $nodes->where('is_active', true)->count(),
                'resources' => CourseResource::where('subject_id', $subject->id)->count(),
                'contributors' => $contributors->where('is_active', true)->count(),
            ];
        }

        return view('admin.library.structure', compact(
            'type',
            'subject',
            'space',
            'nodeTree',
            'flatNodes',
            'contributors',
            'activities',
            'stats'
        ))->with([
            'capabilityLabels' => [
                'can_upload' => 'رفع الملفات',
                'can_create_sections' => 'إنشاء الأقسام',
                'can_update_sections' => 'تعديل الأقسام',
                'can_delete_sections' => 'حذف الأقسام',
                'can_reorder_sections' => 'ترتيب الأقسام',
                'can_manage_resources' => 'نقل وربط الملفات',
            ],
            'activityLabels' => [
                'space_created' => 'إنشاء مساحة المكتبة',
                'node_created' => 'إنشاء قسم',
                'node_updated' => 'تعديل قسم',
                'node_deleted' => 'حذف قسم',
                'nodes_reordered' => 'إعادة ترتيب الأقسام',
                'resource_uploaded' => 'رفع ملف',
                'resource_attached' => 'ربط ملف',
                'resource_moved' => 'نقل ملف',
                'contributor_granted' => 'منح صلاحيات',
                'contributor_revoked' => 'سحب صلاحيات',
            ],
        ]);
    }

    public function lookupSubjects(Request $request): JsonResponse
    {
        $this->management->assertAdmin($request->user());
        $term = trim((string) $request->input('q', ''));

        $subjects = Subject::with(['major:id,name,college_id', 'level:id,name', 'term:id,name'])
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($scope) use ($term) {
                    $scope->where('name', 'like', "%{$term}%")
                        ->orWhere('code', 'like', "%{$term}%")
                        ->orWhereHas('major', fn ($major) => $major->where('name', 'like', "%{$term}%"));
                });
            })
            ->orderBy('name')
            ->limit(25)
            ->get();

        return response()->json([
            'results' => $subjects->map(fn (Subject $subject) => [
                'id' => $subject->id,
                'label' => $subject->name.($subject->code ? " ({$subject->code})" : ''),
                'meta' => collect([
                    $subject->major?->name,
                    $subject->level?->name,
                    $subject->term?->name,
                ])->filter()->implode(' - '),
            ])->values(),
        ]);
    }

    public function lookupUsers(Request $request): JsonResponse
    {
        $this->management->assertAdmin($request->user());
        $request->validate(['subject_id' => ['required', 'integer', 'exists:subjects,id']]);
        $subject = Subject::with('major')->findOrFail($request->integer('subject_id'));
        $term = trim((string) $request->input('q', ''));

        $users = User::where('college_id', $subject->major->college_id)
            ->whereIn('role', [
                UserRole::DOCTOR->value,
                UserRole::DELEGATE->value,
                UserRole::STUDENT->value,
            ])
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($scope) use ($term) {
                    $scope->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('student_number', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->limit(25)
            ->get(['id', 'name', 'email', 'student_number', 'role']);

        return response()->json([
            'results' => $users->map(fn (User $user) => [
                'id' => $user->id,
                'label' => $user->name,
                'meta' => collect([
                    $user->student_number,
                    $user->email,
                    $user->role instanceof UserRole ? $user->role->label() : (string) $user->role,
                ])->filter()->implode(' - '),
            ])->values(),
        ]);
    }

    public function storeNode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'library_space_id' => ['required', 'integer', 'exists:library_spaces,id'],
            'parent_id' => ['nullable', 'integer', 'exists:library_nodes,id'],
            'title' => ['required', 'string', 'max:150'],
            'node_type' => ['required', Rule::in(['folder', 'section'])],
            'icon' => ['nullable', 'string', 'max:64'],
        ]);

        $this->management->createNode(
            $request->user(),
            LibrarySpace::findOrFail($validated['library_space_id']),
            $validated
        );

        return back()->with('success', 'تمت إضافة القسم إلى هيكل المكتبة.');
    }

    public function updateNode(Request $request, LibraryNode $node): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:150'],
            'icon' => ['sometimes', 'nullable', 'string', 'max:64'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        if ($request->has('toggle_active')) {
            $validated['is_active'] = ! $node->is_active;
        }

        $this->management->updateNode($request->user(), $node, $validated);

        return back()->with('success', 'تم تحديث القسم.');
    }

    public function destroyNode(Request $request, LibraryNode $node): RedirectResponse
    {
        $this->management->deleteNode($request->user(), $node);

        return back()->with('success', 'تم حذف القسم المخصص وفروعه من العرض.');
    }

    public function moveNode(Request $request, LibraryNode $node): RedirectResponse
    {
        $validated = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]]);
        $siblings = LibraryNode::where('library_space_id', $node->library_space_id)
            ->where('parent_id', $node->parent_id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $index = $siblings->search(fn (LibraryNode $item) => $item->id === $node->id);
        $targetIndex = $validated['direction'] === 'up' ? $index - 1 : $index + 1;

        if ($index !== false && $targetIndex >= 0 && $targetIndex < $siblings->count()) {
            $ordered = $siblings->values()->all();
            [$ordered[$index], $ordered[$targetIndex]] = [$ordered[$targetIndex], $ordered[$index]];
            $items = collect($ordered)->values()->map(fn (LibraryNode $item, int $position) => [
                'id' => $item->id,
                'sort_order' => $position,
            ])->all();
            $this->management->reorderNodes($request->user(), $node->space, $items);
        }

        return back()->with('success', 'تم تحديث ترتيب القسم.');
    }

    public function uploadResource(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'node_id' => ['required', 'integer', 'exists:library_nodes,id'],
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf,ppt,pptx,doc,docx,xls,xlsx,jpg,jpeg,png,zip,rar', 'max:51200'],
            'description' => ['nullable', 'string'],
            'semester_info' => ['nullable', 'string', 'max:255'],
            'visibility' => ['required', Rule::in(['batch', 'college', 'everyone'])],
        ]);

        $this->management->uploadResource(
            $request->user(),
            LibraryNode::findOrFail($validated['node_id']),
            $request->file('file'),
            $validated
        );

        return back()->with('success', 'تم رفع الملف وربطه بالقسم المحدد.');
    }

    public function storeContributor(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'library_space_id' => ['required', 'integer', 'exists:library_spaces,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'capabilities' => ['required', 'array', 'min:1'],
            'capabilities.*' => ['required', 'string', 'distinct', Rule::in(LibraryContract::CAPABILITIES)],
            'node_ids' => ['nullable', 'array'],
            'node_ids.*' => ['integer', 'distinct', 'exists:library_nodes,id'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $this->management->grantContributor(
            $request->user(),
            LibrarySpace::findOrFail($validated['library_space_id']),
            User::findOrFail($validated['user_id']),
            $validated['capabilities'],
            $validated['node_ids'] ?? [],
            $validated['expires_at'] ?? null
        );

        return back()->with('success', 'تم حفظ صلاحيات المساهم ونطاقه.');
    }

    public function revokeContributor(Request $request, LibraryContributor $contributor): RedirectResponse
    {
        $this->management->revokeContributor($request->user(), $contributor);

        return back()->with('success', 'تم سحب صلاحيات المساهم.');
    }

    private function buildNodeTree($nodes, ?int $parentId = null): \Illuminate\Support\Collection
    {
        return $nodes->filter(fn (LibraryNode $node) => $node->parent_id === $parentId)
            ->values()
            ->map(fn (LibraryNode $node) => [
                'node' => $node,
                'children' => $this->buildNodeTree($nodes, $node->id),
            ]);
    }
}
