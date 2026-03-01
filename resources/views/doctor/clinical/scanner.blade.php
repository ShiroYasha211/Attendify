@extends('layouts.doctor')
@section('title', 'ماسح QR — التحضير')
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
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text-primary);
        margin: 0 0 0.15rem 0;
    }

    .clinical-page-header .right-side p {
        color: var(--text-secondary);
        font-size: 0.9rem;
        margin: 0;
    }

    .btn-back {
        background: white;
        color: var(--text-secondary);
        border: 1.5px solid #e2e8f0;
        padding: 0.55rem 1.1rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.88rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-back:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        text-decoration: none;
    }

    .scanner-card {
        background: white;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    #scanner-video {
        width: 100%;
        max-width: 400px;
        border-radius: 14px;
        margin: 0 auto 1rem;
        display: block;
        border: 2px solid #e2e8f0;
    }

    #manual-input {
        display: flex;
        gap: 0.75rem;
        max-width: 500px;
        margin: 0 auto;
    }

    #manual-input input {
        flex: 1;
        padding: 0.65rem 0.85rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.9rem;
        background: #f8fafc;
        font-family: inherit;
    }

    #manual-input button {
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        color: white;
        border: none;
        padding: 0.65rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
    }

    .result-popup {
        display: none;
        background: white;
        border-radius: 18px;
        border: 2px solid #c7d2fe;
        padding: 1.5rem;
        margin-top: 1rem;
        box-shadow: 0 4px 20px rgba(79, 70, 229, 0.08);
    }

    .result-popup.show {
        display: block;
        animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .student-info {
        background: #f8fafc;
        border-radius: 14px;
        padding: 1.25rem;
        margin-bottom: 1rem;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 0.35rem 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.88rem;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-row .lbl {
        color: var(--text-secondary);
        font-weight: 600;
    }

    .info-row .val {
        color: var(--text-primary);
        font-weight: 700;
    }

    .signatures-section {
        margin: 1rem 0;
    }

    .signatures-section h4 {
        font-weight: 700;
        font-size: 0.95rem;
        margin-bottom: 0.75rem;
    }

    .sig-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }

    .sig-card {
        border-radius: 10px;
        padding: 0.75rem;
        text-align: center;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .sig-card.active {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .sig-card.empty {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    .activities-list {
        background: #fafbfe;
        border-radius: 10px;
        padding: 0.75rem;
        margin: 0.75rem 0;
    }

    .activity-item {
        font-size: 0.82rem;
        padding: 0.3rem 0;
        color: #475569;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        gap: 0.5rem;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .action-btns {
        display: flex;
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .btn-confirm {
        flex: 1;
        background: linear-gradient(135deg, #059669, #10b981);
        color: white;
        border: none;
        padding: 0.75rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
    }

    .btn-reject {
        flex: 1;
        background: #fef2f2;
        color: #dc2626;
        border: 1.5px solid #fca5a5;
        padding: 0.75rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
    }

    .notes-input {
        width: 100%;
        padding: 0.65rem 0.85rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.88rem;
        background: #f8fafc;
        font-family: inherit;
        margin-top: 0.75rem;
        box-sizing: border-box;
    }

    .alert {
        padding: 0.85rem 1rem;
        border-radius: 10px;
        margin-top: 1rem;
        font-weight: 600;
        font-size: 0.9rem;
        display: none;
    }

    .alert.success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .alert.error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }
</style>

<div class="clinical-page-header">
    <div class="right-side">
        <h1>📷 ماسح QR — التحضير</h1>
        <p>امسح رمز الطالب لتأكيد الحضور وتوقيع السجل اليومي</p>
    </div>
    <div class="left-side">
        <a href="{{ route('doctor.clinical.manual-attendance') }}" class="btn-back">✍ تحضير يدوي</a>
        <a href="{{ route('doctor.clinical.index') }}" class="btn-back"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg> القسم العملي</a>
    </div>
</div>

<div class="scanner-card">
    <div id="scanner-container" style="width: 100%; max-width: 400px; margin: 0 auto 1rem; border: 2px solid #e2e8f0; border-radius: 14px; overflow: hidden;"></div>
    <div id="manual-input">
        <input type="text" id="qr-input" placeholder="أو أدخل رمز QR يدوياً...">
        <button onclick="processManualInput()">بحث</button>
    </div>
</div>

<div class="result-popup" id="resultPopup">
    <div class="student-info" id="studentInfo"></div>
    <div class="signatures-section" id="sigSection"></div>
    <div id="activitiesSection"></div>
    <textarea class="notes-input" id="doctorNotes" rows="2" placeholder="ملاحظات الدكتور (اختياري)..."></textarea>
    <div class="action-btns">
        <button class="btn-confirm" onclick="confirmLog('confirm')">✅ تأكيد (4 توقيعات)</button>
        <button class="btn-reject" onclick="confirmLog('reject')">❌ رفض</button>
    </div>
</div>

<div class="alert success" id="successAlert"></div>
<div class="alert error" id="errorAlert"></div>

@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.4/html5-qrcode.min.js"></script>
<script>
    let currentLogId = null;

    document.addEventListener('DOMContentLoaded', function() {
        try {
            // Use Html5QrcodeScanner for automatic camera handling and UI
            const html5QrcodeScanner = new Html5QrcodeScanner(
                "scanner-container", {
                    fps: 10,
                    qrbox: {
                        width: 250,
                        height: 250
                    },
                    rememberLastUsedCamera: true
                },
                /* verbose= */
                false);

            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        } catch (e) {
            console.log('Scanner init error:', e);
            showAlert('error', 'فشل في تهيئة الكاميرا. الرجاء التأكد من الأذونات.');
        }
    });

    function onScanFailure(error) {
        // handle scan failure, usually better to ignore and keep scanning
        // console.warn(`Code scan error = ${error}`);
    }

    function onScanSuccess(token) {
        processToken(token);
    }

    function processManualInput() {
        processToken(document.getElementById('qr-input').value.trim());
    }

    function processToken(token) {
        if (!token) return;
        hideAlerts();
        fetch("{{ route('doctor.clinical.scanner.process') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    qr_token: token
                })
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    showAlert('error', data.message);
                    return;
                }
                currentLogId = data.log_id;
                const d = data.data;

                document.getElementById('studentInfo').innerHTML = `
            <div class="info-row"><span class="val">${d.student_name}</span><span class="lbl">الطالب</span></div>
            <div class="info-row"><span class="val">${d.student_number}</span><span class="lbl">الرقم</span></div>
            <div class="info-row"><span class="val">${d.training_center}</span><span class="lbl">المركز</span></div>
            <div class="info-row"><span class="val">${d.department}</span><span class="lbl">القسم</span></div>
            <div class="info-row"><span class="val">${d.doctor_name}</span><span class="lbl">الدكتور</span></div>
            <div class="info-row"><span class="val">${d.log_date} — ${d.log_time}</span><span class="lbl">التاريخ</span></div>
        `;

                document.getElementById('sigSection').innerHTML = `
            <h4>📋 ملخص التوقيعات</h4>
            <div class="sig-grid">
                <div class="sig-card active">✅ حضور</div>
                <div class="sig-card ${d.history_count > 0 ? 'active' : 'empty'}">📋 قصص مرضية (${d.history_count})</div>
                <div class="sig-card ${d.exam_count > 0 ? 'active' : 'empty'}">🩺 فحوصات (${d.exam_count})</div>
                <div class="sig-card ${d.did_round ? 'active' : 'empty'}">🔄 مرور ${d.did_round ? '✓' : '✗'}</div>
            </div>
        `;

                let acts = '';
                if (d.histories && d.histories.length) {
                    acts += '<strong>📋 القصص:</strong>';
                    d.histories.forEach(h => acts += `<div class="activity-item"><span>•</span>${h.body_system}</div>`);
                }
                if (d.exams && d.exams.length) {
                    acts += '<strong style="display:block;margin-top:0.5rem;">🩺 الفحوصات:</strong>';
                    d.exams.forEach(e => acts += `<div class="activity-item"><span>•</span>${e.body_system}</div>`);
                }
                if (d.rounds && d.rounds.length) {
                    acts += '<strong style="display:block;margin-top:0.5rem;">🔄 المرور:</strong>';
                    d.rounds.forEach(r => acts += `<div class="activity-item"><span>•</span>${r.case_name}</div>`);
                }
                document.getElementById('activitiesSection').innerHTML = acts ? `<div class="activities-list">${acts}</div>` : '';

                document.getElementById('resultPopup').classList.add('show');
            })
            .catch(err => showAlert('error', 'خطأ في الشبكة'));
    }

    function confirmLog(action) {
        fetch("{{ route('doctor.clinical.scanner.confirm') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    log_id: currentLogId,
                    action: action,
                    doctor_notes: document.getElementById('doctorNotes').value
                })
            })
            .then(r => r.json())
            .then(data => {
                document.getElementById('resultPopup').classList.remove('show');
                showAlert(data.success ? 'success' : 'error', data.message);
                currentLogId = null;
            });
    }

    function showAlert(type, msg) {
        hideAlerts();
        const el = document.getElementById(type === 'success' ? 'successAlert' : 'errorAlert');
        el.textContent = msg;
        el.style.display = 'block';
        setTimeout(() => el.style.display = 'none', 5000);
    }

    function hideAlerts() {
        document.getElementById('successAlert').style.display = 'none';
        document.getElementById('errorAlert').style.display = 'none';
    }
</script>
@endpush