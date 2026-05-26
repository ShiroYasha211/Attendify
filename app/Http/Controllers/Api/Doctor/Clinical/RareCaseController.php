<?php

namespace App\Http\Controllers\Api\Doctor\Clinical;

use App\Http\Controllers\Api\Doctor\DoctorApiController;
use App\Models\Clinical\RareCase;
use App\Models\StudentNotification;
use App\Models\User;
use App\Enums\UserRole;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        $recipientsCount = $this->notifyCollegeStudents($rareCase);

        return $this->success([
            'case' => $this->serializeCase($rareCase),
            'recipients_count' => $recipientsCount,
        ], 'تم نشر الحالة وإرسال الإشعار للطلاب بنجاح.', 201);
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

    protected function notifyCollegeStudents(RareCase $rareCase): int
    {
        $doctor = Auth::user();
        $collegeId = $doctor->college_id;

        if (! $collegeId) {
            return 0;
        }

        $students = User::query()
            ->where('college_id', $collegeId)
            ->where('status', 'active')
            ->whereIn('role', [
                UserRole::STUDENT->value,
                UserRole::DELEGATE->value,
                UserRole::PRACTICAL_DELEGATE->value,
            ])
            ->get(['id']);

        if ($students->isEmpty()) {
            return 0;
        }

        $batchId = 'rare-case-' . $rareCase->id . '-' . Str::uuid();
        $now = now();
        $title = 'إعلان حالة نادرة';
        $message = 'تم نشر حالة نادرة جديدة: ' . $rareCase->diagnosis;

        $rows = $students->map(fn (User $student) => [
            'user_id' => $student->id,
            'college_id' => $collegeId,
            'sender_id' => $doctor->id,
            'batch_id' => $batchId,
            'type' => 'rare_case',
            'title' => $title,
            'message' => $message,
            'attachment_path' => $rareCase->attachment_path,
            'attachment_name' => $rareCase->attachment_path ? basename($rareCase->attachment_path) : null,
            'data' => json_encode([
                'rare_case_id' => $rareCase->id,
                'doctor_id' => $doctor->id,
                'screen' => 'rare_case',
                'target_screen' => 'rare_case',
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        foreach (array_chunk($rows, 500) as $chunk) {
            StudentNotification::insert($chunk);
        }

        try {
            app(PushNotificationService::class)->sendBatchByBatchId($batchId);
        } catch (\Throwable $exception) {
            Log::warning('Rare case push notification failed.', [
                'rare_case_id' => $rareCase->id,
                'batch_id' => $batchId,
                'error' => $exception->getMessage(),
            ]);
        }

        return $students->count();
    }
}
