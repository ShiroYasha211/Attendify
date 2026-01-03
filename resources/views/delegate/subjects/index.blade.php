@extends('layouts.delegate')

@section('title', 'المواد الدراسية')

@section('content')

<div class="container" style="max-width: 100%;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">المواد الدراسية ورصد الحضور</h1>
        <p style="color: var(--text-secondary);">قائمة المواد المقررة للدفعة. يمكنك من هنا رصد الحضور اليومي لكل مادة.</p>
    </div>

    @if($subjects->isEmpty())
    <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
        <div style="color: var(--text-secondary); margin-bottom: 1rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
        </div>
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">لا توجد مواد دراسية</h3>
        <p style="color: var(--text-secondary);">لم يتم إضافة مواد دراسية لهذا المستوى بعد.</p>
    </div>
    @else
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
        @foreach($subjects as $subject)
        <div class="card" style="display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                <div style="width: 48px; height: 48px; background-color: rgba(67, 56, 202, 0.1); color: var(--primary-color); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                </div>
                <span style="background-color: #f1f5f9; color: var(--text-secondary); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-family: monospace;">
                    {{ $subject->code }}
                </span>
            </div>

            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">{{ $subject->name }}</h3>

            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; font-size: 0.9rem; color: var(--text-secondary);">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>دكتور:</span>
                <span style="font-weight: 600; color: var(--text-primary);">{{ $subject->doctor->name ?? 'غير محدد' }}</span>
            </div>

            <div style="margin-top: auto;">
                <a href="{{ route('delegate.attendance.create', $subject->id) }}" class="btn btn-primary" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; width: 100%;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 11 12 14 22 4"></polyline>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                    </svg>
                    رصد الحضور
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

@endsection