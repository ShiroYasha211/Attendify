<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Setting;

class AttendanceController extends StudentApiController
{
    /**
     * Get Student Attendance Stats & History
     */
    public function index(Request $request)
    {
        $student = $request->user();

        // Fetch all attendance records for this student
        $attendances = Attendance::where('student_id', $student->id)
            ->with(['subject:id,name', 'excuse'])
            ->orderBy('date', 'desc')
            ->get();

        // Calculate Stats
        $totalLectures = $attendances->count();
        $presentCount = $attendances->where('status', 'present')->count();
        $absentCount = $attendances->where('status', 'absent')->count();
        $lateCount = $attendances->where('status', 'late')->count();

        // Presence Percentage
        $presencePercentage = $totalLectures > 0
            ? round((($presentCount + $lateCount) / $totalLectures) * 100, 1)
            : 0;

        // Group by Subject for warnings and subject-specific stats
        $attendanceBySubject = $attendances->groupBy('subject_id');

        // Deprivation Warning Logic
        $maxAbsences = (int) Setting::get('default_max_absences', 3);
        $deprivationThreshold = (int) Setting::get('deprivation_threshold', 25);

        $subjectWarnings = [];
        $history = [];

        foreach ($attendanceBySubject as $subjectId => $records) {
            $subjectName = $records->first()->subject->name ?? 'مجهول';
            $subjectAbsent = $records->where('status', 'absent')->count();
            $subjectTotal  = $records->count();
            $absencePercent = $subjectTotal > 0 ? round(($subjectAbsent / $subjectTotal) * 100) : 0;

            $warningLevel = null;
            if ($absencePercent >= $deprivationThreshold) {
                $warningLevel = 'danger'; // Deprivation zone
            } elseif ($subjectAbsent >= $maxAbsences) {
                $warningLevel = 'danger'; // Exceeded max allowed
            } elseif ($subjectAbsent >= ($maxAbsences - 1)) {
                $warningLevel = 'warning'; // One absence away from max
            }

            $subjectWarnings[] = [
                'subject_id' => $subjectId,
                'subject_name' => $subjectName,
                'absent_count' => $subjectAbsent,
                'total_lectures'  => $subjectTotal,
                'absence_percent' => $absencePercent,
                'warning_level' => $warningLevel,
                'max_absences_allowed' => $maxAbsences,
                'is_banned' => $warningLevel === 'danger',
            ];

            // Push to history array but simplified
            foreach ($records as $rec) {
                $history[] = [
                    'id' => $rec->id,
                    'subject_name' => $subjectName,
                    'date' => $rec->date,
                    'status' => $rec->status,
                    'is_excused' => $rec->excuse ? true : false,
                    'excuse_status' => $rec->excuse ? $rec->excuse->status : null,
                ];
            }
        }

        // Sort history by date descending again just in case traversing grouped collection messed order
        usort($history, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return $this->success([
            'overview' => [
                'total_lectures' => $totalLectures,
                'present_count' => $presentCount,
                'absent_count' => $absentCount,
                'late_count' => $lateCount,
                'presence_percentage' => $presencePercentage,
            ],
            'subjects_status' => $subjectWarnings,
            'history' => $history,
        ]);
    }
}
