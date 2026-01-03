<?php

namespace App\Http\Controllers\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $delegate = Auth::user();

        $students = User::where('role', UserRole::STUDENT)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->with(['university', 'college', 'major', 'level.terms.subjects.doctor'])
            ->latest()
            ->paginate(10);

        return view('delegate.students.index', compact('students'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('delegate.students.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $delegate = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'student_number' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => UserRole::STUDENT,
            'student_number' => $validated['student_number'],
            'university_id' => $delegate->university_id,
            'college_id' => $delegate->college_id,
            'major_id' => $delegate->major_id,
            'level_id' => $delegate->level_id,
        ]);

        return redirect()->route('delegate.students.index')
            ->with('success', 'تم إضافة الطالب بنجاح.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $student)
    {
        $delegate = Auth::user();

        // Enforce Scope
        if ($student->major_id != $delegate->major_id || $student->level_id != $delegate->level_id) {
            abort(403);
        }

        return view('delegate.students.edit', compact('student'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $student)
    {
        $delegate = Auth::user();
        if ($student->major_id != $delegate->major_id || $student->level_id != $delegate->level_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($student->id)],
            'student_number' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($student->id)],
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'student_number' => $validated['student_number'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $student->update($data);

        return redirect()->route('delegate.students.index')
            ->with('success', 'تم تحديث بيانات الطالب بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $student)
    {
        $delegate = Auth::user();
        if ($student->major_id != $delegate->major_id || $student->level_id != $delegate->level_id) {
            abort(403);
        }

        $student->delete();

        return redirect()->route('delegate.students.index')
            ->with('success', 'تم حذف الطالب بنجاح.');
    }
}
