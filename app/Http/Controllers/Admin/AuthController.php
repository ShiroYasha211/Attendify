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

            // إذا لم يكن admin نعيده إلى صفحة الدخول مع رسالة خطأ
            if ($user->role !== UserRole::ADMIN) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'هذا الحساب غير مصرح له بالدخول إلى لوحة الإدارة.',
                ]);
            }

            // نجاح – نعيده إلى لوحة التحكم (مسار placeholder الآن)
            return redirect()->intended(route('admin.dashboard'));
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
