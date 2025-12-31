@extends('layouts.admin')

@section('title', 'إدارة الطلاب')

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
    editStudentNumber: '',
    editLevelId: '',
    
    // View Data
    viewStudent: {},
    viewSubjects: [],
    viewDelegate: null
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
                تسجيل طالب جديد
            </h3>

            <form action="{{ route('admin.students.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="level_id" class="form-label">المرحلة الدراسية</label>
                    <select name="level_id" id="level_id" class="form-control" required style="font-size: 0.9rem;">
                        <option value="">اختر المرحلة...</option>
                        @foreach($universities as $university)
                        <optgroup label="{{ $university->name }}">
                            @foreach($university->colleges as $college)
                            @foreach($college->majors as $major)
                            @foreach($major->levels as $level)
                            <option value="{{ $level->id }}">
                                {{ $level->name }} - {{ $major->name }} ({{ $college->name }})
                            </option>
                            @endforeach
                            @endforeach
                            @endforeach
                        </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="name" class="form-label">الاسم الكامل</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="اسم الطالب..." required>
                </div>

                <div class="form-group">
                    <label for="student_number" class="form-label">الرقم الجامعي</label>
                    <input type="text" name="student_number" id="student_number" class="form-control" placeholder="12345678" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="student@example.com" required>
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
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">قائمة الطلاب</h3>
                <span class="badge badge-info">{{ $students->total() }} طالب</span>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الطالب</th>
                            <th>الرقم الجامعي</th>
                            <th>الانتساب الأكاديمي</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div style="font-weight: 600;">{{ $student->name }}</div>
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ $student->email }}</div>
                            </td>
                            <td style="font-family: monospace;">{{ $student->student_number }}</td>
                            <td>
                                <div style="font-weight: 600;">{{ $student->level->name ?? '-' }}</div>
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">
                                    {{ $student->major->name ?? '' }} ({{ $student->college->name ?? '' }})
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">

                                    {{-- View Details Button --}}
                                    <button
                                        type="button"
                                        class="btn btn-secondary"
                                        style="padding: 0.4rem 0.6rem; font-size: 0.8rem; background-color: #f3f4f6; color: var(--text-secondary); display: flex; align-items: center; justify-content: center;"
                                        title="عرض التفاصيل"
                                        @click="
                                            showDetailsModal = true;
                                            viewStudent = {
                                                name: '{{ $student->name }}',
                                                email: '{{ $student->email }}',
                                                student_number: '{{ $student->student_number }}',
                                                level: '{{ $student->level->name ?? '-' }}',
                                                major: '{{ $student->major->name ?? '-' }}',
                                                college: '{{ $student->college->name ?? '-' }}',
                                                university: '{{ $student->university->name ?? '-' }}'
                                            };
                                            
                                            {{-- Calculate Student Subjects from Level -> Terms -> Subjects --}}
                                            viewSubjects = {{ json_encode($student->level ? $student->level->terms->flatMap->subjects->map(function($s) {
                                                return [
                                                    'name' => $s->name,
                                                    'code' => $s->code,
                                                    'doctor' => $s->doctor ? $s->doctor->name : 'غير محدد',
                                                    'term' => $s->term->name
                                                ];
                                            }) : []) }};
                                            
                                            {{-- Find Delegate --}}
                                            viewDelegate = {{ json_encode(isset($delegates[$student->level_id]) ? [
                                                'name' => $delegates[$student->level_id]->name,
                                                'email' => $delegates[$student->level_id]->email
                                            ] : null) }};
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
                                            modalTitle = 'تعديل: {{ $student->name }}';
                                            editUrl = '{{ route('admin.students.update', $student) }}';
                                            editName = '{{ $student->name }}';
                                            editEmail = '{{ $student->email }}';
                                            editStudentNumber = '{{ $student->student_number }}';
                                            editLevelId = '{{ $student->level_id }}';
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
                                            deleteUrl = '{{ route('admin.students.destroy', $student) }}';
                                            modalTitle = 'حذف الطالب {{ $student->name }}';
                                            modalMessage = 'تحذير: حذف الطالب سيؤدي إلى حذف جميع سجلات حضوره. هل أنت متأكد؟';
                                        ">
                                        حذف
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                لا يوجد طلاب مسجلين.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1.5rem;">
                {{ $students->links() }}
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
                <label for="edit_level_id" class="form-label">المرحلة الدراسية</label>
                <select name="level_id" id="edit_level_id" class="form-control" x-model="editLevelId" required style="font-size: 0.9rem;">
                    <option value="">اختر المرحلة...</option>
                    @foreach($universities as $university)
                    <optgroup label="{{ $university->name }}">
                        @foreach($university->colleges as $college)
                        @foreach($college->majors as $major)
                        @foreach($major->levels as $level)
                        <option value="{{ $level->id }}">
                            {{ $level->name }} - {{ $major->name }} ({{ $college->name }})
                        </option>
                        @endforeach
                        @endforeach
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
                <label for="edit_student_number" class="form-label">الرقم الجامعي</label>
                <input type="text" name="student_number" id="edit_student_number" class="form-control" x-model="editStudentNumber" required>
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
                    تفاصيل الطالب الأكاديمية
                </h3>
                <button @click="showDetailsModal = false" style="background: none; border: none; cursor: pointer; font-size: 1.5rem; color: var(--text-secondary); line-height: 1;">&times;</button>
            </div>

            <!-- Student Basic Info -->
            <div style="background-color: var(--primary-color); color: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                <h4 x-text="viewStudent.name" style="margin: 0; font-size: 1.25rem;"></h4>
                <div style="opacity: 0.9; margin-top: 0.5rem; display: flex; gap: 1rem; font-size: 0.9rem; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 0.25rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        <span x-text="viewStudent.student_number"></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.25rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 10v6M2 10v6M12 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"></path>
                            <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                        </svg>
                        <span x-text="viewStudent.major"></span> - <span x-text="viewStudent.level"></span>
                    </div>
                </div>
            </div>

            <!-- Tabs/Sections -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: start;">

                <!-- Subjects List -->
                <div>
                    <h5 style="margin-top: 0; margin-bottom: 1rem; color: var(--text-primary); border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem; display: inline-block;">
                        المواد الدراسية والدكاترة
                    </h5>

                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <template x-for="subject in viewSubjects">
                            <div style="background: white; border: 1px solid var(--border-color); padding: 0.75rem; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-weight: 600; font-size: 0.95rem;" x-text="subject.name"></div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);">
                                        كود: <span x-text="subject.code || '-'"></span> | <span x-text="subject.term"></span>
                                    </div>
                                </div>
                                <div style="text-align: left;">
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);">مدرس المادة</div>
                                    <div style="font-weight: 600; font-size: 0.85rem; color: var(--primary-color);" x-text="subject.doctor"></div>
                                </div>
                            </div>
                        </template>
                        <div x-show="viewSubjects.length === 0" style="text-align: center; padding: 1rem; color: var(--text-secondary); background: #f8fafc; border-radius: 6px;">
                            لا توجد مواد مسجلة لهذا الطالب حالياً.
                        </div>
                    </div>
                </div>

                <!-- Delegate Info -->
                <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <h5 style="margin-top: 0; margin-bottom: 1rem; font-size: 0.9rem; font-weight: 700;">مندوب الدفعة</h5>

                    <template x-if="viewDelegate">
                        <div style="text-align: center;">
                            <div style="width: 40px; height: 40px; background-color: #cbd5e1; color: var(--text-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; margin: 0 auto 0.5rem; font-weight: bold;">
                                <span x-text="viewDelegate.name.charAt(0)"></span>
                            </div>
                            <div style="font-weight: 600; font-size: 0.9rem;" x-text="viewDelegate.name"></div>
                            <div style="font-size: 0.75rem; color: var(--text-secondary); word-break: break-all;" x-text="viewDelegate.email"></div>
                        </div>
                    </template>

                    <template x-if="!viewDelegate">
                        <div style="text-align: center; color: var(--text-secondary); font-size: 0.8rem;">
                            لم يتم تعيين مندوب لهذه الدفعة بعد.
                        </div>
                    </template>
                </div>

            </div>

            <div class="modal-actions" style="margin-top: 2rem; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                <button type="button" class="btn btn-secondary" @click="showDetailsModal = false">إغلاق</button>
            </div>
        </div>
    </div>

</div>

@endsection