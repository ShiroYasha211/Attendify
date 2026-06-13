<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StarTransaction;
use App\Models\Academic\University;
use App\Models\Academic\College;
use App\Models\Academic\Major;
use App\Models\Academic\Level;
use App\Models\StudentNotification;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StarController extends Controller
{
    /**
     * Display student search and star granting interface.
     */
    public function index(Request $request)
    {
        $universities = University::all();
        $colleges     = College::all();
        $majors       = Major::all();
        $levels       = Level::all();

        $query = User::whereIn('role', ['student', 'delegate', 'practical_delegate']);

        // Apply filters
        if ($request->filled('university_id')) $query->where('university_id', $request->university_id);
        if ($request->filled('college_id'))    $query->where('college_id', $request->college_id);
        if ($request->filled('major_id'))      $query->where('major_id', $request->major_id);
        if ($request->filled('level_id'))      $query->where('level_id', $request->level_id);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('student_number', 'like', "%{$search}%");
            });
        }

        $honorQuery = clone $query;

        $students = $query->with(['university', 'college', 'major', 'level'])
            ->latest()
            ->paginate(20);

        $honorBoard = $honorQuery
            ->with(['university', 'college', 'major', 'level'])
            ->where('stars_balance', '>', 0)
            ->orderByDesc('stars_balance')
            ->orderByDesc('total_stars_earned')
            ->orderBy('name')
            ->limit(25)
            ->get();

        $honorStats = [
            'count' => $honorBoard->count(),
            'total_balance' => $honorBoard->sum('stars_balance'),
            'top_balance' => (int) ($honorBoard->first()?->stars_balance ?? 0),
        ];

        return view('admin.stars.index', compact(
            'students',
            'honorBoard',
            'honorStats',
            'universities',
            'colleges',
            'majors',
            'levels'
        ));
    }

    /**
     * Grant stars to selected students.
     */
    public function grant(Request $request, PushNotificationService $pushNotifications)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
            'amount'      => 'required|integer|min:-1000|max:1000',
            'description' => 'required|string|max:255',
        ]);

        if ((int) $request->amount === 0) {
            return back()->with('error', 'يجب أن لا تكون قيمة النجوم صفرًا.');
        }

        $admin = Auth::user();
        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($request->student_ids as $studentId) {
                $student = User::find($studentId);
                if (!$student) continue;

                if ($request->amount > 0) {
                    $student->addStars($request->amount, 'admin_grant', $admin->id, $request->description);
                } elseif ($request->amount < 0) {
                    $student->deductStars($request->amount, 'penalty', $admin->id, $request->description);
                }

                if ($request->amount !== 0) {
                    $this->notifyStudent($student, (int) $request->amount, $request->description, $admin->id, $pushNotifications);
                }

                $count++;
            }

            DB::commit();
            $actionWord = $request->amount > 0 ? 'منح' : 'خصم';
            return back()->with('success', "تم {$actionWord} " . abs((int) $request->amount) . " نجوم بنجاح لـ {$count} مستخدم.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء تنفيذ عملية النجوم: ' . $e->getMessage());
        }
    }

    private function notifyStudent(User $student, int $amount, string $description, int $adminId, PushNotificationService $pushNotifications): void
    {
        $absoluteAmount = abs($amount);
        $isGrant = $amount > 0;

        $notification = StudentNotification::create([
            'user_id' => $student->id,
            'sender_id' => $adminId,
            'type' => 'stars',
            'title' => $isGrant ? '⭐ منحة نجوم من الإدارة' : 'خصم نجوم من الإدارة',
            'message' => $isGrant
                ? "تم منحك {$absoluteAmount} نجمة من الإدارة. السبب: {$description}"
                : "تم خصم {$absoluteAmount} نجمة من رصيدك من الإدارة. السبب: {$description}",
            'data' => [
                'amount' => $amount,
                'description' => $description,
                'source' => 'admin_stars',
                'screen' => 'stars',
                'target_screen' => 'stars',
            ],
        ]);

        try {
            $pushNotifications->sendStudentNotification($notification);
        } catch (\Throwable $e) {
            Log::warning('Admin star adjustment completed but push notification failed.', [
                'notification_id' => $notification->id,
                'user_id' => $student->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
