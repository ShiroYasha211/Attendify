<?php

namespace App\Http\Controllers\Api\Student;

use App\Models\User;
use App\Enums\UserRole;
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
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error('بيانات الدخول غير صحيحة.', 401);
        }

        // Allow students, delegates, and practical delegates to use the student app
        if (!in_array($user->role, [UserRole::STUDENT, UserRole::DELEGATE, UserRole::PRACTICAL_DELEGATE])) {
            return $this->error('غير مصرح لك بالدخول إلى تطبيق الطالب.', 403);
        }

        if ($user->status !== 'active') {
            return $this->error('حسابك موقوف حالياً. يرجى مراجعة الإدارة.', 403);
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
        ], 'تم تسجيل الدخول بنجاح');
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

        return $this->success(null, 'تم تسجيل الخروج بنجاح.');
    }

    /**
     * Change Student Password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return $this->error('كلمة المرور القديمة غير صحيحة.', 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return $this->success(null, 'تم تغيير كلمة المرور بنجاح.');
    }
}
