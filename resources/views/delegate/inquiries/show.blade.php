@extends('layouts.delegate')

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

    .action-buttons {
        display: flex;
        gap: 0.75rem;
    }

    .action-btn {
        padding: 0.6rem 1.25rem;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-forward {
        background: #dbeafe;
        color: #2563eb;
    }

    .btn-forward:hover {
        background: #bfdbfe;
    }

    .btn-close {
        background: #f3f4f6;
        color: #6b7280;
    }

    .btn-close:hover {
        background: #e5e7eb;
    }

    .inquiry-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .inquiry-header {
        padding: 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .student-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .student-avatar {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #4f46e5;
    }

    .student-name {
        font-weight: 700;
        color: var(--text-primary);
    }

    .student-details {
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    .subject-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        background: #f1f5f9;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-top: 0.5rem;
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 700;
    }

    .inquiry-title {
        padding: 1.5rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .inquiry-title h2 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .inquiry-time {
        font-size: 0.8rem;
        color: #94a3b8;
        margin-top: 0.5rem;
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
        background: #f8fafc;
        padding: 1.25rem;
        border-radius: 12px;
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

    .reply-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
    }

    .reply-title {
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .reply-textarea {
        width: 100%;
        min-height: 150px;
        padding: 1rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-family: inherit;
        font-size: 1rem;
        resize: vertical;
        margin-bottom: 1rem;
    }

    .reply-textarea:focus {
        outline: none;
        background: white;
        border-color: var(--primary-color);
    }

    .btn-primary {
        padding: 0.875rem 1.5rem;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }

    .btn-primary:hover {
        box-shadow: 0 4px 12px -2px rgba(16, 185, 129, 0.4);
    }
</style>

<div class="page-header">
    <a href="{{ route('delegate.inquiries.index') }}" class="back-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
        العودة للاستفسارات
    </a>
    <div class="action-buttons">
        @if($inquiry->status == 'pending')
        <form action="{{ route('delegate.inquiries.forward', $inquiry->id) }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="action-btn btn-forward">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 14 20 9 15 4"></polyline>
                    <path d="M4 20v-7a4 4 0 0 1 4-4h12"></path>
                </svg>
                تحويل للدكتور
            </button>
        </form>
        @endif
        @if($inquiry->status != 'closed')
        <form action="{{ route('delegate.inquiries.close', $inquiry->id) }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="action-btn btn-close">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                إغلاق
            </button>
        </form>
        @endif
    </div>
</div>

<div class="inquiry-card">
    <div class="inquiry-header">
        <div>
            <div class="student-info">
                <div class="student-avatar">{{ mb_substr($inquiry->student->name ?? '?', 0, 1) }}</div>
                <div>
                    <div class="student-name">{{ $inquiry->student->name ?? 'غير معروف' }}</div>
                    <div class="student-details">
                        {{ $inquiry->student->student_number ?? '' }}
                    </div>
                </div>
            </div>
            <div class="subject-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
                {{ $inquiry->subject->name ?? 'غير محدد' }}
            </div>
        </div>
        <span class="status-badge" style="background: {{ $inquiry->status_color }}20; color: {{ $inquiry->status_color }};">
            {{ $inquiry->status_label }}
        </span>
    </div>

    <div class="inquiry-title">
        <h2>{{ $inquiry->title }}</h2>
        <div class="inquiry-time">تم الإرسال {{ $inquiry->created_at->diffForHumans() }}</div>
    </div>

    <div class="inquiry-section">
        <div class="section-label">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            سؤال الطالب
        </div>
        <div class="question-text">{{ $inquiry->question }}</div>
    </div>

    @if($inquiry->answer)
    <div class="answer-section">
        <div class="section-label">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            الرد
            @if($inquiry->answered_at)
            <span style="font-weight: 400; font-size: 0.7rem;">({{ $inquiry->answered_at->diffForHumans() }})</span>
            @endif
        </div>
        <div class="answer-text">{{ $inquiry->answer }}</div>
    </div>
    @endif
</div>

@if(!$inquiry->answer && $inquiry->status != 'closed')
<div class="reply-card">
    <div class="reply-title">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="9 14 4 9 9 4"></polyline>
            <path d="M20 20v-7a4 4 0 0 0-4-4H4"></path>
        </svg>
        الرد على الاستفسار
    </div>
    <form action="{{ route('delegate.inquiries.answer', $inquiry->id) }}" method="POST">
        @csrf
        <textarea name="answer" class="reply-textarea" placeholder="اكتب الرد هنا..." required></textarea>
        <button type="submit" class="btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            إرسال الرد
        </button>
    </form>
</div>
@endif

@endsection