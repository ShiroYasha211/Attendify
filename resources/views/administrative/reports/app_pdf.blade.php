<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            direction: rtl;
            color: #111827;
            font-size: 11px;
            line-height: 1.65;
        }
        .header {
            border-bottom: 2px solid #111827;
            padding-bottom: 12px;
            margin-bottom: 18px;
            width: 100%;
            overflow: hidden;
        }
        .header-block {
            display: inline-block;
            vertical-align: top;
            width: 32%;
        }
        h1 {
            text-align: center;
            font-size: 18px;
            margin: 0 0 16px;
            text-decoration: underline;
        }
        .meta, .summary {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            padding: 10px;
            margin-bottom: 14px;
        }
        .summary span {
            display: inline-block;
            margin-left: 18px;
            font-weight: 700;
        }
        .report-table {
            width: 100%;
            border-top: 1px solid #d1d5db;
            border-right: 1px solid #d1d5db;
            margin-top: 10px;
        }
        .report-row {
            width: 100%;
            white-space: nowrap;
            font-size: 0;
            page-break-inside: avoid;
        }
        .report-cell {
            display: inline-block;
            box-sizing: border-box;
            white-space: normal;
            vertical-align: top;
            border-left: 1px solid #d1d5db;
            border-bottom: 1px solid #d1d5db;
            padding: 6px 4px;
            min-height: 24px;
            text-align: center;
            font-size: 10px;
        }
        .report-head .report-cell {
            background: #f1f5f9;
            font-weight: 700;
        }
        .text-right { text-align: right; }
        .danger { color: #b91c1c; font-weight: 700; }
        .warning { color: #b45309; font-weight: 700; }
        .ok { color: #047857; font-weight: 700; }
        .w-5 { width: 5%; }
        .w-8 { width: 8%; }
        .w-10 { width: 10%; }
        .w-12 { width: 12%; }
        .w-14 { width: 14%; }
        .w-15 { width: 15%; }
        .w-16 { width: 16%; }
        .w-18 { width: 18%; }
        .w-20 { width: 20%; }
        .w-22 { width: 22%; }
        .w-24 { width: 24%; }
        .w-25 { width: 25%; }
        .w-28 { width: 28%; }
        .signatures {
            margin-top: 48px;
            width: 100%;
            overflow: hidden;
        }
        .signature {
            display: inline-block;
            width: 30%;
            text-align: center;
            padding: 0 18px;
        }
        .line {
            border-top: 1px dotted #111827;
            margin-top: 42px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-block">
            <strong>جامعة {{ $college->university->name ?? '-' }}</strong><br>
            كلية {{ $college->name ?? '-' }}
        </div>
        <div class="header-block" style="text-align:center;">
            <strong>{{ $title }}</strong>
        </div>
        <div class="header-block" style="text-align:left;">
            التاريخ: {{ $generatedAt }}
        </div>
    </div>

    @php
        $period = $data['date_range']['label'] ?? 'كل الفترات';
    @endphp

    @if($type === 'subject')
        @php
            $subject = $data['subject'] ?? [];
            $summary = $data['summary'] ?? [];
            $report = $data['report'] ?? [];
            $maxAbsences = (int)($subject['max_absences'] ?? 4);
        @endphp
        <h1>كشف حضور وغياب الطلاب</h1>
        <div class="meta">
            المادة: {{ $subject['name'] ?? '-' }} |
            التخصص: {{ $subject['major']['name'] ?? '-' }} |
            المستوى: {{ $subject['level']['name'] ?? '-' }} |
            الدكتور: {{ $subject['doctor']['name'] ?? 'غير محدد' }} |
            حد الغياب: {{ $maxAbsences }} |
            الفترة: {{ $period }}
        </div>
        <div class="summary">
            <span>الطلاب: {{ $summary['students_count'] ?? count($report) }}</span>
            <span>إنذارات: {{ $summary['warning_count'] ?? 0 }}</span>
            <span>حرمان: {{ $summary['deprived_count'] ?? 0 }}</span>
            <span>متوسط الغياب: {{ $summary['average_absence_percentage'] ?? 0 }}%</span>
        </div>
        <div class="report-table">
            <div class="report-row report-head">
                <span class="report-cell w-5">#</span>
                <span class="report-cell w-12">الرقم الجامعي</span>
                <span class="report-cell w-25 text-right">اسم الطالب</span>
                <span class="report-cell w-8">حاضر</span>
                <span class="report-cell w-8">غائب</span>
                <span class="report-cell w-8">متأخر</span>
                <span class="report-cell w-8">أعذار</span>
                <span class="report-cell w-12">النسبة</span>
                <span class="report-cell w-14">الموقف</span>
            </div>
            @foreach($report as $row)
                @php
                    $decision = $row['decision'] ?? '-';
                    $class = $decision === 'محروم' ? 'danger' : ($decision === 'إنذار' ? 'warning' : 'ok');
                @endphp
                <div class="report-row">
                    <span class="report-cell w-5">{{ $loop->iteration }}</span>
                    <span class="report-cell w-12">{{ $row['student']['student_number'] ?? '-' }}</span>
                    <span class="report-cell w-25 text-right">{{ $row['student']['name'] ?? '-' }}</span>
                    <span class="report-cell w-8">{{ $row['present'] ?? 0 }}</span>
                    <span class="report-cell w-8">{{ $row['absent'] ?? 0 }}</span>
                    <span class="report-cell w-8">{{ $row['late'] ?? 0 }}</span>
                    <span class="report-cell w-8">{{ $row['excused'] ?? 0 }}</span>
                    <span class="report-cell w-12">{{ $row['absence_percentage'] ?? 0 }}%</span>
                    <span class="report-cell w-14 {{ $class }}">{{ $decision === '-' ? 'سليم' : $decision }}</span>
                </div>
            @endforeach
        </div>
    @elseif($type === 'threshold')
        @php
            $level = $data['level'] ?? [];
            $summary = $data['summary'] ?? [];
            $alerts = $data['alerts'] ?? [];
        @endphp
        <h1>كشف الطلاب المتجاوزين لنسبة الغياب</h1>
        <div class="meta">
            التخصص: {{ $level['major']['name'] ?? '-' }} |
            المستوى: {{ $level['name'] ?? '-' }} |
            العتبة: {{ $data['threshold'] ?? 0 }}% |
            الفترة: {{ $period }}
        </div>
        <div class="summary">
            <span>الحالات: {{ $summary['alerts_count'] ?? count($alerts) }}</span>
            <span>حرمان: {{ $summary['critical_count'] ?? 0 }}</span>
            <span>إنذارات: {{ $summary['warning_count'] ?? 0 }}</span>
            <span>أعلى نسبة: {{ $summary['highest_percentage'] ?? 0 }}%</span>
        </div>
        <div class="report-table">
            <div class="report-row report-head">
                <span class="report-cell w-5">#</span>
                <span class="report-cell w-25 text-right">اسم الطالب</span>
                <span class="report-cell w-25 text-right">المادة</span>
                <span class="report-cell w-10">الغيابات</span>
                <span class="report-cell w-10">الجلسات</span>
                <span class="report-cell w-12">النسبة</span>
                <span class="report-cell w-13">الإجراء</span>
            </div>
            @forelse($alerts as $row)
                @php
                    $percentage = (float)($row['absence_percentage'] ?? 0);
                    $action = $row['action_label'] ?? ($percentage >= 25 ? 'حرمان' : 'إنذار');
                @endphp
                <div class="report-row">
                    <span class="report-cell w-5">{{ $loop->iteration }}</span>
                    <span class="report-cell w-25 text-right">{{ $row['student']['name'] ?? '-' }}</span>
                    <span class="report-cell w-25 text-right">{{ $row['subject']['name'] ?? '-' }}</span>
                    <span class="report-cell w-10">{{ $row['absent_count'] ?? 0 }}</span>
                    <span class="report-cell w-10">{{ $row['total_sessions'] ?? 0 }}</span>
                    <span class="report-cell w-12 danger">{{ $percentage }}%</span>
                    <span class="report-cell w-13 {{ $action === 'حرمان' ? 'danger' : 'warning' }}">{{ $action }}</span>
                </div>
            @empty
                <div class="report-row">
                    <span class="report-cell" style="width:100%;">لا توجد حالات تجاوزت الحد المحدد.</span>
                </div>
            @endforelse
        </div>
    @elseif($type === 'level_summary')
        @php
            $level = $data['level'] ?? [];
            $summary = $data['summary'] ?? [];
            $stats = $data['subject_stats'] ?? [];
        @endphp
        <h1>ملخص الدفعة الدراسية</h1>
        <div class="meta">
            التخصص: {{ $level['major']['name'] ?? '-' }} |
            المستوى: {{ $level['name'] ?? '-' }} |
            المندوب: {{ $data['delegate']['name'] ?? 'غير معين' }} |
            الفترة: {{ $period }}
        </div>
        <div class="summary">
            <span>الطلاب: {{ $summary['students_count'] ?? count($data['students'] ?? []) }}</span>
            <span>المواد: {{ $summary['subjects_count'] ?? count($stats) }}</span>
            <span>متوسط الحضور: {{ $summary['average_attendance_rate'] ?? 0 }}%</span>
        </div>
        <div class="report-table">
            <div class="report-row report-head">
                <span class="report-cell w-5">#</span>
                <span class="report-cell w-35 text-right" style="width:35%;">المادة</span>
                <span class="report-cell w-25 text-right">الدكتور</span>
                <span class="report-cell w-18">سجلات الرصد</span>
                <span class="report-cell w-17" style="width:17%;">نسبة الحضور</span>
            </div>
            @foreach($stats as $row)
                <div class="report-row">
                    <span class="report-cell w-5">{{ $loop->iteration }}</span>
                    <span class="report-cell text-right" style="width:35%;">{{ $row['subject']['name'] ?? '-' }}</span>
                    <span class="report-cell w-25 text-right">{{ $row['subject']['doctor']['name'] ?? '-' }}</span>
                    <span class="report-cell w-18">{{ $row['total_records'] ?? 0 }}</span>
                    <span class="report-cell" style="width:17%;">{{ $row['attendance_rate'] ?? 0 }}%</span>
                </div>
            @endforeach
        </div>
    @else
        @php
            $summary = $data['summary'] ?? [];
            $doctors = $data['doctors'] ?? $data;
        @endphp
        <h1>أداء الكادر التعليمي</h1>
        <div class="meta">الفترة: {{ $period }}</div>
        <div class="summary">
            <span>الدكاترة: {{ $summary['doctors_count'] ?? count($doctors) }}</span>
            <span>نشطون: {{ $summary['active_doctors_count'] ?? 0 }}</span>
            <span>متوسط الحضور: {{ $summary['average_attendance_rate'] ?? 0 }}%</span>
        </div>
        <div class="report-table">
            <div class="report-row report-head">
                <span class="report-cell w-5">#</span>
                <span class="report-cell w-25 text-right">الدكتور</span>
                <span class="report-cell w-28">البريد</span>
                <span class="report-cell w-14">جلسات QR</span>
                <span class="report-cell w-10">المواد</span>
                <span class="report-cell w-18">متوسط الحضور</span>
            </div>
            @foreach($doctors as $doctor)
                <div class="report-row">
                    <span class="report-cell w-5">{{ $loop->iteration }}</span>
                    <span class="report-cell w-25 text-right">{{ $doctor['name'] ?? '-' }}</span>
                    <span class="report-cell w-28">{{ $doctor['email'] ?? '-' }}</span>
                    <span class="report-cell w-14">{{ $doctor['qr_sessions_count'] ?? 0 }}</span>
                    <span class="report-cell w-10">{{ $doctor['subjects_count'] ?? 0 }}</span>
                    <span class="report-cell w-18">{{ $doctor['attendance_rate'] ?? 0 }}%</span>
                </div>
            @endforeach
        </div>
    @endif

    <div class="signatures">
        <div class="signature">مسؤول شؤون الطلاب<div class="line"></div></div>
        <div class="signature">رئيس القسم<div class="line"></div></div>
        <div class="signature">عميد الكلية<div class="line"></div></div>
    </div>
</body>
</html>
