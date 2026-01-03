<?php

namespace App\Http\Controllers\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use Illuminate\Support\Facades\Auth;
use App\Models\Academic\Subject;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $delegate = Auth::user();

        $subjects = Subject::with('doctor')
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->latest()
            ->get();

        return view('delegate.subjects.index', compact('subjects'));
    }
}
