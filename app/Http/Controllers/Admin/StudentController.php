<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\Academic\Major;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * عرض قائمة الطلاب.
     */
    /**
     * عرض قائمة الطلاب.
     */
    public function index()
    {
        $students = User::where('role', UserRole::STUDENT)
            ->with(['university', 'college', 'major', 'level.terms.subjects.doctor'])
            ->latest()
            ->paginate(10);

        // Fetch Universities for the Create Form dropdown
        $universities = \App\Models\Academic\University::with('colleges.majors.levels')->get();

        // Fetch Delegates keyed by level_id to easily find the delegate for a student's level
        $delegates = User::where('role', UserRole::DELEGATE)->get()->keyBy('level_id');

        return view('admin.users.students.index', compact('students', 'universities', 'delegates'));
    }

    /**
     * عرض صفحة إضافـة طالب.
     */
    public function create()
    {
        // This is now handled within the Index page modal/form, but keeping it for fallback
        $majors = Major::with(['college', 'levels'])->get();
        return view('admin.users.students.create', compact('majors'));
    }

    /**
     * تخزين طالب جديد.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'student_number' => 'required|string|unique:users,student_number|max:50',
            'password' => 'required|string|min:8',
            'level_id' => 'required|exists:levels,id',
        ], [
            'student_number.unique' => 'الرقم الجامعي مسجل مسبقاً.',
            'level_id.required' => 'يرجى تحديد المستوى الدراسي.',
            'email.unique' => 'البريد الإلكتروني مسجل مسبقاً.'
        ]);

        $level = \App\Models\Academic\Level::with('major.college.university')->findOrFail($request->level_id);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'student_number' => $request->student_number,
            'password' => Hash::make($request->password),
            'role' => UserRole::STUDENT,
            'level_id' => $level->id,
            'major_id' => $level->major_id,
            'college_id' => $level->major->college_id,
            'university_id' => $level->major->college->university_id,
        ]);

        return redirect()->route('admin.students.index')
            ->with('success', 'تم تسجيل الطالب بنجاح.');
    }

    /**
     * تحديث بيانات الطالب.
     */
    /**
     * تحديث بيانات الطالب.
     */
    public function update(Request $request, User $student)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $student->id,
            'student_number' => 'required|string|max:50|unique:users,student_number,' . $student->id,
            'level_id' => 'required|exists:levels,id',
        ]);

        $level = \App\Models\Academic\Level::with('major.college.university')->findOrFail($request->level_id);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'student_number' => $request->student_number,
            'level_id' => $level->id,
            'major_id' => $level->major_id,
            'college_id' => $level->major->college_id,
            'university_id' => $level->major->college->university_id,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8']);
            $updateData['password'] = Hash::make($request->password);
        }

        $student->update($updateData);

        return redirect()->route('admin.students.index')
            ->with('success', 'تم تحديث بيانات الطالب بنجاح.');
    }

    /**
     * حذف طالب.
     */
    public function destroy(User $student)
    {
        if ($student->role !== UserRole::STUDENT) {
            return back()->with('error', 'لا يمكن حذف هذا المستخدم من قائمة الطلاب.');
        }

        $student->delete();
        return redirect()->route('admin.students.index')
            ->with('success', 'تم حذف الطالب بنجاح.');
    }
}
