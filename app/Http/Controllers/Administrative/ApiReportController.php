<?php

namespace App\Http\Controllers\Administrative;

use App\Enums\UserRole;
use App\Http\Controllers\Api\Administrative\AdministrativeApiController;
use App\Models\Academic\Major;
use App\Models\Academic\Subject;
use App\Models\Attendance;
use App\Models\User;
use App\Support\ExcuseWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiReportController extends AdministrativeApiController
{
    public function index()
    {
        $college = $this->college();

        $subjects = Subject::whereHas('major', fn ($query) => $query->where('college_id', $college->id))
            ->with(['level:id,name', 'major:id,name'])
            ->get(['id', 'name', 'level_id', 'major_id']);

        $statusCounts = Attendance::whereHas('student', fn ($query) => $query->where('college_id', $college->id))
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $distribution = ExcuseWorkflow::statusDistribution($statusCounts);

        $subjectsMaxAbsences = Subject::whereHas('major', fn ($query) => $query->where('college_id', $college->id))
            ->pluck('max_absences', 'id');

        $absences = Attendance::where('status', 'absent')
            ->whereHas('student', fn ($query) => $query->where('college_id', $college->id))
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
                'total_students' => User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])->where('college_id', $college->id)->count(),
                'total_doctors' => User::where('role', UserRole::DOCTOR)->where('college_id', $college->id)->count(),
                'total_subjects' => Subject::whereHas('major', fn ($query) => $query->where('college_id', $college->id))->count(),
                'total_attendance' => Attendance::whereHas('student', fn ($query) => $query->where('college_id', $college->id))->count(),
                'present_count' => $distribution['present'],
                'absent_count' => $distribution['absent'],
                'late_count' => $distribution['late'],
                'excused_count' => $distribution['excused_total'],
                'permitted_count' => $distribution['permitted'],
                'exempted_count' => $distribution['exempted'],
                'deprived_count' => $deprivedCount,
            ],
            'subjects' => $subjects,
            'majors' => Major::where('college_id', $college->id)->with('levels:id,name,major_id')->get(['id', 'name']),
        ]);
    }

    public function attendance(Request $request)
    {
        $college = $this->college();
        $query = Attendance::with(['student:id,name,student_number,major_id,level_id', 'subject:id,name', 'excuse'])
            ->whereHas('student', function ($query) use ($college, $request) {
                $query->where('college_id', $college->id);
                if ($request->filled('major_id')) {
                    $query->where('major_id', $request->integer('major_id'));
                }
                if ($request->filled('level_id')) {
                    $query->where('level_id', $request->integer('level_id'));
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
            $query->where(function ($query) use ($search) {
                $query->whereHas('student', fn ($subQuery) => $subQuery->where('name', 'like', "%{$search}%")->orWhere('student_number', 'like', "%{$search}%"))
                    ->orWhereHas('subject', fn ($subQuery) => $subQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $records = $query->latest('date')->paginate($request->integer('per_page', 20));

        return $this->success([
            'records' => collect($records->items())->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'date' => $attendance->date,
                    'status' => $attendance->status,
                    'status_label' => ExcuseWorkflow::attendanceStatusLabel($attendance->status),
                    'student' => $attendance->student,
                    'subject' => $attendance->subject,
                    'excuse' => $attendance->excuse ? [
                        'id' => $attendance->excuse->id,
                        'status' => $attendance->excuse->status,
                        'resolution' => $attendance->excuse->resolution,
                        'resolution_label' => ExcuseWorkflow::resolutionLabel($attendance->excuse->resolution),
                    ] : null,
                ];
            })->values(),
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
