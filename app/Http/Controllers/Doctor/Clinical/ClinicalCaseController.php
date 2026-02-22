<?php

namespace App\Http\Controllers\Doctor\Clinical;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Clinical\ClinicalCase;
use App\Models\Clinical\TrainingCenter;
use App\Models\Clinical\ClinicalDepartment;
use App\Models\Clinical\BodySystem;
use Illuminate\Support\Facades\Auth;

class ClinicalCaseController extends Controller
{
    public function index()
    {
        $cases = ClinicalCase::with(['trainingCenter', 'clinicalDepartment', 'bodySystem'])
            ->where('doctor_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('doctor.clinical.cases.index', compact('cases'));
    }

    public function create()
    {
        $centers = TrainingCenter::all();
        $departments = ClinicalDepartment::all();
        $systems = BodySystem::all();

        return view('doctor.clinical.cases.create', compact('centers', 'departments', 'systems'));
    }

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

        ClinicalCase::create($validated);

        return redirect()->route('doctor.clinical.cases.index')
            ->with('success', 'تم إدراج الحالة المرضية بنجاح.');
    }

    public function show(string $id)
    {
        $case = ClinicalCase::with(['trainingCenter', 'clinicalDepartment', 'bodySystem'])
            ->where('doctor_id', Auth::id())
            ->findOrFail($id);

        return view('doctor.clinical.cases.show', compact('case'));
    }

    public function edit(string $id)
    {
        $case = ClinicalCase::where('doctor_id', Auth::id())->findOrFail($id);
        $centers = TrainingCenter::all();
        $departments = ClinicalDepartment::all();
        $systems = BodySystem::all();

        return view('doctor.clinical.cases.edit', compact('case', 'centers', 'departments', 'systems'));
    }

    public function update(Request $request, string $id)
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

        return redirect()->route('doctor.clinical.cases.index')
            ->with('success', 'تم تحديث بيانات الحالة بنجاح.');
    }

    public function destroy(string $id)
    {
        $case = ClinicalCase::where('doctor_id', Auth::id())->findOrFail($id);
        $case->delete();

        return redirect()->route('doctor.clinical.cases.index')
            ->with('success', 'تم المسح بنجاح.');
    }
}
