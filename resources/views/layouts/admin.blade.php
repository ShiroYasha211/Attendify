<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - لوحة التحكم</title>

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
                    <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                    <polyline points="2 17 12 22 22 17"></polyline>
                    <polyline points="2 12 12 17 22 12"></polyline>
                </svg>
                <span>لوحة الإدارة</span>
            </div>

            <nav class="sidebar-nav">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" title="الرئيسية">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="3" y1="9" x2="21" y2="9"></line>
                        <line x1="9" y1="21" x2="9" y2="9"></line>
                    </svg>
                    <span>الرئيسية</span>
                </a>

                <div class="nav-group-label" title="الهيكل الأكاديمي">الهيكل الأكاديمي</div>

                <a href="{{ route('admin.universities.index') }}" class="nav-link {{ request()->routeIs('admin.universities.*') ? 'active' : '' }}" title="الجامعات">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21h18v-8a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2z"></path>
                        <polyline points="12 3 2 11 22 11"></polyline>
                    </svg>
                    <span>الجامعات</span>
                </a>
                <a href="{{ route('admin.colleges.index') }}" class="nav-link {{ request()->routeIs('admin.colleges.*') ? 'active' : '' }}" title="الكليات">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                    </svg>
                    <span>الكليات</span>
                </a>
                <a href="{{ route('admin.majors.index') }}" class="nav-link {{ request()->routeIs('admin.majors.*') ? 'active' : '' }}" title="التخصصات">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <span>التخصصات</span>
                </a>
                <a href="{{ route('admin.subjects.index') }}" class="nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}" title="المواد الدراسية">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                    <span>المواد الدراسية</span>
                </a>

                <div class="nav-group-label" title="إدارة المستخدمين">إدارة المستخدمين</div>

                <a href="{{ route('admin.doctors.index') }}" class="nav-link {{ request()->routeIs('admin.doctors.*') ? 'active' : '' }}" title="الدكاترة">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span>الدكاترة</span>
                </a>
                <a href="{{ route('admin.delegates.index') }}" class="nav-link {{ request()->routeIs('admin.delegates.*') ? 'active' : '' }}" title="المندوبين">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                        <line x1="12" y1="11" x2="12" y2="17"></line>
                    </svg>
                    <span>المندوبين</span>
                </a>
                <a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}" title="الطلاب">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <span>الطلاب</span>
                </a>

                <div class="nav-group-label" title="الحضور والتقارير">الحضور والتقارير</div>

                {{-- Attendance Link Removed for Admin --}}
                <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" title="التقارير والإحصائيات">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                    <span>التقارير والإحصائيات</span>
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
                        <span class="user-name">{{ Auth::user()->name ?? 'الأدمن' }}</span>
                        <span class="user-role">مدير النظام</span>
                    </div>

                    <div class="user-avatar">
                        {{ mb_substr(Auth::user()->name ?? 'A', 0, 1) }}
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