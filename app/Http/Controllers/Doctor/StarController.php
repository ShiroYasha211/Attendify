<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Academic\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StarController extends Controller
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

    /**
     * Show star granting form for the doctor.
     */
    public function index()
    {
        $doctor = Auth::user();
        $subjects = Subject::where('doctor_id', $doctor->id)->with('level')->get();

        $students = $this->eligibleStudentsQuery()
            ->orderBy('name')
            ->get(['id', 'name', 'student_number', 'stars_balance', 'major_id', 'level_id']);

        return view('doctor.stars.index', compact('subjects', 'students'));
    }

    /**
     * Grant stars to students.
     */
    public function grant(Request $request)
    {
        $doctor = Auth::user();

        $validated = $request->validate([
            'student_ids'  => 'required|array|min:1',
            'student_ids.*'=> 'exists:users,id',
            'amount'       => 'required|integer|min:1|max:100',
            'description'  => 'nullable|string|max:200',
        ]);

        $eligibleIds = $this->eligibleStudentsQuery()
            ->whereIn('id', $validated['student_ids'])
            ->pluck('id')
            ->all();

        if (count($eligibleIds) !== count($validated['student_ids'])) {
            return back()->with('error', 'أحد الطلاب المحددين خارج نطاق المواد التابعة لك.');
        }

        $count = 0;
        foreach ($eligibleIds as $studentId) {
            $student = User::find($studentId);
            if ($student) {
                $student->addStars(
                    $validated['amount'],
                    'doctor_gift',
                    $doctor->id,
                    $validated['description'] ?? "هدية نجوم من د. {$doctor->name}"
                );
                $count++;
            }
        }

        return back()->with('success', "تم منح {$validated['amount']} نجمة لـ {$count} طالب بنجاح!");
    }
}
