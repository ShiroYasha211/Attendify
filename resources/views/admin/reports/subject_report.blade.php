@extends('layouts.admin')

@section('title', 'تقرير حضور: ' . $subject->name)

@section('content')

<style>
    @media print {
        @page {
            size: A4;
            margin: 0.5cm;
        }

        body {
            background: white;
            font-family: 'Tajawal', sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Hide Admin UI Elements explicitly */
        .sidebar,
        .top-header,
        .btn,
        footer,
        .no-print,
        .mobile-toggle,
        .desktop-toggle,
        .user-menu {
            display: none !important;
        }

        .main-content {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            background: white !important;
        }

        .container {
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
        }

        .card {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
        }

        /* Official Report Styling */
        .report-container {
            border: 2px solid #000;
            padding: 1rem;
            min-height: 28cm;
            /* Approximate A4 height minus margins */
            position: relative;
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }

        .header-right,
        .header-left {
            text-align: center;
            width: 30%;
        }

        .header-right h3,
        .header-left h3 {
            font-size: 1rem;
            margin: 2px 0;
            font-weight: bold;
        }

        .header-center {
            text-align: center;
            width: 30%;
        }

        .university-logo-placeholder {
            width: 80px;
            height: 80px;
            border: 1px dashed #999;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #f9f9f9;
        }

        .report-title {
            text-align: center;
            margin: 1.5rem 0;
            font-size: 1.4rem;
            font-weight: 900;
            text-decoration: underline;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
            border: 1px solid #000;
            padding: 0.5rem;
            background: #f8f9fa;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
            font-size: 0.9rem;
        }

        .meta-label {
            font-weight: bold;
            color: #555;
        }

        table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 10pt;
            margin-bottom: 2rem;
        }

        th,
        td {
            border: 1px solid #000 !important;
            padding: 6px !important;
            text-align: center;
        }

        th {
            background-color: #e5e7eb !important;
            font-weight: bold;
        }

        .progress-bg {
            background-color: #ddd !important;
            border: 1px solid #999;
        }

        .report-footer {
            margin-top: 3rem;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        .footer-sign {
            text-align: center;
            width: 30%;
        }

        .footer-sign p {
            margin-bottom: 3rem;
            font-weight: bold;
        }
    }
</style>

<div class="container" style="max-width: 100%; padding: 2rem;">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;" class="no-print">
        <h1 class="page-title" style="font-size: 1.5rem; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
            </svg>
            كشف متابعة الحضور
        </h1>
        <div style="display: flex; gap: 0.5rem;">
            <button onclick="window.print()" class="btn btn-primary" style="display: flex; align-items: center; gap: 0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                طباعة التقرير
            </button>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">عودة للمركز</a>
        </div>
    </div>

    <div class="card" style="padding: 2rem;">

        <!-- Official Report Container for Print -->
        <div class="report-container">

            <!-- Header -->
            <div class="report-header">
                <div class="header-right">
                    <h3>{{ $subject->major->college->university->name ?? 'اسم الجامعة' }}</h3>
                    <h3>كلية {{ $subject->major->college->name ?? 'اسم الكلية' }}</h3>
                    <h3>قسم {{ $subject->major->name ?? 'اسم القسم' }}</h3>
                </div>

                <div class="header-center">
                    <div class="university-logo-placeholder" style="border: none; background: transparent;">
                        @if($subject->major->college->university->logo)
                        <img src="{{ asset('storage/' . $subject->major->college->university->logo) }}" alt="University Logo" style="width: 100%; height: 100%; object-fit: contain;">
                        @else
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 10v6M2 10v6M12 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"></path>
                            <path d="M6 15v-2a6 6 0 1 1 12 0v2"></path>
                            <path d="M2 10s2 6 10 6 10-6 10-6"></path>
                        </svg>
                        @endif
                    </div>
                </div>

                <div class="header-left">
                    <h3>التاريخ: {{ now()->format('Y/m/d') }}</h3>
                    <h3>الفصل الدراسي: {{ $subject->term->name ?? '-' }}</h3>
                    <h3>العام الجامعي: {{ date('Y') }}</h3>
                </div>
            </div>

            <div class="report-title">
                كشف حضور وانتظام الطلاب
            </div>

            <!-- Meta Data Grid -->
            <div class="meta-grid">
                <div class="meta-item">
                    <span class="meta-label">المقرر الدراسي:</span>
                    <span>{{ $subject->name }} ({{ $subject->code }})</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">المرحلة الدراسية:</span>
                    <span>{{ $subject->level->name ?? '-' }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">أستاذ المقرر:</span>
                    <span>{{ $subject->doctor->name ?? 'غير محدد' }}</span>
                </div>
            </div>

            <!-- Detailed Table -->
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="text-align: right;">اسم الطالب</th>
                        <th style="width: 8%;">حضور</th>
                        <th style="width: 8%;">تأخير</th>
                        <th style="width: 8%;">غياب</th>
                        <th style="width: 8%;">بعذر</th>
                        <th style="width: 25%;">نسبة الغياب</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $data)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td style="text-align: right; font-weight: bold;">{{ $data['student']->name }}</td>
                        <td style="color: green;">{{ $data['present'] }}</td>
                        <td style="color: orange;">{{ $data['late'] }}</td>
                        <td style="color: red; font-weight: bold;">{{ $data['absent'] }}</td>
                        <td>{{ $data['excused'] }}</td>
                        <td>
                            @php
                            $percentage = min($data['absence_percentage'], 100);
                            $color = $percentage > 20 ? '#ef4444' : ($percentage > 10 ? '#f59e0b' : '#22c55e');
                            @endphp
                            <div style="display: flex; align-items: center; gap: 0.5rem; justify-content: center;">
                                <div style="flex-grow: 1; height: 10px; background: #eee; border: 1px solid #ccc; border-radius: 2px; overflow: hidden;" class="progress-bg">
                                    <div style="height: 100%; width: {{ $percentage }}%; background-color: {{ $color }} !important; -webkit-print-color-adjust: exact;"></div>
                                </div>
                                <span style="font-size: 0.85rem; font-weight: 600; width: 40px;">{{ $data['absence_percentage'] }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="padding: 2rem;">لا يوجد طلاب مسجلين في هذه المادة.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Footer Signatures -->
            <div class="report-footer">
                <div class="footer-sign">
                    <p>أستاذ المقرر</p>
                    <span>.................................</span>
                </div>
                <div class="footer-sign">
                    <p>رئيس القسم</p>
                    <span>.................................</span>
                </div>
                <div class="footer-sign">
                    <p>يعتمد، عميد الكلية</p>
                    <span>.................................</span>
                </div>
            </div>

        </div> <!-- End Report Container -->

    </div>

</div>

@endsection