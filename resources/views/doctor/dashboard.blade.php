@extends('layouts.doctor')

@section('title', 'لوحة التحكم')

@section('content')

<style>
    .dashboard-header {
        margin-bottom: 2rem;
    }

    .welcome-text h1 {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .welcome-text p {
        color: var(--text-secondary);
        font-size: 0.95rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px -4px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon.primary {
        background: rgba(79, 70, 229, 0.1);
        color: #4f46e5;
    }

    .stat-icon.info {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .stat-icon.warning {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .stat-icon.success {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .stat-icon.danger {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .stat-content h3 {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .stat-content span {
        color: var(--text-secondary);
        font-size: 0.85rem;
    }

    .main-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
    }

    @media (max-width: 1024px) {
        .main-grid {
            grid-template-columns: 1fr;
        }
    }

    .card-section {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .weekly-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .weekly-stat-card {
        border-radius: 14px;
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .weekly-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .weekly-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .weekly-stat-label {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }

    .weekly-stat-percent {
        margin-right: auto;
        font-size: 1.25rem;
        font-weight: 700;
    }

    .daily-bars {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .daily-bar-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .daily-bar-label {
        width: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .daily-bar-track {
        flex: 1;
        height: 10px;
        background: #e2e8f0;
        border-radius: 5px;
        overflow: hidden;
    }

    .daily-bar-fill {
        height: 100%;
        border-radius: 5px;
        transition: width 0.5s ease;
    }

    .daily-bar-value {
        width: 50px;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-align: left;
    }

    .chart-container {
        height: 280px;
        position: relative;
    }

    .activities-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .activity-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.875rem;
        border-radius: 12px;
        background: #f8fafc;
        transition: background 0.2s;
    }

    .activity-item:hover {
        background: #f1f5f9;
    }

    .activity-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .activity-icon.excuse {
        background: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
    }

    .activity-icon.inquiry {
        background: rgba(139, 92, 246, 0.15);
        color: #8b5cf6;
    }

    .activity-content {
        flex: 1;
        min-width: 0;
    }

    .activity-title {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.9rem;
        margin-bottom: 0.15rem;
    }

    .activity-subtitle {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }

    .activity-badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        font-weight: 600;
    }

    .activity-badge.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .activity-badge.forwarded {
        background: #e0e7ff;
        color: #4338ca;
    }

    .activity-badge.answered {
        background: #d1fae5;
        color: #065f46;
    }

    .activity-badge.accepted {
        background: #d1fae5;
        color: #065f46;
    }

    .activity-badge.rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .subjects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .subject-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        padding: 1.25rem;
        transition: all 0.2s;
    }

    .subject-card:hover {
        border-color: var(--primary-color);
        box-shadow: 0 4px 12px -4px rgba(79, 70, 229, 0.15);
    }

    .subject-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .subject-name {
        font-weight: 700;
        font-size: 1.05rem;
        color: var(--text-primary);
    }

    .subject-meta {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }

    .subject-stats {
        display: flex;
        gap: 1.5rem;
        margin-bottom: 1rem;
    }

    .subject-stat {
        text-align: center;
    }

    .subject-stat-value {
        font-size: 1.25rem;
        font-weight: 700;
    }

    .subject-stat-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .attendance-bar {
        height: 6px;
        background: #e2e8f0;
        border-radius: 3px;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }

    .attendance-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.5s ease;
    }

    .subject-action {
        display: block;
        text-align: center;
        padding: 0.6rem;
        background: #f8fafc;
        border-radius: 10px;
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
        transition: background 0.2s;
    }

    .subject-action:hover {
        background: #eff6ff;
    }

    .empty-state {
        text-align: center;
        padding: 2rem;
        color: var(--text-secondary);
    }
</style>

<!-- Header -->
<div class="dashboard-header">
    <div class="welcome-text">
        <h1>مرحباً د. {{ $doctor->name }} 👋</h1>
        <p>إليك نظرة عامة على نشاطك الأكاديمي</p>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
        </div>
        <div class="stat-content">
            <h3>{{ $subjects->count() }}</h3>
            <span>مقرر دراسي</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div class="stat-content">
            <h3>{{ $studentsCount }}</h3>
            <span>طالب</span>
        </div>
    </div>

    <a href="{{ route('doctor.excuses.index') }}" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon warning">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
        </div>
        <div class="stat-content">
            <h3>{{ $pendingExcusesCount }}</h3>
            <span>أعذار معلقة</span>
        </div>
    </a>

    <a href="{{ route('doctor.inquiries.index') }}" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon danger">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </div>
        <div class="stat-content">
            <h3>{{ $pendingInquiriesCount }}</h3>
            <span>استفسارات جديدة</span>
        </div>
    </a>

    <a href="{{ route('doctor.messages.index') }}" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon success">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
        </div>
        <div class="stat-content">
            <h3>{{ $unreadMessagesCount }}</h3>
            <span>رسائل جديدة</span>
        </div>
    </a>
</div>

<div class="main-grid">
    <!-- Attendance Stats Section -->
    <div class="card-section">
        <div class="section-header">
            <h3 class="section-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary-color);">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
                إحصائيات الحضور (آخر 7 أيام)
            </h3>
        </div>
        <div class="weekly-stats">
            @php
            $totalPresent = array_sum($attendanceChartData['present'] ?? []);
            $totalAbsent = array_sum($attendanceChartData['absent'] ?? []);
            $totalAll = $totalPresent + $totalAbsent;
            $presentPercent = $totalAll > 0 ? round(($totalPresent / $totalAll) * 100) : 0;
            $absentPercent = $totalAll > 0 ? round(($totalAbsent / $totalAll) * 100) : 0;
            @endphp

            <div class="weekly-stat-card" style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border: 1px solid #6ee7b7;">
                <div class="weekly-stat-icon" style="background: #10b981; color: white;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <div class="weekly-stat-info">
                    <div class="weekly-stat-value" style="color: #065f46;">{{ $totalPresent }}</div>
                    <div class="weekly-stat-label">حضور</div>
                </div>
                <div class="weekly-stat-percent" style="color: #059669;">{{ $presentPercent }}%</div>
            </div>

            <div class="weekly-stat-card" style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border: 1px solid #f87171;">
                <div class="weekly-stat-icon" style="background: #ef4444; color: white;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </div>
                <div class="weekly-stat-info">
                    <div class="weekly-stat-value" style="color: #991b1b;">{{ $totalAbsent }}</div>
                    <div class="weekly-stat-label">غياب</div>
                </div>
                <div class="weekly-stat-percent" style="color: #dc2626;">{{ $absentPercent }}%</div>
            </div>
        </div>

        <!-- Daily bars -->
        <div class="daily-bars">
            @foreach($attendanceLabels as $index => $label)
            @php
            $dayPresent = $attendanceChartData['present'][$index] ?? 0;
            $dayAbsent = $attendanceChartData['absent'][$index] ?? 0;
            $dayTotal = $dayPresent + $dayAbsent;
            $dayPercent = $dayTotal > 0 ? round(($dayPresent / $dayTotal) * 100) : 0;
            @endphp
            <div class="daily-bar-item">
                <div class="daily-bar-label">{{ $label }}</div>
                <div class="daily-bar-track">
                    <div class="daily-bar-fill" style="width: {{ $dayPercent }}%; background: {{ $dayPercent >= 70 ? '#10b981' : ($dayPercent >= 50 ? '#f59e0b' : '#ef4444') }};"></div>
                </div>
                <div class="daily-bar-value">{{ $dayPresent }}/{{ $dayTotal }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="card-section">
        <div class="section-header">
            <h3 class="section-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--warning-color);">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                آخر النشاطات
            </h3>
        </div>
        <div class="activities-list">
            @forelse($recentActivities as $activity)
            <div class="activity-item">
                <div class="activity-icon {{ $activity['type'] }}">
                    @if($activity['type'] == 'excuse')
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                    @else
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    @endif
                </div>
                <div class="activity-content">
                    <div class="activity-title">{{ $activity['title'] }}</div>
                    <div class="activity-subtitle">{{ $activity['subtitle'] }} • {{ $activity['date']->diffForHumans() }}</div>
                </div>
                <span class="activity-badge {{ $activity['status'] }}">
                    @switch($activity['status'])
                    @case('pending') معلق @break
                    @case('forwarded') محوّل @break
                    @case('answered') تم الرد @break
                    @case('accepted') مقبول @break
                    @case('rejected') مرفوض @break
                    @default {{ $activity['status'] }}
                    @endswitch
                </span>
            </div>
            @empty
            <div class="empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: #cbd5e1; margin-bottom: 0.5rem;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <p>لا توجد نشاطات حديثة</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Subjects Section -->
<div class="card-section" style="margin-top: 1.5rem;">
    <div class="section-header">
        <h3 class="section-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary-color);">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
            المقررات الدراسية
        </h3>
    </div>

    <div class="subjects-grid">
        @forelse($subjects as $subject)
        <div class="subject-card">
            <div class="subject-header">
                <div>
                    <div class="subject-name">{{ $subject->name }}</div>
                    <div class="subject-meta">{{ $subject->major->name ?? '' }} • {{ $subject->level->name ?? '' }}</div>
                </div>
            </div>

            <div class="subject-stats">
                <div class="subject-stat">
                    <div class="subject-stat-value" style="color: var(--info-color);">{{ $subject->students_count }}</div>
                    <div class="subject-stat-label">طالب</div>
                </div>
                <div class="subject-stat">
                    <div class="subject-stat-value" style="color: {{ $subject->attendance_rate >= 70 ? '#10b981' : ($subject->attendance_rate >= 50 ? '#f59e0b' : '#ef4444') }};">{{ $subject->attendance_rate }}%</div>
                    <div class="subject-stat-label">نسبة الحضور</div>
                </div>
            </div>

            <div class="attendance-bar">
                <div class="attendance-fill" style="width: {{ $subject->attendance_rate }}%; background: {{ $subject->attendance_rate >= 70 ? '#10b981' : ($subject->attendance_rate >= 50 ? '#f59e0b' : '#ef4444') }};"></div>
            </div>

            <a href="{{ route('doctor.reports.show', $subject->id) }}" class="subject-action">
                عرض التقرير الكامل
            </a>
        </div>
        @empty
        <div class="empty-state" style="grid-column: 1 / -1;">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: #cbd5e1; margin-bottom: 1rem;">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
            <p>لم يتم إسناد أي مقررات دراسية لك بعد</p>
        </div>
        @endforelse
    </div>
</div>



@endsection