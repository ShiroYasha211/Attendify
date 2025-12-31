<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\Academic\Major;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DelegateController extends Controller
{
    /**
     * عرض قائمة المندوبين.
     */
    /**
     * عرض قائمة المندوبين.
     */
    public function index()
    {
        $delegates = User::where('role', UserRole::DELEGATE)
            ->with(['university', 'college', 'major', 'level'])
            ->latest()
            ->paginate(10);

        // For the dropdown (Grouped: University > College > Major > Levels)
        // Since it's deeply nested, we might just pass Universites with all children, 
        // but to keep it simple and performant for the form, passing Majors with Levels is good enough as implemented.
        // Actually best UX is: Select College -> Select Major -> Select Level.
        // For simplicity in this iteration: We load universities with colleges, majors, levels.
        $universities = \App\Models\Academic\University::with('colleges.majors.levels')->get();

        return view('admin.users.delegates.index', compact('delegates', 'universities'));
    }

    /**
     * تخزين مندوب جديد.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'level_id' => 'required|exists:levels,id',
        ], [
            'level_id.required' => 'يرجى اختيار المستوى الدراسي.',
            'email.unique' => 'البريد الإلكتروني مسجل مسبقاً.'
        ]);

        $level = \App\Models\Academic\Level::with('major.college.university')->findOrFail($request->level_id);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => UserRole::DELEGATE,
            'level_id' => $level->id,
            'major_id' => $level->major_id,
            'college_id' => $level->major->college_id,
            'university_id' => $level->major->college->university_id,
        ]);

        return redirect()->route('admin.delegates.index')
            ->with('success', 'تم إضافة المندوب بنجاح.');
    }

    /**
     * تحديث بيانات المندوب.
     */
    public function update(Request $request, User $delegate)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $delegate->id,
            'level_id' => 'required|exists:levels,id',
        ], [
            'level_id.required' => 'يرجى تحديد المستوى الذي يديره المندوب.',
            'email.unique' => 'البريد الإلكتروني مسجل مسبقاً.'
        ]);

        $level = \App\Models\Academic\Level::with('major.college.university')->findOrFail($request->level_id);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'level_id' => $level->id,
            'major_id' => $level->major_id,
            'college_id' => $level->major->college_id,
            'university_id' => $level->major->college->university_id,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8']);
            $updateData['password'] = Hash::make($request->password);
        }

        $delegate->update($updateData);

        return redirect()->route('admin.delegates.index')
            ->with('success', 'تم تحديث بيانات المندوب بنجاح.');
    }

    /**
     * حذف مندوب.
     */
    public function destroy(User $delegate)
    {
        if ($delegate->role !== UserRole::DELEGATE) {
            return back()->with('error', 'لا يمكن حذف هذا المستخدم من هنا.');
        }

        $delegate->delete();
        return redirect()->route('admin.delegates.index')
            ->with('success', 'تم حذف المندوب بنجاح.');
    }
}
