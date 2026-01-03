<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - لوحة المندوب</title>

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
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <polyline points="17 11 19 13 23 9"></polyline>
                </svg>
                <span>لوحة المندوب</span>
            </div>

            <nav class="sidebar-nav">
                <a href="{{ route('delegate.dashboard') }}" class="nav-link {{ request()->routeIs('delegate.dashboard') ? 'active' : '' }}" title="الرئيسية">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    <span>الرئيسية</span>
                </a>

                <div class="nav-group-label" title="إدارة الدفعة">إدارة الدفعة</div>

                <a href="{{ route('delegate.students.index') }}" class="nav-link {{ request()->routeIs('delegate.students.*') ? 'active' : '' }}" title="الطلاب">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <span>الطلاب</span>
                </a>

                <a href="{{ route('delegate.subjects.index') }}" class="nav-link {{ request()->routeIs('delegate.subjects.*') ? 'active' : '' }}" title="المواد الدراسية">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                    <span>المواد الدراسية</span>
                </a>

                <a href="{{ route('delegate.schedules.index') }}" class="nav-link {{ request()->routeIs('delegate.schedules.*') ? 'active' : '' }}" title="الجدول الدراسي">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span>الجدول الدراسي</span>
                </a>

                <div class="nav-group-label" title="الحضور والمتابعة">الحضور والمتابعة</div>

                <a href="{{ route('delegate.attendance.index') }}" class="nav-link {{ request()->routeIs('delegate.attendance.*') ? 'active' : '' }}" title="رصد الحضور">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 11 12 14 22 4"></polyline>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                    </svg>
                    <span>رصد الحضور</span>
                </a>

                <a href="{{ route('delegate.notifications.index') }}" class="nav-link {{ request()->routeIs('delegate.notifications.*') ? 'active' : '' }}" title="التنبيهات">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <span>التنبيهات</span>
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
                        <span class="user-role">مندوب {{ Auth::user()->level->name ?? '' }}</span>
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