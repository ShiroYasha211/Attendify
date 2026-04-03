<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StarTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StarController extends Controller
{
    /**
     * Show student's stars dashboard with balance, transactions, and honor board.
     */
    public function index()
    {
        $student = Auth::user();

        // Star transactions history
        $transactions = StarTransaction::forUser($student->id)
            ->with('grantedBy:id,name')
            ->latest()
            ->paginate(20);

        // Honor board — top students for same major/level
        $honorBoard = User::where('major_id', $student->major_id)
            ->where('level_id', $student->level_id)
            ->where('stars_balance', '>', 0)
            ->orderByDesc('stars_balance')
            ->limit(10)
            ->select('id', 'name', 'student_number', 'stars_balance', 'total_stars_earned')
            ->get();

        return view('student.stars.index', compact('student', 'transactions', 'honorBoard'));
    }

    /**
     * Gift stars to another student.
     */
    public function gift(Request $request)
    {
        $student = Auth::user();

        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id|different:' . $student->id,
            'amount'       => 'required|integer|min:1|max:' . $student->stars_balance,
            'message'      => 'nullable|string|max:200',
        ]);

        $recipient = User::findOrFail($validated['recipient_id']);

        $success = $student->giftStars($recipient, $validated['amount'], $validated['message'] ?? null);

        if (!$success) {
            return back()->with('error', 'رصيدك من النجوم غير كافٍ.');
        }

        return back()->with('success', "تم إرسال {$validated['amount']} نجمة إلى {$recipient->name} بنجاح!");
    }
}
