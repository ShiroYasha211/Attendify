<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\ClinicalDelegate;
use App\Models\Academic\Major;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;

class ClinicalDelegateController extends AdminApiController
{
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

        // Remove existing delegate for this major
        ClinicalDelegate::where('major_id', $request->major_id)->delete();

        $delegate = ClinicalDelegate::create([
            'major_id' => $request->major_id,
            'student_id' => $request->student_id,
        ]);

        return $this->success($delegate->load('student', 'major'), 'تم تعيين المندوب السريري بنجاح', 201);
    }

    public function destroy(ClinicalDelegate $clinicalDelegate)
    {
        $clinicalDelegate->delete();
        return $this->success(null, 'تم إزالة المندوب السريري بنجاح');
    }
}
