@extends('layouts.student')

@section('title', 'لوحة التحكم')

@section('content')
<div class="fade-in-up">

    <!-- Hero Section -->
    <div class="welcome-hero mb-4 mb-md-5">
        <div class="hero-content">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge-glass">
                    <i class="feather-flag"></i>
                    {{ $term->name ?? 'الفصل الدراسي' }}
                </span>
                <span class="badge-glass">
                    {{ $level->name ?? 'المستوى' }}
                </span>
            </div>
            <h1 class="hero-title">مرحباً، {{ explode(' ', $student->name)[0] }} 👋</h1>
            <p class="hero-subtitle">نتمنى لك فصلاً دراسياً مليئاً بالإنجاز والتميز الأكاديمي.</p>

            <div class="hero-stats">
                <div class="stat-item">
                    <div class="stat-value">{{ $subjects->count() }}</div>
                    <div class="stat-label">المواد المسجلة</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-value {{ $absentCount > 0 ? 'text-warning' : '' }}">{{ $absentCount }}</div>
                    <div class="stat-label">أيام الغياب</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-value text-success" style="font-family: 'Plus Jakarta Sans', sans-serif;">{{ $attendancePercentage }}%</div>
                    <div class="stat-label">نسبة الحضور</div>
                </div>
            </div>
        </div>
        <div class="hero-decoration">
            <!-- Abstract Shapes -->
            <div class="circle circle-1"></div>
            <div class="circle circle-2"></div>
            @if($delegate)
            <div class="delegate-card-floating">
                <div class="delegate-avatar">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($delegate->name) }}&background=random" alt="">
                </div>
                <div class="delegate-info">
                    <div class="delegate-label">مندوب الدفعة</div>
                    <div class="delegate-name">{{ $delegate->name }}</div>
                </div>
                <a href="mailto:{{ $delegate->email }}" class="delegate-action" title="تواصل">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Subjects Grid -->
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="section-title">مقرراتي الدراسية</h2>
            <p class="section-subtitle">تابع حالة الحضور والغياب لكل مادة</p>
        </div>
    </div>

    <div class="row g-4">
        @forelse($subjects as $subject)
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('student.subject.show', $subject) }}" class="course-card">
                <div class="course-header">
                    <div class="course-code">{{ $subject->code }}</div>
                    <div class="course-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="course-body">
                    <h3 class="course-title">{{ $subject->name }}</h3>
                    <p class="course-instructor">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        د. {{ $subject->doctor->name ?? 'غير محدد' }}
                    </p>
                </div>

                <!-- On-The-Fly Attendance Calc for Card Preview (Or pass from controller if expensive) -->
                <!-- Assuming $subject->attendances_count or similar is available or we just skip this detail snippet to keep it fast, 
                     OR we rely on global stats. Let's keep it clean for now. -->

                <div class="course-footer">
                    <span class="btn-link">
                        عرض السجل
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </span>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12">
            <div class="empty-state">
                <div class="empty-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <h3>لا توجد مواد مسجلة</h3>
                <p>لم يتم تسجيل أي مواد دراسية لك في هذا الفصل حتى الآن.</p>
            </div>
        </div>
        @endforelse
    </div>

</div>

<style>
    /* Hero Section */
    .welcome-hero {
        background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
        border-radius: 24px;
        padding: 3rem;
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.25), 0 10px 10px -5px rgba(59, 130, 246, 0.1);
    }

    .hero-content {
        position: relative;
        z-index: 2;
        max-width: 600px;
    }

    .badge-glass {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(4px);
        padding: 0.35rem 0.75rem;
        border-radius: 99px;
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .hero-title {
        font-size: 2.25rem;
        font-weight: 800;
        margin-bottom: 0.75rem;
        line-height: 1.2;
    }

    .hero-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-bottom: 2.5rem;
        font-weight: 300;
    }

    .hero-stats {
        display: flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 16px;
        padding: 1rem 2rem;
        width: fit-content;
    }

    .stat-item {
        text-align: center;
        padding: 0 1.5rem;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.75rem;
        opacity: 0.8;
    }

    .stat-divider {
        width: 1px;
        height: 30px;
        background: rgba(255, 255, 255, 0.2);
    }

    /* Abstract Shapes */
    .hero-decoration {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 1;
        pointer-events: none;
    }

    .circle {
        position: absolute;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0) 70%);
    }

    .circle-1 {
        width: 400px;
        height: 400px;
        top: -100px;
        left: -100px;
        opacity: 0.6;
    }

    .circle-2 {
        width: 300px;
        height: 300px;
        bottom: -50px;
        right: -50px;
        opacity: 0.4;
    }

    /* Delegate Card Floating */
    .delegate-card-floating {
        position: absolute;
        top: 2rem;
        left: 2rem;
        background: white;
        padding: 0.75rem;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 5;
        max-width: 250px;
        display: none;
        /* Hidden on mobile */
    }

    @media(min-width: 768px) {
        .delegate-card-floating {
            display: flex;
        }
    }

    .delegate-avatar {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        overflow: hidden;
        background: #f1f5f9;
    }

    .delegate-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .delegate-info {
        flex: 1;
    }

    .delegate-label {
        font-size: 0.65rem;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
    }

    .delegate-name {
        font-size: 0.85rem;
        color: #0f172a;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100px;
    }

    .delegate-action {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #eff6ff;
        color: #3b82f6;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .delegate-action:hover {
        background: #3b82f6;
        color: white;
    }

    /* Section Titles */
    .section-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 0.25rem;
    }

    .section-subtitle {
        color: #64748b;
        font-size: 0.95rem;
    }

    /* Course Cards Styling */
    .course-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        height: 100%;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        position: relative;
        overflow: hidden;
    }

    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.01);
        border-color: #e2e8f0;
    }

    .course-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .course-code {
        background: #f8fafc;
        color: #64748b;
        padding: 0.25rem 0.75rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.025em;
    }

    .course-icon {
        width: 40px;
        height: 40px;
        background: #eff6ff;
        color: #3b82f6;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .course-body {
        margin-bottom: 1.5rem;
        flex: 1;
    }

    .course-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }

    .course-instructor {
        color: #64748b;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .course-footer {
        padding-top: 1rem;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: flex-end;
    }

    .btn-link {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: 0.4rem;
        transition: gap 0.2s;
    }

    .course-card:hover .btn-link {
        gap: 0.6rem;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 1rem;
        background: white;
        border-radius: 20px;
        border: 2px dashed #e2e8f0;
    }

    .empty-icon {
        color: #cbd5e1;
        margin-bottom: 1.5rem;
    }
</style>
@endsection