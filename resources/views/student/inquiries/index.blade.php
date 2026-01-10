@extends('layouts.student')

@section('title', 'استفسارات الدكتور')

@section('content')

<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .new-inquiry-btn {
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }

    .new-inquiry-btn:hover {
        box-shadow: 0 4px 12px -2px rgba(79, 70, 229, 0.4);
        transform: translateY(-1px);
        color: white;
    }

    .stats-row {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .stat-badge {
        padding: 0.6rem 1.25rem;
        border-radius: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-row {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        color: var(--text-secondary);
        background: white;
        border: 1px solid #e2e8f0;
        transition: all 0.2s;
    }

    .filter-btn:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    .filter-btn.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .inquiry-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .inquiry-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        transition: all 0.2s;
    }

    .inquiry-card:hover {
        box-shadow: 0 4px 12px -4px rgba(0, 0, 0, 0.1);
    }

    .inquiry-header {
        padding: 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
    }

    .inquiry-subject {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.35rem 0.75rem;
        background: #f1f5f9;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .inquiry-title {
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .inquiry-preview {
        color: var(--text-secondary);
        font-size: 0.9rem;
    }

    .inquiry-footer {
        padding: 1rem 1.25rem;
        background: #f8fafc;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .inquiry-time {
        font-size: 0.8rem;
        color: #94a3b8;
    }

    .status-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
    }

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
        background: #f1f5f9;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        color: #94a3b8;
    }

    .answer-box {
        margin: 0 1.25rem 1.25rem;
        padding: 1rem;
        background: #ecfdf5;
        border-radius: 12px;
        border-right: 4px solid #10b981;
    }

    .answer-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #059669;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
    }

    .answer-text {
        color: #065f46;
        line-height: 1.6;
    }
</style>

<div class="page-header">
    <h1 class="page-title">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary-color);">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        استفساراتي للدكتور
    </h1>
    <a href="{{ route('student.inquiries.create') }}" class="new-inquiry-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        استفسار جديد
    </a>
</div>

<div class="stats-row">
    <span class="stat-badge" style="background: #eff6ff; color: #2563eb;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
        </svg>
        {{ $stats['total'] }} استفسار
    </span>
    <span class="stat-badge" style="background: #fef3c7; color: #d97706;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
        </svg>
        {{ $stats['pending'] }} قيد الانتظار
    </span>
    <span class="stat-badge" style="background: #dcfce7; color: #16a34a;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        {{ $stats['answered'] }} تم الرد
    </span>
</div>

<div class="filter-row">
    <a href="{{ route('student.inquiries.index') }}" class="filter-btn {{ !$status ? 'active' : '' }}">الكل</a>
    <a href="{{ route('student.inquiries.index', ['status' => 'pending']) }}" class="filter-btn {{ $status == 'pending' ? 'active' : '' }}">قيد الانتظار</a>
    <a href="{{ route('student.inquiries.index', ['status' => 'forwarded']) }}" class="filter-btn {{ $status == 'forwarded' ? 'active' : '' }}">تم التحويل</a>
    <a href="{{ route('student.inquiries.index', ['status' => 'answered']) }}" class="filter-btn {{ $status == 'answered' ? 'active' : '' }}">تم الرد</a>
</div>

@if($inquiries->count() > 0)
<div class="inquiry-list">
    @foreach($inquiries as $inquiry)
    <div class="inquiry-card">
        <div class="inquiry-header">
            <div>
                <div class="inquiry-subject">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                    {{ $inquiry->subject->name ?? 'غير محدد' }}
                </div>
                <h3 class="inquiry-title" style="margin-top: 0.5rem;">{{ $inquiry->title }}</h3>
                <p class="inquiry-preview">{{ Str::limit($inquiry->question, 100) }}</p>
            </div>
            <span class="status-badge" style="background: {{ $inquiry->status_color }}20; color: {{ $inquiry->status_color }};">
                {{ $inquiry->status_label }}
            </span>
        </div>

        @if($inquiry->answer)
        <div class="answer-box">
            <div class="answer-label">رد الدكتور</div>
            <div class="answer-text">{{ $inquiry->answer }}</div>
        </div>
        @endif

        <div class="inquiry-footer">
            <span class="inquiry-time">{{ $inquiry->created_at->diffForHumans() }}</span>
            <a href="{{ route('student.inquiries.show', $inquiry->id) }}" style="color: var(--primary-color); font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 0.25rem;">
                عرض التفاصيل
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </a>
        </div>
    </div>
    @endforeach
</div>

@if($inquiries->hasPages())
<div style="margin-top: 2rem; display: flex; justify-content: center;">
    {{ $inquiries->appends(['status' => $status])->links() }}
</div>
@endif

@else
<div class="empty-state">
    <div class="empty-icon">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
    </div>
    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">لا توجد استفسارات</h3>
    <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">يمكنك إرسال استفسار جديد للدكتور عبر المندوب.</p>
    <a href="{{ route('student.inquiries.create') }}" class="new-inquiry-btn" style="display: inline-flex;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        استفسار جديد
    </a>
</div>
@endif

@endsection