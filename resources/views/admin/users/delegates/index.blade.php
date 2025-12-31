@extends('layouts.admin')

@section('title', 'إدارة المندوبين')

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
    editLevelId: '',
    
    // View Data
    viewDelegate: {}
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
                إضافة مندوب جديد
            </h3>

            <form action="{{ route('admin.delegates.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="level_id" class="form-label">الدفعة التي يمثلها (المستوى)</label>
                    <select name="level_id" id="level_id" class="form-control" required style="font-size: 0.9rem;">
                        <option value="">اختر الدفعة...</option>
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
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">قائمة المندوبين</h3>
                <span class="badge badge-info">{{ $delegates->total() }} مندوب</span>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المندوب</th>
                            <th>المسؤولية (الدفعة)</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($delegates as $delegate)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div style="font-weight: 600;">{{ $delegate->name }}</div>
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ $delegate->email }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 600;">{{ $delegate->level->name ?? '-' }}</div>
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">
                                    {{ $delegate->major->name ?? '' }}
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
                                            viewDelegate = {
                                                name: '{{ $delegate->name }}',
                                                email: '{{ $delegate->email }}',
                                                university: '{{ $delegate->university->name ?? '-' }}',
                                                college: '{{ $delegate->college->name ?? '-' }}',
                                                major: '{{ $delegate->major->name ?? '-' }}',
                                                level: '{{ $delegate->level->name ?? '-' }}'
                                            };
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
                                            modalTitle = 'تعديل: {{ $delegate->name }}';
                                            editUrl = '{{ route('admin.delegates.update', $delegate) }}';
                                            editName = '{{ $delegate->name }}';
                                            editEmail = '{{ $delegate->email }}';
                                            editLevelId = '{{ $delegate->level_id }}';
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
                                            deleteUrl = '{{ route('admin.delegates.destroy', $delegate) }}';
                                            modalTitle = 'حذف المندوب {{ $delegate->name }}';
                                            modalMessage = 'تحذير: هل أنت متأكد من الحذف؟';
                                        ">
                                        حذف
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                لا يوجد مندوبين مسجلين.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1.5rem;">
                {{ $delegates->links() }}
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
                <label for="edit_level_id" class="form-label">الدفعة التي يمثلها (المستوى)</label>
                <select name="level_id" id="edit_level_id" class="form-control" x-model="editLevelId" required style="font-size: 0.9rem;">
                    <option value="">اختر الدفعة...</option>
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
            style="text-align: right; max-width: 500px;"
            @click.away="showDetailsModal = false">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
                <h3 class="modal-title" style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                    تفاصيل المندوب
                </h3>
                <button @click="showDetailsModal = false" style="background: none; border: none; cursor: pointer; font-size: 1.5rem; color: var(--text-secondary); line-height: 1;">&times;</button>
            </div>

            <!-- Delegate Details -->
            <div style="margin-bottom: 1.5rem; text-align: center;">
                <div style="width: 60px; height: 60px; background-color: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 1rem;">
                    <span x-text="viewDelegate.name ? viewDelegate.name.charAt(0) : ''"></span>
                </div>
                <h4 x-text="viewDelegate.name" style="margin: 0; font-size: 1.2rem;"></h4>
                <div x-text="viewDelegate.email" style="color: var(--text-secondary); font-size: 0.9rem;"></div>
            </div>

            <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                <h5 style="margin-top: 0; margin-bottom: 1rem; font-size: 0.95rem; font-weight: 700; color: var(--text-primary); border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem;">
                    مسؤول عن الدفعة:
                </h5>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.9rem;">
                    <div>
                        <span style="display: block; color: var(--text-secondary); font-size: 0.8rem;">الجامعة</span>
                        <div x-text="viewDelegate.university" style="font-weight: 600;"></div>
                    </div>
                    <div>
                        <span style="display: block; color: var(--text-secondary); font-size: 0.8rem;">الكلية</span>
                        <div x-text="viewDelegate.college" style="font-weight: 600;"></div>
                    </div>
                    <div>
                        <span style="display: block; color: var(--text-secondary); font-size: 0.8rem;">التخصص</span>
                        <div x-text="viewDelegate.major" style="font-weight: 600;"></div>
                    </div>
                    <div>
                        <span style="display: block; color: var(--text-secondary); font-size: 0.8rem;">المستوى</span>
                        <span class="badge badge-warning" x-text="viewDelegate.level"></span>
                    </div>
                </div>
            </div>

            <div class="modal-actions" style="margin-top: 2rem;">
                <button type="button" class="btn btn-secondary" @click="showDetailsModal = false">إغلاق</button>
            </div>
        </div>
    </div>

</div>

@endsection