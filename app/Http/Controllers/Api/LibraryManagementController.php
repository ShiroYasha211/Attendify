<?php

namespace App\Http\Controllers\Api;

use App\Models\Academic\Subject;
use App\Models\CourseResource;
use App\Models\LibraryContributor;
use App\Models\LibraryNode;
use App\Models\LibrarySpace;
use App\Models\User;
use App\Services\Library\LibraryManagementService;
use App\Support\Library\LibraryContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LibraryManagementController extends BaseController
{
    public function __construct(private readonly LibraryManagementService $management) {}

    public function ensureSpace(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'type' => ['required', Rule::in(LibraryContract::TYPES)],
        ]);

        $space = $this->management->ensureSpace(
            $request->user(),
            Subject::findOrFail($validated['subject_id']),
            $validated['type']
        );

        return $this->success($space, 'تم تجهيز مساحة المكتبة وأقسامها الأساسية.');
    }

    public function createNode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'library_space_id' => ['required', 'integer', 'exists:library_spaces,id'],
            'parent_id' => ['nullable', 'integer', 'exists:library_nodes,id'],
            'title' => ['required', 'string', 'max:150'],
            'node_type' => ['nullable', Rule::in(['folder', 'section'])],
            'icon' => ['nullable', 'string', 'max:64'],
        ]);

        $node = $this->management->createNode(
            $request->user(),
            LibrarySpace::findOrFail($validated['library_space_id']),
            $validated
        );

        return $this->success($node, 'تم إنشاء القسم بنجاح.', 201);
    }

    public function updateNode(Request $request, LibraryNode $node): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:library_nodes,id'],
            'title' => ['sometimes', 'required', 'string', 'max:150'],
            'icon' => ['sometimes', 'nullable', 'string', 'max:64'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        $updated = $this->management->updateNode($request->user(), $node, $validated);

        return $this->success($updated, 'تم تحديث القسم بنجاح.');
    }

    public function deleteNode(Request $request, LibraryNode $node): JsonResponse
    {
        $this->management->deleteNode($request->user(), $node);

        return $this->success(null, 'تم حذف القسم وفروعه من العرض.');
    }

    public function reorderNodes(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'library_space_id' => ['required', 'integer', 'exists:library_spaces,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'distinct', 'exists:library_nodes,id'],
            'items.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $this->management->reorderNodes(
            $request->user(),
            LibrarySpace::findOrFail($validated['library_space_id']),
            $validated['items']
        );

        return $this->success(null, 'تم حفظ ترتيب الأقسام.');
    }

    public function uploadResource(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'node_id' => ['required', 'integer', 'exists:library_nodes,id'],
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf,ppt,pptx,doc,docx,xls,xlsx,jpg,jpeg,png,zip,rar', 'max:51200'],
            'description' => ['nullable', 'string'],
            'semester_info' => ['nullable', 'string', 'max:255'],
            'visibility' => ['nullable', Rule::in(['batch', 'college', 'everyone'])],
        ]);

        $resource = $this->management->uploadResource(
            $request->user(),
            LibraryNode::findOrFail($validated['node_id']),
            $request->file('file'),
            $validated
        );

        return $this->success($resource, 'تم رفع الملف وربطه بالقسم.', 201);
    }

    public function attachResource(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'node_id' => ['required', 'integer', 'exists:library_nodes,id'],
            'resource_id' => ['required', 'integer', 'exists:course_resources,id'],
        ]);

        $placement = $this->management->attachResource(
            $request->user(),
            LibraryNode::findOrFail($validated['node_id']),
            CourseResource::findOrFail($validated['resource_id'])
        );

        return $this->success($placement, 'تم ربط الملف بالقسم.');
    }

    public function moveResource(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source_node_id' => ['required', 'integer', 'exists:library_nodes,id'],
            'target_node_id' => ['required', 'integer', 'different:source_node_id', 'exists:library_nodes,id'],
            'resource_id' => ['required', 'integer', 'exists:course_resources,id'],
        ]);

        $placement = $this->management->moveResource(
            $request->user(),
            LibraryNode::findOrFail($validated['source_node_id']),
            LibraryNode::findOrFail($validated['target_node_id']),
            CourseResource::findOrFail($validated['resource_id'])
        );

        return $this->success($placement, 'تم نقل الملف إلى القسم المحدد.');
    }

    public function grantContributor(Request $request): JsonResponse
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

        $contributor = $this->management->grantContributor(
            $request->user(),
            LibrarySpace::findOrFail($validated['library_space_id']),
            User::findOrFail($validated['user_id']),
            $validated['capabilities'],
            $validated['node_ids'] ?? [],
            $validated['expires_at'] ?? null
        );

        return $this->success($contributor, 'تم حفظ صلاحيات المساهم.');
    }

    public function revokeContributor(Request $request, LibraryContributor $contributor): JsonResponse
    {
        $this->management->revokeContributor($request->user(), $contributor);

        return $this->success(null, 'تم سحب صلاحيات المساهم.');
    }
}
