@extends('layouts.student')

@section('title', 'التكاليف والواجبات')

@section('content')

<!-- Header -->
<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            التكاليف والواجبات
        </h1>
        <p style="color: var(--text-secondary);">عرض التكاليف المطلوبة والواجبات المنزلية</p>
    </div>
</div>

<div x-data="{ activeTab: 'active' }">

    <!-- Tabs -->
    <div style="display: flex; gap: 1rem; border-bottom: 1px solid #e2e8f0; margin-bottom: 2rem;">
        <button @click="activeTab = 'active'" :class="{ 'active-tab': activeTab === 'active' }" class="tab-btn" style="display: flex; align-items: center; gap: 0.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            التكاليف الحالية
            @if($activeAssignments->count() > 0)
            <span class="badge badge-primary" style="margin-right: 0.5rem;">{{ $activeAssignments->count() }}</span>
            @endif
        </button>
        <button @click="activeTab = 'past'" :class="{ 'active-tab': activeTab === 'past' }" class="tab-btn" style="display: flex; align-items: center; gap: 0.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            التكاليف المنتهية
        </button>
    </div>

    <!-- Active Assignments -->
    <div x-show="activeTab === 'active'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        @if($activeAssignments->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
            @foreach($activeAssignments as $assignment)
            <div class="card" style="display: flex; flex-direction: column; height: 100%;">
                <div style="height: 5px; background: var(--primary-color);"></div>
                <div style="padding: 1.5rem; flex: 1; display: flex; flex-direction: column;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                        <span class="badge badge-info-subtle">{{ $assignment->subject->name }}</span>
                        <span style="font-size: 0.8rem; font-weight: 600; color: var(--danger-color);">
                            متبقي {{ \Carbon\Carbon::parse($assignment->due_date)->diffForHumans(null, true) }}
                        </span>
                    </div>

                    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--text-primary);">{{ $assignment->title }}</h3>
                    <p style="color: var(--text-secondary); font-size: 0.9rem; line-height: 1.5; margin-bottom: 1.5rem; flex: 1;">
                        {{ \Illuminate\Support\Str::limit($assignment->description, 100) }}
                    </p>

                    <div style="border-top: 1px solid #f1f5f9; padding-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 0.85rem; color: var(--text-secondary); display: flex; align-items: center; gap: 0.5rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            {{ \Carbon\Carbon::parse($assignment->due_date)->format('Y-m-d') }}
                        </div>
                        <a href="{{ route('student.subjects.show', ['subject' => $assignment->subject_id]) }}" class="btn btn-sm btn-outline-primary">
                            التفاصيل
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 12px; border: 1px dashed #e2e8f0;">
            <div style="width: 64px; height: 64px; background: #f0fdf4; color: #16a34a; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary);">لا توجد واجبات نشطة</h3>
            <p style="color: var(--text-secondary);">ممتاز! لقد أنجزت جميع واجباتك الحالية.</p>
        </div>
        @endif
    </div>

    <!-- Past Assignments -->
    <div x-show="activeTab === 'past'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
        <div class="card">
            <table class="table table-bordered mb-0" style="vertical-align: middle;">
                <thead class="bg-light">
                    <tr>
                        <th style="padding: 1rem;">العنوان</th>
                        <th style="padding: 1rem;">المادة</th>
                        <th style="padding: 1rem;">تاريخ الاستحقاق</th>
                        <th style="padding: 1rem;">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pastAssignments as $assignment)
                    <tr>
                        <td style="padding: 1rem; font-weight: 600;">{{ $assignment->title }}</td>
                        <td style="padding: 1rem;">{{ $assignment->subject->name }}</td>
                        <td style="padding: 1rem; color: var(--text-secondary);">{{ \Carbon\Carbon::parse($assignment->due_date)->format('Y-m-d') }}</td>
                        <td style="padding: 1rem;">
                            <span class="badge badge-secondary">منتهي</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-secondary);">لا يوجد سجل واجبات سابقة.</td>
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