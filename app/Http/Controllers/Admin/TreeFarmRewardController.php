<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student\TreeFarmProfile;
use App\Models\Student\TreeFarmRewardRequest;
use App\Models\Student\TreeFarmSession;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TreeFarmRewardController extends Controller
{
    public function index(Request $request): View
    {
        $sortBy = $request->query('sort_by', 'focus'); // 'focus' or 'coins'

        $pendingRequests = TreeFarmRewardRequest::query()
            ->with(['user:id,name,email,student_number', 'reviewer:id,name'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(10, ['*'], 'pending_page');

        $recentRequests = TreeFarmRewardRequest::query()
            ->with(['user:id,name,email,student_number', 'reviewer:id,name'])
            ->whereIn('status', ['approved', 'rejected'])
            ->latest('reviewed_at')
            ->limit(10)
            ->get();

        // Query students who have started in the tree farm
        $studentsQuery = TreeFarmProfile::query()
            ->with(['user:id,name,email,student_number']);

        if ($sortBy === 'coins') {
            $studentsQuery->orderByDesc('coins_balance');
        } else {
            $studentsQuery->orderByDesc('total_focus_seconds');
        }

        $students = $studentsQuery->paginate(15, ['*'], 'students_page');

        // Advanced Analytics
        $subjectInsights = TreeFarmSession::query()
            ->select('subject_name', 
                DB::raw('count(*) as total_sessions'),
                DB::raw('sum(focused_seconds) as total_focused_seconds')
            )
            ->whereNotNull('subject_name')
            ->where('subject_name', '!=', '')
            ->groupBy('subject_name')
            ->orderByDesc('total_focused_seconds')
            ->get();

        $totalSessions = TreeFarmSession::count();
        $failedSessions = TreeFarmSession::where('awarded_plant_code', 'burned_tree')->count();
        $successSessions = $totalSessions - $failedSessions;

        $successRate = $totalSessions > 0 ? round(($successSessions / $totalSessions) * 100, 1) : 100;
        $failRate = $totalSessions > 0 ? round(($failedSessions / $totalSessions) * 100, 1) : 0;

        $atRiskStudents = TreeFarmSession::query()
            ->select('user_id',
                DB::raw('count(*) as total_sessions'),
                DB::raw('sum(case when awarded_plant_code = "burned_tree" then 1 else 0 end) as burned_sessions')
            )
            ->with('user:id,name,email,student_number')
            ->groupBy('user_id')
            ->having('total_sessions', '>=', 3)
            ->get()
            ->map(function ($row) {
                $row->failure_rate = $row->total_sessions > 0 ? round(($row->burned_sessions / $row->total_sessions) * 100, 1) : 0;
                return $row;
            })
            ->filter(function ($row) {
                return $row->failure_rate >= 50.0;
            })
            ->sortByDesc('failure_rate');

        $exchangeRate = Setting::get('tree_farm_exchange_rate', 25);
        $weeklyStarLimit = Setting::get('tree_farm_weekly_star_limit', 5);

        $allTreeFarmStudents = User::whereHas('treeFarmProfile')
            ->select('id', 'name', 'student_number')
            ->orderBy('name')
            ->get();

        return view('admin.tree-farm-rewards.index', compact(
            'pendingRequests', 
            'recentRequests', 
            'students',
            'sortBy',
            'subjectInsights',
            'totalSessions',
            'successSessions',
            'failedSessions',
            'successRate',
            'failRate',
            'atRiskStudents',
            'exchangeRate',
            'weeklyStarLimit',
            'allTreeFarmStudents'
        ));
    }

    public function approve(TreeFarmRewardRequest $reward): RedirectResponse
    {
        if ($reward->status !== 'pending') {
            return back()->with('error', 'تمت مراجعة هذا الطلب مسبقًا.');
        }

        DB::transaction(function () use ($reward) {
            $profile = TreeFarmProfile::where('user_id', $reward->user_id)->lockForUpdate()->first();

            if (!$profile || $profile->coins_balance < $reward->coins_amount) {
                $reward->update([
                    'status' => 'rejected',
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                    'rejection_reason' => 'رصيد العملات لم يعد كافيًا عند المراجعة.',
                ]);

                return;
            }

            $profile->decrement('coins_balance', $reward->coins_amount);
            $reward->user->addStars(
                $reward->stars_amount,
                'tree_farm_reward',
                auth()->id(),
                'مكافأة مزرعة الأشجار',
                $reward
            );

            $reward->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);
        });

        return back()->with('success', 'تم اعتماد طلب المكافأة وتحويل العملات إلى نجوم.');
    }

    public function reject(Request $request, TreeFarmRewardRequest $reward): RedirectResponse
    {
        if ($reward->status !== 'pending') {
            return back()->with('error', 'تمت مراجعة هذا الطلب مسبقًا.');
        }

        $data = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $reward->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => $data['rejection_reason'] ?? 'تم رفض الطلب من الإدارة.',
        ]);

        return back()->with('success', 'تم رفض طلب المكافأة.');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tree_farm_exchange_rate' => ['required', 'integer', 'min:1'],
            'tree_farm_weekly_star_limit' => ['required', 'integer', 'min:0'],
        ]);

        Setting::set('tree_farm_exchange_rate', $data['tree_farm_exchange_rate']);
        Setting::set('tree_farm_weekly_star_limit', $data['tree_farm_weekly_star_limit']);

        return back()->with('success', 'تم تحديث شروط تبديل المكافآت والحدود بنجاح.');
    }

    public function adjustBalance(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'adjustment_type' => ['required', 'in:coins,stars'],
            'action' => ['required', 'in:add,deduct'],
            'amount' => ['required', 'integer', 'min:1'],
            'description' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($data) {
            $user = User::findOrFail($data['user_id']);
            $profile = TreeFarmProfile::firstOrCreate(['user_id' => $user->id]);
            $amount = (int)$data['amount'];

            if ($data['adjustment_type'] === 'coins') {
                if ($data['action'] === 'add') {
                    $profile->increment('coins_balance', $amount);
                } else {
                    $profile->decrement('coins_balance', min($amount, $profile->coins_balance));
                }
            } else {
                // Adjust stars
                if ($data['action'] === 'add') {
                    $user->addStars($amount, 'admin_grant', auth()->id(), $data['description']);
                } else {
                    $user->deductStars($amount, 'admin_deduction', auth()->id(), $data['description']);
                }
            }
        });

        $adjTypeLabel = $data['adjustment_type'] === 'coins' ? 'عملات' : 'نجوم';
        $actionLabel = $data['action'] === 'add' ? 'إضافة' : 'خصم';
        return back()->with('success', "تمت عملية {$actionLabel} {$data['amount']} {$adjTypeLabel} للطالب بنجاح.");
    }
}
