<?php

namespace App\Http\Controllers\Api\Administrative;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DoctorController extends AdministrativeApiController
{
    public function index(Request $request)
    {
        $query = User::where('college_id', $this->college()->id)
            ->where('role', UserRole::DOCTOR)
            ->with(['subjects:id,name,doctor_id,major_id,level_id']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $doctors = $query->latest()->paginate($request->integer('per_page', 15));

        return $this->success([
            'doctors' => $doctors->items(),
            'pagination' => [
                'current_page' => $doctors->currentPage(),
                'last_page' => $doctors->lastPage(),
                'per_page' => $doctors->perPage(),
                'total' => $doctors->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $college = $this->college();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $doctor = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => UserRole::DOCTOR,
            'college_id' => $college->id,
            'university_id' => $college->university_id,
            'status' => 'active',
        ]);

        return $this->success($doctor, 'تم إضافة الدكتور بنجاح', 201);
    }

    public function show(User $doctor)
    {
        $this->ensureCollegeUser($doctor, ['doctor']);
        return $this->success($doctor->load(['subjects:id,name,doctor_id,major_id,level_id']));
    }

    public function update(Request $request, User $doctor)
    {
        $this->ensureCollegeUser($doctor, ['doctor']);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $doctor->id,
            'password' => 'nullable|string|min:8',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $doctor->update($updateData);

        return $this->success($doctor->fresh(), 'تم تحديث بيانات الدكتور بنجاح');
    }

    public function destroy(User $doctor)
    {
        $this->ensureCollegeUser($doctor, ['doctor']);
        $doctor->delete();
        return $this->success(null, 'تم حذف الدكتور بنجاح');
    }
}
