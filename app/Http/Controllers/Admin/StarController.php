<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StarTransaction;
use App\Models\Academic\University;
use App\Models\Academic\College;
use App\Models\Academic\Major;
use App\Models\Academic\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StarController extends Controller
{
    /**
     * Display student search and star granting interface.
     */
    public function index(Request $request)
    {
        $universities = University::all();
        $colleges     = College::all();
        $majors       = Major::all();
        $levels       = Level::all();

        $query = User::whereIn('role', ['student', 'delegate', 'practical_delegate']);

        // Apply filters
        if ($request->filled('university_id')) $query->where('university_id', $request->university_id);
        if ($request->filled('college_id'))    $query->where('college_id', $request->college_id);
        if ($request->filled('major_id'))      $query->where('major_id', $request->major_id);
        if ($request->filled('level_id'))      $query->where('level_id', $request->level_id);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('student_number', 'like', "%{$search}%");
            });
        }

        $students = $query->with(['university', 'college', 'major', 'level'])
            ->latest()
            ->paginate(20);

        return view('admin.stars.index', compact('students', 'universities', 'colleges', 'majors', 'levels'));
    }

    /**
     * Grant stars to selected students.
     */
    public function grant(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
            'amount'      => 'required|integer|min:-1000|max:1000',
            'description' => 'required|string|max:255',
        ]);

        $admin = Auth::user();
        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($request->student_ids as $studentId) {
                $student = User::find($studentId);
                if (!$student) continue;

                if ($request->amount > 0) {
                    $student->addStars($request->amount, 'admin_grant', $admin->id, $request->description);
                } elseif ($request->amount < 0) {
                    $student->deductStars($request->amount, 'admin_penalty', $admin->id, $request->description);
                }
                
                $count++;
            }

            DB::commit();
            return back()->with('success', "تم منح {$request->amount} نجوم بنجاح لـ {$count} طالباً.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء منح النجوم: ' . $e->getMessage());
        }
    }
}
