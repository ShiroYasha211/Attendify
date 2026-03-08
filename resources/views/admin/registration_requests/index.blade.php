@extends('layouts.admin')

@section('title', 'طلبات إنشاء حساب')

@section('content')

<!-- Header Section -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary);">طلبات إنشاء حساب</h1>
        <p style="color: var(--text-secondary); margin-top: 0.5rem;">مراجعة واعتماد الحسابات الجديدة المعلقة</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-error">
    {{ session('error') }}
</div>
@endif

<!-- Pending Requests Table -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">الحسابات قيد المراجعة</h3>
        <span class="badge badge-warning" style="font-size: 0.85rem;">{{ $pendingRequests->total() }} طلبات</span>
    </div>

    <div class="table-container">
        <div class="table-responsive">
<table>
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>المستخدم</th>
                    <th>نوع الحساب</th>
                    <th>البيانات الأكاديمية</th>
                    <th>تاريخ الطلب</th>
                    <th style="width: 150px; text-align: center;">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingRequests as $request)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <div style="font-weight: 700; color: var(--text-primary);">{{ $request->name }}</div>
                        <div style="font-size: 0.85rem; color: var(--text-secondary);">{{ $request->email }}</div>
                    </td>
                    <td>
                        @if($request->role->value == 'doctor')
                            <span class="badge badge-primary" style="background:rgba(67, 56, 202, 0.1); color:var(--primary-color);">👨‍🏫 دكتور</span>
                        @elseif($request->role->value == 'delegate')
                            <span class="badge badge-info">⭐ مندوب</span>
                        @elseif($request->role->value == 'student')
                            <span class="badge" style="background:#f1f5f9; color:#64748b;">👨‍🎓 طالب</span>
                        @else
                            <span class="badge">{{ $request->role->value }}</span>
                        @endif
                    </td>
                    <td>
                        @if(in_array($request->role->value, ['student', 'delegate']))
                            <div style="font-size: 0.9rem; color: var(--text-primary);">
                                <strong>الرقم:</strong> {{ $request->student_number ?? 'غير محدد' }}<br>
                                <strong>الجهة:</strong> {{ $request->university->name ?? '-' }} - {{ $request->college->name ?? '-' }}<br>
                                <strong>التخصص:</strong> {{ $request->major->name ?? '-' }} ({{ $request->level->name ?? '-' }})
                            </div>
                        @else
                            <span style="color: var(--text-light); font-size: 0.85rem;">- غير مطلوبة -</span>
                        @endif
                    </td>
                    <td style="color: var(--text-secondary); font-size: 0.9rem;">
                        {{ $request->created_at->format('Y/m/d H:i') }}<br>
                        <span style="font-size: 0.8rem;">{{ $request->created_at->diffForHumans() }}</span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.5rem; justify-content: center;">
                            <!-- Approve Button -->
                            <form action="{{ route('admin.registration_requests.approve', $request->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من اعتماد وتفعيل هذا الحساب؟');">
                                @csrf
                                <button type="submit" class="btn" style="padding: 0.5rem 1rem; background: #d1fae5; color: #059669; font-size: 0.9rem; font-weight: bold;" title="اعتماد وتفعيل">
                                    ✓ اعتماد
                                </button>
                            </form>

                            <!-- Reject Button -->
                            <form action="{{ route('admin.registration_requests.reject', $request->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من رفض وحذف هذا الطلب مبدئياً؟');">
                                @csrf
                                <button type="submit" class="btn" style="padding: 0.5rem 1rem; background: #fee2e2; color: #dc2626; font-size: 0.9rem; font-weight: bold;" title="رفض وحذف">
                                    ✕ رفض
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🎉</div>
                        <strong>لا توجد طلبات معلقة!</strong><br>
                        تمت مراجعة جميع طلبات إنشاء الحساب.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
</div>
    </div>

    <!-- Pagination -->
    <div style="padding-top: 1.5rem;">
        {{ $pendingRequests->links('pagination::bootstrap-5') }}
    </div>
</div>

<style>
    /* Pagination Fixes */
    .pagination {
        display: flex;
        padding-left: 0;
        list-style: none;
        justify-content: center;
        gap: 0.25rem;
    }

    .page-link {
        position: relative;
        display: block;
        padding: 0.5rem 0.75rem;
        margin-left: -1px;
        line-height: 1.25;
        color: var(--text-primary);
        background-color: #fff;
        border: 1px solid var(--border-color);
        border-radius: 0.375rem;
        text-decoration: none;
        font-size: 0.875rem;
    }

    .page-item.active .page-link {
        z-index: 3;
        color: #fff;
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        cursor: auto;
        background-color: #fff;
        border-color: #dee2e6;
    }

    .page-link:hover {
        z-index: 2;
        color: var(--primary-hover);
        text-decoration: none;
        background-color: #e9ecef;
        border-color: #dee2e6;
    }

    .page-link svg {
        width: 1rem;
        height: 1rem;
    }

    .d-none.d-md-block {
        display: none !important;
    }
</style>
@endsection
