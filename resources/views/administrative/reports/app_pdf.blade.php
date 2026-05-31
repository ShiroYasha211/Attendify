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
            display: table;
            width: 100%;
        }
        .header > div {
            display: table-cell;
            vertical-align: top;
            width: 33.33%;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 7px;
            text-align: center;
        }
        th {
            background: #f1f5f9;
            font-weight: 700;
        }
        .text-right { text-align: right; }
        .danger { color: #b91c1c; font-weight: 700; }
        .warning { color: #b45309; font-weight: 700; }
        .ok { color: #047857; font-weight: 700; }
        .signatures {
            margin-top: 48px;
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .signatures div {
            display: table-cell;
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
        <div>
            <strong>جامعة {{ $college->university->name ?? '-' }}</strong><br>
            كلية {{ $college->name ?? '-' }}
        </div>
        <div style="text-align:center;">
            <strong>{{ $title }}</strong>
        </div>
        <div style="text-align:left;">
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
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>الرقم الجامعي</th>
                    <th class="text-right">اسم الطالب</th>
                    <th>حاضر</th>
                    <th>غائب</th>
                    <th>متأخر</th>
                    <th>أعذار</th>
                    <th>النسبة</th>
                    <th>الموقف</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report as $row)
                    @php
                        $decision = $row['decision'] ?? '-';
                        $class = $decision === 'محروم' ? 'danger' : ($decision === 'إنذار' ? 'warning' : 'ok');
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $row['student']['student_number'] ?? '-' }}</td>
                        <td class="text-right">{{ $row['student']['name'] ?? '-' }}</td>
                        <td>{{ $row['present'] ?? 0 }}</td>
                        <td>{{ $row['absent'] ?? 0 }}</td>
                        <td>{{ $row['late'] ?? 0 }}</td>
                        <td>{{ $row['excused'] ?? 0 }}</td>
                        <td>{{ $row['absence_percentage'] ?? 0 }}%</td>
                        <td class="{{ $class }}">{{ $decision === '-' ? 'سليم' : $decision }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
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
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th class="text-right">اسم الطالب</th>
                    <th class="text-right">المادة</th>
                    <th>الغيابات</th>
                    <th>الجلسات</th>
                    <th>النسبة</th>
                    <th>الإجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alerts as $row)
                    @php
                        $percentage = (float)($row['absence_percentage'] ?? 0);
                        $action = $row['action_label'] ?? ($percentage >= 25 ? 'حرمان' : 'إنذار');
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="text-right">{{ $row['student']['name'] ?? '-' }}</td>
                        <td class="text-right">{{ $row['subject']['name'] ?? '-' }}</td>
                        <td>{{ $row['absent_count'] ?? 0 }}</td>
                        <td>{{ $row['total_sessions'] ?? 0 }}</td>
                        <td class="danger">{{ $percentage }}%</td>
                        <td class="{{ $action === 'حرمان' ? 'danger' : 'warning' }}">{{ $action }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7">لا توجد حالات تجاوزت الحد المحدد.</td></tr>
                @endforelse
            </tbody>
        </table>
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
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th class="text-right">المادة</th>
                    <th class="text-right">الدكتور</th>
                    <th>سجلات الرصد</th>
                    <th>نسبة الحضور</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="text-right">{{ $row['subject']['name'] ?? '-' }}</td>
                        <td class="text-right">{{ $row['subject']['doctor']['name'] ?? '-' }}</td>
                        <td>{{ $row['total_records'] ?? 0 }}</td>
                        <td>{{ $row['attendance_rate'] ?? 0 }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
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
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th class="text-right">الدكتور</th>
                    <th>البريد</th>
                    <th>جلسات QR</th>
                    <th>المواد</th>
                    <th>متوسط الحضور</th>
                </tr>
            </thead>
            <tbody>
                @foreach($doctors as $doctor)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="text-right">{{ $doctor['name'] ?? '-' }}</td>
                        <td>{{ $doctor['email'] ?? '-' }}</td>
                        <td>{{ $doctor['qr_sessions_count'] ?? 0 }}</td>
                        <td>{{ $doctor['subjects_count'] ?? 0 }}</td>
                        <td>{{ $doctor['attendance_rate'] ?? 0 }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="signatures">
        <div>مسؤول شؤون الطلاب<div class="line"></div></div>
        <div>رئيس القسم<div class="line"></div></div>
        <div>عميد الكلية<div class="line"></div></div>
    </div>
</body>
</html>
