<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - لوحة الإدارة</title>

    <!-- Link to our new Dashboard CSS -->
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

</head>

<body style="background: white;">

    <div class="login-split-screen">

        <!-- Left Side: Form -->
        <div class="login-left">
            <div class="login-form-container">
                <div style="margin-bottom: 2rem; text-align: center;">
                    <h2 style="font-size: 2rem; margin-bottom: 0.5rem; color: var(--primary-color);">مرحباً بعودتك!</h2>
                    <p style="color: var(--text-secondary);">يرجى تسجيل الدخول للوصول إلى لوحة التحكم</p>
                </div>

                @if($errors->any())
                <div class="alert alert-error">
                    بيانات الدخول غير صحيحة، حاول مرة أخرى.
                </div>
                @endif

                <form action="{{ route('admin.login') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="email" class="form-label">البريد الإلكتروني</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">كلمة المرور</label>
                        <div style="position: relative;">
                            <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required style="padding-left: 2.5rem;">
                            <span onclick="togglePassword()" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-secondary);">
                                <!-- Eye Icon SVG -->
                                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <!-- Eye Off Icon SVG (Hidden by default) -->
                                <svg id="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="form-group" style="display: flex; align-items: center; justify-content: space-between;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-secondary); font-size: 0.9rem;">
                            <input type="checkbox" name="remember" style="width: 16px; height: 16px;">
                            تذكرني
                        </label>
                        <a href="#" style="font-size: 0.9rem; color: var(--primary-color); font-weight: 600;">نسيت كلمة المرور؟</a>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                        تسجيل الدخول
                    </button>
                </form>

                <div style="margin-top: 2rem; text-align: center; color: var(--text-light); font-size: 0.9rem;">
                    جميع الحقوق محفوظة &copy; {{ date('Y') }} النظام الأكاديمي
                </div>
            </div>
        </div>

        <!-- Right Side: Visual -->
        <div class="login-right">
            <div class="login-content-slider" id="slider">
                <div class="slide active">
                    <h1 style="font-size: 3rem; margin-bottom: 1rem; font-weight: 700;">النظام الأكاديمي الذكي</h1>
                    <p style="font-size: 1.2rem; opacity: 0.9; line-height: 1.8;">
                        بوابة متكاملة لإدارة الطلاب، الحضور،<br> والمتابعة الأكاديمية بكفاءة عالية.
                    </p>
                </div>
                <div class="slide">
                    <h1 style="font-size: 3rem; margin-bottom: 1rem; font-weight: 700;">إدارة الحضور والغياب</h1>
                    <p style="font-size: 1.2rem; opacity: 0.9; line-height: 1.8;">
                        تتبع دقيق لحضور الطلاب،<br> مع تقارير إحصائية فورية وتنبيهات آلية.
                    </p>
                </div>
                <div class="slide">
                    <h1 style="font-size: 3rem; margin-bottom: 1rem; font-weight: 700;">تقارير شاملة</h1>
                    <p style="font-size: 1.2rem; opacity: 0.9; line-height: 1.8;">
                        لوحات معلومات تفاعلية<br> تساعدك على اتخاذ القرارات الأكاديمية الصائبة.
                    </p>
                </div>

                <div class="slider-dots" style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: center;">
                    <div class="dot active" onclick="setSlide(0)"></div>
                    <div class="dot" onclick="setSlide(1)"></div>
                    <div class="dot" onclick="setSlide(2)"></div>
                </div>
            </div>
        </div>

    </div>

    <style>
        /* Slider Styles */
        .slide {
            display: none;
            animation: fadeInSlide 0.8s ease;
            text-align: center;
        }

        .slide.active {
            display: block;
        }

        .slider-dots .dot {
            width: 10px;
            height: 10px;
            background: white;
            border-radius: 50%;
            opacity: 0.5;
            cursor: pointer;
            transition: 0.3s;
        }

        .slider-dots .dot.active {
            opacity: 1;
            transform: scale(1.2);
        }

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
    </style>

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
    </script>

</body>

</html>