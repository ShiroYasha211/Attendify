@extends('layouts.admin')

@section('title', 'نتائج الكويز الإداري')

@section('content')
<style>
    .results-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
    }

    .table th {
        background: #f8fafc;
        font-weight: 700;
        font-size: 0.85rem;
        color: #475569;
        text-transform: uppercase;
        padding: 1rem;
    }

    .table td {
        padding: 1rem;
        vertical-align: middle;
        font-size: 0.9rem;
        color: #1e293b;
    }

    .avatar-sm {
        width: 32px;
        height: 32px;
        background: #e2e8f0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.8rem;
        color: #475569;
    }

    .score-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 99px;
        font-weight: 800;
        font-size: 0.8rem;
    }

    .status-badge {
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">نتائج: {{ $quiz->title }}</h2>
        <div class="text-muted small">إجمالي المحاولات: {{ $attempts->count() }}</div>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="{{ route('admin.quizzes.show', $quiz) }}" class="btn btn-outline-secondary" style="border-radius: 12px;">
            <i class="fa-solid fa-arrow-right me-1"></i> رجوع للتفاصيل
        </a>
    </div>
</div>

<div class="results-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>الطالب</th>
                    <th>النموذج</th>
                    <th>وقت البدء</th>
                    <th>المدة المستغرقة</th>
                    <th>الحالة</th>
                    <th>الدرجة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attempts as $attempt)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-sm">{{ mb_substr($attempt->student->name, 0, 1) }}</div>
                            <div>
                                <div class="fw-bold">{{ $attempt->student->name }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $attempt->student->major->name ?? '—' }} ({{ $attempt->student->level->name ?? '—' }})</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-light text-dark fw-bold">النموذج {{ $attempt->quizModel->name }}</span></td>
                    <td class="small">{{ $attempt->started_at->format('Y/m/d H:i') }}</td>
                    <td class="small">
                        @if($attempt->completed_at)
                            {{ $attempt->started_at->diffInMinutes($attempt->completed_at) }} دقيقة
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if($attempt->status === 'graded')
                            <span class="status-badge bg-success-subtle text-success">مكتمل</span>
                        @elseif($attempt->status === 'in_progress')
                            <span class="status-badge bg-warning-subtle text-warning">قيد الحل</span>
                        @else
                            <span class="status-badge bg-secondary-subtle text-secondary">{{ $attempt->status }}</span>
                        @endif
                    </td>
                    <td>
                        @if($attempt->status === 'graded')
                            <span class="score-badge {{ $attempt->percentage >= 50 ? 'bg-success text-white' : 'bg-danger text-white' }}">
                                {{ round($attempt->score, 1) }} / {{ round($attempt->total_score, 1) }} ({{ round($attempt->percentage, 1) }}%)
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        {{-- Future: View detailed answers --}}
                        <button class="btn btn-sm btn-light" title="عرض الإجابات" disabled><i class="fa-solid fa-eye"></i></button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">لا توجد محاولات لهذا الكويز حتى الآن.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
