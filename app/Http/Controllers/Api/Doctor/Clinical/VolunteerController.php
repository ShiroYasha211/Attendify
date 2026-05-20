<?php

namespace App\Http\Controllers\Api\Doctor\Clinical;

use App\Http\Controllers\Api\Doctor\DoctorApiController;
use App\Models\Clinical\ClinicalVolunteer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VolunteerController extends DoctorApiController
{
    public function index()
    {
        $volunteers = ClinicalVolunteer::where('doctor_id', Auth::id())
            ->latest()
            ->paginate(20);

        $volunteers->getCollection()->transform(
            fn (ClinicalVolunteer $volunteer) => $this->serializeVolunteer($volunteer)
        );

        return $this->success($volunteers, 'تم جلب سجل المتطوعين بنجاح');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_info' => 'required|string|max:255',
            'phone_secondary' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'diagnosis' => 'required|string|max:255',
            'clinical_signs' => 'nullable|string',
        ]);

        $validated['doctor_id'] = Auth::id();
        $validated['is_available'] = true;

        $volunteer = ClinicalVolunteer::create($validated);

        return $this->success($this->serializeVolunteer($volunteer), 'تم إضافة المتطوع بنجاح.', 201);
    }

    public function update(Request $request, $id)
    {
        $volunteer = ClinicalVolunteer::where('doctor_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_info' => 'required|string|max:255',
            'phone_secondary' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'diagnosis' => 'required|string|max:255',
            'clinical_signs' => 'nullable|string',
        ]);

        $volunteer->update($validated);

        return $this->success($this->serializeVolunteer($volunteer->fresh()), 'تم تحديث بيانات المتطوع بنجاح.');
    }

    public function toggleStatus($id)
    {
        $volunteer = ClinicalVolunteer::where('doctor_id', Auth::id())->findOrFail($id);
        $volunteer->update(['is_available' => !$volunteer->is_available]);

        return $this->success([
            'id' => $volunteer->id,
            'is_available' => (bool) $volunteer->is_available,
        ], 'تم تحديث حالة التوفر.');
    }

    public function destroy($id)
    {
        $volunteer = ClinicalVolunteer::where('doctor_id', Auth::id())->findOrFail($id);
        $volunteer->delete();

        return $this->success(null, 'تم حذف المتطوع من السجل.');
    }

    protected function serializeVolunteer(ClinicalVolunteer $volunteer): array
    {
        return [
            'id' => $volunteer->id,
            'doctor_id' => $volunteer->doctor_id,
            'name' => $volunteer->name,
            'contact_info' => $volunteer->contact_info,
            'phone_secondary' => $volunteer->phone_secondary,
            'email' => $volunteer->email,
            'diagnosis' => $volunteer->diagnosis,
            'clinical_signs' => $volunteer->clinical_signs,
            'is_available' => (bool) $volunteer->is_available,
            'created_at' => optional($volunteer->created_at)->toISOString(),
            'updated_at' => optional($volunteer->updated_at)->toISOString(),
        ];
    }
}
