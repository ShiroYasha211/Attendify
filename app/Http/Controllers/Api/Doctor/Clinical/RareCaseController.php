<?php

namespace App\Http\Controllers\Api\Doctor\Clinical;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Clinical\RareCase;
use App\Models\Academic\Major;
use App\Models\User;
use App\Notifications\Clinical\RareCaseAnnounced;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class RareCaseController extends Controller
{
    /**
     * Get doctor's own rare case announcements.
     */
    public function index()
    {
        $cases = RareCase::where('doctor_id', Auth::id())
            ->latest()
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $cases
        ]);
    }

    /**
     * Store a new rare case via API.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'hospital'     => 'required|string|max:255',
            'department'   => 'required|string|max:255',
            'diagnosis'    => 'required|string|max:255',
            'patient_name' => 'nullable|string|max:255',
            'room_number'  => 'nullable|string|max:50',
            'clinical_signs'=> 'nullable|string',
            'attachment'   => 'nullable|image|max:5120',
        ]);

        $data = $validated;
        unset($data['attachment']);
        $data['doctor_id'] = Auth::id();

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('clinical/rare_cases', 'public');
        }

        $rareCase = RareCase::create($data);

        // Notify students
        $clinicalMajorIds = Major::where('has_clinical', true)->pluck('id');
        $students = User::whereIn('major_id', $clinicalMajorIds)->where('status', 'active')->get();
        Notification::send($students, new RareCaseAnnounced($rareCase));

        return response()->json([
            'status' => 'success',
            'message' => 'تم نشر الحالة بنجاح.',
            'data' => $rareCase
        ]);
    }

    /**
     * Toggle status.
     */
    public function toggleStatus($id)
    {
        $case = RareCase::findOrFail($id);
        if ($case->doctor_id !== Auth::id()) abort(403);

        $case->update(['is_active' => !$case->is_active]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث حالة الإعلان.',
            'is_active' => $case->is_active
        ]);
    }
}
