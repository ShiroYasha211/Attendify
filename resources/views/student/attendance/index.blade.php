@extends('layouts.student')

@section('title', 'سجل الحضور')

@section('content')

@section('content')

<div x-data="{ 
    showExcuseModal: false, 
    attendanceId: null, 
    lectureDate: '',
    openModal(id, date) {
        this.attendanceId = id;
        this.lectureDate = date;
        this.showExcuseModal = true;
    }
}">

    <!-- Header -->
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            سجل الحضور والغياب
        </h1>
        <p style="color: var(--text-secondary);">تقرير شامل عن حضورك في جميع المقررات الدراسية</p>
    </div>

    <!-- Stats Cards (Exact Dashboard Style) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">

        <!-- Presence Percentage -->
        <div class="card" style="display: flex; align-items: center; gap: 1.5rem; border-right: 4px solid var(--success-color);">
            <div style="width: 50px; height: 50px; background: rgba(16, 185, 129, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--success-color);">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div>
                <div style="font-size: 2rem; font-weight: 700; line-height: 1;">{{ $presencePercentage }}%</div>
                <div style="color: var(--text-secondary); font-size: 0.9rem;">نسبة الحضور</div>
            </div>
        </div>

        <!-- Total Lectures -->
        <div class="card" style="display: flex; align-items: center; gap: 1.5rem; border-right: 4px solid var(--primary-color);">
            <div style="width: 50px; height: 50px; background: rgba(79, 70, 229, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary-color);">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
            </div>
            <div>
                <div style="font-size: 2rem; font-weight: 700; line-height: 1;">{{ $totalLectures }}</div>
                <div style="color: var(--text-secondary); font-size: 0.9rem;">إجمالي المحاضرات</div>
            </div>
        </div>

        <!-- Present Count -->
        <div class="card" style="display: flex; align-items: center; gap: 1.5rem; border-right: 4px solid var(--info-color);">
            <div style="width: 50px; height: 50px; background: rgba(59, 130, 246, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--info-color);">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <div>
                <div style="font-size: 2rem; font-weight: 700; line-height: 1;">{{ $presentCount + $lateCount }}</div>
                <div style="color: var(--text-secondary); font-size: 0.9rem;">عدد مرات الحضور</div>
            </div>
        </div>

        <!-- Absent Count -->
        <div class="card" style="display: flex; align-items: center; gap: 1.5rem; border-right: 4px solid var(--danger-color);">
            <div style="width: 50px; height: 50px; background: rgba(239, 68, 68, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--danger-color);">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
            </div>
            <div>
                <div style="font-size: 2rem; font-weight: 700; line-height: 1;">{{ $absentCount }}</div>
                <div style="color: var(--text-secondary); font-size: 0.9rem;">عدد مرات الغياب</div>
            </div>
        </div>

    </div>

    <!-- Detailed Report -->
    <div class="card">
        <div class="card-header bg-white border-bottom pt-4 px-4 pb-2">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                📝 تفاصيل الحضور حسب المقرر
            </h3>
        </div>
        <div class="card-body p-0">
            @if($attendanceBySubject->count() > 0)
            <div class="accordion" id="attendanceAccordion">
                @foreach($attendanceBySubject as $subjectId => $records)
                @php
                $subjectName = $records->first()->subject->name ?? 'مادة غير معروفة';
                $subPresent = $records->whereIn('status', ['present', 'late'])->count();
                $subTotal = $records->count();
                $subPercentage = $subTotal > 0 ? round(($subPresent / $subTotal) * 100) : 0;
                $collapsId = "collapse-" . $subjectId;
                @endphp
                <div style="border-bottom: 1px solid #f1f5f9;">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapsId }}" style="width: 100%; background: none; border: none; padding: 1.25rem; text-align: right; display: flex; justify-content: space-between; align-items: center; cursor: pointer;">
                        <span style="font-weight: 700; color: var(--text-primary); font-size: 1rem;">
                            {{ $subjectName }}
                        </span>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span class="badge {{ $subPercentage >= 75 ? 'badge-success-subtle' : 'badge-danger-subtle' }}">
                                نسبة الحضور: {{ $subPercentage }}%
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-secondary);">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </button>
                    <div id="{{ $collapsId }}" class="accordion-collapse collapse" data-bs-parent="#attendanceAccordion">
                        <div style="padding: 0 1.25rem 1.25rem;">
                            <table class="table table-sm table-bordered mb-0" style="font-size: 0.9rem;">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="padding: 0.75rem;">التاريخ</th>
                                        <th style="padding: 0.75rem;">الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($records as $record)
                                    <tr>
                                        <td style="padding: 0.75rem;">{{ \Carbon\Carbon::parse($record->date)->format('Y-m-d') }}</td>
                                        <td style="padding: 0.75rem;">
                                            @if($record->status == 'present')
                                            <span class="badge badge-success">حاضر</span>
                                            @elseif($record->status == 'absent')
                                            <span class="badge badge-danger">غائب</span>

                                            {{-- Excuse Logic --}}
                                            @php
                                            $canExcuse = false;
                                            $deadline = \Carbon\Carbon::parse($record->date)->addDays(7);
                                            if(now()->lte($deadline) && !$record->excuse) {
                                            $canExcuse = true;
                                            }
                                            @endphp

                                            @if($record->excuse)
                                            <div style="margin-top: 5px; font-size: 0.8rem;">
                                                @if($record->excuse->status == 'pending')
                                                <span class="text-warning">⏳ العذر قيد المراجعة</span>
                                                @elseif($record->excuse->status == 'accepted')
                                                <span class="text-success">✅ تم قبول العذر</span>
                                                @elseif($record->excuse->status == 'rejected')
                                                <span class="text-danger">❌ تم رفض العذر</span>
                                                @endif
                                            </div>
                                            @elseif($canExcuse)
                                            <button type="button"
                                                class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1 mt-1"
                                                style="padding: 0.25rem 0.75rem; font-size: 0.8rem; border-radius: 8px; transition: all 0.2s;"
                                                @click="openModal({{ $record->id }}, '{{ $record->date->format('Y-m-d') }}')">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                    <polyline points="14 2 14 8 20 8"></polyline>
                                                    <line x1="12" y1="18" x2="12" y2="12"></line>
                                                    <line x1="9" y1="15" x2="15" y2="15"></line>
                                                </svg>
                                                تقديم عذر
                                            </button>
                                            @endif

                                            @elseif($record->status == 'late')
                                            <span class="badge badge-warning">تأخر</span>
                                            @elseif($record->status == 'excused')
                                            <span class="badge badge-info">معذور</span>
                                            @endif
                                        </td>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                لا توجد سجلات حضور مسجلة حتى الآن.
            </div>
            @endif
        </div>
    </div>


    <!-- Alpine Expense Modal -->
    <div x-show="showExcuseModal" class="modal-overlay" style="display: none;" x-transition.opacity.duration.300ms>
        <div class="modal-container" @click.away="showExcuseModal = false" style="max-width: 500px;">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 class="modal-title" style="font-size: 1.25rem; font-weight: 700;">تقديم عذر غياب</h3>
                <button type="button" @click="showExcuseModal = false" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-secondary);">&times;</button>
            </div>

            <form action="{{ route('student.excuse.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="attendance_id" :value="attendanceId">

                <div class="mb-3" style="margin-bottom: 1rem;">
                    <label class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">تاريخ المحاضرة</label>
                    <input type="text" class="form-control" :value="lectureDate" disabled style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px; background: #f3f4f6;">
                </div>

                <div class="mb-3" style="margin-bottom: 1rem;">
                    <label class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">سبب الغياب <span class="text-danger" style="color: var(--danger-color);">*</span></label>
                    <textarea name="reason" rows="3" required placeholder="اشرح سبب الغياب بالتفصيل..." style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px;"></textarea>
                </div>

                <div class="mb-3" style="margin-bottom: 1.5rem;">
                    <label class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">مرفق (اختياري)</label>
                    <input type="file" name="attachment" accept=".pdf,.jpg,.png,.jpeg" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px;">
                    <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.25rem;">صورة أو ملف PDF يثبت العذر (الحد الأقصى 2 ميجابايت).</div>
                </div>

                <div class="modal-actions" style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                    <button type="button" class="btn btn-secondary" @click="showExcuseModal = false" style="padding: 0.5rem 1rem; border-radius: 6px; border: 1px solid var(--border-color); background: white;">إلغاء</button>
                    <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; border-radius: 6px; background: var(--primary-color); color: white; border: none;">إرسال العذر</button>
                </div>
            </form>
        </div>
    </div>

</div> <!-- End x-data -->

<!-- Bootstrap JS Bundle for Accordion Only -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@endsection