@extends('layouts.doctor')

@section('title', 'أعذار الغياب')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="h3 fw-bold text-gray-800">أعذار الغياب المعلقة</h2>
        <p class="text-gray-500">قائمة بالأعذار المقدمة من الطلاب والتي تحتاج إلى مراجعة.</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success mb-4">
    {{ session('success') }}
</div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>الطالب</th>
                    <th>المقرر</th>
                    <th>تاريخ الغياب</th>
                    <th>سبب العذر</th>
                    <th>المرفق</th>
                    <th>تاريخ التقديم</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($excuses as $excuse)
                <tr>
                    <td class="fw-bold">{{ $excuse->student->name }}</td>
                    <td>{{ $excuse->attendance->subject->name }}</td>
                    <td>{{ $excuse->attendance->date }}</td>
                    <td>{{ Str::limit($excuse->reason, 50) }}</td>
                    <td>
                        @if($excuse->attachment)
                        <a href="{{ asset('storage/' . $excuse->attachment) }}" target="_blank" class="btn btn-sm btn-secondary">
                            عرض المرفق
                        </a>
                        @else
                        -
                        @endif
                    </td>
                    <td>{{ $excuse->created_at->diffForHumans() }}</td>
                    <td>
                        <div style="display: flex; gap: 0.5rem;">
                            <form action="{{ route('doctor.excuses.update', $excuse->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="accepted">
                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('هل أنت متأكد من قبول العذر؟ سيتم تحويل حالة الغياب إلى (بعذر).')">
                                    قبول
                                </button>
                            </form>

                            <form action="{{ route('doctor.excuses.update', $excuse->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من رفض العذر؟')">
                                    رفض
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <p>لا توجد أعذار معلقة للمراجعة حالياً.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $excuses->links() }}
    </div>
</div>
@endsection