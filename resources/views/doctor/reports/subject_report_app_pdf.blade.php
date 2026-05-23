<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            direction: rtl;
            color: #0f172a;
            font-size: 12px;
            line-height: 1.65;
        }
        .header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 14px;
            margin-bottom: 18px;
            text-align: center;
        }
        .title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .subtitle {
            color: #475569;
        }
        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .meta th,
        .meta td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            text-align: right;
        }
        .meta th {
            background: #f1f5f9;
            font-weight: 700;
            width: 18%;
        }
        .students {
            width: 100%;
            border-collapse: collapse;
        }
        .students th,
        .students td {
            border: 1px solid #cbd5e1;
            padding: 7px;
            text-align: center;
        }
        .students th {
            background: #0f766e;
            color: white;
            font-weight: 700;
        }
        .students td.name {
            text-align: right;
        }
        .footer {
            margin-top: 22px;
            color: #64748b;
            font-size: 10px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">تقرير الحضور والغياب</div>
        <div class="subtitle">
            {{ $subject->major?->college?->university?->name ?? '-' }} -
            كلية {{ $subject->major?->college?->name ?? '-' }}
        </div>
    </div>

    <table class="meta">
        <tr>
            <th>المقرر</th>
            <td>{{ $subject->name }}</td>
            <th>التخصص</th>
            <td>{{ $subject->major?->name ?? '-' }}</td>
        </tr>
        <tr>
            <th>المستوى</th>
            <td>{{ $subject->level?->name ?? '-' }}</td>
            <th>الفصل</th>
            <td>{{ $subject->term?->name ?? '-' }}</td>
        </tr>
        <tr>
            <th>تاريخ التقرير</th>
            <td>{{ $generatedAt }}</td>
            <th>عدد الطلاب</th>
            <td>{{ $students->count() }}</td>
        </tr>
    </table>

    <table class="students">
        <thead>
            <tr>
                <th>#</th>
                <th>اسم الطالب</th>
                <th>رقم القيد</th>
                <th>الإجمالي</th>
                <th>حضور</th>
                <th>غياب</th>
                <th>بعذر</th>
                <th>النسبة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="name">{{ $student['name'] }}</td>
                    <td>{{ $student['student_number'] ?? '-' }}</td>
                    <td>{{ $student['total'] }}</td>
                    <td>{{ $student['present'] }}</td>
                    <td>{{ $student['absent'] }}</td>
                    <td>{{ $student['excused'] }}</td>
                    <td>{{ $student['attendance_rate'] }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">لا توجد بيانات طلاب لهذا التقرير.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">تم إنشاء التقرير من تطبيق معين للكادر التعليمي.</div>
</body>
</html>
