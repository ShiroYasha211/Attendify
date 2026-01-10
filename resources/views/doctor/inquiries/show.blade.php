@extends('layouts.doctor')

@section('title', 'تفاصيل الاستفسار')

@section('content')

<style>
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-secondary);
        text-decoration: none;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    .back-link:hover {
        color: var(--primary-color);
    }

    .inquiry-container {
        max-width: 800px;
    }

    .inquiry-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        padding: 2rem;
        margin-bottom: 1.5rem;
    }

    .inquiry-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .inquiry-subject {
        font-size: 0.85rem;
        padding: 0.4rem 0.85rem;
        background: #eff6ff;
        color: var(--primary-color);
        border-radius: 8px;
        font-weight: 600;
    }

    .inquiry-status {
        font-size: 0.8rem;
        padding: 0.35rem 0.75rem;
        border-radius: 8px;
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

    .student-info {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
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
        font-size: 1.1rem;
    }

    .student-details h4 {
        font-weight: 700;
        color: var(--text-primary);
        margin: 0 0 0.25rem 0;
    }

    .student-details span {
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    .question-section {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .section-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .question-text {
        font-size: 1.1rem;
        line-height: 1.7;
        color: var(--text-primary);
    }

    .answer-section {
        background: #ecfdf5;
        border-radius: 12px;
        padding: 1.25rem;
        border-right: 4px solid #10b981;
    }

    .answer-text {
        font-size: 1rem;
        line-height: 1.7;
        color: var(--text-primary);
    }

    .answer-form {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        padding: 2rem;
    }

    .form-label {
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.75rem;
        display: block;
    }

    .form-textarea {
        width: 100%;
        padding: 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-family: inherit;
        font-size: 1rem;
        min-height: 150px;
        resize: vertical;
        margin-bottom: 1rem;
    }

    .form-textarea:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .btn-submit {
        padding: 0.875rem 2rem;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-submit:hover {
        box-shadow: 0 4px 12px -2px rgba(16, 185, 129, 0.4);
    }
</style>

<a href="{{ route('doctor.inquiries.index') }}" class="back-link">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="15 18 9 12 15 6"></polyline>
    </svg>
    العودة للاستفسارات
</a>

<div class="inquiry-container">
    <div class="inquiry-card">
        <div class="inquiry-header">
            <span class="inquiry-subject">{{ $inquiry->subject->name ?? 'غير محدد' }}</span>
            <span class="inquiry-status {{ $inquiry->status }}">
                @switch($inquiry->status)
                @case('forwarded') بانتظار الرد @break
                @case('answered') تم الرد @break
                @endswitch
            </span>
        </div>

        <div class="student-info">
            <div class="student-avatar">{{ mb_substr($inquiry->student->name ?? '?', 0, 1) }}</div>
            <div class="student-details">
                <h4>{{ $inquiry->student->name ?? 'طالب' }}</h4>
                <span>{{ $inquiry->student->student_number ?? '' }} • {{ $inquiry->created_at->format('Y-m-d H:i') }}</span>
            </div>
        </div>

        <div class="question-section">
            <div class="section-label">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                السؤال
            </div>
            <p class="question-text">{{ $inquiry->question }}</p>
        </div>

        @if($inquiry->answer)
        <div class="answer-section">
            <div class="section-label" style="color: #065f46;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                الإجابة
            </div>
            <p class="answer-text">{{ $inquiry->answer }}</p>
            @if($inquiry->answered_at)
            <p style="font-size: 0.8rem; color: #065f46; margin-top: 0.75rem;">
                تم الرد: {{ $inquiry->answered_at->format('Y-m-d H:i') }}
            </p>
            @endif
        </div>
        @endif
    </div>

    @if($inquiry->status == 'forwarded')
    <div class="answer-form">
        <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #10b981;">
                <line x1="22" y1="2" x2="11" y2="13"></line>
                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
            الرد على الاستفسار
        </h3>

        <form action="{{ route('doctor.inquiries.answer', $inquiry->id) }}" method="POST">
            @csrf
            <label class="form-label" for="answer">نص الإجابة</label>
            <textarea
                id="answer"
                name="answer"
                class="form-textarea"
                placeholder="اكتب إجابتك هنا..."
                required>{{ old('answer') }}</textarea>

            @error('answer')
            <p style="color: #ef4444; font-size: 0.85rem; margin-bottom: 1rem;">{{ $message }}</p>
            @enderror

            <button type="submit" class="btn-submit">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
                إرسال الإجابة
            </button>
        </form>
    </div>
    @endif
</div>

@endsection