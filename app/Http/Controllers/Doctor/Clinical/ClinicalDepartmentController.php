<?php

namespace App\Http\Controllers\Doctor\Clinical;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClinicalDepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Clinical\ClinicalDepartment::latest();
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $departments = $query->paginate(10)->withQueryString();
        return view('doctor.clinical.departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('doctor.clinical.departments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000'
        ]);

        \App\Models\Clinical\ClinicalDepartment::create($validated);

        return redirect()->route('doctor.clinical.departments.index')->with('success', 'تم إضافة القسم الطبي بنجاح.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $department = \App\Models\Clinical\ClinicalDepartment::findOrFail($id);
        return view('doctor.clinical.departments.edit', compact('department'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000'
        ]);

        $department = \App\Models\Clinical\ClinicalDepartment::findOrFail($id);
        $department->update($validated);

        return redirect()->route('doctor.clinical.departments.index')->with('success', 'تم تعديل القسم الطبي بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $department = \App\Models\Clinical\ClinicalDepartment::findOrFail($id);

        // Prevent deletion if it has cases
        if ($department->cases()->exists()) {
            return back()->with('error', 'لا يمكن حذف هذا القسم لوجود حالات سريرية مرتبطة به.');
        }

        $department->delete();
        return redirect()->route('doctor.clinical.departments.index')->with('success', 'تم مسح القسم الطبي بنجاح.');
    }
}
