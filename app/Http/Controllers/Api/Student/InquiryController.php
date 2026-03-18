<?php

namespace App\Http\Controllers\Api\Student;

use Illuminate\Http\Request;
use App\Models\Inquiry;
use App\Models\Academic\Subject;

class InquiryController extends StudentApiController
{
    /**
     * Display student's inquiries.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $status = $request->get('status');

        $query = Inquiry::where('student_id', $user->id)
            ->with(['subject:id,name', 'delegate:id,name'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $inquiries = $query->paginate(15);

        $stats = [
            'total' => Inquiry::where('student_id', $user->id)->count(),
            'pending' => Inquiry::where('student_id', $user->id)->where('status', 'pending')->count(),
            'answered' => Inquiry::where('student_id', $user->id)->where('status', 'answered')->count(),
        ];

        return $this->success([
            'stats' => $stats,
            'inquiries' => $inquiries,
        ]);
    }

    /**
     * Store a new inquiry.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'question' => 'required|string',
        ]);

        $user = $request->user();

        $inquiry = Inquiry::create([
            'student_id' => $user->id,
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'question' => $request->question,
            'status' => 'pending',
        ]);

        return $this->success([
            'inquiry' => $inquiry,
        ], 'تم إرسال استفسارك بنجاح وسيتم تحويله للدكتور', 201);
    }

    /**
     * Show a specific inquiry.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $inquiry = Inquiry::where('student_id', $user->id)
            ->with(['subject', 'delegate'])
            ->findOrFail($id);

        return $this->success([
            'inquiry' => $inquiry,
        ]);
    }
}
