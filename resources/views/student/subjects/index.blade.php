@extends('layouts.student')

@section('title', 'المقررات الدراسية')

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

    .subjects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
    }

    .subject-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .subject-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.12);
    }

    .subject-header {
        height: 8px;
        background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 50%, #a855f7 100%);
    }

    .subject-body {
        padding: 1.5rem;
    }

    .subject-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .subject-code {
        display: inline-block;
        background: #f1f5f9;
        color: var(--text-secondary);
        padding: 0.25rem 0.75rem;
        border-radius: 6px;
        font-size: 0.85rem;
        font-family: monospace;
        font-weight: 600;
    }

    .doctor-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 1.25rem 0;
        padding: 1rem;
        background: #f8fafc;
        border-radius: 12px;
    }

    .doctor-avatar {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4f46e5;
        font-weight: 700;
        font-size: 1.1rem;
    }

    .doctor-label {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }

    .doctor-name {
        font-weight: 600;
        color: var(--text-primary);
    }

    .subject-footer {
        padding: 1rem 1.5rem;
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
    }

    .view-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        padding: 0.75rem;
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s;
    }

    .view-btn:hover {
        transform: scale(1.02);
        box-shadow: 0 8px 20px -6px rgba(79, 70, 229, 0.5);
    }

    .empty-state {
        grid-column: 1 / -1;
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
        color: var(--text-secondary);
    }
</style>

<div class="page-header">
    <h1 class="page-title">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary-color);">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
        </svg>
        المقررات الدراسية
    </h1>
    <p class="page-subtitle">جميع المواد المسجلة لهذا الفصل الدراسي</p>
</div>

<div class="subjects-grid">
    @forelse($subjects as $subject)
    <div class="subject-card">
        <div class="subject-header"></div>
        <div class="subject-body">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                <h3 class="subject-title">{{ $subject->name }}</h3>
                <span class="subject-code">{{ $subject->code }}</span>
            </div>

            <div class="doctor-info">
                <div class="doctor-avatar">
                    {{ $subject->doctor ? mb_substr($subject->doctor->name, 0, 1) : '?' }}
                </div>
                <div>
                    <div class="doctor-label">مدرس المادة</div>
                    <div class="doctor-name">{{ $subject->doctor->name ?? 'غير محدد' }}</div>
                </div>
            </div>
        </div>
        <div class="subject-footer">
            <a href="{{ route('student.subjects.show', $subject->id) }}" class="view-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
                عرض التفاصيل والواجبات
            </a>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <div class="empty-icon">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
        </div>
        <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">لا توجد مقررات</h3>
        <p style="color: var(--text-secondary);">لم يتم تسجيل أي مواد دراسية لك في هذا الفصل حتى الآن.</p>
    </div>
    @endforelse
</div>

@endsection