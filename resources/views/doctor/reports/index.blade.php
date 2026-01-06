@extends('layouts.doctor')

@section('title', 'التقارير الدراسية')

@section('content')
<div class="container-fluid p-0">
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="fw-bold text-gray-800 mb-2">التقارير الدراسية</h2>
            <p class="text-muted">قائمة بالمقررات الدراسية - اختر مقرراً لعرض التفاصيل.</p>
        </div>
    </div>

    <div class="row g-3">
        @forelse($subjects as $subject)
        <div class="col-12">
            <div class="card border-0 shadow-sm hover-lift transition-all" style="border-radius: 12px; overflow: hidden; background: #fff;">
                <div class="card-body p-4">
                    <div class="row align-items-center">

                        <!-- Icon & Basic Info -->
                        <div class="col-md-6 d-flex align-items-center gap-4">
                            <div class="rounded-3 p-3 flex-shrink-0" style="background-color: rgba(var(--primary-rgb), 0.1); color: var(--primary-color);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h5 class="fw-bold fs-5 mb-1 text-dark">{{ $subject->name }}</h5>
                                <div class="d-flex align-items-center gap-2 text-muted small">
                                    <span class="font-monospace">{{ $subject->code }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $subject->major->name ?? 'عام' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Meta Info (Level / Term) -->
                        <div class="col-md-3 my-3 my-md-0">
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-light text-secondary border fw-normal px-3 py-2 rounded-pill">
                                    {{ $subject->level->name ?? '-' }}
                                </span>
                                <span class="badge bg-light text-secondary border fw-normal px-3 py-2 rounded-pill">
                                    {{ $subject->term->name ?? '-' }}
                                </span>
                            </div>
                        </div>

                        <!-- Action -->
                        <div class="col-md-3 text-md-end">
                            <a href="{{ route('doctor.reports.show', $subject->id) }}" class="btn btn-primary px-4 py-2 fw-bold" style="border-radius: 8px; min-width: 140px;">
                                عرض التقرير
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ms-2">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-5">
                <div class="mb-3 text-muted">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <h5 class="text-muted">لا توجد مقررات دراسية مسندة إليك.</h5>
            </div>
        </div>
        @endforelse
    </div>
</div>

<style>
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05) !important;
    }
</style>
@endsection