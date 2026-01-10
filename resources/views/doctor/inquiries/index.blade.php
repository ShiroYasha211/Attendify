@extends('layouts.doctor')

@section('title', 'استفسارات الطلاب')

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

    .stats-row {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .stat-badge {
        padding: 0.5rem 1rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        transition: all 0.2s;
    }

    .stat-badge.all {
        background: #f1f5f9;
        color: var(--text-primary);
    }

    .stat-badge.forwarded {
        background: #fef3c7;
        color: #92400e;
    }

    .stat-badge.answered {
        background: #d1fae5;
        color: #065f46;
    }

    .stat-badge.closed {
        background: #e2e8f0;
        color: #64748b;
    }

    .stat-badge:hover,
    .stat-badge.active {
        transform: scale(1.05);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .inquiry-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.2s;
    }

    .inquiry-card:hover {
        border-color: var(--primary-color);
        box-shadow: 0 4px 12px -4px rgba(79, 70, 229, 0.15);
    }

    .inquiry-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .inquiry-subject {
        font-size: 0.8rem;
        padding: 0.35rem 0.75rem;
        background: #eff6ff;
        color: var(--primary-color);
        border-radius: 8px;
        font-weight: 600;
    }

    .inquiry-status {
        font-size: 0.75rem;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-weight: 700;
    }

    .inquiry-status.forwarded {
        background: #fef3c7;
        color: #92400e;
    }

    .inquiry-status.answered {
        background: #d1fae5;
        color: #065f46;
    }

    .inquiry-status.closed {
        background: #e2e8f0;
        color: #64748b;
    }

    .inquiry-question {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.75rem;
        line-height: 1.5;
    }

    .inquiry-meta {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    .inquiry-meta span {
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .view-btn {
        padding: 0.5rem 1rem;
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.85rem;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
    }
</style>

<div class="page-header">
    <h1 class="page-title">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary-color);">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        استفسارات الطلاب
    </h1>
</div>

<!-- Stats -->
<div class="stats-row">
    <a href="{{ route('doctor.inquiries.index') }}" class="stat-badge all {{ !$status ? 'active' : '' }}">
        <span>الكل</span>
        <strong>{{ $stats['total'] }}</strong>
    </a>
    <a href="{{ route('doctor.inquiries.index', ['status' => 'forwarded']) }}" class="stat-badge forwarded {{ $status == 'forwarded' ? 'active' : '' }}">
        <span>بانتظار الرد</span>
        <strong>{{ $stats['forwarded'] }}</strong>
    </a>
    <a href="{{ route('doctor.inquiries.index', ['status' => 'answered']) }}" class="stat-badge answered {{ $status == 'answered' ? 'active' : '' }}">
        <span>تم الرد</span>
        <strong>{{ $stats['answered'] }}</strong>
    </a>
    <a href="{{ route('doctor.inquiries.index', ['status' => 'closed']) }}" class="stat-badge closed {{ $status == 'closed' ? 'active' : '' }}">
        <span>مغلق</span>
        <strong>{{ $stats['closed'] }}</strong>
    </a>
</div>

@if(session('success'))
<div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

<!-- Inquiries List -->
@forelse($inquiries as $inquiry)
<div class="inquiry-card">
    <div class="inquiry-header">
        <span class="inquiry-subject">{{ $inquiry->subject->name ?? 'غير محدد' }}</span>
        <span class="inquiry-status {{ $inquiry->status }}">
            @switch($inquiry->status)
            @case('forwarded') بانتظار الرد @break
            @case('answered') تم الرد @break
            @case('closed') مغلق @break
            @endswitch
        </span>
    </div>

    <h3 class="inquiry-question">{{ Str::limit($inquiry->question, 120) }}</h3>

    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div class="inquiry-meta">
            <span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                {{ $inquiry->student->name ?? 'طالب' }}
            </span>
            <span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                {{ $inquiry->created_at->diffForHumans() }}
            </span>
        </div>

        <a href="{{ route('doctor.inquiries.show', $inquiry->id) }}" class="view-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
            عرض
        </a>
    </div>
</div>
@empty
<div class="empty-state">
    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: #cbd5e1; margin-bottom: 1rem;">
        <circle cx="12" cy="12" r="10"></circle>
        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
        <line x1="12" y1="17" x2="12.01" y2="17"></line>
    </svg>
    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">لا توجد استفسارات</h3>
    <p style="color: var(--text-secondary);">ستظهر هنا استفسارات الطلاب المحولة إليك من المندوب</p>
</div>
@endforelse

<!-- Pagination -->
@if($inquiries->hasPages())
<div style="margin-top: 2rem;">
    {{ $inquiries->links() }}
</div>
@endif

@endsection