@extends('layouts.student')

@section('title', 'المقررات الدراسية')

@section('content')

<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
            المقررات الدراسية
        </h1>
        <p style="color: var(--text-secondary);">عرض جميع المواد المسجلة في هذا الفصل</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
    @forelse($subjects as $subject)
    <div class="card" style="transition: transform 0.2s, box-shadow 0.2s; display: flex; flex-direction: column; height: 100%;">
        <!-- Decor Header -->
        <div style="height: 6px; background: linear-gradient(to right, var(--primary-color), var(--info-color));"></div>

        <div style="padding: 1.5rem; flex: 1; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--text-primary); margin: 0;">{{ $subject->name }}</h3>
                <span class="badge badge-secondary" style="font-family: monospace;">{{ $subject->code }}</span>
            </div>

            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem;">
                <div style="width: 32px; height: 32px; background: #eef2ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary-color); font-weight: 700;">
                    {{ $subject->doctor ? mb_substr($subject->doctor->name, 0, 1) : '?' }}
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: var(--text-secondary);">مدرس المادة</div>
                    <div style="font-weight: 600; font-size: 0.95rem;">{{ $subject->doctor->name ?? 'غير محدد' }}</div>
                </div>
            </div>

            <div style="margin-top: auto; padding-top: 1rem; border-top: 1px solid #f1f5f9; display: flex; gap: 0.5rem;">
                <a href="{{ route('student.subjects.show', $subject->id) }}" class="btn btn-primary" style="flex: 1; justify-content: center;">
                    التفاصيل والواجبات
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 4rem 2rem;">
        <div style="color: var(--text-secondary); margin-bottom: 1rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
        </div>
        <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.5rem;">لا توجد مقررات</h3>
        <p style="color: var(--text-secondary);">لم يتم تسجيل أي مواد دراسية لك في هذا الفصل حتى الآن.</p>
    </div>
    @endforelse
</div>

@endsection