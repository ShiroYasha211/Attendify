<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\GradeCategory;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthorizedGradeController extends Controller
{
    /**
     * Display categories delegated to the current user.
     */
    public function index()
    {
        $user = Auth::user();
        
        $delegations = $user->delegatedGradeCategories()
            ->with(['subject', 'doctor'])
            ->get();

        $helperTasks = $user->delegatedGradeHelperTasks()
            ->active()
            ->with(['category.subject', 'category.doctor', 'delegatedBy', 'students'])
            ->get();

        return view('user.grades.authorized.index', compact('delegations', 'helperTasks'));
    }

    /**
     * Show entry form for a specific category.
     */
    public function show($categoryId)
    {
        $user = Auth::user();

        [$category, $helperTask, $students] = $this->resolveCategoryAccess($user, (int) $categoryId);

        $delegateHelperCandidates = collect();
        $delegateHelperTasks = collect();
        $delegateHelperStudentScope = collect();

        if ($user->role === UserRole::DELEGATE && !$helperTask) {
            $delegateHelperCandidates = $this->eligibleStudentsQuery($category)
                ->where('id', '!=', $user->id)
                ->orderBy('name')
                ->get();

            $delegateHelperStudentScope = $this->eligibleStudentsQuery($category)
                ->orderBy('name')
                ->get();

            $delegateHelperTasks = $user->issuedGradeHelperTasks()
                ->active()
                ->where('category_id', $category->id)
                ->with(['helperUser', 'students'])
                ->latest()
                ->get();
        }

        return view('user.grades.authorized.show', compact('category', 'students', 'helperTask', 'delegateHelperCandidates', 'delegateHelperTasks', 'delegateHelperStudentScope'));
    }

    /**
     * Store grades for a category.
     */
    public function store(Request $request, $categoryId)
    {
        $user = Auth::user();
        
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
                if ($gradeData['score'] === null) continue;

                if (!$allowedStudentIds->contains((int) $gradeData['student_id'])) {
                    return back()->with('error', 'بعض الطلاب المحددين خارج نطاق التفويض المسموح لك.');
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
                        'status' => 'pending', // Always pending when authorized user enters it
                    ]
                );
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء حفظ الدرجات.');
        }

        $redirectRoute = Auth::user()->role === UserRole::DELEGATE 
            ? 'delegate.authorized-grades.index' 
            : 'student.authorized-grades.index';

        return redirect()->route($redirectRoute)
            ->with('success', 'تم حفظ الدرجات وإرسالها للمراجعة.');
    }
    protected function resolveCategoryAccess(User $user, int $categoryId): array
    {
        $category = $user->delegatedGradeCategories()
            ->with(['subject', 'doctor'])
            ->find($categoryId);

        $helperTask = null;

        if (!$category) {
            $helperTask = $user->delegatedGradeHelperTasks()
                ->active()
                ->with(['category.subject', 'category.doctor', 'students'])
                ->where('category_id', $categoryId)
                ->latest()
                ->firstOrFail();

            $category = $helperTask->category;
        }

        $students = $this->eligibleStudentsQuery($category)
            ->with(['grades' => function ($query) use ($category) {
                $query->where('category_id', $category->id);
            }])
            ->orderBy('name');

        if ($helperTask && $helperTask->delegation_type === \App\Models\DelegateGradeDelegation::TYPE_PARTIAL) {
            $students->whereIn('id', $helperTask->students->pluck('id'));
        }

        return [$category, $helperTask, $students->get()];
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
