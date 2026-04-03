<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\BaseController;
use App\Models\Academic\University;
use App\Models\Academic\College;
use App\Models\Academic\Major;
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
     */
    public function colleges($universityId)
    {
        $colleges = College::where('university_id', $universityId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
        return $this->success($colleges);
    }

    /**
     * Get majors for a specific college.
     */
    public function majors($collegeId)
    {
        $majors = Major::where('college_id', $collegeId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
        return $this->success($majors);
    }

    /**
     * Get levels for a specific major.
     */
    public function levels($majorId)
    {
        $major = Major::with('levels')->find($majorId);
        if (!$major) {
            return $this->error('التخصص غير موجود', 404);
        }
        $levels = $major->levels->map(function($level) {
            return ['id' => $level->id, 'name' => $level->name];
        });
        return $this->success($levels);
    }

    /**
     * Get subjects for a specific level.
     */
    public function subjects($levelId)
    {
        $subjects = \App\Models\Academic\Subject::where('level_id', $levelId)
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();
        return $this->success($subjects);
    }
}
