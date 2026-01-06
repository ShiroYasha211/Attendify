<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - بوابة أعضاء هيئة التدريس</title>

    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
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
            color: var(--sidebar-text);
            text-decoration: none;
            transition: all 0.2s;
            font-weight: 500;
            gap: 0.75rem;
            white-space: nowrap;
        }

        .nav-link:hover,
        .nav-link.active {
            background-color: var(--sidebar-active-bg);
            color: var(--sidebar-active-text);
            border-right: 3px solid var(--primary-color);
        }

        .nav-group-label {
            padding: 1rem 1.5rem 0.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgba(255, 255, 255, 0.4);
            font-weight: 700;
            white-space: nowrap;
        }

        .main-content {
            flex: 1;
            margin-right: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            background-color: var(--bg-body);
            min-height: 100vh;
            transition: margin-right 0.3s ease;
        }

        .top-header {
            background-color: white;
            padding: 1rem 2rem;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-right: 0 !important;
            }
        }

        /* Toggle Button Visibility */
        .mobile-toggle {
            display: none;
        }

        .desktop-toggle {
            display: none;
        }

        @media (max-width: 768px) {
            .mobile-toggle {
                display: block;
            }
        }

        @media (min-width: 769px) {
            .desktop-toggle {
                display: block;
            }
        }
    </style>
</head>

<body x-data="{ sidebarOpen: false, sidebarCollapsed: false, showLogoutModal: false }">

    <div class="admin-wrapper">

        <!-- Sidebar -->
        <aside class="sidebar" :class="{ 'open': sidebarOpen, 'collapsed': sidebarCollapsed }">
            <div class="sidebar-brand">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10v6M12 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"></path>
                    <path d="M6 15v-2a6 6 0 1 1 12 0v2"></path>
                </svg>
                <span>بوابة هيئة التدريس</span>
            </div>

            <nav class="sidebar-nav">
                <a href="{{ route('doctor.dashboard') }}" class="nav-link {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}" title="الرئيسية">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    <span>الرئيسية</span>
                </a>

                <div class="nav-group-label" title="الأكاديمية">الأكاديمية</div>

                <!-- Excuses -->
                <a href="{{ route('doctor.excuses.index') }}" class="nav-link {{ request()->routeIs('doctor.excuses.*') ? 'active' : '' }}" title="أعذار الغياب">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <span>أعذار الغياب</span>
                </a>

                <!-- Reports -->
                <a href="{{ route('doctor.reports.index') }}" class="nav-link {{ request()->routeIs('doctor.reports.*') ? 'active' : '' }}" title="التقارير">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                    <span>التقارير</span>
                </a>

                <!-- Assignments -->
                <a href="{{ route('doctor.assignments.index') }}" class="nav-link {{ request()->routeIs('doctor.assignments.*') ? 'active' : '' }}" title="التكاليف">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <span>التكاليف</span>
                </a>

            </nav>
        </aside>

        <!-- Content Area -->
        <main class="main-content" :class="{ 'expanded': sidebarCollapsed }">

            <!-- Header -->
            <header class="top-header">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <!-- Mobile Sidebar Toggle -->
                    <button @click="sidebarOpen = !sidebarOpen" class="btn mobile-toggle" style="background: none; border: none; font-size: 1.5rem; padding: 0;">
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

                    <h2 class="header-title">@yield('title')</h2>
                </div>

                <!-- User Profile Section -->
                <div class="user-menu">

                    <button @click="showLogoutModal = true" class="logout-btn-icon" title="تسجيل الخروج">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                    </button>

                    <div style="width: 1px; height: 24px; background-color: var(--border-color);"></div>

                    <div class="user-info">
                        <span class="user-name">{{ Auth::user()->name }}</span>
                        <span class="user-role">دكتور</span>
                    </div>

                    <div class="user-avatar">
                        {{ mb_substr(Auth::user()->name, 0, 1) }}
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div style="flex: 1; padding: 2rem; overflow-x: auto;">
                @yield('content')
            </div>

            <!-- Footer -->
            <footer style="text-align: center; padding: 1.5rem; color: var(--text-secondary); font-size: 0.85rem; border-top: 1px solid var(--border-color);">
                جميع الحقوق محفوظة &copy; {{ date('Y') }} النظام الأكاديمي
            </footer>

        </main>
    </div>

    <!-- Logout Confirmation Modal -->
    <div
        x-show="showLogoutModal"
        class="modal-overlay"
        style="display: none;"
        x-transition.opacity.duration.300ms>
        <div
            class="modal-container"
            @click.away="showLogoutModal = false">
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
                <button
                    type="button"
                    class="btn btn-secondary"
                    @click="showLogoutModal = false">
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

</body>

</html>