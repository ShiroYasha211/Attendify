<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - بوابة الطالب</title>

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

        <!-- Mobile Sidebar Overlay -->
        <div class="sidebar-overlay" :class="{ 'active': sidebarOpen }" @click="sidebarOpen = false"></div>

        <!-- Sidebar -->
        <aside class="sidebar" :class="{ 'open': sidebarOpen, 'collapsed': sidebarCollapsed }">
            <div class="sidebar-brand">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10v6M12 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"></path>
                    <path d="M6 15v-2a6 6 0 1 1 12 0v2"></path>
                    <path d="M2 10s2 6 10 6 10-6 10-6"></path>
                </svg>
                <span>بوابة الطالب</span>
            </div>

            <nav class="sidebar-nav">
                <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" title="الرئيسية">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    <span>الرئيسية</span>
                </a>

                <div class="nav-group-label" title="الشؤون الأكاديمية">أكاديمي</div>

                <a href="{{ route('student.subjects.index') }}" class="nav-link {{ request()->routeIs('student.subjects.*') ? 'active' : '' }}" title="المقررات الدراسية">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                    <span>المقررات الدراسية</span>
                </a>



                <a href="{{ route('student.schedule.index') }}" class="nav-link {{ request()->routeIs('student.schedule.*') ? 'active' : '' }}" title="مركز الدراسة">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                    </svg>
                    <span>مركز الدراسة</span>
                </a>

                <a href="{{ route('student.schedules.index') }}" class="nav-link {{ request()->routeIs('student.schedules.index') ? 'active' : '' }}" title="الجدول الدراسي للدفعة">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span>الجدول الدراسي للدفعة</span>
                </a>

                <a href="{{ route('student.announcements.index') }}" class="nav-link {{ request()->routeIs('student.announcements.*') ? 'active' : '' }}" title="الأخبار والإعلانات">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <span>الأخبار والإعلانات</span>
                </a>

                <a href="{{ route('student.alerts.index') }}" class="nav-link {{ request()->routeIs('student.alerts.*') ? 'active' : '' }}" title="التنبيهات">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <span>التنبيهات</span>
                </a>



                <a href="{{ route('student.exams.index') }}" class="nav-link {{ request()->routeIs('student.exams.*') ? 'active' : '' }}" title="جداول الاختبارات">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <span>جداول الاختبارات</span>
                </a>

                <a href="{{ route('student.resources.index') }}" class="nav-link {{ request()->routeIs('student.resources.*') ? 'active' : '' }}" title="مصادر المقرر">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                    <span>مصادر المقرر</span>
                </a>

                <a href="{{ route('student.library.index') }}" class="nav-link {{ request()->routeIs('student.library.*') ? 'active' : '' }}" title="المكتبة المشتركة">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                    </svg>
                    <span>المكتبة المشتركة</span>
                </a>

                <a href="{{ route('student.assignments.index') }}" class="nav-link {{ request()->routeIs('student.assignments.*') ? 'active' : '' }}" title="التكاليف والواجبات">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <span>التكاليف والواجبات</span>
                </a>

                <div class="nav-group-label" title="الحضور والغياب">تقارير</div>

                <a href="{{ route('student.attendance.scan') }}" class="nav-link {{ request()->routeIs('student.attendance.scan') ? 'active' : '' }}" title="تسجيل الحضور (QR)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 7V5a2 2 0 0 1 2-2h2"></path>
                        <path d="M17 3h2a2 2 0 0 1 2 2v2"></path>
                        <path d="M21 17v2a2 2 0 0 1-2 2h-2"></path>
                        <path d="M7 21H5a2 2 0 0 1-2-2v-2"></path>
                        <rect x="7" y="7" width="10" height="10" rx="1"></rect>
                    </svg>
                    <span>مسح الكود (QR)</span>
                </a>

                @if(Auth::user()->major && Auth::user()->major->has_clinical)
                <div class="nav-group-label" title="القسم السريري/العملي">القسم السريري</div>

                <a href="{{ route('student.clinical.index') }}" class="nav-link {{ request()->routeIs('student.clinical.*') && !request()->routeIs('student.clinical.mock.*') ? 'active' : '' }}" title="الرئيسية السريرية">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3"></path>
                        <path d="M8 15v1a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6v-4"></path>
                        <circle cx="20" cy="10" r="2"></circle>
                    </svg>
                    <span>الرئيسية السريرية</span>
                </a>

                <a href="{{ route('student.clinical.mock.index') }}" class="nav-link {{ request()->routeIs('student.clinical.mock.*') ? 'active' : '' }}" title="الاختبارات التجريبية">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 16v-4"></path>
                        <path d="M12 8h.01"></path>
                    </svg>
                    <span>الاختبارات التجريبية</span>
                    <span style="background:#4f46e5; color:white; border-radius:12px; font-size:0.65rem; padding:0.15rem 0.4rem; margin-right:auto; font-weight: 700;">جديد</span>
                </a>
                @endif

                @if(Auth::user()->isClinicalDelegate() || Auth::user()->isClinicalSubDelegate())
                <div class="nav-group-label" title="القسم العملي (خاص)">القسم العملي (خاص)</div>

                <a href="{{ route('student.clinical.cases.index') }}" class="nav-link {{ request()->is('*/clinical/cases') ? 'active' : '' }}" title="إدارة الحالات السريرية">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                    </svg>
                    <span>الحالات المعتمدة</span>
                </a>

                <a href="{{ route('student.clinical.cases.pending') }}" class="nav-link {{ request()->routeIs('student.clinical.cases.pending') ? 'active' : '' }}" title="الحالات المعلقة">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                    </svg>
                    <span>الحالات المعلقة</span>
                    @php
                        $myPendingCount = \App\Models\Clinical\ClinicalCase::where('doctor_id', Auth::id())->whereIn('approval_status', ['pending', 'rejected'])->count();
                    @endphp
                    @if($myPendingCount > 0)
                        <span style="background: #f59e0b; color: white; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 800; margin-right: auto;">{{ $myPendingCount }}</span>
                    @endif
                </a>
                @endif


                <a href="{{ route('student.reminders.index') }}" class="nav-link {{ request()->routeIs('student.reminders.*') ? 'active' : '' }}" title="التذكيرات">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <span>التذكيرات</span>
                </a>

                <div class="nav-group-label" title="التواصل">التواصل</div>

                <a href="{{ route('student.notifications.index') }}" class="nav-link {{ request()->routeIs('student.notifications.*') ? 'active' : '' }}" title="الإشعارات">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        <circle cx="18" cy="5" r="3" fill="currentColor"></circle>
                    </svg>
                    <span>الإشعارات</span>
                </a>

                <a href="{{ route('student.messages.index') }}" class="nav-link {{ request()->routeIs('student.messages.*') ? 'active' : '' }}" title="الرسائل">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <span>الرسائل</span>
                </a>

                <a href="{{ route('student.inquiries.index') }}" class="nav-link {{ request()->routeIs('student.inquiries.*') ? 'active' : '' }}" title="استفسارات الدكتور">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <span>استفسارات الدكتور</span>
                </a>

                <div class="nav-group-label" title="الحساب">الحساب</div>
                <a href="{{ route('student.profile.password') }}" class="nav-link {{ request()->routeIs('student.profile.password') ? 'active' : '' }}" title="تغيير كلمة المرور">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <span>تغيير كلمة المرور</span>
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
                <div class="user-menu" style="display: flex; align-items: center; gap: 1rem;">

                    @if(Auth::user()->role->value === 'delegate' || \App\Models\ClinicalDelegate::where('student_id', Auth::id())->exists())
                    <a href="{{ route('delegate.dashboard') }}" class="btn-submit" style="padding: 0.5rem 1rem; font-size: 0.85rem; border-radius: 8px; text-decoration: none; width: auto; background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);" title="العودة للوحة المندوب">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 3h5v5"></path>
                            <path d="M4 20L21 3"></path>
                            <path d="M21 16v5h-5"></path>
                            <path d="M15 15l6 6"></path>
                            <path d="M4 4l5 5"></path>
                        </svg>
                        العودة للمندوب
                    </a>

                    <div style="width: 1px; height: 24px; background-color: var(--border-color); margin: 0 0.5rem;"></div>
                    @endif

                    <button @click="showLogoutModal = true" class="logout-btn-icon" title="تسجيل الخروج">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                    </button>

                    <div style="width: 1px; height: 24px; background-color: var(--border-color);"></div>

                    <div class="user-info">
                        <span class="user-name">
                            {{ Auth::user()->name }}
                            @if(\App\Models\ClinicalDelegate::where('student_id', Auth::id())->exists() || Auth::user()->isClinicalSubDelegate())
                            <span style="background-color: #10b981; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.70rem; margin-right: 4px; display: inline-flex; align-items: center; gap: 4px;" title="{{ Auth::user()->isClinicalSubDelegate() ? 'مندوب فرعي مؤقت' : 'مندوب العملي' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"></path>
                                    <path d="m9 12 2 2 4-4"></path>
                                </svg>
                                {{ Auth::user()->isClinicalSubDelegate() ? 'مندوب فرعي' : 'مندوب عملي' }}
                            </span>
                            @endif
                        </span>
                        <span class="user-role">{{ Auth::user()->student_number }}</span>
                    </div>

                    <div class="user-avatar" style="@if(\App\Models\ClinicalDelegate::where('student_id', Auth::id())->exists() || Auth::user()->isClinicalSubDelegate()) border: 2px solid #10b981; @endif">
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
</body>

</html>