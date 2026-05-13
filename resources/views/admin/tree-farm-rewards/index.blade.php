@extends('layouts.admin')

@section('title', 'طلبات مكافآت مزرعة الأشجار')

@section('content')
<div class="container-fluid py-4" dir="rtl">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">طلبات مكافآت مزرعة الأشجار</h1>
            <p class="text-muted mb-0">مراجعة تحويل عملات المزرعة إلى نجوم بعد اعتماد الإدارة.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-light border">العودة</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h2 class="h5 fw-bold mb-0">طلبات بانتظار المراجعة</h2>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الطالب</th>
                            <th>رقم القيد</th>
                            <th>العملات</th>
                            <th>النجوم</th>
                            <th>تاريخ الطلب</th>
                            <th style="min-width: 280px;">الإجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingRequests as $request)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $request->user?->name ?? 'طالب محذوف' }}</div>
                                    <div class="small text-muted">{{ $request->user?->email }}</div>
                                </td>
                                <td>{{ $request->user?->student_number ?? '-' }}</td>
                                <td><span class="badge bg-warning text-dark">{{ number_format($request->coins_amount) }}</span></td>
                                <td><span class="badge bg-success">{{ number_format($request->stars_amount) }}</span></td>
                                <td>{{ optional($request->created_at)->format('Y-m-d H:i') }}</td>
                                <td>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <form action="{{ route('admin.tree-farm-rewards.approve', $request) }}" method="POST" onsubmit="return confirm('اعتماد هذا الطلب وتحويل العملات إلى نجوم؟')">
                                            @csrf
                                            <button class="btn btn-sm btn-success" type="submit">اعتماد</button>
                                        </form>
                                        <form action="{{ route('admin.tree-farm-rewards.reject', $request) }}" method="POST" class="d-flex gap-2">
                                            @csrf
                                            <input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="سبب الرفض اختياري">
                                            <button class="btn btn-sm btn-outline-danger" type="submit">رفض</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">لا توجد طلبات مكافآت بانتظار المراجعة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($pendingRequests->hasPages())
            <div class="card-footer bg-white">
                {{ $pendingRequests->links() }}
            </div>
        @endif
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <h2 class="h5 fw-bold mb-0">آخر الطلبات التي تمت مراجعتها</h2>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الطالب</th>
                            <th>الحالة</th>
                            <th>العملات</th>
                            <th>النجوم</th>
                            <th>المراجع</th>
                            <th>ملاحظة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentRequests as $request)
                            <tr>
                                <td>{{ $request->user?->name ?? 'طالب محذوف' }}</td>
                                <td>
                                    @if($request->status === 'approved')
                                        <span class="badge bg-success">معتمد</span>
                                    @else
                                        <span class="badge bg-danger">مرفوض</span>
                                    @endif
                                </td>
                                <td>{{ number_format($request->coins_amount) }}</td>
                                <td>{{ number_format($request->stars_amount) }}</td>
                                <td>{{ $request->reviewer?->name ?? '-' }}</td>
                                <td>{{ $request->rejection_reason ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">لا توجد مراجعات سابقة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
