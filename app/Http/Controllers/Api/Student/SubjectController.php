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

class SubjectController extends Controller
{
    /**
     * Get Student's Subjects
     */
    public function index(Request $request)
    {
        $student = $request->user();

        $subjects = Subject::where('major_id', $student->major_id)
            ->where('level_id', $student->level_id)
            ->with('doctor:id,name,avatar')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subjects
        ], 200);
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
            ->with(['doctor:id,name,avatar', 'resources'])
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

        // 3. Attendance Stats
        $attendanceRecords = Attendance::where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->get();

        $presentCount = $attendanceRecords->where('status', 'present')->count();
        $absentCount = $attendanceRecords->where('status', 'absent')->count();
        $totalLectures = $attendanceRecords->count();
        $attendancePercentage = $totalLectures > 0 ? round(($presentCount / $totalLectures) * 100) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'subject' => $subject,
                'progress' => [
                    'attendance_percentage' => $attendancePercentage,
                    'total_lectures_held' => $totalLectures,
                    'absences' => $absentCount
                ],
                'lectures' => $lectures,
                'assignments' => $assignments,
            ]
        ], 200);
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
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $status = StudentLectureStatus::firstOrCreate(
            ['lecture_id' => $lecture->id, 'student_id' => $student->id]
        );

        $status->is_studied = !$status->is_studied;
        $status->studied_at = $status->is_studied ? now() : null;
        $status->save();

        return response()->json([
            'success' => true,
            'is_studied' => $status->is_studied,
            'message' => $status->is_studied ? 'تم تحديد المحاضرة كـ "تمت المذاكرة"' : 'تم إلغاء تحديد المحاضرة',
        ]);
    }
}
