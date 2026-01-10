<?php

namespace App\Http\Controllers\Delegate;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InquiryController extends Controller
{
    /**
     * Display all inquiries from students.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->get('status');

        // Get inquiries from students in delegate's major/level
        $query = Inquiry::whereHas('student', function ($q) use ($user) {
            $q->where('major_id', $user->major_id)
                ->where('level_id', $user->level_id);
        })
            ->with(['student', 'subject'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $inquiries = $query->paginate(15);

        $stats = [
            'total' => Inquiry::whereHas('student', function ($q) use ($user) {
                $q->where('major_id', $user->major_id)->where('level_id', $user->level_id);
            })->count(),
            'pending' => Inquiry::whereHas('student', function ($q) use ($user) {
                $q->where('major_id', $user->major_id)->where('level_id', $user->level_id);
            })->where('status', 'pending')->count(),
            'forwarded' => Inquiry::whereHas('student', function ($q) use ($user) {
                $q->where('major_id', $user->major_id)->where('level_id', $user->level_id);
            })->where('status', 'forwarded')->count(),
            'answered' => Inquiry::whereHas('student', function ($q) use ($user) {
                $q->where('major_id', $user->major_id)->where('level_id', $user->level_id);
            })->where('status', 'answered')->count(),
        ];

        return view('delegate.inquiries.index', compact('inquiries', 'stats', 'status'));
    }

    /**
     * Show a specific inquiry.
     */
    public function show($id)
    {
        $user = Auth::user();

        $inquiry = Inquiry::whereHas('student', function ($q) use ($user) {
            $q->where('major_id', $user->major_id)
                ->where('level_id', $user->level_id);
        })
            ->with(['student', 'subject'])
            ->findOrFail($id);

        return view('delegate.inquiries.show', compact('inquiry'));
    }

    /**
     * Forward inquiry to doctor (change status).
     */
    public function forward($id)
    {
        $user = Auth::user();

        $inquiry = Inquiry::whereHas('student', function ($q) use ($user) {
            $q->where('major_id', $user->major_id)
                ->where('level_id', $user->level_id);
        })->findOrFail($id);

        $inquiry->update([
            'status' => 'forwarded',
            'delegate_id' => $user->id,
        ]);

        return back()->with('success', 'تم تحويل الاستفسار للدكتور');
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

        $inquiry = Inquiry::whereHas('student', function ($q) use ($user) {
            $q->where('major_id', $user->major_id)
                ->where('level_id', $user->level_id);
        })->findOrFail($id);

        $inquiry->update([
            'answer' => $request->answer,
            'status' => 'answered',
            'delegate_id' => $user->id,
            'answered_at' => now(),
        ]);

        return redirect()->route('delegate.inquiries.show', $id)
            ->with('success', 'تم إرسال الرد بنجاح');
    }

    /**
     * Close an inquiry.
     */
    public function close($id)
    {
        $user = Auth::user();

        $inquiry = Inquiry::whereHas('student', function ($q) use ($user) {
            $q->where('major_id', $user->major_id)
                ->where('level_id', $user->level_id);
        })->findOrFail($id);

        $inquiry->update(['status' => 'closed']);

        return back()->with('success', 'تم إغلاق الاستفسار');
    }
}
