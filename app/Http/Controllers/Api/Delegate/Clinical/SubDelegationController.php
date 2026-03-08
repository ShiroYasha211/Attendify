<?php

namespace App\Http\Controllers\Api\Delegate\Clinical;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Clinical\ClinicalSubDelegation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SubDelegationController extends Controller
{
    /**
     * List active and recent delegations created by the current delegate.
     */
    public function index()
    {
        $delegator = Auth::user();
        
        $subDelegations = ClinicalSubDelegation::with('student:id,name,university_id,major_id,level_id')
            ->where('delegator_id', $delegator->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $subDelegations
        ]);
    }

    /**
     * Fetch students only from the same cohort as the delegator.
     */
    public function getStudents()
    {
        $delegator = Auth::user();

        $students = User::where('role', 'student')
            ->where('id', '!=', $delegator->id)
            ->where('university_id', $delegator->university_id)
            ->where('college_id', $delegator->college_id)
            ->where('major_id', $delegator->major_id)
            ->where('level_id', $delegator->level_id)
            ->get(['id', 'name', 'university_id', 'major_id', 'level_id']);

        return response()->json([
            'success' => true,
            'data' => $students
        ]);
    }

    /**
     * Create a new sub-delegation.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:users,id',
            'duration_hours' => 'required|integer|min:1|max:168',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $delegator = Auth::user();
        $student = User::findOrFail($request->student_id);

        // Security check: Student must be in the same cohort
        if (
            $student->university_id !== $delegator->university_id ||
            $student->college_id !== $delegator->college_id ||
            $student->major_id !== $delegator->major_id ||
            $student->level_id !== $delegator->level_id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'عذراً لا يمكنك منح صلاحية لطالب من دفعة أو تخصص مختلف.'
            ], 403);
        }

        $exists = ClinicalSubDelegation::where('delegator_id', $delegator->id)
            ->where('student_id', $request->student_id)
            ->where('is_revoked', false)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'هذا الطالب لديه صلاحية فعالة حالياً ولم تنتهي بعد.'
            ], 400);
        }

        $delegation = ClinicalSubDelegation::create([
            'delegator_id' => $delegator->id,
            'student_id' => $request->student_id,
            'expires_at' => now()->addHours((int) $request->duration_hours),
            'is_revoked' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم منح الصلاحية كـ (مندوب فرعي) للطالب بنجاح.',
            'data' => $delegation->load('student')
        ], 201);
    }

    /**
     * Revoke an active delegation.
     */
    public function revoke($id)
    {
        $delegation = ClinicalSubDelegation::where('delegator_id', Auth::id())
            ->findOrFail($id);

        $delegation->update(['is_revoked' => true]);

        return response()->json([
            'success' => true,
            'message' => 'تم سحب الصلاحية من الطالب وإيقافه فوراً.'
        ]);
    }
}
