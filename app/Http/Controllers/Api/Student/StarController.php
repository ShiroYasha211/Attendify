<?php

namespace App\Http\Controllers\Api\Student;

use App\Models\StarTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StarController extends StudentApiController
{
    /**
     * Get student's stars dashboard (Balance, Transactions, Honor Board).
     */
    public function index()
    {
        $student = Auth::user();

        // 1. Transaction History
        $transactions = StarTransaction::where('user_id', $student->id)
            ->with('grantedBy:id,name')
            ->latest()
            ->paginate(15);

        // 2. Honor Board (Top 10 in same major/level)
        $honorBoard = User::where('major_id', $student->major_id)
            ->where('level_id', $student->level_id)
            ->where('stars_balance', '>', 0)
            ->orderByDesc('stars_balance')
            ->limit(10)
            ->select('id', 'name', 'student_number', 'stars_balance', 'total_stars_earned')
            ->get();

        return $this->success([
            'balance' => [
                'current' => (int)$student->stars_balance,
                'total_earned' => (int)$student->total_stars_earned,
            ],
            'transactions' => $transactions,
            'honor_board' => $honorBoard,
        ], 'تم جلب بيانات النجوم بنجاح');
    }

    /**
     * Search for students to gift stars to.
     */
    public function searchUsers(Request $request)
    {
        $query = $request->query('query');
        if (strlen($query) < 2) {
            return $this->success([], 'بانتظار البحث...');
        }

        $users = User::where('id', '!=', Auth::id())
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('student_number', 'like', "%{$query}%");
            })
            ->limit(15)
            ->get(['id', 'name', 'student_number']);

        return $this->success($users, 'نتائج البحث');
    }

    /**
     * Gift stars to another student.
     */
    public function gift(Request $request)
    {
        $student = Auth::user();

        $request->validate([
            'recipient_id' => 'required|exists:users,id|different:' . $student->id,
            'amount'       => 'required|integer|min:1|max:' . (int)$student->stars_balance,
            'message'      => 'nullable|string|max:200',
        ]);

        $recipient = User::findOrFail($request->recipient_id);

        $success = $student->giftStars($recipient, $request->amount, $request->message);

        if (!$success) {
            return $this->error('رصيدك من النجوم غير كافٍ', 400);
        }

        return $this->success([
            'new_balance' => (int)$student->fresh()->stars_balance
        ], "تم إرسال {$request->amount} نجمة إلى {$recipient->name} بنجاح!");
    }
}
