<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Academic\Schedule;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index()
    {
        $student = Auth::user();

        // Fetch all schedules for the student's group (Major + Level)
        // Assuming simplistic Major/Level assignment for now as per previous modules
        $allSchedules = Schedule::whereHas('subject', function ($q) use ($student) {
            $q->where('major_id', $student->major_id)
                ->where('level_id', $student->level_id);
        })
            ->with('subject.doctor')
            ->orderBy('start_time')
            ->get();

        // Determine Today and Tomorrow (Carbon uses 0=Sunday, 6=Saturday. adjusting if needed based on app config)
        // Let's assume standard Carbon::now()->dayOfWeek returns 0 (Sun) to 6 (Sat)
        // And our DB stores 1=Saturday, 2=Sunday ... 7=Friday (Common in Arab world systems) OR 0-6.
        // Let's check Delegate Controller or just stick to standard and map it.
        // Usually: 1=Sat, 2=Sun, 3=Mon, 4=Tue, 5=Wed, 6=Thu, 7=Fri

        $todayDayOfWeek = Carbon::now()->dayOfWeek + 2; // If Carbon Sun=0, we want Sun=2. If Carbon Sat=6, we want Sat=1? 
        // Logic: Sat(6)->1, Sun(0)->2, Mon(1)->3, Tue(2)->4, Wed(3)->5, Thu(4)->6, Fri(5)->7
        $todayDayOfWeek = (Carbon::now()->dayOfWeek == 6) ? 1 : (Carbon::now()->dayOfWeek + 2);

        $tomorrowDayOfWeek = $todayDayOfWeek == 7 ? 1 : $todayDayOfWeek + 1;

        $todayLectures = $allSchedules->where('day_of_week', $todayDayOfWeek);
        $tomorrowLectures = $allSchedules->where('day_of_week', $tomorrowDayOfWeek);

        // Group by Day for the Grid
        $weeklySchedule = $allSchedules->groupBy('day_of_week');

        return view('student.schedule.index', compact('weeklySchedule', 'todayLectures', 'tomorrowLectures'));
    }
}
