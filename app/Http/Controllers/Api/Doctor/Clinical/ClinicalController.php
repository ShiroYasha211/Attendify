<?php

namespace App\Http\Controllers\Api\Doctor\Clinical;

use App\Http\Controllers\Api\Doctor\DoctorApiController;
use Illuminate\Support\Facades\Auth;
use App\Models\Clinical\ClinicalCase;

class ClinicalController extends DoctorApiController
{
    /** GET /api/doctor/clinical/overview */
    public function index()
    {
        $doctorId = Auth::id();

        return $this->success([
            'total_cases' => ClinicalCase::where('doctor_id', $doctorId)->count(),
            'active_cases' => ClinicalCase::where('doctor_id', $doctorId)->where('status', 'active')->count(),
            'discharged_cases' => ClinicalCase::where('doctor_id', $doctorId)->where('status', 'discharged')->count(),
        ]);
    }
}
