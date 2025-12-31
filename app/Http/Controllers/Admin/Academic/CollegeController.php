<?php

namespace App\Http\Controllers\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Services\AcademicService;
use App\Models\Academic\College;
use App\Models\Academic\University;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CollegeController extends Controller
{
    protected AcademicService $academicService;

    public function __construct(AcademicService $academicService)
    {
        $this->academicService = $academicService;
    }

    /**
     * عرض قائمة الكليات.
     */
    public function index()
    {
        // نحتاج الجامعات من أجل القائمة المنسدلة في نموذج الإضافة
        $universities = $this->academicService->getAllUniversities();

        // جلب الكليات مع بيانات الجامعة التابعة لها
        $colleges = College::with('university')->get();

        return view('admin.academic.colleges.index', compact('colleges', 'universities'));
    }

    /**
     * تخزين كلية جديدة.
     */
    public function store(Request $request)
    {
        $request->validate([
            'university_id' => 'required|exists:universities,id',
            'name' => [
                'required',
                'string',
                'max:255',
                // التأكد من عدم تكرار اسم الكلية داخل نفس الجامعة
                Rule::unique('colleges')->where(function ($query) use ($request) {
                    return $query->where('university_id', $request->university_id);
                }),
            ],
        ], [
            'university_id.required' => 'يرجى اختيار الجامعة.',
            'name.unique' => 'اسم الكلية مسجل بالفعل في هذه الجامعة.',
        ]);

        $university = University::findOrFail($request->university_id);

        $this->academicService->createCollege($university, ['name' => $request->name]);

        return redirect()->route('admin.colleges.index')
            ->with('success', 'تم إضافة الكلية بنجاح.');
    }

    /**
     * تحديث بيانات الكلية.
     */
    public function update(Request $request, College $college)
    {
        $request->validate([
            'university_id' => 'required|exists:universities,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('colleges')->where(function ($query) use ($request) {
                    return $query->where('university_id', $request->university_id);
                })->ignore($college->id),
            ],
        ], [
            'university_id.required' => 'يرجى اختيار الجامعة.',
            'name.unique' => 'اسم الكلية مسجل بالفعل في هذه الجامعة.',
        ]);

        $this->academicService->updateCollege($college, [
            'university_id' => $request->university_id,
            'name' => $request->name
        ]);

        return redirect()->route('admin.colleges.index')
            ->with('success', 'تم تحديث الكلية بنجاح.');
    }

    /**
     * حذف كلية.
     */
    public function destroy(College $college)
    {
        $this->academicService->deleteCollege($college);

        return redirect()->route('admin.colleges.index')
            ->with('success', 'تم حذف الكلية بنجاح.');
    }
}
