<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Enums\UserRole;
use App\Models\Academic\Subject;
use App\Models\GradeCategory;
use App\Models\GradePermission;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeCategoryController extends DoctorApiController
{
    public function index(int $subjectId)
    {
        $subject = Subject::where('doctor_id', Auth::id())->findOrFail($subjectId);
        $categories = GradeCategory::where('subject_id', $subjectId)
            ->withCount('grades')
            ->with(['permissions.authorizedUser:id,name,student_number'])
            ->get();

        return $this->success([
            'subject' => $subject->only(['id', 'name', 'major_id', 'level_id']),
            'categories' => $categories,
            'total_max_score' => $categories->sum('max_score'),
        ]);
    }

    public function store(Request $request, int $subjectId)
    {
        Subject::where('doctor_id', Auth::id())->findOrFail($subjectId);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'max_score' => 'required|numeric|min:0.5|max:40',
        ]);

        $currentTotal = GradeCategory::where('subject_id', $subjectId)->sum('max_score');
        if (($currentTotal + $validated['max_score']) > 40.01) {
            return $this->error('إجمالي درجات أعمال السنة لا يمكن أن يتجاوز 40 درجة.', 422);
        }

        $category = GradeCategory::create([
            'subject_id' => $subjectId,
            'doctor_id' => Auth::id(),
            'name' => $validated['name'],
            'max_score' => $validated['max_score'],
        ]);

        return $this->success($category, 'تم إضافة تصنيف الدرجات بنجاح.', 201);
    }

    public function delegations(int $subjectId)
    {
        $subject = Subject::where('doctor_id', Auth::id())->findOrFail($subjectId);

        $categories = GradeCategory::where('subject_id', $subjectId)
            ->with(['permissions.authorizedUser:id,name,student_number'])
            ->get();

        $students = $this->eligibleUsersQuery($subject)
            ->orderBy('name')
            ->get(['id', 'name', 'student_number']);

        return $this->success([
            'module' => [
                'name' => 'grade_category_delegations',
                'purpose' => 'Allows the doctor to delegate one specific grade category to an eligible student or delegate for score entry.',
                'note' => 'Delegation is category-based, not subject-wide. A doctor must create grade categories before delegation becomes usable.',
            ],
            'workflow' => [
                'step_1' => 'Create one or more grade categories for the subject.',
                'step_2' => 'Open the delegations dashboard for that subject.',
                'step_3' => 'Choose an eligible student or delegate and assign the category.',
                'step_4' => 'The authorized user enters grades, which stay pending until doctor approval.',
            ],
            'subject' => $subject->only(['id', 'name']),
            'categories' => $categories,
            'students' => $students,
        ]);
    }

    public function delegate(Request $request, int $categoryId)
    {
        $category = GradeCategory::where('doctor_id', Auth::id())->findOrFail($categoryId);

        $validated = $request->validate([
            'authorized_user_id' => 'required|integer',
        ]);

        $authorizedUser = $this->eligibleUsersQuery($category->subject)
            ->whereKey($validated['authorized_user_id'])
            ->first();

        if (!$authorizedUser) {
            return $this->error('الطالب المحدد غير مؤهل للتفويض في هذه المادة.', 422);
        }

        $exists = GradePermission::where('category_id', $categoryId)
            ->where('authorized_user_id', $authorizedUser->id)
            ->exists();

        if ($exists) {
            return $this->error('هذا الطالب مفوض بالفعل لهذا التصنيف.', 422);
        }

        $permission = GradePermission::create([
            'category_id' => $categoryId,
            'authorized_user_id' => $authorizedUser->id,
        ]);

        return $this->success([
            'permission' => $permission->load('authorizedUser:id,name,student_number'),
            'effect' => 'The selected user can now enter grades for this category. Submitted grades will remain pending doctor review.',
        ], 'تم إضافة التفويض بنجاح.', 201);

    }
    public function revoke(Request $request, int $categoryId)
    {
        GradeCategory::where('doctor_id', Auth::id())->findOrFail($categoryId);

        $validated = $request->validate([
            'authorized_user_id' => 'required|exists:users,id',
        ]);

        GradePermission::where('category_id', $categoryId)
            ->where('authorized_user_id', $validated['authorized_user_id'])
            ->delete();

        return $this->success(null, 'تم سحب التفويض بنجاح.');
    }

    public function destroy(int $categoryId)
    {
        $category = GradeCategory::where('doctor_id', Auth::id())->findOrFail($categoryId);

        if ($category->grades()->exists()) {
            return $this->error('لا يمكن حذف هذا التصنيف لوجود درجات مسجلة عليه بالفعل.', 422);
        }

        $category->delete();

        return $this->success(null, 'تم حذف التصنيف بنجاح.');
    }

    protected function eligibleUsersQuery(Subject $subject): Builder
    {
        return User::query()
            ->whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE, UserRole::PRACTICAL_DELEGATE])
            ->where('major_id', $subject->major_id)
            ->where('level_id', $subject->level_id);
    }
}
