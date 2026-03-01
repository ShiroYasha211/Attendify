<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
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
            return response()->json([
                'success' => false,
                'message' => 'بيانات الدخول غير صحيحة.',
            ], 401);
        }

        if ($user->role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالدخول إلى تطبيق الطالب.',
            ], 403);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'حسابك موقوف حالياً. يرجى مراجعة الإدارة.',
            ], 403);
        }

        // Load major relationship for token abilities or profile data
        $user->load('major');

        $token = $user->createToken('student_api_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح.',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'is_practical_delegate' => $user->is_practical_delegate,
                    'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                    'major' => $user->major ? [
                        'id' => $user->major->id,
                        'name' => $user->major->name,
                        'has_clinical' => $user->major->has_clinical,
                    ] : null,
                    'academic_year' => $user->academic_year,
                ],
            ],
        ], 200);
    }

    /**
     * Get Current Student Profile
     */
    public function me(Request $request)
    {
        $user = $request->user()->load('major', 'university', 'college');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'gender' => $user->gender,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'role' => $user->role,
                'is_practical_delegate' => $user->is_practical_delegate,
                'academic_year' => $user->academic_year,
                'university' => $user->university->name ?? null,
                'college' => $user->college->name ?? null,
                'major' => $user->major ? [
                    'id' => $user->major->id,
                    'name' => $user->major->name,
                    'has_clinical' => $user->major->has_clinical,
                ] : null,
            ],
        ]);
    }

    /**
     * Student Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح.',
        ]);
    }
}
