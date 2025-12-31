@extends('layouts.admin')

@section('title', 'إدارة الجامعات')

@section('content')

<div class="container" style="max-width: 100%;" x-data="{ 
    showDeleteModal: false, 
    showEditModal: false,
    deleteUrl: '', 
    modalTitle: '', 
    modalMessage: '',
    editUrl: '',
    editName: '',
    editCode: '',
    editAddress: ''
}">

    <!-- Success Message -->
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">

        <!-- Create Form -->
        <div class="card">
            <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text-primary);">
                إضافة جامعة جديدة
            </h3>

            <form action="{{ route('admin.universities.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="name" class="form-label">اسم الجامعة</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="مثال: جامعة الملك سعود" required>
                </div>

                <div class="form-group">
                    <label for="code" class="form-label">كود الجامعة (اختياري)</label>
                    <input type="text" name="code" id="code" class="form-control" placeholder="KSU">
                </div>

                <div class="form-group">
                    <label for="address" class="form-label">العنوان (اختياري)</label>
                    <input type="text" name="address" id="address" class="form-control" placeholder="الرياض">
                </div>

                <div class="form-group">
                    <label for="logo" class="form-label">شعار الجامعة (اختياري)</label>
                    <input type="file" name="logo" id="logo" class="form-control" accept="image/*">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    حفظ البيانات
                </button>
            </form>
        </div>

        <!-- List Table -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">قائمة الجامعات المسجلة</h3>
                <span class="badge badge-info">{{ $universities->count() }} جامعة</span>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الشعار</th>
                            <th>الاسم</th>
                            <th>الكود</th>
                            <th>العنوان</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($universities as $university)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                @if($university->logo)
                                <img src="{{ asset('storage/' . $university->logo) }}" alt="Logo" style="width: 40px; height: 40px; object-fit: contain; border-radius: 4px;">
                                @else
                                <span style="color: #ccc;">-</span>
                                @endif
                            </td>
                            <td style="font-weight: 600;">{{ $university->name }}</td>
                            <td>
                                @if($university->code)
                                <span class="badge badge-warning">{{ $university->code }}</span>
                                @else
                                <span style="color: var(--text-light);">-</span>
                                @endif
                            </td>
                            <td style="color: var(--text-secondary);">{{ $university->address ?? '-' }}</td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">

                                    {{-- Edit Button --}}
                                    <button
                                        type="button"
                                        class="btn btn-secondary"
                                        style="padding: 0.4rem 0.8rem; font-size: 0.8rem; background-color: #e0f2fe; color: #0284c7;"
                                        @click="
                                            showEditModal = true;
                                            modalTitle = 'تعديل: {{ $university->name }}';
                                            editUrl = '{{ route('admin.universities.update', $university) }}';
                                            editName = '{{ $university->name }}';
                                            editCode = '{{ $university->code }}';
                                            editAddress = '{{ $university->address }}';
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
                                            deleteUrl = '{{ route('admin.universities.destroy', $university) }}';
                                            modalTitle = 'حذف جامعة {{ $university->name }}';
                                            modalMessage = 'تحذير: سيؤدي حذف الجامعة إلى حذف جميع الكليات ({{ $university->colleges->count() }}) والتخصصات التابعة لها. هل أنت متأكد تماماً؟';
                                        ">
                                        حذف
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                لا توجد جامعات مضافة حتى الآن.
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

    <!-- Include Edit Modal with Form -->
    <x-edit-modal>
        <form :action="editUrl" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="edit_name" class="form-label">اسم الجامعة</label>
                <input type="text" name="name" id="edit_name" class="form-control" x-model="editName" required>
            </div>

            <div class="form-group">
                <label for="edit_code" class="form-label">كود الجامعة (اختياري)</label>
                <input type="text" name="code" id="edit_code" class="form-control" x-model="editCode">
            </div>

            <div class="form-group">
                <label for="edit_address" class="form-label">العنوان (اختياري)</label>
                <input type="text" name="address" id="edit_address" class="form-control" x-model="editAddress">
            </div>

            <div class="form-group">
                <label for="edit_logo" class="form-label">تحديث الشعار (اختياري)</label>
                <input type="file" name="logo" id="edit_logo" class="form-control" accept="image/*">
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" @click="showEditModal = false">إلغاء</button>
                <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
            </div>
        </form>
    </x-edit-modal>

</div>

@endsection