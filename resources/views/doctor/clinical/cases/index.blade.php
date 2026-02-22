@extends('layouts.doctor')

@section('title', 'قائمة الحالات المرضية')

@section('content')
<style>
    .dashboard-header {
        margin-bottom: 2rem;
    }

    .welcome-text h1 {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .welcome-text p {
        color: var(--text-secondary);
        font-size: 0.95rem;
    }

    .card-section {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-create {
        background: var(--primary-color);
        color: white;
        padding: 0.6rem 1.2rem;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }

    .btn-create:hover {
        background: #4338ca;
        color: white;
        transform: translateY(-1px);
    }

    .table-modern {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-modern th {
        background: #f8fafc;
        font-weight: 600;
        color: var(--text-secondary);
        padding: 1rem;
        text-align: right;
        font-size: 0.85rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .table-modern td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.95rem;
        color: var(--text-primary);
    }

    .table-modern tr:hover td {
        background: #f8fafc;
    }

    .table-modern tr:last-child td {
        border-bottom: none;
    }

    .action-btn {
        background: #f1f5f9;
        border: none;
        padding: 0.5rem;
        border-radius: 8px;
        color: var(--text-secondary);
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        text-decoration: none;
        margin-left: 0.25rem;
    }

    .action-btn:hover {
        background: #e2e8f0;
        color: var(--text-primary);
    }

    .action-btn.edit:hover {
        background: #eff6ff;
        color: #3b82f6;
    }

    .action-btn.delete:hover {
        background: #fee2e2;
        color: #ef4444;
    }

    .patient-name {
        font-weight: 700;
        color: var(--primary-color);
    }

    .badge-status {
        padding: 0.3rem 0.6rem;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.8rem;
    }

    .badge-status.active {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-status.discharged {
        background: #f1f5f9;
        color: #475569;
    }

    .badge-status.transferred {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-system {
        background: #e0e7ff;
        color: #4338ca;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
    }
</style>

<div class="dashboard-header">
    <div class="welcome-text">
        <h1>الحالات المرضية 🛏️</h1>
        <p>إدارة وتشخيص الحالات السريرية في مراكز التدريب</p>
    </div>
</div>

@if(session('success'))
<div style="background: #d1fae5; color: #065f46; padding: 1rem 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid #10b981; font-weight: 600;">
    {{ session('success') }}
</div>
@endif

<div class="card-section">
    <div class="section-header">
        <h3 class="section-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary-color);">
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
            </svg>
            سجل الحالات السريرية
        </h3>
        <a href="{{ route('doctor.clinical.cases.create') }}" class="btn-create">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            إدراج حالة جديدة
        </a>
    </div>

    <div style="overflow-x: auto;">
        <table class="table-modern">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="20%">اسم الحالة/المريض</th>
                    <th width="10%">العمر/الجنس</th>
                    <th width="25%">المركز (القسم)</th>
                    <th width="15%">الجهاز المرضي</th>
                    <th width="10%">التاريخ</th>
                    <th width="5%">الحالة</th>
                    <th width="10%" style="text-align: center;">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cases as $case)
                <tr>
                    <td>{{ $loop->iteration + $cases->firstItem() - 1 }}</td>
                    <td class="patient-name">{{ $case->patient_name }}</td>
                    <td>
                        {{ $case->age ?? '-' }} <br>
                        <span style="font-size: 0.8rem; color: var(--text-secondary);">
                            @if($case->gender == 'male') ذكر @elseif($case->gender == 'female') أنثى @else - @endif
                        </span>
                    </td>
                    <td>
                        <div style="font-weight: 600;">{{ $case->trainingCenter->name ?? '-' }}</div>
                        <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ $case->clinicalDepartment->name ?? '-' }}</div>
                    </td>
                    <td><span class="badge-system">{{ $case->bodySystem->name ?? '-' }}</span></td>
                    <td style="font-size: 0.85rem; color: var(--text-secondary);">{{ $case->created_at->format('Y-m-d') }}</td>
                    <td>
                        @if($case->status == 'active')
                        <span class="badge-status active">نشطة</span>
                        @elseif($case->status == 'discharged')
                        <span class="badge-status discharged">مُخلى</span>
                        @else
                        <span class="badge-status transferred">محول</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <a href="{{ route('doctor.clinical.cases.edit', $case->id) }}" class="action-btn edit" title="تعديل">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </a>
                        <form action="{{ route('doctor.clinical.cases.destroy', $case->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من مسح الحالة؟ ستمسح معها جميع المهام السريرية المرتبطة بها.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn delete" title="مسح">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                </svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 3rem 1rem;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="color: #cbd5e1; margin-bottom: 1rem;">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                        </svg>
                        <p>لم تقم بإدراج أي حالة سريرية بعد.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($cases->hasPages())
    <div style="margin-top: 1.5rem;">
        {{ $cases->links() }}
    </div>
    @endif
</div>
@endsection