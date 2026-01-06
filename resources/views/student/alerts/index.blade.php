@extends('layouts.student')

@section('title', 'التنبيهات والإنذارات')

@section('content')

<!-- Header -->
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem;">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-danger">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        التنبيهات والإنذارات
    </h1>
    <p style="color: var(--text-secondary);">سجل التنبيهات والإنذارات المرسلة من القسم الأكاديمي</p>
</div>

<!-- Alerts List -->
@if($alerts->count() > 0)
<div style="display: flex; flex-direction: column; gap: 1rem;">
    @foreach($alerts as $alert)
    <div class="card {{ $alert->read_at ? '' : 'border-danger' }}" style="padding: 1.5rem; position: relative; background: {{ $alert->read_at ? '#ffffff' : '#fef2f2' }}; border: 1px solid {{ $alert->read_at ? 'var(--border-color)' : '#fca5a5' }};">

        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <!-- Icon based on type? For now generic warning icon -->
                <div style="width: 32px; height: 32px; background: {{ $alert->read_at ? '#f3f4f6' : '#fee2e2' }}; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: {{ $alert->read_at ? 'var(--text-secondary)' : '#dc2626' }};">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <div>
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin: 0;">{{ $alert->data['title'] ?? 'تنبيه جديد' }}</h3>
                    <span style="font-size: 0.85rem; color: var(--text-secondary);">{{ $alert->created_at->diffForHumans() }}</span>
                </div>
            </div>

            @if(!$alert->read_at)
            <form action="{{ route('student.alerts.read', $alert->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-light text-secondary" style="font-size: 0.8rem;">تحديد كمقروء</button>
            </form>
            @endif
        </div>

        <p style="color: var(--text-secondary); margin-bottom: 0; margin-right: 3.5rem;">
            {{ $alert->data['message'] ?? 'لا توجد تفاصيل.' }}
        </p>

    </div>
    @endforeach

    <!-- Pagination -->
    <div class="mt-4">
        {{ $alerts->links() }}
    </div>
</div>
@else
<div class="card" style="text-align: center; padding: 3rem;">
    <div style="width: 60px; height: 60px; background: #f3f4f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: var(--text-secondary);">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 12h-6l-2 3h-4l-2-3H2"></path>
            <path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path>
        </svg>
    </div>
    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">لا توجد تنبيهات</h3>
    <p style="color: var(--text-secondary);">سجلك نظيف! لا توجد إنذارات أو تنبيهات حالياً.</p>
</div>
@endif

@endsection