@extends('layouts.admin')

@section('title', 'طلبات تعديل الحضور')

@section('content')

<div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; margin-bottom:2rem;">
    <div>
        <h1 style="font-size:1.5rem; font-weight:700; color:var(--text-primary);">طلبات تعديل الحضور</h1>
        <p style="color:var(--text-secondary); margin-top:0.5rem;">فتح تعديل سجلات الحضور يكون من الأدمن الرئيسي فقط ولمدة محددة.</p>
    </div>
    <span class="badge badge-warning" style="font-size:0.9rem;">{{ $pendingCount }} طلب معلق</span>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul style="margin:0; padding-right:1.25rem;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card" style="margin-bottom:1.5rem;">
    <div style="border-bottom:1px solid var(--border-color); padding-bottom:1rem; margin-bottom:1rem;">
        <h3 style="font-size:1.1rem; font-weight:700; color:var(--text-primary);">فتح تعديل يدوي</h3>
        <p style="font-size:0.9rem; color:var(--text-secondary); margin-top:0.35rem;">استخدمه عند تواصل الدكتور أو المندوب مباشرة مع الأدمن.</p>
    </div>

    <form action="{{ route('admin.attendance-edit-requests.grant') }}" method="POST">
        @csrf
        <div style="display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:1rem;">
            <div>
                <label style="font-weight:600; font-size:0.85rem;">الدكتور / المندوب</label>
                <select name="user_id" class="form-control" required>
                    <option value="">اختر المستخدم</option>
                    @foreach($editableUsers as $user)
                        <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                            {{ $user->name }} - {{ $user->role->value }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="font-weight:600; font-size:0.85rem;">محاضرة محددة</label>
                <select name="lecture_id" class="form-control">
                    <option value="">بدون محاضرة محددة</option>
                    @foreach($recentLectures as $lecture)
                        <option value="{{ $lecture->id }}" @selected(old('lecture_id') == $lecture->id)>
                            {{ $lecture->date?->format('Y-m-d') }} - {{ $lecture->subject?->name ?? 'بدون مادة' }} - {{ $lecture->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="font-weight:600; font-size:0.85rem;">المادة عند عدم اختيار محاضرة</label>
                <select name="subject_id" class="form-control">
                    <option value="">بدون مادة / غير رسمي</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>
                            {{ $subject->name }} {{ $subject->code ? "({$subject->code})" : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="font-weight:600; font-size:0.85rem;">التاريخ عند عدم اختيار محاضرة</label>
                <input type="date" name="date" class="form-control" value="{{ old('date') }}">
            </div>

            <div>
                <label style="font-weight:600; font-size:0.85rem;">مدة الفتح بالساعات</label>
                <input type="number" name="expires_hours" class="form-control" min="1" max="168" value="{{ old('expires_hours', 2) }}" required>
            </div>

            <div>
                <label style="font-weight:600; font-size:0.85rem;">سبب الفتح</label>
                <input type="text" name="reason" class="form-control" value="{{ old('reason') }}" placeholder="مثال: تصحيح خطأ رصد">
            </div>
        </div>

        <div style="margin-top:1rem;">
            <button class="btn btn-primary" type="submit">فتح التعديل</button>
        </div>
    </form>
</div>

<div class="card" style="margin-bottom:1.5rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border-color); padding-bottom:1rem; margin-bottom:1rem;">
        <h3 style="font-size:1.1rem; font-weight:700; color:var(--text-primary);">طلبات الفتح</h3>
        <form method="GET" style="display:flex; gap:0.5rem; align-items:center;">
            <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="pending" @selected($status === 'pending')>المعلقة</option>
                <option value="approved" @selected($status === 'approved')>المقبولة</option>
                <option value="rejected" @selected($status === 'rejected')>المرفوضة</option>
                <option value="all" @selected($status === 'all')>الكل</option>
            </select>
        </form>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>المستخدم</th>
                        <th>النطاق</th>
                        <th>السبب</th>
                        <th>الحالة</th>
                        <th>تاريخ الطلب</th>
                        <th style="width:260px; text-align:center;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $editRequest)
                        <tr>
                            <td>
                                <div style="font-weight:700;">{{ $editRequest->requester?->name ?? '-' }}</div>
                                <div style="font-size:0.85rem; color:var(--text-secondary);">
                                    {{ $editRequest->requester?->role?->value ?? '-' }}
                                    @if($editRequest->requester?->major || $editRequest->requester?->level)
                                        - {{ $editRequest->requester?->major?->name ?? '-' }} / {{ $editRequest->requester?->level?->name ?? '-' }}
                                    @endif
                                </div>
                            </td>
                            <td style="font-size:0.9rem;">
                                <strong>المادة:</strong> {{ $editRequest->subject?->name ?? 'غير رسمي / غير محدد' }}<br>
                                <strong>المحاضرة:</strong> {{ $editRequest->lecture?->title ?? $editRequest->qrSession?->title ?? $editRequest->title ?? '-' }}<br>
                                <strong>التاريخ:</strong> {{ $editRequest->date?->format('Y-m-d') ?? $editRequest->lecture?->date?->format('Y-m-d') ?? $editRequest->qrSession?->date?->format('Y-m-d') ?? '-' }}
                            </td>
                            <td style="max-width:280px;">{{ $editRequest->reason }}</td>
                            <td>
                                @if($editRequest->status === 'pending')
                                    <span class="badge badge-warning">معلق</span>
                                @elseif($editRequest->status === 'approved')
                                    <span class="badge badge-success">مقبول</span>
                                @elseif($editRequest->status === 'rejected')
                                    <span class="badge badge-danger">مرفوض</span>
                                @else
                                    <span class="badge">{{ $editRequest->status }}</span>
                                @endif
                                @if($editRequest->admin_note)
                                    <div style="font-size:0.8rem; color:var(--text-secondary); margin-top:0.35rem;">{{ $editRequest->admin_note }}</div>
                                @endif
                            </td>
                            <td style="font-size:0.85rem; color:var(--text-secondary);">
                                {{ $editRequest->created_at->format('Y-m-d H:i') }}<br>
                                {{ $editRequest->created_at->diffForHumans() }}
                            </td>
                            <td>
                                @if($editRequest->status === 'pending')
                                    <div style="display:grid; gap:0.5rem;">
                                        <form action="{{ route('admin.attendance-edit-requests.approve', $editRequest) }}" method="POST" style="display:grid; grid-template-columns:80px 1fr auto; gap:0.4rem;">
                                            @csrf
                                            <input type="number" name="expires_hours" class="form-control" min="1" max="168" value="2" title="الساعات">
                                            <input type="text" name="admin_note" class="form-control" placeholder="ملاحظة اختيارية">
                                            <button class="btn" style="background:#d1fae5; color:#059669;" type="submit">قبول</button>
                                        </form>
                                        <form action="{{ route('admin.attendance-edit-requests.reject', $editRequest) }}" method="POST" style="display:grid; grid-template-columns:1fr auto; gap:0.4rem;">
                                            @csrf
                                            <input type="text" name="admin_note" class="form-control" placeholder="سبب الرفض">
                                            <button class="btn" style="background:#fee2e2; color:#dc2626;" type="submit">رفض</button>
                                        </form>
                                    </div>
                                @else
                                    <span style="color:var(--text-secondary); font-size:0.85rem;">تمت المراجعة</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:3rem; color:var(--text-secondary);">لا توجد طلبات ضمن هذا الفلتر.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="padding-top:1rem;">
        {{ $requests->links('pagination::bootstrap-5') }}
    </div>
</div>

<div class="card">
    <div style="border-bottom:1px solid var(--border-color); padding-bottom:1rem; margin-bottom:1rem;">
        <h3 style="font-size:1.1rem; font-weight:700; color:var(--text-primary);">الصلاحيات النشطة</h3>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>المستخدم</th>
                        <th>النطاق</th>
                        <th>فتح بواسطة</th>
                        <th>ينتهي في</th>
                        <th style="width:120px; text-align:center;">الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activePermissions as $permission)
                        <tr>
                            <td>
                                <div style="font-weight:700;">{{ $permission->user?->name ?? '-' }}</div>
                                <div style="font-size:0.85rem; color:var(--text-secondary);">{{ $permission->user?->role?->value ?? '-' }}</div>
                            </td>
                            <td style="font-size:0.9rem;">
                                <strong>المادة:</strong> {{ $permission->subject?->name ?? 'غير رسمي / غير محدد' }}<br>
                                <strong>المحاضرة:</strong> {{ $permission->lecture?->title ?? $permission->qrSession?->title ?? '-' }}<br>
                                <strong>التاريخ:</strong> {{ $permission->date?->format('Y-m-d') ?? $permission->lecture?->date?->format('Y-m-d') ?? $permission->qrSession?->date?->format('Y-m-d') ?? '-' }}
                            </td>
                            <td>{{ $permission->grantedBy?->name ?? '-' }}</td>
                            <td>
                                <div style="font-weight:700;">{{ $permission->expires_at->format('Y-m-d H:i') }}</div>
                                <div style="font-size:0.8rem; color:var(--text-secondary);">{{ $permission->expires_at->diffForHumans() }}</div>
                            </td>
                            <td style="text-align:center;">
                                <form action="{{ route('admin.attendance-edit-permissions.revoke', $permission) }}" method="POST" onsubmit="return confirm('إغلاق صلاحية التعديل الآن؟');">
                                    @csrf
                                    <button class="btn" style="background:#fee2e2; color:#dc2626;" type="submit">إغلاق</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:3rem; color:var(--text-secondary);">لا توجد صلاحيات نشطة الآن.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="padding-top:1rem;">
        {{ $activePermissions->links('pagination::bootstrap-5') }}
    </div>
</div>

@endsection
