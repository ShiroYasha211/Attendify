<?php

namespace App\Http\Controllers\Api\Administrative;

use App\Http\Controllers\Api\BaseController;
use App\Models\Academic\College;
use App\Models\Academic\Level;
use App\Models\Academic\Major;
use App\Models\Academic\Subject;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class AdministrativeApiController extends BaseController
{
    protected function administrative(): User
    {
        /** @var User $user */
        $user = request()->user();
        return $user;
    }

    protected function college(): College
    {
        $college = $this->administrative()->college;

        if (!$college) {
            $this->forbid('ط­ط³ط§ط¨ظƒ ط؛ظٹط± ظ…ط±طھط¨ط· ط¨ظƒظ„ظٹط©.');
        }

        return $college;
    }

    protected function collegeMajorIds()
    {
        return Major::where('college_id', $this->college()->id)->pluck('id');
    }

    protected function ensureCollegeUser(User $user, array $roles = []): void
    {
        if ($user->college_id !== $this->college()->id) {
            $this->forbid('ط§ظ„ظ…ط³طھط®ط¯ظ… ظ„ط§ ظٹظ†طھظ…ظٹ ط¥ظ„ظ‰ ظƒظ„ظٹطھظƒ.');
        }

        if ($roles !== []) {
            $roleValue = $user->role?->value ?? $user->role;
            if (!in_array($roleValue, $roles, true)) {
                $this->forbid('ط§ظ„ظ…ط³طھط®ط¯ظ… ظ„ط§ ظٹظ†طھظ…ظٹ ط¥ظ„ظ‰ ط§ظ„ظپط¦ط© ط§ظ„ظ…ط·ظ„ظˆط¨ط©.', 422);
            }
        }
    }

    protected function ensureCollegeMajor(Major $major): void
    {
        if ($major->college_id !== $this->college()->id) {
            $this->forbid('ط§ظ„طھط®طµطµ ظ„ط§ ظٹظ†طھظ…ظٹ ط¥ظ„ظ‰ ظƒظ„ظٹطھظƒ.');
        }
    }

    protected function ensureCollegeLevel(Level $level): void
    {
        if ($level->major?->college_id !== $this->college()->id) {
            $this->forbid('ط§ظ„ظ…ط³طھظˆظ‰ ظ„ط§ ظٹظ†طھظ…ظٹ ط¥ظ„ظ‰ ظƒظ„ظٹطھظƒ.');
        }
    }

    protected function ensureCollegeSubject(Subject $subject): void
    {
        if (!in_array($subject->major_id, $this->collegeMajorIds()->all(), true)) {
            $this->forbid('ط§ظ„ظ…ط§ط¯ط© ظ„ط§ طھظ†طھظ…ظٹ ط¥ظ„ظ‰ ظƒظ„ظٹطھظƒ.');
        }
    }

    protected function forbid(string $message = 'ط؛ظٹط± ظ…طµط±ط­ ظ„ظƒ ط¨ط§ظ„ظˆطµظˆظ„ ط¥ظ„ظ‰ ظ‡ط°ط§ ط§ظ„ظ…ظˆط±ط¯.', int $code = 403): never
    {
        throw new HttpResponseException($this->error($message, $code));
    }

    protected function paginated(LengthAwarePaginator $paginator, string $message = 'طھظ… ط¬ظ„ط¨ ط§ظ„ط¨ظٹط§ظ†ط§طھ ط¨ظ†ط¬ط§ط­'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
