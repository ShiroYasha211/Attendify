@extends('layouts.doctor')

@section('title', 'مركز تفويض الطلاب - ' . $subject->name)

@section('content')
@push('styles')
<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.3);
    }

    .premium-header {
        background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
        border-radius: 24px;
        padding: 2.5rem;
        color: white;
        position: relative;
        overflow: hidden;
        margin-bottom: 2rem;
        box-shadow: 0 20px 40px -15px rgba(37, 99, 235, 0.3);
    }

    .premium-header::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -10%;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        filter: blur(60px);
    }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
        border-radius: 24px;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
    }

    .student-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid transparent;
        background: white;
    }

    .assigned-badge {
        background: #f0fdf4;
        color: #16a34a;
        border: 1px solid #bbf7d0;
        padding: 0.4rem 1rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .btn-delegate {
        background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
        color: white;
        border: none;
        padding: 0.6rem 1.5rem;
        border-radius: 12px;
        font-weight: 800;
        transition: all 0.3s ease;
    }

    .btn-delegate:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 15px -3px rgba(37, 99, 235, 0.4);
        color: white;
    }

    .avatar-circle {
        width: 45px;
        height: 45px;
        background: #e0f2fe;
        color: #0369a1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        border-radius: 12px;
    }

    .category-sidebar-item {
        padding: 1rem;
        border-radius: 16px;
        margin-bottom: 0.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        border-right: 4px solid transparent; /* RTL specific focus */
    }

    .category-sidebar-item.active {
        background: white;
        border-color: #e2e8f0;
        border-right-color: #0284c7;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .category-sidebar-item:hover:not(.active) {
        background: rgba(255,255,255,0.4);
    }
</style>
@endpush

<div class="premium-header">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 position-relative" style="z-index: 2;">
        <div>
            <span class="badge px-3 py-2 rounded-pill mb-3 fw-700" style="background: rgba(255,255,255,0.2); color: white;">التحكم في صلاحيات الرصد</span>
            <h1 class="fw-900 mb-2" style="font-size: 2.5rem;">مركز تفويض الطلاب</h1>
            <p class="text-white text-opacity-80 fw-700 m-0"><i class="fa-solid fa-users-gear me-2"></i>{{ $subject->name }} | تفويض المهام الأكاديمية</p>
        </div>
        <div class="d-flex gap-3">
            <a href="{{ route('doctor.grades.categories.index', $subject->id) }}" class="btn px-4 rounded-4 fw-800 border-0" style="background: rgba(255,255,255,0.2); color: white;">توزيع الدرجات <i class="fa-solid fa-layer-group ms-2"></i></a>
            <a href="{{ route('doctor.grades.show', $subject->id) }}" class="btn bg-white px-4 rounded-4 fw-800 border-0 shadow-lg" style="color: #0ea5e9;">العودة للدرجات</a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Active Category Delegation (Now taking the primary space on the right in RTL) -->
    <div class="col-lg-8">
        @forelse($categories as $category)
        <div class="delegation-content glass-card p-4 h-100 {{ $loop->first ? '' : 'd-none' }}" id="cat-{{ $category->id }}">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h4 class="fw-900 text-dark mb-1">المفوضون لـ: {{ $category->name }}</h4>
                    <p class="text-secondary fw-700 small">الطلاب المدرجين أدناه لديهم صلاحية رصد درجات زملائهم في هذا النشاط.</p>
                </div>
                <div class="assigned-badge">بحد أقصى: {{ $category->max_score }} درجة</div>
            </div>

            <!-- Current Delegates -->
            <div class="current-delegates mb-5">
                <h6 class="fw-900 text-secondary text-uppercase small mb-3">الصلاحيات النشطة</h6>
                <div class="row g-3">
                    @forelse($category->permissions as $permission)
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 border bg-white d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-circle">{{ mb_substr($permission->authorizedUser->name, 0, 1) }}</div>
                                <div>
                                    <div class="fw-900 text-dark small">{{ $permission->authorizedUser->name }}</div>
                                    <div class="text-secondary fw-700" style="font-size: 11px;">رقم: {{ $permission->authorizedUser->student_number }}</div>
                                </div>
                            </div>
                            <form action="{{ route('doctor.grades.categories.revoke', $category->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="authorized_user_id" value="{{ $permission->authorized_user_id }}">
                                <button type="submit" class="btn btn-link text-danger p-2 rounded-3 hover-bg-danger"><i class="fa-solid fa-user-minus"></i></button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="p-4 rounded-4 border border-dashed text-center bg-light bg-opacity-50">
                            <i class="fa-solid fa-user-lock fa-2x text-light-emphasis mb-2"></i>
                            <p class="text-secondary fw-700 m-0">لا يوجد مفوضون لهذا التصنيف حالياً.</p>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- New Delegation -->
            <div class="new-delegation pt-4 border-top">
                <h6 class="fw-900 text-secondary text-uppercase small mb-4">إضافة مفوض جديد</h6>
                <form action="{{ route('doctor.grades.categories.delegate', $category->id) }}" method="POST" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-9">
                        <label class="form-label fw-800 text-secondary small">اختر من قائمة طلاب الدفعة</label>
                        <select name="authorized_user_id" class="form-select form-select-lg rounded-4 border-0 shadow-sm" required style="background: white;">
                            <option value="">بحث عن اسم الطالب..</option>
                            @foreach($students as $student)
                                @unless($category->permissions->contains('authorized_user_id', $student->id))
                                <option value="{{ $student->id }}">{{ $student->name }} | {{ $student->student_number ?? 'بدون رقم' }}</option>
                                @endunless
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-delegate w-100 py-3">منح الصلاحية</button>
                    </div>
                </form>
            </div>
        </div>
        @empty
        <div class="glass-card p-5 text-center">
            <i class="fa-solid fa-layer-group fa-4x text-light-emphasis opacity-25 mb-4"></i>
            <h5 class="fw-900 text-secondary">لا توجد تصنيفات للدرجات بعد</h5>
            <p class="text-secondary">يجب عليك تحديد توزيع الدرجات أولاً لتتمكن من تفويض الطلاب.</p>
            <a href="{{ route('doctor.grades.categories.index', $subject->id) }}" class="btn btn-primary px-4 py-2 rounded-4 fw-800 mt-2">انتقل لتوزيع الدرجات</a>
        </div>
        @endforelse
    </div>

    <!-- Categories Sidebar (Now on the left in RTL, providing secondary navigation) -->
    <div class="col-lg-4">
        <div class="glass-card p-4 h-100">
            <h5 class="fw-900 mb-4 d-flex align-items-center gap-2">
                <i class="fa-solid fa-tags text-primary"></i>
                اختر التصنيف للتفويض
            </h5>
            
            <div class="category-menu">
                @foreach($categories as $category)
                <div class="category-sidebar-item {{ $loop->first ? 'active' : '' }}" onclick="switchCategory('cat-{{ $category->id }}', this)">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-900 text-dark mb-0">{{ $category->name }}</div>
                            <small class="text-secondary fw-700">الدرجة: {{ $category->max_score }}</small>
                        </div>
                        <span class="badge rounded-pill bg-light text-dark border fw-800">{{ count($category->permissions) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            
            @if(count($categories) > 0)
            <div class="mt-5 p-3 rounded-4 bg-info bg-opacity-10 border border-info border-opacity-20">
                <h6 class="fw-900 text-info mb-1 small"><i class="fa-solid fa-circle-info me-1"></i> تلميح</h6>
                <p class="text-info-emphasis fw-700 m-0" style="font-size: 11px;">هنا تظهر فقط التصنيفات التي قمت بتعريفها في صفحة توزيع الدرجات.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
    function switchCategory(id, el) {
        // Hide all
        document.querySelectorAll('.delegation-content').forEach(c => c.classList.add('d-none'));
        // Show target
        document.getElementById(id).classList.remove('d-none');
        
        // Update sidebar
        document.querySelectorAll('.category-sidebar-item').forEach(i => i.classList.remove('active'));
        el.classList.add('active');
    }
</script>
@endsection
