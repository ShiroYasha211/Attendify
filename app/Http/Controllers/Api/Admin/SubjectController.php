<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Academic\Subject;
use Illuminate\Http\Request;

class SubjectController extends AdminApiController
{
    public function index(Request $request)
    {
        $query = Subject::with(['term.level.major', 'doctor']);
        if ($request->term_id) {
            $query->where('term_id', $request->term_id);
        }
        return $this->success($query->latest()->get());
    }

    public function show(Subject $subject)
    {
        return $this->success($subject->load('term.level.major.college', 'doctor'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'term_id' => 'required|exists:terms,id',
            'doctor_id' => 'nullable|exists:users,id',
            'max_absences' => 'required|integer|min:1',
            'lecture_count' => 'nullable|integer|min:0',
        ]);
        $subject = Subject::create($request->only('name', 'term_id', 'doctor_id', 'max_absences', 'lecture_count'));
        return $this->success($subject->load('term', 'doctor'), 'تم إنشاء المادة بنجاح', 201);
    }

    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'term_id' => 'required|exists:terms,id',
            'doctor_id' => 'nullable|exists:users,id',
            'max_absences' => 'required|integer|min:1',
            'lecture_count' => 'nullable|integer|min:0',
        ]);
        $subject->update($request->only('name', 'term_id', 'doctor_id', 'max_absences', 'lecture_count'));
        return $this->success($subject->fresh()->load('term', 'doctor'), 'تم تحديث المادة بنجاح');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return $this->success(null, 'تم حذف المادة بنجاح');
    }
}
