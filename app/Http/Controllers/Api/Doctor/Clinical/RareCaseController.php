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
use Illuminate\Support\Facades\Storage;

class RareCaseController extends DoctorApiController
{
    public function index()
    {
        $cases = RareCase::where('doctor_id', Auth::id())
            ->latest()
            ->paginate(15);

        $cases->getCollection()->transform(
            fn (RareCase $case) => $this->serializeCase($case)
        );

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

        return $this->success($this->serializeCase($rareCase), 'تم نشر الحالة بنجاح.', 201);
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

    public function destroy($id)
    {
        $case = RareCase::where('doctor_id', Auth::id())->findOrFail($id);

        if ($case->attachment_path) {
            Storage::disk('public')->delete($case->attachment_path);
        }

        $case->delete();

        return $this->success(null, 'تم حذف إعلان الحالة النادرة.');
    }

    protected function serializeCase(RareCase $case): array
    {
        return [
            'id' => $case->id,
            'doctor_id' => $case->doctor_id,
            'patient_name' => $case->patient_name,
            'hospital' => $case->hospital,
            'department' => $case->department,
            'room_number' => $case->room_number,
            'diagnosis' => $case->diagnosis,
            'clinical_signs' => $case->clinical_signs,
            'attachment_path' => $case->attachment_path,
            'attachment_url' => $case->attachment_path ? asset('storage/' . $case->attachment_path) : null,
            'is_active' => (bool) $case->is_active,
            'created_at' => optional($case->created_at)->toISOString(),
            'updated_at' => optional($case->updated_at)->toISOString(),
        ];
    }
}
