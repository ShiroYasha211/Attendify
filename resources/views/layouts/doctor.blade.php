<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - بوابة الأكاديميين</title>

    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    <style>
        :root {
            --primary-color: #4f46e5;
            --bg-body: #f3f4f6;
        }

        body {
            background-color: var(--bg-body);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .top-navbar {
            background: white;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            padding: 0.75rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 800;
            font-size: 1.25rem;
            color: var(--primary-color);
            text-decoration: none;
        }

        .main-container {
            flex: 1;
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
            padding: 2rem;
        }

        .doctor-badge {
            background-color: #e0e7ff;
            color: #4f46e5;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
        }
    </style>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body x-data="{ showLogoutModal: false }">

    <!-- Top Navbar (No Sidebar) -->
    <nav class="top-navbar">
        <a href="{{ route('doctor.dashboard') }}" class="navbar-brand">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 10v6M2 10v6M12 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"></path>
                <path d="M6 15v-2a6 6 0 1 1 12 0v2"></path>
                <path d="M2 10s2 6 10 6 10-6 10-6"></path>
            </svg>
            <span>بوابة الأكاديميين</span>
        </a>

        <!-- User Menu -->
        <div class="user-menu" style="display: flex; align-items: center; gap: 1rem;">
            <div style="display: flex; flex-direction: column; align-items: end; line-height: 1.2;">
                <span style="font-weight: 700; color: #1f2937;">{{ Auth::user()->name }}</span>
                <span class="doctor-badge">دكتور</span>
            </div>

            <div style="width: 40px; height: 40px; background: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                {{ mb_substr(Auth::user()->name, 0, 1) }}
            </div>

            <div style="width: 1px; height: 24px; background-color: #e5e7eb; margin: 0 0.5rem;"></div>

            <button @click="showLogoutModal = true" style="background: none; border: none; cursor: pointer; color: #ef4444; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; padding: 0.5rem; border-radius: 8px; transition: background 0.2s;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span style="display: none; @media(min-width: 640px){display:inline;}">خروج</span>
            </button>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-container">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer style="text-align: center; padding: 1.5rem; color: #6b7280; font-size: 0.85rem; border-top: 1px solid #e5e7eb; background: white; margin-top: auto;">
        جميع الحقوق محفوظة &copy; {{ date('Y') }} النظام الأكاديمي
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
            <h3 class="modal-title">تأكيد تسجيل الخروج</h3>
            <p class="modal-message">هل أنت متأكد من رغبتك في تسجيل الخروج؟</p>
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