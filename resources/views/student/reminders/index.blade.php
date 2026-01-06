@extends('layouts.student')

@section('title', 'التذكيرات والمواعيد')

@section('content')

<!-- Header -->
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem;">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        التذكيرات والمواعيد الهامة
    </h1>
    <p style="color: var(--text-secondary);">استعراض المواعيد الهامة والتنبيهات المجدولة</p>
</div>

<!-- Reminders List -->
@if($reminders->count() > 0)
<div style="display: grid; gap: 1rem;">
    @foreach($reminders as $reminder)
    <div class="card" style="border-right: 4px solid var(--primary-color);">
        <div class="card-body" style="padding: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                    {{ $reminder->title }}
                </h3>
                <span class="badge badge-primary-subtle" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">
                    {{ $reminder->event_date->format('Y-m-d h:i A') }}
                </span>
            </div>

            <p style="color: var(--text-secondary); line-height: 1.6; margin-bottom: 1rem;">
                {{ $reminder->description }}
            </p>

            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-secondary);">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span>متبقي: {{ $reminder->event_date->diffForHumans(null, true) }}</span>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="card" style="text-align: center; padding: 3rem;">
    <div style="width: 60px; height: 60px; background: #f3f4f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: var(--text-secondary);">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
    </div>
    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">لا توجد تذكيرات</h3>
    <p style="color: var(--text-secondary);">لا توجد أي تذكيرات أو مواعيد هامة حالياً.</p>
</div>
@endif

@endsection