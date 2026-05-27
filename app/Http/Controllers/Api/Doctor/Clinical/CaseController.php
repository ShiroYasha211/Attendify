<?php

namespace App\Http\Controllers\Api\Doctor\Clinical;

use App\Http\Controllers\Api\Doctor\DoctorApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Clinical\ClinicalCase;
use App\Models\Clinical\TrainingCenter;
use App\Models\Clinical\ClinicalDepartment;
use App\Models\Clinical\BodySystem;

class CaseController extends DoctorApiController
{
    /** GET /api/doctor/clinical/cases */
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->integer('per_page', 100), 1), 200);

        $query = ClinicalCase::with(['trainingCenter', 'clinicalDepartment', 'bodySystem', 'doctor'])
            ->where('doctor_id', Auth::id());

        if ($request->filled('patient_name')) {
            $query->where('patient_name', 'like', "%{$request->patient_name}%");
        }
        if ($request->filled('training_center_id')) {
            $query->where('training_center_id', $request->training_center_id);
        }
        if ($request->filled('clinical_department_id')) {
            $query->where('clinical_department_id', $request->clinical_department_id);
        }
        if ($request->filled('body_system_id')) {
            $query->where('body_system_id', $request->body_system_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $cases = $query->latest()->paginate($perPage);
        $cases->getCollection()->transform(
            fn (ClinicalCase $case) => $this->serializeCase($case)
        );

        return $this->paginated($cases);
    }

    /** POST /api/doctor/clinical/cases */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'age' => 'nullable|integer|min:0|max:150',
            'gender' => 'nullable|in:male,female',
            'training_center_id' => 'required|exists:training_centers,id',
            'clinical_department_id' => 'required|exists:clinical_departments,id',
            'body_system_id' => 'required|exists:body_systems,id',
            'diagnosis_or_description' => 'nullable|string',
            'status' => 'required|in:active,discharged,transferred',
        ]);
        $validated['doctor_id'] = Auth::id();
        $validated['approval_status'] = 'approved';

        $case = ClinicalCase::create($validated);

        return $this->success(
            $this->serializeCase($case->load(['trainingCenter', 'clinicalDepartment', 'bodySystem', 'doctor'])),
            'تم إدراج الحالة بنجاح.',
            201
        );
    }

    /** GET /api/doctor/clinical/cases/{id} */
    public function show($id)
    {
        $case = ClinicalCase::with(['trainingCenter', 'clinicalDepartment', 'bodySystem', 'doctor'])
            ->where('doctor_id', Auth::id())
            ->findOrFail($id);
        return $this->success($this->serializeCase($case));
    }

    /** PUT /api/doctor/clinical/cases/{id} */
    public function update(Request $request, $id)
    {
        $case = ClinicalCase::where('doctor_id', Auth::id())->findOrFail($id);
        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'age' => 'nullable|integer|min:0|max:150',
            'gender' => 'nullable|in:male,female',
            'training_center_id' => 'required|exists:training_centers,id',
            'clinical_department_id' => 'required|exists:clinical_departments,id',
            'body_system_id' => 'required|exists:body_systems,id',
            'diagnosis_or_description' => 'nullable|string',
            'status' => 'required|in:active,discharged,transferred',
        ]);
        $case->update($validated);

        return $this->success(
            $this->serializeCase($case->load(['trainingCenter', 'clinicalDepartment', 'bodySystem', 'doctor'])),
            'تم تحديث الحالة بنجاح.'
        );
    }

    /** DELETE /api/doctor/clinical/cases/{id} */
    public function destroy($id)
    {
        $case = ClinicalCase::where('doctor_id', Auth::id())->findOrFail($id);
        $case->delete();
        return $this->success(null, 'تم مسح الحالة بنجاح.');
    }

    protected function serializeCase(ClinicalCase $case): array
    {
        return [
            'id' => $case->id,
            'patient_name' => $case->patient_name,
            'age' => $case->age,
            'gender' => $case->gender,
            'training_center_id' => $case->training_center_id,
            'clinical_department_id' => $case->clinical_department_id,
            'body_system_id' => $case->body_system_id,
            'doctor_id' => $case->doctor_id,
            'diagnosis_or_description' => $case->diagnosis_or_description,
            'status' => $case->status,
            'status_label' => $this->statusLabel($case->status),
            'approval_status' => $case->approval_status,
            'approval_status_label' => $this->approvalStatusLabel($case->approval_status),
            'training_center' => $case->trainingCenter,
            'clinical_department' => $case->clinicalDepartment,
            'body_system' => $case->bodySystem,
            'doctor' => $case->doctor,
            'created_at' => optional($case->created_at)->toISOString(),
            'updated_at' => optional($case->updated_at)->toISOString(),
        ];
    }

    protected function statusLabel(?string $status): string
    {
        return match ($status) {
            'discharged' => 'تم الخروج',
            'transferred' => 'تم التحويل',
            default => 'حالة نشطة',
        };
    }

    protected function approvalStatusLabel(?string $status): string
    {
        return match ($status) {
            'pending' => 'بانتظار الاعتماد',
            'rejected' => 'مرفوضة',
            default => 'معتمدة',
        };
    }
}
