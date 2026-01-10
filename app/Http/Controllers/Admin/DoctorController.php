<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\Academic\College;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DoctorController extends Controller
{
    use LogsActivity;

    /**
     * عرض قائمة الدكاترة.
     */
    public function index()
    {
        // جلب الدكاترة مع بياناتهم + المواد التي يدرسونها (وتفاصيل المواد)
        $doctors = User::where('role', UserRole::DOCTOR)
            ->with(['university', 'college', 'subjects.term.level.major'])
            ->latest()
            ->paginate(10);

        // نحتاج الجامعات والكليات للقوائم المنسدلة
        $universities = \App\Models\Academic\University::with('colleges')->get();

        return view('admin.users.doctors.index', compact('doctors', 'universities'));
    }

    /**
     * تخزين دكتور جديد.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'college_id' => 'required|exists:colleges,id',
        ], [
            'college_id.required' => 'يرجى تحديد الكلية التي يتبع لها الدكتور.',
            'email.unique' => 'البريد الإلكتروني مسجل مسبقاً.',
        ]);

        $college = College::findOrFail($request->college_id);

        $doctor = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => UserRole::DOCTOR,
            'college_id' => $college->id,
            'university_id' => $college->university_id,
        ]);

        $this->logCreate('Doctor', $doctor, "تم إضافة الدكتور: {$doctor->name}");

        return redirect()->route('admin.doctors.index')
            ->with('success', 'تم إضافة الدكتور بنجاح.');
    }

    /**
     * تحديث بيانات الدكتور.
     */
    public function update(Request $request, User $doctor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $doctor->id,
            'college_id' => 'required|exists:colleges,id',
        ]);

        $college = College::findOrFail($request->college_id);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'college_id' => $college->id,
            'university_id' => $college->university_id,
        ];

        // Update password only if provided
        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8']);
            $updateData['password'] = Hash::make($request->password);
        }

        $doctor->update($updateData);

        $this->logUpdate('Doctor', $doctor, "تم تعديل بيانات الدكتور: {$doctor->name}");

        return redirect()->route('admin.doctors.index')
            ->with('success', 'تم تحديث بيانات الدكتور بنجاح.');
    }

    /**
     * حذف دكتور.
     */
    public function destroy(User $doctor)
    {
        if ($doctor->role !== UserRole::DOCTOR) {
            return back()->with('error', 'لا يمكن حذف هذا المستخدم من قائمة الدكاترة.');
        }

        $this->logDelete('Doctor', $doctor, "تم حذف الدكتور: {$doctor->name}");

        $doctor->delete();
        return redirect()->route('admin.doctors.index')
            ->with('success', 'تم حذف الدكتور بنجاح.');
    }
}
