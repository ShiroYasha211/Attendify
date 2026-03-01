<?php

namespace App\Http\Controllers\Api\Doctor;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Academic\Subject;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Excuse;
use App\Models\Inquiry;
use App\Models\Grade;
use App\Enums\UserRole;
use Carbon\Carbon;

class DashboardController extends DoctorApiController
{
    /** GET /api/doctor/dashboard */
    public function index()
    {
        $doctor = Auth::user();
        $subjects = Subject::where('doctor_id', $doctor->id)->with(['major', 'level'])->get();
        $subjectIds = $subjects->pluck('id');

        // Students count
        $studentsCount = User::where('role', UserRole::STUDENT)
            ->where(function ($query) use ($subjects) {
                foreach ($subjects as $subject) {
                    $query->orWhere(function ($q) use ($subject) {
                        $q->where('major_id', $subject->major_id)->where('level_id', $subject->level_id);
                    });
                }
            })->count();

        // Pending excuses
        $pendingExcuses = Excuse::whereHas('attendance', fn($q) => $q->whereIn('subject_id', $subjectIds))
            ->where('status', 'pending')->count();

        // Pending inquiries
        $pendingInquiries = Inquiry::whereIn('subject_id', $subjectIds)->where('status', 'forwarded')->count();

        // Unread messages
        $unreadMessages = \App\Models\DoctorMessage::whereHas('conversation', fn($q) => $q->where('doctor_id', $doctor->id))
            ->where('sender_id', '!=', $doctor->id)->whereNull('read_at')->count();

        // Grades count
        $gradesCount = Grade::whereIn('subject_id', $subjectIds)->distinct('student_id')->count('student_id');

        // Attendance chart (7 days)
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $dailyStats = Attendance::whereIn('subject_id', $subjectIds)
            ->whereBetween('date', [$startDate, Carbon::now()->endOfDay()])
            ->selectRaw('DATE(date) as day, status, COUNT(*) as count')
            ->groupBy('day', 'status')->get()->groupBy('day');

        $chart = ['labels' => [], 'present' => [], 'absent' => []];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $chart['labels'][] = $date->format('m/d');
            $dayData = $dailyStats->get($dateKey, collect());
            $chart['present'][] = $dayData->where('status', 'present')->sum('count');
            $chart['absent'][] = $dayData->where('status', 'absent')->sum('count');
        }

        // Subjects with stats
        $subjectsData = $subjects->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'major' => $s->major?->name,
            'level' => $s->level?->name,
        ]);

        return $this->success([
            'subjects_count' => $subjects->count(),
            'students_count' => $studentsCount,
            'pending_excuses' => $pendingExcuses,
            'pending_inquiries' => $pendingInquiries,
            'unread_messages' => $unreadMessages,
            'grades_entered' => $gradesCount,
            'attendance_chart' => $chart,
            'subjects' => $subjectsData,
        ]);
    }
}
