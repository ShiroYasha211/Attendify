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
            <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; align-items: flex-end;">

                <!-- Date Input -->
                <div style="flex: 1; min-width: 200px;">
                    <label for="date" style="font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 0.5rem;">تاريخ المحاضرة:</label>
                    <input type="date" name="date" id="date" value="{{ date('Y-m-d') }}" required class="form-control" style="width: 100%;">
                </div>

                <!-- Lecture Title Input -->
                <div style="flex: 2; min-width: 300px;">
                    <label for="title" style="font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 0.5rem;">عنوان المحاضرة:</label>
                    <input type="text" name="title" id="title" value="{{ $prefill['title'] ?? old('title') }}" placeholder="مثال: مقدمة في الفيزياء، القانون الأول..." required class="form-control" style="width: 100%;">
                </div>

                <!-- Lecture Number Input -->
                <div style="flex: 0 0 150px;">
                    <label for="lecture_number" style="font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 0.5rem;">رقم المحاضرة:</label>
                    <input type="text" name="lecture_number" id="lecture_number" value="{{ $prefill['lecture_number'] ?? old('lecture_number') }}" placeholder="مثال: 1، 2..." class="form-control" style="width: 100%;">
                </div>

                <!-- Start Time Input (Optional) -->
                <div style="flex: 0 0 150px;">
                    <label for="start_time" style="font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 0.5rem;">وقت البداية: <span style="color: var(--text-secondary); font-size: 0.8rem;">(اختياري)</span></label>
                    <input type="time" name="start_time" id="start_time" value="{{ old('start_time') }}" class="form-control" style="width: 100%;">
                </div>

                <!-- End Time Input (Optional) -->
                <div style="flex: 0 0 150px;">
                    <label for="end_time" style="font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 0.5rem;">وقت الانتهاء: <span style="color: var(--text-secondary); font-size: 0.8rem;">(اختياري)</span></label>
                    <input type="time" name="end_time" id="end_time" value="{{ old('end_time') }}" class="form-control" style="width: 100%;">
                </div>

            </div>
        </div>

        <!-- Student List Card -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem; margin: 0;">
                    <span>قائمة الطلاب</span>
                    <span class="badge badge-info">{{ $students->count() }} طالب</span>
                </h3>

                <!-- Bulk Selection Buttons -->
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <button type="button" onclick="selectAll('present')" class="btn btn-sm" style="background: var(--success-color); color: white; display: flex; align-items: center; gap: 0.3rem; padding: 0.4rem 0.8rem; font-size: 0.85rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        الكل حاضر
                    </button>
                    <button type="button" onclick="selectAll('absent')" class="btn btn-sm" style="background: var(--danger-color); color: white; display: flex; align-items: center; gap: 0.3rem; padding: 0.4rem 0.8rem; font-size: 0.85rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        الكل غائب
                    </button>
                    <button type="button" onclick="selectAll('late')" class="btn btn-sm" style="background: var(--warning-color); color: white; display: flex; align-items: center; gap: 0.3rem; padding: 0.4rem 0.8rem; font-size: 0.85rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        الكل متأخر
                    </button>
                </div>
            </div>

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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $index => $student)
                        @php
                        $record = $attendanceRecords ? ($attendanceRecords[$student->id] ?? null) : null;
                        // Default logic:
                        // If records exist (review mode): Scanned -> present, Not Scanned -> absent
                        // If new mode: Default -> present (as before)
                        $defaultStatus = $attendanceRecords ? 'absent' : 'present';
                        $status = $record ? $record->status : $defaultStatus;
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9; color: var(--text-secondary);">{{ $index + 1 }}</td>
                            <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9;">
                                <div style="font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                                    {{ $student->name }}
                                    @if($record && $record->status == 'present')
                                    <span style="font-size: 0.7rem; background-color: #dcfce7; color: #166534; padding: 2px 6px; border-radius: 4px; border: 1px solid #bbf7d0;">QR</span>
                                    @endif
                                </div>
                                <div style="font-family: monospace; font-size: 0.8rem; color: var(--text-secondary);">{{ $student->student_number }}</div>
                            </td>

                            <!-- Radio Buttons -->
                            <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9; text-align: center;">
                                <label class="status-label present">
                                    <input type="radio" name="attendance[{{ $student->id }}]" value="present" {{ $status == 'present' ? 'checked' : '' }}>
                                    <span class="indicator"></span>
                                </label>
                            </td>
                            <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9; text-align: center;">
                                <label class="status-label absent">
                                    <input type="radio" name="attendance[{{ $student->id }}]" value="absent" {{ $status == 'absent' ? 'checked' : '' }}>
                                    <span class="indicator"></span>
                                </label>
                            </td>
                            <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9; text-align: center;">
                                <label class="status-label late">
                                    <input type="radio" name="attendance[{{ $student->id }}]" value="late" {{ $status == 'late' ? 'checked' : '' }}>
                                    <span class="indicator"></span>
                                </label>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">

                <!-- Lecture Notes -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="description" style="font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary-color)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        ملاحظات المحاضرة (اختياري)
                    </label>
                    <textarea name="description" id="description" rows="3" class="form-control"
                        style="width: 100%; resize: vertical; border-radius: 8px; border: 1px solid #e2e8f0; padding: 0.75rem;"
                        placeholder="أكتب هنا أي ملاحظات مهمة من الدكتور تخص هذه المحاضرة... مثلاً: الفصل الثالث مهم للاختبار، يجب مراجعة التمارين...">{{ old('description') }}</textarea>
                    <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.25rem;">
                        ستظهر هذه الملاحظات للطلاب في صفحة المحاضرات وفي مركز الدراسة
                    </p>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 1rem; font-weight: 700;">
                    حفظ سجل الحضور
                </button>
            </div>
            @endif
        </div>
    </form>
</div>

<!-- Overwrite Confirmation Modal -->
<div id="overwrite-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; width: 90%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
        <h3 style="margin-top: 0; font-size: 1.25rem; font-weight: 700; color: var(--text-primary);">تنبيه: تحضير مكرر ⚠️</h3>
        <p style="color: var(--text-secondary); margin: 1rem 0;">
            تم العثور على سجل حضور سابق لهذا اليوم بعنوان: <br>
            <strong id="existing-title" style="color: var(--primary-color);"></strong>
        </p>
        <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
            هل تريد استبدال البيانات السابقة بالبيانات الجديدة؟
        </p>
        <div style="display: flex; gap: 1rem; justify-content: flex-end;">
            <button onclick="cancelOverwrite()" class="btn btn-secondary">إلغاء</button>
            <button onclick="confirmOverwrite()" class="btn btn-primary">نعم، استبدال</button>
        </div>
    </div>
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

<script>
    function selectAll(status) {
        // Get all radio buttons with the specified value
        const radios = document.querySelectorAll('input[type="radio"][value="' + status + '"]');
        radios.forEach(function(radio) {
            radio.checked = true;
        });

        // Show feedback toast
        const messages = {
            'present': 'تم تحديد جميع الطلاب كـ حاضرين ✓',
            'absent': 'تم تحديد جميع الطلاب كـ غائبين ✗',
            'late': 'تم تحديد جميع الطلاب كـ متأخرين ⏱'
        };

        showToast(messages[status]);
    }

    function showToast(message) {
        // Create toast element if not exists
        let toast = document.getElementById('bulk-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'bulk-toast';
            toast.style.cssText = 'position: fixed; bottom: 2rem; left: 50%; transform: translateX(-50%); background: #1e293b; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; font-size: 0.9rem; z-index: 1000; opacity: 0; transition: opacity 0.3s;';
            document.body.appendChild(toast);
        }

        toast.textContent = message;
        toast.style.opacity = '1';

        setTimeout(function() {
            toast.style.opacity = '0';
        }, 2000);
    }

    // Duplicate Check Logic
    const dateInput = document.getElementById('date');
    const titleInput = document.getElementById('title');
    const lectureNumberInput = document.getElementById('lecture_number');
    const modal = document.getElementById('overwrite-modal');
    const existingTitleSpan = document.getElementById('existing-title');
    let previousDate = dateInput.value;

    function checkAttendance() {
        const date = dateInput.value;
        if (!date) return;

        fetch(`{{ route('delegate.attendance.check', $subject->id) }}?date=${date}`)
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    // Show warning
                    existingTitleSpan.textContent = data.title || 'بدون عنوان';
                    modal.style.display = 'flex';

                    // Pre-fill fields with existing data
                    if (data.title) titleInput.value = data.title;
                    if (data.lecture_number) lectureNumberInput.value = data.lecture_number;
                }
            })
            .catch(error => console.error('Error checking attendance:', error));
    }

    dateInput.addEventListener('change', checkAttendance);

    // Check on load if date is present
    if (dateInput.value) {
        checkAttendance();
    }

    function confirmOverwrite() {
        modal.style.display = 'none';
        showToast('سيتم تحديث سجل الحضور والمحاضرة عند الحفظ.');
    }

    function cancelOverwrite() {
        modal.style.display = 'none';
        // If it was an accidentally selected date, maybe clear it?
        // But if it was the default today, clearing it is annoying.
        // Let's just notify.
        showToast('يرجى تغيير التاريخ لتجنب الاستبدال.');
    }
</script>

@endsection