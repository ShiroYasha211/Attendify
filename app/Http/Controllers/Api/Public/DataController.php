<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\BaseController;
use App\Models\Academic\College;
use App\Models\Academic\Major;
use App\Models\Academic\University;
use Illuminate\Http\Request;

class DataController extends BaseController
{
    /**
     * Get all universities.
     */
    public function universities()
    {
        $universities = University::select('id', 'name')->orderBy('name')->get();

        return $this->success($universities);
    }

    /**
     * Get colleges for a specific university.
     * Supports both /public/colleges/{university} and /public/colleges?university_id=...
     */
    public function colleges(Request $request, $universityId = null)
    {
        $universityId = $universityId ?? $request->query('university_id');

        if (!$universityId) {
            return $this->error('University id is required.', 422);
        }

        $colleges = College::where('university_id', $universityId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return $this->success($colleges);
    }

    /**
     * Get majors for a specific college.
     * Supports both /public/majors/{college} and /public/majors?college_id=...
     */
    public function majors(Request $request, $collegeId = null)
    {
        $collegeId = $collegeId ?? $request->query('college_id');

        if (!$collegeId) {
            return $this->error('College id is required.', 422);
        }

        $majors = Major::where('college_id', $collegeId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return $this->success($majors);
    }

    /**
     * Get levels for a specific major.
     * Supports both /public/levels/{major} and /public/levels?major_id=...
     */
    public function levels(Request $request, $majorId = null)
    {
        $majorId = $majorId ?? $request->query('major_id');

        if (!$majorId) {
            return $this->error('Major id is required.', 422);
        }

        $major = Major::with('levels')->find($majorId);

        if (!$major) {
            return $this->error('Major not found.', 404);
        }

        $levels = $major->levels->map(function ($level) {
            return [
                'id' => $level->id,
                'name' => $level->name,
            ];
        })->values();

        return $this->success($levels);
    }

    /**
     * Get subjects for a specific level.
     * Supports both /public/subjects/{level} and /public/subjects?level_id=...
     */
    public function subjects(Request $request, $levelId = null)
    {
        $levelId = $levelId ?? $request->query('level_id');

        if (!$levelId) {
            return $this->error('Level id is required.', 422);
        }

        $subjects = \App\Models\Academic\Subject::where('level_id', $levelId)
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        return $this->success($subjects);
    }
}
