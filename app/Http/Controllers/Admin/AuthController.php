<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRole;

class AuthController extends Controller
{
    /**
     * إظهار نموذج تسجيل الدخول للمدير.
     */
    public function showLoginForm()
    {
        // نستخدم view باسم admin.login (سننشئه لاحقاً)
        return view('admin.login');
    }

    /**
     * معالجة طلب تسجيل الدخول.
     */
    public function login(Request $request)
    {
        // التحقق من البيانات المدخلة
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // محاولة تسجيل الدخول مع التحقق من أن المستخدم من نوع admin
        $remember = $request->boolean('remember');
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            /** @var \App\Models\User $user */
            $user = Auth::user();

            return match ($user->role) {
                UserRole::ADMIN => redirect()->intended(route('admin.dashboard')),
                UserRole::DOCTOR => redirect()->intended(route('doctor.dashboard')),
                UserRole::DELEGATE => redirect()->intended(route('delegate.dashboard')), // Placeholder
                UserRole::STUDENT => redirect()->intended(route('student.dashboard')),   // Placeholder
            };
        }

        // فشل المصادقة
        return back()->withErrors([
            'email' => 'بيانات الدخول غير صحيحة.',
        ]);
    }

    /**
     * تسجيل الخروج للمدير.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
