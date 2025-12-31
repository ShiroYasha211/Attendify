<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRole;

class DashboardController extends Controller
{
    /**
     * عرض لوحة تحكم المدير.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->role !== UserRole::ADMIN) {
            Auth::logout();
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'ليس لديك صلاحية الوصول إلى لوحة الإدارة.']);
        }

        // 1. إحصائيات عامة
        $stats = [
            'students_count' => \App\Models\User::where('role', UserRole::STUDENT)->count(),
            'doctors_count' => \App\Models\User::where('role', UserRole::DOCTOR)->count(),
            'delegates_count' => \App\Models\User::where('role', UserRole::DELEGATE)->count(),
            'subjects_count' => \App\Models\Academic\Subject::count(),
        ];

        // 2. آخر سجلات الحضور (للمتابعة السريعة)
        $latestAttendance = \App\Models\Attendance::with(['student', 'subject', 'recorder'])
            ->latest('date')
            ->take(5)
            ->get();

        // 3. عدد الجامعات (كمثال إضافي)
        $universities_count = \App\Models\Academic\University::count();

        return view('admin.dashboard', compact('user', 'stats', 'latestAttendance', 'universities_count'));
    }
    /**
     * عرض صفحة عن المطور.
     */
    public function about()
    {
        return view('admin.about');
    }
}
