<?php

namespace App\Http\Controllers\Doctor\Clinical;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BodySystemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Clinical\BodySystem::latest();
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $systems = $query->paginate(10)->withQueryString();
        return view('doctor.clinical.body_systems.index', compact('systems'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('doctor.clinical.body_systems.create');
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

        \App\Models\Clinical\BodySystem::create($validated);

        return redirect()->route('doctor.clinical.body-systems.index')->with('success', 'تم إضافة الجهاز المرضي بنجاح.');
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
        $bodySystem = \App\Models\Clinical\BodySystem::findOrFail($id);
        return view('doctor.clinical.body_systems.edit', compact('bodySystem'));
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

        $bodySystem = \App\Models\Clinical\BodySystem::findOrFail($id);
        $bodySystem->update($validated);

        return redirect()->route('doctor.clinical.body-systems.index')->with('success', 'تم تعديل الجهاز المرضي بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bodySystem = \App\Models\Clinical\BodySystem::findOrFail($id);

        // Prevent deletion if it has cases
        if ($bodySystem->cases()->exists()) {
            return back()->with('error', 'لا يمكن حذف هذا الجهاز لوجود حالات سريرية مرتبطة به.');
        }

        $bodySystem->delete();
        return redirect()->route('doctor.clinical.body-systems.index')->with('success', 'تم مسح الجهاز المرضي بنجاح.');
    }
}
