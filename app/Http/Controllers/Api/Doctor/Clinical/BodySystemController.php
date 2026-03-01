<?php

namespace App\Http\Controllers\Api\Doctor\Clinical;

use App\Http\Controllers\Api\Doctor\DoctorApiController;
use Illuminate\Http\Request;
use App\Models\Clinical\BodySystem;

class BodySystemController extends DoctorApiController
{
    /** GET /api/doctor/clinical/body-systems */
    public function index(Request $request)
    {
        $query = BodySystem::latest();
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        return $this->paginated($query->paginate(15));
    }

    /** POST /api/doctor/clinical/body-systems */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);
        $system = BodySystem::create($validated);
        return $this->success($system, 'تم إضافة الجهاز المرضي بنجاح.', 201);
    }

    /** PUT /api/doctor/clinical/body-systems/{id} */
    public function update(Request $request, $id)
    {
        $system = BodySystem::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);
        $system->update($validated);
        return $this->success($system, 'تم تحديث الجهاز المرضي بنجاح.');
    }

    /** DELETE /api/doctor/clinical/body-systems/{id} */
    public function destroy($id)
    {
        $system = BodySystem::findOrFail($id);
        if ($system->cases()->exists()) {
            return $this->error('لا يمكن حذف هذا الجهاز لوجود حالات سريرية مرتبطة به.', 422);
        }
        $system->delete();
        return $this->success(null, 'تم مسح الجهاز المرضي بنجاح.');
    }
}
