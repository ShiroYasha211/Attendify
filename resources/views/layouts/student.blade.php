<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - بوابة الطالب</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">

    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg-body: #f1f5f9;
            --surface: #ffffff;
            --border: #e2e8f0;
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Tajawal', sans-serif;
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Modern Navbar */
        .student-navbar {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: #0f172a;
            font-weight: 800;
            font-size: 1.35rem;
            letter-spacing: -0.5px;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
        }

        .user-pill {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: white;
            padding: 0.5rem 0.5rem 0.5rem 1.25rem;
            border-radius: 99px;
            border: 1px solid var(--border);
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }

        .user-pill:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid white;
            box-shadow: 0 0 0 2px #e2e8f0;
        }

        .main-container {
            flex: 1;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 2.5rem 1.5rem;
        }

        .btn-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            color: var(--secondary);
            transition: all 0.2s;
            border: none;
            background: transparent;
            cursor: pointer;
        }

        .btn-icon:hover {
            background-color: #f1f5f9;
            color: var(--danger);
        }

        /* Animations */
        .fade-in-up {
            animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body x-data="{ showLogoutModal: false }">

    <nav class="student-navbar">
        <div class="navbar-container">
            <a href="{{ route('student.dashboard') }}" class="brand-logo">
                <div class="brand-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10v6M2 10v6M12 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"></path>
                        <path d="M6 15v-2a6 6 0 1 1 12 0v2"></path>
                        <path d="M2 10s2 6 10 6 10-6 10-6"></path>
                    </svg>
                </div>
                <span>بوابة الطالب</span>
            </a>

            <div style="display: flex; align-items: center; gap: 1rem;">
                <div class="user-pill d-none d-md-flex">
                    <div style="text-align: left; line-height: 1.2;">
                        <div style="font-weight: 700; font-size: 0.9rem; color: #1e293b;">{{ Auth::user()->name }}</div>
                        <div style="font-size: 0.75rem; color: #64748b; font-weight: 500;">{{ Auth::user()->student_number }}</div>
                    </div>
                    <div class="user-avatar">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0ea5e9&color=fff&bold=true" alt="Avatar" style="width: 100%; height: 100%;">
                    </div>
                </div>

                <!-- Logout Trigger -->
                <button @click="showLogoutModal = true" class="btn-icon" title="تسجيل خروج">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    <main class="main-container">
        @yield('content')
    </main>

    <footer style="text-align: center; padding: 2rem; color: #94a3b8; font-size: 0.85rem;">
        &copy; {{ date('Y') }} النظام الأكاديمي الذكي. جميع الحقوق محفوظة.
    </footer>

    <!-- Logout Modal -->
    <div x-show="showLogoutModal" class="modal-overlay" style="display: none;" x-transition.opacity.duration.300ms>
        <div class="modal-container" @click.away="showLogoutModal = false">
            <div class="modal-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
            </div>
            <h3 class="modal-title">تسجيل الخروج</h3>
            <p class="modal-message">هل أنت متأكد من أنك تريد إنهاء جلستك الحالية؟</p>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" @click="showLogoutModal = false">إلغاء</button>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger">نعم، تسجيل الخروج</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>