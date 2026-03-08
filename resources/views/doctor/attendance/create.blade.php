@extends('layouts.doctor')

@section('title', 'رصد الحضور')

@section('content')

<div class="container" style="max-width: 100%;" x-data="attendancePage()" x-cloak>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">رصد الحضور</h1>
            <p style="color: var(--text-secondary);">
                المادة: <span style="font-weight: 700; color: var(--primary-color);">{{ $subject->name }}</span> ({{ $subject->code }})
            </p>
        </div>
        <a href="{{ route('doctor.attendance.index') }}" class="btn btn-secondary" style="display: flex; align-items: center; gap: 0.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            عودة للحضور
        </a>
    </div>

    <form id="attendance-form" action="{{ route('doctor.attendance.store', $subject->id) }}" method="POST">
        @csrf

        @if(!empty($prefill['from_qr']))
        <input type="hidden" name="qr_session_id" value="{{ request('qr_session_id') }}">
        @endif

        <!-- Date Selection Card -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; align-items: flex-end;">

                <!-- Date Input -->
                <div style="flex: 1; min-width: 200px;">
                    <label for="date" style="font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 0.5rem;">تاريخ المحاضرة:</label>
                    <input type="date" name="date" id="date" value="{{ $prefill['date'] ?? date('Y-m-d') }}" required class="form-control" style="width: 100%;">
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
                    <input type="time" name="start_time" id="start_time" value="{{ $prefill['start_time'] ?? old('start_time') }}" class="form-control" style="width: 100%;">
                </div>

                <!-- End Time Input (Optional) -->
                <div style="flex: 0 0 150px;">
                    <label for="end_time" style="font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 0.5rem;">وقت الانتهاء: <span style="color: var(--text-secondary); font-size: 0.8rem;">(اختياري)</span></label>
                    <input type="time" name="end_time" id="end_time" value="{{ $prefill['end_time'] ?? old('end_time') }}" class="form-control" style="width: 100%;">
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
                    <button type="button" @click="openQrModal()" class="btn btn-sm" style="background: #6366f1; color: white; display: flex; align-items: center; gap: 0.3rem; padding: 0.4rem 0.8rem; font-size: 0.85rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                        </svg>
                        تحضير بـ QR
                    </button>
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
                    <button type="button" onclick="selectAll('excused')" class="btn btn-sm" style="background: var(--info-color); color: white; display: flex; align-items: center; gap: 0.3rem; padding: 0.4rem 0.8rem; font-size: 0.85rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                        الكل بعذر
                    </button>
                </div>
            </div>

            @if($students->isEmpty())
            <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                لا يوجد طلاب مسجلين في هذه الدفعة حالياً.
            </div>
            @else
            <div class="table-container">
                <div class="table-responsive">
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
                        @php
                        $record = $attendanceRecords ? ($attendanceRecords[$student->id] ?? null) : null;
                        $defaultStatus = $attendanceRecords ? 'absent' : 'present';
                        $status = $record ? $record->status : $defaultStatus;
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9; color: var(--text-secondary);">{{ $index + 1 }}</td>
                            <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9;">
                                <div style="font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                                    {{ $student->name }}
                                    @if($record && $record->attendance_method == 'qr')
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
                            <td style="padding: 1rem; border-bottom: 1px solid #f1f5f9; text-align: center;">
                                <label class="status-label excused">
                                    <input type="radio" name="attendance[{{ $student->id }}]" value="excused" {{ $status == 'excused' ? 'checked' : '' }}>
                                    <span class="indicator"></span>
                                </label>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
</div>
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
                        placeholder="أكتب هنا أي ملاحظات مهمة تخص هذه المحاضرة... مثلاً: الفصل الثالث مهم للاختبار، يجب مراجعة التمارين...">{{ $prefill['description'] ?? old('description') }}</textarea>
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

    <!-- QR Code Modal (Integrated into Create Page) -->
    <div x-show="showQrModal" style="display: none;"
        class="qr-modal-overlay"
        x-transition.opacity>
        <div @click.away="closeQrModal()"
            style="background: white; border-radius: 16px; width: 100%; max-width: 480px; padding: 2rem; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">

            <!-- QR Active Phase -->
            <div style="text-align: center;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                    <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700;">تحضير سريع بـ QR</h3>
                    <button @click="closeQrModal()" style="background: none; border: none; cursor: pointer; padding: 4px; color: var(--text-secondary);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                {{-- Loading State --}}
                <div x-show="qrLoading" style="padding: 3rem; display: none;">
                    <div style="color: var(--text-secondary); font-size: 0.95rem;">جاري بدء الجلسة...</div>
                </div>

                {{-- Active QR State --}}
                <div x-show="qrActive && !qrLoading" style="display: none;">
                    <p style="color: var(--text-secondary); margin-bottom: 1rem; font-size: 0.9rem;">اعرض الكود للطلاب لمسحه — يتجدد كل 10 ثواني</p>

                    <!-- QR Code Display -->
                    <div id="qrcode" style="display: flex; justify-content: center; margin-bottom: 1.5rem;"></div>

                    <!-- Timer Bar -->
                    <div style="height: 4px; background: #e2e8f0; border-radius: 2px; margin-bottom: 1.5rem; overflow: hidden;">
                        <div style="height: 100%; background: #6366f1; transition: width 0.1s linear;" :style="'width: ' + timerWidth + '%'"></div>
                    </div>

                    <!-- Stats -->
                    <div style="display: flex; justify-content: center; gap: 2rem; margin-bottom: 1.5rem; background: #f8fafc; padding: 1rem; border-radius: 8px;">
                        <div>
                            <div style="font-size: 1.5rem; font-weight: 700; color: #10b981;" x-text="scannedCount"></div>
                            <div style="font-size: 0.85rem; color: var(--text-secondary);">حضور</div>
                        </div>
                        <div>
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary);" x-text="totalStudents"></div>
                            <div style="font-size: 0.85rem; color: var(--text-secondary);">إجمالي الطلاب</div>
                        </div>
                    </div>

                    <button @click="finalizeQrSession()" class="btn btn-danger" :disabled="qrFinalizing"
                        style="width: 100%; padding: 0.65rem; font-weight: 600;">
                        <span x-show="qrFinalizing" style="display: none;">جاري الإنهاء...</span>
                        <span x-show="!qrFinalizing" style="display: inline;">إنهاء المسح وتعبئة القائمة</span>
                    </button>
                </div>

                {{-- Error State --}}
                <div x-show="qrError" style="padding: 2rem; display: none;">
                    <div style="color: var(--danger-color); font-size: 0.95rem; margin-bottom: 1rem;" x-text="qrError"></div>
                    <button @click="showQrModal = false" class="btn btn-secondary">إغلاق</button>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    /* QR Modal Overlay - centered */
    .qr-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        z-index: 9999;
        display: flex !important;
        align-items: center;
        justify-content: center;
    }
    .qr-modal-overlay[style*="display: none"] {
        display: none !important;
    }

    [x-cloak] {
        display: none !important;
    }

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

    /* Inner dot */
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

<!-- QR Code Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
    function attendancePage() {
        return {
            showQrModal: false,
            qrLoading: false,
            qrActive: false,
            qrFinalizing: false,
            qrError: '',
            sessionId: null,
            qrObject: null,
            timerWidth: 100,
            scannedCount: 0,
            totalStudents: {{ $students->count() }},
            tokenInterval: null,
            statusInterval: null,
            animationInterval: null,

            openQrModal() {
                const title = document.getElementById('title').value;
                if (!title) {
                    showToast('يرجى إدخال عنوان المحاضرة أولاً ⚠️');
                    document.getElementById('title').focus();
                    return;
                }

                this.showQrModal = true;
                this.qrError = '';
                this.qrActive = false;
                this.qrLoading = true;
                this.scannedCount = 0;

                this.startQrSession();
            },

            closeQrModal() {
                if (this.qrActive) {
                    if (!confirm('هل أنت متأكد من إغلاق نافذة QR؟ سيتم إيقاف الجلسة.')) return;
                    this.stopIntervals();
                }
                this.showQrModal = false;
                this.qrActive = false;
            },

            async startQrSession() {
                const formData = {
                    subject_id: '{{ $subject->id }}',
                    date: document.getElementById('date').value,
                    title: document.getElementById('title').value,
                    lecture_number: document.getElementById('lecture_number').value || null,
                };

                try {
                    const response = await fetch('/api/qr-attendance/start', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(formData)
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        this.qrLoading = false;
                        this.qrError = data.message || 'حدث خطأ ما';
                        return;
                    }

                    this.sessionId = data.session_id;
                    this.qrLoading = false;
                    this.qrActive = true;

                    // Initialize QR code
                    this.$nextTick(() => {
                        const qrContainer = document.getElementById('qrcode');
                        if (qrContainer) qrContainer.innerHTML = '';
                        this.qrObject = new QRCode(document.getElementById("qrcode"), {
                            text: data.token,
                            width: 256,
                            height: 256,
                            colorDark: "#000000",
                            colorLight: "#ffffff",
                            correctLevel: QRCode.CorrectLevel.H
                        });

                        this.startRotation();
                        this.startPollingStatus();
                    });

                } catch (error) {
                    console.error(error);
                    this.qrLoading = false;
                    this.qrError = 'حدث خطأ في الاتصال بالخادم';
                }
            },

            startRotation() {
                let seconds = 10;
                this.timerWidth = 100;

                // Token rotation every 10 seconds
                this.tokenInterval = setInterval(async () => {
                    this.timerWidth = 100;
                    seconds = 10;

                    try {
                        const response = await fetch(`/api/qr-attendance/${this.sessionId}/token`);
                        const data = await response.json();

                        if (response.ok && this.qrObject) {
                            this.qrObject.clear();
                            this.qrObject.makeCode(data.token);
                        }
                    } catch (e) {
                        console.error('Failed to rotate token', e);
                    }
                }, 10000);

                // Animation interval for progress bar
                this.animationInterval = setInterval(() => {
                    seconds -= 0.1;
                    if (seconds < 0) seconds = 0;
                    this.timerWidth = (seconds / 10) * 100;
                }, 100);
            },

            startPollingStatus() {
                this.statusInterval = setInterval(async () => {
                    try {
                        const response = await fetch(`/api/qr-attendance/${this.sessionId}/status`);
                        const data = await response.json();

                        if (response.ok) {
                            this.scannedCount = data.scanned_count;
                            this.totalStudents = data.total_students;
                        }
                    } catch (e) {
                        console.error('Failed to fetch status', e);
                    }
                }, 3000);
            },

            stopIntervals() {
                if (this.tokenInterval) clearInterval(this.tokenInterval);
                if (this.statusInterval) clearInterval(this.statusInterval);
                if (this.animationInterval) clearInterval(this.animationInterval);
                this.tokenInterval = null;
                this.statusInterval = null;
                this.animationInterval = null;
            },

            async finalizeQrSession() {
                if (!confirm('هل أنت متأكد من إنهاء جلسة QR؟ سيتم تعيين الطلاب الذين لم يمسحوا الكود كـ "غائبين" تلقائياً.')) return;

                this.qrFinalizing = true;
                this.stopIntervals();

                try {
                    // First finalize the session
                    const finalizeResponse = await fetch(`/api/qr-attendance/${this.sessionId}/finalize`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const finalizeData = await finalizeResponse.json();

                    if (!finalizeResponse.ok) {
                        this.qrError = finalizeData.message || 'حدث خطأ أثناء الإنهاء';
                        this.qrFinalizing = false;
                        this.qrActive = false;
                        return;
                    }

                    // Then fetch the final status to update the attendance form
                    const statusResponse = await fetch(`/api/qr-attendance/${this.sessionId}/status`);
                    const statusData = await statusResponse.json();

                    if (statusResponse.ok && statusData.students) {
                        // Update radio buttons based on QR scan results
                        statusData.students.forEach(student => {
                            const presentRadio = document.querySelector(`input[name="attendance[${student.id}]"][value="present"]`);
                            const absentRadio = document.querySelector(`input[name="attendance[${student.id}]"][value="absent"]`);

                            if (student.status === 'present' && presentRadio) {
                                presentRadio.checked = true;
                            } else if (absentRadio) {
                                absentRadio.checked = true;
                            }
                        });

                        showToast(`✅ تم تحديث القائمة: ${statusData.scanned_count} حاضر — ${statusData.total_students - statusData.scanned_count} غائب. يمكنك التعديل قبل الحفظ.`);
                    }

                    // Add hidden field for QR session tracking
                    let hiddenInput = document.querySelector('input[name="qr_session_id"]');
                    if (!hiddenInput) {
                        hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'qr_session_id';
                        document.getElementById('attendance-form').appendChild(hiddenInput);
                    }
                    hiddenInput.value = this.sessionId;

                    // Close modal
                    this.showQrModal = false;
                    this.qrActive = false;
                    this.qrFinalizing = false;

                } catch (error) {
                    console.error(error);
                    this.qrError = 'حدث خطأ أثناء الإنهاء';
                    this.qrFinalizing = false;
                    this.qrActive = false;
                }
            }
        }
    }

    function selectAll(status) {
        const radios = document.querySelectorAll('input[type="radio"][value="' + status + '"]');
        radios.forEach(function(radio) {
            radio.checked = true;
        });

        const messages = {
            'present': 'تم تحديد جميع الطلاب كـ حاضرين ✓',
            'absent': 'تم تحديد جميع الطلاب كـ غائبين ✗',
            'late': 'تم تحديد جميع الطلاب كـ متأخرين ⏱',
            'excused': 'تم تحديد جميع الطلاب كـ بعذر ℹ️'
        };

        showToast(messages[status]);
    }

    function showToast(message) {
        let toast = document.getElementById('bulk-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'bulk-toast';
            toast.style.cssText = 'position: fixed; bottom: 2rem; left: 50%; transform: translateX(-50%); background: #1e293b; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; font-size: 0.9rem; z-index: 10000; opacity: 0; transition: opacity 0.3s;';
            document.body.appendChild(toast);
        }

        toast.textContent = message;
        toast.style.opacity = '1';

        setTimeout(function() {
            toast.style.opacity = '0';
        }, 2000);
    }
</script>

@endsection