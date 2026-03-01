<?php

namespace App\Http\Controllers\Api\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Delegate\DelegateApiController;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends DelegateApiController
{
    /**
     * Login Delegate and issue a token.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->error('بيانات غير صالحة', 422, $validator->errors());
        }

        $user = User::where('email', $request->email)->first();

        // Check if user exists and password is correct
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->error('البريد الإلكتروني أو كلمة المرور غير صحيحة', 401);
        }

        // Check active status
        if ($user->status !== 'active') {
            return $this->error('حسابك غير مفعل', 403);
        }

        // Allow delegates, practical delegates, or super-admins to use this app
        if (!in_array($user->role, [UserRole::DELEGATE, UserRole::PRACTICAL_DELEGATE, UserRole::ADMIN])) {
            return $this->error('غير مصرح لك بالدخول لتطبيق المندوب', 403);
        }

        // Delete old tokens (optional - if you want single device login)
        $user->tokens()->where('name', 'delegate_app')->delete();

        // Create new token
        $token = $user->createToken('delegate_app', ['delegate'])->plainTextToken;

        return $this->success([
            'user' => $user->only(['id', 'name', 'email', 'avatar', 'role', 'major_id', 'level_id']),
            'token' => $token,
        ], 'تم تسجيل الدخول بنجاح');
    }

    /**
     * Logout and revoke token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'تم تسجيل الخروج بنجاح');
    }

    /**
     * Get the authenticated delegate profile.
     */
    public function me(Request $request)
    {
        $user = $request->user()->load(['major', 'level']);

        return $this->success([
            'user' => $user,
        ], 'تم جلب البيانات بنجاح');
    }
}
