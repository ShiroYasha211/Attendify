<?php

namespace App\Http\Controllers\Api\Student;

use App\Models\Grade;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthorizedGradeController extends StudentApiController
{
    /**
     * Display categories delegated to the current user.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $delegations = $user->delegatedGradeCategories()
            ->with(['subject:id,name', 'doctor:id,name'])
            ->get();

        $helperTasks = $user->delegatedGradeHelperTasks()
            ->active()
            ->with(['category.subject:id,name', 'category.doctor:id,name', 'delegatedBy:id,name', 'students:id,name,student_number'])
            ->get();

        return $this->success([
            'module' => [
                'name' => 'authorized_grade_tasks',
                'purpose' => 'Shows grade categories delegated to the current student or delegate for score entry.',
                'when_used' => 'Used only after a doctor delegates a specific grade category to this user.',
                'access_rule' => 'Not every student can use this module. It becomes useful only when the user has active delegated grade categories.',
            ],
            'delegations' => $delegations,
            'helper_tasks' => $helperTasks,
        ]);
    }

    /**
     * Show entry form for a specific category.
     */
    public function show(Request $request, $categoryId)
    {
        $user = $request->user();
        
        [$category, $helperTask, $students] = $this->resolveCategoryAccess($user, (int) $categoryId);

        return $this->success([
            'workflow' => [
                'step_1' => 'Doctor creates a grade category for a subject.',
                'step_2' => 'Doctor delegates that category to a student or delegate.',
                'step_3' => 'Authorized user enters scores for eligible students.',
                'step_4' => 'Saved scores remain pending until reviewed by the doctor.',
            ],
            'category' => $category,
            'access_mode' => $helperTask ? 'helper_task' : 'direct_delegation',
            'helper_task' => $helperTask,
            'students' => $students,
        ]);
    }

    /**
     * Store grades for a category.
     */
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
                if (!isset($gradeData['score']) || $gradeData['score'] === null) continue;

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
                        'status' => 'pending', // Always pending when authorized user enters
                    ]
                );
            }
            DB::commit();
            return $this->success([
                'status' => 'pending_review',
                'access_mode' => $helperTask ? 'helper_task' : 'direct_delegation',
                'next_step' => 'Doctor must review and approve delegated grades before they are considered final.',
            ], '?? ??? ??????? ???????? ????????.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('??? ??? ????? ??? ???????.');
        }
    }

    protected function resolveCategoryAccess($user, int $categoryId): array
    {
        $category = $user->delegatedGradeCategories()
            ->with(['subject:id,name,major_id,level_id', 'doctor:id,name'])
            ->find($categoryId);

        $helperTask = null;

        if (!$category) {
            $helperTask = $user->delegatedGradeHelperTasks()
                ->active()
                ->with(['category.subject:id,name,major_id,level_id', 'category.doctor:id,name', 'students:id,name,student_number'])
                ->where('category_id', $categoryId)
                ->latest()
                ->firstOrFail();

            $category = $helperTask->category;
        }

        $students = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE, UserRole::PRACTICAL_DELEGATE])
            ->where('major_id', $category->subject->major_id)
            ->where('level_id', $category->subject->level_id)
            ->with(['grades' => function ($query) use ($category) {
                $query->where('category_id', $category->id);
            }])
            ->select('id', 'name', 'student_number')
            ->orderBy('name');

        if ($helperTask && $helperTask->delegation_type === \App\Models\DelegateGradeDelegation::TYPE_PARTIAL) {
            $students->whereIn('id', $helperTask->students->pluck('id'));
        }

        return [$category, $helperTask, $students->get()];
    }
}
