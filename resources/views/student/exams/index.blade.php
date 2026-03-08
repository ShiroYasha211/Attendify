@extends('layouts.student')

@section('title', 'جداول الاختبارات')

@section('content')

<style>
    .page-header {
        margin-bottom: 2rem;
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .page-subtitle {
        color: var(--text-secondary);
    }

    /* Exam Schedule Container */
    .exam-schedule {
        background: white;
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-bottom: 3rem;
    }

    .schedule-header {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: white;
        padding: 1.75rem;
        position: relative;
        overflow: hidden;
    }

    .schedule-header::before {
        content: '';
        position: absolute;
        top: -30px;
        left: -30px;
        width: 100px;
        height: 100px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .schedule-header::after {
        content: '';
        position: absolute;
        bottom: -40px;
        right: 5%;
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
    }

    .schedule-title {
        font-size: 1.4rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
    }

    .schedule-meta {
        display: flex;
        gap: 1.5rem;
        font-size: 0.9rem;
        opacity: 0.9;
        position: relative;
        z-index: 1;
    }

    .schedule-meta span {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Layout */
    .schedule-content {
        display: grid;
        grid-template-columns: 1fr 280px;
        min-height: 400px;
    }

    /* Info Sidebar */
    .info-sidebar {
        background: #f8fafc;
        border-right: 1px solid #e2e8f0;
        padding: 1.5rem;
    }

    .info-title {
        font-weight: 700;
        color: var(--text-secondary);
        font-size: 0.85rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-box {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        text-align: center;
        border: 1px solid #e2e8f0;
    }

    .stat-number {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--primary-color);
    }

    .stat-label {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }

    .notes-box {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem;
        font-size: 0.9rem;
        line-height: 1.7;
        color: var(--text-primary);
    }

    /* Exam Table */
    .exam-table-container {
        padding: 1.5rem;
    }

    .exam-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .exam-table thead th {
        background: #f8fafc;
        padding: 1rem 1.25rem;
        text-align: right;
        font-weight: 700;
        color: var(--text-secondary);
        font-size: 0.85rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .exam-table thead th:first-child {
        border-radius: 12px 0 0 0;
    }

    .exam-table thead th:last-child {
        border-radius: 0 12px 0 0;
    }

    .exam-table tbody tr {
        transition: all 0.2s;
    }

    .exam-table tbody tr:hover {
        background: #fafbfc;
    }

    .exam-table tbody td {
        padding: 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .subject-cell {
        font-weight: 700;
        color: var(--text-primary);
    }

    .subject-code {
        font-size: 0.8rem;
        color: var(--text-secondary);
        font-family: monospace;
    }

    .date-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .day-badge {
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        color: var(--primary-color);
        padding: 0.3rem 0.6rem;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.75rem;
        min-width: 70px;
        text-align: center;
    }

    .date-text {
        font-family: monospace;
        font-size: 0.95rem;
        font-weight: 600;
    }

    .time-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: white;
        border: 1px solid #e2e8f0;
        padding: 0.5rem 0.85rem;
        border-radius: 20px;
        font-size: 0.85rem;
    }

    .time-label {
        color: var(--text-secondary);
        font-size: 0.75rem;
    }

    .time-value {
        font-weight: 700;
    }

    .location-cell {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-secondary);
    }

    .location-cell svg {
        color: #dc2626;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 5rem 2rem;
        background: #f8fafc;
        border: 2px dashed #e2e8f0;
        border-radius: 20px;
    }

    .empty-icon {
        width: 80px;
        height: 80px;
        background: #f1f5f9;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        color: #cbd5e1;
    }

    @media (max-width: 900px) {
        .schedule-content {
            grid-template-columns: 1fr;
        }

        .info-sidebar {
            border-left: none;
            border-top: 1px solid #e2e8f0;
        }
    }

    @media print {

        .sidebar,
        .top-header,
        .btn-group,
        .btn {
            display: none !important;
        }

        .main-content {
            margin: 0 !important;
            width: 100% !important;
        }

        .exam-schedule {
            box-shadow: none !important;
            border: 1px solid #ccc !important;
        }
    }
</style>

<div class="page-header">
    <h1 class="page-title">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary-color);">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        جداول الاختبارات
    </h1>
    <p class="page-subtitle">استعراض جداول الاختبارات للفصل الدراسي الحالي</p>
</div>

@forelse($schedules as $exam)
<div class="exam-schedule">
    <!-- Header -->
    <div class="schedule-header">
        <div class="schedule-title">{{ $exam->title }}</div>
        <div class="schedule-meta">
            <span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                {{ $exam->term->name ?? '-' }}
            </span>
            <span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                نشر في: {{ $exam->created_at->format('Y/m/d') }}
            </span>
        </div>
    </div>

    <!-- Content -->
    <div class="schedule-content">
        <!-- Main Table -->
        <div class="exam-table-container">
            <div class="table-responsive">
<table class="exam-table">
                <thead>
                    <tr>
                        <th>المادة</th>
                        <th>اليوم والتاريخ</th>
                        <th>الوقت</th>
                        <th>المكان</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exam->items->sortBy('exam_date') as $item)
                    <tr>
                        <td>
                            <div class="subject-cell">{{ $item->subject->name }}</div>
                            @if($item->subject->code)
                            <div class="subject-code">{{ $item->subject->code }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="date-cell">
                                <span class="day-badge">{{ $item->exam_date->locale('ar')->translatedFormat('l') }}</span>
                                <span class="date-text">{{ $item->exam_date->format('Y/m/d') }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="time-badge">
                                <span class="time-label">من</span>
                                <span class="time-value">{{ \Carbon\Carbon::parse($item->start_time)->format('h:i') }}</span>
                                <span class="time-label">إلى</span>
                                <span class="time-value">{{ \Carbon\Carbon::parse($item->end_time)->format('h:i') }}</span>
                                <span class="time-label">{{ \Carbon\Carbon::parse($item->start_time)->format('A') }}</span>
                            </div>
                        </td>
                        <td>
                            @if($item->location)
                            <div class="location-cell">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                {{ $item->location }}
                            </div>
                            @else
                            <span style="color: var(--text-light); font-size: 0.85rem;">غير محدد</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                            لا توجد مواد مضافة لهذا الجدول بعد
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
</div>
        </div>

        <!-- Info Sidebar -->
        <div class="info-sidebar">
            <div class="info-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                بيانات الجدول
            </div>

            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-number">{{ $exam->items->count() }}</div>
                    <div class="stat-label">عدد المواد</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" style="font-size: 1rem;">{{ $exam->term->name ?? '-' }}</div>
                    <div class="stat-label">الفصل</div>
                </div>
            </div>

            @if($exam->description)
            <div class="info-title" style="margin-top: 1rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                ملاحظات
            </div>
            <div class="notes-box">{{ $exam->description }}</div>
            @endif
        </div>
    </div>
</div>
@empty
<div class="empty-state">
    <div class="empty-icon">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
    </div>
    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">لا توجد جداول اختبارات</h3>
    <p style="color: var(--text-secondary);">لم يتم نشر جداول اختبارات لتخصصك حالياً</p>
</div>
@endforelse

@endsection