@extends('layouts.admin')

@section('title', 'إدارة المواد الدراسية')

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
    editTermId: '',
    editDoctorId: ''
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
                إضافة مادة جديدة
            </h3>

            <form action="{{ route('admin.subjects.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name" class="form-label">اسم المادة</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="مثال: برمجة 1" required>
                </div>

                <div class="form-group">
                    <label for="code" class="form-label">كود المادة (اختياري)</label>
                    <input type="text" name="code" id="code" class="form-control" placeholder="CS-101">
                </div>

                <div class="form-group">
                    <label for="term_id" class="form-label">الفصل الدراسي</label>
                    <select name="term_id" id="term_id" class="form-control" required>
                        <option value="">اختر الترم...</option>
                        @foreach($terms as $term)
                        <option value="{{ $term->id }}">
                            {{ $term->name }} - {{ $term->level->name }}
                            ({{ $term->level->major->name }} - {{ $term->level->major->college->name }})
                        </option>
                        @endforeach
                    </select>
                    <small style="color: var(--text-secondary); font-size: 0.8rem; margin-top: 0.25rem; display: block;">
                        سيتم ربط المادة تلقائياً بتخصص ومستوى هذا الترم.
                    </small>
                </div>

                <div class="form-group">
                    <label for="doctor_id" class="form-label">مدرس المادة</label>
                    <select name="doctor_id" id="doctor_id" class="form-control">
                        <option value="">بدون مدرس حالياً</option>
                        @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    حفظ البيانات
                </button>
            </form>
        </div>

        <!-- List Table -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">قائمة المواد الدراسية</h3>
                <span class="badge badge-info">{{ $subjects->total() }} مادة</span>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المادة</th>
                            <th>التفاصيل الأكاديمية</th>
                            <th>المدرس</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjects as $subject)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div style="font-weight: 600;">{{ $subject->name }}</div>
                                @if($subject->code)
                                <div class="badge badge-warning" style="font-size: 0.7rem; margin-top: 0.25rem;">{{ $subject->code }}</div>
                                @endif
                            </td>
                            <td>
                                <div style="font-size: 0.9rem;">{{ $subject->term->name }}</div>
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">
                                    {{ $subject->major->name }}
                                </div>
                            </td>
                            <td>
                                @if($subject->doctor)
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 30px; height: 30px; background-color: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold;">
                                        {{ mb_substr($subject->doctor->name, 0, 1) }}
                                    </div>
                                    <span style="font-size: 0.9rem;">{{ $subject->doctor->name }}</span>
                                </div>
                                @else
                                <span style="color: var(--text-light); font-size: 0.85rem;">غير معين</span>
                                @endif
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
                                            modalTitle = 'تعديل: {{ $subject->name }}';
                                            editUrl = '{{ route('admin.subjects.update', $subject) }}';
                                            editName = '{{ $subject->name }}';
                                            editCode = '{{ $subject->code }}';
                                            editTermId = '{{ $subject->term_id }}';
                                            editDoctorId = '{{ $subject->doctor_id }}';
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
                                            deleteUrl = '{{ route('admin.subjects.destroy', $subject) }}';
                                            modalTitle = 'حذف مادة {{ $subject->name }}';
                                            modalMessage = 'تحذير: سيؤدي حذف المادة إلى حذف جميع سجلات الحضور الخاصة بها. هل أنت متأكد؟';
                                        ">
                                        حذف
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                لا توجد مواد مسجلة.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div style="margin-top: 1.5rem;">
                {{ $subjects->links() }}
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
                <label for="edit_term_id" class="form-label">الفصل الدراسي</label>
                <select name="term_id" id="edit_term_id" class="form-control" x-model="editTermId" required>
                    <option value="">اختر الترم...</option>
                    @foreach($terms as $term)
                    <option value="{{ $term->id }}">
                        {{ $term->name }} - {{ $term->level->name }}
                        ({{ $term->level->major->name }} - {{ $term->level->major->college->name }})
                    </option>
                    @endforeach
                </select>
                <small style="color: var(--text-secondary); font-size: 0.8rem; display: block; margin-top: 0.25rem;">
                    تغيير الترم سيغير تلقائياً المستوى والتخصص المرتبط بالمادة.
                </small>
            </div>

            <div class="form-group">
                <label for="edit_name" class="form-label">اسم المادة</label>
                <input type="text" name="name" id="edit_name" class="form-control" x-model="editName" required>
            </div>

            <div class="form-group">
                <label for="edit_code" class="form-label">كود المادة</label>
                <input type="text" name="code" id="edit_code" class="form-control" x-model="editCode">
            </div>

            <div class="form-group">
                <label for="edit_doctor_id" class="form-label">مدرس المادة</label>
                <select name="doctor_id" id="edit_doctor_id" class="form-control" x-model="editDoctorId">
                    <option value="">بدون مدرس حالياً</option>
                    @foreach($doctors as $doctor)
                    <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" @click="showEditModal = false">إلغاء</button>
                <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
            </div>
        </form>
    </x-edit-modal>

</div>

@endsection