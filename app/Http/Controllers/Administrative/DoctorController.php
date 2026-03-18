<?php

namespace App\Http\Controllers\Administrative;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserRole;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DoctorController extends Controller
{
    use LogsActivity;

    /**
     * Display a listing of doctors within the admin's college.
     */
    public function index()
    {
        $college = auth()->user()->college;

        if (!$college) {
            abort(403, 'حسابك غير مرتبط بكلية. يرجى التواصل مع مدير النظام.');
        }

        $doctors = User::where('college_id', $college->id)
            ->where('role', UserRole::DOCTOR)
            ->with(['subjects.term.level.major'])
            ->latest()
            ->paginate(15);

        return view('administrative.doctors.index', compact('doctors', 'college'));
    }

    /**
     * Store a newly created doctor.
     */
    public function store(Request $request)
    {
        $college = auth()->user()->college;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ], [
            'email.unique' => 'البريد الإلكتروني مسجل مسبقاً.',
        ]);

        $doctor = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => UserRole::DOCTOR,
            'college_id' => $college->id,
            'university_id' => $college->university_id,
            'status' => 'active',
        ]);

        $this->logCreate('Doctor', $doctor, "تم إضافة الدكتور: {$doctor->name} بواسطة مسؤول الكلية");

        return redirect()->route('administrative.doctors.index')
            ->with('success', 'تم إضافة الدكتور بنجاح.');
    }

    /**
     * Update the specified doctor.
     */
    public function update(Request $request, User $doctor)
    {
        $college = auth()->user()->college;

        if ($doctor->college_id !== $college->id || $doctor->role !== UserRole::DOCTOR) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $doctor->id,
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8']);
            $updateData['password'] = Hash::make($request->password);
        }

        $doctor->update($updateData);

        $this->logUpdate('Doctor', $doctor, "تم تعديل بيانات الدكتور: {$doctor->name} بواسطة مسؤول الكلية");

        return redirect()->route('administrative.doctors.index')
            ->with('success', 'تم تحديث بيانات الدكتور بنجاح.');
    }

    /**
     * Remove the specified doctor.
     */
    public function destroy(User $doctor)
    {
        $college = auth()->user()->college;

        if ($doctor->college_id !== $college->id || $doctor->role !== UserRole::DOCTOR) {
            return back()->with('error', 'لا يمكن حذف هذا المستخدم.');
        }

        $this->logDelete('Doctor', $doctor, "تم حذف الدكتور: {$doctor->name} بواسطة مسؤول الكلية");

        $doctor->delete();
        
        return redirect()->route('administrative.doctors.index')
            ->with('success', 'تم حذف الدكتور بنجاح.');
    }
}
