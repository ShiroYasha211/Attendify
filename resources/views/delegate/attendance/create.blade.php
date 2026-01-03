@extends('layouts.delegate')

@section('title', 'رصد الحضور')

@section('content')

<div class="container" style="max-width: 100%;">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">رصد الحضور</h1>
            <p style="color: var(--text-secondary);">
                المادة: <span style="font-weight: 700; color: var(--primary-color);">{{ $subject->name }}</span> ({{ $subject->code }})
            </p>
        </div>
        <a href="{{ route('delegate.subjects.index') }}" class="btn btn-secondary" style="display: flex; align-items: center; gap: 0.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            عودة للمواد
        </a>
    </div>

    <form action="{{ route('delegate.attendance.store', $subject->id) }}" method="POST">
        @csrf

        <!-- Date Selection Card -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <label for="date" style="font-weight: 700; color: var(--text-primary); flex-shrink: 0;">تاريخ المحاضرة:</label>
                <input type="date" name="date" id="date" value="{{ date('Y-m-d') }}" required class="form-control" style="max-width: 300px;">
            </div>
        </div>

        <!-- Student List Card -->
        <div class="card">
            <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text-primary); display: flex; justify-content: space-between; align-items: center;">
                <span>قائمة الطلاب</span>
                <span class="badge badge-info">{{ $students->count() }} طالب</span>
            </h3>

            @if($students->isEmpty())
            <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                لا يوجد طلاب مسجلين في هذه الدفعة حالياً.
            </div>
            @else
            <div class="table-container">
                <table style="width: 100%; border-collapse: separate; border-spacing: 0;">
                    <thead>
                        <tr style="background-color: #f8fafc; text-align: right;">
                            <th style="padding: 1rem; border-bottom: 1px solid var(--border-color); width: 60px;">#</th>
                            <th style="padding: 1rem; border-bottom: 1px solid var(--border-color);">الطالب</th>
                            <th style="padding: 1rem; border-bottom: 1px solid var(--border-color); text-align: center;">حاضر</th>
                            <th style="padding: 1rem; border-bottom: 1px solid var(--border-color); text-align: center;">غائب</th>
                            <th style="padding: 1rem; border-bottom: 1px solid var(--border-color); text-align: center;">تأخر</th>
                            <th style="padding: 1rem; border-bottom: 1px solid var(--border-color); text-align: center;">بعذر</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $index => $student)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9; color: var(--text-secondary);">{{ $index + 1 }}</td>
                            <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9;">
                                <div style="font-weight: 600;">{{ $student->name }}</div>
                                <div style="font-family: monospace; font-size: 0.8rem; color: var(--text-secondary);">{{ $student->student_number }}</div>
                            </td>

                            <!-- Radio Buttons -->
                            <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9; text-align: center;">
                                <label class="status-label present">
                                    <input type="radio" name="attendance[{{ $student->id }}]" value="present" checked>
                                    <span class="indicator"></span>
                                </label>
                            </td>
                            <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9; text-align: center;">
                                <label class="status-label absent">
                                    <input type="radio" name="attendance[{{ $student->id }}]" value="absent">
                                    <span class="indicator"></span>
                                </label>
                            </td>
                            <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9; text-align: center;">
                                <label class="status-label late">
                                    <input type="radio" name="attendance[{{ $student->id }}]" value="late">
                                    <span class="indicator"></span>
                                </label>
                            </td>
                            <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9; text-align: center;">
                                <label class="status-label excused">
                                    <input type="radio" name="attendance[{{ $student->id }}]" value="excused">
                                    <span class="indicator"></span>
                                </label>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 1rem; font-weight: 700;">
                    حفظ سجل الحضور
                </button>
            </div>
            @endif
        </div>
    </form>
</div>

<style>
    /* Custom Radio Styling */
    .status-label {
        display: inline-block;
        cursor: pointer;
        position: relative;
        width: 24px;
        height: 24px;
    }

    .status-label input {
        display: none;
    }

    .status-label .indicator {
        position: absolute;
        top: 0;
        left: 0;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: 2px solid #cbd5e1;
        background: white;
        transition: all 0.2s;
    }

    /* Hover effects */
    .status-label:hover .indicator {
        border-color: var(--text-secondary);
    }

    /* Checked States */
    .status-label.present input:checked+.indicator {
        background-color: var(--success-color);
        border-color: var(--success-color);
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
    }

    .status-label.absent input:checked+.indicator {
        background-color: var(--danger-color);
        border-color: var(--danger-color);
        box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.2);
    }

    .status-label.late input:checked+.indicator {
        background-color: var(--warning-color);
        border-color: var(--warning-color);
        box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
    }

    .status-label.excused input:checked+.indicator {
        background-color: var(--info-color);
        border-color: var(--info-color);
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    /* Inner dot for generic checked (optional, but solid color usually clearer) */
    .status-label input:checked+.indicator::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 10px;
        height: 10px;
        background: white;
        border-radius: 50%;
        opacity: 0.8;
    }
</style>

@endsection