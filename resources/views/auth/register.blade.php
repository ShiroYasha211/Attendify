<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب جديد - النظام الأكاديمي</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    <!-- Alpine.js for dynamic form handling -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-dark: #4338ca;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-light: #94a3b8;
            --bg-light: #f8fafc;
            --border-color: #e2e8f0;
            --success-color: #10b981;
            --danger-color: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .login-container {
            display: flex;
            width: 100%;
            max-width: 1200px;
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        /* Left Side - Form */
        .login-left {
            flex: 1.5;
            background: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-form-container {
            max-width: 600px;
            margin: 0 auto;
            width: 100%;
        }

        /* Logo */
        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4);
        }

        .logo-icon svg {
            color: white;
        }

        .logo-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .logo-subtitle {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        /* Welcome Text */
        .welcome-text {
            text-align: center;
            margin-bottom: 2rem;
        }

        .welcome-text h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .welcome-text p {
            color: var(--text-secondary);
        }

        /* Error Alert */
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }
        
        .alert-error ul {
            margin-top: 0.5rem;
            padding-right: 1.5rem;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.2s;
            background: white;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        /* Role Selector Cards */
        .role-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .role-card {
            border: 2px solid var(--border-color);
            border-radius: 16px;
            padding: 1.5rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            background: white;
        }

        .role-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.1);
        }

        .role-card.selected {
            border-color: var(--primary-color);
            background: #eef2ff;
        }

        .role-card.selected::after {
            content: '✓';
            position: absolute;
            top: -10px;
            right: -10px;
            background: var(--primary-color);
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .role-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .role-name {
            font-weight: 700;
            color: var(--text-primary);
            font-size: 1rem;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px -3px rgba(79, 70, 229, 0.4);
            margin-top: 1rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px -3px rgba(79, 70, 229, 0.5);
        }

        /* Login Link */
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.95rem;
            color: var(--text-secondary);
        }

        .login-link a {
            color: var(--primary-color);
            font-weight: 700;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        /* Right Side - Visual */
        .login-right {
            flex: 1;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #9333ea 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 24px;
            padding: 3rem;
            text-align: center;
            color: white;
            max-width: 450px;
        }

        .glass-card h1 {
            font-size: 2.25rem;
            font-weight: 800;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .glass-card p {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.8;
        }

        /* Decorative Background */
        .login-right::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -150px;
            right: -150px;
        }

        .login-right::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            bottom: -100px;
            left: -100px;
        }

        /* Form Validation Styles */
        .has-error {
            border-color: var(--danger-color) !important;
            background-color: #fef2f2 !important;
        }

        .error-message {
            color: var(--danger-color);
            font-size: 0.8rem;
            margin-top: 0.25rem;
            display: block;
            font-weight: 500;
        }

        /* Password Toggle */
        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .password-toggle:hover {
            color: var(--primary-color);
        }

        @media (max-width: 900px) {
            .login-container {
                flex-direction: column-reverse;
                max-width: 600px;
            }

            .login-right {
                padding: 2rem;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .role-selector {
                grid-template-columns: 1fr;
            }
            .login-left {
                padding: 1.5rem;
            }
            .logo-icon {
                width: 60px;
                height: 60px;
            }
            .welcome-text h2 {
                font-size: 1.5rem;
            }
        }
    </style>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('registerData', () => ({
                role: '{{ old('role', 'student') }}',
                university_id: '{{ old('university_id') }}',
                college_id: '{{ old('college_id') }}',
                major_id: '{{ old('major_id') }}',
                level_id: '{{ old('level_id') }}',
                allColleges: @json($colleges),
                allMajors: @json($majors),
                allLevels: @json($levels),
                
                showPassword: false,
                showPasswordConfirm: false,
                
                get filteredColleges() {
                    if (!this.university_id) return [];
                    return this.allColleges.filter(c => c.university_id == this.university_id);
                },

                get filteredMajors() {
                    if (!this.college_id) return [];
                    return this.allMajors.filter(m => m.college_id == this.college_id);
                },
                
                get filteredLevels() {
                    if (!this.major_id) return [];
                    return this.allLevels.filter(l => l.major_id == this.major_id);
                },
                
                universityChanged() {
                    this.college_id = '';
                    this.major_id = '';
                    this.level_id = '';
                },
                collegeChanged() {
                    this.major_id = '';
                    this.level_id = '';
                },
                majorChanged() {
                    this.level_id = '';
                }
            }))
        })
    </script>
</head>

<body>

    <div class="login-container" x-data="registerData">

        <!-- Left Side: Form -->
        <div class="login-left">
            <div class="login-form-container">

                <div class="logo-container">
                    <div class="logo-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <line x1="19" y1="8" x2="19" y2="14"></line>
                            <line x1="22" y1="11" x2="16" y2="11"></line>
                        </svg>
                    </div>
                    <h1 class="logo-title">طلب إنشاء حساب</h1>
                    <p class="logo-subtitle">أدخل بياناتك وسيتم مراجعتها من قبل الإدارة</p>
                </div>

                @if($errors->any())
                <div class="alert-error">
                    <strong>يرجى تصحيح الأخطاء التالية:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('admin.register') }}" method="POST">
                    @csrf
                    
                    <input type="hidden" name="role" x-model="role">

                    <!-- Role Selection Cards -->
                    <div class="form-group full-width">
                        <label class="form-label">أنا أسجل كـ:</label>
                        <div class="role-selector">
                            <div class="role-card" :class="role === 'student' ? 'selected' : ''" @click="role = 'student'">
                                <span class="role-icon">👨‍🎓</span>
                                <span class="role-name">طالب</span>
                            </div>
                            <div class="role-card" :class="role === 'delegate' ? 'selected' : ''" @click="role = 'delegate'">
                                <span class="role-icon">⭐</span>
                                <span class="role-name">مندوب دفعة</span>
                            </div>
                            <div class="role-card" :class="role === 'doctor' ? 'selected' : ''" @click="role = 'doctor'">
                                <span class="role-icon">👨‍🏫</span>
                                <span class="role-name">دكتور / أكاديمي</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <!-- Basic Info -->
                        <div class="form-group full-width">
                            <label for="name" class="form-label">الاسم الكامل (الرباعي)</label>
                            <input type="text" id="name" name="name" class="form-control @error('name') has-error @enderror" value="{{ old('name') }}" required>
                            @error('name')<span class="error-message">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group full-width">
                            <label for="email" class="form-label">البريد الإلكتروني</label>
                            <input type="email" id="email" name="email" class="form-control @error('email') has-error @enderror" placeholder="name@example.com" value="{{ old('email') }}" required>
                            @error('email')<span class="error-message">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">كلمة المرور</label>
                            <div class="password-container">
                                <input :type="showPassword ? 'text' : 'password'" id="password" name="password" class="form-control @error('password') has-error @enderror" required minlength="8">
                                <button type="button" class="password-toggle" @click="showPassword = !showPassword" tabindex="-1">
                                    <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    <svg x-show="showPassword" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                                </button>
                            </div>
                            @error('password')<span class="error-message">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">تأكيد كلمة المرور</label>
                            <div class="password-container">
                                <input :type="showPasswordConfirm ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" class="form-control" required minlength="8">
                                <button type="button" class="password-toggle" @click="showPasswordConfirm = !showPasswordConfirm" tabindex="-1">
                                    <svg x-show="!showPasswordConfirm" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    <svg x-show="showPasswordConfirm" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Academic Info (Only for Students and Delegates) -->
                        <template x-if="role === 'student' || role === 'delegate'">
                            <div class="form-group full-width" style="margin-bottom: 0;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                                    <div class="form-group full-width">
                                        <label for="student_number" class="form-label">الرقم الجامعي / رقم القيد</label>
                                        <input type="text" id="student_number" name="student_number" class="form-control @error('student_number') has-error @enderror" value="{{ old('student_number') }}" :required="role === 'student' || role === 'delegate'">
                                        @error('student_number')<span class="error-message">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="university_id" class="form-label">الجامعة</label>
                                        <select id="university_id" name="university_id" class="form-select @error('university_id') has-error @enderror" x-model="university_id" @change="universityChanged()" :required="role === 'student' || role === 'delegate'">
                                            <option value="">اختر الجامعة</option>
                                            @foreach($universities as $university)
                                                <option value="{{ $university->id }}">{{ $university->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('university_id')<span class="error-message">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="college_id" class="form-label">الكلية</label>
                                        <select id="college_id" name="college_id" class="form-select @error('college_id') has-error @enderror" x-model="college_id" :disabled="!university_id" @change="collegeChanged()" :required="role === 'student' || role === 'delegate'">
                                            <option value="">اختر الجامعة أولاً...</option>
                                            <template x-for="college in filteredColleges" :key="college.id">
                                                <option :value="college.id" x-text="college.name"></option>
                                            </template>
                                        </select>
                                        @error('college_id')<span class="error-message">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="major_id" class="form-label">التخصص (الدفعة للمندوب)</label>
                                        <select id="major_id" name="major_id" class="form-select @error('major_id') has-error @enderror" x-model="major_id" :disabled="!college_id" @change="majorChanged()" :required="role === 'student' || role === 'delegate'">
                                            <option value="">اختر التخصص أولاً...</option>
                                            <template x-for="major in filteredMajors" :key="major.id">
                                                <option :value="major.id" x-text="major.name"></option>
                                            </template>
                                        </select>
                                        @error('major_id')<span class="error-message">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="level_id" class="form-label">المستوى الدراسي</label>
                                        <select id="level_id" name="level_id" class="form-select @error('level_id') has-error @enderror" x-model="level_id" :disabled="!major_id" :required="role === 'student' || role === 'delegate'">
                                            <option value="">اختر المستوى أولاً...</option>
                                            <template x-for="level in filteredLevels" :key="level.id">
                                                <option :value="level.id" x-text="level.name"></option>
                                            </template>
                                        </select>
                                        @error('level_id')<span class="error-message">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                        </template>

                    </div>

                    <button type="submit" class="btn-submit">
                        إرسال لطلب الاعتماد
                    </button>
                    
                    <div class="login-link">
                        لديك حساب بالفعل؟ <a href="{{ route('admin.login') }}">تسجيل الدخول من هنا</a>
                    </div>
                </form>

            </div>
        </div>

        <!-- Right Side: Visual -->
        <div class="login-right">
            <div class="glass-card">
                <h1>حساب واحد للكل</h1>
                <p>
                    إنشاء الحساب يخضع للمراجعة من قبل الإدارة المركزية للتأكد من هوية المستخدمين وصلاحيتهم حفاظاً على أمن البيانات الأكاديمية.
                </p>
                <div style="margin-top: 2rem; padding: 1rem; background: rgba(0,0,0,0.2); border-radius: 12px; text-align: right; font-size: 0.95rem;">
                    <ul style="list-style-position: inside; margin: 0; padding: 0;">
                        <li style="margin-bottom: 0.5rem">اختر نوع حسابك بدقة</li>
                        <li style="margin-bottom: 0.5rem">البيانات الأكاديمية مطلوبة للمندوب والطالب</li>
                        <li>سيتم التواصل معك أو تفعيل حسابك مباشرة فور الاعتماد</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
