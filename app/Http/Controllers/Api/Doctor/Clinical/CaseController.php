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
        $query = ClinicalCase::with(['trainingCenter', 'clinicalDepartment', 'bodySystem'])
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

        return $this->paginated($query->latest()->paginate(15));
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
        $case = ClinicalCase::create($validated);
        return $this->success($case->load(['trainingCenter', 'clinicalDepartment', 'bodySystem']), 'تم إدراج الحالة بنجاح.', 201);
    }

    /** GET /api/doctor/clinical/cases/{id} */
    public function show($id)
    {
        $case = ClinicalCase::with(['trainingCenter', 'clinicalDepartment', 'bodySystem'])
            ->where('doctor_id', Auth::id())->findOrFail($id);
        return $this->success($case);
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
        return $this->success($case->load(['trainingCenter', 'clinicalDepartment', 'bodySystem']), 'تم تحديث الحالة بنجاح.');
    }

    /** DELETE /api/doctor/clinical/cases/{id} */
    public function destroy($id)
    {
        $case = ClinicalCase::where('doctor_id', Auth::id())->findOrFail($id);
        $case->delete();
        return $this->success(null, 'تم مسح الحالة بنجاح.');
    }
}
