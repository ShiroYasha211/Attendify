@extends(Auth::user()->role === \App\Enums\UserRole::DELEGATE ? 'layouts.delegate' : 'layouts.student')

@section('title', 'مركز مهام الرصد المفوضة')

@section('content')
@push('styles')
<style>
    .premium-header {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        border-radius: 24px;
        padding: 2.5rem;
        color: white;
        position: relative;
        overflow: hidden;
        margin-bottom: 2rem;
        box-shadow: 0 20px 40px -15px rgba(59, 130, 246, 0.35);
    }

    .premium-header::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        filter: blur(60px);
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 24px;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        transition: all 0.3s ease;
    }

    .task-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px -5px rgba(59, 130, 246, 0.2);
        border-color: #3b82f6;
    }

    .score-badge {
        background: rgba(59, 130, 246, 0.1);
        color: #1d4ed8;
        padding: 0.5rem 1rem;
        border-radius: 12px;
        font-weight: 800;
        font-size: 0.9rem;
    }

    .btn-premium {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        border: none;
        padding: 0.8rem 2rem;
        border-radius: 16px;
        font-weight: 800;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-premium:hover {
        transform: scale(1.02);
        box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.4);
        color: white;
    }

    .doctor-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: white;
        border-radius: 14px;
        margin-top: 1.5rem;
    }

    .doctor-avatar {
        width: 35px;
        height: 35px;
        background: #eff6ff;
        color: #3b82f6;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
    }
</style>
@endpush

<div class="premium-header">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 position-relative" style="z-index: 2;">
        <div>
            <span class="badge px-3 py-2 rounded-pill mb-3 fw-700" style="background: rgba(255,255,255,0.2); color: white;">بوابة التفويض الأكاديمي</span>
            <h1 class="fw-900 mb-2" style="font-size: 2.2rem;">مهام رصد الدرجات</h1>
            <p class="text-white text-opacity-80 fw-700 m-0"><i class="fa-solid fa-clipboard-check me-2"></i>لديك {{ $delegations->count() }} مهمة مفوضة بانتظارك</p>
        </div>
        <div>
            <i class="fa-solid fa-user-shield fa-4x text-white opacity-25"></i>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 fw-700 p-3 d-flex align-items-center gap-3">
        <i class="fa-solid fa-circle-check fs-4"></i>
        {{ session('success') }}
    </div>
@endif

<div class="row g-4">
    @forelse($delegations as $category)
    <div class="col-md-6 col-lg-4">
        <div class="glass-card p-4 h-100 task-card d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div class="score-badge">
                    <i class="fa-solid fa-star me-1"></i> {{ $category->max_score }} درجة
                </div>
                <div class="bg-primary bg-opacity-10 p-2 rounded-3">
                    <i class="fa-solid fa-file-invoice text-primary"></i>
                </div>
            </div>
            
            <h4 class="fw-900 text-dark mb-1">{{ $category->name }}</h4>
            <p class="text-secondary fw-700 small mb-2"><i class="fa-solid fa-book-open me-1"></i>{{ $category->subject->name }}</p>
            
            <div class="doctor-info mb-4">
                <div class="doctor-avatar">
                    {{ mb_substr($category->doctor->name, 0, 1) }}
                </div>
                <div>
                    <div class="small text-secondary fw-600">الدكتور المشرف</div>
                    <div class="fw-800 text-dark">د/ {{ $category->doctor->name }}</div>
                </div>
            </div>

            <div class="mt-auto">
                <a href="{{ route(Auth::user()->role === \App\Enums\UserRole::DELEGATE ? 'delegate.authorized-grades.show' : 'student.authorized-grades.show', $category->id) }}" 
                    class="btn btn-premium w-100">
                    رصد الدرجات الآن <i class="fa-solid fa-arrow-left ms-2"></i>
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5">
        <div class="glass-card p-5 border-dashed">
            <i class="fa-solid fa-tasks fa-4x text-light-emphasis opacity-25 mb-4"></i>
            <h4 class="fw-900 text-secondary">لا توجد مهام مفوضة</h4>
            <p class="text-secondary opacity-75 fw-700">عندما يقوم الدكتور بتفويضك لرصد درجات معينة ستظهر هنا.</p>
        </div>
    </div>
    @endforelse
</div>
@endsection
