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

        return $this->success([
            'delegations' => $delegations,
        ]);
    }

    /**
     * Show entry form for a specific category.
     */
    public function show(Request $request, $categoryId)
    {
        $user = $request->user();
        
        $category = $user->delegatedGradeCategories()
            ->with(['subject:id,name'])
            ->findOrFail($categoryId);

        $subject = $category->subject;

        $students = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])
            ->where('major_id', $subject->major_id)
            ->where('level_id', $subject->level_id)
            ->with(['grades' => function($q) use ($category) {
                $q->where('category_id', $category->id);
            }])
            ->select('id', 'name', 'student_number')
            ->orderBy('name')
            ->get();

        return $this->success([
            'category' => $category,
            'students' => $students,
        ]);
    }

    /**
     * Store grades for a category.
     */
    public function store(Request $request, $categoryId)
    {
        $user = $request->user();
        
        $category = $user->delegatedGradeCategories()->findOrFail($categoryId);

        $request->validate([
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:users,id',
            'grades.*.score' => 'nullable|numeric|min:0|max:' . $category->max_score,
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->grades as $gradeData) {
                if (!isset($gradeData['score']) || $gradeData['score'] === null) continue;

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
            return $this->success([], 'تم حفظ الدرجات وإرسالها للمراجعة.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('حدث خطأ أثناء حفظ الدرجات.');
        }
    }
}
