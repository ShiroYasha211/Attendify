@extends('layouts.doctor')

@section('title', 'لوحة التحكم')

@section('content')

<!-- Header Section -->
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.8rem; font-weight: 700; color: var(--text-primary);">مرحباً د. {{ $doctor->name }}</h1>
    <p style="color: var(--text-secondary);">لوحة التحكم الخاصة بإدارة المقررات والطلاب</p>
</div>

<!-- Stats Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">

    <!-- Subjects Card -->
    <div class="card" style="display: flex; align-items: center; gap: 1.5rem; border-right: 4px solid var(--primary-color);">
        <div style="width: 50px; height: 50px; background: rgba(79, 70, 229, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary-color);">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
        </div>
        <div>
            <div style="font-size: 2rem; font-weight: 700; line-height: 1;">{{ $subjects->count() }}</div>
            <div style="color: var(--text-secondary); font-size: 0.9rem;">مقرر دراسي</div>
        </div>
    </div>

    <!-- Students Card -->
    <div class="card" style="display: flex; align-items: center; gap: 1.5rem; border-right: 4px solid var(--info-color);">
        <div style="width: 50px; height: 50px; background: rgba(59, 130, 246, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--info-color);">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div>
            <div style="font-size: 2rem; font-weight: 700; line-height: 1;">{{ $studentsCount }}</div>
            <div style="color: var(--text-secondary); font-size: 0.9rem;">طالب (إجمالي)</div>
        </div>
    </div>

    <!-- Pending Excuses Card (Placeholder) -->
    <div class="card" style="display: flex; align-items: center; gap: 1.5rem; border-right: 4px solid var(--warning-color);">
        <div style="width: 50px; height: 50px; background: rgba(245, 158, 11, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--warning-color);">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
        </div>
        <div>
            <div style="font-size: 2rem; font-weight: 700; line-height: 1;">{{ $pendingExcusesCount }}</div>
            <div style="color: var(--text-secondary); font-size: 0.9rem;">أعذار معلقة</div>
        </div>
    </div>

</div>

<!-- Recent Subjects List -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
            المقررات الدراسية
        </h3>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>اسم المقرر</th>
                    <th>التخصص</th>
                    <th>المستوى</th>
                    <th style="width: 120px;">الطلاب</th>
                    <th style="width: 150px;">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subjects as $subject)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="fw-bold">{{ $subject->name }}</td>
                    <td>{{ $subject->major->name ?? '-' }}</td>
                    <td><span class="badge badge-info">{{ $subject->level->name ?? '-' }}</span></td>
                    <td>{{ $subject->students_count }}</td>
                    <td>
                        <a href="{{ route('doctor.reports.show', $subject->id) }}" class="btn btn-sm btn-primary">
                            تقرير الحضور
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">لم يتم إسناد أي مقررات دراسية لك بعد.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection