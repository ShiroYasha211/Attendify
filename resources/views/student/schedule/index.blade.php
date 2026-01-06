@extends('layouts.student')

@section('title', 'الجدول الدراسي')

@section('content')

<!-- Header -->
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem;">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        الجدول الدراسي
    </h1>
    <p style="color: var(--text-secondary);">جدول المحاضرات الأسبوعي ومحاضرات اليوم</p>
</div>

<div class="row g-4 mb-5">
    <!-- Today's Lectures -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem; margin: 0;">
                    <span style="width: 8px; height: 8px; background: var(--success-color); border-radius: 50%; display: inline-block;"></span>
                    محاضرات اليوم
                    <span class="badge badge-success-subtle">{{ date('l') }}</span>
                </h3>
            </div>
            <div class="card-body p-4">
                @if($todayLectures->count() > 0)
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($todayLectures as $lecture)
                    <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1px solid #f1f5f9; border-radius: 12px; background: #f8fafc;">
                        <div style="min-width: 80px; text-align: center; border-left: 2px solid #e2e8f0; padding-left: 1rem;">
                            <div style="font-weight: 700; font-size: 1.1rem; color: var(--text-primary);">{{ \Carbon\Carbon::parse($lecture->start_time)->format('H:i') }}</div>
                            <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ \Carbon\Carbon::parse($lecture->end_time)->format('H:i') }}</div>
                        </div>
                        <div style="flex: 1;">
                            <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.25rem;">{{ $lecture->subject->name }}</h4>
                            <div style="display: flex; gap: 1rem; font-size: 0.85rem; color: var(--text-secondary);">
                                <span style="display: flex; align-items: center; gap: 0.25rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    {{ $lecture->subject->doctor->name ?? 'غير محدد' }}
                                </span>
                                <span style="display: flex; align-items: center; gap: 0.25rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                    </svg>
                                    {{ $lecture->hall_name ?? 'القاعة ؟' }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <!-- Status Indicator (Time based) -->
                            @php
                            $now = \Carbon\Carbon::now();
                            $start = \Carbon\Carbon::parse($lecture->start_time);
                            $end = \Carbon\Carbon::parse($lecture->end_time);
                            @endphp

                            @if($now->between($start, $end))
                            <span class="badge badge-success">جاري الآن</span>
                            @elseif($now->gt($end))
                            <span class="badge badge-secondary">انتهت</span>
                            @else
                            <span class="badge badge-info">قادمة</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.5; margin-bottom: 1rem;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                        <line x1="9" y1="9" x2="9.01" y2="9"></line>
                        <line x1="15" y1="9" x2="15.01" y2="9"></line>
                    </svg>
                    <p class="mb-0">لا توجد محاضرات مجدولة لهذا اليوم. استمتع بوقتك! 🎉</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Tomorrow's Peek -->
    <div class="col-lg-4">
        <div class="card h-100" style="background: linear-gradient(145deg, #ffffff, #f8fafc);">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-secondary); display: flex; align-items: center; gap: 0.5rem; margin: 0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"></path>
                        <path d="M12 5l7 7-7 7"></path>
                    </svg>
                    ماريكم غداً؟
                </h3>
            </div>
            <div class="card-body p-4">
                @if($tomorrowLectures->count() > 0)
                <ul style="list-style: none; padding: 0; margin: 0;">
                    @foreach($tomorrowLectures as $lecture)
                    <li style="display: flex; gap: 1rem; padding-bottom: 1rem; margin-bottom: 1rem; border-bottom: 1px dashed #e2e8f0; last-child: border-bottom: none;">
                        <div style="font-weight: 600; color: var(--primary-color);">{{ \Carbon\Carbon::parse($lecture->start_time)->format('H:i') }}</div>
                        <div>
                            <div style="font-weight: 600; font-size: 0.9rem;">{{ $lecture->subject->name }}</div>
                            <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ $lecture->hall_name }}</div>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @else
                <p style="color: var(--text-secondary); font-size: 0.9rem;">لا توجد محاضرات غداً.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Weekly Grid -->
<div class="card">
    <div class="card-header bg-white border-bottom pt-4 px-4 pb-2">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin: 0;">
            📅 الجدول الأسبوعي الكامل
        </h3>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered mb-0" style="text-align: center; vertical-align: middle;">
            <thead class="bg-light">
                <tr>
                    <th style="width: 150px; padding: 1.5rem 1rem;">اليوم</th>
                    <th>المحاضرات</th>
                </tr>
            </thead>
            <tbody>
                @php
                $days = [
                1 => 'السبت',
                2 => 'الأحد',
                3 => 'الإثنين',
                4 => 'الثلاثاء',
                5 => 'الأربعاء',
                6 => 'الخميس',
                7 => 'الجمعة',
                ];
                @endphp

                @foreach($days as $key => $dayName)
                <tr>
                    <td style="font-weight: 700; background: #f8fafc;">{{ $dayName }}</td>
                    <td style="padding: 1rem;">
                        @if(isset($weeklySchedule[$key]) && $weeklySchedule[$key]->count() > 0)
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: flex-start;">
                            @foreach($weeklySchedule[$key] as $lecture)
                            <div style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem; text-align: right; min-width: 200px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                <div style="font-weight: 700; opacity: 0.9; margin-bottom: 0.25rem;">{{ $lecture->subject->name }}</div>
                                <div style="font-size: 0.85rem; color: var(--text-secondary); display: flex; justify-content: space-between;">
                                    <span>{{ \Carbon\Carbon::parse($lecture->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($lecture->end_time)->format('H:i') }}</span>
                                    <span style="font-weight: 600; color: var(--primary-color);">{{ $lecture->hall_name }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <span style="color: var(--text-light); font-size: 0.9rem;">--</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection