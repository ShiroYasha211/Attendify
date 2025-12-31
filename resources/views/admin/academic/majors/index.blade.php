@extends('layouts.admin')

@section('title', 'إدارة التخصصات')

@section('content')

<div class="container" style="max-width: 100%;" x-data="{ 
    showDeleteModal: false, 
    showEditModal: false,
    deleteUrl: '', 
    modalTitle: '', 
    modalMessage: '',
    editUrl: '',
    editName: '',
    editCollegeId: ''
}">

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">

        <!-- Create Form -->
        <div class="card">
            <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text-primary);">
                إضافة تخصص جديد
            </h3>

            <form action="{{ route('admin.majors.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="college_id" class="form-label">الكلية التابعة لها</label>
                    <select name="college_id" id="college_id" class="form-control" required>
                        <option value="">اختر الكلية...</option>
                        @foreach($universities as $university)
                        <optgroup label="{{ $university->name }}">
                            @foreach($university->colleges as $college)
                            <option value="{{ $college->id }}">{{ $college->name }}</option>
                            @endforeach
                        </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="name" class="form-label">اسم التخصص</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="مثال: هندسة البرمجيات" required>
                </div>

                <!-- Automation Fields -->
                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                    <h4 style="font-size: 1rem; margin-bottom: 1rem; color: var(--text-primary);">الإعداد التلقائي للهيكل</h4>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="levels_count" class="form-label">عدد المستويات (سنوات)</label>
                            <input type="number" name="levels_count" id="levels_count" class="form-control" value="4" min="1" max="7" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="terms_count" class="form-label">الأترام لكل مستوى</label>
                            <input type="number" name="terms_count" id="terms_count" class="form-control" value="2" min="1" max="4" required>
                        </div>
                    </div>
                    <small style="color: var(--text-secondary); font-size: 0.8rem; display: block; margin-top: 0.5rem;">
                        سيقوم النظام تلقائياً بإنشاء المستويات (المستوى 1، المستوى 2...) والفصول الدراسية (الترم 1، الترم 2) بناءً على هذه الأرقام.
                    </small>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1.5rem;">
                    حفظ و إنشاء الهيكل
                </button>
            </form>
        </div>

        <!-- List Table -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">قائمة التخصصات</h3>
                <span class="badge badge-info">{{ $majors->count() }} تخصص</span>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>التخصص</th>
                            <th>الكلية</th>
                            <th>الهيكل</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($majors as $major)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td style="font-weight: 600;">{{ $major->name }}</td>
                            <td>
                                <span class="badge badge-warning">{{ $major->college->name }}</span>
                                <div style="font-size: 0.75rem; color: var(--text-secondary);">{{ $major->college->university->name }}</div>
                            </td>
                            <td style="font-size: 0.85rem;">
                                <div>{{ $major->levels->count() }} مستويات</div>
                                <div style="color: var(--text-secondary);">إجمالي {{ $major->levels->sum(fn($l) => $l->terms->count()) }} ترم</div>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    {{-- Edit Button --}}
                                    <button
                                        type="button"
                                        class="btn btn-secondary"
                                        style="padding: 0.4rem 0.8rem; font-size: 0.8rem; background-color: #e0f2fe; color: #0284c7;"
                                        @click="
                                            showEditModal = true;
                                            modalTitle = 'تعديل: {{ $major->name }}';
                                            editUrl = '{{ route('admin.majors.update', $major) }}';
                                            editName = '{{ $major->name }}';
                                            editCollegeId = '{{ $major->college_id }}';
                                        ">
                                        تعديل
                                    </button>

                                    {{-- Delete Button --}}
                                    <button
                                        type="button"
                                        class="btn btn-danger"
                                        style="padding: 0.4rem 0.8rem; font-size: 0.8rem;"
                                        @click="
                                            showDeleteModal = true;
                                            deleteUrl = '{{ route('admin.majors.destroy', $major) }}';
                                            modalTitle = 'حذف تخصص {{ $major->name }}';
                                            modalMessage = 'تحذير: سيؤدي حذف التخصص إلى حذف جميع المستويات ({{ $major->levels->count() }}) والترمات والمواد والطلاب المسجلين به. هل أنت متأكد تماماً؟';
                                        ">
                                        حذف
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                لا توجد تخصصات مضافة حتى الآن.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Include Delete Modal -->
    <x-delete-modal />

    <!-- Include Edit Modal -->
    <x-edit-modal>
        <form :action="editUrl" method="POST">
            @csrf
            @method('PUT')

            <div class="alert alert-info" style="font-size: 0.85rem; margin-bottom: 1.5rem;">
                ملاحظة: يمكنك تعديل اسم التخصص والكلية فقط. لا يمكن تعديل الهيكل (عدد المستويات والترمات) بعد الإنشاء حفاظاً على سلامة بيانات الطلاب.
            </div>

            <div class="form-group">
                <label for="edit_college_id" class="form-label">الكلية التابعة لها</label>
                <select name="college_id" id="edit_college_id" class="form-control" x-model="editCollegeId" required>
                    <option value="">اختر الكلية...</option>
                    @foreach($universities as $university)
                    <optgroup label="{{ $university->name }}">
                        @foreach($university->colleges as $college)
                        <option value="{{ $college->id }}">{{ $college->name }}</option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="edit_name" class="form-label">اسم التخصص</label>
                <input type="text" name="name" id="edit_name" class="form-control" x-model="editName" required>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" @click="showEditModal = false">إلغاء</button>
                <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
            </div>
        </form>
    </x-edit-modal>

</div>

@endsection