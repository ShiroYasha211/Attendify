<?php

namespace App\Http\Controllers\Api\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Delegate\DelegateApiController;
use App\Models\Academic\Subject;

class SubjectController extends DelegateApiController
{
    /**
     * Display a listing of subjects for the delegate's batch.
     */
    public function index(Request $request)
    {
        $delegate = $request->user();

        $subjects = Subject::where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->with(['doctor:id,name', 'term:id,name', 'semester:id,name']);

        if ($request->semester_id) {
            $subjects->where('semester_id', $request->semester_id);
        }

        return $this->success($subjects->orderBy('name')->get(), 'تم جلب المواد بنجاح');
    }

    /**
     * Display the specified subject with its resources & assignments.
     */
    public function show(Request $request, string $id)
    {
        $delegate = $request->user();

        // Ensure subject belongs to delegate scope
        $subject = Subject::where('id', $id)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->with(['doctor:id,name', 'term:id,name', 'semester:id,name', 'resources', 'grades']) // Using grades for assignments as in web view
            ->first();

        if (!$subject) {
            return $this->error('المادة غير موجودة أو غير مصرح لك بالوصول', 404);
        }

        return $this->success($subject, 'تم جلب بيانات المادة بنجاح');
    }
}
