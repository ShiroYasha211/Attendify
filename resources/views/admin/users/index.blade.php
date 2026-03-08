@extends('layouts.admin')

@section('title', 'إدارة المستخدمين')

@section('content')

<!-- Header Section -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary);">إدارة المستخدمين</h1>
        <p style="color: var(--text-secondary); margin-top: 0.5rem;">عرض وإدارة كافة حسابات المستخدمين في النظام</p>
    </div>

    <div style="display: flex; align-items: center; gap: 1rem;">
        <!-- Export Button -->
        <a href="{{ route('admin.users.export', ['role' => request('role')]) }}"
            class="btn"
            style="display: flex; align-items: center; gap: 0.5rem; background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 0.75rem 1.25rem; border-radius: 8px; font-weight: 600; text-decoration: none; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);"
            title="تصدير البيانات إلى Excel">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            تصدير Excel
        </a>

        <!-- Search Form -->
        <div style="width: 380px; max-width: 100%;">
            <form action="{{ route('admin.users.index') }}" method="GET" style="position: relative; display: flex; align-items: center;">
                @if(request('role'))
                <input type="hidden" name="role" value="{{ request('role') }}">
                @endif

                <div style="position: relative; width: 100%;">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="ابحث باسم المستخدم أو البريد الإلكتروني..."
                        style="width: 100%; padding: 0.85rem 2.5rem 0.85rem 1.25rem; 
                           border: 1px solid var(--border-color); 
                           border-radius: 10px; 
                           outline: none; 
                           background-color: #fff;
                           box-shadow: 0 2px 4px rgba(0,0,0,0.02);
                           transition: all 0.2s ease;
                           font-size: 0.95rem;"
                        onfocus="this.style.borderColor = 'var(--primary-color)'; this.style.boxShadow = '0 0 0 3px rgba(67, 56, 202, 0.1)';"
                        onblur="this.style.borderColor = 'var(--border-color)'; this.style.boxShadow = '0 2px 4px rgba(0,0,0,0.02)';">

                    <button type="submit" style="position: absolute; left: 8px; top: 50%; transform: translateY(-50%); border: none; background: none; color: var(--text-secondary); cursor: pointer;" title="بحث">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>

                    @if(request('search'))
                    <a href="{{ route('admin.users.index', ['role' => request('role')]) }}"
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #999; text-decoration: none;" title="مسح المبحوث عنه">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-error">
    {{ session('error') }}
</div>
@endif

<!-- Filters Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; margin-bottom: 2rem;">

    <a href="{{ route('admin.users.index', ['role' => 'all']) }}"
        class="card"
        style="padding: 1.5rem; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem; text-align: center; border: 1px solid {{ request('role') == 'all' || !request('role') ? 'var(--primary-color)' : 'transparent' }}; @if(request('role') == 'all' || !request('role')) background-color: rgba(67, 56, 202, 0.05); @endif">
        <div style="color: var(--primary-color);">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            </svg>
        </div>
        <span style="font-weight: 700; font-size: 0.9rem; color: var(--text-primary);">الكل</span>
    </a>

    <a href="{{ route('admin.users.index', ['role' => 'admin']) }}"
        class="card"
        style="padding: 1.5rem; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem; text-align: center; border: 1px solid {{ request('role') == 'admin' ? 'var(--text-primary)' : 'transparent' }}; @if(request('role') == 'admin') background-color: rgba(30, 41, 59, 0.05); @endif">
        <div style="color: var(--text-primary);">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
        </div>
        <span style="font-weight: 700; font-size: 0.9rem; color: var(--text-primary);">المدراء</span>
    </a>

    <a href="{{ route('admin.users.index', ['role' => 'doctor']) }}"
        class="card"
        style="padding: 1.5rem; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem; text-align: center; border: 1px solid {{ request('role') == 'doctor' ? 'var(--primary-color)' : 'transparent' }}; @if(request('role') == 'doctor') background-color: rgba(67, 56, 202, 0.05); @endif">
        <div style="color: var(--primary-color);">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
        </div>
        <span style="font-weight: 700; font-size: 0.9rem; color: var(--text-primary);">الدكاترة</span>
    </a>

    <a href="{{ route('admin.users.index', ['role' => 'delegate']) }}"
        class="card"
        style="padding: 1.5rem; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem; text-align: center; border: 1px solid {{ request('role') == 'delegate' ? 'var(--info-color)' : 'transparent' }}; @if(request('role') == 'delegate') background-color: rgba(59, 130, 246, 0.05); @endif">
        <div style="color: var(--info-color);">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="8.5" cy="7" r="4"></circle>
                <line x1="20" y1="8" x2="20" y2="14"></line>
            </svg>
        </div>
        <span style="font-weight: 700; font-size: 0.9rem; color: var(--text-primary);">المندوبين</span>
    </a>

    <a href="{{ route('admin.users.index', ['role' => 'student']) }}"
        class="card"
        style="padding: 1.5rem; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem; text-align: center; border: 1px solid {{ request('role') == 'student' ? 'var(--secondary-color)' : 'transparent' }}; @if(request('role') == 'student') background-color: rgba(100, 116, 139, 0.05); @endif">
        <div style="color: var(--secondary-color);">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 10v6M2 10v6M12 2l10 7-10 7-10-7zM22 10l-10 7-10-7"></path>
            </svg>
        </div>
        <span style="font-weight: 700; font-size: 0.9rem; color: var(--text-primary);">الطلاب</span>
    </a>

</div>

<!-- Users Table Card -->
<div class="card" x-data="{
    selectedUsers: [],
    selectAll: false,
    resetModalOpen: false,
    resetUserId: null,
    resetUserName: '',
    newPassword: '',
    generatePassword() {
        const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
        let pass = '';
        for(let i=0; i<8; i++) pass += chars.charAt(Math.floor(Math.random() * chars.length));
        this.newPassword = pass;
    }
}">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">قائمة المستخدمين</h3>
        <span class="badge badge-info" style="font-size: 0.85rem;">العدد: {{ $users->total() }}</span>
    </div>

    <!-- Bulk Actions Toolbar (appears when items selected) -->
    <div x-show="selectedUsers.length > 0" x-cloak
        style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border: 1px solid var(--border-color); border-radius: 10px; padding: 1rem; margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between;">
        <span style="font-weight: 600; color: var(--text-primary);">
            <span x-text="selectedUsers.length"></span> محدد
        </span>
        <div style="display: flex; gap: 0.75rem;">
            <form action="{{ route('admin.users.bulk-activate') }}" method="POST" style="display: inline;">
                @csrf
                <template x-for="id in selectedUsers" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <button type="submit" class="btn" style="background: #d1fae5; color: #059669; padding: 0.5rem 1rem; font-size: 0.9rem;">
                    ✓ تفعيل
                </button>
            </form>
            <form action="{{ route('admin.users.bulk-deactivate') }}" method="POST" style="display: inline;">
                @csrf
                <template x-for="id in selectedUsers" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <button type="submit" class="btn" style="background: #fef3c7; color: #d97706; padding: 0.5rem 1rem; font-size: 0.9rem;">
                    ⏸ تعطيل
                </button>
            </form>
            <form action="{{ route('admin.users.bulk-delete') }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف المستخدمين المحددين؟');">
                @csrf
                <template x-for="id in selectedUsers" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <button type="submit" class="btn" style="background: #fee2e2; color: #dc2626; padding: 0.5rem 1rem; font-size: 0.9rem;">
                    🗑 حذف
                </button>
            </form>
            <button @click="selectedUsers = []; selectAll = false" class="btn" style="background: #f1f5f9; color: #64748b; padding: 0.5rem 1rem; font-size: 0.9rem;">
                ✕ إلغاء
            </button>
        </div>
    </div>

    <div class="table-container">
        <div class="table-responsive">
<table>
            <thead>
                <tr>
                    <th style="width: 50px;">
                        <input type="checkbox" x-model="selectAll" @change="selectedUsers = selectAll ? [{{ $users->pluck('id')->filter(fn($id) => $id != auth()->id())->join(',') }}] : []" style="width: 18px; height: 18px; cursor: pointer;">
                    </th>
                    <th style="width: 50px;">#</th>
                    <th>المستخدم</th>
                    <th>الدور</th>
                    <th>تاريخ التسجيل</th>
                    <th>الحالة</th>
                    <th style="width: 120px;">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr :class="{ 'bg-blue-50': selectedUsers.includes({{ $user->id }}) }" style="transition: background-color 0.2s;" :style="selectedUsers.includes({{ $user->id }}) ? 'background-color: rgba(67, 56, 202, 0.05);' : ''">
                    <td>
                        @if($user->id !== auth()->id())
                        <input type="checkbox" value="{{ $user->id }}" x-model.number="selectedUsers" style="width: 18px; height: 18px; cursor: pointer;">
                        @endif
                    </td>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <!-- Improved Avatar -->
                            <div style="width: 38px; height: 38px; border-radius: 50%;
                                      background: {{ $user->role->value == 'admin' ? '#1f2937' : ($user->role->value == 'doctor' ? 'var(--primary-color)' : ($user->role->value == 'delegate' ? 'var(--info-color)' : '#9ca3af')) }};
                                      color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.9rem;">
                                {{ mb_substr($user->name, 0, 1) }}
                            </div>
                            <div>
                                <div style="font-weight: 700; color: var(--text-primary);">{{ $user->name }}</div>
                                <div style="font-size: 0.85rem; color: var(--text-secondary);">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($user->role->value == 'admin')
                        <span class="badge" style="background:#f3f4f6; color:#1f2937;">مدير</span>
                        @elseif($user->role->value == 'doctor')
                        <span class="badge badge-primary" style="background:rgba(67, 56, 202, 0.1); color:var(--primary-color);">دكتور</span>
                        @elseif($user->role->value == 'delegate')
                        <span class="badge badge-info">مندوب</span>
                        @else
                        <span class="badge" style="background:#f1f5f9; color:#64748b;">طالب</span>
                        @endif
                    </td>
                    <td style="color: var(--text-secondary); font-size: 0.9rem;">
                        {{ $user->created_at?->format('Y/m/d') ?? '-' }}
                    </td>
                    <td>
                        @if($user->status == 'active')
                        <span class="badge badge-success">فعال</span>
                        @else
                        <span class="badge badge-danger">موقوف</span>
                        @endif
                    </td>
                    <td>
                        @if($user->id !== auth()->id())
                        <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                            <!-- Reset Password Button -->
                            <button type="button" @click="resetUserId = {{ $user->id }}; resetUserName = '{{ str_replace("'", "\'", $user->name) }}'; newPassword = ''; resetModalOpen = true;" class="btn" style="padding: 0.4rem; background: #e0e7ff; color: #4338ca;" title="تمهيد كلمة المرور">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 18v3c0 .6.4 1 1 1h4v-3h3v-3h2l1.4-1.4a6.5 6.5 0 1 0-4-4Z"></path>
                                    <circle cx="16.5" cy="7.5" r=".5"></circle>
                                </svg>
                            </button>

                            <!-- Kick Session Button -->
                            <form action="{{ route('admin.users.kick', $user->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من طرد هذا المستخدم من الجلسة؟ سيتم تسجيل خروجه فوراً.');">
                                @csrf
                                <button type="submit" class="btn" style="padding: 0.4rem; background: #fee2e2; color: #991b1b;" title="طرد من الجلسة">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <polyline points="16 17 21 12 16 7"></polyline>
                                        <line x1="21" y1="12" x2="9" y2="12"></line>
                                    </svg>
                                </button>
                            </form>

                            <form action="{{ route('admin.users.status', $user->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn"
                                    style="padding: 0.4rem; background: {{ $user->status == 'active' ? '#fef3c7' : '#d1fae5' }}; color: {{ $user->status == 'active' ? '#d97706' : '#059669' }};"
                                    title="{{ $user->status == 'active' ? 'إيقاف الحساب' : 'تفعيل الحساب' }}">
                                    @if($user->status == 'active')
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                    @else
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                    @endif
                                </button>
                            </form>

                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn" style="padding: 0.4rem; background: #fee2e2; color: #dc2626;" title="حذف">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                    </svg>
                                </button>
                            </form>
                        </div>
                        @else
                        <span style="font-size: 0.8rem; color: var(--text-light);">حسابك</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-secondary);">لا يوجد مستخدمين.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div x-show="resetModalOpen" x-cloak class="modal-overlay" style="position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:9999;">
        <div @click.outside="resetModalOpen = false" class="modal-content" style="background:#fff; border-radius:12px; padding:2rem; width:100%; max-width:400px; box-shadow:0 10px 25px rgba(0,0,0,0.1);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                <h3 style="margin:0; font-size:1.2rem; color:var(--text-primary); display:flex; align-items:center; gap:0.5rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-color)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                       <path d="M2 18v3c0 .6.4 1 1 1h4v-3h3v-3h2l1.4-1.4a6.5 6.5 0 1 0-4-4Z"></path>
                       <circle cx="16.5" cy="7.5" r=".5"></circle>
                    </svg>
                    تغيير كلمة المرور
                </h3>
                <button type="button" @click="resetModalOpen = false" style="background:none; border:none; color:#999; cursor:pointer; font-size:1.5rem;">&times;</button>
            </div>
            
            <p style="color:var(--text-secondary); margin-bottom:1.5rem; font-size:0.95rem;">
                إعادة تعيين كلمة المرور للمستخدم: <strong x-text="resetUserName" style="color:var(--text-primary);"></strong>
            </p>

            <form :action="`{{ url('admin/users') }}/${resetUserId}/reset-password`" method="POST">
                @csrf
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600; color:var(--text-primary);">كلمة المرور الجديدة</label>
                    <div style="display:flex; gap:0.5rem;">
                        <input type="text" name="new_password" required x-model="newPassword" minlength="8"
                            style="flex:1; padding:0.75rem; border:1px solid var(--border-color); border-radius:8px; outline:none; font-family:monospace; font-size:1.1rem; letter-spacing:1px;"
                            placeholder="أدخل كلمة المرور الجديدة">
                        
                        <button type="button" @click="generatePassword()" class="btn" style="background:#f1f5f9; color:#475569; padding:0.75rem; border-radius:8px; border:1px solid var(--border-color);" title="توليد عشوائي">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="16 3 21 3 21 8"></polyline>
                                <line x1="4" y1="20" x2="21" y2="3"></line>
                                <polyline points="21 16 21 21 16 21"></polyline>
                                <line x1="15" y1="15" x2="21" y2="21"></line>
                                <line x1="4" y1="4" x2="9" y2="9"></line>
                            </svg>
                        </button>
                    </div>
                    <small style="color:var(--text-light); margin-top:0.5rem; display:block;">الحد الأدنى 8 أحرف. يمكنك كتابة كلمة مرور مخصصة أو الضغط على زر التوليد العشوائي لتوليد كلمة سر آمنة.</small>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:1rem;">
                    <button type="button" @click="resetModalOpen = false" class="btn btn-secondary" style="padding:0.7rem 1.25rem; border-radius:8px; border:1px solid var(--border-color); background: #fff; cursor:pointer;">إلغاء</button>
                    <button type="submit" class="btn btn-primary" style="padding:0.7rem 1.25rem; background:var(--primary-color); color:#fff; border-radius:8px; border:none; cursor:pointer;">تحديث الكلمة والنسخ</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Pagination -->
    <div style="padding-top: 1.5rem;">
        {{ $users->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
</div>

<style>
    /* Pagination Fixes */
    .pagination {
        display: flex;
        padding-left: 0;
        list-style: none;
        justify-content: center;
        gap: 0.25rem;
    }

    .page-link {
        position: relative;
        display: block;
        padding: 0.5rem 0.75rem;
        margin-left: -1px;
        line-height: 1.25;
        color: var(--text-primary);
        background-color: #fff;
        border: 1px solid var(--border-color);
        border-radius: 0.375rem;
        text-decoration: none;
        font-size: 0.875rem;
    }

    .page-item.active .page-link {
        z-index: 3;
        color: #fff;
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        cursor: auto;
        background-color: #fff;
        border-color: #dee2e6;
    }

    .page-link:hover {
        z-index: 2;
        color: var(--primary-hover);
        text-decoration: none;
        background-color: #e9ecef;
        border-color: #dee2e6;
    }

    /* Hide large SVGs in pagination if any */
    .page-link svg {
        width: 1rem;
        height: 1rem;
    }

    /* Hide the 'Showing X to Y' text */
    .d-none.d-md-block {
        display: none !important;
    }
</style>
@endsection