@extends('layouts.student')

@section('title', 'استفسار جديد')

@section('content')

<style>
    .page-header {
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

    .form-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        padding: 2rem;
    }

    .info-box {
        padding: 1rem;
        background: #eff6ff;
        border-radius: 12px;
        margin-bottom: 2rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .info-box svg {
        flex-shrink: 0;
        margin-top: 2px;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .form-input,
    .form-select {
        width: 100%;
        padding: 0.875rem 1rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-size: 1rem;
        font-family: inherit;
        transition: all 0.2s;
    }

    .form-input:focus,
    .form-select:focus {
        outline: none;
        background: white;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .form-textarea {
        min-height: 180px;
        resize: vertical;
    }

    .btn-row {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    .btn-primary {
        padding: 0.875rem 2rem;
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
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
        box-shadow: 0 4px 12px -2px rgba(79, 70, 229, 0.4);
    }

    .btn-secondary {
        padding: 0.875rem 2rem;
        background: #f1f5f9;
        color: var(--text-secondary);
        border: none;
        border-radius: 12px;
        font-weight: 700;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }

    .btn-secondary:hover {
        background: #e2e8f0;
    }
</style>

<div class="page-header">
    <h1 class="page-title">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary-color);">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        استفسار جديد للدكتور
    </h1>
</div>

<div class="form-card">
    <div class="info-box">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
        </svg>
        <div style="color: #1e40af; font-size: 0.9rem; line-height: 1.6;">
            سيتم إرسال استفسارك إلى مندوب الشعبة الذي سيقوم بتحويله إلى الدكتور المختص.
            ستتلقى إشعاراً عند الرد على استفسارك.
        </div>
    </div>

    <form action="{{ route('student.inquiries.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label class="form-label" for="subject_id">المادة الدراسية</label>
            <select id="subject_id" name="subject_id" class="form-select" required>
                <option value="">اختر المادة...</option>
                @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                    {{ $subject->name }} ({{ $subject->code }})
                </option>
                @endforeach
            </select>
            @error('subject_id')
            <span style="color: #ef4444; font-size: 0.85rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="title">عنوان الاستفسار</label>
            <input type="text" id="title" name="title" class="form-input" placeholder="مثال: استفسار عن موعد تسليم المشروع..." value="{{ old('title') }}" required>
            @error('title')
            <span style="color: #ef4444; font-size: 0.85rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="question">سؤالك للدكتور</label>
            <textarea id="question" name="question" class="form-input form-textarea" placeholder="اكتب سؤالك أو استفسارك بالتفصيل..." required>{{ old('question') }}</textarea>
            @error('question')
            <span style="color: #ef4444; font-size: 0.85rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="btn-row">
            <button type="submit" class="btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
                إرسال الاستفسار
            </button>
            <a href="{{ route('student.inquiries.index') }}" class="btn-secondary">إلغاء</a>
        </div>
    </form>
</div>

@endsection