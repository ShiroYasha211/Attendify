@extends('layouts.doctor')

@section('title', 'لوحة التحكم')

@section('content')
<div x-data="{}">

    <!-- Welcome Section -->
    <div class="mb-5">
        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem;">مرحباً، د. {{ $doctor->name }} 👋</h1>
        <p style="color: var(--text-secondary);">إليك نظرة عامة على المقررات والطلاب لهذا الفصل الدراسي.</p>
    </div>

    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">

        <!-- Subjects Count -->
        <div class="card" style="display: flex; align-items: center; gap: 1.5rem; padding: 1.5rem;">
            <div style="width: 56px; height: 56px; border-radius: 12px; background-color: #e0e7ff; color: #4f46e5; display: flex; align-items: center; justify-content: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
            </div>
            <div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--text-primary); line-height: 1;">{{ $subjects->count() }}</div>
                <div style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.25rem;">المقررات الدراسية</div>
            </div>
        </div>

        <!-- Students Count -->
        <div class="card" style="display: flex; align-items: center; gap: 1.5rem; padding: 1.5rem;">
            <div style="width: 56px; height: 56px; border-radius: 12px; background-color: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--text-primary); line-height: 1;">{{ $studentsCount }}</div>
                <div style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.25rem;">إجمالي الطلاب</div>
            </div>
        </div>

    </div>

    <!-- Subjects Section -->
    <div class="card">
        <div style="border-bottom: 1px solid var(--border-color); padding: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin: 0;">المقررات المسندة إليك</h3>
        </div>

        <div style="padding: 1.5rem;">
            @if($subjects->count() > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
                @foreach($subjects as $subject)
                <div class="card" style="border: 1px solid var(--border-color); box-shadow: none; transition: all 0.2s ease; position: relative; overflow: hidden;" onmouseover="this.style.borderColor='var(--primary-color)'; this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--shadow-md)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">

                    <!-- Decorative Top Border -->
                    <div style="height: 4px; background: var(--primary-color); width: 100%;"></div>

                    <div style="padding: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                            <div style="background-color: #f3f4f6; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary);">
                                {{ $subject->code ?? 'CODE' }}
                            </div>
                            <div style="font-size: 0.8rem; color: var(--primary-color); font-weight: 600;">
                                {{ $subject->term->name ?? 'الترم العام' }}
                            </div>
                        </div>

                        <h4 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary);">
                            {{ $subject->name }}
                        </h4>

                        <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 10v6M2 10v6M12 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"></path>
                                <path d="M6 15v-2a6 6 0 1 1 12 0v2"></path>
                                <path d="M2 10s2 6 10 6 10-6 10-6"></path>
                            </svg>
                            {{ $subject->major->name ?? 'غير محدد' }} &bull; {{ $subject->level->name ?? '' }}
                        </p>

                        <a href="{{ route('doctor.subject.report', $subject) }}" class="btn btn-outline-primary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.6rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            عرض كشف الطلاب
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div style="text-align: center; padding: 4rem 1rem;">
                <div style="margin-bottom: 1rem; color: #d1d5db;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="9" y1="15" x2="15" y2="15"></line>
                    </svg>
                </div>
                <h4 style="font-size: 1.1rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">لا توجد مقررات مسندة</h4>
                <p style="color: #9ca3af; font-size: 0.95rem;">لم يتم إسناد أي مواد دراسية إليك في هذا الفصل الدراسي حتى الآن.</p>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection