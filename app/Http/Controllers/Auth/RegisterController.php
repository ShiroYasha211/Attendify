<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Academic\University;
use App\Models\Academic\College;
use App\Models\Academic\Major;
use App\Models\Academic\Level;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        $universities = University::all();
        $colleges = College::all();
        $majors = Major::all();
        $levels = Level::all();
        
        return view('auth.register', compact('universities', 'colleges', 'majors', 'levels'));
    }

    /**
     * Handle a registration request.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['student', 'doctor', 'delegate'])],
            
            // Required for student & delegate
            'student_number' => ['required_if:role,student,delegate', 'nullable', 'string', 'unique:users,student_number'],
            'university_id' => ['required_if:role,student,delegate', 'nullable', 'exists:universities,id'],
            'college_id' => ['required_if:role,student,delegate', 'nullable', 'exists:colleges,id'],
            'major_id' => ['required_if:role,student,delegate', 'nullable', 'exists:majors,id'],
            'level_id' => ['required_if:role,student,delegate', 'nullable', 'exists:levels,id'],
        ], [
            'name.required' => 'حقل الاسم الكامل مطلوب.',
            'name.max' => 'الاسم يجب ألا يتجاوز 255 حرفاً.',
            'email.required' => 'حقل البريد الإلكتروني مطلوب.',
            'email.email' => 'يرجى إدخال بريد إلكتروني صحيح.',
            'email.unique' => 'البريد الإلكتروني هذا مسجل مسبقاً، يرجى تسجيل الدخول أو استخدام بريد آخر.',
            'password.required' => 'حقل كلمة المرور مطلوب.',
            'password.min' => 'كلمة المرور يجب أن تتكون من 8 أحرف على الأقل.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
            
            'role.in' => 'حدد نوع حساب صالح.',
            'student_number.required_if' => 'رقم القيد الجامعي مطلوب للطلاب والمندوبين.',
            'university_id.required_if' => 'حقل الجامعة مطلوب.',
            'college_id.required_if' => 'حقل الكلية مطلوب.',
            'major_id.required_if' => 'حقل التخصص مطلوب.',
            'level_id.required_if' => 'حقل المستوى مطلوب.',
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => 'pending', // Account requires admin approval
        ];

        if (in_array($request->role, ['student', 'delegate'])) {
            $userData['student_number'] = $request->student_number;
            $userData['university_id'] = $request->university_id;
            $userData['college_id'] = $request->college_id;
            $userData['major_id'] = $request->major_id;
            $userData['level_id'] = $request->level_id;
        }

        User::create($userData);

        return redirect()->route('admin.login')->with('success', 'تم إرسال طلب إنشاء الحساب بنجاح. حسابك الآن قيد المراجعة وسنقوم بإعلامك فور الموافقة عليه.');
    }
}
