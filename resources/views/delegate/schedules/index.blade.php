@extends('layouts.delegate')

@section('title', 'الجدول الدراسي')

@section('content')

<div class="container" style="max-width: 100%;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">الجدول الدراسي</h1>
            <p style="color: var(--text-secondary);">جدول محاضرات الدفعة الأسبوعي.</p>
        </div>
        <a href="{{ route('delegate.schedules.create') }}" class="btn btn-primary" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.2rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            إضافة موعد
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 1.5rem;">
        {{ session('success') }}
    </div>
    @endif

    @foreach([1 => 'الإثنين', 2 => 'الثلاثاء', 3 => 'الأربعاء', 4 => 'الخميس', 5 => 'الجمعة', 6 => 'السبت', 7 => 'الأحد'] as $dayId => $dayName)
    @php
    $daySchedules = $schedules->where('day_of_week', $dayId);
    @endphp

    @if($daySchedules->count() > 0)
    <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--primary-color); border-bottom: 2px solid #e0e7ff; padding-bottom: 0.5rem; margin-bottom: 1rem; display: inline-block;">
            {{ $dayName }}
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
            @foreach($daySchedules as $schedule)
            <div class="card" style="display: flex; flex-direction: column; border-right: 4px solid var(--primary-color);">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                    <h4 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin: 0;">{{ $schedule->subject->name }}</h4>

                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <a href="{{ route('delegate.schedules.edit', $schedule->id) }}" style="color: var(--primary-color); padding: 0.2rem;" title="تعديل الموعد">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </a>
                        <form action="{{ route('delegate.schedules.destroy', $schedule->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الموعد؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: none; border: none; color: var(--danger-color); cursor: pointer; padding: 0.2rem;" title="حذف الموعد">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 1rem;">
                    دكتور: {{ $schedule->subject->doctor->name ?? 'غير محدد' }}
                </div>

                <div style="margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-secondary);">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                    </div>
                    <div style="background-color: #f3f4f6; color: var(--text-secondary); padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.85rem;">
                        {{ $schedule->hall_name }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endforeach

    @if($schedules->isEmpty())
    <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
        <div style="color: var(--text-secondary); margin-bottom: 1rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
        </div>
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">لا يوجد جدول دراسي</h3>
        <p style="color: var(--text-secondary);">ابدأ بإضافة مواعيد المحاضرات لتنظيم جدول الدفعة.</p>
        <a href="{{ route('delegate.schedules.create') }}" class="btn btn-primary" style="margin-top: 1rem;">
            إضافة أول موعد
        </a>
    </div>
    @endif
</div>

@endsection