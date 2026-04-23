<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Models\Academic\Major;
use App\Models\ClinicalDelegate;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClinicalDelegateController extends AdminApiController
{
    use LogsActivity;

    public function index()
    {
        $majors = Major::where('has_clinical', true)
            ->with(['college.university', 'clinicalDelegate.student'])
            ->get()
            ->map(function (Major $major) {
                return [
                    'major' => $major,
                    'delegate' => $major->clinicalDelegate ? [
                        'id' => $major->clinicalDelegate->id,
                        'student' => $major->clinicalDelegate->student,
                        'assigned_at' => $major->clinicalDelegate->created_at,
                    ] : null,
                ];
            });

        return $this->success($majors);
    }

    public function store(Request $request)
    {
        $request->validate([
            'major_id' => 'required|exists:majors,id',
            'student_id' => 'required|exists:users,id',
        ]);

        $major = Major::where('has_clinical', true)->findOrFail($request->major_id);

        $student = User::where('role', UserRole::STUDENT)
            ->where('major_id', $major->id)
            ->findOrFail($request->student_id);

        $delegate = DB::transaction(function () use ($major, $student) {
            $existing = ClinicalDelegate::with('student')->where('major_id', $major->id)->first();

            if ($existing && $existing->student_id !== $student->id) {
                $previousStudent = $existing->student;

                if ($previousStudent && $previousStudent->role === UserRole::PRACTICAL_DELEGATE) {
                    $previousStudent->update(['role' => UserRole::STUDENT]);
                    $previousStudent->revokeDelegatePermissions();
                }
            }

            $student->update(['role' => UserRole::PRACTICAL_DELEGATE]);
            $student->grantAllDelegatePermissions($request->user()->id);

            return ClinicalDelegate::updateOrCreate(
                ['major_id' => $major->id],
                ['student_id' => $student->id]
            );
        });

        $this->logCreate('ClinicalDelegate', $delegate, "تم تعيين {$student->name} كمندوب عملي لتخصص {$major->name} عبر الـ API");

        return $this->success($delegate->load('student', 'major'), 'تم تعيين المندوب العملي بنجاح', 201);
    }

    public function destroy(ClinicalDelegate $clinicalDelegate)
    {
        $studentName = $clinicalDelegate->student->name ?? 'غير معروف';
        $majorName = $clinicalDelegate->major->name ?? 'غير معروف';

        DB::transaction(function () use ($clinicalDelegate) {
            $student = $clinicalDelegate->student;
            $clinicalDelegate->delete();

            if ($student && $student->role === UserRole::PRACTICAL_DELEGATE) {
                $student->update(['role' => UserRole::STUDENT]);
                $student->revokeDelegatePermissions();
            }
        });

        $this->logDelete('ClinicalDelegate', $clinicalDelegate, "تم إزالة {$studentName} كمندوب عملي لتخصص {$majorName} عبر الـ API");

        return $this->success(null, 'تم إزالة المندوب العملي بنجاح');
    }
}
