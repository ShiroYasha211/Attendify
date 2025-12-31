@extends('layouts.admin')

@section('title', 'إدارة أعضاء هيئة التدريس')

@section('content')

<div class="container" style="max-width: 100%;" x-data="{ 
    showDeleteModal: false, 
    showEditModal: false,
    showDetailsModal: false,
    deleteUrl: '', 
    modalTitle: '', 
    modalMessage: '',
    
    // Edit Data
    editUrl: '',
    editName: '',
    editEmail: '',
    editCollegeId: '',
    
    // View Data
    viewDoctor: {},
    viewSubjects: []
}">

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger" style="border-right: 5px solid #dc2626; background-color: #fef2f2; color: #b91c1c; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <h4 style="margin-top: 0; margin-bottom: 0.5rem; font-size: 1rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            تنبيه: يرجى التحقق من البيانات التالية
        </h4>
        <ul style="margin-bottom: 0; padding-right: 1.5rem;">
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
                إضافة دكتور جديد
            </h3>

            <form action="{{ route('admin.doctors.store') }}" method="POST">
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
                    <label for="name" class="form-label">الاسم الكامل</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="د. محمد ..." required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="doctor@example.com" required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">كلمة المرور</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    حفظ البيانات
                </button>
            </form>
        </div>

        <!-- List Table -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">قائمة الدكاترة</h3>
                <span class="badge badge-info">{{ $doctors->total() }} دكتور</span>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الدكتور</th>
                            <th>الكلية</th>
                            <th>المواد</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($doctors as $doctor)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div style="font-weight: 600;">{{ $doctor->name }}</div>
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ $doctor->email }}</div>
                            </td>
                            <td>
                                <span class="badge badge-warning">{{ $doctor->college->name ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $doctor->subjects->count() }} مواد</span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">

                                    {{-- View Details Button --}}
                                    <button
                                        type="button"
                                        class="btn btn-secondary"
                                        style="padding: 0.4rem 0.8rem; font-size: 0.8rem; background-color: #f3f4f6; color: var(--text-primary);"
                                        title="عرض التفاصيل"
                                        @click="
                                            showDetailsModal = true;
                                            viewDoctor = {
                                                name: '{{ $doctor->name }}',
                                                email: '{{ $doctor->email }}',
                                                college: '{{ $doctor->college->name ?? '-' }}',
                                                university: '{{ $doctor->university->name ?? '-' }}'
                                            };
                                            viewSubjects = {{ json_encode($doctor->subjects->map(function($s) {
                                                return [
                                                    'name' => $s->name,
                                                    'code' => $s->code,
                                                    'term' => $s->term->name,
                                                    'level' => $s->term->level->name,
                                                    'major' => $s->term->level->major->name
                                                ];
                                            })) }};
                                        ">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                            <polyline points="10 9 9 9 8 9"></polyline>
                                        </svg>
                                    </button>

                                    {{-- Edit Button --}}
                                    <button
                                        type="button"
                                        class="btn btn-secondary"
                                        style="padding: 0.4rem 0.8rem; font-size: 0.8rem; background-color: #e0f2fe; color: #0284c7;"
                                        @click="
                                            showEditModal = true;
                                            modalTitle = 'تعديل: {{ $doctor->name }}';
                                            editUrl = '{{ route('admin.doctors.update', $doctor) }}';
                                            editName = '{{ $doctor->name }}';
                                            editEmail = '{{ $doctor->email }}';
                                            editCollegeId = '{{ $doctor->college_id }}';
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
                                            deleteUrl = '{{ route('admin.doctors.destroy', $doctor) }}';
                                            modalTitle = 'حذف الدكتور {{ $doctor->name }}';
                                            modalMessage = 'تحذير: حذف الدكتور سيؤدي إلى فك ارتباطه بالمواد التي يدرسها. هل أنت متأكد؟';
                                        ">
                                        حذف
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                لا يوجد دكاترة مسجلين.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1.5rem;">
                {{ $doctors->links() }}
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
                <label for="edit_name" class="form-label">الاسم الكامل</label>
                <input type="text" name="name" id="edit_name" class="form-control" x-model="editName" required>
            </div>

            <div class="form-group">
                <label for="edit_email" class="form-label">البريد الإلكتروني</label>
                <input type="email" name="email" id="edit_email" class="form-control" x-model="editEmail" required>
            </div>

            <div class="form-group" style="border-top: 1px solid var(--border-color); padding-top: 1rem; margin-top: 1rem;">
                <label for="edit_password" class="form-label">كلمة المرور الجديدة (اختياري)</label>
                <input type="password" name="password" id="edit_password" class="form-control" placeholder="اتركه فارغاً إذا كنت لا تريد تغييرها">
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" @click="showEditModal = false">إلغاء</button>
                <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
            </div>
        </form>
    </x-edit-modal>

    <!-- Details Modal -->
    <div
        x-show="showDetailsModal"
        class="modal-overlay"
        style="display: none;"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div
            class="modal-container"
            style="text-align: right; max-width: 600px;"
            @click.away="showDetailsModal = false">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
                <h3 class="modal-title" style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                    تفاصيل عضو هيئة التدريس
                </h3>
                <button @click="showDetailsModal = false" style="background: none; border: none; cursor: pointer; font-size: 1.5rem; color: var(--text-secondary); line-height: 1;">&times;</button>
            </div>

            <!-- Doctor Info -->
            <div style="display: flex; gap: 1rem; margin-bottom: 2rem; background: #f8fafc; padding: 1rem; border-radius: 8px;">
                <div style="flex: 1;">
                    <div style="color: var(--text-secondary); font-size: 0.85rem;">الاسم</div>
                    <div style="font-weight: 700; font-size: 1.1rem;" x-text="viewDoctor.name"></div>
                </div>
                <div style="flex: 1;">
                    <div style="color: var(--text-secondary); font-size: 0.85rem;">البريد الإلكتروني</div>
                    <div style="font-weight: 600;" x-text="viewDoctor.email"></div>
                </div>
            </div>

            <div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
                <div style="flex: 1;">
                    <div style="color: var(--text-secondary); font-size: 0.85rem;">الجامعة</div>
                    <div style="font-weight: 600;" x-text="viewDoctor.university"></div>
                </div>
                <div style="flex: 1;">
                    <div style="color: var(--text-secondary); font-size: 0.85rem;">الكلية</div>
                    <div style="font-weight: 600;" x-text="viewDoctor.college"></div>
                </div>
            </div>

            <!-- Subjects Table -->
            <h4 style="font-size: 1rem; margin-bottom: 1rem; border-bottom: 2px solid var(--primary-color); display: inline-block; padding-bottom: 0.25rem;">المواد الدراسية المسندة</h4>

            <div style="max-height: 250px; overflow-y: auto; overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f1f5f9;">
                            <th style="padding: 0.75rem; text-align: right; font-size: 0.9rem;">المادة</th>
                            <th style="padding: 0.75rem; text-align: right; font-size: 0.9rem;">الكود</th>
                            <th style="padding: 0.75rem; text-align: right; font-size: 0.9rem;">الموقع الأكاديمي</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="subject in viewSubjects">
                            <tr>
                                <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color); font-weight: 600;" x-text="subject.name"></td>
                                <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color);">
                                    <span class="badge badge-warning" x-show="subject.code" x-text="subject.code"></span>
                                    <span x-show="!subject.code">-</span>
                                </td>
                                <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color); font-size: 0.85rem;">
                                    <div x-text="subject.major"></div>
                                    <div style="color: var(--text-secondary);">
                                        <span x-text="subject.level"></span> - <span x-text="subject.term"></span>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="viewSubjects.length === 0">
                            <td colspan="3" style="text-align: center; padding: 1.5rem; color: var(--text-secondary);">
                                هذا الدكتور لا يدرس أي مواد حالياً.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="modal-actions" style="margin-top: 2rem;">
                <button type="button" class="btn btn-secondary" @click="showDetailsModal = false">إغلاق</button>
            </div>
        </div>
    </div>

</div>

@endsection