@extends('layouts.admin')

@section('title', 'لوحة القيادة')

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
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 4px 12px -2px rgba(79, 70, 229, 0.4);
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
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="7" height="7"></rect>
            <rect x="14" y="3" width="7" height="7"></rect>
            <rect x="14" y="14" width="7" height="7"></rect>
            <rect x="3" y="14" width="7" height="7"></rect>
        </svg>
    </div>
    <div class="page-header-text">
        <h1>لوحة القيادة</h1>
        <p>مرحباً {{ $user->name }}، إليك ملخص سريع لما يحدث في النظام</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem;">

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
            <div style="font-size: 2rem; font-weight: 700; line-height: 1;">{{ $userStats['students_count'] }}</div>
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
            <div style="font-size: 2rem; font-weight: 700; line-height: 1;">{{ $userStats['doctors_count'] }}</div>
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
            <div style="font-size: 2rem; font-weight: 700; line-height: 1;">{{ $academicStats['subjects_count'] }}</div>
            <div style="color: var(--text-secondary); font-size: 0.9rem;">مادة دراسية</div>
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
            <div style="font-size: 2rem; font-weight: 700; line-height: 1;">{{ $userStats['delegates_count'] }}</div>
            <div style="color: var(--text-secondary); font-size: 0.9rem;">مندوب دفعة</div>
        </div>
    </div>

</div>

<!-- Secondary Stats Row -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem;">

    <!-- Attendance Rate Card -->
    <div class="card" style="text-align: center; padding: 1.5rem;">
        <div style="font-size: 2.5rem; font-weight: 700; color: var(--success-color);">{{ $attendanceStats['attendance_rate'] }}%</div>
        <div style="color: var(--text-secondary); font-size: 0.9rem;">نسبة الحضور العامة</div>
        <div style="margin-top: 0.75rem; height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden;">
            <div style="height: 100%; width: {{ $attendanceStats['attendance_rate'] }}%; background: var(--success-color); border-radius: 3px;"></div>
        </div>
    </div>

    <!-- At Risk Students Card -->
    <div class="card" style="text-align: center; padding: 1.5rem; {{ $atRiskStudents > 0 ? 'background: linear-gradient(135deg, #fef2f2, #fff);' : '' }}">
        <div style="font-size: 2.5rem; font-weight: 700; color: {{ $atRiskStudents > 0 ? '#dc2626' : 'var(--text-primary)' }};">{{ $atRiskStudents }}</div>
        <div style="color: var(--text-secondary); font-size: 0.9rem;">طالب معرض للحرمان</div>
        @if($atRiskStudents > 0)
        <a href="{{ route('admin.reports.index') }}" style="display: inline-block; margin-top: 0.5rem; font-size: 0.8rem; color: #dc2626;">عرض التفاصيل ←</a>
        @endif
    </div>

    <!-- Today Attendance Card -->
    <div class="card" style="text-align: center; padding: 1.5rem;">
        <div style="font-size: 2.5rem; font-weight: 700; color: var(--info-color);">{{ $todayAttendance }}</div>
        <div style="color: var(--text-secondary); font-size: 0.9rem;">سجل حضور اليوم</div>
        <div style="font-size: 0.8rem; color: #dc2626; margin-top: 0.5rem;">{{ $todayAbsent }} غائب</div>
    </div>

    <!-- Pending Users Card -->
    <div class="card" style="text-align: center; padding: 1.5rem; {{ $userStats['pending_users'] > 0 ? 'background: linear-gradient(135deg, #fffbeb, #fff);' : '' }}">
        <div style="font-size: 2.5rem; font-weight: 700; color: {{ $userStats['pending_users'] > 0 ? '#f59e0b' : 'var(--text-primary)' }};">{{ $userStats['pending_users'] }}</div>
        <div style="color: var(--text-secondary); font-size: 0.9rem;">حساب بانتظار التفعيل</div>
        @if($userStats['pending_users'] > 0)
        <a href="{{ route('admin.users.index') }}" style="display: inline-block; margin-top: 0.5rem; font-size: 0.8rem; color: #f59e0b;">مراجعة ←</a>
        @endif
    </div>

</div>

<!-- Charts & Tables Row -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">

    <!-- Attendance Breakdown -->
    <div class="card">
        <h3 style="margin-bottom: 1.5rem; font-size: 1.1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="20" x2="18" y2="10"></line>
                <line x1="12" y1="20" x2="12" y2="4"></line>
                <line x1="6" y1="20" x2="6" y2="14"></line>
            </svg>
            توزيع سجلات الحضور
        </h3>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; text-align: center;">
            <div style="padding: 1rem; background: rgba(16, 185, 129, 0.1); border-radius: 12px;">
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--success-color);">{{ $attendanceStats['present'] }}</div>
                <div style="font-size: 0.85rem; color: var(--text-secondary);">حاضر</div>
            </div>
            <div style="padding: 1rem; background: rgba(239, 68, 68, 0.1); border-radius: 12px;">
                <div style="font-size: 1.5rem; font-weight: 700; color: #ef4444;">{{ $attendanceStats['absent'] }}</div>
                <div style="font-size: 0.85rem; color: var(--text-secondary);">غائب</div>
            </div>
            <div style="padding: 1rem; background: rgba(245, 158, 11, 0.1); border-radius: 12px;">
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--warning-color);">{{ $attendanceStats['late'] }}</div>
                <div style="font-size: 0.85rem; color: var(--text-secondary);">متأخر</div>
            </div>
            <div style="padding: 1rem; background: rgba(59, 130, 246, 0.1); border-radius: 12px;">
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--info-color);">{{ $attendanceStats['excused'] }}</div>
                <div style="font-size: 0.85rem; color: var(--text-secondary);">معذور</div>
            </div>
        </div>

        <!-- Visual Bar -->
        @if($attendanceStats['total'] > 0)
        <div style="margin-top: 1.5rem;">
            <div style="height: 12px; background: #e5e7eb; border-radius: 6px; overflow: hidden; display: flex;">
                <div style="height: 100%; width: {{ ($attendanceStats['present'] / $attendanceStats['total']) * 100 }}%; background: var(--success-color);" title="حاضر"></div>
                <div style="height: 100%; width: {{ ($attendanceStats['late'] / $attendanceStats['total']) * 100 }}%; background: var(--warning-color);" title="متأخر"></div>
                <div style="height: 100%; width: {{ ($attendanceStats['excused'] / $attendanceStats['total']) * 100 }}%; background: var(--info-color);" title="معذور"></div>
                <div style="height: 100%; width: {{ ($attendanceStats['absent'] / $attendanceStats['total']) * 100 }}%; background: #ef4444;" title="غائب"></div>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 0.5rem; font-size: 0.75rem; color: var(--text-secondary);">
                <span>إجمالي: {{ $attendanceStats['total'] }} سجل</span>
                <span>نسبة الحضور: {{ $attendanceStats['attendance_rate'] }}%</span>
            </div>
        </div>
        @endif
    </div>

    <!-- Students per Major -->
    <div class="card">
        <h3 style="margin-bottom: 1.5rem; font-size: 1.1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
            </svg>
            الطلاب حسب التخصص
        </h3>

        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            @forelse($studentsPerMajor as $major)
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="flex: 1; font-size: 0.9rem; font-weight: 500;">{{ $major->name }}</div>
                <div style="font-weight: 700; color: var(--primary-color);">{{ $major->students_count }}</div>
            </div>
            @empty
            <div style="text-align: center; color: var(--text-secondary); padding: 2rem;">لا توجد تخصصات</div>
            @endforelse
        </div>
    </div>

</div>

<!-- Bottom Section -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">

    <!-- Recent Attendance Table -->
    <div class="card">
        <h3 style="margin-bottom: 1.5rem; font-size: 1.1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            آخر سجلات الحضور
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

    <!-- Quick Actions & Top Absent Subjects -->
    <div>
        <!-- Quick Actions -->
        <div class="card" style="margin-bottom: 1.5rem; background: linear-gradient(135deg, var(--primary-color), #2e268a); color: white;">
            <h3 style="margin-bottom: 0.5rem;">مشاهدة التقارير</h3>
            <p style="opacity: 0.8; font-size: 0.9rem; margin-bottom: 1.5rem;">اطلع على تقارير الحضور والغياب للمواد المختلفة.</p>
            <a href="{{ route('admin.reports.index') }}" class="btn" style="background: white; color: var(--primary-color); width: 100%;">
                عرض التقارير
            </a>
        </div>

        <!-- Top Absent Subjects -->
        <div class="card">
            <h3 style="margin-bottom: 1rem; font-size: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                أكثر المواد غياباً
            </h3>
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                @forelse($topAbsentSubjects as $subject)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                    <span style="font-size: 0.9rem;">{{ $subject->name }}</span>
                    <span style="font-size: 0.8rem; color: #ef4444; font-weight: 600;">{{ $subject->absent_count }} غياب</span>
                </div>
                @empty
                <div style="text-align: center; color: var(--text-secondary); padding: 1rem; font-size: 0.9rem;">لا توجد بيانات</div>
                @endforelse
            </div>
        </div>
    </div>

</div>

@endsection