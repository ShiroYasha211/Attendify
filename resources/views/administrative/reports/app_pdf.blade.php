<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 40px 30px;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            direction: rtl;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.6;
            background-color: #ffffff;
        }
        
        /* Top Header Styling */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .header-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .header-right {
            width: 38%;
            text-align: right;
        }
        .header-center {
            width: 24%;
            text-align: center;
        }
        .header-left {
            width: 38%;
            text-align: left;
        }
        .header-logo-text {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
        }
        .header-sub-text {
            font-size: 11px;
            color: #64748b;
            margin-top: 1px;
        }

        /* Report Header Title */
        h1 {
            text-align: center;
            font-size: 17px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 15px 0;
        }

        /* Card Container & Styling */
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 14px;
            margin-bottom: 12px;
            background-color: #f8fafc;
        }
        .card-title {
            font-weight: bold;
            font-size: 10px;
            color: #475569;
            margin-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }
        .card-content {
            font-size: 10.5px;
            color: #334155;
        }
        
        /* Card Types styling */
        .card-blue {
            border-right: 4px solid #2563eb;
            background-color: #f0f9ff;
        }
        .card-emerald {
            border-right: 4px solid #059669;
            background-color: #f0fdf4;
        }
        .card-rose {
            border-right: 4px solid #e11d48;
            background-color: #fff1f2;
        }
        .card-teal {
            border-right: 4px solid #0d9488;
            background-color: #f0fdfa;
        }
        .card-violet {
            border-right: 4px solid #7c3aed;
            background-color: #faf5ff;
        }
        .card-amber {
            border-right: 4px solid #d97706;
            background-color: #fffbeb;
        }

        /* Premium Table Styles */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            border: 1px solid #cbd5e1;
        }
        .report-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            font-size: 10px;
            padding: 8px 6px;
            text-align: center;
            border: 1px solid #334155;
        }
        .report-table td {
            padding: 7px 5px;
            font-size: 9.5px;
            color: #334155;
            text-align: center;
            border: 1px solid #cbd5e1;
        }
        .report-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        /* Alignments */
        .text-right {
            text-align: right !important;
        }
        .font-bold {
            font-weight: bold;
        }

        /* Status Badges */
        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 4px;
            font-size: 8.5px;
            font-weight: bold;
            text-align: center;
        }
        .badge-success {
            background-color: #dcfce7;
            color: #166534;
        }
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .badge-info {
            background-color: #dbeafe;
            color: #1e40af;
        }

        /* Signature block */
        .signatures {
            margin-top: 40px;
            width: 100%;
            border-collapse: collapse;
        }
        .signatures td {
            border: none;
        }
        .signature-cell {
            width: 33.33%;
            text-align: center;
            padding: 0 15px;
            vertical-align: top;
        }
        .signature-title {
            font-weight: bold;
            color: #475569;
            font-size: 10.5px;
            margin-bottom: 40px;
        }
        .signature-line {
            border-top: 1px solid #94a3b8;
            width: 80%;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="header-right">
                <div class="header-logo-text">جامعة {{ $college->university->name ?? '-' }}</div>
                <div class="header-sub-text">كلية {{ $college->name ?? '-' }}</div>
            </td>
            <td class="header-center">
                <div style="font-size: 12px; font-weight: bold; color: #1e293b; background: #f1f5f9; padding: 6px 12px; border-radius: 6px; display: inline-block;">
                    {{ $title }}
                </div>
            </td>
            <td class="header-left">
                <div style="font-size: 9px; color: #64748b;">تاريخ الإصدار</div>
                <div style="font-size: 11px; font-weight: bold; color: #0f172a; margin-top: 2px;">{{ $generatedAt }}</div>
            </td>
        </tr>
    </table>
    <div style="height: 1px; background-color: #e2e8f0; margin-top: 10px; margin-bottom: 20px;"></div>

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
        
        <table style="width: 100%; border: none; margin-bottom: 15px; border-collapse: collapse;">
            <tr>
                <td style="width: 48%; border: none; padding: 0; vertical-align: top;">
                    <div class="card card-blue">
                        <div class="card-title">معلومات المساق والتعليم</div>
                        <div class="card-content" style="line-height: 1.8;">
                            <strong>المادة:</strong> {{ $subject['name'] ?? '-' }}<br>
                            <strong>الدكتور:</strong> {{ $subject['doctor']['name'] ?? 'غير حدد' }}<br>
                            <strong>الفترة الدراسية:</strong> {{ $period }}
                        </div>
                    </div>
                </td>
                <td style="width: 4%; border: none; padding: 0;"></td>
                <td style="width: 48%; border: none; padding: 0; vertical-align: top;">
                    <div class="card card-blue">
                        <div class="card-title">الشعبة والتخصص</div>
                        <div class="card-content" style="line-height: 1.8;">
                            <strong>التخصص:</strong> {{ $subject['major']['name'] ?? '-' }}<br>
                            <strong>المستوى:</strong> {{ $subject['level']['name'] ?? '-' }}<br>
                            <strong>حد الغياب المسموح:</strong> <span class="badge badge-danger" style="font-size: 10px; padding: 1px 6px;">{{ $maxAbsences }} محاضرات</span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="card card-emerald" style="margin-bottom: 20px;">
            <div class="card-title">الملخص التنفيذي للشعبة</div>
            <div class="card-content">
                <table style="width: 100%; border: none; border-collapse: collapse; text-align: center;">
                    <tr>
                        <td style="border: none; padding: 4px; width: 25%;">
                            <span style="font-size: 10px; color: #475569; display: block; margin-bottom: 2px;">إجمالي الطلاب</span>
                            <strong style="font-size: 14px; color: #0f172a;">{{ $summary['students_count'] ?? count($report) }}</strong>
                        </td>
                        <td style="border: none; padding: 4px; width: 25%; border-right: 1px solid #e2e8f0;">
                            <span style="font-size: 10px; color: #475569; display: block; margin-bottom: 2px;">الإنذارات الصادرة</span>
                            <strong style="font-size: 14px; color: #d97706;">{{ $summary['warning_count'] ?? 0 }}</strong>
                        </td>
                        <td style="border: none; padding: 4px; width: 25%; border-right: 1px solid #e2e8f0;">
                            <span style="font-size: 10px; color: #475569; display: block; margin-bottom: 2px;">حالات الحرمان</span>
                            <strong style="font-size: 14px; color: #e11d48;">{{ $summary['deprived_count'] ?? 0 }}</strong>
                        </td>
                        <td style="border: none; padding: 4px; width: 25%; border-right: 1px solid #e2e8f0;">
                            <span style="font-size: 10px; color: #475569; display: block; margin-bottom: 2px;">متوسط غياب الشعبة</span>
                            <strong style="font-size: 14px; color: #2563eb;">{{ $summary['average_absence_percentage'] ?? 0 }}%</strong>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 14%;">الرقم الجامعي</th>
                    <th class="text-right" style="width: 29%;">اسم الطالب</th>
                    <th style="width: 8%;">حاضر</th>
                    <th style="width: 8%;">غائب</th>
                    <th style="width: 8%;">متأخر</th>
                    <th style="width: 8%;">أعذار</th>
                    <th style="width: 10%;">النسبة</th>
                    <th style="width: 10%;">الموقف</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report as $row)
                    @php
                        $decision = $row['decision'] ?? '-';
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="font-bold">{{ $row['student']['student_number'] ?? '-' }}</td>
                        <td class="text-right font-bold" style="color: #0f172a;">{{ $row['student']['name'] ?? '-' }}</td>
                        <td>{{ $row['present'] ?? 0 }}</td>
                        <td>{{ $row['absent'] ?? 0 }}</td>
                        <td>{{ $row['late'] ?? 0 }}</td>
                        <td>{{ $row['excused'] ?? 0 }}</td>
                        <td class="font-bold">{{ $row['absence_percentage'] ?? 0 }}%</td>
                        <td>
                            @if($decision === 'محروم')
                                <span class="badge badge-danger">محروم</span>
                            @elseif($decision === 'إنذار')
                                <span class="badge badge-warning">إنذار</span>
                            @elseif($decision === '-')
                                <span class="badge badge-success">سليم</span>
                            @else
                                <span class="badge badge-info">{{ $decision }}</span>
                            @endif
                        </td>
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
        
        <table style="width: 100%; border: none; margin-bottom: 15px; border-collapse: collapse;">
            <tr>
                <td style="width: 48%; border: none; padding: 0; vertical-align: top;">
                    <div class="card card-rose">
                        <div class="card-title">معلومات التقرير</div>
                        <div class="card-content" style="line-height: 1.8;">
                            <strong>التخصص:</strong> {{ $level['major']['name'] ?? '-' }}<br>
                            <strong>المستوى:</strong> {{ $level['name'] ?? '-' }}
                        </div>
                    </div>
                </td>
                <td style="width: 4%; border: none; padding: 0;"></td>
                <td style="width: 48%; border: none; padding: 0; vertical-align: top;">
                    <div class="card card-rose">
                        <div class="card-title">الضوابط والفترة</div>
                        <div class="card-content" style="line-height: 1.8;">
                            <strong>عتبة الغياب المرصودة:</strong> <span class="badge badge-danger" style="font-size: 10px; padding: 1px 6px;">&ge; {{ $data['threshold'] ?? 0 }}%</span><br>
                            <strong>الفترة الدراسية:</strong> {{ $period }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="card card-amber" style="margin-bottom: 20px;">
            <div class="card-title">الملخص التنفيذي للإنذارات</div>
            <div class="card-content">
                <table style="width: 100%; border: none; border-collapse: collapse; text-align: center;">
                    <tr>
                        <td style="border: none; padding: 4px; width: 25%;">
                            <span style="font-size: 10px; color: #475569; display: block; margin-bottom: 2px;">إجمالي الحالات</span>
                            <strong style="font-size: 14px; color: #0f172a;">{{ $summary['alerts_count'] ?? count($alerts) }}</strong>
                        </td>
                        <td style="border: none; padding: 4px; width: 25%; border-right: 1px solid #e2e8f0;">
                            <span style="font-size: 10px; color: #475569; display: block; margin-bottom: 2px;">حالات الإنذار</span>
                            <strong style="font-size: 14px; color: #d97706;">{{ $summary['warning_count'] ?? 0 }}</strong>
                        </td>
                        <td style="border: none; padding: 4px; width: 25%; border-right: 1px solid #e2e8f0;">
                            <span style="font-size: 10px; color: #475569; display: block; margin-bottom: 2px;">حالات الحرمان الكلي</span>
                            <strong style="font-size: 14px; color: #e11d48;">{{ $summary['critical_count'] ?? 0 }}</strong>
                        </td>
                        <td style="border: none; padding: 4px; width: 25%; border-right: 1px solid #e2e8f0;">
                            <span style="font-size: 10px; color: #475569; display: block; margin-bottom: 2px;">أعلى نسبة غياب مسجلة</span>
                            <strong style="font-size: 14px; color: #b91c1c;">{{ $summary['highest_percentage'] ?? 0 }}%</strong>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th class="text-right" style="width: 25%;">اسم الطالب</th>
                    <th class="text-right" style="width: 25%;">المادة</th>
                    <th style="width: 11%;">الغيابات</th>
                    <th style="width: 11%;">الجلسات</th>
                    <th style="width: 11%;">النسبة</th>
                    <th style="width: 12%;">الإجراء</th>
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
                        <td class="text-right font-bold" style="color: #0f172a;">{{ $row['student']['name'] ?? '-' }}</td>
                        <td class="text-right">{{ $row['subject']['name'] ?? '-' }}</td>
                        <td>{{ $row['absent_count'] ?? 0 }}</td>
                        <td>{{ $row['total_sessions'] ?? 0 }}</td>
                        <td class="font-bold" style="color: #b91c1c;">{{ $percentage }}%</td>
                        <td>
                            @if($action === 'حرمان')
                                <span class="badge badge-danger">حرمان</span>
                            @else
                                <span class="badge badge-warning">إنذار</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding: 15px; color: #64748b;">لا توجد حالات تجاوزت الحد المحدد.</td>
                    </tr>
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
        
        <table style="width: 100%; border: none; margin-bottom: 15px; border-collapse: collapse;">
            <tr>
                <td style="width: 48%; border: none; padding: 0; vertical-align: top;">
                    <div class="card card-teal">
                        <div class="card-title">معلومات الدفعة</div>
                        <div class="card-content" style="line-height: 1.8;">
                            <strong>التخصص:</strong> {{ $level['major']['name'] ?? '-' }}<br>
                            <strong>المستوى:</strong> {{ $level['name'] ?? '-' }}
                        </div>
                    </div>
                </td>
                <td style="width: 4%; border: none; padding: 0;"></td>
                <td style="width: 48%; border: none; padding: 0; vertical-align: top;">
                    <div class="card card-teal">
                        <div class="card-title">المندوب والفترة</div>
                        <div class="card-content" style="line-height: 1.8;">
                            <strong>مندوب الدفعة:</strong> {{ $data['delegate']['name'] ?? 'غير معين' }}<br>
                            <strong>الفترة الدراسية:</strong> {{ $period }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="card card-emerald" style="margin-bottom: 20px;">
            <div class="card-title">البيانات الإجمالية للدفعة</div>
            <div class="card-content">
                <table style="width: 100%; border: none; border-collapse: collapse; text-align: center;">
                    <tr>
                        <td style="border: none; padding: 4px; width: 33.33%;">
                            <span style="font-size: 10px; color: #475569; display: block; margin-bottom: 2px;">إجمالي الطلاب المقيدين</span>
                            <strong style="font-size: 14px; color: #0f172a;">{{ $summary['students_count'] ?? count($data['students'] ?? []) }}</strong>
                        </td>
                        <td style="border: none; padding: 4px; width: 33.33%; border-right: 1px solid #e2e8f0;">
                            <span style="font-size: 10px; color: #475569; display: block; margin-bottom: 2px;">عدد المساقات النشطة</span>
                            <strong style="font-size: 14px; color: #0d9488;">{{ $summary['subjects_count'] ?? count($stats) }}</strong>
                        </td>
                        <td style="border: none; padding: 4px; width: 33.33%; border-right: 1px solid #e2e8f0;">
                            <span style="font-size: 10px; color: #475569; display: block; margin-bottom: 2px;">متوسط حضور الدفعة العام</span>
                            <strong style="font-size: 14px; color: #2563eb;">{{ $summary['average_attendance_rate'] ?? 0 }}%</strong>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th class="text-right" style="width: 35%;">المادة</th>
                    <th class="text-right" style="width: 30%;">الدكتور</th>
                    <th style="width: 15%;">سجلات الرصد</th>
                    <th style="width: 15%;">نسبة الحضور</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="text-right font-bold" style="color: #0f172a;">{{ $row['subject']['name'] ?? '-' }}</td>
                        <td class="text-right">{{ $row['subject']['doctor']['name'] ?? '-' }}</td>
                        <td>{{ $row['total_records'] ?? 0 }}</td>
                        <td class="font-bold" style="color: #2563eb;">{{ $row['attendance_rate'] ?? 0 }}%</td>
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
        
        <table style="width: 100%; border: none; margin-bottom: 15px; border-collapse: collapse;">
            <tr>
                <td style="width: 48%; border: none; padding: 0; vertical-align: top;">
                    <div class="card card-violet">
                        <div class="card-title">معلومات التقرير</div>
                        <div class="card-content" style="line-height: 1.8;">
                            <strong>نوع التقرير:</strong> تقرير أداء الكادر التعليمي العام<br>
                            <strong>الفترة الدراسية:</strong> {{ $period }}
                        </div>
                    </div>
                </td>
                <td style="width: 4%; border: none; padding: 0;"></td>
                <td style="width: 48%; border: none; padding: 0; vertical-align: top;">
                    <div class="card card-violet">
                        <div class="card-title">الهيئة المصدرة للتقرير</div>
                        <div class="card-content" style="line-height: 1.8;">
                            <strong>المصدر:</strong> عمادة شؤون الطلاب والتعليم<br>
                            <strong>الكلية:</strong> كلية {{ $college->name ?? '-' }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="card card-emerald" style="margin-bottom: 20px;">
            <div class="card-title">ملخص أداء الكادر</div>
            <div class="card-content">
                <table style="width: 100%; border: none; border-collapse: collapse; text-align: center;">
                    <tr>
                        <td style="border: none; padding: 4px; width: 33.33%;">
                            <span style="font-size: 10px; color: #475569; display: block; margin-bottom: 2px;">إجمالي الكادر المقيد</span>
                            <strong style="font-size: 14px; color: #0f172a;">{{ $summary['doctors_count'] ?? count($doctors) }}</strong>
                        </td>
                        <td style="border: none; padding: 4px; width: 33.33%; border-right: 1px solid #e2e8f0;">
                            <span style="font-size: 10px; color: #475569; display: block; margin-bottom: 2px;">الأعضاء النشطين</span>
                            <strong style="font-size: 14px; color: #7c3aed;">{{ $summary['active_doctors_count'] ?? 0 }}</strong>
                        </td>
                        <td style="border: none; padding: 4px; width: 33.33%; border-right: 1px solid #e2e8f0;">
                            <span style="font-size: 10px; color: #475569; display: block; margin-bottom: 2px;">متوسط حضور الطلاب العام</span>
                            <strong style="font-size: 14px; color: #2563eb;">{{ $summary['average_attendance_rate'] ?? 0 }}%</strong>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th class="text-right" style="width: 25%;">الدكتور</th>
                    <th class="text-right" style="width: 30%;">البريد الإلكتروني</th>
                    <th style="width: 14%;">جلسات QR</th>
                    <th style="width: 11%;">المساقات</th>
                    <th style="width: 15%;">متوسط الحضور</th>
                </tr>
            </thead>
            <tbody>
                @foreach($doctors as $doctor)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="text-right font-bold" style="color: #0f172a;">{{ $doctor['name'] ?? '-' }}</td>
                        <td class="text-right" style="color: #475569;">{{ $doctor['email'] ?? '-' }}</td>
                        <td>{{ $doctor['qr_sessions_count'] ?? 0 }}</td>
                        <td>{{ $doctor['subjects_count'] ?? 0 }}</td>
                        <td class="font-bold" style="color: #2563eb;">{{ $doctor['attendance_rate'] ?? 0 }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <table class="signatures">
        <tr>
            <td class="signature-cell">
                <div class="signature-title">مسؤول شؤون الطلاب</div>
                <div class="signature-line"></div>
                <div style="font-size: 9px; color: #94a3b8; margin-top: 6px;">التوقيع / الختم</div>
            </td>
            <td class="signature-cell">
                <div class="signature-title">رئيس القسم المصدّر</div>
                <div class="signature-line"></div>
                <div style="font-size: 9px; color: #94a3b8; margin-top: 6px;">التوقيع / الختم</div>
            </td>
            <td class="signature-cell">
                <div class="signature-title">عميد الكلية المصدّقة</div>
                <div class="signature-line"></div>
                <div style="font-size: 9px; color: #94a3b8; margin-top: 6px;">التوقيع / الختم</div>
            </td>
        </tr>
    </table>
</body>
</html>
