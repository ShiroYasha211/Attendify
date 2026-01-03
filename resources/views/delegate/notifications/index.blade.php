@extends('layouts.delegate')

@section('title', 'تنبيهات الغياب')

@section('content')

<div class="container" style="max-width: 100%;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">تنبيهات الغياب</h1>
            <p style="color: var(--text-secondary);">متابعة غيابات الطلاب وإرسال تنبيهات الحرمان والإنذارات.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 1.5rem;">
        {{ session('success') }}
    </div>
    @endif

    <div class="card">
        @if(count($report) == 0)
        <div style="text-align: center; padding: 4rem 2rem;">
            <div style="color: var(--text-secondary); margin-bottom: 1rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
            </div>
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">لا توجد غيابات ملحوظة</h3>
            <p style="color: var(--text-secondary);">جميع الطلاب في حالة التزام جيد ولا يوجد تنبيهات لإرسالها.</p>
        </div>
        @else
        <div class="table-container">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0;">
                <thead>
                    <tr style="background-color: #f8fafc; text-align: right;">
                        <th style="padding: 1rem; border-bottom: 1px solid var(--border-color);">الطالب</th>
                        <th style="padding: 1rem; border-bottom: 1px solid var(--border-color);">المادة</th>
                        <th style="padding: 1rem; border-bottom: 1px solid var(--border-color); text-align: center;">عدد الغياب</th>
                        <th style="padding: 1rem; border-bottom: 1px solid var(--border-color);">الوضع الأكاديمي</th>
                        <th style="padding: 1rem; border-bottom: 1px solid var(--border-color); text-align: center;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report as $item)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9;">
                            <div style="font-weight: 700; color: var(--text-primary);">{{ $item['student']->name }}</div>
                            <div style="font-size: 0.85rem; color: var(--text-secondary); font-family: monospace;">{{ $item['student']->student_number }}</div>
                        </td>
                        <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9;">
                            {{ $item['subject']->name }}
                        </td>
                        <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9; text-align: center;">
                            <span class="badge {{ $item['absences'] >= 5 ? 'badge-danger' : 'badge-warning' }}">
                                {{ $item['absences'] }} محاضرة
                            </span>
                        </td>
                        <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9;">
                            @if($item['absences'] >= 5)
                            <span style="color: var(--danger-color); font-weight: 700; display: flex; align-items: center; gap: 0.25rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg>
                                مهدد بالحرمان
                            </span>
                            @elseif($item['absences'] >= 3)
                            <span style="color: var(--warning-color); font-weight: 700;">إنذار أول</span>
                            @else
                            <span style="color: var(--success-color);">طبيعي</span>
                            @endif
                        </td>
                        <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9; text-align: center;">
                            <button type="button"
                                class="btn btn-outline-danger btn-sm"
                                onclick="openWarningModal('{{ $item['student']->id }}', '{{ $item['student']->name }}', '{{ $item['subject']->id }}', '{{ $item['subject']->name }}', {{ $item['absences'] }})">
                                إرسال تنبيه
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<!-- Warning Modal (Styled to match Admin AlpinJs or generic modal) -->
<!-- Using standard Bootstrap here as injected in layout, but styling to match -->
<div class="modal fade" id="warningModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('delegate.notifications.store') }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-danger text-white">
                    <h5 class="modal-title font-weight-bold">إرسال تنبيه غياب</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="student_id" id="modal_student_id">
                    <input type="hidden" name="subject_id" id="modal_subject_id">

                    <div class="alert alert-light border d-flex align-items-center mb-4">
                        <div style="width: 40px; height: 40px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #dc2626; margin-left: 1rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                <line x1="12" y1="9" x2="12" y2="13"></line>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                        </div>
                        <div>
                            <div class="small text-muted">سيتم إرسال التنبيه للطالب:</div>
                            <strong id="modal_student_name_display" class="fs-5"></strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">نص الرسالة</label>
                        <textarea name="message" class="form-control" rows="4" required id="modal_message" style="background-color: #f8fafc; border-color: #e2e8f0;"></textarea>
                        <div class="form-text text-muted">يمكنك تعديل الرسالة المقترحة أعلاه.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger px-4">تأكيد الإرسال</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function openWarningModal(studentId, studentName, subjectId, subjectName, absences) {
        document.getElementById('modal_student_id').value = studentId;
        document.getElementById('modal_subject_id').value = subjectId;
        document.getElementById('modal_student_name_display').innerText = studentName;

        let msg = `عزيزي الطالب ${studentName}،\nنود تنبيهك بأن عدد مرات غيابك في مادة (${subjectName}) قد وصل إلى ${absences} محاضرات.\nيرجى مراجعة المرشد الأكاديمي أو أستاذ المقرر لتجنب الحرمان من دخول الاختبار النهائي.`;
        document.getElementById('modal_message').value = msg;

        var myModal = new bootstrap.Modal(document.getElementById('warningModal'));
        myModal.show();
    }
</script>

<!-- Ensure Bootstrap 5 JS is available for Modal -->
@if(!View::hasSection('scripts_loaded'))
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endif

@endsection