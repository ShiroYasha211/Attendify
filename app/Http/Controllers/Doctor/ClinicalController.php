<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Clinical\ClinicalCase;
use App\Models\Clinical\TrainingCenter;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;

class ClinicalController extends Controller
{
    public function index()
    {
        $doctor = Auth::user();

        // 1. Total Cases
        $totalCases = ClinicalCase::where('doctor_id', $doctor->id)->count();
        $activeCases = ClinicalCase::where('doctor_id', $doctor->id)
            ->where('status', 'active')
            ->count();

        // 2. Training Centers with Doctor's Active Cases count
        $centers = TrainingCenter::withCount(['cases' => function ($query) use ($doctor) {
            $query->where('doctor_id', $doctor->id)
                ->where('status', 'active');
        }])->get();

        return view('doctor.clinical.index', compact(
            'totalCases',
            'activeCases',
            'centers'
        ));
    }
}
