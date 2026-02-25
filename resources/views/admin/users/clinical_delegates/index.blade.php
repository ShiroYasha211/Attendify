@extends('layouts.admin')

@section('title', 'إدارة مندوبي العملي')

@section('content')

<style>
    .page-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .page-header-icon {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 4px 12px -2px rgba(99, 102, 241, 0.4);
    }

    .page-header-text h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .page-header-text p {
        color: var(--text-secondary);
        font-size: 0.9rem;
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.25rem;
        border: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-icon.indigo {
        background: rgba(99, 102, 241, 0.1);
        color: #6366f1;
    }

    .stat-icon.green {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .stat-icon.amber {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .stat-info h3 {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .stat-info p {
        color: var(--text-secondary);
        font-size: 0.85rem;
    }

    .delegate-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 1.25rem;
    }

    .delegate-card {
        background: white;
        border-radius: 16px;
        border: 1px solid var(--border-color);
        padding: 1.5rem;
        transition: box-shadow 0.2s;
    }

    .delegate-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    .delegate-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .major-info h4 {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .major-info .college-name {
        font-size: 0.82rem;
        color: var(--text-secondary);
        margin-top: 0.2rem;
    }

    .clinical-badge {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: white;
        padding: 0.3rem 0.6rem;
        border-radius: 8px;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .delegate-status {
        margin-top: 0.75rem;
        padding: 1rem;
        border-radius: 10px;
    }

    .delegate-status.assigned {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
    }

    .delegate-status.empty {
        background: #fefce8;
        border: 1px solid #fde68a;
    }

    .delegate-name {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-weight: 600;
        color: #166534;
    }

    .delegate-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #a5f3fc, #67e8f9);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        color: #0e7490;
    }

    .delegate-actions {
        margin-top: 1rem;
        display: flex;
        gap: 0.5rem;
    }

    .btn-assign {
        flex: 1;
        padding: 0.6rem;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
    }

    .btn-assign:hover {
        box-shadow: 0 4px 12px -2px rgba(99, 102, 241, 0.4);
        transform: translateY(-1px);
    }

    .btn-remove {
        padding: 0.6rem 0.875rem;
        background: #fef2f2;
        color: #ef4444;
        border: none;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-remove:hover {
        background: #fee2e2;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-secondary);
    }

    .empty-state svg {
        margin-bottom: 1rem;
        color: #e2e8f0;
    }
</style>

<div x-data="{
    showAssignModal: false,
    selectedMajorId: '',
    selectedMajorName: '',
    searchQuery: '',
    studentsByMajor: @js($studentsByMajor),
    get filteredStudents() {
        const students = this.studentsByMajor[this.selectedMajorId] || [];
        if (!this.searchQuery) return students;
        const q = this.searchQuery.toLowerCase();
        return students.filter(s => s.name.toLowerCase().includes(q) || (s.student_number && s.student_number.toLowerCase().includes(q)));
    },
    openModal(majorId, majorName) {
        this.selectedMajorId = majorId;
        this.selectedMajorName = majorName;
        this.searchQuery = '';
        this.showAssignModal = true;
    }
}">

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="8.5" cy="7" r="4"></circle>
                <polyline points="17 11 19 13 23 9"></polyline>
            </svg>
        </div>
        <div class="page-header-text">
            <h1>إدارة مندوبي العملي</h1>
            <p>تعيين مندوبين لإدارة التدريب العملي (السريري) في التخصصات المؤهلة</p>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 1.5rem;">{{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div style="background: linear-gradient(135deg, #fef2f2, #fff); border: 1px solid #fecaca; border-right: 4px solid #ef4444; color: #991b1b; padding: 1rem 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon indigo">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                </svg>
            </div>
            <div class="stat-info">
                <h3>{{ $clinicalMajors->count() }}</h3>
                <p>تخصصات بتدريب عملي</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <div class="stat-info">
                <h3>{{ $clinicalMajors->filter(fn($m) => $m->clinicalDelegate)->count() }}</h3>
                <p>تخصصات معيّن لها مندوب</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <div class="stat-info">
                <h3>{{ $clinicalMajors->filter(fn($m) => !$m->clinicalDelegate)->count() }}</h3>
                <p>تخصصات بدون مندوب</p>
            </div>
        </div>
    </div>

    @if($clinicalMajors->isEmpty())
    <!-- Empty State -->
    <div class="empty-state" style="background: white; border-radius: 20px; border: 1px solid var(--border-color);">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
            <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
        </svg>
        <h3 style="font-size: 1.2rem; margin-bottom: 0.5rem; color: var(--text-primary);">لا توجد تخصصات بتدريب عملي</h3>
        <p>قم بتفعيل خيار "يحتوي على تدريب عملي" في <a href="{{ route('admin.majors.index') }}" style="color: #6366f1; font-weight: 600;">صفحة التخصصات</a> أولاً</p>
    </div>
    @else

    <!-- Delegates Grid -->
    <div class="delegate-grid">
        @foreach($clinicalMajors as $major)
        <div class="delegate-card">
            <div class="delegate-card-header">
                <div class="major-info">
                    <h4>{{ $major->name }}</h4>
                    <div class="college-name">{{ $major->college->name }} — {{ $major->college->university->name }}</div>
                </div>
                <span class="clinical-badge">🏥 عملي</span>
            </div>

            @if($major->clinicalDelegate)
            {{-- Has Delegate --}}
            <div class="delegate-status assigned">
                <div class="delegate-name">
                    <div class="delegate-avatar">{{ mb_substr($major->clinicalDelegate->student->name, 0, 1) }}</div>
                    <div>
                        <div>{{ $major->clinicalDelegate->student->name }}</div>
                        <div style="font-size: 0.78rem; color: #16a34a; font-weight: 400;">{{ $major->clinicalDelegate->student->student_number ?? '' }}</div>
                    </div>
                </div>
            </div>
            <div class="delegate-actions">
                <button type="button" class="btn-assign"
                    @click="openModal('{{ $major->id }}', '{{ $major->name }}')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    تغيير المندوب
                </button>
                <form action="{{ route('admin.clinical-delegates.destroy', $major->clinicalDelegate) }}" method="POST" onsubmit="return confirm('هل تريد إلغاء تعيين المندوب؟')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-remove">إلغاء</button>
                </form>
            </div>
            @else
            {{-- No Delegate --}}
            <div class="delegate-status empty">
                <div style="display: flex; align-items: center; gap: 0.5rem; color: #92400e; font-weight: 600; font-size: 0.9rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    لم يتم تعيين مندوب بعد
                </div>
            </div>
            <div class="delegate-actions">
                <button type="button" class="btn-assign"
                    @click="openModal('{{ $major->id }}', '{{ $major->name }}')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    تعيين مندوب
                </button>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    <!-- Assign Modal -->
    <div x-show="showAssignModal" class="modal-overlay" style="display: none;" x-transition.opacity.duration.300ms>
        <div class="modal-container" @click.away="showAssignModal = false" style="max-width: 480px;">
            <div class="modal-icon" style="background: linear-gradient(135deg, rgba(99,102,241,0.1), rgba(79,70,229,0.1));">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <polyline points="17 11 19 13 23 9"></polyline>
                </svg>
            </div>

            <h3 class="modal-title">تعيين مندوب عملي</h3>
            <p class="modal-message">اختر طالب من نفس التخصص لتعيينه كمندوب عملي لتخصص <strong x-text="selectedMajorName"></strong></p>

            <form action="{{ route('admin.clinical-delegates.store') }}" method="POST">
                @csrf
                <input type="hidden" name="major_id" :value="selectedMajorId">

                {{-- Search Input --}}
                <div class="form-group" style="margin-top: 1rem;">
                    <label class="form-label">ابحث عن طالب</label>
                    <div style="position: relative;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" x-model="searchQuery" class="form-control" placeholder="اكتب اسم الطالب أو رقم القيد..." style="padding-right: 2.5rem;">
                    </div>
                </div>

                {{-- Student Select --}}
                <div class="form-group">
                    <label class="form-label">اختر الطالب <span style="font-weight: 400; color: var(--text-secondary); font-size: 0.8rem;">(from same major)</span></label>
                    <select name="student_id" class="form-control" required>
                        <option value="">-- اختر طالب --</option>
                        <template x-for="student in filteredStudents" :key="student.id">
                            <option :value="student.id" x-text="student.name + ' (' + (student.student_number || '') + ')'"></option>
                        </template>
                    </select>
                    <template x-if="filteredStudents.length === 0">
                        <small style="color: #ef4444; margin-top: 0.35rem; display: block;">لا يوجد طلاب في هذا التخصص مطابقين للبحث</small>
                    </template>
                </div>

                <div class="modal-actions" style="margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" @click="showAssignModal = false">إلغاء</button>
                    <button type="submit" class="btn btn-primary" :disabled="filteredStudents.length === 0">تعيين المندوب</button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection