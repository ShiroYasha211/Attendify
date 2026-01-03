@extends('layouts.student')

@section('title', $subject->name)

@section('content')
<div class="container-fluid" style="max-width: 1000px;">

    <!-- Header -->
    <div class="mb-4">
        <a href="{{ route('student.dashboard') }}" class="text-decoration-none text-muted small mb-2 d-inline-block">
            &larr; العودة للرئيسية
        </a>
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1 class="h3 font-weight-bold text-dark mb-1">{{ $subject->name }}</h1>
                <p class="text-muted">{{ $subject->code }} &bull; د. {{ $subject->doctor->name ?? 'غير محدد' }}</p>
            </div>

            <div class="text-center px-4 py-2 bg-white rounded shadow-sm border">
                <div class="small text-muted mb-1">نسبة الحضور</div>
                @php
                $attendanceRate = 100 - $percentage;
                $colorClass = $attendanceRate >= 75 ? 'text-success' : 'text-danger';
                @endphp
                <div class="h4 font-weight-bold mb-0 {{ $colorClass }}" style="direction: ltr;">{{ $attendanceRate }}%</div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center p-3">
                    <div class="h3 font-weight-bold mb-0">{{ $total - $absent }}</div>
                    <div class="small opacity-75">حضور</div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body text-center p-3">
                    <div class="h3 font-weight-bold mb-0">{{ $absent }}</div>
                    <div class="small opacity-75">غياب</div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body text-center p-3">
                    <div class="h3 font-weight-bold mb-0 text-dark">{{ $total }}</div>
                    <div class="small text-muted">إجمالي المحاضرات</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance History Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="font-weight-bold mb-0 text-dark">سجل الحضور التفصيلي</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="vertical-align: middle;">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 text-muted small font-weight-bold">التاريخ</th>
                            <th class="px-4 py-3 text-muted small font-weight-bold">اليوم</th>
                            <th class="px-4 py-3 text-muted small font-weight-bold">الحالة</th>
                            <th class="px-4 py-3 text-muted small font-weight-bold">ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $record)
                        <tr>
                            <td class="px-4">{{ $record->created_at->format('Y-m-d') }}</td>
                            <td class="px-4">{{ $record->created_at->translatedFormat('l') }}</td>
                            <td class="px-4">
                                @if($record->status == 'present')
                                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">حاضر</span>
                                @elseif($record->status == 'absent')
                                <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill">غائب</span>
                                @elseif($record->status == 'late')
                                <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill">متأخر</span>
                                @elseif($record->status == 'excused')
                                <span class="badge bg-info-subtle text-info px-3 py-2 rounded-pill">بعذر</span>
                                @endif
                            </td>
                            <td class="px-4 text-muted small">
                                {{ $record->status == 'absent' ? 'تم تسجيل الغياب' : '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                لا توجد سجلات حضور مسجلة لهذه المادة حتى الآن.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<style>
    .bg-success-subtle {
        background-color: #d1fae5;
    }

    .bg-danger-subtle {
        background-color: #fee2e2;
    }

    .bg-warning-subtle {
        background-color: #fef3c7;
    }

    .bg-info-subtle {
        background-color: #e0f2fe;
    }
</style>
@endsection