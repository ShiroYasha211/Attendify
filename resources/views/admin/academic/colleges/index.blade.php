@extends('layouts.admin')

@section('title', 'إدارة الكليات')

@section('content')

<div class="container" style="max-width: 100%;" x-data="{ 
    showDeleteModal: false, 
    showEditModal: false,
    deleteUrl: '', 
    modalTitle: '', 
    modalMessage: '',
    editUrl: '',
    editName: '',
    editUniversityId: ''
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
                إضافة كلية جديدة
            </h3>

            <form action="{{ route('admin.colleges.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="university_id" class="form-label">الجامعة التابعة لها</label>
                    <select name="university_id" id="university_id" class="form-control" required>
                        <option value="">اختر الجامعة...</option>
                        @foreach($universities as $university)
                        <option value="{{ $university->id }}">{{ $university->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="name" class="form-label">اسم الكلية</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="مثال: كلية الحاسبات" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    حفظ البيانات
                </button>
            </form>
        </div>

        <!-- List Table -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">قائمة الكليات</h3>
                <span class="badge badge-info">{{ $colleges->count() }} كلية</span>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الكلية</th>
                            <th>الجامعة</th>
                            <th>عدد التخصصات</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($colleges as $college)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td style="font-weight: 600;">{{ $college->name }}</td>
                            <td>
                                <span class="badge badge-warning">{{ $college->university->name }}</span>
                            </td>
                            <td>{{ $college->majors->count() }} تخصص</td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    {{-- Edit Button --}}
                                    <button
                                        type="button"
                                        class="btn btn-secondary"
                                        style="padding: 0.4rem 0.8rem; font-size: 0.8rem; background-color: #e0f2fe; color: #0284c7;"
                                        @click="
                                            showEditModal = true;
                                            modalTitle = 'تعديل: {{ $college->name }}';
                                            editUrl = '{{ route('admin.colleges.update', $college) }}';
                                            editName = '{{ $college->name }}';
                                            editUniversityId = '{{ $college->university_id }}';
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
                                            deleteUrl = '{{ route('admin.colleges.destroy', $college) }}';
                                            modalTitle = 'حذف كلية {{ $college->name }}';
                                            modalMessage = 'تحذير: سيؤدي حذف الكلية إلى حذف جميع التخصصات ({{ $college->majors->count() }}) والطلاب والمواد المرتبطة بها. هل أنت متأكد؟';
                                        ">
                                        حذف
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                لا توجد كليات مضافة حتى الآن.
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

            <div class="form-group">
                <label for="edit_university_id" class="form-label">الجامعة التابعة لها</label>
                <select name="university_id" id="edit_university_id" class="form-control" x-model="editUniversityId" required>
                    <option value="">اختر الجامعة...</option>
                    @foreach($universities as $university)
                    <option value="{{ $university->id }}">{{ $university->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="edit_name" class="form-label">اسم الكلية</label>
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