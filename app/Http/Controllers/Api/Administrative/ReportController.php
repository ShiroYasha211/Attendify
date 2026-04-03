<?php

namespace App\Http\Controllers\Api\Administrative;

use App\Enums\UserRole;
use App\Models\Academic\Level;
use App\Models\Academic\Major;
use App\Models\Academic\Subject;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends AdministrativeApiController
{
    public function index()
    {
        $college = $this->college();

        $subjects = Subject::whereHas('major', fn ($q) => $q->where('college_id', $college->id))
            ->with(['level:id,name', 'major:id,name'])
            ->get(['id', 'name', 'level_id', 'major_id']);

        $totalStudents = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])
            ->where('college_id', $college->id)
            ->count();

        $totalDoctors = User::where('role', UserRole::DOCTOR)
            ->where('college_id', $college->id)
            ->count();

        $totalSubjects = Subject::whereHas('major', fn ($q) => $q->where('college_id', $college->id))->count();
        $totalAttendance = Attendance::whereHas('student', fn ($q) => $q->where('college_id', $college->id))->count();

        $statusCounts = Attendance::whereHas('student', fn ($q) => $q->where('college_id', $college->id))
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $subjectsMaxAbsences = Subject::whereHas('major', fn ($q) => $q->where('college_id', $college->id))
            ->pluck('max_absences', 'id');

        $absences = Attendance::where('status', 'absent')
            ->whereHas('student', fn ($q) => $q->where('college_id', $college->id))
            ->select('student_id', 'subject_id', DB::raw('count(*) as absent_count'))
            ->groupBy('student_id', 'subject_id')
            ->get();

        $deprivedCount = 0;
        foreach ($absences as $record) {
            $maxAbsences = $subjectsMaxAbsences->get($record->subject_id) ?? 4;
            if ($record->absent_count >= $maxAbsences) {
                $deprivedCount++;
            }
        }

        return $this->success([
            'summary' => [
                'total_students' => $totalStudents,
                'total_doctors' => $totalDoctors,
                'total_subjects' => $totalSubjects,
                'total_attendance' => $totalAttendance,
                'present_count' => $statusCounts->get('present', 0),
                'absent_count' => $statusCounts->get('absent', 0),
                'late_count' => $statusCounts->get('late', 0),
                'excused_count' => $statusCounts->get('excused', 0),
                'deprived_count' => $deprivedCount,
            ],
            'subjects' => $subjects,
            'majors' => Major::where('college_id', $college->id)->with('levels:id,name,major_id')->get(['id', 'name']),
        ]);
    }

    public function subjectReport(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $subject = Subject::whereHas('major', fn ($q) => $q->where('college_id', $this->college()->id))
            ->with(['major:id,name', 'level:id,name', 'doctor:id,name'])
            ->findOrFail($request->subject_id);

        $students = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])
            ->where('major_id', $subject->major_id)
            ->where('level_id', $subject->level_id)
            ->orderBy('name')
            ->get(['id', 'name', 'student_number']);

        $attendances = Attendance::where('subject_id', $subject->id)->get()->groupBy('student_id');
        $totalSessions = Attendance::where('subject_id', $subject->id)->distinct('date')->count();

        $reportData = $students->map(function ($student) use ($attendances, $totalSessions) {
            $records = $attendances->get($student->id, collect());
            $absent = $records->where('status', 'absent')->count();

            return [
                'student' => $student,
                'present' => $records->where('status', 'present')->count(),
                'late' => $records->where('status', 'late')->count(),
                'excused' => $records->where('status', 'excused')->count(),
                'absent' => $absent,
                'total_sessions' => $totalSessions,
                'absence_percentage' => $totalSessions > 0 ? round(($absent / $totalSessions) * 100, 1) : 0,
            ];
        })->values();

        return $this->success([
            'subject' => $subject,
            'report' => $reportData,
        ]);
    }

    public function thresholdReport(Request $request)
    {
        $request->validate([
            'level_id' => 'required|exists:levels,id',
            'threshold' => 'required|numeric|min:0|max:100',
        ]);

        $level = Level::whereHas('major', fn ($q) => $q->where('college_id', $this->college()->id))
            ->with('major:id,name')
            ->findOrFail($request->level_id);

        $subjects = Subject::whereHas('major', fn ($q) => $q->where('college_id', $this->college()->id))
            ->where('level_id', $level->id)
            ->get(['id', 'name', 'level_id', 'major_id']);

        $students = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])
            ->where('level_id', $level->id)
            ->where('college_id', $this->college()->id)
            ->get(['id', 'name', 'student_number']);

        $subjectSessions = [];
        foreach ($subjects as $subject) {
            $subjectSessions[$subject->id] = Attendance::where('subject_id', $subject->id)->distinct('date')->count();
        }

        $absences = Attendance::whereIn('subject_id', $subjects->pluck('id'))
            ->whereIn('student_id', $students->pluck('id'))
            ->where('status', 'absent')
            ->select('student_id', 'subject_id', DB::raw('count(*) as count'))
            ->groupBy('student_id', 'subject_id')
            ->get()
            ->groupBy('student_id');

        $alertData = [];
        $threshold = (float) $request->threshold;
        foreach ($students as $student) {
            $studentAbsences = $absences->get($student->id, collect())->keyBy('subject_id');
            foreach ($subjects as $subject) {
                $totalSessions = $subjectSessions[$subject->id] ?? 0;
                if ($totalSessions === 0) {
                    continue;
                }
                $absentCount = $studentAbsences->has($subject->id) ? $studentAbsences[$subject->id]->count : 0;
                $percentage = ($absentCount / $totalSessions) * 100;
                if ($percentage >= $threshold) {
                    $alertData[] = [
                        'student' => $student,
                        'subject' => $subject,
                        'absence_percentage' => round($percentage, 1),
                        'absent_count' => $absentCount,
                        'total_sessions' => $totalSessions,
                    ];
                }
            }
        }

        return $this->success([
            'level' => $level,
            'threshold' => $threshold,
            'alerts' => $alertData,
        ]);
    }

    public function levelSummary(Request $request)
    {
        $request->validate([
            'level_id' => 'required|exists:levels,id',
        ]);

        $level = Level::whereHas('major', fn ($q) => $q->where('college_id', $this->college()->id))
            ->with(['major:id,name', 'terms.subjects.doctor:id,name'])
            ->findOrFail($request->level_id);

        $students = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])
            ->where('level_id', $level->id)
            ->where('college_id', $this->college()->id)
            ->get(['id', 'name', 'student_number']);

        $delegate = User::where('role', UserRole::DELEGATE)
            ->where('level_id', $level->id)
            ->where('college_id', $this->college()->id)
            ->first(['id', 'name', 'student_number']);

        $subjects = Subject::where('level_id', $level->id)
            ->whereHas('major', fn ($q) => $q->where('college_id', $this->college()->id))
            ->with('doctor:id,name')
            ->get(['id', 'name', 'doctor_id', 'level_id', 'major_id']);

        $subjectStats = $subjects->map(function ($subject) {
            $totalRecords = Attendance::where('subject_id', $subject->id)->count();
            $presentCount = Attendance::where('subject_id', $subject->id)->where('status', 'present')->count();
            return [
                'subject' => $subject,
                'total_records' => $totalRecords,
                'attendance_rate' => $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100, 1) : 0,
            ];
        })->values();

        return $this->success([
            'level' => $level,
            'students' => $students,
            'delegate' => $delegate,
            'subject_stats' => $subjectStats,
        ]);
    }

    public function doctorPerformance()
    {
        $doctors = User::where('college_id', $this->college()->id)
            ->where('role', UserRole::DOCTOR)
            ->withCount(['qrSessions' => fn ($q) => $q->where('status', 'finalized')])
            ->get(['id', 'name', 'email']);

        foreach ($doctors as $doctor) {
            $sessions = $doctor->qrSessions()->where('status', 'finalized')->with('subject')->get();
            $totalPossible = 0;
            $totalPresent = 0;

            foreach ($sessions as $session) {
                $subject = $session->subject;
                if (!$subject) {
                    continue;
                }

                $expectedCount = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])
                    ->where('major_id', $subject->major_id)
                    ->where('level_id', $subject->level_id)
                    ->count();

                $totalPossible += $expectedCount;
                $totalPresent += Attendance::where('subject_id', $session->subject_id)
                    ->whereDate('date', $session->date)
                    ->where('status', 'present')
                    ->count();
            }

            $doctor->attendance_rate = $totalPossible > 0 ? round(($totalPresent / $totalPossible) * 100, 1) : 0;
        }

        return $this->success($doctors);
    }

    public function attendance(Request $request)
    {
        $college = $this->college();
        $query = Attendance::with(['student:id,name,student_number,major_id,level_id', 'subject:id,name'])
            ->whereHas('student', function ($q) use ($college, $request) {
                $q->where('college_id', $college->id);
                if ($request->filled('major_id')) {
                    $q->where('major_id', $request->integer('major_id'));
                }
                if ($request->filled('level_id')) {
                    $q->where('level_id', $request->integer('level_id'));
                }
            });

        if ($request->filled('date_start')) {
            $query->where('date', '>=', $request->date_start);
        }
        if ($request->filled('date_end')) {
            $query->where('date', '<=', $request->date_end);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', fn ($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('student_number', 'like', "%{$search}%"))
                    ->orWhereHas('subject', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        $records = $query->latest('date')->paginate($request->integer('per_page', 20));

        return $this->success([
            'records' => $records->items(),
            'pagination' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
            ],
            'majors' => Major::where('college_id', $college->id)->with('levels:id,name,major_id')->get(['id', 'name']),
        ]);
    }
}
