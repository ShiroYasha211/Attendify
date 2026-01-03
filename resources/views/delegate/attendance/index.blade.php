@extends('layouts.delegate')

@section('title', 'سجل الحضور')

@section('content')

<div class="container" style="max-width: 100%;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">سجل الحضور</h1>
            <p style="color: var(--text-secondary);">عرض سجلات الحضور السابقة وإمكانية طباعة التقارير.</p>
        </div>
        <a href="{{ route('delegate.subjects.index') }}" class="btn btn-primary" style="display: flex; align-items: center; gap: 0.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            رصد جديد
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 1.5rem;">
        {{ session('success') }}
    </div>
    @endif

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
            <p style="color: var(--text-secondary);">لم تقم برصد أي حضور حتى الآن.</p>
        </div>
        @else
        <div class="table-container">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0;">
                <thead>
                    <tr style="background-color: #f8fafc; text-align: right;">
                        <th style="padding: 1rem; border-bottom: 1px solid var(--border-color);">المادة الدراسية</th>
                        <th style="padding: 1rem; border-bottom: 1px solid var(--border-color);">التاريخ</th>
                        <th style="padding: 1rem; border-bottom: 1px solid var(--border-color);">عدد الطلاب</th>
                        <th style="padding: 1rem; border-bottom: 1px solid var(--border-color); text-align: center;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $session)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9;">
                            <div style="font-weight: 700; color: var(--text-primary);">{{ $session->subject->name }}</div>
                            <div style="font-size: 0.85rem; color: var(--text-secondary);">{{ $session->subject->code }}</div>
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
                                <a href="{{ route('delegate.attendance.create', $session->subject_id) }}?date={{ $session->date->format('Y-m-d') }}" class="btn btn-secondary btn-sm" title="تعديل السجل">
                                    تعديل
                                </a>
                                <a href="{{ route('delegate.attendance.report', ['subject' => $session->subject_id, 'date' => $session->date->format('Y-m-d')]) }}" class="btn btn-primary btn-sm" target="_blank" title="طباعة التقرير">
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

        @if($sessions->hasPages())
        <div style="border-top: 1px solid var(--border-color); padding: 1rem;">
            {{ $sessions->links() }}
        </div>
        @endif
        @endif
    </div>
</div>

@endsection