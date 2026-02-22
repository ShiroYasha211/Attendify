@extends('layouts.doctor')

@section('title', 'توزيع الحالات السريرية')

@section('content')
<style>
    .dashboard-header {
        margin-bottom: 2rem;
    }

    .welcome-text h1 {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .welcome-text p {
        color: var(--text-secondary);
        font-size: 0.95rem;
    }

    .assignments-layout {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 1.5rem;
        align-items: start;
    }

    @media (max-width: 992px) {
        .assignments-layout {
            grid-template-columns: 1fr;
        }
    }

    .card-section {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-label {
        display: block;
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .form-control,
    .form-select {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        background: #f8fafc;
        transition: all 0.2s;
        font-family: inherit;
    }

    .form-control:focus,
    .form-select:focus {
        outline: none;
        border-color: var(--primary-color);
        background: white;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }

    .btn-submit {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 0.8rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
    }

    .btn-submit:hover {
        background: #4338ca;
        transform: translateY(-1px);
    }

    .table-modern {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-modern th {
        background: #f8fafc;
        font-weight: 600;
        color: var(--text-secondary);
        padding: 1rem;
        text-align: right;
        font-size: 0.85rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .table-modern td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.95rem;
        color: var(--text-primary);
    }

    .table-modern tr:hover td {
        background: #f8fafc;
    }

    .table-modern tr:last-child td {
        border-bottom: none;
    }

    .badge-status {
        padding: 0.3rem 0.6rem;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.8rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .badge-status.completed {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-status.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .student-name {
        font-weight: 700;
        color: var(--primary-color);
    }

    .task-type-badge {
        background: #e0e7ff;
        color: #4338ca;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
    }
</style>

<div class="dashboard-header">
    <div class="welcome-text">
        <h1>توزيع الحالات السريرية 📋</h1>
        <p>تكليف الطلاب بمهام سريرية على الحالات المرضية المتاحة</p>
    </div>
</div>

@if(session('success'))
<div style="background: #d1fae5; color: #065f46; padding: 1rem 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid #10b981; font-weight: 600;">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div style="background: #fee2e2; color: #991b1b; padding: 1rem 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid #ef4444; font-weight: 600;">
    {{ session('error') }}
</div>
@endif

<div class="assignments-layout">
    <!-- نموذج التوزيع السريع -->
    <div class="card-section">
        <div class="section-header" style="margin-bottom: 1rem;">
            <h3 class="section-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary-color);">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                    <line x1="12" y1="11" x2="16" y2="11"></line>
                    <line x1="12" y1="16" x2="16" y2="16"></line>
                    <line x1="8" y1="11" x2="8.01" y2="11"></line>
                    <line x1="8" y1="16" x2="8.01" y2="16"></line>
                </svg>
                تكليف جديد
            </h3>
        </div>
        <form action="{{ route('doctor.clinical.assignments.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">الطالب المستهدف <span style="color:red">*</span></label>
                <select name="student_id" class="form-select select2" required>
                    <option value="">-- ابحث عن الطالب --</option>
                    @foreach($students as $s)
                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->student_number ?? 'بدون رقم' }})</option>
                    @endforeach
                </select>
                <small style="color: var(--text-secondary); font-size: 0.8rem; margin-top: 0.25rem; display: block;">جميع الطلاب يظهرون للبحث.</small>
            </div>

            <div class="form-group">
                <label class="form-label">الحالة السريرية (المريض) <span style="color:red">*</span></label>
                <select name="clinical_case_id" class="form-select select2" required>
                    <option value="">-- اختر الحالة --</option>
                    @foreach($cases as $c)
                    <option value="{{ $c->id }}">{{ $c->patient_name }} ({{ $c->trainingCenter->name ?? '' }} - {{ $c->bodySystem->name ?? '' }})</option>
                    @endforeach
                </select>
                <small style="color: var(--text-secondary); font-size: 0.8rem; margin-top: 0.25rem; display: block;">تظهر فقط حالاتك "النشطة".</small>
            </div>

            <div class="form-group">
                <label class="form-label">نوع المهمة <span style="color:red">*</span></label>
                <select name="task_type" class="form-select" required>
                    <option value="history_taking">أخذ قصة مرضية (History Taking)</option>
                    <option value="clinical_examination">فحص سريري (Clinical Examination)</option>
                    <option value="follow_up">متابعة ومرور (Follow Up / Round)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">تعليمات إضافية (اختياري)</label>
                <textarea name="instructions" class="form-control" placeholder="مثال: ركز على الصمامات في الفحص..."></textarea>
            </div>

            <button type="submit" class="btn-submit">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
                إرسال التكليف للطالب
            </button>
        </form>
    </div>

    <!-- قائمة التوزيعات السابقة -->
    <div class="card-section">
        <div class="section-header" style="margin-bottom: 1rem;">
            <h3 class="section-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary-color);">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                سجل التكليفات السابقة
            </h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="25%">الطالب</th>
                        <th width="25%">الحالة</th>
                        <th width="20%">المهمة</th>
                        <th width="25%">الإنجاز</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="student-name">{{ $assignment->student->name ?? '-' }}</td>
                        <td>
                            <div style="font-weight: 600;">{{ $assignment->clinicalCase->patient_name ?? '-' }}</div>
                        </td>
                        <td>
                            <span class="task-type-badge">
                                @if($assignment->task_type == 'history_taking') قصة مرضية
                                @elseif($assignment->task_type == 'clinical_examination') فحص سريري
                                @else متابعة (Round) @endif
                            </span>
                        </td>
                        <td>
                            @if($assignment->is_completed)
                            <span class="badge-status completed">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                أُنجزت ({{ $assignment->completed_at->format('Y-m-d') }})
                            </span>
                            @else
                            <span class="badge-status pending">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                قيد الإنجاز
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 3rem 1rem;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="color: #cbd5e1; margin-bottom: 1rem;">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="9" y1="3" x2="9" y2="21"></line>
                            </svg>
                            <p>لم تقم بتوزيع أي مهام بعد.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        @if($assignments->hasPages())
        <div style="margin-top: 1.5rem;">
            {{ $assignments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            dir: "rtl"
        });
    });
</script>
@endpush