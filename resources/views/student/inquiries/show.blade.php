@extends('layouts.student')

@section('title', 'عرض الاستفسار')

@section('content')

<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .back-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-secondary);
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s;
    }

    .back-btn:hover {
        color: var(--primary-color);
    }

    .inquiry-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }

    .inquiry-header {
        padding: 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .subject-badge {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.35rem 0.75rem;
        background: #f1f5f9;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
    }

    .inquiry-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .inquiry-time {
        font-size: 0.8rem;
        color: #94a3b8;
        margin-top: 0.25rem;
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 700;
    }

    .inquiry-section {
        padding: 1.5rem;
    }

    .section-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--text-secondary);
        text-transform: uppercase;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .question-text {
        color: var(--text-primary);
        line-height: 1.8;
        white-space: pre-wrap;
    }

    .answer-section {
        padding: 1.5rem;
        background: #ecfdf5;
        border-top: 1px solid #d1fae5;
    }

    .answer-section .section-label {
        color: #059669;
    }

    .answer-text {
        color: #065f46;
        line-height: 1.8;
        white-space: pre-wrap;
    }

    .waiting-section {
        padding: 2rem;
        text-align: center;
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
    }

    .waiting-icon {
        width: 60px;
        height: 60px;
        background: #fef3c7;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        color: #d97706;
    }
</style>

<div class="page-header">
    <a href="{{ route('student.inquiries.index') }}" class="back-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
        العودة للاستفسارات
    </a>
</div>

<div class="inquiry-card">
    <div class="inquiry-header">
        <div>
            <div class="subject-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
                {{ $inquiry->subject->name ?? 'غير محدد' }}
            </div>
            <h2 class="inquiry-title">{{ $inquiry->title }}</h2>
            <div class="inquiry-time">تم الإرسال {{ $inquiry->created_at->diffForHumans() }}</div>
        </div>
        <span class="status-badge" style="background: {{ $inquiry->status_color }}20; color: {{ $inquiry->status_color }};">
            {{ $inquiry->status_label }}
        </span>
    </div>

    <div class="inquiry-section">
        <div class="section-label">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            سؤالك
        </div>
        <div class="question-text">{{ $inquiry->question }}</div>
    </div>

    @if($inquiry->answer)
    <div class="answer-section">
        <div class="section-label">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            رد الدكتور
            @if($inquiry->answered_at)
            <span style="font-weight: 400; font-size: 0.7rem;">({{ $inquiry->answered_at->diffForHumans() }})</span>
            @endif
        </div>
        <div class="answer-text">{{ $inquiry->answer }}</div>
    </div>
    @elseif($inquiry->status != 'closed')
    <div class="waiting-section">
        <div class="waiting-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>
        <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem;">في انتظار الرد</div>
        <div style="color: var(--text-secondary); font-size: 0.9rem;">سيتم إشعارك عند وصول رد الدكتور</div>
    </div>
    @endif
</div>

@endsection