<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\DesktopPairingCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DesktopPairingCodeController extends Controller
{
    public function issueForDoctor(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_unless($user && $user->canAccessDoctorWorkspace(), 403);

        return $this->issue($request, 'doctor');
    }

    public function issueForDelegate(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_unless($user, 403);

        $allowed = in_array($user->role, [UserRole::DELEGATE, UserRole::PRACTICAL_DELEGATE], true)
            || $user->hasClinicalDelegateAssignment();

        abort_unless($allowed, 403);

        return $this->issue($request, 'delegate');
    }

    protected function issue(Request $request, string $workspace): JsonResponse
    {
        $validated = $request->validate([
            'device_name' => 'nullable|string|max:120',
        ]);

        $user = $request->user();

        DesktopPairingCode::query()
            ->where('user_id', $user->id)
            ->where('workspace', $workspace)
            ->whereNull('used_at')
            ->delete();

        $pairing = DesktopPairingCode::create([
            'user_id' => $user->id,
            'workspace' => $workspace,
            'code' => DesktopPairingCode::generateUniqueCode(),
            'device_name' => $validated['device_name'] ?? null,
            'expires_at' => now()->addMinutes(10),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء رمز الربط بنجاح.',
            'data' => [
                'code' => $pairing->code,
                'display_code' => $pairing->formattedCode(),
                'workspace' => $workspace,
                'expires_at' => $pairing->expires_at?->toIso8601String(),
                'expires_in_seconds' => max(0, now()->diffInSeconds($pairing->expires_at, false)),
            ],
        ]);
    }
}
