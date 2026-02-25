@extends('layouts.student')

@section('title', 'القسم السريري')

@section('content')
<style>
    .clinical-page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.75rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .clinical-page-header .right-side h1 {
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--text-primary);
        margin: 0 0 0.15rem 0;
    }

    .clinical-page-header .right-side p {
        color: var(--text-secondary);
        font-size: 0.9rem;
        margin: 0;
    }

    .clinical-page-header .left-side {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    .btn-outline {
        background: white;
        color: var(--text-secondary);
        border: 1.5px solid #e2e8f0;
        padding: 0.55rem 1.1rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.88rem;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }

    .btn-outline:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
        text-decoration: none;
    }

    /* Stats Row */
    .stats-row {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.75rem;
        flex-wrap: wrap;
    }

    .stat-card {
        flex: 1;
        min-width: 140px;
        background: white;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-icon.purple {
        background: #ede9fe;
        color: #7c3aed;
    }

    .stat-icon.green {
        background: #d1fae5;
        color: #059669;
    }

    .stat-icon.amber {
        background: #fef3c7;
        color: #d97706;
    }

    .stat-info .stat-number {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1;
    }

    .stat-info .stat-label {
        font-size: 0.8rem;
        color: var(--text-secondary);
        margin-top: 0.15rem;
    }

    /* Task Cards */
    .card-section {
        background: white;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .section-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .task-card {
        background: #fafbfe;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        transition: all 0.2s;
        position: relative;
    }

    .task-card:hover {
        border-color: #c7d2fe;
        box-shadow: 0 2px 12px rgba(79, 70, 229, 0.06);
    }

    .task-card .task-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 0.75rem;
    }

    .task-card .patient {
        font-weight: 700;
        font-size: 1.05rem;
        color: var(--text-primary);
    }

    .task-card .task-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        font-size: 0.82rem;
        color: var(--text-secondary);
    }

    .task-card .task-meta .meta-item {
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .task-type-badge {
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .task-type-badge.history {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .task-type-badge.exam {
        background: #fce7f3;
        color: #be185d;
    }

    .task-type-badge.followup {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-status {
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .badge-status.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-status.completed {
        background: #d1fae5;
        color: #065f46;
    }

    .btn-qr {
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        color: white;
        border: none;
        padding: 0.55rem 1.2rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.25s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.2);
    }

    .btn-qr:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);
        color: white;
        text-decoration: none;
    }

    .btn-qr:disabled,
    .btn-qr.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--text-secondary);
    }

    .empty-state svg {
        margin-bottom: 0.75rem;
        color: #cbd5e1;
    }
</style>

<div class="clinical-page-header">
    <div class="right-side">
        <h1>القسم السريري العملي 🩺</h1>
        <p>المهام السريرية المكلف بها وسجلك في التدريب العملي</p>
    </div>
    <div class="left-side">
        <a href="{{ route('student.clinical.daily-log.create') }}" class="btn-qr">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
            </svg>
            تسجيل بيانات اليوم
        </a>
        <a href="{{ route('student.clinical.evaluations') }}" class="btn-outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 11l3 3L22 4"></path>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
            </svg>
            تقييماتي
        </a>
        <a href="{{ route('student.clinical.logbook') }}" class="btn-outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
            سجلي
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon purple"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
            </svg></div>
        <div class="stat-info">
            <div class="stat-number">{{ $assignments->count() }}</div>
            <div class="stat-label">مهمة مكلف بها</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg></div>
        <div class="stat-info">
            <div class="stat-number">{{ $confirmedCount }}</div>
            <div class="stat-label">مؤكدة من الدكتور</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg></div>
        <div class="stat-info">
            <div class="stat-number">{{ $pendingCount }}</div>
            <div class="stat-label">بانتظار التأكيد</div>
        </div>
    </div>
</div>

{{-- Pending Daily Logs (show QR) --}}
@if($pendingLogs->count() > 0)
<div class="card-section" style="margin-bottom: 1.25rem; border-color: #fde68a; background: #fffbeb;">
    <div class="section-header" style="border-bottom-color: #fde68a;">
        <h3 class="section-title" style="color: #92400e;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #d97706;">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            سجلات بانتظار تأكيد الدكتور ({{ $pendingLogs->count() }})
        </h3>
    </div>
    @foreach($pendingLogs as $log)
    <div class="task-card" style="border-color: #fde68a; background: #fffef5;">
        <div class="task-header">
            <div>
                <div class="patient" style="font-size: 0.95rem;">📅 {{ $log->log_date->format('Y-m-d') }} — {{ $log->log_date->locale('ar')->dayName }}</div>
                <div class="task-meta" style="margin-top: 0.35rem;">
                    <span class="meta-item">🏥 {{ $log->trainingCenter->name ?? '-' }}</span>
                    <span class="meta-item">🏷 {{ $log->department->name ?? '-' }}</span>
                    <span class="meta-item">👨‍⚕️ د. {{ $log->doctor->name ?? '-' }}</span>
                </div>
                <div style="display: flex; gap: 0.35rem; flex-wrap: wrap; margin-top: 0.4rem;">
                    <span style="font-size:0.75rem;padding:0.15rem 0.4rem;border-radius:4px;font-weight:700;background:#dbeafe;color:#1d4ed8;">📋 قصص: {{ $log->history_count }}</span>
                    <span style="font-size:0.75rem;padding:0.15rem 0.4rem;border-radius:4px;font-weight:700;background:#fce7f3;color:#be185d;">🩺 فحص: {{ $log->exam_count }}</span>
                    <span style="font-size:0.75rem;padding:0.15rem 0.4rem;border-radius:4px;font-weight:700;background:{{ $log->did_round ? '#d1fae5' : '#f1f5f9' }};color:{{ $log->did_round ? '#065f46' : '#94a3b8' }};">🔄 مرور {{ $log->did_round ? '✓' : '✗' }}</span>
                </div>
            </div>
            <a href="{{ route('student.clinical.show-qr', $log->id) }}" class="btn-qr">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                </svg>
                عرض QR
            </a>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Task Cards --}}
<div class="card-section">
    <div class="section-header">
        <h3 class="section-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary-color);">
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
            </svg>
            المهام السريرية المكلف بها
        </h3>
    </div>

    @forelse($assignments as $a)
    <div class="task-card">
        <div class="task-header">
            <div>
                <div class="patient">{{ $a->clinicalCase->patient_name ?? '-' }}</div>
                <div class="task-meta" style="margin-top: 0.4rem;">
                    <span class="meta-item">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 21h18"></path>
                            <path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path>
                        </svg>
                        {{ $a->clinicalCase->trainingCenter->name ?? '-' }}
                    </span>
                    <span class="meta-item">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                        </svg>
                        {{ $a->clinicalCase->bodySystem->name ?? '-' }}
                    </span>
                    <span class="meta-item">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        د. {{ $a->assigner->name ?? '-' }}
                    </span>
                </div>
            </div>
            <div style="text-align: left;">
                @if($a->task_type == 'history_taking')
                <span class="task-type-badge history">قصة مرضية</span>
                @elseif($a->task_type == 'clinical_examination')
                <span class="task-type-badge exam">فحص سريري</span>
                @else
                <span class="task-type-badge followup">متابعة (Round)</span>
                @endif
            </div>
        </div>

        @if($a->instructions)
        <div style="background: #f1f5f9; border-radius: 8px; padding: 0.6rem 0.85rem; font-size: 0.82rem; color: #475569; margin-bottom: 0.75rem;">
            <strong>تعليمات:</strong> {{ $a->instructions }}
        </div>
        @endif

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem;">
            @if($a->is_completed)
            <span class="badge-status completed">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:middle;">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                تم الإنجاز ✅
            </span>
            <span style="font-size: 0.8rem; color: var(--text-secondary);">{{ $a->completed_at ? $a->completed_at->format('Y-m-d') : '' }}</span>
            @else
            <span class="badge-status pending">⏳ قيد الإنجاز</span>
            @endif
        </div>
    </div>
    @empty
    <div class="empty-state">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
        </svg>
        <p>لا توجد مهام سريرية مكلف بها حالياً.</p>
        <p style="font-size: 0.82rem;">سيقوم الدكتور بتكليفك بمهام عند تواجدك في المركز التدريبي.</p>
    </div>
    @endforelse
</div>
@endsection