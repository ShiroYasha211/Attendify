<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Academic\Major;
use App\Models\ClinicalDelegate;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClinicalDelegateController extends Controller
{
    use LogsActivity;

    /**
     * Display the list of practical delegates per clinical major.
     */
    public function index()
    {
        $clinicalMajors = Major::where('has_clinical', true)
            ->with(['college.university', 'clinicalDelegate.student'])
            ->get();

        $studentsByMajor = User::where('role', UserRole::STUDENT)
            ->select('id', 'name', 'student_number', 'major_id')
            ->orderBy('name')
            ->get()
            ->groupBy('major_id');

        return view('admin.users.clinical_delegates.index', compact('clinicalMajors', 'studentsByMajor'));
    }

    /**
     * Assign or replace the practical delegate for one clinical major.
     */
    public function store(Request $request)
    {
        $request->validate([
            'major_id' => 'required|exists:majors,id',
            'student_id' => 'required|exists:users,id',
        ], [
            'major_id.required' => 'يرجى اختيار التخصص.',
            'student_id.required' => 'يرجى اختيار الطالب.',
        ]);

        $major = Major::where('has_clinical', true)->findOrFail($request->major_id);

        $student = User::where('role', UserRole::STUDENT)
            ->where('major_id', $major->id)
            ->findOrFail($request->student_id);

        $delegate = DB::transaction(function () use ($major, $student) {
            $existing = ClinicalDelegate::with('student')->where('major_id', $major->id)->first();

            if ($existing && $existing->student_id !== $student->id) {
                $previousStudent = $existing->student;

                if ($previousStudent && $previousStudent->role === UserRole::PRACTICAL_DELEGATE) {
                    $previousStudent->update(['role' => UserRole::STUDENT]);
                    $previousStudent->revokeDelegatePermissions();
                }
            }

            $student->update(['role' => UserRole::PRACTICAL_DELEGATE]);
            $student->grantAllDelegatePermissions(auth()->id());

            return ClinicalDelegate::updateOrCreate(
                ['major_id' => $major->id],
                ['student_id' => $student->id]
            );
        });

        $this->logCreate('ClinicalDelegate', $delegate, "تم تعيين {$student->name} كمندوب عملي لتخصص {$major->name}");

        return redirect()->route('admin.clinical-delegates.index')
            ->with('success', "تم تعيين {$student->name} كمندوب عملي لتخصص {$major->name} بنجاح.");
    }

    /**
     * Remove the practical delegate assignment for one major.
     */
    public function destroy(ClinicalDelegate $clinical_delegate)
    {
        $studentName = $clinical_delegate->student->name ?? 'غير معروف';
        $majorName = $clinical_delegate->major->name ?? 'غير معروف';

        DB::transaction(function () use ($clinical_delegate) {
            $student = $clinical_delegate->student;
            $clinical_delegate->delete();

            if ($student && $student->role === UserRole::PRACTICAL_DELEGATE) {
                $student->update(['role' => UserRole::STUDENT]);
                $student->revokeDelegatePermissions();
            }
        });

        $this->logDelete('ClinicalDelegate', $clinical_delegate, "تم إلغاء تعيين {$studentName} كمندوب عملي لتخصص {$majorName}");

        return redirect()->route('admin.clinical-delegates.index')
            ->with('success', 'تم إلغاء تعيين مندوب العملي بنجاح.');
    }
}
