<?php

namespace App\Http\Controllers\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Academic\Subject;
use App\Enums\UserRole;

class DashboardController extends Controller
{
    public function index()
    {
        $delegate = Auth::user();

        // Scope: Same Major and Level
        $studentsCount = User::where('role', UserRole::STUDENT)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->count();

        // Subjects with Doctor info
        $subjects = Subject::where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->with('doctor')
            ->get();


        // Latest Students
        $latestStudents = User::where('role', UserRole::STUDENT)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->latest()
            ->take(5)
            ->get();

        // Latest Attendance
        $latestAttendance = \App\Models\Attendance::whereHas('student', function ($q) use ($delegate) {
            $q->where('major_id', $delegate->major_id)
                ->where('level_id', $delegate->level_id);
        })
            ->with(['student', 'subject'])
            ->latest()
            ->take(5)
            ->get();

        // Today's Lectures Count
        $todayDayOfWeek = \Carbon\Carbon::now()->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
        $todayLecturesCount = \App\Models\Academic\Schedule::where('day_of_week', $todayDayOfWeek)
            ->whereHas('subject', function ($q) use ($delegate) {
                $q->where('major_id', $delegate->major_id)
                    ->where('level_id', $delegate->level_id);
            })
            ->count();

        // Alerts Count (e.g. Absences today)
        $alertsCount = \App\Models\Attendance::where('date', date('Y-m-d'))
            ->where('status', 'absent')
            ->whereHas('student', function ($q) use ($delegate) {
                $q->where('major_id', $delegate->major_id)
                    ->where('level_id', $delegate->level_id);
            })
            ->count();

        return view('delegate.dashboard', compact('delegate', 'studentsCount', 'subjects', 'latestStudents', 'latestAttendance', 'todayLecturesCount', 'alertsCount'));
    }
}
