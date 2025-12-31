@extends('layouts.admin')

@section('title', 'إدارة المستويات الدراسية')

@section('content')

<div class="container" style="max-width: 100%;">

    <!-- Success Message -->
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <!-- Error Messages (Important for debugging) -->
    @if($errors->any())
    <div class="alert alert-error">
        <ul style="margin: 0; padding-right: 1rem;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">

        <!-- Create Form -->
        <div class="card">
            <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text-primary);">
                إضافة مستوى جديد
            </h3>

            <form action="{{ route('admin.levels.store') }}" method="POST">
                @csrf

                {{-- Major Select (MANDATORY) --}}
                <div class="form-group">
                    <label for="major_id" class="form-label">التخصص التابع له <span style="color: red">*</span></label>
                    <select name="major_id" id="major_id" class="form-control" required>
                        <option value="">-- اختر التخصص --</option>
                        @foreach($colleges as $college)
                        <optgroup label="{{ $college->name }}">
                            @foreach($college->majors as $major)
                            <option value="{{ $major->id }}">
                                {{ $major->name }}
                            </option>
                            @endforeach
                        </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="name" class="form-label">اسم المستوى <span style="color: red">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="مثال: المستوى الأول" required>
                </div>

                <!-- Removed numeric_value since it's not in DB -->

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    حفظ البيانات
                </button>
            </form>
        </div>

        <!-- List Table -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">المستويات الدراسية</h3>
                <span class="badge badge-info">{{ $levels->count() }} مستوى</span>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المستوى</th>
                            <th>التخصص</th>
                            <th>الكلية</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($levels as $level)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td style="font-weight: 600;">{{ $level->name }}</td>
                            <td>
                                <span class="badge badge-warning">{{ $level->major->name }}</span>
                            </td>
                            <td style="color: var(--text-secondary); font-size: 0.9rem;">
                                {{ $level->major->college->name ?? '-' }}
                            </td>
                            <td>
                                <form action="{{ route('admin.levels.destroy', $level) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟');" style="display: inline-block;">
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
                            <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                لا توجد مستويات مضافة حتى الآن.
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