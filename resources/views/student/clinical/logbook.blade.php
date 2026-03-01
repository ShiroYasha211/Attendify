@extends('layouts.student')
@section('title', 'سجلي السريري')
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
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text-primary);
        margin: 0 0 0.15rem 0;
    }

    .clinical-page-header .right-side p {
        color: var(--text-secondary);
        font-size: 0.9rem;
        margin: 0;
    }

    .btn-back {
        background: white;
        color: var(--text-secondary);
        border: 1.5px solid #e2e8f0;
        padding: 0.55rem 1.1rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.88rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-back:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        text-decoration: none;
    }

    .card-section {
        background: white;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
    }

    .log-entry {
        background: #fafbfe;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        transition: all 0.2s;
    }

    .log-entry:hover {
        border-color: #c7d2fe;
        box-shadow: 0 2px 12px rgba(79, 70, 229, 0.06);
    }

    .log-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .log-date {
        font-weight: 700;
        font-size: 1rem;
        color: var(--text-primary);
    }

    .status-badge {
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.78rem;
    }

    .status-badge.confirmed {
        background: #d1fae5;
        color: #065f46;
    }

    .status-badge.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-badge.rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .log-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
        font-size: 0.8rem;
        color: var(--text-secondary);
        margin-bottom: 0.4rem;
    }

    .btn-export {
        background: #fef2f2;
        color: #ef4444;
        border: 1px solid #fecaca;
        padding: 0.55rem 1.1rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.88rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-export:hover {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #b91c1c;
        text-decoration: none;
    }

    .header-actions {
        display: flex;
        gap: 0.75rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .stats-row {
        display: flex;
        gap: 0.35rem;
        flex-wrap: wrap;
    }

    .sig-row {
        display: flex;
        gap: 0.35rem;
        flex-wrap: wrap;
    }

    .sig-pill {
        font-size: 0.75rem;
        padding: 0.2rem 0.5rem;
        border-radius: 5px;
        font-weight: 700;
    }

    .sig-pill.on {
        background: #d1fae5;
        color: #065f46;
    }

    .sig-pill.off {
        background: #f1f5f9;
        color: #94a3b8;
    }

    .log-activities {
        font-size: 0.8rem;
        color: #475569;
        margin-top: 0.4rem;
    }

    .log-activities span {
        margin-left: 0.75rem;
    }

    .notes-box {
        background: #f1f5f9;
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        font-size: 0.82rem;
        color: #475569;
        margin-top: 0.5rem;
    }
</style>

<div class="clinical-page-header">
    <div class="right-side">
        <h1>📖 سجلي السريري (Logbook)</h1>
        <p>كل تسجيلات تدريبك العملي المؤرشفة</p>
    </div>
    <div class="left-side header-actions">
        <a href="{{ route('student.clinical.logbook.export_pdf') }}" class="btn-export">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            تصدير كـ PDF 📄
        </a>
        <a href="{{ route('student.clinical.index') }}" class="btn-back">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            القسم العملي
        </a>
    </div>
</div>

<div class="card-section">
    @forelse($entries as $e)
    <div class="log-entry">
        <div class="log-header">
            <span class="log-date">{{ $e->log_date->format('Y-m-d') }} — {{ $e->log_date->locale('ar')->dayName }}</span>
            <span class="status-badge {{ $e->status }}">{{ $e->status_label }}</span>
        </div>
        <div class="log-meta">
            <span>🏥 {{ $e->trainingCenter->name ?? '-' }}</span>
            <span>🏷 {{ $e->department->name ?? '-' }}</span>
            <span>👨‍⚕️ د. {{ $e->doctor->name ?? '-' }}</span>
        </div>
        <div class="sig-row">
            <span class="sig-pill on">حضور ✓</span>
            <span class="sig-pill {{ $e->history_count > 0 ? 'on' : 'off' }}">📋 قصص: {{ $e->history_count }}</span>
            <span class="sig-pill {{ $e->exam_count > 0 ? 'on' : 'off' }}">🩺 فحص: {{ $e->exam_count }}</span>
            <span class="sig-pill {{ $e->did_round ? 'on' : 'off' }}">🔄 مرور {{ $e->did_round ? '✓' : '✗' }}</span>
        </div>
        @if($e->activities->count())
        <div class="log-activities">
            @foreach($e->activities->where('activity_type', 'history_taking') as $a)<span>📋 {{ $a->bodySystem->name ?? '-' }}</span>@endforeach
            @foreach($e->activities->where('activity_type', 'clinical_examination') as $a)<span>🩺 {{ $a->bodySystem->name ?? '-' }}</span>@endforeach
            @foreach($e->activities->where('activity_type', 'round') as $a)<span>🔄 {{ $a->case_name }}</span>@endforeach
        </div>
        @endif
        @if($e->doctor_notes)
        <div class="notes-box"><strong>ملاحظة الدكتور:</strong> {{ $e->doctor_notes }}</div>
        @endif
    </div>
    @empty
    <div style="text-align:center;padding:2.5rem;color:var(--text-secondary);">
        <p>لا توجد تسجيلات بعد. ابدأ بتسجيل بيانات يومك.</p>
    </div>
    @endforelse

    {{ $entries->links() }}
</div>
@endsection