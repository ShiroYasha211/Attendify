<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Api\Doctor\DoctorApiController;
use App\Models\Attendance;
use App\Support\ExcuseWorkflow;
use Illuminate\Http\Request;

class ApiAttendanceRecordController extends DoctorApiController
{
    public function update(Request $request, Attendance $attendance)
    {
        if ($attendance->subject?->doctor_id !== $request->user()->id) {
            return $this->error('Unauthorized.', 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', ExcuseWorkflow::editableAttendanceStatuses()),
        ]);

        $attendance->update([
            'status' => $validated['status'],
            'recorded_by' => $request->user()->id,
        ]);

        if ($attendance->excuse && $attendance->excuse->status === 'accepted') {
            $resolution = match ($validated['status']) {
                ExcuseWorkflow::STATUS_PERMITTED => ExcuseWorkflow::RESOLUTION_PERMISSION,
                ExcuseWorkflow::STATUS_EXEMPTED => ExcuseWorkflow::RESOLUTION_EXEMPTION,
                'absent' => ExcuseWorkflow::RESOLUTION_KEEP_ABSENT,
                default => $attendance->excuse->resolution,
            };

            $attendance->excuse->update([
                'resolution' => $resolution,
                'reviewed_by' => $request->user()->id,
            ]);
        }

        return $this->success($attendance->fresh(['student:id,name,student_number', 'subject:id,name', 'excuse']), 'Attendance record updated successfully.');
    }
}
