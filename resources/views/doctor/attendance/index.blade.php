@extends('layouts.doctor')

@section('title', 'الحضور والغياب')

@section('content')

<div class="container" style="max-width: 100%;" x-data="{ activeTab: new URLSearchParams(window.location.search).get('tab') || 'subjects' }">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">الحضور والغياب</h1>
            <p style="color: var(--text-secondary);">رصد الحضور وعرض التقارير السابقة وتحديد صلاحيات المندوبين.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 1.5rem;">
        {{ session('success') }}
    </div>
    @endif

    <!-- Tabs Navigation -->
    <div style="display: flex; gap: 0; margin-bottom: 0; border-bottom: 2px solid #e2e8f0;">
        <button @click="activeTab = 'subjects'"
            :class="activeTab === 'subjects' ? 'tab-active' : 'tab-inactive'">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 11 12 14 22 4"></polyline>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
            </svg>
            رصد الحضور
        </button>
        <button @click="activeTab = 'reports'"
            :class="activeTab === 'reports' ? 'tab-active' : 'tab-inactive'">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
            </svg>
            التقارير
        </button>
    </div>

    <!-- ══════════════════════════════════════════════════ -->
    <!-- TAB 1: Subjects (رصد الحضور) -->
    <!-- ══════════════════════════════════════════════════ -->
    <div x-show="activeTab === 'subjects'" style="margin-top: 1.5rem;">
        @if($subjects->isEmpty())
        <div class="card" style="text-align: center; padding: 4rem 2rem;">
            <div style="color: var(--text-secondary); margin-bottom: 1rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
            </div>
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">لا توجد مقررات</h3>
            <p style="color: var(--text-secondary);">لم يتم إسناد أي مقررات دراسية لك بعد.</p>
        </div>
        @else
        <div class="card" style="padding: 0; overflow: hidden;">
            <div class="table-container">
                <div class="table-responsive">
<table style="width: 100%; border-collapse: separate; border-spacing: 0;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); text-align: right;">
                            <th style="padding: 1rem 1.25rem; border-bottom: 2px solid #e2e8f0; font-weight: 700; color: #475569; font-size: 0.85rem;">#</th>
                            <th style="padding: 1rem 1.25rem; border-bottom: 2px solid #e2e8f0; font-weight: 700; color: #475569; font-size: 0.85rem;">المقرر الدراسي</th>
                            <th style="padding: 1rem 1.25rem; border-bottom: 2px solid #e2e8f0; font-weight: 700; color: #475569; font-size: 0.85rem;">التخصص / المستوى</th>
                            <th style="padding: 1rem 1.25rem; border-bottom: 2px solid #e2e8f0; font-weight: 700; color: #475569; font-size: 0.85rem;">تحضير المندوب</th>
                            <th style="padding: 1rem 1.25rem; border-bottom: 2px solid #e2e8f0; font-weight: 700; color: #475569; font-size: 0.85rem; text-align: center;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subjects as $index => $subject)
                        <tr style="transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                            <td style="padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; color: #94a3b8; font-weight: 600;">{{ $index + 1 }}</td>
                            <td style="padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9;">
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #10b981, #059669); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--text-primary); font-size: 0.95rem;">{{ $subject->name }}</div>
                                        <div style="font-family: monospace; font-size: 0.8rem; color: #94a3b8; margin-top: 2px;">{{ $subject->code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9;">
                                <span style="font-size: 0.85rem; background: #f1f5f9; padding: 4px 10px; border-radius: 6px; color: #64748b; font-weight: 600;">{{ $subject->major->name ?? '-' }} — {{ $subject->level->name ?? '-' }}</span>
                            </td>
                            <td style="padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9;">
                                <form action="{{ route('doctor.attendance.toggle-delegate', $subject->id) }}" method="POST" class="d-inline m-0 p-0">
                                    @csrf
                                    <button type="submit" class="btn btn-sm" style="padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; border: 1px solid; display: inline-flex; align-items: center; gap: 4px;
                                        {{ $subject->allow_delegate_attendance
                                            ? 'background: #ecfdf5; color: #059669; border-color: #a7f3d0;'
                                            : 'background: #fef2f2; color: #dc2626; border-color: #fecaca;' }}">
                                        @if($subject->allow_delegate_attendance)
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        مفعّل
                                        @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                        موقف
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td style="padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; text-align: center;">
                                <a href="{{ route('doctor.attendance.create', $subject->id) }}"
                                    class="btn btn-primary btn-sm"
                                    style="background: #10b981; border-color: #10b981; display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.45rem 1rem; font-size: 0.85rem; font-weight: 600; border-radius: 8px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="9 11 12 14 22 4"></polyline>
                                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                                    </svg>
                                    بدء التحضير
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
</div>
            </div>
        </div>
        @endif
    </div>

    <!-- ══════════════════════════════════════════════════ -->
    <!-- TAB 2: Reports (التقارير) -->
    <!-- ══════════════════════════════════════════════════ -->
    <div x-show="activeTab === 'reports'" style="margin-top: 1.5rem;">
        <div class="card">
            @if($sessions->isEmpty())
            <div style="text-align: center; padding: 4rem 2rem;">
                <div style="color: var(--text-secondary); margin-bottom: 1rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">لا توجد سجلات حضور</h3>
                <p style="color: var(--text-secondary);">لم يتم رصد أي حضور حتى الآن.</p>
            </div>
            @else
            <div class="table-container">
                <div class="table-responsive">
<table style="width: 100%; border-collapse: separate; border-spacing: 0;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); text-align: right;">
                            <th style="padding: 1rem; border-bottom: 2px solid #e2e8f0; font-weight: 700; color: #475569; font-size: 0.85rem;">المقرر الدراسي</th>
                            <th style="padding: 1rem; border-bottom: 2px solid #e2e8f0; font-weight: 700; color: #475569; font-size: 0.85rem;">عنوان المحاضرة</th>
                            <th style="padding: 1rem; border-bottom: 2px solid #e2e8f0; font-weight: 700; color: #475569; font-size: 0.85rem;">التاريخ</th>
                            <th style="padding: 1rem; border-bottom: 2px solid #e2e8f0; font-weight: 700; color: #475569; font-size: 0.85rem;">عدد الطلاب</th>
                            <th style="padding: 1rem; border-bottom: 2px solid #e2e8f0; font-weight: 700; color: #475569; font-size: 0.85rem; text-align: center;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sessions as $session)
                        <tr style="transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                            <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9;">
                                <div style="font-weight: 700; color: var(--text-primary);">{{ $session->subject->name }}</div>
                                <div style="font-size: 0.85rem; color: var(--text-secondary);">{{ $session->subject->code }}</div>
                            </td>
                            <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9;">
                                @php
                                $lecture = $session->lecture_id
                                ? \App\Models\Academic\Lecture::find($session->lecture_id)
                                : \App\Models\Academic\Lecture::where('subject_id', $session->subject_id)->where('date', $session->date)->first();
                                @endphp
                                <div style="font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                                    @if($lecture && $lecture->lecture_number)
                                    <span style="font-size: 0.85rem; color: var(--text-secondary); background: #f1f5f9; padding: 2px 8px; border-radius: 4px; border: 1px solid #e2e8f0;">
                                        #{{ $lecture->lecture_number }}
                                    </span>
                                    @endif
                                    <span>{{ $lecture ? $lecture->title : '-' }}</span>
                                </div>
                            </td>
                            <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-primary);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-secondary);">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    {{ $session->date->format('Y-m-d') }}
                                </div>
                            </td>
                            <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9;">
                                <span class="badge badge-info">{{ $session->total_records }} طالب</span>
                            </td>
                            <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9; text-align: center;">
                                <div style="display: flex; justify-content: center; gap: 0.5rem;">
                                    <a href="{{ route('doctor.attendance.create', $session->subject_id) }}?date={{ $session->date->format('Y-m-d') }}&lecture_id={{ $session->lecture_id }}" class="btn btn-secondary btn-sm" title="تعديل السجل">
                                        تعديل
                                    </a>
                                    <a href="{{ route('doctor.attendance.report', ['subject' => $session->subject_id, 'date' => $session->date->format('Y-m-d')]) }}" class="btn btn-primary btn-sm" target="_blank" title="طباعة التقرير">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                            <rect x="6" y="14" width="12" height="8"></rect>
                                        </svg>
                                        تقرير
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
</div>
            </div>

            @if($sessions->hasPages())
            <div style="border-top: 1px solid var(--border-color); padding: 1rem; display: flex; justify-content: center; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                {{-- Previous Button --}}
                @if($sessions->onFirstPage())
                <span class="pagination-btn disabled">« السابق</span>
                @else
                <a href="{{ $sessions->previousPageUrl() }}&tab=reports" class="pagination-btn">« السابق</a>
                @endif

                {{-- Page Numbers --}}
                @foreach($sessions->getUrlRange(1, $sessions->lastPage()) as $page => $url)
                    @if($page == $sessions->currentPage())
                    <span class="pagination-btn active">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}&tab=reports" class="pagination-btn">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Next Button --}}
                @if($sessions->hasMorePages())
                <a href="{{ $sessions->nextPageUrl() }}&tab=reports" class="pagination-btn">التالي »</a>
                @else
                <span class="pagination-btn disabled">التالي »</span>
                @endif
            </div>
            @endif
            @endif
        </div>
    </div>
</div>

<style>
    .tab-active {
        padding: 0.75rem 1.5rem;
        font-weight: 700;
        font-size: 0.95rem;
        border: none;
        background: none;
        cursor: pointer;
        color: #10b981;
        border-bottom: 2px solid #10b981;
        margin-bottom: -2px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }

    .tab-inactive {
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        font-size: 0.95rem;
        border: none;
        background: none;
        cursor: pointer;
        color: var(--text-secondary);
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }

    .tab-inactive:hover {
        color: var(--text-primary);
        border-bottom-color: #cbd5e1;
    }

    /* Pagination Buttons */
    .pagination-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 0.75rem;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: white;
        color: #475569;
        text-decoration: none;
        transition: all 0.15s;
        cursor: pointer;
    }

    .pagination-btn:hover:not(.disabled):not(.active) {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #1e293b;
    }

    .pagination-btn.active {
        background: #10b981;
        border-color: #10b981;
        color: white;
    }

    .pagination-btn.disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
</style>

@endsection