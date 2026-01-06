@extends('layouts.student')

@section('title', $subject->name)

@section('content')

<!-- Header with Tabs -->
<div x-data="{ activeTab: 'overview' }" class="mb-4">

    <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 1.5rem; margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">{{ $subject->name }}</h1>
                <div style="display: flex; align-items: center; gap: 1rem; color: var(--text-secondary);">
                    <span class="badge badge-primary">{{ $subject->code }}</span>
                    <span><span style="font-weight: 600;">الدكتور:</span> {{ $subject->doctor->name ?? 'غير محدد' }}</span>
                </div>
            </div>

            <a href="{{ route('student.subjects.index') }}" class="btn btn-secondary" style="display: flex; align-items: center; gap: 0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                عودة للقائمة
            </a>
        </div>

        <div style="display: flex; gap: 1rem; border-bottom: 1px solid #e2e8f0;">
            <button @click="activeTab = 'overview'" :class="{ 'active-tab': activeTab === 'overview' }" class="tab-btn" style="display: flex; align-items: center; gap: 0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
                نظرة عامة
            </button>
            <button @click="activeTab = 'assignments'" :class="{ 'active-tab': activeTab === 'assignments' }" class="tab-btn" style="display: flex; align-items: center; gap: 0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                التكاليف والواجبات
                @if($assignments->count() > 0)
                <span class="badge badge-warning" style="margin-right: 0.5rem;">{{ $assignments->count() }}</span>
                @endif
            </button>
            <button @click="activeTab = 'attendance'" :class="{ 'active-tab': activeTab === 'attendance' }" class="tab-btn" style="display: flex; align-items: center; gap: 0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                سجل الحضور
            </button>
        </div>
    </div>

    <!-- Overview Tab -->
    <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">

            <!-- Attendance Rate -->
            <div class="card" style="text-align: center; padding: 2rem;">
                <div style="width: 80px; height: 80px; margin: 0 auto 1rem; position: relative; display: flex; align-items: center; justify-content: center;">
                    <svg viewBox="0 0 36 36" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e2e8f0" stroke-width="3" />
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="{{ $attendancePercentage >= 75 ? 'var(--success-color)' : ($attendancePercentage >= 50 ? 'var(--warning-color)' : 'var(--danger-color)') }}" stroke-width="3" stroke-dasharray="{{ $attendancePercentage }}, 100" />
                    </svg>
                    <div style="position: absolute; font-size: 1.2rem; font-weight: 700; color: var(--text-primary);">{{ $attendancePercentage }}%</div>
                </div>
                <h3 style="font-size: 1rem; color: var(--text-secondary);">نسبة الحضور</h3>
            </div>

            <!-- Stats -->
            <div class="card" style="display: flex; gap: 1rem; align-items: center;">
                <div style="width: 48px; height: 48px; background: #dcfce7; color: #15803d; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;">{{ $presentCount }}</div>
                    <div style="color: var(--text-secondary); font-size: 0.9rem;">محاضرة حضور</div>
                </div>
            </div>

            <div class="card" style="display: flex; gap: 1rem; align-items: center;">
                <div style="width: 48px; height: 48px; background: #fee2e2; color: #b91c1c; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;">{{ $absentCount }}</div>
                    <div style="color: var(--text-secondary); font-size: 0.9rem;">محاضرة غياب</div>
                </div>
            </div>

        </div>
    </div>

    <!-- Assignments Tab -->
    <div x-show="activeTab === 'assignments'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
        <div class="card">
            <h3 style="margin-bottom: 1.5rem; font-size: 1.1rem; padding-bottom: 1rem; border-bottom: 1px solid #f1f5f9;">التكاليف الدراسية</h3>

            @forelse($assignments as $assignment)
            <div style="padding: 1.5rem; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 1rem; transition: all 0.2s; background: #f8fafc;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                    <div>
                        <h4 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem;">{{ $assignment->title }}</h4>
                        <div style="display: flex; gap: 1rem; font-size: 0.85rem; color: var(--text-secondary);">
                            <span>📅 تاريخ التسليم: {{ \Carbon\Carbon::parse($assignment->due_date)->format('Y-m-d') }}</span>
                            @if(\Carbon\Carbon::parse($assignment->due_date)->isPast())
                            <span class="text-danger">منتهي</span>
                            @else
                            <span class="text-success">متاح</span>
                            @endif
                        </div>
                    </div>
                </div>
                <p style="color: var(--text-secondary); margin-bottom: 0;">{{ $assignment->description }}</p>
            </div>
            @empty
            <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📝</div>
                <p>لا توجد تكاليف دراسية لهذه المادة حالياً.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Attendance Tab -->
    <div x-show="activeTab === 'attendance'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
        <div class="card">
            <h3 style="margin-bottom: 1.5rem; font-size: 1.1rem; padding-bottom: 1rem; border-bottom: 1px solid #f1f5f9;">سجل الحضور التفصيلي</h3>

            <table style="width: 100%; border-collapse: separate; border-spacing: 0;">
                <thead>
                    <tr style="background-color: #f8fafc; text-align: right;">
                        <th style="padding: 1rem; border-bottom: 1px solid #e2e8f0; width: 60px;">#</th>
                        <th style="padding: 1rem; border-bottom: 1px solid #e2e8f0;">التاريخ</th>
                        <th style="padding: 1rem; border-bottom: 1px solid #e2e8f0; text-align: center;">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendanceRecords as $index => $record)
                    <tr>
                        <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9;">{{ $index + 1 }}</td>
                        <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9; font-weight: 600;">{{ \Carbon\Carbon::parse($record->date)->format('Y-m-d') }}</td>
                        <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9; text-align: center;">
                            @if($record->status == 'present')
                            <span class="badge badge-success">حاضر</span>
                            @elseif($record->status == 'absent')
                            <span class="badge badge-danger">غائب</span>
                            @elseif($record->status == 'late')
                            <span class="badge badge-warning">متأخر</span>
                            @elseif($record->status == 'excused')
                            <span class="badge badge-info">بعذر</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 2rem; color: var(--text-secondary);">لا يوجد سجلات حضور حتى الآن.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<style>
    .tab-btn {
        background: none;
        border: none;
        padding: 1rem 1.5rem;
        font-family: inherit;
        font-weight: 600;
        color: var(--text-secondary);
        cursor: pointer;
        border-bottom: 2px solid transparent;
        transition: all 0.2s;
    }

    .tab-btn:hover {
        color: var(--primary-color);
    }

    .tab-btn.active-tab {
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
    }
</style>

@endsection