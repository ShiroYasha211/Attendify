<?php

namespace App\Http\Controllers\Api\Doctor;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Inquiry;
use App\Models\Academic\Subject;

class InquiryController extends DoctorApiController
{
    /** GET /api/doctor/inquiries */
    public function index(Request $request)
    {
        $subjectIds = Subject::where('doctor_id', Auth::id())->pluck('id');

        $query = Inquiry::whereIn('subject_id', $subjectIds)
            ->where('status', '!=', 'pending')
            ->with(['student:id,name,student_number', 'subject:id,name']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $inquiries = $query->latest()->paginate(15);

        $statsRaw = Inquiry::whereIn('subject_id', $subjectIds)
            ->where('status', '!=', 'pending')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'forwarded' THEN 1 ELSE 0 END) as forwarded,
                SUM(CASE WHEN status = 'answered' THEN 1 ELSE 0 END) as answered,
                SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed
            ")->first();

        return $this->success([
            'stats' => [
                'total' => $statsRaw->total ?? 0,
                'forwarded' => $statsRaw->forwarded ?? 0,
                'answered' => $statsRaw->answered ?? 0,
                'closed' => $statsRaw->closed ?? 0,
            ],
            'inquiries' => $inquiries->items(),
            'pagination' => [
                'current_page' => $inquiries->currentPage(),
                'last_page' => $inquiries->lastPage(),
                'per_page' => $inquiries->perPage(),
                'total' => $inquiries->total(),
            ],
        ]);
    }

    /** GET /api/doctor/inquiries/{id} */
    public function show($id)
    {
        $subjectIds = Subject::where('doctor_id', Auth::id())->pluck('id');
        $inquiry = Inquiry::whereIn('subject_id', $subjectIds)
            ->with(['student:id,name,student_number', 'subject:id,name'])
            ->findOrFail($id);

        return $this->success($inquiry);
    }

    /** POST /api/doctor/inquiries/{id}/answer */
    public function answer(Request $request, $id)
    {
        $request->validate(['answer' => 'required|string']);

        $subjectIds = Subject::where('doctor_id', Auth::id())->pluck('id');
        $inquiry = Inquiry::whereIn('subject_id', $subjectIds)->findOrFail($id);

        $inquiry->update([
            'answer' => $request->answer,
            'answered_by' => Auth::id(),
            'answered_at' => now(),
            'status' => 'answered',
        ]);

        return $this->success(null, 'تم الرد على الاستفسار بنجاح.');
    }
}
