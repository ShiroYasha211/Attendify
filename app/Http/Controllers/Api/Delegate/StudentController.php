<?php

namespace App\Http\Controllers\Api\Delegate;

use App\Http\Controllers\Api\Delegate\DelegateApiController;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StudentController extends DelegateApiController
{
    /**
     * Display a listing of students in the delegate's batch.
     */
    public function index(Request $request)
    {
        $delegate = $request->user();

        $students = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->with(['university:id,name', 'college:id,name', 'major:id,name', 'level:id,name'])
            ->latest()
            ->paginate(15);

        return $this->success($students, 'تم جلب قائمة الطلاب بنجاح');
    }

    /**
     * Store a newly created student in the delegate's batch.
     */
    public function store(Request $request)
    {
        $delegate = $request->user();

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
            'status' => 'active', // Since it's added by the delegate of the batch
        ]);

        return $this->success($user, 'تم إضافة الطالب بنجاح', 201);
    }

    /**
     * Update the specified student.
     */
    public function update(Request $request, User $student)
    {
        $delegate = $request->user();

        // Enforce Scope
        if ($student->major_id != $delegate->major_id || $student->level_id != $delegate->level_id) {
            return $this->error('غير مصرح لك بتعديل هذا الطالب', 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($student->id)],
            'student_number' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($student->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'status' => ['nullable', Rule::in(['active', 'inactive', 'pending'])],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'student_number' => $validated['student_number'],
        ];

        if (isset($validated['status'])) {
            $data['status'] = $validated['status'];
        }

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $student->update($data);

        return $this->success($student, 'تم تحديث بيانات الطالب بنجاح');
    }

    /**
     * Remove the specified student.
     */
    public function destroy(Request $request, User $student)
    {
        $delegate = $request->user();

        // Enforce Scope
        if ($student->major_id != $delegate->major_id || $student->level_id != $delegate->level_id) {
            return $this->error('غير مصرح لك بحذف هذا الطالب', 403);
        }

        $student->delete();

        return $this->success(null, 'تم حذف الطالب بنجاح');
    }

    /**
     * Import Students from CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $delegate = $request->user();
        $file = $request->file('csv_file');

        $successCount = 0;
        $errors = [];

        if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
            $header = fgetcsv($handle, 1000, ",");
            
            // Clean BOM
            if (isset($header[0])) {
                $header[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header[0]);
            }

            $rowNumber = 1;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $rowNumber++;

                if (empty(array_filter($data))) continue;

                if (count($data) < 3) {
                    $errors[] = "السطر $rowNumber: أعمدة ناقصة.";
                    continue;
                }

                $name = trim($data[0]);
                $email = trim($data[1]);
                $studentNumber = trim($data[2]);

                if (empty($name) || empty($email) || empty($studentNumber)) {
                    $errors[] = "السطر $rowNumber: بيانات ناقصة ($name).";
                    continue;
                }

                if (User::where('email', $email)->exists() || User::where('student_number', $studentNumber)->exists()) {
                    $errors[] = "السطر $rowNumber: البريد أو رقم القيد مسجل مسبقاً ($email).";
                    continue;
                }

                try {
                    User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make($studentNumber),
                        'role' => UserRole::STUDENT,
                        'student_number' => $studentNumber,
                        'university_id' => $delegate->university_id,
                        'college_id' => $delegate->college_id,
                        'major_id' => $delegate->major_id,
                        'level_id' => $delegate->level_id,
                        'status' => 'active',
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "السطر $rowNumber: خطأ ({$e->getMessage()}).";
                }
            }
            fclose($handle);
        }

        return $this->success([
            'success_count' => $successCount,
            'errors' => $errors,
        ], "اكتمل الاستيراد. تم إضافة $successCount طالب.");
    }
}
