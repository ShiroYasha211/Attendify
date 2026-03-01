<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Academic\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InquiryController extends Controller
{
    /**
     * Display inquiries forwarded to doctor.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->get('status');

        // Get subjects assigned to this doctor
        $subjectIds = Subject::where('doctor_id', $user->id)->pluck('id');

        // Get inquiries for these subjects that have been forwarded
        $query = Inquiry::whereIn('subject_id', $subjectIds)
            ->where('status', '!=', 'pending')
            ->with(['student', 'subject'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $inquiries = $query->paginate(15);

        // Stats — single optimized query (was 4 queries)
        $statsRaw = Inquiry::whereIn('subject_id', $subjectIds)
            ->where('status', '!=', 'pending')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'forwarded' THEN 1 ELSE 0 END) as forwarded,
                SUM(CASE WHEN status = 'answered' THEN 1 ELSE 0 END) as answered,
                SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed
            ")->first();

        $stats = [
            'total' => $statsRaw->total ?? 0,
            'forwarded' => $statsRaw->forwarded ?? 0,
            'answered' => $statsRaw->answered ?? 0,
            'closed' => $statsRaw->closed ?? 0,
        ];

        return view('doctor.inquiries.index', compact('inquiries', 'stats', 'status'));
    }

    /**
     * Show a specific inquiry.
     */
    public function show($id)
    {
        $user = Auth::user();
        $subjectIds = Subject::where('doctor_id', $user->id)->pluck('id');

        $inquiry = Inquiry::whereIn('subject_id', $subjectIds)
            ->with(['student', 'subject'])
            ->findOrFail($id);

        return view('doctor.inquiries.show', compact('inquiry'));
    }

    /**
     * Answer an inquiry.
     */
    public function answer(Request $request, $id)
    {
        $request->validate([
            'answer' => 'required|string',
        ]);

        $user = Auth::user();
        $subjectIds = Subject::where('doctor_id', $user->id)->pluck('id');

        $inquiry = Inquiry::whereIn('subject_id', $subjectIds)->findOrFail($id);

        $inquiry->update([
            'answer' => $request->answer,
            'answered_by' => $user->id,
            'answered_at' => now(),
            'status' => 'answered',
        ]);

        return redirect()->route('doctor.inquiries.index')
            ->with('success', 'تم الرد على الاستفسار بنجاح');
    }
}
