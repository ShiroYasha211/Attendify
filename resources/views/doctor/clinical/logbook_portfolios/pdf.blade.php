<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; direction: rtl; color: #111827; font-size: 12px; }
        h1, h2 { margin: 0 0 10px; }
        .muted { color: #6b7280; }
        .summary { display: table; width: 100%; margin: 18px 0; }
        .summary div { display: table-cell; border: 1px solid #e5e7eb; padding: 10px; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { background: #f3f4f6; font-weight: bold; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: right; }
        .center { text-align: center; }
    </style>
</head>
<body>
@php
    $student = $portfolio['student'];
    $summary = $portfolio['summary'];
@endphp
<h1>تقرير إنجازات الطالب العملية</h1>
<div class="muted">{{ $student['name'] }} | {{ $student['student_number'] ?: '-' }} | {{ data_get($student, 'major.name', '-') }} | {{ data_get($student, 'level.name', '-') }}</div>

<div class="summary">
    <div><strong>{{ $summary['approved_activities'] }}</strong><br>إجمالي الأنشطة</div>
    <div><strong>{{ $summary['history_taking'] }}</strong><br>قصص مرضية</div>
    <div><strong>{{ $summary['clinical_examination'] }}</strong><br>فحوصات سريرية</div>
    <div><strong>{{ $summary['round'] }}</strong><br>مرور</div>
</div>

<h2>الجدول التراكمي</h2>
<table>
    <thead>
        <tr>
            <th>نظام الجسم / المهارة</th>
            <th class="center">قصص مرضية</th>
            <th class="center">فحوصات سريرية</th>
            <th class="center">مرور</th>
            <th class="center">الإجمالي</th>
        </tr>
    </thead>
    <tbody>
        @foreach($portfolio['matrix'] as $row)
            <tr>
                <td>{{ $row['body_system'] }}</td>
                <td class="center">{{ $row['history_taking'] }}</td>
                <td class="center">{{ $row['clinical_examination'] }}</td>
                <td class="center">{{ $row['round'] }}</td>
                <td class="center">{{ $row['total'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<h2 style="margin-top: 22px;">تفاصيل الجلسات</h2>
<table>
    <thead>
        <tr>
            <th>التاريخ</th>
            <th>المركز</th>
            <th>القسم</th>
            <th>النشاط</th>
            <th>النظام/الحالة</th>
        </tr>
    </thead>
    <tbody>
        @foreach($portfolio['logs'] as $log)
            @foreach($log['activities'] as $activity)
                <tr>
                    <td>{{ $log['date'] }}</td>
                    <td>{{ $log['training_center'] ?: '-' }}</td>
                    <td>{{ $log['department'] ?: '-' }}</td>
                    <td>{{ $activity['type_label'] }}</td>
                    <td>{{ $activity['body_system'] }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
</body>
</html>
