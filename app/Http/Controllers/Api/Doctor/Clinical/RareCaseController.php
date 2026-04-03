<?php

namespace App\Http\Controllers\Api\Doctor\Clinical;

use App\Http\Controllers\Api\Doctor\DoctorApiController;
use App\Models\Academic\Major;
use App\Models\Clinical\RareCase;
use App\Models\User;
use App\Notifications\Clinical\RareCaseAnnounced;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class RareCaseController extends DoctorApiController
{
    public function index()
    {
        $cases = RareCase::where('doctor_id', Auth::id())
            ->latest()
            ->paginate(15);

        return $this->success($cases, 'تم جلب الحالات النادرة بنجاح');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'hospital' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'diagnosis' => 'required|string|max:255',
            'patient_name' => 'nullable|string|max:255',
            'room_number' => 'nullable|string|max:50',
            'clinical_signs' => 'nullable|string',
            'attachment' => 'nullable|image|max:5120',
        ]);

        $data = $validated;
        unset($data['attachment']);
        $data['doctor_id'] = Auth::id();

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('clinical/rare_cases', 'public');
        }

        $rareCase = RareCase::create($data);

        $clinicalMajorIds = Major::where('has_clinical', true)->pluck('id');
        $students = User::whereIn('major_id', $clinicalMajorIds)->where('status', 'active')->get();
        Notification::send($students, new RareCaseAnnounced($rareCase));

        return $this->success($rareCase, 'تم نشر الحالة بنجاح.', 201);
    }

    public function toggleStatus($id)
    {
        $case = RareCase::where('doctor_id', Auth::id())->findOrFail($id);
        $case->update(['is_active' => !$case->is_active]);

        return $this->success([
            'id' => $case->id,
            'is_active' => (bool) $case->is_active,
        ], 'تم تحديث حالة الإعلان.');
    }
}
