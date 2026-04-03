<?php

namespace App\Support;

use App\Models\Academic\College;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Collection;

class ExcuseWorkflow
{
    public const RECEIVER_ADMINISTRATIVE = 'administrative';
    public const RECEIVER_DOCTOR = 'doctor';

    public const RESOLUTION_PERMISSION = 'excused_permission';
    public const RESOLUTION_EXEMPTION = 'excused_exemption';
    public const RESOLUTION_KEEP_ABSENT = 'keep_absent';

    public const STATUS_PERMITTED = 'permitted';
    public const STATUS_EXEMPTED = 'exempted';

    public static function receiverOptions(): array
    {
        return [
            self::RECEIVER_ADMINISTRATIVE,
            self::RECEIVER_DOCTOR,
        ];
    }

    public static function resolutionOptions(): array
    {
        return [
            self::RESOLUTION_PERMISSION,
            self::RESOLUTION_EXEMPTION,
            self::RESOLUTION_KEEP_ABSENT,
        ];
    }

    public static function editableAttendanceStatuses(): array
    {
        return [
            'present',
            'absent',
            'late',
            'excused',
            self::STATUS_PERMITTED,
            self::STATUS_EXEMPTED,
        ];
    }

    public static function nonAbsentStatuses(): array
    {
        return [
            'present',
            'late',
            'excused',
            self::STATUS_PERMITTED,
            self::STATUS_EXEMPTED,
        ];
    }

    public static function countedAsExcusedStatuses(): array
    {
        return [
            'excused',
            self::STATUS_PERMITTED,
            self::STATUS_EXEMPTED,
        ];
    }

    public static function normalizeReceiver(?string $receiver): string
    {
        return in_array($receiver, self::receiverOptions(), true)
            ? $receiver
            : self::RECEIVER_ADMINISTRATIVE;
    }

    public static function determineReceiver(Attendance $attendance, User $student): array
    {
        $college = $student->college ?? $attendance->subject?->major?->college;
        $receiverType = self::normalizeReceiver($college?->excuse_receiver);
        $receiverId = null;

        if ($receiverType === self::RECEIVER_DOCTOR && $attendance->subject?->doctor_id) {
            $receiverId = $attendance->subject->doctor_id;
        }

        return [
            'receiver_type' => $receiverType,
            'receiver_id' => $receiverId,
            'receiver_label' => self::receiverLabel($receiverType),
        ];
    }

    public static function receiverLabel(?string $receiver): string
    {
        return match (self::normalizeReceiver($receiver)) {
            self::RECEIVER_DOCTOR => 'Doctor',
            default => 'Administrative',
        };
    }

    public static function receiverDescription(?string $receiver): string
    {
        return match (self::normalizeReceiver($receiver)) {
            self::RECEIVER_DOCTOR => 'The excuse is routed to the subject doctor for the final decision.',
            default => 'The excuse is routed to the college administrative account for the final decision.',
        };
    }

    public static function pendingMessage(?string $receiver): string
    {
        return match (self::normalizeReceiver($receiver)) {
            self::RECEIVER_DOCTOR => 'The excuse has been submitted and is waiting for the subject doctor review.',
            default => 'The excuse has been submitted and is waiting for the college administrative review.',
        };
    }

    public static function canAdministrativeReview(College $college): bool
    {
        return self::normalizeReceiver($college->excuse_receiver) === self::RECEIVER_ADMINISTRATIVE;
    }

    public static function finalAttendanceStatus(?string $resolution): string
    {
        return match ($resolution) {
            self::RESOLUTION_PERMISSION => self::STATUS_PERMITTED,
            self::RESOLUTION_EXEMPTION => self::STATUS_EXEMPTED,
            self::RESOLUTION_KEEP_ABSENT => 'absent',
            default => 'excused',
        };
    }

    public static function resolutionLabel(?string $resolution): ?string
    {
        return match ($resolution) {
            self::RESOLUTION_PERMISSION => 'Permitted',
            self::RESOLUTION_EXEMPTION => 'Exempted',
            self::RESOLUTION_KEEP_ABSENT => 'Keep Absent',
            default => null,
        };
    }

    public static function attendanceStatusLabel(?string $status): string
    {
        return match ($status) {
            'present' => 'Present',
            'absent' => 'Absent',
            'late' => 'Late',
            'excused' => 'Excused',
            self::STATUS_PERMITTED => 'Permitted',
            self::STATUS_EXEMPTED => 'Exempted',
            default => (string) $status,
        };
    }

    public static function statusDistribution(Collection $counts): array
    {
        $present = (int) $counts->get('present', 0);
        $absent = (int) $counts->get('absent', 0);
        $late = (int) $counts->get('late', 0);
        $excused = (int) $counts->get('excused', 0);
        $permitted = (int) $counts->get(self::STATUS_PERMITTED, 0);
        $exempted = (int) $counts->get(self::STATUS_EXEMPTED, 0);

        return [
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'excused' => $excused,
            'permitted' => $permitted,
            'exempted' => $exempted,
            'excused_total' => $excused + $permitted + $exempted,
            'total' => $present + $absent + $late + $excused + $permitted + $exempted,
        ];
    }
}
