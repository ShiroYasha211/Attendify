<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student\TreeFarmProfile;
use App\Models\Student\TreeFarmRewardRequest;
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

        return view('admin.tree-farm-rewards.index', compact(
            'pendingRequests', 
            'recentRequests', 
            'students',
            'sortBy'
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
}
