<?php

namespace App\Http\Controllers\Api\Student\Clinical;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Clinical\RareCase;

class RareCaseController extends Controller
{
    /**
     * Get active rare cases for mobile app.
     */
    public function index()
    {
        $cases = RareCase::with('doctor:id,name')
            ->where('is_active', true)
            ->latest()
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $cases
        ]);
    }

    /**
     * Get details of a single rare case.
     */
    public function show($id)
    {
        $case = RareCase::with('doctor:id,name')->find($id);

        if (!$case || !$case->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'الحالة غير متاحة حالياً.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $case
        ]);
    }
}
