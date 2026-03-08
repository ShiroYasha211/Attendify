@extends('layouts.admin')

@section('title', 'إدارة الطلاب')

@section('content')

<style>
    .page-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .page-header-icon {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 4px 12px -2px rgba(16, 185, 129, 0.4);
    }

    .page-header-text h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .page-header-text p {
        color: var(--text-secondary);
        font-size: 0.9rem;
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.25rem;
        border: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-icon.green {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .stat-icon.blue {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .stat-icon.purple {
        background: rgba(139, 92, 246, 0.1);
        color: #8b5cf6;
    }

    .stat-icon.amber {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .stat-info h3 {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .stat-info p {
        color: var(--text-secondary);
        font-size: 0.85rem;
    }

    .form-card {
        background: white;
        border-radius: 20px;
        border: 1px solid var(--border-color);
        padding: 1.5rem;
    }

    .form-card-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
    }

    .form-card-header .icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .form-card-header h3 {
        font-size: 1.1rem;
        font-weight: 700;
    }

    .input-with-icon {
        position: relative;
    }

    .input-with-icon .icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-light);
    }

    .input-with-icon input,
    .input-with-icon select {
        padding-right: 2.75rem;
    }

    .table-card {
        background: white;
        border-radius: 20px;
        border: 1px solid var(--border-color);
        padding: 1.5rem;
    }

    .table-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
    }

    .table-card-header h3 {
        font-size: 1.1rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .count-badge {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .modern-table thead th {
        background: #f8fafc;
        padding: 1rem;
        text-align: right;
        font-weight: 600;
        color: var(--text-secondary);
        font-size: 0.85rem;
        border-bottom: 1px solid var(--border-color);
    }

    .modern-table thead th:first-child {
        border-radius: 0 12px 0 0;
    }

    .modern-table thead th:last-child {
        border-radius: 12px 0 0 0;
    }

    .modern-table tbody td {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .modern-table tbody tr:hover {
        background: #fafafa;
    }

    .action-btn {
        padding: 0.5rem 0.875rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .action-btn.view {
        background: #f3f4f6;
        color: #6b7280;
    }

    .action-btn.view:hover {
        background: #e5e7eb;
    }

    .action-btn.edit {
        background: #eff6ff;
        color: #3b82f6;
    }

    .action-btn.edit:hover {
        background: #dbeafe;
    }

    .action-btn.delete {
        background: #fef2f2;
        color: #ef4444;
    }

    .action-btn.delete:hover {
        background: #fee2e2;
    }

    .btn-submit {
        width: 100%;
        padding: 0.875rem;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-submit:hover {
        box-shadow: 0 4px 15px -3px rgba(16, 185, 129, 0.4);
        transform: translateY(-1px);
    }

    .student-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        font-weight: 700;
        color: #059669;
    }

    .student-number {
        font-family: monospace;
        background: #f0fdf4;
        color: #166534;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
    }
</style>

<div x-data="{ 
    showDeleteModal: false, 
    showEditModal: false,
    showDetailsModal: false,
    deleteUrl: '', 
    modalTitle: '', 
    modalMessage: '',
    
    editUrl: '',
    editName: '',
    editEmail: '',
    editStudentNumber: '',
    editLevelId: '',
    
    viewStudent: {},
    viewSubjects: [],
    viewDelegate: null
}">

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div class="page-header-text">
            <h1>إدارة الطلاب</h1>
            <p>تسجيل وإدارة بيانات الطلاب المسجلين في النظام</p>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 1.5rem;">{{ session('success') }}</div>
    @endif

    @if($errors->any())
    <div style="background: linear-gradient(135deg, #fef2f2, #fff); border: 1px solid #fecaca; border-right: 4px solid #ef4444; color: #991b1b; padding: 1rem 1.25rem; border-radius: 12px; margin-bottom: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <strong>تنبيه:</strong>
        </div>
        <ul style="margin: 0; padding-right: 1.5rem;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon green">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="stat-info">
                <h3>{{ $students->total() }}</h3>
                <p>إجمالي الطلاب</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                </svg>
            </div>
            <div class="stat-info">
                <h3>{{ \App\Models\Academic\Major::count() }}</h3>
                <p>التخصصات</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
            </div>
            <div class="stat-info">
                <h3>{{ \App\Models\Academic\Level::count() }}</h3>
                <p>المستويات</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <polyline points="17 11 19 13 23 9"></polyline>
                </svg>
            </div>
            <div class="stat-info">
                <h3>{{ \App\Models\User::where('role', 'delegate')->count() }}</h3>
                <p>المندوبين</p>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem; align-items: start;">

        <!-- Create Form -->
        <div class="form-card">
            <div class="form-card-header">
                <div class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                </div>
                <h3>تسجيل طالب جديد</h3>
            </div>

            <form action="{{ route('admin.students.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="level_id" class="form-label">المرحلة الدراسية</label>
                    <div class="input-with-icon">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                        </span>
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
                </div>

                <div class="form-group">
                    <label for="name" class="form-label">الاسم الكامل</label>
                    <div class="input-with-icon">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </span>
                        <input type="text" name="name" id="name" class="form-control" placeholder="اسم الطالب..." required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="student_number" class="form-label">الرقم الجامعي</label>
                    <div class="input-with-icon">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="16" rx="2" ry="2"></rect>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </span>
                        <input type="text" name="student_number" id="student_number" class="form-control" placeholder="12345678" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <div class="input-with-icon">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                        </span>
                        <input type="email" name="email" id="email" class="form-control" placeholder="student@example.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">كلمة المرور</label>
                    <div class="input-with-icon">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </span>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    </svg>
                    تسجيل الطالب
                </button>
            </form>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <div class="table-card-header">
                <h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                    </svg>
                    قائمة الطلاب
                </h3>
                <span class="count-badge">{{ $students->total() }} طالب</span>
            </div>

            <div class="table-responsive">
<table class="modern-table">
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
                        <td style="font-weight: 600; color: var(--text-secondary);">{{ $loop->iteration }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div class="student-avatar">{{ mb_substr($student->name, 0, 1) }}</div>
                                <div>
                                    <div style="font-weight: 600;">{{ $student->name }}</div>
                                    <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ $student->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="student-number">{{ $student->student_number }}</span>
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $student->level->name ?? '-' }}</div>
                            <div style="font-size: 0.8rem; color: var(--text-secondary);">
                                {{ $student->major->name ?? '' }} ({{ $student->college->name ?? '' }})
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <button type="button" class="action-btn view" title="عرض التفاصيل"
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
                                        viewSubjects = {{ json_encode($student->level ? $student->level->terms->flatMap->subjects->map(function($s) {
                                            return [
                                                'name' => $s->name,
                                                'code' => $s->code,
                                                'doctor' => $s->doctor ? $s->doctor->name : 'غير محدد',
                                                'term' => $s->term->name
                                            ];
                                        }) : []) }};
                                        viewDelegate = {{ json_encode(isset($delegates[$student->level_id]) ? [
                                            'name' => $delegates[$student->level_id]->name,
                                            'email' => $delegates[$student->level_id]->email
                                        ] : null) }};
                                    ">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button type="button" class="action-btn edit"
                                    @click="
                                        showEditModal = true;
                                        modalTitle = 'تعديل: {{ $student->name }}';
                                        editUrl = '{{ route('admin.students.update', $student) }}';
                                        editName = '{{ $student->name }}';
                                        editEmail = '{{ $student->email }}';
                                        editStudentNumber = '{{ $student->student_number }}';
                                        editLevelId = '{{ $student->level_id }}';
                                    ">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                    تعديل
                                </button>
                                <button type="button" class="action-btn delete"
                                    @click="
                                        showDeleteModal = true;
                                        deleteUrl = '{{ route('admin.students.destroy', $student) }}';
                                        modalTitle = 'حذف {{ $student->name }}';
                                        modalMessage = 'حذف الطالب سيؤدي إلى حذف جميع سجلات حضوره.';
                                    ">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                    حذف
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#e2e8f0" stroke-width="1.5" style="margin-bottom: 1rem;">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                            </svg>
                            <div>لا يوجد طلاب مسجلين</div>
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

    <!-- Modals -->
    <x-delete-modal />

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
    <div x-show="showDetailsModal" class="modal-overlay" style="display: none;"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="modal-container" style="text-align: right; max-width: 650px;" @click.away="showDetailsModal = false">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
                <h3 class="modal-title" style="margin: 0;">تفاصيل الطالب الأكاديمية</h3>
                <button @click="showDetailsModal = false" style="background: none; border: none; cursor: pointer; font-size: 1.5rem; color: var(--text-secondary);">&times;</button>
            </div>

            <!-- Student Header -->
            <div style="background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem;">
                <h4 x-text="viewStudent.name" style="margin: 0; font-size: 1.25rem;"></h4>
                <div style="opacity: 0.9; margin-top: 0.5rem; display: flex; gap: 1rem; font-size: 0.9rem; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 0.25rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="16" rx="2" ry="2"></rect>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span x-text="viewStudent.student_number"></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.25rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                        </svg>
                        <span x-text="viewStudent.major"></span> - <span x-text="viewStudent.level"></span>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: start;">

                <!-- Subjects -->
                <div>
                    <h5 style="margin-top: 0; margin-bottom: 1rem; color: var(--text-primary); border-bottom: 2px solid #10b981; display: inline-block; padding-bottom: 0.25rem;">
                        المواد الدراسية والدكاترة
                    </h5>

                    <div style="display: flex; flex-direction: column; gap: 0.75rem; max-height: 280px; overflow-y: auto;">
                        <template x-for="subject in viewSubjects">
                            <div style="background: white; border: 1px solid var(--border-color); padding: 0.75rem; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-weight: 600; font-size: 0.95rem;" x-text="subject.name"></div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);">
                                        كود: <span x-text="subject.code || '-'"></span> | <span x-text="subject.term"></span>
                                    </div>
                                </div>
                                <div style="text-align: left;">
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);">المدرس</div>
                                    <div style="font-weight: 600; font-size: 0.85rem; color: #059669;" x-text="subject.doctor"></div>
                                </div>
                            </div>
                        </template>
                        <div x-show="viewSubjects.length === 0" style="text-align: center; padding: 1rem; color: var(--text-secondary); background: #f8fafc; border-radius: 8px;">
                            لا توجد مواد مسجلة
                        </div>
                    </div>
                </div>

                <!-- Delegate -->
                <div style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); padding: 1rem; border-radius: 12px; border: 1px solid #bbf7d0;">
                    <h5 style="margin-top: 0; margin-bottom: 1rem; font-size: 0.9rem; font-weight: 700; color: #166534;">مندوب الدفعة</h5>

                    <template x-if="viewDelegate">
                        <div style="text-align: center;">
                            <div style="width: 45px; height: 45px; background: linear-gradient(135deg, #10b981, #059669); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; margin: 0 auto 0.5rem; font-weight: bold;">
                                <span x-text="viewDelegate.name.charAt(0)"></span>
                            </div>
                            <div style="font-weight: 600; font-size: 0.9rem; color: #166534;" x-text="viewDelegate.name"></div>
                            <div style="font-size: 0.75rem; color: #22c55e; word-break: break-all;" x-text="viewDelegate.email"></div>
                        </div>
                    </template>

                    <template x-if="!viewDelegate">
                        <div style="text-align: center; color: #16a34a; font-size: 0.8rem;">
                            لم يتم تعيين مندوب لهذه الدفعة بعد.
                        </div>
                    </template>
                </div>

            </div>

            <div class="modal-actions" style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                <button type="button" class="btn btn-secondary" @click="showDetailsModal = false">إغلاق</button>
            </div>
        </div>
    </div>

</div>

@endsection