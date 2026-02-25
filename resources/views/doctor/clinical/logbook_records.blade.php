@extends('layouts.doctor')
@section('title', 'سجل التحضير')
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

    .filter-bar {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
    }

    .filter-bar select,
    .filter-bar input {
        padding: 0.5rem 0.75rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.85rem;
        background: white;
        font-family: inherit;
    }

    .filter-bar button {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
    }

    .card-section {
        background: white;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
    }

    .log-card {
        background: #fafbfe;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1rem 1.25rem;
        margin-bottom: 0.75rem;
        transition: all 0.2s;
    }

    .log-card:hover {
        border-color: #c7d2fe;
    }

    .log-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 0.4rem;
    }

    .log-name {
        font-weight: 700;
        font-size: 1rem;
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
    }

    .sig-mini {
        display: inline-flex;
        gap: 0.35rem;
        margin-top: 0.4rem;
    }

    .sig-mini span {
        font-size: 0.72rem;
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
        font-weight: 600;
    }

    .sig-mini .on {
        background: #d1fae5;
        color: #065f46;
    }

    .sig-mini .off {
        background: #f1f5f9;
        color: #94a3b8;
    }
</style>

<div class="clinical-page-header">
    <div class="right-side">
        <h1>📋 سجل التحضير اليومي</h1>
        <p>كل السجلات اليومية للطلاب</p>
    </div>
    <div class="left-side"><a href="{{ route('doctor.clinical.index') }}" class="btn-back"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg> القسم العملي</a></div>
</div>

<form class="filter-bar">
    <select name="status">
        <option value="">كل الحالات</option>
        <option value="confirmed" {{ request('status')=='confirmed'?'selected':'' }}>مؤكد</option>
        <option value="pending" {{ request('status')=='pending'?'selected':'' }}>بانتظار</option>
        <option value="rejected" {{ request('status')=='rejected'?'selected':'' }}>مرفوض</option>
    </select>
    <input type="date" name="date" value="{{ request('date') }}">
    <button type="submit">🔍 بحث</button>
</form>

<div class="card-section">
    @forelse($logs as $log)
    <div class="log-card">
        <div class="log-header">
            <span class="log-name">{{ $log->student->name ?? '-' }}</span>
            <span class="status-badge {{ $log->status }}">{{ $log->status_label }}</span>
        </div>
        <div class="log-meta">
            <span>🏥 {{ $log->trainingCenter->name ?? '-' }}</span>
            <span>🏷 {{ $log->department->name ?? '-' }}</span>
            <span>📅 {{ $log->log_date->format('Y-m-d') }}</span>
            @if($log->confirmed_by)<span>✍ {{ $log->confirmedBy->name ?? '-' }}</span>@endif
        </div>
        <div class="sig-mini">
            <span class="on">حضور ✓</span>
            <span class="{{ $log->history_count > 0 ? 'on' : 'off' }}">قصص: {{ $log->history_count }}</span>
            <span class="{{ $log->exam_count > 0 ? 'on' : 'off' }}">فحص: {{ $log->exam_count }}</span>
            <span class="{{ $log->did_round ? 'on' : 'off' }}">مرور {{ $log->did_round ? '✓' : '✗' }}</span>
        </div>
    </div>
    @empty
    <div style="text-align:center;padding:2.5rem;color:var(--text-secondary);">
        <p>لا توجد سجلات بعد.</p>
    </div>
    @endforelse

    {{ $logs->links() }}
</div>
@endsection