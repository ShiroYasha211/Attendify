<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Academic\Subject;
use App\Models\Academic\Lecture;
use App\Models\Academic\Assignment;
use App\Models\Academic\StudentLectureStatus;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class SubjectController extends StudentApiController
{
    /**
     * Get Student's Subjects
     */
    public function index(Request $request)
    {
        $student = $request->user();

        $subjects = Subject::where('major_id', $student->major_id)
            ->where('level_id', $student->level_id)
            ->with(['doctor:id,name,avatar', 'term:id,name', 'semester:id,name']);

        if ($request->semester_id) {
            $subjects->where('semester_id', $request->semester_id);
        }

        return $this->success($subjects->get());
    }

    /**
     * Get Subject Details (Lectures, Stats, Assignments)
     */
    public function show($id, Request $request)
    {
        $student = $request->user();

        $subject = Subject::where('id', $id)
            ->where('major_id', $student->major_id)
            ->where('level_id', $student->level_id)
            ->with(['doctor:id,name,avatar', 'resources', 'term:id,name', 'semester:id,name'])
            ->firstOrFail();

        // 1. Fetch Lectures with Study Status
        $lectures = Lecture::where('subject_id', $subject->id)
            ->with(['statuses' => function ($query) use ($student) {
                $query->where('student_id', $student->id);
            }])
            ->ordered()
            ->get()
            ->map(function ($lecture) {
                $status = $lecture->statuses->first();
                return [
                    'id' => $lecture->id,
                    'title' => $lecture->title,
                    'description' => $lecture->description,
                    'date' => $lecture->date,
                    'is_studied' => $status ? (bool) $status->is_studied : false,
                    'studied_at' => $status ? $status->studied_at : null,
                ];
            });

        // 2. Fetch Assignments
        $assignments = Assignment::where('subject_id', $subject->id)
            ->latest()
            ->get()
            ->map(function ($assignment) use ($student) {
                $isSubmitted = $assignment->submissions()->where('student_id', $student->id)->exists();
                $isLate = now()->greaterThan($assignment->due_date);

                $status = 'available';
                if ($isSubmitted) $status = 'submitted';
                elseif ($isLate) $status = 'missing';

                return [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'description' => $assignment->description,
                    'due_date' => $assignment->due_date,
                    'marks' => $assignment->marks,
                    'status' => $status,
                ];
            });

        // 3. Attendance Stats & History
        $attendanceRecords = Attendance::where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->with('excuse')
            ->orderBy('date', 'desc')
            ->get();

        $presentCount = $attendanceRecords->where('status', 'present')->count();
        $absentCount = $attendanceRecords->where('status', 'absent')->count();
        $lateCount = $attendanceRecords->where('status', 'late')->count();
        $excusedCount = $attendanceRecords->where('status', 'excused')->count();
        $totalLecturesHeld = $attendanceRecords->count();
        
        $attendancePercentage = $totalLecturesHeld > 0 
            ? round((($presentCount + $lateCount) / $totalLecturesHeld) * 100) 
            : 0;

        // 4. Deprivation Warning Logic
        $maxAbsences = (int) Setting::get('default_max_absences', 3);
        $deprivationThreshold = (int) Setting::get('deprivation_threshold', 25);
        $excuseDeadlineDays = (int) Setting::get('excuse_deadline_days', 3);

        $absencePercent = $totalLecturesHeld > 0 ? round(($absentCount / $totalLecturesHeld) * 100) : 0;
        $warningLevel = null;

        if ($absencePercent >= $deprivationThreshold) {
            $warningLevel = 'danger'; // Deprivation zone
        } elseif ($absentCount >= $maxAbsences) {
            $warningLevel = 'danger'; // Exceeded max allowed
        } elseif ($absentCount >= ($maxAbsences - 1)) {
            $warningLevel = 'warning'; // One absence away from max
        }

        $history = $attendanceRecords->map(function ($rec) use ($excuseDeadlineDays) {
            $canSubmit = false;
            $daysSince = 0;
            if ($rec->status == 'absent' && !$rec->excuse) {
                $daysSince = now()->diffInDays($rec->date);
                $canSubmit = $daysSince <= $excuseDeadlineDays;
            }

            return [
                'id' => $rec->id,
                'date' => $rec->date,
                'status' => $rec->status,
                'is_excused' => $rec->status == 'excused' || ($rec->excuse && $rec->excuse->status == 'accepted'),
                'excuse' => $rec->excuse ? [
                    'id' => $rec->excuse->id,
                    'reason' => $rec->excuse->reason,
                    'status' => $rec->excuse->status,
                    'attachment_url' => $rec->excuse->attachment ? asset('storage/' . $rec->excuse->attachment) : null,
                    'doctor_comment' => $rec->excuse->doctor_comment,
                    'created_at' => $rec->excuse->created_at->format('Y-m-d H:i'),
                ] : null,
                'can_submit_excuse' => $canSubmit,
                'deadline_info' => [
                    'days_since_absence' => $daysSince,
                    'max_deadline_days' => $excuseDeadlineDays,
                    'is_expired' => !$canSubmit && $rec->status == 'absent' && !$rec->excuse
                ]
            ];
        });

        // 4. Fetch Grades
        $grades = \App\Models\Grade::where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->get();

        $continuousGrade = $grades->where('type', 'continuous')->first();
        $finalGrade = $grades->where('type', 'final')->first();

        // Calculate total percentage
        $totalGradePercentage = null;
        if ($continuousGrade || $finalGrade) {
            $cWeight = $continuousGrade ? ($continuousGrade->score / $continuousGrade->max_score) * 40 : 0;
            $fWeight = $finalGrade ? ($finalGrade->score / $finalGrade->max_score) * 60 : 0;
            $totalGradePercentage = round($cWeight + $fWeight, 1);
        }

        return $this->success([
            'subject' => $subject,
            'grades' => [
                'continuous' => $continuousGrade ? [
                    'score' => $continuousGrade->score,
                    'max_score' => $continuousGrade->max_score,
                ] : null,
                'final' => $finalGrade ? [
                    'score' => $finalGrade->score,
                    'max_score' => $finalGrade->max_score,
                ] : null,
                'total_percentage' => $totalGradePercentage,
            ],
            'progress' => [
                'attendance_percentage' => $attendancePercentage,
                'total_lectures_held' => $totalLecturesHeld,
                'absences' => $absentCount,
                'presents' => $presentCount,
                'lates' => $lateCount,
                'excused' => $excusedCount,
            ],
            'deprivation_info' => [
                'warning_level' => $warningLevel,
                'absence_percent' => $absencePercent,
                'max_absences_allowed' => $maxAbsences,
                'is_banned' => $warningLevel === 'danger',
            ],
            'lectures' => $lectures,
            'assignments' => $assignments,
            'attendance_history' => $history,
        ]);
    }

    /**
     * Toggle the study status of a lecture
     */
    public function toggleListen($lecture_id, Request $request)
    {
        $student = $request->user();
        $lecture = Lecture::with('subject')->findOrFail($lecture_id);

        // Security check
        if ($lecture->subject->major_id != $student->major_id || $lecture->subject->level_id != $student->level_id) {
            return $this->error('Unauthorized', 403);
        }

        $status = StudentLectureStatus::firstOrCreate(
            ['lecture_id' => $lecture->id, 'student_id' => $student->id]
        );

        $status->is_studied = !$status->is_studied;
        $status->studied_at = $status->is_studied ? now() : null;
        $status->save();

        return $this->success([
            'is_studied' => $status->is_studied,
        ], $status->is_studied ? 'تم تحديد المحاضرة كـ "تمت المذاكرة"' : 'تم إلغاء تحديد المحاضرة');
    }
}
