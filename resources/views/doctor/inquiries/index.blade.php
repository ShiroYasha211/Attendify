@extends('layouts.doctor')

@section('title', 'استفسارات الطلاب')

@section('content')

<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .section-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .section-card h2 {
        font-size: 1.1rem;
        font-weight: 800;
        margin-bottom: 0.25rem;
        color: var(--text-primary);
    }

    .section-subtitle {
        color: var(--text-secondary);
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }

    .subject-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
    }

    .subject-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1rem;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 0.9rem;
    }

    .subject-top {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        align-items: flex-start;
    }

    .subject-name {
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .subject-meta {
        font-size: 0.85rem;
        color: var(--text-secondary);
        line-height: 1.5;
    }

    .toggle-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .toggle-pill.open {
        background: #dcfce7;
        color: #166534;
    }

    .toggle-pill.closed {
        background: #fee2e2;
        color: #991b1b;
    }

    .reason-box {
        padding: 0.75rem 0.9rem;
        border-radius: 12px;
        background: #fff7ed;
        color: #9a3412;
        font-size: 0.88rem;
        line-height: 1.6;
    }

    .settings-form textarea {
        width: 100%;
        min-height: 88px;
        resize: vertical;
        border-radius: 12px;
        border: 1px solid #dbe3ee;
        background: white;
        padding: 0.75rem 0.9rem;
        font-family: inherit;
        font-size: 0.95rem;
    }

    .settings-form textarea:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .settings-actions {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .btn-toggle {
        border: none;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-weight: 800;
        color: white;
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-toggle:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px -2px rgba(79, 70, 229, 0.35);
    }

    .stats-row {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
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
        margin-bottom: 1.5rem;
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

    .filter-btn.active,
    .filter-btn:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    .filter-btn.active {
        background: var(--primary-color);
        color: white;
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
        gap: 1rem;
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
        font-size: 1.05rem;
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
        flex-wrap: wrap;
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

<div class="section-card">
    <h2>التحكم في فتح وإغلاق الاستفسارات</h2>
    <div class="section-subtitle">افتح أو أغلق الاستفسارات لكل مادة تدرسها بشكل مستقل. إغلاق المادة يمنع الطالب من إرسال استفسارات جديدة عليها فقط.</div>

    @if($subjects->count())
        <div class="subject-grid">
            @foreach($subjects as $subject)
                @php
                    $enabled = (bool) $subject->inquiries_enabled;
                    $reason = $subject->inquiries_closed_reason;
                @endphp
                <div class="subject-card">
                    <div class="subject-top">
                        <div>
                            <div class="subject-name">{{ $subject->name }}</div>
                            <div class="subject-meta">
                                {{ $subject->code ?: 'لا يوجد كود مادة' }}
                                @if($subject->level?->name)
                                    <br>{{ $subject->level->name }}
                                @endif
                            </div>
                        </div>
                        <span class="toggle-pill {{ $enabled ? 'open' : 'closed' }}">
                            {{ $enabled ? 'مفتوحة' : 'مغلقة' }}
                        </span>
                    </div>

                    @if(!$enabled)
                        <div class="reason-box">
                            {{ $reason ?: 'لا يوجد سبب مسجل للإغلاق.' }}
                        </div>
                    @endif

                    <form class="settings-form" method="POST" action="{{ route('doctor.inquiries.settings.update', $subject->id) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="inquiries_enabled" value="{{ $enabled ? 0 : 1 }}">
                        <textarea name="inquiries_closed_reason" placeholder="سبب الإغلاق اختياري، ويمكن تركه فارغاً.">{{ old('inquiries_closed_reason', $reason) }}</textarea>
                        <div style="font-size: 0.82rem; color: #64748b; margin-top: 0.35rem;">
                            إذا تم الإغلاق بدون سبب سيبقى السبب السابق إن وجد.
                        </div>
                        <div class="settings-actions" style="margin-top: 0.85rem;">
                            <button type="submit" class="btn-toggle">
                                {{ $enabled ? 'إغلاق الاستفسارات' : 'فتح الاستفسارات' }}
                            </button>
                            <span style="font-size: 0.85rem; color: #64748b;">
                                {{ $enabled ? 'الطلاب لن يتمكنوا من إرسال استفسارات جديدة.' : 'الطلاب يمكنهم الإرسال الآن.' }}
                            </span>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state" style="padding: 2rem; margin-top: 0.5rem;">
            <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem;">لا توجد مواد مرتبطة بك</h3>
            <p style="color: var(--text-secondary);">لا يمكن إدارة الاستفسارات قبل ربطك بمواد دراسية.</p>
        </div>
    @endif
</div>

<div class="stats-row">
    <a href="{{ route('doctor.inquiries.index') }}" class="stat-badge all {{ !$status ? 'active' : '' }}" style="background: #f1f5f9; color: var(--text-primary);">
        <span>الكل</span>
        <strong>{{ $stats['total'] }}</strong>
    </a>
    <a href="{{ route('doctor.inquiries.index', ['status' => 'forwarded']) }}" class="stat-badge forwarded {{ $status == 'forwarded' ? 'active' : '' }}" style="background: #fef3c7; color: #92400e;">
        <span>بانتظار الرد</span>
        <strong>{{ $stats['forwarded'] }}</strong>
    </a>
    <a href="{{ route('doctor.inquiries.index', ['status' => 'answered']) }}" class="stat-badge answered {{ $status == 'answered' ? 'active' : '' }}" style="background: #d1fae5; color: #065f46;">
        <span>تم الرد</span>
        <strong>{{ $stats['answered'] }}</strong>
    </a>
    <a href="{{ route('doctor.inquiries.index', ['status' => 'closed']) }}" class="stat-badge closed {{ $status == 'closed' ? 'active' : '' }}" style="background: #e2e8f0; color: #64748b;">
        <span>مغلق</span>
        <strong>{{ $stats['closed'] }}</strong>
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

@forelse($inquiries as $inquiry)
    <div class="inquiry-card">
        <div class="inquiry-header">
            <span class="inquiry-subject">{{ $inquiry->subject->name ?? 'غير محدد' }}</span>
            <span class="inquiry-status {{ $inquiry->status }}">
                @switch($inquiry->status)
                    @case('forwarded') بانتظار الرد @break
                    @case('answered') تم الرد @break
                    @case('closed') مغلق @break
                    @default غير محدد
                @endswitch
            </span>
        </div>

        <h3 class="inquiry-question">{{ Str::limit($inquiry->question, 120) }}</h3>

        <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
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
        <p style="color: var(--text-secondary);">ستظهر هنا استفسارات الطلاب المحوّلة إليك من المنسق أو من النظام.</p>
    </div>
@endforelse

@if($inquiries->hasPages())
    <div style="margin-top: 2rem;">
        {{ $inquiries->links() }}
    </div>
@endif

@endsection
