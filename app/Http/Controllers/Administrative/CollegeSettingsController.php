<?php

namespace App\Http\Controllers\Administrative;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CollegeSettingsController extends Controller
{
    public function edit()
    {
        $college = auth()->user()->college;

        return view('administrative.settings-clean', compact('college'));
    }

    public function update(Request $request)
    {
        $college = auth()->user()->college;

        $validated = $request->validate([
            'absence_deprivation_percentage' => 'required|integer|min:1|max:100',
            'excuses_deadline_days' => 'required|integer|min:1|max:30',
            'excuse_receiver' => 'required|in:administrative,doctor',
            'qr_rotation_seconds' => 'required|integer|min:5|max:300',
        ]);

        $college->update($validated);

        return back()->with('success', 'College settings updated successfully.');
    }
}
