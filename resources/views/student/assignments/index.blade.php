@extends('layouts.student')

@section('title', 'التكاليف والواجبات')

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

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.25rem;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.06);
    }

    .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text-primary);
    }

    .stat-label {
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    /* Tabs */
    .tabs-wrapper {
        background: white;
        border-radius: 16px;
        padding: 0.5rem;
        display: inline-flex;
        gap: 0.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .tab-btn {
        background: transparent;
        border: none;
        padding: 0.85rem 1.5rem;
        font-family: inherit;
        font-weight: 700;
        color: var(--text-secondary);
        cursor: pointer;
        border-radius: 12px;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .tab-btn:hover {
        color: var(--primary-color);
        background: #f8fafc;
    }

    .tab-btn.active-tab {
        color: white;
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        box-shadow: 0 4px 12px -2px rgba(79, 70, 229, 0.35);
    }

    .tab-btn .badge {
        background: rgba(255, 255, 255, 0.25);
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        font-size: 0.75rem;
    }

    /* Assignment Cards */
    .assignments-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 1.5rem;
    }

    .assignment-card {
        background: white;
        border-radius: 20px;
        border: 2px solid transparent;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .assignment-card:hover {
        transform: translateY(-5px);
        border-color: #e2e8f0;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .card-stripe {
        height: 5px;
        background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 100%);
    }

    .card-stripe.urgent {
        background: linear-gradient(90deg, #dc2626 0%, #ef4444 100%);
    }

    .card-stripe.soon {
        background: linear-gradient(90deg, #f59e0b 0%, #fbbf24 100%);
    }

    .card-body {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .card-header-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .subject-badge {
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        color: #4f46e5;
        padding: 0.4rem 0.85rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.8rem;
    }

    .deadline-badge {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.8rem;
        font-weight: 700;
        padding: 0.4rem 0.75rem;
        border-radius: 999px;
    }

    .deadline-badge.urgent {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    .deadline-badge.soon {
        background: #fffbeb;
        color: #d97706;
        border: 1px solid #fde68a;
    }

    .deadline-badge.normal {
        background: #f0fdf4;
        color: #16a34a;
        border: 1px solid #bbf7d0;
    }

    .assignment-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.75rem;
        line-height: 1.4;
    }

    .assignment-desc {
        color: var(--text-secondary);
        font-size: 0.9rem;
        line-height: 1.6;
        flex: 1;
        margin-bottom: 1rem;
    }

    .card-footer {
        border-top: 1px solid #f1f5f9;
        padding-top: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .due-date {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    .details-btn {
        padding: 0.6rem 1.25rem;
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        color: white;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.85rem;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .details-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px -2px rgba(79, 70, 229, 0.4);
        color: white;
    }

    /* Past Assignments Table */
    .table-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }

    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .modern-table thead th {
        background: #f8fafc;
        padding: 1rem 1.25rem;
        font-weight: 700;
        color: var(--text-secondary);
        font-size: 0.85rem;
        text-align: right;
        border-bottom: 2px solid #e2e8f0;
    }

    .modern-table tbody tr {
        transition: all 0.2s;
    }

    .modern-table tbody tr:hover {
        background: #fafbfc;
    }

    .modern-table tbody td {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .past-badge {
        background: #f1f5f9;
        color: #64748b;
        padding: 0.35rem 0.75rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.8rem;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
    }

    .empty-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        color: #16a34a;
    }
</style>

<div class="page-header">
    <h1 class="page-title">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary-color);">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
        </svg>
        التكاليف والواجبات
    </h1>
    <p class="page-subtitle">عرض التكاليف المطلوبة والواجبات المنزلية</p>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); color: #2563eb;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>
        <div>
            <div class="stat-value">{{ $activeAssignments->count() }}</div>
            <div class="stat-label">تكليف نشط</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); color: #16a34a;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>
        <div>
            <div class="stat-value">{{ $pastAssignments->count() }}</div>
            <div class="stat-label">تكليف منتهي</div>
        </div>
    </div>

    @php
    $urgentCount = $activeAssignments->filter(function($a) {
    return (int) floor(\Carbon\Carbon::now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($a->due_date)->startOfDay(), false)) <= 2;
        })->count();
        @endphp
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); color: #dc2626;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
            <div>
                <div class="stat-value">{{ $urgentCount }}</div>
                <div class="stat-label">تسليم قريب</div>
            </div>
        </div>
</div>

<div x-data="{ activeTab: 'active' }">

    <!-- Modern Tabs -->
    <div class="tabs-wrapper">
        <button @click="activeTab = 'active'" :class="{ 'active-tab': activeTab === 'active' }" class="tab-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            التكاليف الحالية
            @if($activeAssignments->count() > 0)
            <span class="badge">{{ $activeAssignments->count() }}</span>
            @endif
        </button>
        <button @click="activeTab = 'past'" :class="{ 'active-tab': activeTab === 'past' }" class="tab-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            التكاليف المنتهية
        </button>
    </div>

    <!-- Active Assignments -->
    <div x-show="activeTab === 'active'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        @if($activeAssignments->count() > 0)
        <div class="assignments-grid">
            @foreach($activeAssignments as $assignment)
            @php
            $daysLeft = (int) floor(\Carbon\Carbon::now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($assignment->due_date)->startOfDay(), false));
            $urgencyClass = $daysLeft <= 1 ? 'urgent' : ($daysLeft <=3 ? 'soon' : 'normal' );
                $stripeClass=$daysLeft <=1 ? 'urgent' : ($daysLeft <=3 ? 'soon' : '' );
                @endphp
                <div class="assignment-card">
                <div class="card-stripe {{ $stripeClass }}"></div>
                <div class="card-body">
                    <div class="card-header-row">
                        <span class="subject-badge">{{ $assignment->subject->name }}</span>
                        <span class="deadline-badge {{ $urgencyClass }}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            @if($daysLeft <= 0)
                                اليوم!
                                @elseif($daysLeft==1)
                                غداً
                                @else
                                {{ $daysLeft }} أيام
                                @endif
                                </span>
                    </div>

                    <h3 class="assignment-title">{{ $assignment->title }}</h3>
                    <p class="assignment-desc">{{ \Illuminate\Support\Str::limit($assignment->description, 120) }}</p>

                    <div class="card-footer">
                        <div class="due-date">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            {{ \Carbon\Carbon::parse($assignment->due_date)->format('Y-m-d') }}
                        </div>
                        <a href="{{ route('student.subjects.show', ['subject' => $assignment->subject_id]) }}" class="details-btn">
                            عرض التفاصيل
                        </a>
                    </div>
                </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <div class="empty-icon">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>
        <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">🎉 لا توجد واجبات نشطة</h3>
        <p style="color: var(--text-secondary);">ممتاز! لقد أنجزت جميع واجباتك الحالية.</p>
    </div>
    @endif
</div>

<!-- Past Assignments -->
<div x-show="activeTab === 'past'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display: none;">
    @if($pastAssignments->count() > 0)
    <div class="table-card">
        <table class="modern-table">
            <thead>
                <tr>
                    <th>العنوان</th>
                    <th>المادة</th>
                    <th>تاريخ الاستحقاق</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pastAssignments as $assignment)
                <tr>
                    <td style="font-weight: 700;">{{ $assignment->title }}</td>
                    <td>
                        <span style="background: #f1f5f9; padding: 0.35rem 0.75rem; border-radius: 8px; font-size: 0.85rem;">
                            {{ $assignment->subject->name }}
                        </span>
                    </td>
                    <td style="color: var(--text-secondary);">{{ \Carbon\Carbon::parse($assignment->due_date)->format('Y-m-d') }}</td>
                    <td>
                        <span class="past-badge">✓ منتهي</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state">
        <div class="empty-icon" style="background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); color: #64748b;">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
        </div>
        <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">لا يوجد سجل</h3>
        <p style="color: var(--text-secondary);">لا يوجد سجل واجبات سابقة.</p>
    </div>
    @endif
</div>

</div>

@endsection