<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\User;
use App\Enums\UserRole;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DoctorController extends AdminApiController
{
    use LogsActivity;

    public function index(Request $request)
    {
        $query = User::where('role', UserRole::DOCTOR)
            ->with(['university', 'college', 'major'])
            ->latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        return $this->paginated($query->paginate($request->per_page ?? 15));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'university_id' => 'nullable|exists:universities,id',
            'college_id' => 'nullable|exists:colleges,id',
        ]);

        $doctor = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => UserRole::DOCTOR,
            'university_id' => $request->university_id,
            'college_id' => $request->college_id,
        ]);

        $this->logCreate('Doctor', $doctor, "تم إضافة الدكتور: {$doctor->name}");
        return $this->success($doctor, 'تم إضافة الدكتور بنجاح', 201);
    }

    public function update(Request $request, User $doctor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $doctor->id,
        ]);

        $data = $request->only('name', 'email', 'university_id', 'college_id');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $doctor->update($data);
        $this->logUpdate('Doctor', $doctor, "تم تعديل بيانات الدكتور: {$doctor->name}");
        return $this->success($doctor->fresh(), 'تم تحديث بيانات الدكتور بنجاح');
    }

    public function destroy(User $doctor)
    {
        $this->logDelete('Doctor', $doctor, "تم حذف الدكتور: {$doctor->name}");
        $doctor->delete();
        return $this->success(null, 'تم حذف الدكتور بنجاح');
    }
}
