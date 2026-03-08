<?php

namespace App\Http\Controllers\Api\Delegate\Clinical;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Clinical\ClinicalCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ClinicalCaseController extends Controller
{
    /**
     * List all clinical cases specifically awaiting review (pending).
     */
    public function pending()
    {
        $cases = ClinicalCase::with(['trainingCenter', 'clinicalDepartment', 'bodySystem', 'doctor'])
            ->where('approval_status', 'pending')
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $cases
        ]);
    }

    /**
     * Approve a clinical case.
     */
    public function approve($id)
    {
        $case = ClinicalCase::findOrFail($id);

        if ($case->approval_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'هذه الحالة ليست في وضع الانتظار حالياً.'
            ], 400);
        }

        $case->update([
            'approval_status' => 'approved',
            'approved_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم اعتماد الحالة بنجاح وهي الآن متاحة للجميع.'
        ]);
    }

    /**
     * Reject a clinical case with a reason.
     */
    public function reject(Request $request, $id)
    {
        $case = ClinicalCase::findOrFail($id);

        if ($case->approval_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'هذه الحالة ليست في وضع الانتظار حالياً.'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $case->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $request->rejection_reason
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم رفض الحالة وإرسال الملاحظات إلى الطالب.'
        ]);
    }
}
