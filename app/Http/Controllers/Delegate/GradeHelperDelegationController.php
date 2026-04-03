<?php

namespace App\Http\Controllers\Delegate;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\DelegateGradeDelegation;
use App\Models\GradeCategory;
use App\Models\User;
use Illuminate\Http\Request;

class GradeHelperDelegationController extends Controller
{
    public function store(Request $request, GradeCategory $category)
    {
        $delegate = $request->user();

        abort_unless($delegate->role === UserRole::DELEGATE, 403);
        abort_unless($delegate->delegatedGradeCategories()->where('grade_categories.id', $category->id)->exists(), 403);

        $validated = $request->validate([
            'helper_user_id' => 'required|exists:users,id',
            'delegation_type' => 'required|in:full,partial',
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'due_at' => 'nullable|date',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:users,id',
        ]);

        $eligibleStudents = $this->eligibleStudentsQuery($category)->pluck('id');
        abort_unless($eligibleStudents->contains((int) $validated['helper_user_id']), 422);

        if (($validated['delegation_type'] ?? 'full') === DelegateGradeDelegation::TYPE_PARTIAL) {
            $selectedIds = collect($validated['student_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
            if ($selectedIds->isEmpty() || $selectedIds->diff($eligibleStudents)->isNotEmpty()) {
                return back()->with('error', 'قائمة الطلاب المحددة لهذه المهمة غير صالحة.');
            }
        } else {
            $selectedIds = collect();
        }

        $delegation = DelegateGradeDelegation::create([
            'category_id' => $category->id,
            'delegated_by_id' => $delegate->id,
            'helper_user_id' => $validated['helper_user_id'],
            'delegation_type' => $validated['delegation_type'],
            'title' => $validated['title'],
            'notes' => $validated['notes'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
        ]);

        if ($selectedIds->isNotEmpty()) {
            $delegation->students()->sync($selectedIds);
        }

        return back()->with('success', 'تم إنشاء مهمة المساعدة في رصد الدرجات بنجاح.');
    }

    public function revoke(Request $request, DelegateGradeDelegation $delegation)
    {
        $delegate = $request->user();

        abort_unless($delegate->role === UserRole::DELEGATE, 403);
        abort_unless((int) $delegation->delegated_by_id === (int) $delegate->id, 403);

        $delegation->update([
            'is_revoked' => true,
            'revoked_at' => now(),
        ]);

        return back()->with('success', 'تم سحب مهمة المساعدة بنجاح.');
    }

    protected function eligibleStudentsQuery(GradeCategory $category)
    {
        $subject = $category->subject;

        return User::query()
            ->whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE, UserRole::PRACTICAL_DELEGATE])
            ->where('major_id', $subject->major_id)
            ->where('level_id', $subject->level_id);
    }
}
