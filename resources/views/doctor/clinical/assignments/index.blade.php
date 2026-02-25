@extends('layouts.doctor')

@section('title', 'توزيع الحالات السريرية')

@section('content')
<style>
    .clinical-page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.75rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .clinical-page-header .right-side h1 {
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--text-primary);
        margin: 0 0 0.15rem 0;
    }

    .clinical-page-header .right-side p {
        color: var(--text-secondary);
        font-size: 0.9rem;
        margin: 0;
    }

    .clinical-page-header .left-side {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    .btn-back {
        background: white;
        color: var(--text-secondary);
        border: 1.5px solid #e2e8f0;
        padding: 0.55rem 1.1rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.88rem;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }

    .btn-back:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        color: var(--text-primary);
        text-decoration: none;
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
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .section-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-group {
        margin-bottom: 1.15rem;
    }

    .form-label {
        display: block;
        font-weight: 600;
        font-size: 0.88rem;
        color: var(--text-primary);
        margin-bottom: 0.4rem;
    }

    .form-control,
    .form-select {
        width: 100%;
        padding: 0.65rem 0.85rem;
        font-size: 0.9rem;
        border: 1.5px solid #e2e8f0;
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
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.08);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }

    .btn-submit {
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.25s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.25);
    }

    .btn-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
    }

    /* --- Filter Bar --- */
    .filter-bar {
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        padding: 1.25rem;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        margin-bottom: 1.5rem;
    }

    .filter-bar .filter-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-secondary);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 0.85rem;
        align-items: end;
    }

    .filter-group label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 0.35rem;
    }

    .filter-group select {
        width: 100%;
        padding: 0.55rem 0.75rem;
        font-size: 0.88rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 9px;
        background: white;
        transition: all 0.2s;
        font-family: inherit;
    }

    .filter-group select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.08);
    }

    .filter-actions {
        display: flex;
        gap: 0.5rem;
        align-items: flex-end;
    }

    .btn-filter {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 0.55rem 1.2rem;
        border-radius: 9px;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        white-space: nowrap;
    }

    .btn-filter:hover {
        background: #4338ca;
    }

    .btn-filter-reset {
        background: white;
        color: #64748b;
        border: 1.5px solid #e2e8f0;
        padding: 0.55rem 1rem;
        border-radius: 9px;
        font-weight: 600;
        font-size: 0.85rem;
        text-decoration: none;
        white-space: nowrap;
    }

    .btn-filter-reset:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: var(--text-primary);
        text-decoration: none;
    }

    /* --- Table --- */
    .table-modern {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-modern th {
        background: #f8fafc;
        font-weight: 600;
        color: var(--text-secondary);
        padding: 0.85rem 1rem;
        text-align: right;
        font-size: 0.82rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .table-modern td {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
        color: var(--text-primary);
    }

    .table-modern tr:hover td {
        background: #fafbfe;
    }

    .table-modern tr:last-child td {
        border-bottom: none;
    }

    .student-name {
        font-weight: 700;
        color: var(--primary-color);
    }

    .task-type-badge {
        background: #e0e7ff;
        color: #4338ca;
        padding: 0.2rem 0.5rem;
        border-radius: 5px;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .badge-status {
        padding: 0.25rem 0.55rem;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.78rem;
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

    .alert-banner {
        padding: 0.85rem 1.25rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .alert-banner.success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #6ee7b7;
    }

    .alert-banner.danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }

    .form-hint {
        color: var(--text-secondary);
        font-size: 0.78rem;
        margin-top: 0.25rem;
        display: block;
    }
</style>

{{-- ====== Page Header ====== --}}
<div class="clinical-page-header">
    <div class="right-side">
        <h1>توزيع الحالات السريرية 📋</h1>
        <p>تكليف الطلاب بمهام سريرية على الحالات المرضية المتاحة</p>
    </div>
    <div class="left-side">
        <a href="{{ route('doctor.clinical.index') }}" class="btn-back">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            القسم العملي
        </a>
    </div>
</div>

@if(session('success'))<div class="alert-banner success">✅ {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert-banner danger">⚠️ {{ session('error') }}</div>@endif

<div class="assignments-layout">
    {{-- ====== Assignment Form ====== --}}
    <div class="card-section">
        <div class="section-header" style="margin-bottom: 1rem;">
            <h3 class="section-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary-color);">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
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
                <small class="form-hint">جميع الطلاب يظهرون للبحث.</small>
            </div>

            <div class="form-group">
                <label class="form-label">الحالة السريرية (المريض) <span style="color:red">*</span></label>
                <select name="clinical_case_id" class="form-select select2" required>
                    <option value="">-- اختر الحالة --</option>
                    @foreach($cases as $c)
                    <option value="{{ $c->id }}">{{ $c->patient_name }} ({{ $c->trainingCenter->name ?? '' }})</option>
                    @endforeach
                </select>
                <small class="form-hint">تظهر فقط حالاتك "النشطة".</small>
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
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
                إرسال التكليف للطالب
            </button>
        </form>
    </div>

    {{-- ====== Assignments List ====== --}}
    <div>
        {{-- Filter Bar --}}
        <div class="filter-bar">
            <div class="filter-title">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
                تصفية التكليفات
            </div>
            <form action="{{ route('doctor.clinical.assignments.index') }}" method="GET">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label>الطالب</label>
                        <select name="filter_student_id" class="select2">
                            <option value="">الكل</option>
                            @foreach($students as $s)
                            <option value="{{ $s->id }}" {{ request('filter_student_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>الحالة</label>
                        <select name="filter_case_id" class="select2">
                            <option value="">الكل</option>
                            @foreach($cases as $c)
                            <option value="{{ $c->id }}" {{ request('filter_case_id') == $c->id ? 'selected' : '' }}>{{ $c->patient_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>المهمة</label>
                        <select name="filter_task_type" class="select2">
                            <option value="">الكل</option>
                            <option value="history_taking" {{ request('filter_task_type') == 'history_taking' ? 'selected' : '' }}>قصة مرضية</option>
                            <option value="clinical_examination" {{ request('filter_task_type') == 'clinical_examination' ? 'selected' : '' }}>فحص سريري</option>
                            <option value="follow_up" {{ request('filter_task_type') == 'follow_up' ? 'selected' : '' }}>متابعة (Round)</option>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn-filter">بحث</button>
                        <a href="{{ route('doctor.clinical.assignments.index') }}" class="btn-filter-reset">إلغاء</a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="card-section">
            <div class="section-header" style="margin-bottom: 1rem;">
                <h3 class="section-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary-color);">
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
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    أُنجزت ({{ $assignment->completed_at->format('Y-m-d') }})
                                </span>
                                @else
                                <span class="badge-status pending">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                            <td colspan="5" style="text-align:center; color:var(--text-secondary); padding:3rem 1rem;">
                                <p>لم تقم بتوزيع أي مهام بعد.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($assignments->hasPages())<div style="margin-top:1.5rem;">{{ $assignments->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            dir: "rtl",
            width: '100%'
        });
    });
</script>
@endpush