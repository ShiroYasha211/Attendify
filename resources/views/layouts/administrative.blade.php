<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - بوابة المسؤول الإداري</title>
    @if($favicon = \App\Models\Setting::get('app_favicon'))
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $favicon) }}">
    @endif

    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">

    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width);
            background-color: #0f172a; /* Darker slate for administrative */
            color: var(--sidebar-text);
            display: flex;
            flex-direction: column;
            transition: width 0.3s ease, transform 0.3s ease;
            position: fixed;
            height: 100vh;
            z-index: 100;
            overflow-x: hidden;
        }

        .sidebar-brand {
            padding: 1.5rem;
            color: white;
            font-size: 1.2rem;
            font-weight: 700;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            white-space: nowrap;
        }

        .sidebar-nav {
            flex: 1;
            padding: 1rem 0;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.2s;
            font-weight: 500;
            gap: 0.75rem;
            white-space: nowrap;
        }

        .nav-link:hover,
        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.05);
            color: white;
            border-right: 3px solid #38bdf8; /* Sky blue for admin role */
        }

        .nav-group-label {
            padding: 1rem 1.5rem 0.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgba(255, 255, 255, 0.3);
            font-weight: 700;
            white-space: nowrap;
        }

        .main-content {
            flex: 1;
            margin-right: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            background-color: #f8fafc;
            min-height: 100vh;
            transition: margin-right 0.3s ease;
        }

        .top-header {
            background-color: white;
            padding: 1rem 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .college-tag {
            background: #e0f2fe;
            color: #0369a1;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Subscription Lock Styles */
        .nav-link.locked {
            opacity: 0.5;
            cursor: not-allowed;
            filter: grayscale(1);
            position: relative;
        }

        .nav-link.locked::after {
            content: '🔒';
            position: absolute;
            left: 1.5rem;
            font-size: 0.8rem;
        }

        .balance-badge {
            background: #f1f5f9;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            color: var(--text-primary);
            border: 1px solid #e2e8f0;
            text-decoration: none;
            transition: all 0.2s;
        }

        .balance-badge:hover {
            background: #e2e8f0;
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-right: 0 !important; }
        }
    </style>
</head>

<body x-data="{ sidebarOpen: false, sidebarCollapsed: false, showLogoutModal: false }">

    <div class="admin-wrapper">

        <!-- Mobile Sidebar Overlay -->
        <div class="sidebar-overlay" :class="{ 'active': sidebarOpen }" @click="sidebarOpen = false"></div>

        <!-- Sidebar -->
        <aside class="sidebar" :class="{ 'open': sidebarOpen, 'collapsed': sidebarCollapsed }">
            <div class="sidebar-brand">
                <i class="fa-solid fa-building-columns"></i>
                <span>بوابة الكلية</span>
            </div>

            <nav class="sidebar-nav">
                <a href="{{ route('administrative.dashboard') }}" class="nav-link {{ request()->routeIs('administrative.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>الرئيسية</span>
                </a>

                <div class="nav-group-label">إدارة الحساب</div>
                <a href="{{ route('administrative.subscription.index') }}" class="nav-link {{ request()->routeIs('administrative.subscription.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-wallet"></i>
                    <span>الاشتراك والرصيد</span>
                </a>
                <a href="{{ route('administrative.ledger') }}" class="nav-link {{ request()->routeIs('administrative.ledger') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>السجل المالي</span>
                </a>
                <a href="{{ route('administrative.profile.password') }}" class="nav-link {{ request()->routeIs('administrative.profile.password') ? 'active' : '' }}">
                    <i class="fa-solid fa-lock"></i>
                    <span>تغيير كلمة المرور</span>
                </a>

                <a href="{{ route('administrative.exams.index') }}" class="nav-link {{ request()->routeIs('administrative.exams.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>جداول الاختبارات</span>
                </a>

                <a href="{{ route('administrative.schedules.index') }}" class="nav-link {{ request()->routeIs('administrative.schedules.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-days"></i>
                    <span>جداول المحاضرات</span>
                </a>

                <div class="nav-group-label">إعدادات الكلية</div>
                <a href="{{ route('administrative.settings') }}" class="nav-link {{ request()->routeIs('administrative.settings') ? 'active' : '' }}">
                    <i class="fa-solid fa-gears"></i>
                    <span>إعدادات النظام</span>
                </a>

                <div class="nav-group-label">الشؤون الأكاديمية</div>
                <a href="{{ route('administrative.majors.index') }}" class="nav-link {{ !auth()->user()->isSubscribed() ? 'locked' : '' }} {{ request()->routeIs('administrative.majors.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>التخصصات</span>
                </a>
                <a href="{{ route('administrative.subjects.index') }}" class="nav-link {{ !auth()->user()->isSubscribed() ? 'locked' : '' }} {{ request()->routeIs('administrative.subjects.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-book"></i>
                    <span>المواد الدراسية</span>
                </a>

                <div class="nav-group-label">إدارة الشؤون</div>
                <a href="{{ route('administrative.doctors.index') }}" class="nav-link {{ !auth()->user()->isSubscribed() ? 'locked' : '' }} {{ request()->routeIs('administrative.doctors.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-tie"></i>
                    <span>الدكاترة</span>
                </a>
                <a href="{{ route('administrative.students.index') }}" class="nav-link {{ !auth()->user()->isSubscribed() ? 'locked' : '' }} {{ request()->routeIs('administrative.students.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-graduate"></i>
                    <span>الطلاب</span>
                </a>
                <a href="{{ route('administrative.delegates.index') }}" class="nav-link {{ !auth()->user()->isSubscribed() ? 'locked' : '' }} {{ request()->routeIs('administrative.delegates.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users-gear"></i>
                    <span>المندوبين</span>
                </a>

                <div class="nav-group-label">المحتوى والإعلانات</div>
                <a href="{{ route('administrative.notifications.index') }}" class="nav-link {{ !auth()->user()->isSubscribed() ? 'locked' : '' }} {{ request()->routeIs('administrative.notifications.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-bullhorn"></i>
                    <span>الإعلانات</span>
                </a>

                <div class="nav-group-label">التقارير</div>
                <a href="{{ route('administrative.reports.index') }}" class="nav-link {{ !auth()->user()->isSubscribed() ? 'locked' : '' }} {{ request()->routeIs('administrative.reports.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>تقارير القسم</span>
                </a>
            </nav>
        </aside>

        <!-- Content Area -->
        <main class="main-content">

            <!-- Header -->
            <!-- Header -->
            <header class="top-header">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <!-- Mobile Sidebar Toggle -->
                    <button @click="sidebarOpen = !sidebarOpen" class="btn mobile-toggle" style="background: none; border: none; font-size: 1.5rem; padding: 0; display: none;">
                        ☰
                    </button>
                    <!-- Desktop Collapse Toggle -->
                    <button @click="sidebarCollapsed = !sidebarCollapsed" class="btn desktop-toggle" style="background: none; border: none; cursor: pointer; color: var(--text-secondary); padding: 0;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>

                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <h2 class="header-title" style="margin:0; font-size: 1.25rem;">@yield('title')</h2>
                        <span class="college-tag">{{ auth()->user()->college->name ?? 'الكلية' }}</span>
                    </div>
                </div>

                <!-- User Profile Section -->
                <div class="user-menu" style="display: flex; align-items: center; gap: 1rem;">

                    <button @click="showLogoutModal = true" class="logout-btn-icon" title="تسجيل الخروج">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                    </button>

                    <a href="{{ route('administrative.subscription.index') }}" class="balance-badge" title="رصيدك الحالي">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-color)" stroke-width="2">
                            <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                        <span>{{ number_format(auth()->user()->balance) }} ريال</span>
                    </a>

                    <div style="width: 1px; height: 24px; background-color: var(--border-color);"></div>

                    <div class="user-info">
                        <span class="user-name">
                            {{ auth()->user()->name }}
                            @if(auth()->user()->isSubscribed())
                                <span style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 2px 8px; border-radius: 6px; font-size: 0.70rem; font-weight: 800; margin-right: 4px; display: inline-flex; align-items: center;">
                                    مشترك
                                </span>
                            @else
                                <span style="background-color: #94a3b8; color: white; padding: 2px 8px; border-radius: 6px; font-size: 0.70rem; font-weight: 800; margin-right: 4px; display: inline-flex; align-items: center;">
                                    غير مشترك
                                </span>
                            @endif
                        </span>
                        <span class="user-role">مسؤول إداري</span>
                    </div>

                    <div class="user-avatar">
                        {{ mb_substr(auth()->user()->name, 0, 1) }}
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div style="flex: 1; padding: 2rem;">
                @include('partials.alerts')
                @yield('content')
            </div>

            <!-- Footer -->
            <footer style="text-align: center; padding: 1.5rem; color: var(--text-secondary); font-size: 0.85rem; border-top: 1px solid var(--border-color);">
                جميع الحقوق محفوظة &copy; {{ date('Y') }} بوابة كلية {{ auth()->user()->college->name ?? '' }}
            </footer>

        </main>
    </div>

    <!-- Logout Confirmation Modal -->
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
            <p class="modal-message">هل أنت متأكد من رغبتك في تسجيل الخروج من النظام؟</p>

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" @click="showLogoutModal = false">
                    إلغاء
                </button>

                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        نعم، تسجيل الخروج
                    </button>
                </form>
            </div>
        </div>
    </div>

    @stack('scripts')
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
