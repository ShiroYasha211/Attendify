<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Academic\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InquiryController extends Controller
{
    /**
     * Display student's inquiries.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->get('status');

        $query = Inquiry::where('student_id', $user->id)
            ->with(['subject', 'delegate'])
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

        return view('student.inquiries.index', compact('inquiries', 'stats', 'status'));
    }

    /**
     * Show inquiry creation form.
     */
    public function create()
    {
        $user = Auth::user();

        $subjects = Subject::where('major_id', $user->major_id)
            ->where('level_id', $user->level_id)
            ->get();

        return view('student.inquiries.create', compact('subjects'));
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

        $user = Auth::user();

        Inquiry::create([
            'student_id' => $user->id,
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'question' => $request->question,
            'status' => 'pending',
        ]);

        return redirect()->route('student.inquiries.index')
            ->with('success', 'تم إرسال استفسارك بنجاح وسيتم تحويله للدكتور');
    }

    /**
     * Show a specific inquiry.
     */
    public function show($id)
    {
        $user = Auth::user();

        $inquiry = Inquiry::where('student_id', $user->id)
            ->with(['subject', 'delegate'])
            ->findOrFail($id);

        return view('student.inquiries.show', compact('inquiry'));
    }
}
