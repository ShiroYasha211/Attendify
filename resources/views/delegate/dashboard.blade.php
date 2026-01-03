@extends('layouts.delegate')

@section('title', 'لوحة القيادة')

@section('content')

<!-- Welcome Section (Clean & Simple like Admin) -->
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.8rem; font-weight: 700; color: var(--text-primary);">{{ $delegate->university->name ?? 'الجامعة غير محددة' }}</h1>
    <p style="color: var(--text-secondary);">
        {{ $delegate->college->name ?? 'الكلية' }} - {{ $delegate->major->name ?? 'التخصص' }} (المستوى {{ $delegate->level->name ?? '-' }})
    </p>
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
            <div style="font-size: 2rem; font-weight: 700; line-height: 1;">{{ $studentsCount ?? 0 }}</div>
            <div style="color: var(--text-secondary); font-size: 0.9rem;">طالب في الدفعة</div>
        </div>
    </div>

    <!-- Subjects Card -->
    <div class="card" style="display: flex; align-items: center; gap: 1.5rem; border-right: 4px solid var(--info-color);">
        <div style="width: 50px; height: 50px; background: rgba(59, 130, 246, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--info-color);">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
        </div>
        <div>
            <div style="font-size: 2rem; font-weight: 700; line-height: 1;">{{ count($subjects) }}</div>
            <div style="color: var(--text-secondary); font-size: 0.9rem;">مادة دراسية</div>
        </div>
    </div>

    <!-- Today's Lectures Card -->
    <div class="card" style="display: flex; align-items: center; gap: 1.5rem; border-right: 4px solid var(--success-color);">
        <div style="width: 50px; height: 50px; background: rgba(16, 185, 129, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--success-color);">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
        </div>
        <div>
            <div style="font-size: 2rem; font-weight: 700; line-height: 1;">{{ $todayLecturesCount ?? 0 }}</div>
            <div style="color: var(--text-secondary); font-size: 0.9rem;">محاضرات اليوم</div>
        </div>
    </div>

    <!-- Attendance Alerts Card -->
    <div class="card" style="display: flex; align-items: center; gap: 1.5rem; border-right: 4px solid var(--warning-color);">
        <div style="width: 50px; height: 50px; background: rgba(245, 158, 11, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--warning-color);">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
        </div>
        <div>
            <div style="font-size: 2rem; font-weight: 700; line-height: 1;">{{ $alertsCount ?? 0 }}</div>
            <div style="color: var(--text-secondary); font-size: 0.9rem;">تنبيه غياب</div>
        </div>
    </div>

</div>

<!-- Content Area: Subjects and Latest Activity -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">

    <!-- Subjects List -->
    <div class="card">
        <h3 style="margin-bottom: 1.5rem; font-size: 1.1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
            <span>📚 المواد المسجلة</span>
            <a href="{{ route('delegate.subjects.index') }}" class="btn btn-sm btn-secondary" style="font-size: 0.8rem; padding: 0.3rem 0.8rem;">عرض الكل</a>
        </h3>

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: right; color: var(--text-secondary); font-size: 0.9rem;">
                    <th style="padding-bottom: 0.5rem;">المادة</th>
                    <th style="padding-bottom: 0.5rem;">الدكتور</th>
                    <th style="padding-bottom: 0.5rem;">عدد الطلاب</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subjects as $subject)
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 1rem 0;">
                        <div style="font-weight: 600; color: var(--text-primary);">{{ $subject->name }}</div>
                        <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ $subject->code }}</div>
                    </td>
                    <td style="padding: 1rem 0;">
                        @if($subject->doctor)
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 24px; height: 24px; background: rgba(67, 56, 202, 0.1); color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: bold;">
                                {{ mb_substr($subject->doctor->name, 0, 1) }}
                            </div>
                            <span>{{ $subject->doctor->name }}</span>
                        </div>
                        @else
                        <span style="color: var(--text-light);">غير محدد</span>
                        @endif
                    </td>
                    <td style="padding: 1rem 0;">
                        <span class="badge badge-info">{{ $studentsCount }} طالب</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                        لا توجد مواد مسجلة حالياً.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Latest Activity Column -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">

        <!-- Quick Attendance Log -->
        <div class="card">
            <h3 style="margin-bottom: 1rem; font-size: 1.1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                🕒 آخر الحضور
            </h3>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                @forelse($latestAttendance as $attendance)
                <div style="display: flex; align-items: center; gap: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f8fafc;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem; flex-shrink: 0;
                        background-color: {{ $attendance->status == 'present' ? '#d1fae5' : ($attendance->status == 'absent' ? '#ffe4e6' : '#fef3c7') }};
                        color: {{ $attendance->status == 'present' ? '#065f46' : ($attendance->status == 'absent' ? '#9f1239' : '#92400e') }};">
                        {{ mb_substr($attendance->student->name, 0, 1) }}
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 600; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $attendance->student->name }}</div>
                        <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ $attendance->subject->name }}</div>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-light);">
                        {{ $attendance->created_at->format('H:i') }}
                    </div>
                </div>
                @empty
                <div style="text-align: center; color: var(--text-secondary); padding: 1rem;">
                    لا يوجد سجلات حديثة
                </div>
                @endforelse
            </div>
            <div style="text-align: center; margin-top: 1rem;">
                <a href="{{ route('delegate.attendance.index') }}" style="font-size: 0.85rem; color: var(--primary-color); font-weight: 600;">عرض سجل الحضور &larr;</a>
            </div>
        </div>

        <!-- Latest Students -->
        <div class="card">
            <h3 style="margin-bottom: 1rem; font-size: 1.1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                🎓 آخر المنضمين
            </h3>
            <ul style="padding: 0; margin: 0; list-style: none;">
                @forelse($latestStudents as $student)
                <li style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0; border-bottom: 1px solid #f8fafc;">
                    <div style="width: 8px; height: 8px; background-color: var(--success-color); border-radius: 50%;"></div>
                    <span style="font-size: 0.9rem; font-weight: 500;">{{ $student->name }}</span>
                    <span style="font-size: 0.75rem; color: var(--text-light); margin-right: auto;">{{ $student->created_at->diffForHumans(null, true, true) }}</span>
                </li>
                @empty
                <li style="text-align: center; color: var(--text-secondary); padding: 1rem;">لا يوجد طلاب جدد</li>
                @endforelse
            </ul>
        </div>

    </div>

</div>

@endsection