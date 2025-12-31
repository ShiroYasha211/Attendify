@extends('layouts.admin')

@section('title', 'إضافة دكتور جديد')

@section('content')
<div class="container" style="padding: 2rem;">
    <div style="margin-bottom: 2rem;">
        <h1>إضافة دكتور جديد</h1>
    </div>

    <div class="card" style="max-width: 600px; margin: 0 auto;">

        @if ($errors->any())
        <div style="color: #721c24; background-color: #f8d7da; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem;">
            <ul style="margin: 0; padding-right: 1.5rem;">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('admin.doctors.store') }}" method="POST">
            @csrf

            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">الاسم الكامل <span style="color: red">*</span></label>
                <input type="text" name="name" required value="{{ old('name') }}" style="width: 100%; padding: 0.5rem; border: 1px solid #ced4da; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">البريد الإلكتروني <span style="color: red">*</span></label>
                <input type="email" name="email" required value="{{ old('email') }}" style="width: 100%; padding: 0.5rem; border: 1px solid #ced4da; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">كلمة المرور <span style="color: red">*</span></label>
                <input type="password" name="password" required style="width: 100%; padding: 0.5rem; border: 1px solid #ced4da; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">الكلية التابع لها <span style="color: red">*</span></label>
                <select name="college_id" required style="width: 100%; padding: 0.5rem; border: 1px solid #ced4da; border-radius: 4px; background: white;">
                    <option value="">-- اختر الكلية --</option>
                    @foreach($colleges as $college)
                    <option value="{{ $college->id }}" {{ old('college_id') == $college->id ? 'selected' : '' }}>
                        {{ $college->name }} ({{ $college->university->name ?? '' }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" style="flex: 1; background: #007bff; color: white; border: none; padding: 0.75rem; border-radius: 4px; cursor: pointer; font-size: 1rem;">
                    حفظ الدكتور
                </button>
                <a href="{{ route('admin.doctors.index') }}" style="flex: 1; text-align: center; background: #e2e6ea; color: #495057; text-decoration: none; padding: 0.75rem; border-radius: 4px; font-size: 1rem;">
                    إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection