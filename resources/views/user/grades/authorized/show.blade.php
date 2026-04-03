@extends(Auth::user()->role === \App\Enums\UserRole::DELEGATE ? 'layouts.delegate' : 'layouts.student')

@section('title', 'رصد درجات ' . $category->name)

@section('content')
@push('styles')
<style>
    .premium-header {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        border-radius: 24px;
        padding: 2.5rem;
        color: white;
        position: relative;
        overflow: hidden;
        margin-bottom: 2rem;
        box-shadow: 0 20px 40px -15px rgba(59, 130, 246, 0.35);
    }

    .premium-header::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        filter: blur(60px);
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 24px;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
    }

    .btn-premium {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        border: none;
        padding: 0.8rem 2.5rem;
        border-radius: 16px;
        font-weight: 800;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-premium:hover {
        transform: scale(1.02);
        box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.4);
        color: white;
    }

    .btn-glass {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 0.6rem 1.5rem;
        border-radius: 14px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-glass:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
    }

    .grading-table th {
        background: #f8fafc;
        border: none;
        padding: 1.25rem 1rem;
        color: #64748b;
        font-weight: 800;
        font-size: 0.85rem;
        text-transform: uppercase;
    }

    .grading-table td {
        padding: 1.25rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .student-avatar {
        width: 40px;
        height: 40px;
        background: #eff6ff;
        color: #3b82f6;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1rem;
    }

    .grade-input {
        width: 100px;
        padding: 0.75rem;
        border: 2px solid #f1f5f9;
        border-radius: 12px;
        text-align: center;
        font-weight: 900;
        color: #1d4ed8;
        transition: all 0.2s;
    }

    .grade-input:focus {
        border-color: #3b82f6;
        outline: none;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-weight: 800;
        font-size: 0.8rem;
    }
</style>
@endpush

<div class="premium-header">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 position-relative" style="z-index: 2;">
        <div>
            <span class="badge px-3 py-2 rounded-pill mb-3 fw-700" style="background: rgba(255,255,255,0.2); color: white;">جاري رصد الدرجات</span>
            <h1 class="fw-900 mb-2" style="font-size: 2.2rem;">{{ $category->name }}</h1>
            <p class="text-white text-opacity-80 fw-700 m-0"><i class="fa-solid fa-book-open me-2"></i>{{ $category->subject->name }} | الدرجة العظمى: {{ $category->max_score }}</p>
        </div>
        <div>
            <a href="{{ route(Auth::user()->role === \App\Enums\UserRole::DELEGATE ? 'delegate.authorized-grades.index' : 'student.authorized-grades.index') }}" 
               class="btn-glass">
                <i class="fa-solid fa-arrow-right me-2"></i> العودة للمهام
            </a>
        </div>
    </div>
</div>

<div class="alert alert-primary border-0 shadow-sm rounded-4 mb-4 fw-700 p-3 d-flex align-items-center gap-3">
    <div class="bg-primary bg-opacity-10 p-2 rounded-3">
        <i class="fa-solid fa-info-circle fs-4 text-primary"></i>
    </div>
    <div>
        <div class="text-primary">ملاحظة هامة</div>
        <div class="small opacity-75">الدرجات التي ترصدها ستنتقل للمراجعة من قبل الدكتور ولن تظهر في سجلات الطلاب إلا بعد اعتمادها.</div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 fw-700 p-3 d-flex align-items-center gap-3">
        <div class="bg-danger bg-opacity-10 p-2 rounded-3">
            <i class="fa-solid fa-triangle-exclamation fs-4 text-danger"></i>
        </div>
        <div>
            <div class="text-danger">توجد أخطاء في البيانات المدخلة</div>
            <ul class="small mb-0 opacity-75 list-unstyled">
                @foreach($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 fw-700 p-3 d-flex align-items-center gap-3">
        <div class="bg-danger bg-opacity-10 p-2 rounded-3">
            <i class="fa-solid fa-circle-xmark fs-4 text-danger"></i>
        </div>
        <div class="text-danger">{{ session('error') }}</div>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 fw-700 p-3 d-flex align-items-center gap-3">
        <div class="bg-success bg-opacity-10 p-2 rounded-3">
            <i class="fa-solid fa-circle-check fs-4 text-success"></i>
        </div>
        <div class="text-success">{{ session('success') }}</div>
    </div>
@endif

<div class="glass-card overflow-hidden mb-5">
    <form action="{{ route(Auth::user()->role === \App\Enums\UserRole::DELEGATE ? 'delegate.authorized-grades.store' : 'student.authorized-grades.store', $category->id) }}" method="POST">
        @csrf
        <div class="table-responsive">
            <table class="table grading-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">الطالب</th>
                        <th class="text-center">الرقم الجامعي</th>
                        <th class="text-center">الدرجة (من {{ $category->max_score }})</th>
                        <th class="text-center">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    @php $currentGrade = $student->grades->first(); @endphp
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="student-avatar">
                                    {{ mb_substr($student->name, 0, 1) }}
                                </div>
                                <div class="fw-800 text-dark">{{ $student->name }}</div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-secondary rounded-3 px-3 py-2 fw-700">
                                {{ $student->student_number }}
                            </span>
                        </td>
                        <td class="text-center">
                            <input type="hidden" name="grades[{{ $loop->index }}][student_id]" value="{{ $student->id }}">
                            <input type="number" name="grades[{{ $loop->index }}][score]" 
                                   class="grade-input @error('grades.'.$loop->index.'.score') is-invalid border-danger @enderror" 
                                   step="0.5" min="0" max="{{ $category->max_score }}"
                                   value="{{ old('grades.'.$loop->index.'.score', ($currentGrade ? $currentGrade->score : '')) }}"
                                   placeholder="0.0">
                            @error('grades.'.$loop->index.'.score')
                                <div class="text-danger small fw-700 mt-1" style="font-size: 0.75rem;">{{ $message }}</div>
                            @enderror
                        </td>
                        <td class="text-center">
                            @if($currentGrade)
                                @if($currentGrade->status === 'pending')
                                    <span class="status-badge bg-warning bg-opacity-10 text-warning">قيد المراجعة</span>
                                @elseif($currentGrade->status === 'approved')
                                    <span class="status-badge bg-success bg-opacity-10 text-success">معتمدة</span>
                                @elseif($currentGrade->status === 'rejected')
                                    <span class="status-badge bg-danger bg-opacity-10 text-danger">مرفوضة</span>
                                @endif
                            @else
                                <span class="text-secondary opacity-50 small fw-700">لم يتم الرصد</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="p-4 bg-light bg-opacity-50 border-top">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-secondary small fw-700">
                    <i class="fa-solid fa-check-double me-1 text-primary"></i> تأكد من مراجعة الدرجات قبل الحفظ
                </div>
                <button type="submit" class="btn btn-premium shadow-lg">
                    حفظ وإرسال للمراجعة <i class="fa-solid fa-paper-plane ms-2"></i>
                </button>
            </div>
        </div>
    </form>
</div>

@if(Auth::user()->role === \App\Enums\UserRole::DELEGATE && empty($helperTask))
<div class="glass-card overflow-hidden mb-5">
    <div class="p-4 border-bottom bg-light bg-opacity-50">
        <h4 class="fw-900 mb-1">تفويض مساعدين لرصد الدرجات</h4>
        <p class="text-secondary mb-0 small">يمكنك إنشاء مهمة مساعدة كاملة أو جزئية لطلاب محددين. يبقى الاعتماد النهائي للدكتور فقط.</p>
    </div>

    <form action="{{ route('delegate.authorized-grades.helpers.store', $category->id) }}" method="POST" class="p-4">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-700">عنوان المهمة</label>
                <input type="text" name="title" class="form-control" placeholder="مثال: رصد درجات المجموعة الأولى" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-700">المساعد</label>
                <select name="helper_user_id" class="form-select" required>
                    <option value="">اختر الطالب أو المندوب</option>
                    @foreach(($delegateHelperStudentScope ?? collect()) as $candidate)
                    <option value="{{ $candidate->id }}">{{ $candidate->name }} - {{ $candidate->student_number }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-700">نوع التفويض</label>
                <select name="delegation_type" class="form-select">
                    <option value="full">كامل على الفئة</option>
                    <option value="partial">جزئي على طلاب محددين</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-700">مهلة المهمة</label>
                <input type="datetime-local" name="due_at" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-700">ملاحظات</label>
                <input type="text" name="notes" class="form-control" placeholder="تفاصيل إضافية">
            </div>
            <div class="col-12">
                <label class="form-label fw-700">الطلاب المحددون للتفويض الجزئي</label>
                <select name="student_ids[]" class="form-select" multiple size="8">
                    @foreach($delegateHelperCandidates as $candidate)
                    <option value="{{ $candidate->id }}">{{ $candidate->name }} - {{ $candidate->student_number }}</option>
                    @endforeach
                </select>
                <div class="form-text">يُستخدم هذا الحقل فقط عند اختيار تفويض جزئي.</div>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-between align-items-center">
            <div class="small text-secondary">التفويض الكامل يسمح للمساعد بإدخال درجات جميع طلاب هذه الفئة، أما الجزئي فيقصره على الطلاب المحددين فقط.</div>
            <button type="submit" class="btn btn-premium">
                إنشاء مهمة مساعدة <i class="fa-solid fa-user-plus ms-2"></i>
            </button>
        </div>
    </form>

    @if(($delegateHelperTasks ?? collect())->isNotEmpty())
    <div class="table-responsive border-top">
        <table class="table mb-0">
            <thead class="table-light">
                <tr>
                    <th>المهمة</th>
                    <th>المساعد</th>
                    <th>النوع</th>
                    <th>المهلة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($delegateHelperTasks as $task)
                <tr>
                    <td>
                        <div class="fw-700">{{ $task->title }}</div>
                        <div class="small text-secondary">{{ $task->notes ?: 'بدون ملاحظات إضافية' }}</div>
                    </td>
                    <td>{{ $task->helperUser->name ?? '-' }}</td>
                    <td>{{ $task->delegation_type === 'partial' ? 'جزئي' : 'كامل' }}</td>
                    <td>{{ $task->due_at?->format('Y-m-d H:i') ?? 'غير محددة' }}</td>
                    <td class="text-end">
                        <form action="{{ route('delegate.authorized-grades.helpers.revoke', $task) }}" method="POST" onsubmit="return confirm('هل تريد سحب هذه المهمة؟');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">سحب المهمة</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@elseif(!empty($helperTask))
<div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4 fw-700 p-3">
    تعمل هنا كمساعد رصد. نطاق هذه المهمة: {{ $helperTask->delegation_type === 'partial' ? 'طلاب محددون فقط' : 'كل طلاب الفئة' }}.
</div>
@endif
@endsection
