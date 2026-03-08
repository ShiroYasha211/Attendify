<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClinicalDelegate;
use App\Models\Academic\Major;
use App\Models\User;
use App\Enums\UserRole;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class ClinicalDelegateController extends Controller
{
    use LogsActivity;

    /**
     * عرض قائمة مندوبي العملي.
     */
    public function index()
    {
        // التخصصات التي تحتوي على عملي فقط
        $clinicalMajors = Major::where('has_clinical', true)
            ->with(['college.university', 'clinicalDelegate.student'])
            ->get();

        // الطلاب المتاحين — مجمّعين حسب التخصص
        $studentsByMajor = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])
            ->select('id', 'name', 'student_number', 'major_id')
            ->orderBy('name')
            ->get()
            ->groupBy('major_id');

        return view('admin.users.clinical_delegates.index', compact('clinicalMajors', 'studentsByMajor'));
    }

    /**
     * تعيين أو تحديث مندوب عملي لتخصص.
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

        // Verify major has clinical
        $major = Major::where('has_clinical', true)->findOrFail($request->major_id);

        // Verify user is a student
        $student = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])->findOrFail($request->student_id);

        // Create or update (each major has at most one clinical delegate)
        $delegate = ClinicalDelegate::updateOrCreate(
            ['major_id' => $major->id],
            ['student_id' => $student->id]
        );

        $this->logCreate('ClinicalDelegate', $delegate, "تم تعيين {$student->name} كمندوب عملي لتخصص {$major->name}");

        return redirect()->route('admin.clinical-delegates.index')
            ->with('success', "تم تعيين {$student->name} كمندوب عملي لتخصص {$major->name} بنجاح.");
    }

    /**
     * إلغاء تعيين مندوب عملي.
     */
    public function destroy(ClinicalDelegate $clinical_delegate)
    {
        $studentName = $clinical_delegate->student->name ?? 'غير معروف';
        $majorName = $clinical_delegate->major->name ?? 'غير معروف';

        $this->logDelete('ClinicalDelegate', $clinical_delegate, "تم إلغاء تعيين {$studentName} كمندوب عملي لتخصص {$majorName}");

        $clinical_delegate->delete();

        return redirect()->route('admin.clinical-delegates.index')
            ->with('success', "تم إلغاء تعيين مندوب العملي بنجاح.");
    }
}
