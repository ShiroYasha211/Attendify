@extends('layouts.student')

@section('title', 'الجدول الدراسي')

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

    /* Today Banner */
    .today-banner {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        border-radius: 20px;
        padding: 1.5rem;
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .today-banner::before {
        content: '';
        position: absolute;
        top: -30px;
        left: -30px;
        width: 120px;
        height: 120px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .today-banner::after {
        content: '';
        position: absolute;
        bottom: -40px;
        right: 10%;
        width: 100px;
        height: 100px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
    }

    /* Lecture Card */
    .lecture-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        padding: 1.25rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        transition: all 0.2s;
    }

    .lecture-card:hover {
        transform: translateX(-4px);
        box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.08);
    }

    .lecture-time {
        min-width: 80px;
        text-align: center;
        padding-left: 1.25rem;
        border-left: 2px solid #e2e8f0;
    }

    .lecture-time .start {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--primary-color);
    }

    .lecture-time .end {
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    .lecture-info {
        flex: 1;
    }

    .lecture-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.35rem;
    }

    .lecture-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    .lecture-meta span {
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .status-badge {
        padding: 0.4rem 0.85rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.8rem;
    }

    .status-badge.now {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        color: #16a34a;
    }

    .status-badge.upcoming {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #2563eb;
    }

    .status-badge.ended {
        background: #f1f5f9;
        color: #64748b;
    }

    /* Sections */
    .section-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .section-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--text-primary);
    }

    .section-body {
        padding: 1.5rem;
    }

    /* Two Column Layout */
    .two-column {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    /* Tomorrow Card */
    .tomorrow-card {
        background: linear-gradient(145deg, #f8fafc, #ffffff);
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 1.5rem;
        height: fit-content;
    }

    .tomorrow-item {
        display: flex;
        gap: 1rem;
        padding: 0.75rem 0;
        border-bottom: 1px dashed #e2e8f0;
    }

    .tomorrow-item:last-child {
        border-bottom: none;
    }

    .tomorrow-time {
        font-weight: 700;
        color: var(--primary-color);
        min-width: 50px;
    }

    /* Weekly Table */
    .weekly-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .weekly-table thead th {
        background: #f8fafc;
        padding: 1rem;
        text-align: right;
        font-weight: 700;
        color: var(--text-secondary);
        font-size: 0.9rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .weekly-table tbody td {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: top;
    }

    .weekly-table tbody tr:hover {
        background: #fafbfc;
    }

    .day-cell {
        font-weight: 700;
        background: #f8fafc;
        color: var(--text-primary);
    }

    .lecture-chip {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        display: inline-block;
        min-width: 200px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }

    .lecture-chip:last-child {
        margin-bottom: 0;
    }

    .lecture-chip .name {
        font-weight: 700;
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
    }

    .lecture-chip .details {
        display: flex;
        justify-content: space-between;
        font-size: 0.8rem;
        color: var(--text-secondary);
    }

    .lecture-chip .hall {
        color: var(--primary-color);
        font-weight: 600;
    }

    .empty-day {
        color: var(--text-light);
        font-size: 0.9rem;
    }

    @media (max-width: 900px) {
        .two-column {
            grid-template-columns: 1fr;
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
        الجدول الدراسي
    </h1>
    <p class="page-subtitle">جدول المحاضرات الأسبوعي ومحاضرات اليوم</p>
</div>

<!-- Today Banner -->
<div class="today-banner">
    <div style="position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 0.5rem;">📅 اليوم</div>
            <div style="font-size: 1.5rem; font-weight: 800;">{{ now()->locale('ar')->translatedFormat('l, d F Y') }}</div>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 900; line-height: 1;">{{ $todayLectures->count() }}</div>
            <div style="font-size: 0.85rem; opacity: 0.9;">محاضرة اليوم</div>
        </div>
    </div>
</div>

<div class="two-column">
    <!-- Today's Lectures -->
    <div>
        <div class="section-card">
            <div class="section-header">
                <div style="width: 10px; height: 10px; background: #10b981; border-radius: 50%;"></div>
                محاضرات اليوم
            </div>
            <div class="section-body">
                @if($todayLectures->count() > 0)
                @foreach($todayLectures as $lecture)
                <div class="lecture-card">
                    <div class="lecture-time">
                        <div class="start">{{ \Carbon\Carbon::parse($lecture->start_time)->format('H:i') }}</div>
                        <div class="end">{{ \Carbon\Carbon::parse($lecture->end_time)->format('H:i') }}</div>
                    </div>
                    <div class="lecture-info">
                        <div class="lecture-name">{{ $lecture->subject->name }}</div>
                        <div class="lecture-meta">
                            <span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                {{ $lecture->subject->doctor->name ?? 'غير محدد' }}
                            </span>
                            <span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                </svg>
                                {{ $lecture->hall_name ?? 'غير محدد' }}
                            </span>
                        </div>
                    </div>
                    @php
                    $now = \Carbon\Carbon::now();
                    $start = \Carbon\Carbon::parse($lecture->start_time);
                    $end = \Carbon\Carbon::parse($lecture->end_time);
                    @endphp
                    @if($now->between($start, $end))
                    <span class="status-badge now">🟢 جاري الآن</span>
                    @elseif($now->gt($end))
                    <span class="status-badge ended">انتهت</span>
                    @else
                    <span class="status-badge upcoming">قادمة</span>
                    @endif
                </div>
                @endforeach
                @else
                <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity: 0.5; margin-bottom: 1rem;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                        <line x1="9" y1="9" x2="9.01" y2="9"></line>
                        <line x1="15" y1="9" x2="15.01" y2="9"></line>
                    </svg>
                    <p style="margin: 0;">لا توجد محاضرات اليوم - استمتع بوقتك! 🎉</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Tomorrow Preview -->
    <div class="tomorrow-card">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; color: var(--text-secondary); font-weight: 700;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 12h14"></path>
                <path d="M12 5l7 7-7 7"></path>
            </svg>
            ما الذي ينتظرك غداً؟
        </div>
        @if($tomorrowLectures->count() > 0)
        @foreach($tomorrowLectures as $lecture)
        <div class="tomorrow-item">
            <div class="tomorrow-time">{{ \Carbon\Carbon::parse($lecture->start_time)->format('H:i') }}</div>
            <div>
                <div style="font-weight: 600; font-size: 0.95rem;">{{ $lecture->subject->name }}</div>
                <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ $lecture->hall_name }}</div>
            </div>
        </div>
        @endforeach
        @else
        <p style="color: var(--text-secondary); font-size: 0.9rem;">لا توجد محاضرات غداً 🙌</p>
        @endif
    </div>
</div>

<!-- Weekly Schedule -->
<div class="section-card">
    <div class="section-header">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        الجدول الأسبوعي الكامل
    </div>
    <div style="overflow-x: auto;">
        <table class="weekly-table">
            <thead>
                <tr>
                    <th style="width: 120px;">اليوم</th>
                    <th>المحاضرات</th>
                </tr>
            </thead>
            <tbody>
                @php
                $days = [
                6 => 'السبت',
                7 => 'الأحد',
                1 => 'الإثنين',
                2 => 'الثلاثاء',
                3 => 'الأربعاء',
                4 => 'الخميس',
                5 => 'الجمعة',
                ];
                @endphp

                @foreach($days as $key => $dayName)
                <tr>
                    <td class="day-cell">{{ $dayName }}</td>
                    <td>
                        @if(isset($weeklySchedule[$key]) && $weeklySchedule[$key]->count() > 0)
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            @foreach($weeklySchedule[$key] as $lecture)
                            <div class="lecture-chip">
                                <div class="name">{{ $lecture->subject->name }}</div>
                                <div class="details">
                                    <span>{{ \Carbon\Carbon::parse($lecture->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($lecture->end_time)->format('H:i') }}</span>
                                    <span class="hall">{{ $lecture->hall_name }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <span class="empty-day">-- لا توجد محاضرات --</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection