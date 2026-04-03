<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Models\Academic\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StarController extends DoctorApiController
{
    protected function eligibleStudentsQuery()
    {
        $doctor = Auth::user();
        $subjectScopes = Subject::where('doctor_id', $doctor->id)
            ->get(['major_id', 'level_id']);

        if ($subjectScopes->isEmpty()) {
            return User::whereRaw('1 = 0');
        }

        return User::whereIn('role', ['student', 'delegate', 'practical_delegate'])
            ->where(function ($query) use ($subjectScopes) {
                foreach ($subjectScopes as $scope) {
                    $query->orWhere(function ($inner) use ($scope) {
                        $inner->where('major_id', $scope->major_id)
                            ->where('level_id', $scope->level_id);
                    });
                }
            });
    }

    public function index(Request $request)
    {
        $subjects = Subject::where('doctor_id', Auth::id())
            ->with('level:id,name')
            ->get(['id', 'name', 'level_id', 'major_id']);

        $students = $this->eligibleStudentsQuery()
            ->when($request->filled('subject_id'), function ($query) use ($request, $subjects) {
                $subject = $subjects->firstWhere('id', (int) $request->subject_id);
                if ($subject) {
                    $query->where('major_id', $subject->major_id)
                        ->where('level_id', $subject->level_id);
                }
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('student_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($request->integer('per_page', 25), ['id', 'name', 'student_number', 'stars_balance', 'major_id', 'level_id']);

        return $this->success([
            'subjects' => $subjects,
            'students' => $students,
        ]);
    }

    public function grant(Request $request)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
            'amount' => 'required|integer|min:1|max:100',
            'description' => 'nullable|string|max:200',
        ]);

        $eligibleIds = $this->eligibleStudentsQuery()
            ->whereIn('id', $validated['student_ids'])
            ->pluck('id')
            ->all();

        if (count($eligibleIds) !== count($validated['student_ids'])) {
            return $this->error('أحد الطلاب المحددين خارج نطاق المواد التابعة لك.', 403);
        }

        $doctor = $request->user();
        $count = 0;

        foreach ($eligibleIds as $studentId) {
            $student = User::find($studentId);
            if (!$student) {
                continue;
            }

            $student->addStars(
                $validated['amount'],
                'doctor_gift',
                $doctor->id,
                $validated['description'] ?? "هدية نجوم من د. {$doctor->name}"
            );
            $count++;
        }

        return $this->success([
            'granted_count' => $count,
            'amount' => $validated['amount'],
        ], "تم منح {$validated['amount']} نجمة لـ {$count} طالب بنجاح.");
    }
}
