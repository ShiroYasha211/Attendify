<?php

namespace App\Http\Controllers\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Services\AcademicService;
use App\Models\Academic\Major;
use App\Models\Academic\College;
use App\Models\Academic\University;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class MajorController extends Controller
{
    protected AcademicService $academicService;

    public function __construct(AcademicService $academicService)
    {
        $this->academicService = $academicService;
    }

    /**
     * عرض قائمة التخصصات.
     */
    public function index()
    {
        $universities = University::with('colleges')->get();
        $colleges = College::with('university')->get();

        // جلب التخصصات مع مستوياتها لعرض الإحصائيات
        $majors = Major::with(['college.university', 'levels'])->get();

        return view('admin.academic.majors.index', compact('majors', 'universities', 'colleges'));
    }

    /**
     * تخزين تخصص جديد وإنشاء الهيكل تلقائياً.
     */
    public function store(Request $request)
    {
        $request->validate([
            'college_id' => 'required|exists:colleges,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('majors')->where(function ($query) use ($request) {
                    return $query->where('college_id', $request->college_id);
                }),
            ],
            'levels_count' => 'required|integer|min:1|max:8',
            'terms_count' => 'required|integer|min:1|max:4',
        ], [
            'college_id.required' => 'يرجى اختيار الكلية.',
            'name.unique' => 'اسم التخصص مسجل بالفعل في هذه الكلية.',
        ]);

        // استخدام Transaction لضمان إنشاء الهيكل كاملاً أو عدمه
        DB::transaction(function () use ($request) {

            $college = College::findOrFail($request->college_id);

            // 1. Create Major
            $major = $this->academicService->createMajor($college, ['name' => $request->name]);

            // 2. Loop to create Levels (Years)
            for ($i = 1; $i <= $request->levels_count; $i++) {
                // Determine name based on locale or standard (Level 1, Level 2...)
                $levelName = "المستوى $i";

                $level = $this->academicService->createLevel($major, ['name' => $levelName]);

                // 3. Loop to create Terms for each Level
                for ($j = 1; $j <= $request->terms_count; $j++) {
                    $termName = "الترم $j";

                    // Optional: You could set default start/end dates here if needed logic existed
                    $this->academicService->createTerm($level, ['name' => $termName]);
                }
            }
        });

        return redirect()->route('admin.majors.index')
            ->with('success', 'تم إضافة التخصص وإنشاء جميع المستويات والفصول الدراسية بنجاح.');
    }


    /**
     * تحديث بيانات التخصص.
     */
    public function update(Request $request, Major $major)
    {
        $request->validate([
            'college_id' => 'required|exists:colleges,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('majors')->where(function ($query) use ($request) {
                    return $query->where('college_id', $request->college_id);
                })->ignore($major->id),
            ],
        ], [
            'college_id.required' => 'يرجى اختيار الكلية.',
            'name.unique' => 'اسم التخصص مسجل بالفعل في هذه الكلية.',
        ]);

        $this->academicService->updateMajor($major, [
            'college_id' => $request->college_id,
            'name' => $request->name
        ]);

        return redirect()->route('admin.majors.index')
            ->with('success', 'تم تحديث بيانات التخصص بنجاح.');
    }

    /**
     * حذف تخصص.
     */
    public function destroy(Major $major)
    {
        $major->delete();

        return redirect()->route('admin.majors.index')
            ->with('success', 'تم حذف التخصص بنجاح.');
    }
}
