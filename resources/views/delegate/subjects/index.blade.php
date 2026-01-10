@extends('layouts.delegate')

@section('title', 'المواد الدراسية')

@section('content')

<style>
    .page-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .page-header-icon {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 4px 12px -2px rgba(59, 130, 246, 0.4);
    }

    .page-header-text h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .page-header-text p {
        color: var(--text-secondary);
        font-size: 0.9rem;
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-mini {
        background: white;
        border-radius: 16px;
        padding: 1.25rem;
        border: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-mini .icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-mini .value {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1;
    }

    .stat-mini .label {
        font-size: 0.85rem;
        color: var(--text-secondary);
        margin-top: 0.25rem;
    }

    .subjects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
    }

    .subject-card {
        background: white;
        border-radius: 20px;
        border: 1px solid var(--border-color);
        overflow: hidden;
        transition: all 0.3s;
    }

    .subject-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.12);
    }

    .subject-header {
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, #f8fafc 0%, #fff 100%);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .subject-icon {
        width: 52px;
        height: 52px;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 4px 12px -2px rgba(59, 130, 246, 0.3);
    }

    .subject-code {
        background: #e0f2fe;
        color: #0369a1;
        padding: 0.35rem 0.75rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        font-family: monospace;
    }

    .subject-body {
        padding: 1.25rem 1.5rem;
    }

    .subject-name {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.75rem;
    }

    .subject-doctor {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .doctor-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #06b6d4, #0891b2);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .doctor-info .name {
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--text-primary);
    }

    .doctor-info .role {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }

    .subject-meta {
        display: flex;
        gap: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #f1f5f9;
        margin-top: 0.5rem;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    .subject-actions {
        padding: 1rem 1.5rem;
        background: #fafafa;
        border-top: 1px solid var(--border-color);
    }

    .btn-attendance {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.875rem 1.25rem;
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-attendance:hover {
        box-shadow: 0 4px 12px -2px rgba(16, 185, 129, 0.4);
        transform: translateY(-1px);
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 20px;
        border: 1px solid var(--border-color);
    }

    .empty-state svg {
        margin-bottom: 1rem;
        opacity: 0.4;
    }

    .empty-state h3 {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: var(--text-secondary);
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
        </svg>
    </div>
    <div class="page-header-text">
        <h1>المواد الدراسية</h1>
        <p>قائمة المواد المقررة للدفعة مع إمكانية رصد الحضور</p>
    </div>
</div>

<!-- Stats Row -->
<div class="stats-row">
    <div class="stat-mini">
        <div class="icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
        </div>
        <div>
            <div class="value" style="color: #3b82f6;">{{ count($subjects) }}</div>
            <div class="label">مادة مسجلة</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="icon" style="background: rgba(6, 182, 212, 0.1); color: #06b6d4;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
        </div>
        <div>
            <div class="value" style="color: #06b6d4;">{{ $subjects->filter(fn($s) => $s->doctor)->count() }}</div>
            <div class="label">دكتور يدرّس</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 11 12 14 22 4"></polyline>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
            </svg>
        </div>
        <div>
            <div class="value" style="color: #10b981;">جاهز</div>
            <div class="label">للرصد اليوم</div>
        </div>
    </div>
</div>

@if($subjects->isEmpty())
<div class="empty-state">
    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
    </svg>
    <h3>لا توجد مواد دراسية</h3>
    <p>لم يتم إضافة مواد دراسية لهذا المستوى بعد.</p>
</div>
@else
<div class="subjects-grid">
    @foreach($subjects as $subject)
    <div class="subject-card">
        <div class="subject-header">
            <div class="subject-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
            </div>
            <span class="subject-code">{{ $subject->code ?? 'N/A' }}</span>
        </div>

        <div class="subject-body">
            <h3 class="subject-name">{{ $subject->name }}</h3>

            <div class="subject-doctor">
                @if($subject->doctor)
                <div class="doctor-avatar">{{ mb_substr($subject->doctor->name, 0, 1) }}</div>
                <div class="doctor-info">
                    <div class="name">{{ $subject->doctor->name }}</div>
                    <div class="role">أستاذ المادة</div>
                </div>
                @else
                <div class="doctor-avatar" style="background: #e5e7eb; color: #9ca3af;">?</div>
                <div class="doctor-info">
                    <div class="name" style="color: var(--text-secondary);">غير محدد</div>
                    <div class="role">لم يتم تعيين دكتور</div>
                </div>
                @endif
            </div>

            <div class="subject-meta">
                <div class="meta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    {{ $subject->term->name ?? 'الترم الحالي' }}
                </div>
                <div class="meta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    {{ $studentsCount ?? 0 }} طالب
                </div>
            </div>
        </div>

        <div class="subject-actions">
            <a href="{{ route('delegate.attendance.create', $subject->id) }}" class="btn-attendance">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 11 12 14 22 4"></polyline>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                </svg>
                رصد الحضور
            </a>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection