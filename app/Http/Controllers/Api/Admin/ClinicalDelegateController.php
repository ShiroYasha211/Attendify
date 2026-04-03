<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\ClinicalDelegate;
use App\Models\Academic\Major;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class ClinicalDelegateController extends AdminApiController
{
    use LogsActivity;

    public function index()
    {
        $majors = Major::where('has_clinical', true)
            ->with(['college.university'])
            ->get()
            ->map(function ($major) {
                $delegate = ClinicalDelegate::where('major_id', $major->id)
                    ->with('student')
                    ->first();
                return [
                    'major' => $major,
                    'delegate' => $delegate ? [
                        'id' => $delegate->id,
                        'student' => $delegate->student,
                        'assigned_at' => $delegate->created_at,
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

        $major = Major::findOrFail($request->major_id);
        $student = User::findOrFail($request->student_id);

        // Remove existing delegate for this major
        ClinicalDelegate::where('major_id', $request->major_id)->delete();

        $delegate = ClinicalDelegate::create([
            'major_id' => $request->major_id,
            'student_id' => $request->student_id,
        ]);

        $this->logCreate('ClinicalDelegate', $delegate, "تم تعيين {$student->name} كمندوب عملي لتخصص {$major->name} عبر الـ API");

        return $this->success($delegate->load('student', 'major'), 'تم تعيين المندوب السريري بنجاح', 201);
    }

    public function destroy(ClinicalDelegate $clinicalDelegate)
    {
        $studentName = $clinicalDelegate->student->name ?? 'غير معروف';
        $majorName = $clinicalDelegate->major->name ?? 'غير معروف';

        $this->logDelete('ClinicalDelegate', $clinicalDelegate, "تم إزالة {$studentName} كمندوب عملي لتخصص {$majorName} عبر الـ API");
        
        $clinicalDelegate->delete();
        return $this->success(null, 'تم إزالة المندوب السريري بنجاح');
    }
}
