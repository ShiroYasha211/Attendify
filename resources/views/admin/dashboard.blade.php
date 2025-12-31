@extends('layouts.admin')

@section('title', 'لوحة القيادة')

@section('content')

<!-- Welcome Section -->
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.8rem; font-weight: 700; color: var(--text-primary);">نظرة عامة على النظام</h1>
    <p style="color: var(--text-secondary);">مرحباً، إليك ملخص سريع لما يحدث في النظام اليوم.</p>
</div>

<!-- Stats Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">

    <!-- Students Card -->
    <div class="card" style="display: flex; align-items: center; gap: 1.5rem; border-right: 4px solid var(--primary-color);">
        <div style="width: 50px; height: 50px; background: rgba(67, 56, 202, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary-color);">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div>
            <div style="font-size: 2rem; font-weight: 700; line-height: 1;">{{ $stats['students_count'] }}</div>
            <div style="color: var(--text-secondary); font-size: 0.9rem;">إجمالي الطلاب</div>
        </div>
    </div>

    <!-- Doctors Card -->
    <div class="card" style="display: flex; align-items: center; gap: 1.5rem; border-right: 4px solid var(--info-color);">
        <div style="width: 50px; height: 50px; background: rgba(59, 130, 246, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--info-color);">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
        </div>
        <div>
            <div style="font-size: 2rem; font-weight: 700; line-height: 1;">{{ $stats['doctors_count'] }}</div>
            <div style="color: var(--text-secondary); font-size: 0.9rem;">أعضاء هيئة التدريس</div>
        </div>
    </div>

    <!-- Subjects Card -->
    <div class="card" style="display: flex; align-items: center; gap: 1.5rem; border-right: 4px solid var(--success-color);">
        <div style="width: 50px; height: 50px; background: rgba(16, 185, 129, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--success-color);">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
        </div>
        <div>
            <div style="font-size: 2rem; font-weight: 700; line-height: 1;">{{ $stats['subjects_count'] }}</div>
            <div style="color: var(--text-secondary); font-size: 0.9rem;">مادة دراسية نشطة</div>
        </div>
    </div>

    <!-- Delegates Card -->
    <div class="card" style="display: flex; align-items: center; gap: 1.5rem; border-right: 4px solid var(--warning-color);">
        <div style="width: 50px; height: 50px; background: rgba(245, 158, 11, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--warning-color);">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
        </div>
        <div>
            <div style="font-size: 2rem; font-weight: 700; line-height: 1;">{{ $stats['delegates_count'] }}</div>
            <div style="color: var(--text-secondary); font-size: 0.9rem;">مندوب دفعة</div>
        </div>
    </div>

</div>

<!-- Recent Activity & Quick Actions -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">

    <!-- Recent Attendance Table -->
    <div class="card">
        <h3 style="margin-bottom: 1.5rem; font-size: 1.1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
            🕒 آخر عمليات تسجيل الحضور
        </h3>

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: right; color: var(--text-secondary); font-size: 0.9rem;">
                    <th style="padding-bottom: 0.5rem;">المادة</th>
                    <th style="padding-bottom: 0.5rem;">الطالب</th>
                    <th style="padding-bottom: 0.5rem;">الحالة</th>
                    <th style="padding-bottom: 0.5rem;">التاريخ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($latestAttendance as $attendance)
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 1rem 0; font-weight: 600;">{{ $attendance->subject->name ?? '-' }}</td>
                    <td style="padding: 1rem 0;">{{ $attendance->student->name ?? '-' }}</td>
                    <td style="padding: 1rem 0;">
                        @if($attendance->status == 'present')
                        <span class="badge badge-success">حاضر</span>
                        @elseif($attendance->status == 'absent')
                        <span class="badge badge-danger">غائب</span>
                        @elseif($attendance->status == 'late')
                        <span class="badge badge-warning">متأخر</span>
                        @else
                        <span class="badge badge-info">معذور</span>
                        @endif
                    </td>
                    <td style="padding: 1rem 0; color: var(--text-secondary); font-size: 0.85rem;">
                        {{ $attendance->date->format('Y-m-d') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                        لا يوجد سجلات حضور حديثة.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 1.5rem; text-align: center;">
            <a href="{{ route('admin.reports.index') }}" style="color: var(--primary-color); font-weight: 600; font-size: 0.9rem;">عرض كل التقارير &larr;</a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div>
        <div class="card" style="margin-bottom: 1.5rem; background: linear-gradient(135deg, var(--primary-color), #2e268a); color: white;">
            <h3 style="margin-bottom: 0.5rem;">مشاهدة التقارير</h3>
            <p style="opacity: 0.8; font-size: 0.9rem; margin-bottom: 1.5rem;">اطلع على تقارير الحضور والغياب للمواد المختلفة.</p>
            <a href="{{ route('admin.reports.index') }}" class="btn" style="background: white; color: var(--primary-color); width: 100%;">
                عرض التقارير
            </a>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">روابط سريعة</h3>
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <a href="{{ route('admin.students.index') }}" style="padding: 0.75rem; background: var(--bg-body); border-radius: 6px; display: flex; align-items: center; gap: 0.5rem; font-weight: 500;">
                    <span style="color: var(--success-color);">+</span> إضافة طالب جديد
                </a>
                <a href="{{ route('admin.subjects.index') }}" style="padding: 0.75rem; background: var(--bg-body); border-radius: 6px; display: flex; align-items: center; gap: 0.5rem; font-weight: 500;">
                    <span style="color: var(--info-color);">+</span> إضافة مادة جديدة
                </a>
                <a href="{{ route('admin.doctors.index') }}" style="padding: 0.75rem; background: var(--bg-body); border-radius: 6px; display: flex; align-items: center; gap: 0.5rem; font-weight: 500;">
                    <span style="color: var(--warning-color);">+</span> إضافة دكتور
                </a>
            </div>
        </div>
    </div>

</div>

@endsection