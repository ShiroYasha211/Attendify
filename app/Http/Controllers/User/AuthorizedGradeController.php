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

        return view('user.grades.authorized.index', compact('delegations'));
    }

    /**
     * Show entry form for a specific category.
     */
    public function show($categoryId)
    {
        $user = Auth::user();
        
        $category = $user->delegatedGradeCategories()
            ->with(['subject'])
            ->findOrFail($categoryId);

        $subject = $category->subject;

        $students = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])
            ->where('major_id', $subject->major_id)
            ->where('level_id', $subject->level_id)
            ->with(['grades' => function($q) use ($category) {
                $q->where('category_id', $category->id);
            }])
            ->orderBy('name')
            ->get();

        return view('user.grades.authorized.show', compact('category', 'students'));
    }

    /**
     * Store grades for a category.
     */
    public function store(Request $request, $categoryId)
    {
        $user = Auth::user();
        
        $category = $user->delegatedGradeCategories()->findOrFail($categoryId);

        $request->validate([
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:users,id',
            'grades.*.score' => 'nullable|numeric|min:0|max:' . $category->max_score,
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->grades as $gradeData) {
                if ($gradeData['score'] === null) continue;

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
}
