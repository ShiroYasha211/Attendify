<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends AdminApiController
{
    /**
     * POST /api/admin/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->error('بيانات الدخول غير صحيحة.', 401);
        }

        if ($user->role !== UserRole::ADMIN) {
            return $this->error('ليس لديك صلاحية الوصول إلى لوحة الإدارة.', 403);
        }

        if ($user->status !== 'active') {
            return $this->error('حسابك غير مفعّل. يرجى التواصل مع الإدارة.', 403);
        }

        // Revoke old tokens
        $user->tokens()->delete();

        $token = $user->createToken('admin-api')->plainTextToken;

        return $this->success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token,
        ], 'تم تسجيل الدخول بنجاح');
    }

    /**
     * POST /api/admin/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'تم تسجيل الخروج بنجاح');
    }

    /**
     * GET /api/admin/me
     */
    public function me(Request $request)
    {
        $user = $request->user()->load(['university', 'college', 'major']);

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'university' => $user->university?->name,
            'college' => $user->college?->name,
            'major' => $user->major?->name,
        ]);
    }
}
