<?php

namespace App\Http\Controllers\Api\Doctor\Clinical;

use App\Http\Controllers\Api\Doctor\DoctorApiController;
use Illuminate\Http\Request;
use App\Models\Clinical\ClinicalDepartment;

class DepartmentController extends DoctorApiController
{
    /** GET /api/doctor/clinical/departments */
    public function index(Request $request)
    {
        $query = ClinicalDepartment::latest();
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        return $this->paginated($query->paginate(15));
    }

    /** POST /api/doctor/clinical/departments */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);
        $dept = ClinicalDepartment::create($validated);
        return $this->success($dept, 'تم إضافة القسم السريري بنجاح.', 201);
    }

    /** PUT /api/doctor/clinical/departments/{id} */
    public function update(Request $request, $id)
    {
        $dept = ClinicalDepartment::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);
        $dept->update($validated);
        return $this->success($dept, 'تم تحديث القسم السريري بنجاح.');
    }

    /** DELETE /api/doctor/clinical/departments/{id} */
    public function destroy($id)
    {
        $dept = ClinicalDepartment::findOrFail($id);
        if ($dept->cases()->exists()) {
            return $this->error('لا يمكن حذف هذا القسم لوجود حالات سريرية مرتبطة به.', 422);
        }
        $dept->delete();
        return $this->success(null, 'تم مسح القسم السريري بنجاح.');
    }
}
