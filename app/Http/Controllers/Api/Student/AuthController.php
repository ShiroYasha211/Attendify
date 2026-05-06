<?php

namespace App\Http\Controllers\Api\Student;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends StudentApiController
{
    /**
     * Student login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required',
        ]);

        $login = $request->login;

        $user = User::where(function ($query) use ($login) {
            $query->where('email', $login)
                ->orWhere('student_number', $login);
        })->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->error('بيانات الدخول غير صحيحة.', 401);
        }

        if (! $user->canAccessStudentWorkspace()) {
            return $this->error('غير مصرح لك بالدخول إلى تطبيق الطالب.', 403);
        }

        if ($user->status !== 'active') {
            return $this->error('حسابك غير مفعل حاليًا. يرجى مراجعة الإدارة.', 403);
        }

        $user->load(['major', 'level', 'clinicalDelegateAssignment']);
        $token = $user->createToken('student_api_token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_practical_delegate' => $user->isPracticalDelegate(),
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
                'permissions' => $user->canAccessDelegateWorkspace()
                    ? $user->all_delegate_permissions
                    : [],
            ],
        ], 'تم تسجيل الدخول بنجاح');
    }

    /**
     * Get current student profile.
     */
    public function me(Request $request)
    {
        $user = $request->user()->load(['major', 'university', 'college', 'level', 'clinicalDelegateAssignment']);

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'student_number' => $user->student_number,
            'phone' => $user->phone,
            'gender' => $user->gender,
            'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
            'role' => $user->role,
            'status' => $user->status,
            'is_practical_delegate' => $user->isPracticalDelegate(),
            'academic_year' => $user->academic_year,
            'permissions' => $user->canAccessDelegateWorkspace()
                ? $user->all_delegate_permissions
                : [],
            'university' => $user->university->name ?? null,
            'college' => $user->college->name ?? null,
            'clinical_delegate_assignment' => $user->clinicalDelegateAssignment ? [
                'id' => $user->clinicalDelegateAssignment->id,
                'status' => $user->clinicalDelegateAssignment->status,
            ] : null,
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
     * Student logout.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->success(null, 'تم تسجيل الخروج بنجاح.');
    }

    /**
     * Change student password.
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

        if (! Hash::check($currentPassword, $user->password)) {
            return $this->error('كلمة المرور القديمة غير صحيحة.', 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return $this->success(null, 'تم تغيير كلمة المرور بنجاح.');
    }
}
