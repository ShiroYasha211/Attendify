@extends('layouts.doctor')

@section('title', 'القسم العملي (Clinical Hub)')

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

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.2s;
        text-decoration: none;
        color: inherit;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px -4px rgba(0, 0, 0, 0.1);
        text-decoration: none;
        color: inherit;
    }

    .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon.primary {
        background: rgba(79, 70, 229, 0.1);
        color: #4f46e5;
    }

    .stat-icon.success {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .stat-icon.warning {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .stat-icon.info {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .stat-content h3 {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .stat-content span {
        color: var(--text-secondary);
        font-size: 0.85rem;
        font-weight: 600;
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

    .btn-create-center {
        background: #f1f5f9;
        color: var(--text-primary);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: all 0.2s;
    }

    .btn-create-center:hover {
        background: #e2e8f0;
        color: var(--text-primary);
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

    .badge-cases {
        background: #eff6ff;
        color: #3b82f6;
        padding: 0.3rem 0.6rem;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.85rem;
    }
</style>

<div class="dashboard-header">
    <div class="welcome-text">
        <h1>القسم العملي (Clinical Hub) 🩺</h1>
        <p>إدارة الحالات المرضية وتوزيعها على الطلاب للتدريب السريري</p>
    </div>
</div>

<div class="stats-grid">
    <a href="{{ route('doctor.clinical.cases.index') }}" class="stat-card" title="إجمالي الحالات">
        <div class="stat-icon primary">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                <path d="M12 11h4"></path>
                <path d="M12 16h4"></path>
                <path d="M8 11h.01"></path>
                <path d="M8 16h.01"></path>
            </svg>
        </div>
        <div class="stat-content">
            <h3>{{ $totalCases }}</h3>
            <span>إجمالي الحالات המסجلة</span>
        </div>
    </a>

    <a href="{{ route('doctor.clinical.cases.index') }}" class="stat-card" title="الحالات النشطة">
        <div class="stat-icon success">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
            </svg>
        </div>
        <div class="stat-content">
            <h3>{{ $activeCases }}</h3>
            <span>الحالات النشطة حالياً</span>
        </div>
    </a>

    <a href="{{ route('doctor.clinical.assignments.index') }}" class="stat-card" title="توزيع الحالات">
        <div class="stat-icon warning">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div class="stat-content">
            <h3 style="font-size: 1.25rem; margin-top:0.4rem;">توزيع الطلاب</h3>
            <span>إسناد الحالات للتدريب</span>
        </div>
    </a>

    <a href="{{ route('doctor.clinical.training-centers.index') }}" class="stat-card" title="إدارة المراكز">
        <div class="stat-icon info">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 21h18"></path>
                <path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path>
                <line x1="9" y1="9" x2="15" y2="9"></line>
                <line x1="9" y1="13" x2="15" y2="13"></line>
            </svg>
        </div>
        <div class="stat-content">
            <h3 style="font-size: 1.25rem; margin-top:0.4rem;">مراكز التدريب</h3>
            <span>المستشفيات والأقسام</span>
        </div>
    </a>
</div>

<div class="card-section">
    <div class="section-header">
        <h3 class="section-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary-color);">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            مراكز التدريب التابعة لك
        </h3>
        <a href="{{ route('doctor.clinical.training-centers.create') }}" class="btn-create-center">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            إضافة مركز جديد
        </a>
    </div>

    @if($centers->count() > 0)
    <div style="overflow-x:auto;">
        <table class="table-modern">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="30%">اسم المستشفى / المركز</th>
                    <th width="40%">الموقع / الوصف</th>
                    <th width="25%">عدد حالاتك النشطة فيه</th>
                </tr>
            </thead>
            <tbody>
                @foreach($centers as $index => $center)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="font-weight: 700; color: var(--primary-color);">{{ $center->name }}</td>
                    <td>
                        {{ $center->location ?? '-' }}
                        <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.2rem;">{{ Str::limit($center->description, 50) }}</div>
                    </td>
                    <td>
                        <span class="badge-cases">{{ $center->cases_count }} حالة</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: #cbd5e1; margin-bottom: 1rem;">
            <path d="M3 21h18"></path>
            <path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path>
        </svg>
        <p>لا يوجد مراكز تدريب مضافة أو لا يوجد لديك حالات فيها بعد.</p>
    </div>
    @endif
</div>
@endsection