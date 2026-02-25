<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - النظام الأكاديمي</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

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
            padding: 1rem;
        }

        .login-container {
            display: flex;
            width: 100%;
            max-width: 1100px;
            min-height: 600px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        /* Left Side - Form */
        .login-left {
            flex: 1;
            background: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-form-container {
            max-width: 400px;
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

        /* Alert */
        .alert-error {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
        }

        .alert-error svg {
            flex-shrink: 0;
        }

        /* Form Group */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            padding-right: 2.75rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.2s;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .form-control::placeholder {
            color: var(--text-light);
        }

        /* Password toggle */
        .password-toggle {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-light);
            transition: color 0.2s;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        /* Checkbox & Links Row */
        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .remember-me input {
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
        }

        .forgot-link {
            font-size: 0.9rem;
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
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
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px -3px rgba(79, 70, 229, 0.4);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px -3px rgba(79, 70, 229, 0.5);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-submit .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            margin-left: 0.5rem;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Footer */
        .login-footer {
            margin-top: 2rem;
            text-align: center;
            color: var(--text-light);
            font-size: 0.85rem;
        }

        /* Right Side - Visual */
        .login-right {
            flex: 1.1;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #9333ea 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }

        /* Glassmorphism card */
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

        .slide {
            display: none;
            animation: fadeInSlide 0.8s ease;
        }

        .slide.active {
            display: block;
        }

        .slide h1 {
            font-size: 2.25rem;
            font-weight: 800;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .slide p {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.8;
        }

        /* Slider Dots */
        .slider-dots {
            margin-top: 2rem;
            display: flex;
            gap: 0.75rem;
            justify-content: center;
        }

        .dot {
            width: 10px;
            height: 10px;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
        }

        .dot.active {
            background: white;
            transform: scale(1.3);
        }

        /* Decorative elements */
        .login-right::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -100px;
            right: -100px;
        }

        .login-right::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            bottom: -50px;
            left: -50px;
        }

        /* Animation */
        @keyframes fadeInSlide {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 900px) {
            .login-container {
                flex-direction: column;
                max-width: 500px;
            }

            .login-right {
                display: none;
            }

            .login-left {
                padding: 2rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 0;
            }

            .login-container {
                border-radius: 0;
                min-height: 100vh;
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
</head>

<body>

    <div class="login-container">

        <!-- Left Side: Form -->
        <div class="login-left">
            <div class="login-form-container">

                <!-- Logo -->
                <div class="logo-container">
                    <div class="logo-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                            <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                        </svg>
                    </div>
                    <h1 class="logo-title">المنصة الأكاديمية الشاملة</h1>
                    <p class="logo-subtitle">بوابتك المتكاملة للإدارة والتواصل الأكاديمي</p>
                </div>

                <!-- Welcome -->
                <div class="welcome-text">
                    <h2>مرحباً بعودتك! 👋</h2>
                    <p>سجّل دخولك للوصول إلى لوحة التحكم</p>
                </div>

                <!-- Error Alert -->
                @if($errors->any())
                <div class="alert-error">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span>بيانات الدخول غير صحيحة، حاول مرة أخرى.</span>
                </div>
                @endif

                <form action="{{ route('admin.login') }}" method="POST">
                    @csrf

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">البريد الإلكتروني</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </span>
                            <input type="email" id="email" name="email" class="form-control" placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">كلمة المرور</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </span>
                            <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required style="padding-left: 2.75rem;">
                            <span class="password-toggle" onclick="togglePassword()">
                                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg id="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <!-- Options Row -->
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            تذكرني
                        </label>
                        <a href="#" class="forgot-link" onclick="alert('تواصل مع مسؤول النظام لإعادة تعيين كلمة المرور'); return false;">نسيت كلمة المرور؟</a>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn-submit" id="loginBtn">
                        <span id="btnText">تسجيل الدخول</span>
                        <span class="spinner" id="btnSpinner"></span>
                    </button>
                </form>

                <!-- Footer -->
                <div class="login-footer">
                    جميع الحقوق محفوظة &copy; {{ date('Y') }} النظام الأكاديمي
                </div>

            </div>
        </div>

        <!-- Right Side: Visual -->
        <div class="login-right">
            <div class="glass-card">
                <div class="slide active">
                    <h1>🎓 المنصة الأكاديمية الشاملة</h1>
                    <p>
                        نظام متكامل لإدارة العملية التعليمية<br>من الحضور والتكاليف إلى الدرجات والتواصل
                    </p>
                </div>
                <div class="slide">
                    <h1>📊 الحضور والدرجات والتقارير</h1>
                    <p>
                        تتبع دقيق للحضور والغياب، إدارة الدرجات،<br>وتقارير تفصيلية وإحصائيات لحظية
                    </p>
                </div>
                <div class="slide">
                    <h1>📝 التكاليف والتسليمات</h1>
                    <p>
                        إنشاء التكاليف ومتابعة تسليمات الطلاب<br>مع نظام تقييم ومراجعة متكامل
                    </p>
                </div>
                <div class="slide">
                    <h1>💬 التواصل والاستفسارات</h1>
                    <p>
                        نظام رسائل متكامل بين الطلاب والمدرسين<br>وقنوات استفسار فورية مع الإدارة
                    </p>
                </div>
                <div class="slide">
                    <h1>📋 الأعذار والموارد التعليمية</h1>
                    <p>
                        إدارة طلبات الأعذار الطبية،<br>ومشاركة الملفات والموارد التعليمية
                    </p>
                </div>

                <div class="slider-dots">
                    <div class="dot active" onclick="setSlide(0)"></div>
                    <div class="dot" onclick="setSlide(1)"></div>
                    <div class="dot" onclick="setSlide(2)"></div>
                    <div class="dot" onclick="setSlide(3)"></div>
                    <div class="dot" onclick="setSlide(4)"></div>
                </div>
            </div>
        </div>

    </div>

    <script>
        // Password Toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            const eyeOffIcon = document.getElementById('eye-off-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.style.display = 'none';
                eyeOffIcon.style.display = 'block';
            } else {
                passwordInput.type = 'password';
                eyeIcon.style.display = 'block';
                eyeOffIcon.style.display = 'none';
            }
        }

        // Slider Logic
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.dot');

        function showSlide(index) {
            slides.forEach(s => s.classList.remove('active'));
            dots.forEach(d => d.classList.remove('active'));

            slides[index].classList.add('active');
            dots[index].classList.add('active');
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }

        function setSlide(index) {
            currentSlide = index;
            showSlide(currentSlide);
        }

        setInterval(nextSlide, 5000);

        // Loading spinner on form submit
        document.querySelector('form').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            const text = document.getElementById('btnText');
            const spinner = document.getElementById('btnSpinner');
            btn.disabled = true;
            text.textContent = 'جاري تسجيل الدخول...';
            spinner.style.display = 'inline-block';
        });
    </script>

</body>

</html>