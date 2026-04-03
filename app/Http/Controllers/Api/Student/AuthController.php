<?php

namespace App\Http\Controllers\Api\Student;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends StudentApiController
{
    /**
     * Student Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required', // Can be email or student_number
            'password' => 'required',
        ]);

        $login = $request->login;
        $user = User::where(function ($q) use ($login) {
            $q->where('email', $login)
                ->orWhere('student_number', $login);
        })->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error('ظ¨ظٹط§ظ†ط§طھ ط§ظ„ط¯ط®ظˆظ„ ط؛ظٹط± طµط­ظٹط­ط©.', 401);
        }

        // Allow students, delegates, and practical delegates to use the student app
        if (!in_array($user->role->value, [UserRole::STUDENT->value, UserRole::DELEGATE->value, UserRole::PRACTICAL_DELEGATE->value])) {
            return $this->error('ط؛ظٹط± ظ…طµط±ط­ ظ„ظƒ ط¨ط§ظ„ط¯ط®ظˆظ„ ط¥ظ„ظ‰ طھط·ط¨ظٹظ‚ ط§ظ„ط·ط§ظ„ط¨.', 403);
        }

        if ($user->status !== 'active') {
            return $this->error('ط­ط³ط§ط¨ظƒ ظ…ظˆظ‚ظˆظپ ط­ط§ظ„ظٹط§ظ‹. ظٹط±ط¬ظ‰ ظ…ط±ط§ط¬ط¹ط© ط§ظ„ط¥ط¯ط§ط±ط©.', 403);
        }

        // Load relationships for profile data
        $user->load(['major', 'level']);

        $token = $user->createToken('student_api_token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_practical_delegate' => ($user->role === UserRole::PRACTICAL_DELEGATE),
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'major' => $user->major ? [
                    'id' => $user->major->id,
                    'name' => $user->major->name,
                    'has_clinical' => $user->major->has_clinical,
                ] : null,
                'level' => $user->level ? [
                    'id' => $user->level->id,
                    'name' => $user->level->name,
                ] : null,
                'academic_year' => $user->academic_year,
            ],
        ], 'طھظ… طھط³ط¬ظٹظ„ ط§ظ„ط¯ط®ظˆظ„ ط¨ظ†ط¬ط§ط­');
    }

    /**
     * Get Current Student Profile
     */
    public function me(Request $request)
    {
        $user = $request->user()->load(['major', 'university', 'college', 'level']);

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'gender' => $user->gender,
            'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
            'role' => $user->role,
            'is_practical_delegate' => ($user->role === UserRole::PRACTICAL_DELEGATE),
            'academic_year' => $user->academic_year,
            'university' => $user->university->name ?? null,
            'college' => $user->college->name ?? null,
            'major' => $user->major ? [
                'id' => $user->major->id,
                'name' => $user->major->name,
                'has_clinical' => $user->major->has_clinical,
            ] : null,
            'level' => $user->level ? [
                'id' => $user->level->id,
                'name' => $user->level->name,
            ] : null,
        ]);
    }

    /**
     * Student Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'طھظ… طھط³ط¬ظٹظ„ ط§ظ„ط®ط±ظˆط¬ ط¨ظ†ط¬ط§ط­.');
    }

    /**
     * Change Student Password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required_without:old_password',
            'old_password' => 'required_without:current_password',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();
        $currentPassword = $request->input('current_password', $request->input('old_password'));

        if (!Hash::check($currentPassword, $user->password)) {
            return $this->error('ظƒظ„ظ…ط© ط§ظ„ظ…ط±ظˆط± ط§ظ„ظ‚ط¯ظٹظ…ط© ط؛ظٹط± طµط­ظٹط­ط©.', 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return $this->success(null, 'طھظ… طھط؛ظٹظٹط± ظƒظ„ظ…ط© ط§ظ„ظ…ط±ظˆط± ط¨ظ†ط¬ط§ط­.');
    }
}
