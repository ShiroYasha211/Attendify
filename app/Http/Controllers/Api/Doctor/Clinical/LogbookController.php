<?php

namespace App\Http\Controllers\Api\Doctor\Clinical;

use App\Http\Controllers\Api\Doctor\DoctorApiController;
use App\Models\Academic\Subject;
use App\Models\Clinical\StudentDailyLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogbookController extends DoctorApiController
{
    public function processQr(Request $request)
    {
        $request->validate(['qr_token' => 'required|string']);

        $log = StudentDailyLog::with([
            'student',
            'trainingCenter',
            'department',
            'doctor',
            'activities.bodySystem',
            'activities.confirmedBy',
        ])->where('qr_token', $request->qr_token)
            ->where('doctor_id', Auth::id())
            ->first();

        if (!$log) {
            return $this->error('Invalid QR token.', 404);
        }
        if ($log->status === 'confirmed') {
            return $this->error('This log is already fully confirmed.', 422);
        }
        if ($log->status === 'rejected') {
            return $this->error('This log has already been rejected.', 422);
        }
        if ($log->status === 'pending' && $log->isExpired()) {
            return $this->error('This QR code has expired.', 422);
        }

        return $this->success($this->serializeLog($log), 'Daily log loaded successfully.');
    }

    public function confirm(Request $request)
    {
        $validated = $request->validate([
            'log_id' => 'required|exists:student_daily_logs,id',
            'action' => 'required|in:confirm,reject',
            'doctor_notes' => 'nullable|string|max:1000',
            'confirmations' => 'nullable|array',
            'confirmations.history.confirm' => 'nullable|boolean',
            'confirmations.history.diagnosis' => 'nullable|string|max:1000',
            'confirmations.exam.confirm' => 'nullable|boolean',
            'confirmations.exam.diagnosis' => 'nullable|string|max:1000',
            'confirmations.round.confirm' => 'nullable|boolean',
            'confirmations.round.diagnosis' => 'nullable|string|max:1000',
        ]);

        $log = StudentDailyLog::with('activities')
            ->where('doctor_id', Auth::id())
            ->findOrFail($validated['log_id']);

        if (!in_array($log->status, ['pending', 'partially_confirmed'], true)) {
            return $this->error('This log has already been processed.', 422);
        }

        if ($validated['action'] === 'reject') {
            $log->activities()->update([
                'is_confirmed' => false,
                'diagnosis' => null,
                'confirmed_by' => null,
                'confirmed_at' => null,
            ]);

            $log->update([
                'status' => 'rejected',
                'confirmed_by' => Auth::id(),
                'confirmed_at' => now(),
                'doctor_notes' => $validated['doctor_notes'] ?? null,
            ]);

            return $this->success([
                'status' => $log->status,
            ], 'The daily log was rejected.');
        }

        $groups = $log->groupedActivities();
        $confirmations = $validated['confirmations'] ?? [];
        $selectedAny = false;

        DB::transaction(function () use ($log, $groups, $confirmations, &$selectedAny, $validated) {
            foreach ($groups as $key => $group) {
                $selection = (bool) data_get($confirmations, $key . '.confirm', false);
                if (!$selection) {
                    continue;
                }

                $selectedAny = true;
                $diagnosis = trim((string) data_get($confirmations, $key . '.diagnosis', ''));

                $log->activities()
                    ->where('activity_type', $group['activity_type'])
                    ->update([
                        'is_confirmed' => true,
                        'diagnosis' => $diagnosis !== '' ? $diagnosis : null,
                        'confirmed_by' => Auth::id(),
                        'confirmed_at' => now(),
                    ]);
            }

            if (!$selectedAny) {
                return;
            }

            $log->refresh();
            $log->syncApprovalStatus();
            $log->update([
                'confirmed_by' => Auth::id(),
                'confirmed_at' => now(),
                'doctor_notes' => $validated['doctor_notes'] ?? null,
            ]);
        });

        if (!$selectedAny) {
            return $this->error('Select at least one section to confirm.', 422);
        }

        $log->refresh();
        return $this->success([
            'status' => $log->status,
            'status_label' => $log->status_label,
            'log' => $this->serializeLog($log->load(['student', 'trainingCenter', 'department', 'doctor', 'activities.bodySystem', 'activities.confirmedBy'])),
        ], $log->status === 'confirmed' ? 'All sections were confirmed.' : 'The log was partially confirmed.');
    }

    public function records(Request $request)
    {
        $query = StudentDailyLog::with([
            'student:id,name,student_number',
            'trainingCenter',
            'department',
            'confirmedBy:id,name',
            'activities.bodySystem',
            'activities.confirmedBy:id,name',
        ])->where('doctor_id', Auth::id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('log_date', $request->date);
        }

        return $this->paginated($query->latest()->paginate(20));
    }

    public function manualAttendance(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'training_center_id' => 'required|exists:training_centers,id',
            'department_id' => 'required|exists:clinical_departments,id',
            'doctor_notes' => 'nullable|string|max:1000',
        ]);

        $doctorScopes = Subject::where('doctor_id', Auth::id())
            ->select('major_id', 'level_id')
            ->distinct()
            ->get();

        $studentQuery = User::whereKey($request->student_id)
            ->whereIn('role', ['student', 'delegate', 'practical_delegate']);

        if ($doctorScopes->isEmpty()) {
            $studentQuery->whereRaw('1 = 0');
        } else {
            $studentQuery->where(function ($query) use ($doctorScopes) {
                foreach ($doctorScopes as $scope) {
                    $query->orWhere(function ($inner) use ($scope) {
                        $inner->where('major_id', $scope->major_id)
                            ->where('level_id', $scope->level_id);
                    });
                }
            });
        }

        $studentQuery->firstOrFail();

        $log = StudentDailyLog::create([
            'student_id' => $request->student_id,
            'training_center_id' => $request->training_center_id,
            'department_id' => $request->department_id,
            'doctor_id' => Auth::id(),
            'qr_token' => StudentDailyLog::generateToken(),
            'status' => 'confirmed',
            'confirmed_by' => Auth::id(),
            'confirmed_at' => now(),
            'doctor_notes' => $request->doctor_notes ?: 'Manual attendance',
            'log_date' => now()->toDateString(),
            'log_time' => now()->toTimeString(),
        ]);

        return $this->success($log, 'Manual attendance recorded successfully.', 201);
    }

    protected function serializeLog(StudentDailyLog $log): array
    {
        $groups = [];
        foreach ($log->groupedActivities() as $key => $group) {
            $items = $group['items'];
            $groups[] = [
                'key' => $key,
                'label' => $group['label'],
                'count' => $items->count(),
                'confirmed' => $items->every(fn ($item) => (bool) $item->is_confirmed),
                'diagnosis' => $items->pluck('diagnosis')->filter()->first(),
                'items' => $items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'label' => $item->activity_type === 'round'
                            ? ($item->case_name ?: 'Round case')
                            : ($item->bodySystem->name ?? '-'),
                        'is_confirmed' => (bool) $item->is_confirmed,
                        'diagnosis' => $item->diagnosis,
                        'confirmed_at' => optional($item->confirmed_at)?->toIso8601String(),
                    ];
                })->values(),
            ];
        }

        return [
            'log_id' => $log->id,
            'student_name' => $log->student?->name,
            'student_number' => $log->student?->student_number,
            'training_center' => $log->trainingCenter?->name,
            'department' => $log->department?->name,
            'doctor_name' => $log->doctor?->name,
            'log_date' => $log->log_date?->format('Y-m-d'),
            'log_time' => $log->log_time,
            'status' => $log->status,
            'status_label' => $log->status_label,
            'doctor_notes' => $log->doctor_notes,
            'groups' => $groups,
        ];
    }
}
