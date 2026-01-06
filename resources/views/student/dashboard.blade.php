@extends('layouts.student')

@section('title', 'الرئيسية')

@section('content')

<!-- Header Section (Clean & Simple) -->
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.8rem; font-weight: 700; color: var(--text-primary);">{{ $student->university->name ?? 'النظام الأكاديمي' }}</h1>
    <p style="color: var(--text-secondary);">
        {{ $student->major->name ?? 'التخصص' }} - ({{ $student->level->name ?? 'المستوى' }})
    </p>
</div>

<!-- Stats Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">

    <!-- Subjects Card -->
    <div class="card" style="display: flex; align-items: center; gap: 1.5rem; border-right: 4px solid var(--primary-color);">
        <div style="width: 50px; height: 50px; background: rgba(67, 56, 202, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary-color);">
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

    <!-- Assignments Card -->
    <div class="card" style="display: flex; align-items: center; gap: 1.5rem; border-right: 4px solid var(--info-color);">
        <div style="width: 50px; height: 50px; background: rgba(59, 130, 246, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--info-color);">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
        </div>
        <div>
            <div style="font-size: 2rem; font-weight: 700; line-height: 1;">{{ $assignmentsCount }}</div>
            <div style="color: var(--text-secondary); font-size: 0.9rem;">تكليف نشط</div>
        </div>
    </div>

    <!-- Total Absences Card -->
    <div class="card" style="display: flex; align-items: center; gap: 1.5rem; border-right: 4px solid var(--danger-color);">
        <div style="width: 50px; height: 50px; background: rgba(239, 68, 68, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--danger-color);">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
        </div>
        <div>
            <div style="font-size: 2rem; font-weight: 700; line-height: 1;">{{ $totalAbsences }}</div>
            <div style="color: var(--text-secondary); font-size: 0.9rem;">أيام الغياب الكلي</div>
        </div>
    </div>

    <!-- Reminders Card -->
    <div class="card" style="display: flex; align-items: center; gap: 1.5rem; border-right: 4px solid var(--warning-color);">
        <div style="width: 50px; height: 50px; background: rgba(245, 158, 11, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--warning-color);">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>
        <div>
            <div style="font-size: 2rem; font-weight: 700; line-height: 1;">{{ $reminders->count() }}</div>
            <div style="color: var(--text-secondary); font-size: 0.9rem;">تذكير قادم</div>
        </div>
    </div>

</div>

<!-- Content Area: Announcements and Reminders -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: start;">

    <!-- Alerts Section (Deprivation + Excuses) -->
    <div style="grid-column: 1 / -1; margin-bottom: 2rem; display: flex; flex-direction: column; gap: 1rem;">

        <!-- Deprivation Warnings (Horman) -->
        @if(isset($warnings) && count($warnings) > 0)
        @foreach($warnings as $warn)
        <div class="alert alert-danger d-flex align-items-center justify-content-between" role="alert"
            style="border-radius: 12px; border: 1px solid #fca5a5; background-color: #fef2f2;">
            <div class="d-flex align-items-center gap-3">
                <div style="background: rgba(220, 38, 38, 0.1); padding: 8px; border-radius: 50%; color: #dc2626;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <div>
                    @if($warn['status'] == 'banned')
                    <strong>تحذير شديد:</strong> لقد تجاوزت الحد المسموح للغياب في مقرر <strong>{{ $warn['subject'] }}</strong> ({{ $warn['absences'] }}/{{ $warn['max'] }}). أنت معرض للحرمان!
                    @else
                    <strong>تحذير حرمان:</strong> انتبه! بقي لك غياب واحد فقط في مقرر <strong>{{ $warn['subject'] }}</strong> قبل الحرمان ({{ $warn['absences'] }}/{{ $warn['max'] }}).
                    @endif
                </div>
            </div>
        </div>
        @endforeach
        @endif

        <!-- Excuse Warnings -->
        @if(isset($excuseWarnings) && $excuseWarnings->count() > 0)
        @foreach($excuseWarnings as $warning)
        <div class="alert alert-warning d-flex align-items-center justify-content-between" role="alert" style="border-radius: 12px; border: 1px solid #fcd34d; background-color: #fffbeb;">
            <div class="d-flex align-items-center gap-3">
                <div style="background: rgba(245, 158, 11, 0.2); padding: 8px; border-radius: 50%; color: #d97706;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <div>
                    <strong>تنبيـه:</strong> تنتهي مهلة تقديم العذر لغياب مادة <strong>{{ $warning->subject->name }}</strong> بتاريخ {{ $warning->date->format('Y-m-d') }} قريباً.
                </div>
            </div>
            <a href="{{ route('student.attendance.index') }}" class="btn btn-sm btn-warning text-white fw-bold" style="border-radius: 8px;">تقديم عذر</a>
        </div>
        @endforeach
        @endif

    </div>


    <!-- Right Column: Announcements (News Feed) -->
    <div>
        <h3 style="margin-bottom: 1.5rem; font-size: 1.1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            آخر الأخبار والإعلانات
        </h3>

        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            @forelse($announcements as $post)
            <div class="card" style="border: none; position: relative; overflow: hidden; padding: 1.5rem;">
                <!-- Category Stripe -->
                <div style="position: absolute; right: 0; top: 0; bottom: 0; width: 4px; background: {{ $post->category == 'urgent' ? 'var(--danger-color)' : ($post->category == 'academic' ? 'var(--info-color)' : 'var(--secondary-color)') }};"></div>

                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span class="badge {{ $post->category == 'urgent' ? 'badge-danger' : ($post->category == 'academic' ? 'badge-info' : 'badge-secondary') }}">
                            {{ $post->category == 'urgent' ? 'عاجل' : ($post->category == 'academic' ? 'أكاديمي' : 'عام') }}
                        </span>
                        <span style="font-size: 0.8rem; color: var(--text-secondary);">{{ $post->created_at->diffForHumans() }}</span>
                    </div>
                </div>

                <h4 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.75rem;">{{ $post->title }}</h4>
                <p style="color: var(--text-secondary); font-size: 0.95rem; line-height: 1.6; margin-bottom: 0;">
                    {{ Str::limit($post->content, 200) }}
                </p>

                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #f1f5f9; display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 24px; height: 24px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: bold; color: var(--text-secondary);">
                        {{ mb_substr($post->creator->name, 0, 1) }}
                    </div>
                    <span style="font-size: 0.85rem; color: var(--text-secondary);">بواسطة: {{ $post->creator->name }}</span>
                </div>
            </div>
            @empty
            <div class="card" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                لا توجد إعلانات حالياً.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Left Column: Upcoming Reminders -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">

        <!-- Reminders List -->
        <div class="card">
            <h3 style="margin-bottom: 1rem; font-size: 1.1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; font-weight: 700; color: var(--text-primary);">
                ⏰ تذكيرات قادمة
            </h3>

            <div style="display: flex; flex-direction: column; gap: 1rem;">
                @forelse($reminders as $reminder)
                <div style="display: flex; gap: 1rem; align-items: flex-start; padding-bottom: 1rem; border-bottom: 1px solid #f8fafc;">
                    <div style="background-color: #f1f5f9; border-radius: 12px; padding: 0.5rem; min-width: 50px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <span style="font-size: 0.7rem; color: var(--primary-color); font-weight: 700; text-transform: uppercase; display: block;">{{ $reminder->event_date->format('M') }}</span>
                        <span style="font-size: 1.2rem; font-weight: 800; color: var(--text-primary); line-height: 1;">{{ $reminder->event_date->format('d') }}</span>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 0.95rem; color: var(--text-primary); margin-bottom: 0.25rem;">{{ $reminder->title }}</div>
                        <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.25rem;">{{ $reminder->description }}</div>
                        <div style="font-size: 0.8rem; color: var(--danger-color); font-weight: 600;">
                            {{ $reminder->event_date->format('h:i A') }}
                        </div>
                    </div>
                </div>
                @empty
                <div style="text-align: center; color: var(--text-secondary); padding: 1rem;">
                    لا توجد تذكيرات قادمة.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Quick Access Card -->
        <div class="card">
            <h3 style="margin-bottom: 1rem; font-size: 1.1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; font-weight: 700; color: var(--text-primary);">
                ⚡ وصول سريع
            </h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <a href="{{ route('student.subjects.index') }}" style="background: #f8fafc; padding: 1rem; border-radius: 12px; text-align: center; text-decoration: none; transition: all 0.2s;">
                    <div style="font-size: 1.5rem; margin-bottom: 0.5rem; display: flex; justify-content: center;">
                        <div style="width: 40px; height: 40px; background: #e0e7ff; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #4338ca;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div style="font-weight: 600; color: var(--text-primary); font-size: 0.9rem;">المقررات</div>
                </a>
                <a href="{{ route('student.schedule.index') }}" style="background: #f8fafc; padding: 1rem; border-radius: 12px; text-align: center; text-decoration: none; transition: all 0.2s;">
                    <div style="font-size: 1.5rem; margin-bottom: 0.5rem; display: flex; justify-content: center;">
                        <div style="width: 40px; height: 40px; background: #dcfce7; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #15803d;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="20" x2="18" y2="10"></line>
                                <line x1="12" y1="20" x2="12" y2="4"></line>
                                <line x1="6" y1="20" x2="6" y2="14"></line>
                            </svg>
                        </div>
                    </div>
                    <div style="font-weight: 600; color: var(--text-primary); font-size: 0.9rem;">الجدول</div>
                </a>
            </div>
        </div>

    </div>

</div>

<!-- Responsive Grid Adjustment for Mobile -->
<style>
    @media (max-width: 900px) {
        div[style*="grid-template-columns: 2fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>

@endsection