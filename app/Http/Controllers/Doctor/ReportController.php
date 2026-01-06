<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Academic\Subject;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        $subjects = Subject::where('doctor_id', Auth::id())
            ->with(['major', 'level'])
            ->get();

        return view('doctor.reports.index', compact('subjects'));
    }
}
