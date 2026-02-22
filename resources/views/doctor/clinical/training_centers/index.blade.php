@extends('layouts.doctor')

@section('title', 'مراكز التدريب السريري')

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

    .center-name {
        font-weight: 700;
        color: var(--primary-color);
    }

    .alert {
        padding: 1rem 1.25rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #10b981;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #ef4444;
    }
</style>

<div class="dashboard-header">
    <div class="welcome-text">
        <h1>مراكز التدريب السريري 🏥</h1>
        <p>إدارة المستشفيات والمراكز التي يتم فيها تدريب الطلاب وتوزيع الحالات</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card-section">
    <div class="section-header">
        <h3 class="section-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary-color);">
                <path d="M3 21h18"></path>
                <path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path>
                <line x1="9" y1="9" x2="15" y2="9"></line>
                <line x1="9" y1="13" x2="15" y2="13"></line>
            </svg>
            قائمة المراكز المتاحة
        </h3>
        <a href="{{ route('doctor.clinical.training-centers.create') }}" class="btn-create">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            إضافة مركز جديد
        </a>
    </div>

    <div style="overflow-x: auto;">
        <table class="table-modern">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="25%">اسم المركز</th>
                    <th width="20%">الموقع</th>
                    <th width="35%">الوصف</th>
                    <th width="15%">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($centers as $index => $center)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="center-name">{{ $center->name }}</td>
                    <td>{{ $center->location ?? '-' }}</td>
                    <td style="color: var(--text-secondary); font-size: 0.85rem;">{{ Str::limit($center->description, 60) ?? '-' }}</td>
                    <td>
                        <div style="display: flex;">
                            <a href="{{ route('doctor.clinical.training-centers.edit', $center->id) }}" class="action-btn edit" title="تعديل">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </a>
                            <form action="{{ route('doctor.clinical.training-centers.destroy', $center->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من مسح هذا المركز؟')">
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
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 3rem 1rem;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="color: #cbd5e1; margin-bottom: 1rem;">
                            <path d="M3 21h18"></path>
                            <path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path>
                            <line x1="9" y1="9" x2="15" y2="9"></line>
                            <line x1="9" y1="13" x2="15" y2="13"></line>
                        </svg>
                        <p>لا يوجد مراكز تدريب مضافة حالياً.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($centers->hasPages())
    <div style="margin-top: 1.5rem;">
        {{ $centers->links() }}
    </div>
    @endif
</div>

@endsection