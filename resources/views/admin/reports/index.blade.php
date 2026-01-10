@extends('layouts.admin')

@section('title', 'مركز التقارير والإحصائيات')

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
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 4px 12px -2px rgba(245, 158, 11, 0.4);
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

    /* Overview Stats */
    .overview-stats {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .overview-stat {
        background: white;
        border-radius: 16px;
        padding: 1.25rem;
        border: 1px solid var(--border-color);
        text-align: center;
    }

    .overview-stat .value {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .overview-stat .label {
        color: var(--text-secondary);
        font-size: 0.85rem;
    }

    .overview-stat.primary .value {
        color: #4f46e5;
    }

    .overview-stat.cyan .value {
        color: #06b6d4;
    }

    .overview-stat.green .value {
        color: #10b981;
    }

    .overview-stat.purple .value {
        color: #8b5cf6;
    }

    .overview-stat.amber .value {
        color: #f59e0b;
    }

    /* Report Cards */
    .reports-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .report-card {
        background: white;
        border-radius: 20px;
        border: 1px solid var(--border-color);
        padding: 1.75rem;
        position: relative;
        overflow: hidden;
        transition: all 0.3s;
    }

    .report-card:hover {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .report-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
        height: 4px;
    }

    .report-card.blue::before {
        background: linear-gradient(90deg, #3b82f6, #60a5fa);
    }

    .report-card.red::before {
        background: linear-gradient(90deg, #ef4444, #f87171);
    }

    .report-card.green::before {
        background: linear-gradient(90deg, #10b981, #34d399);
    }

    .report-card.purple::before {
        background: linear-gradient(90deg, #8b5cf6, #a78bfa);
    }

    .report-card.amber::before {
        background: linear-gradient(90deg, #f59e0b, #fbbf24);
    }

    .report-card.cyan::before {
        background: linear-gradient(90deg, #06b6d4, #22d3ee);
    }

    .report-card-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .report-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .report-card.blue .report-icon {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .report-card.red .report-icon {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .report-card.green .report-icon {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .report-card.purple .report-icon {
        background: rgba(139, 92, 246, 0.1);
        color: #8b5cf6;
    }

    .report-card.amber .report-icon {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .report-card.cyan .report-icon {
        background: rgba(6, 182, 212, 0.1);
        color: #06b6d4;
    }

    .report-card h3 {
        font-size: 1.15rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .report-card p {
        color: var(--text-secondary);
        font-size: 0.875rem;
        line-height: 1.6;
        margin-bottom: 1.25rem;
    }

    .report-card .form-group {
        margin-bottom: 1rem;
    }

    .report-card .form-label {
        font-size: 0.85rem;
        margin-bottom: 0.35rem;
    }

    .btn-report {
        width: 100%;
        padding: 0.75rem 1rem;
        border: none;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.3s;
    }

    .btn-report.blue {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
    }

    .btn-report.blue:hover {
        box-shadow: 0 4px 12px -2px rgba(59, 130, 246, 0.4);
    }

    .btn-report.red {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
    }

    .btn-report.red:hover {
        box-shadow: 0 4px 12px -2px rgba(239, 68, 68, 0.4);
    }

    .btn-report.green {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
    }

    .btn-report.green:hover {
        box-shadow: 0 4px 12px -2px rgba(16, 185, 129, 0.4);
    }

    .btn-report.purple {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        color: white;
    }

    .btn-report.purple:hover {
        box-shadow: 0 4px 12px -2px rgba(139, 92, 246, 0.4);
    }

    .btn-report.amber {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
    }

    .btn-report.amber:hover {
        box-shadow: 0 4px 12px -2px rgba(245, 158, 11, 0.4);
    }

    .btn-report.cyan {
        background: linear-gradient(135deg, #06b6d4, #0891b2);
        color: white;
    }

    .btn-report.cyan:hover {
        box-shadow: 0 4px 12px -2px rgba(6, 182, 212, 0.4);
    }

    /* Quick Actions */
    .quick-actions {
        background: white;
        border-radius: 20px;
        border: 1px solid var(--border-color);
        padding: 1.5rem;
    }

    .quick-actions h4 {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .quick-links {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
    }

    .quick-link {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem;
        background: #f8fafc;
        border-radius: 12px;
        text-decoration: none;
        color: var(--text-primary);
        transition: all 0.2s;
    }

    .quick-link:hover {
        background: #f1f5f9;
        transform: translateY(-2px);
    }

    .quick-link .icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .quick-link span {
        font-size: 0.85rem;
        font-weight: 600;
    }

    .input-with-icon {
        position: relative;
    }

    .input-with-icon .icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-light);
    }

    .input-with-icon select {
        padding-right: 2.75rem;
    }
</style>

@php
$totalStudents = \App\Models\User::where('role', 'student')->count();
$totalDoctors = \App\Models\User::where('role', 'doctor')->count();
$totalSubjects = \App\Models\Academic\Subject::count();
$totalAttendance = \App\Models\Attendance::count();
$deprivedCount = 0;

// Calculate deprived students count
$subjectsWithMax = \App\Models\Academic\Subject::all();
foreach ($subjectsWithMax as $subj) {
$studentsInSubject = \App\Models\User::where('role', 'student')
->where('level_id', $subj->term->level_id ?? 0)
->get();
foreach ($studentsInSubject as $stu) {
$absentCount = \App\Models\Attendance::where('student_id', $stu->id)
->where('subject_id', $subj->id)
->where('status', 'absent')
->count();
if ($absentCount >= $subj->max_absences) {
$deprivedCount++;
}
}
}
@endphp

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="20" x2="18" y2="10"></line>
            <line x1="12" y1="20" x2="12" y2="4"></line>
            <line x1="6" y1="20" x2="6" y2="14"></line>
        </svg>
    </div>
    <div class="page-header-text">
        <h1>مركز التقارير والإحصائيات</h1>
        <p>استخراج التقارير والكشوفات الرسمية ومراقبة أداء النظام</p>
    </div>
</div>

<!-- Overview Stats -->
<div class="overview-stats">
    <div class="overview-stat primary">
        <div class="value">{{ $totalStudents }}</div>
        <div class="label">إجمالي الطلاب</div>
    </div>
    <div class="overview-stat cyan">
        <div class="value">{{ $totalDoctors }}</div>
        <div class="label">أعضاء هيئة التدريس</div>
    </div>
    <div class="overview-stat green">
        <div class="value">{{ $totalSubjects }}</div>
        <div class="label">المواد الدراسية</div>
    </div>
    <div class="overview-stat purple">
        <div class="value">{{ number_format($totalAttendance) }}</div>
        <div class="label">سجلات الحضور</div>
    </div>
    <div class="overview-stat amber">
        <div class="value">{{ $deprivedCount }}</div>
        <div class="label">حالات حرمان</div>
    </div>
</div>

<!-- Reports Grid -->
<div class="reports-grid">

    <!-- Subject Attendance Report -->
    <div class="report-card blue">
        <div class="report-card-header">
            <div class="report-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
            </div>
            <h3>كشف حضور مادة</h3>
        </div>
        <p>تقرير تفصيلي لحضور وغياب جميع طلاب مادة معينة مع حساب النسب والحالات.</p>

        <form action="{{ route('admin.reports.subject') }}" method="GET">
            <div class="form-group">
                <label class="form-label">اختر المادة</label>
                <div class="input-with-icon">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                        </svg>
                    </span>
                    <select name="subject_id" class="form-control" required>
                        <option value="">-- اختر المادة --</option>
                        @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }} ({{ $subject->level->name ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-report blue">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                استخراج الكشف
            </button>
        </form>
    </div>

    <!-- Threshold Report -->
    <div class="report-card red">
        <div class="report-card-header">
            <div class="report-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <h3>تقرير الحرمان</h3>
        </div>
        <p>فحص الطلاب المتجاوزين لنسبة غياب معينة واستخراج قائمة المحرومين.</p>

        <form action="{{ route('admin.reports.threshold') }}" method="GET">
            <div class="form-group">
                <label class="form-label">الدفعة الدراسية</label>
                <select name="level_id" class="form-control" required>
                    <option value="">اختر الدفعة...</option>
                    @foreach($universities as $university)
                    <optgroup label="{{ $university->name }}">
                        @foreach($university->colleges as $college)
                        @foreach($college->majors as $major)
                        @foreach($major->levels as $level)
                        <option value="{{ $level->id }}">{{ $level->name }} - {{ $major->name }}</option>
                        @endforeach
                        @endforeach
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">نسبة الغياب القصوى</label>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <input type="number" name="threshold" class="form-control" value="25" min="1" max="100" style="width: 80px; text-align: center;">
                    <span style="font-weight: 600;">%</span>
                </div>
            </div>
            <button type="submit" class="btn-report red">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                فحص الحالات
            </button>
        </form>
    </div>

    <!-- Level Summary Report -->
    <div class="report-card green">
        <div class="report-card-header">
            <div class="report-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
            </div>
            <h3>ملخص الدفعة</h3>
        </div>
        <p>نظرة شاملة على حالة دفعة دراسية: عدد الطلاب، المواد، نسب الحضور.</p>

        <form action="{{ route('admin.reports.level-summary') }}" method="GET">
            <div class="form-group">
                <label class="form-label">اختر الدفعة</label>
                <select name="level_id" class="form-control" required>
                    <option value="">اختر الدفعة...</option>
                    @foreach($universities as $university)
                    <optgroup label="{{ $university->name }}">
                        @foreach($university->colleges as $college)
                        @foreach($college->majors as $major)
                        @foreach($major->levels as $level)
                        <option value="{{ $level->id }}">{{ $level->name }} - {{ $major->name }}</option>
                        @endforeach
                        @endforeach
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-report green">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
                عرض الملخص
            </button>
        </form>
    </div>

    <!-- Doctor Performance Report -->
    <div class="report-card purple">
        <div class="report-card-header">
            <div class="report-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <h3>أداء أعضاء هيئة التدريس</h3>
        </div>
        <p>تقرير شامل عن أداء الدكاترة: عدد المحاضرات، نسب الحضور في موادهم.</p>

        <form action="{{ route('admin.reports.doctor-performance') }}" method="GET">
            <div class="form-group">
                <label class="form-label">اختر الدكتور (أو الكل)</label>
                <select name="doctor_id" class="form-control">
                    <option value="">جميع الدكاترة</option>
                    @foreach(\App\Models\User::where('role', 'doctor')->get() as $doctor)
                    <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-report purple">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                </svg>
                عرض التقرير
            </button>
        </form>
    </div>

    <!-- Assignments Report -->
    <div class="report-card amber">
        <div class="report-card-header">
            <div class="report-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="12" y1="18" x2="12" y2="12"></line>
                    <line x1="9" y1="15" x2="15" y2="15"></line>
                </svg>
            </div>
            <h3>تقرير التكاليف</h3>
        </div>
        <p>إحصائيات التكاليف والواجبات: المنتهية، الجارية، نسب التسليم.</p>

        <a href="{{ route('admin.reports.assignments') }}" class="btn-report amber">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
            عرض التقرير
        </a>
    </div>

    <!-- System Overview -->
    <div class="report-card cyan">
        <div class="report-card-header">
            <div class="report-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <h3>نظرة عامة على النظام</h3>
        </div>
        <p>تقرير شامل يعرض جميع الإحصائيات الرئيسية للنظام في صفحة واحدة.</p>

        <a href="{{ route('admin.reports.system-overview') }}" class="btn-report cyan">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            عرض النظرة العامة
        </a>
    </div>

</div>

<!-- Quick Actions -->
<div class="quick-actions">
    <h4>
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
        </svg>
        روابط سريعة
    </h4>
    <div class="quick-links">
        <a href="{{ route('admin.dashboard') }}" class="quick-link">
            <div class="icon" style="background: rgba(79, 70, 229, 0.1); color: #4f46e5;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
            </div>
            <span>لوحة القيادة</span>
        </a>
        <a href="{{ route('admin.students.index') }}" class="quick-link">
            <div class="icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                </svg>
            </div>
            <span>إدارة الطلاب</span>
        </a>
        <a href="{{ route('admin.doctors.index') }}" class="quick-link">
            <div class="icon" style="background: rgba(6, 182, 212, 0.1); color: #06b6d4;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <span>أعضاء هيئة التدريس</span>
        </a>
        <a href="{{ route('admin.subjects.index') }}" class="quick-link">
            <div class="icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
            </div>
            <span>المواد الدراسية</span>
        </a>
    </div>
</div>

@endsection