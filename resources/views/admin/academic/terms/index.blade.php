@extends('layouts.admin')

@section('title', 'إدارة الفصول الدراسية')

@section('content')

<div class="container" style="max-width: 100%;">

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">

        <!-- Create Form -->
        <div class="card">
            <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text-primary);">
                إضافة فصل دراسي
            </h3>

            <form action="{{ route('admin.terms.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name" class="form-label">اسم الفصل الدراسي</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="مثال: خريف 2025" required>
                </div>

                <div class="form-group">
                    <label for="start_date" class="form-label">تاريخ البداية (اختياري)</label>
                    <input type="date" name="start_date" id="start_date" class="form-control">
                </div>

                <div class="form-group">
                    <label for="end_date" class="form-label">تاريخ النهاية (اختياري)</label>
                    <input type="date" name="end_date" id="end_date" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    حفظ البيانات
                </button>
            </form>
        </div>

        <!-- List Table -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">الفصول الدراسية</h3>
                <span class="badge badge-info">{{ $terms->count() }} فصل</span>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الفصل الدراسي</th>
                            <th>تاريخ البداية</th>
                            <th>تاريخ النهاية</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($terms as $term)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td style="font-weight: 600;">{{ $term->name }}</td>
                            <td style="color: var(--text-secondary);">{{ $term->start_date ?? '-' }}</td>
                            <td style="color: var(--text-secondary);">{{ $term->end_date ?? '-' }}</td>
                            <td>
                                <!-- Logic for status could be added here later -->
                                <span class="badge badge-success">نشط</span>
                            </td>
                            <td>
                                <form action="{{ route('admin.terms.destroy', $term) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟');" style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                                        حذف
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                لا توجد فصول دراسية مضافة حتى الآن.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@endsection