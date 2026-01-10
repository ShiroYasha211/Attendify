@extends('layouts.student')

@section('title', $subject->name)

@section('content')

<style>
    /* Header Card */
    .header-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .header-gradient {
        height: 8px;
        background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 50%, #a855f7 100%);
    }

    .header-content {
        padding: 1.5rem;
    }

    .header-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.5rem;
    }

    .subject-info h1 {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .subject-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        color: var(--text-secondary);
    }

    .code-badge {
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        color: #4f46e5;
        padding: 0.35rem 0.75rem;
        border-radius: 8px;
        font-family: monospace;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .back-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1rem;
        background: #f1f5f9;
        color: var(--text-secondary);
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
    }

    .back-btn:hover {
        background: #e2e8f0;
        color: var(--text-primary);
    }

    /* Tabs */
    .tabs-container {
        display: flex;
        gap: 0;
        border-bottom: 2px solid #e2e8f0;
        margin: 0 -1.5rem;
        padding: 0 1.5rem;
    }

    .tab-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem 1.5rem;
        background: none;
        border: none;
        font-weight: 600;
        color: var(--text-secondary);
        cursor: pointer;
        position: relative;
        transition: all 0.2s;
    }

    .tab-btn:hover {
        color: var(--primary-color);
    }

    .tab-btn.active-tab {
        color: var(--primary-color);
    }

    .tab-btn.active-tab::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--primary-color);
        border-radius: 3px 3px 0 0;
    }

    .tab-badge {
        background: #fef3c7;
        color: #d97706;
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    /* Overview Stats */
    .overview-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
        text-align: center;
    }

    .progress-ring {
        width: 100px;
        height: 100px;
        margin: 0 auto 1rem;
        position: relative;
    }

    .progress-ring svg {
        width: 100%;
        height: 100%;
        transform: rotate(-90deg);
    }

    .progress-value {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 1.5rem;
        font-weight: 800;
    }

    .stat-box {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
    }

    .stat-label {
        color: var(--text-secondary);
        font-size: 0.9rem;
    }

    /* Content Card */
    .content-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }

    .content-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .content-body {
        padding: 1.5rem;
    }

    /* Assignment Item */
    .assignment-item {
        padding: 1.5rem;
        background: #f8fafc;
        border-radius: 16px;
        border-right: 4px solid #6366f1;
        margin-bottom: 1rem;
        transition: all 0.2s;
    }

    .assignment-item:hover {
        background: #f1f5f9;
    }

    .assignment-item.expired {
        border-right-color: #ef4444;
        opacity: 0.7;
    }

    .assignment-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .assignment-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.85rem;
        color: var(--text-secondary);
        margin-bottom: 0.75rem;
    }

    .assignment-desc {
        color: var(--text-secondary);
        line-height: 1.6;
    }

    /* Attendance Table */
    .attendance-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .attendance-table thead th {
        background: #f8fafc;
        padding: 1rem;
        text-align: right;
        font-weight: 700;
        color: var(--text-secondary);
        font-size: 0.85rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .attendance-table tbody td {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .attendance-table tbody tr:hover {
        background: #f8fafc;
    }

    .status-badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .status-badge.present {
        background: #dcfce7;
        color: #16a34a;
    }

    .status-badge.absent {
        background: #fee2e2;
        color: #dc2626;
    }

    .status-badge.late {
        background: #fef3c7;
        color: #d97706;
    }

    .status-badge.excused {
        background: #dbeafe;
        color: #2563eb;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: var(--text-secondary);
    }

    .empty-icon {
        width: 64px;
        height: 64px;
        background: #f1f5f9;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
    }

    @media (max-width: 768px) {
        .overview-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div x-data="{ activeTab: 'overview' }">

    <!-- Header Card -->
    <div class="header-card">
        <div class="header-gradient"></div>
        <div class="header-content">
            <div class="header-top">
                <div class="subject-info">
                    <h1>{{ $subject->name }}</h1>
                    <div class="subject-meta">
                        <span class="code-badge">{{ $subject->code }}</span>
                        <span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; vertical-align: middle;">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            {{ $subject->doctor->name ?? 'غير محدد' }}
                        </span>
                    </div>
                </div>
                <a href="{{ route('student.subjects.index') }}" class="back-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    عودة للقائمة
                </a>
            </div>

            <div class="tabs-container">
                <button @click="activeTab = 'overview'" :class="{ 'active-tab': activeTab === 'overview' }" class="tab-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                    نظرة عامة
                </button>
                <button @click="activeTab = 'assignments'" :class="{ 'active-tab': activeTab === 'assignments' }" class="tab-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                    التكاليف
                    @if($assignments->count() > 0)
                    <span class="tab-badge">{{ $assignments->count() }}</span>
                    @endif
                </button>
                <button @click="activeTab = 'attendance'" :class="{ 'active-tab': activeTab === 'attendance' }" class="tab-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    سجل الحضور
                </button>
                <button @click="activeTab = 'grades'" :class="{ 'active-tab': activeTab === 'grades' }" class="tab-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                    الدرجات
                    @if($totalGradePercentage !== null)
                    <span class="tab-badge" style="background: {{ $totalGradePercentage >= 50 ? '#dcfce7' : '#fee2e2' }}; color: {{ $totalGradePercentage >= 50 ? '#16a34a' : '#dc2626' }};">{{ $totalGradePercentage }}%</span>
                    @endif
                </button>
            </div>
        </div>
    </div>

    <!-- Overview Tab -->
    <div x-show="activeTab === 'overview'" x-transition>
        <div class="overview-grid">
            <!-- Attendance Ring -->
            <div class="stat-card">
                <div class="progress-ring">
                    <svg viewBox="0 0 36 36">
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e2e8f0" stroke-width="3" />
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="{{ $attendancePercentage >= 75 ? '#10b981' : ($attendancePercentage >= 50 ? '#f59e0b' : '#ef4444') }}" stroke-width="3" stroke-dasharray="{{ $attendancePercentage }}, 100" stroke-linecap="round" />
                    </svg>
                    <div class="progress-value" style="color: {{ $attendancePercentage >= 75 ? '#10b981' : ($attendancePercentage >= 50 ? '#f59e0b' : '#ef4444') }}">{{ $attendancePercentage }}%</div>
                </div>
                <div style="font-weight: 700; color: var(--text-primary);">نسبة الحضور</div>
            </div>

            <!-- Present Count -->
            <div class="stat-card">
                <div class="stat-box">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); color: #16a34a;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <div>
                        <div class="stat-number">{{ $presentCount }}</div>
                        <div class="stat-label">محاضرة حضور</div>
                    </div>
                </div>
            </div>

            <!-- Absent Count -->
            <div class="stat-card">
                <div class="stat-box">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); color: #dc2626;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                    </div>
                    <div>
                        <div class="stat-number">{{ $absentCount }}</div>
                        <div class="stat-label">محاضرة غياب</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignments Tab -->
    <div x-show="activeTab === 'assignments'" x-transition style="display: none;">
        <div class="content-card">
            <div class="content-header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                التكاليف الدراسية
            </div>
            <div class="content-body">
                @forelse($assignments as $assignment)
                @php
                $isExpired = \Carbon\Carbon::parse($assignment->due_date)->isPast();
                @endphp
                <div class="assignment-item {{ $isExpired ? 'expired' : '' }}">
                    <div class="assignment-title">{{ $assignment->title }}</div>
                    <div class="assignment-meta">
                        <span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; vertical-align: middle;">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            تاريخ التسليم: {{ \Carbon\Carbon::parse($assignment->due_date)->format('Y-m-d') }}
                        </span>
                        @if($isExpired)
                        <span style="color: #ef4444; font-weight: 600;">منتهي</span>
                        @else
                        <span style="color: #10b981; font-weight: 600;">متاح</span>
                        @endif
                    </div>
                    <p class="assignment-desc">{{ $assignment->description }}</p>
                </div>
                @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                    </div>
                    <p>لا توجد تكاليف دراسية لهذه المادة حالياً</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Attendance Tab -->
    <div x-show="activeTab === 'attendance'" x-transition style="display: none;">
        <div class="content-card">
            <div class="content-header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                سجل الحضور التفصيلي
            </div>
            <div style="overflow-x: auto;">
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>التاريخ</th>
                            <th style="text-align: center;">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendanceRecords as $index => $record)
                        <tr>
                            <td style="color: var(--text-secondary);">{{ $index + 1 }}</td>
                            <td style="font-weight: 600;">{{ \Carbon\Carbon::parse($record->date)->format('Y-m-d') }}</td>
                            <td style="text-align: center;">
                                @if($record->status == 'present')
                                <span class="status-badge present">حاضر</span>
                                @elseif($record->status == 'absent')
                                <span class="status-badge absent">غائب</span>
                                @elseif($record->status == 'late')
                                <span class="status-badge late">متأخر</span>
                                @elseif($record->status == 'excused')
                                <span class="status-badge excused">بعذر</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3">
                                <div class="empty-state">
                                    <p>لا يوجد سجلات حضور حتى الآن</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Grades Tab -->
    <div x-show="activeTab === 'grades'" x-transition style="display: none;">
        <div class="content-card">
            <div class="content-header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
                درجات هذا المقرر
            </div>
            <div class="content-body">
                @if($continuousGrade || $finalGrade)
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">

                    <!-- Continuous Grade -->
                    <div style="padding: 1.5rem; background: #f8fafc; border-radius: 16px; border-right: 4px solid #f59e0b;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #d97706;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                    </svg>
                                </div>
                                <div>
                                    <div style="font-weight: 700; font-size: 1.1rem; color: var(--text-primary);">درجة المحصلة</div>
                                    <div style="font-size: 0.85rem; color: var(--text-secondary);">الأعمال الفصلية (40%)</div>
                                </div>
                            </div>
                            <div style="text-align: left;">
                                @if($continuousGrade)
                                <div style="font-size: 1.75rem; font-weight: 800; color: {{ ($continuousGrade->score / $continuousGrade->max_score) >= 0.5 ? '#16a34a' : '#dc2626' }};">{{ number_format($continuousGrade->score, 1) }}</div>
                                <div style="font-size: 0.9rem; color: var(--text-secondary);">من {{ $continuousGrade->max_score }}</div>
                                @else
                                <div style="font-size: 1.1rem; color: var(--text-secondary);">لم تُدخل</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Final Grade -->
                    <div style="padding: 1.5rem; background: #f8fafc; border-radius: 16px; border-right: 4px solid #3b82f6;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #2563eb;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                </div>
                                <div>
                                    <div style="font-weight: 700; font-size: 1.1rem; color: var(--text-primary);">درجة النهائي</div>
                                    <div style="font-size: 0.85rem; color: var(--text-secondary);">الاختبار النهائي (60%)</div>
                                </div>
                            </div>
                            <div style="text-align: left;">
                                @if($finalGrade)
                                <div style="font-size: 1.75rem; font-weight: 800; color: {{ ($finalGrade->score / $finalGrade->max_score) >= 0.5 ? '#16a34a' : '#dc2626' }};">{{ number_format($finalGrade->score, 1) }}</div>
                                <div style="font-size: 0.9rem; color: var(--text-secondary);">من {{ $finalGrade->max_score }}</div>
                                @else
                                <div style="font-size: 1.1rem; color: var(--text-secondary);">لم تُدخل</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Total -->
                    @if($totalGradePercentage !== null)
                    <div style="padding: 1.5rem; background: linear-gradient(135deg, {{ $totalGradePercentage >= 85 ? '#dcfce7, #bbf7d0' : ($totalGradePercentage >= 70 ? '#dbeafe, #bfdbfe' : ($totalGradePercentage >= 50 ? '#fef3c7, #fde68a' : '#fee2e2, #fecaca')) }}); border-radius: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="font-weight: 700; font-size: 1.2rem; color: var(--text-primary);">المجموع الكلي</div>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="font-size: 2.5rem; font-weight: 800; color: {{ $totalGradePercentage >= 85 ? '#16a34a' : ($totalGradePercentage >= 70 ? '#2563eb' : ($totalGradePercentage >= 50 ? '#d97706' : '#dc2626')) }};">{{ $totalGradePercentage }}%</div>
                                <div style="padding: 0.5rem 1rem; background: rgba(255,255,255,0.7); border-radius: 10px; font-weight: 700; color: {{ $totalGradePercentage >= 85 ? '#16a34a' : ($totalGradePercentage >= 70 ? '#2563eb' : ($totalGradePercentage >= 50 ? '#d97706' : '#dc2626')) }};">
                                    @if($totalGradePercentage >= 85)
                                    ممتاز
                                    @elseif($totalGradePercentage >= 70)
                                    جيد جداً
                                    @elseif($totalGradePercentage >= 50)
                                    مقبول
                                    @else
                                    راسب
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
                @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <line x1="18" y1="20" x2="18" y2="10"></line>
                            <line x1="12" y1="20" x2="12" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="14"></line>
                        </svg>
                    </div>
                    <p>لم يتم إدخال درجات لهذا المقرر بعد</p>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

@endsection