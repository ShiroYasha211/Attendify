<?php

namespace App\Http\Controllers\Student\Clinical;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use App\Models\Clinical\StudentEvaluation;

class EvaluationController extends Controller
{
    public function index()
    {
        $student = Auth::user();

        $evaluations = StudentEvaluation::with(['checklist', 'doctor'])
            ->where('student_id', $student->id)
            ->latest()
            ->get();

        // Stats
        $avgPercentage = $evaluations->avg('percentage') ?? 0;
        $totalEvals = $evaluations->count();
        $excellentCount = $evaluations->where('grade', 'excellent')->count();

        return view('student.clinical.evaluations', compact('evaluations', 'avgPercentage', 'totalEvals', 'excellentCount'));
    }

    public function show($id)
    {
        $evaluation = StudentEvaluation::with(['checklist', 'doctor', 'scores.checklistItem'])
            ->where('student_id', Auth::id())
            ->findOrFail($id);

        return view('student.clinical.evaluation_detail', compact('evaluation'));
    }
}
