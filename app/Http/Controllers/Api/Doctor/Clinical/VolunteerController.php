<?php

namespace App\Http\Controllers\Api\Doctor\Clinical;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Clinical\ClinicalVolunteer;
use Illuminate\Support\Facades\Auth;

class VolunteerController extends Controller
{
    /**
     * Get doctor's volunteers registry.
     */
    public function index()
    {
        $volunteers = ClinicalVolunteer::where('doctor_id', Auth::id())
            ->latest()
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data'   => $volunteers
        ]);
    }

    /**
     * Add a new volunteer via API.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'contact_info'    => 'required|string|max:255',
            'phone_secondary' => 'nullable|string|max:255',
            'email'           => 'nullable|email|max:255',
            'diagnosis'       => 'required|string|max:255',
            'clinical_signs'  => 'nullable|string',
        ]);

        $validated['doctor_id'] = Auth::id();
        $validated['is_available'] = true;

        $volunteer = ClinicalVolunteer::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'تم إضافة المتطوع بنجاح.',
            'data'    => $volunteer
        ]);
    }

    /**
     * Toggle availability via API.
     */
    public function toggleStatus($id)
    {
        $volunteer = ClinicalVolunteer::where('doctor_id', Auth::id())->findOrFail($id);
        $volunteer->update(['is_available' => !$volunteer->is_available]);

        return response()->json([
            'status'       => 'success',
            'message'      => 'تم تحديث حالة التوفر.',
            'is_available' => $volunteer->is_available
        ]);
    }

    /**
     * Delete volunteer via API.
     */
    public function destroy($id)
    {
        $volunteer = ClinicalVolunteer::where('doctor_id', Auth::id())->findOrFail($id);
        $volunteer->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'تم حذف المتطوع من السجل.'
        ]);
    }
}
