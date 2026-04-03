<?php

namespace App\Http\Controllers\Api\Delegate;

use App\Enums\UserRole;
use App\Models\DelegateGradeDelegation;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthorizedGradeController extends DelegateApiController
{
    public function index(Request $request)
    {
        $user = $request->user();

        $delegations = $user->delegatedGradeCategories()
            ->with(['subject:id,name,major_id,level_id', 'doctor:id,name'])
            ->get();

        $helperTasks = $user->delegatedGradeHelperTasks()
            ->active()
            ->with(['category.subject:id,name,major_id,level_id', 'category.doctor:id,name', 'delegatedBy:id,name', 'students:id,name,student_number'])
            ->get();

        return $this->success([
            'module' => [
                'name' => 'authorized_grade_tasks',
                'purpose' => 'Shows direct doctor delegations and helper tasks assigned by the main delegate.',
                'main_delegate' => 'The main delegate can create helper tasks for selected students or for the full category scope.',
                'helper_rule' => 'Helpers can enter grades only for their assigned scope and cannot approve them.',
            ],
            'direct_delegations' => $delegations,
            'helper_tasks' => $helperTasks,
        ]);
    }

    public function show(Request $request, $categoryId)
    {
        [$category, $helperTask, $students] = $this->resolveCategoryAccess($request->user(), (int) $categoryId);

        return $this->success([
            'category' => $category->loadMissing(['subject:id,name', 'doctor:id,name']),
            'access_mode' => $helperTask ? 'helper_task' : 'direct_delegation',
            'helper_task' => $helperTask,
            'students' => $students,
        ]);
    }

    public function store(Request $request, $categoryId)
    {
        $user = $request->user();
        [$category, $helperTask, $students] = $this->resolveCategoryAccess($user, (int) $categoryId);
        $allowedStudentIds = $students->pluck('id');

        $request->validate([
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:users,id',
            'grades.*.score' => 'nullable|numeric|min:0|max:' . $category->max_score,
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->grades as $gradeData) {
                if (!array_key_exists('score', $gradeData) || $gradeData['score'] === null || $gradeData['score'] === '') {
                    continue;
                }

                if (!$allowedStudentIds->contains((int) $gradeData['student_id'])) {
                    return $this->error('One or more selected students are outside your delegated scope.', 422);
                }

                Grade::updateOrCreate(
                    [
                        'student_id' => $gradeData['student_id'],
                        'subject_id' => $category->subject_id,
                        'category_id' => $category->id,
                        'type' => 'continuous',
                    ],
                    [
                        'score' => $gradeData['score'],
                        'max_score' => $category->max_score,
                        'created_by' => $user->id,
                        'status' => 'pending',
                    ]
                );
            }

            DB::commit();

            return $this->success([
                'status' => 'pending_review',
                'access_mode' => $helperTask ? 'helper_task' : 'direct_delegation',
            ], 'Grades saved and sent for doctor review.');
        } catch (\Throwable $exception) {
            DB::rollBack();
            return $this->error('Failed to save delegated grades.', 500);
        }
    }

    protected function resolveCategoryAccess(User $user, int $categoryId): array
    {
        $directCategory = $user->delegatedGradeCategories()
            ->with(['subject:id,name,major_id,level_id', 'doctor:id,name'])
            ->find($categoryId);

        $helperTask = null;
        $category = $directCategory;

        if (!$category) {
            $helperTask = $user->delegatedGradeHelperTasks()
                ->active()
                ->with(['category.subject:id,name,major_id,level_id', 'category.doctor:id,name', 'students:id,name,student_number'])
                ->where('category_id', $categoryId)
                ->latest()
                ->firstOrFail();

            $category = $helperTask->category;
        }

        $studentsQuery = User::query()
            ->whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE, UserRole::PRACTICAL_DELEGATE])
            ->where('major_id', $category->subject->major_id)
            ->where('level_id', $category->subject->level_id)
            ->with(['grades' => function ($query) use ($category) {
                $query->where('category_id', $category->id);
            }])
            ->select('id', 'name', 'student_number')
            ->orderBy('name');

        if ($helperTask && $helperTask->delegation_type === DelegateGradeDelegation::TYPE_PARTIAL) {
            $studentsQuery->whereIn('id', $helperTask->students->pluck('id'));
        }

        return [$category, $helperTask, $studentsQuery->get()];
    }
}
