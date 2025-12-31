@extends('layouts.admin')

@section('title', 'مركز التقارير')

@section('content')

<div class="container" style="max-width: 1200px; margin: 0 auto; padding: 2rem;">

    <header style="margin-bottom: 3rem; text-align: center;">
        <h1 style="font-size: 2rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="20" x2="18" y2="10"></line>
                <line x1="12" y1="20" x2="12" y2="4"></line>
                <line x1="6" y1="20" x2="6" y2="14"></line>
            </svg>
            مركز التقارير والإحصائيات
        </h1>
        <p style="color: var(--text-secondary); font-size: 1rem;">اختر نوع التقرير الذي تود استخراجه</p>
    </header>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem;">

        <!-- Card 1: Subject Attendance Report -->
        <div class="card" style="padding: 2rem; border-top: 5px solid var(--primary-color);">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                <div style="background: #e0f2fe; color: var(--primary-color); padding: 1rem; border-radius: 12px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </div>
                <h3 style="margin: 0; font-size: 1.25rem;">تقرير حضور مادة</h3>
            </div>

            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem; line-height: 1.6;">
                كشف تفصيلي لحضور الطلاب في مادة معينة، مع حساب نسب الغياب والإنذارات.
            </p>

            <form action="{{ route('admin.reports.subject') }}" method="GET">
                <div class="form-group">
                    <label for="subject_id" class="form-label">اختر المادة</label>
                    <select name="subject_id" id="subject_id" class="form-control" required>
                        <option value="">-- اختر من القائمة --</option>
                        @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">
                            {{ $subject->name }} ({{ $subject->level->name ?? '-' }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    استخراج الكشف
                </button>
            </form>
        </div>

        <!-- Card 2: Threshold / Alerts Report -->
        <div class="card" style="padding: 2rem; border-top: 5px solid #ef4444;">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                <div style="background: #fee2e2; color: #ef4444; padding: 1rem; border-radius: 12px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <h3 style="margin: 0; font-size: 1.25rem;">تقرير الحرمان والإنذارات</h3>
            </div>

            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem; line-height: 1.6;">
                ابحث عن الطلاب الذين تجاوزت نسبة غيابهم حداً معيناً في أي مادة من مواد مرحلتهم.
            </p>

            <form action="{{ route('admin.reports.threshold') }}" method="GET">
                <div class="form-group">
                    <label for="level_id" class="form-label">الدفعة الدراسية</label>
                    <select name="level_id" id="level_id" class="form-control" required>
                        <option value="">اختر الدفعة...</option>
                        @foreach($universities as $university)
                        <optgroup label="{{ $university->name }}">
                            @foreach($university->colleges as $college)
                            @foreach($college->majors as $major)
                            @foreach($major->levels as $level)
                            <option value="{{ $level->id }}">
                                {{ $level->name }} - {{ $major->name }}
                            </option>
                            @endforeach
                            @endforeach
                            @endforeach
                        </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="threshold" class="form-label">نسبة الغياب (الحد الأقصى)</label>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <input type="number" name="threshold" id="threshold" class="form-control" value="25" min="1" max="100" style="width: 80px; text-align: center;">
                        <span style="font-weight: bold;">%</span>
                        <small style="color: var(--text-secondary); margin-right: 0.5rem;">سيظهر الطلاب الذين تجاوزوا هذه النسبة.</small>
                    </div>
                </div>

                <button type="submit" class="btn btn-danger" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    فحص الحالات الخطرة
                </button>
            </form>
        </div>

    </div>
</div>

@endsection