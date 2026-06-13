@extends('layouts.admin')

@section('title', 'منح النجوم للطلاب')

@section('content')
<style>
    .star-header {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        border-radius: 20px; padding: 2rem; color: white; margin-bottom: 2rem;
        box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.2);
    }
    .filter-card {
        background: white; border-radius: 16px; border: 1px solid #e2e8f0; padding: 1.5rem; margin-bottom: 1.5rem;
    }
    .student-table-card {
        background: white; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden;
    }
    .table th { background: #f8fafc; font-weight: 700; color: #475569; padding: 1rem; font-size: 0.85rem; }
    .table td { padding: 1rem; vertical-align: middle; font-size: 0.9rem; border-bottom: 1px solid #f1f5f9; }
    .student-info { display: flex; align-items: center; gap: 0.75rem; }
    .avatar-circle { width: 36px; height: 36px; border-radius: 50%; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem; }
    .stars-badge { background: #fffbeb; color: #b45309; padding: 0.25rem 0.6rem; border-radius: 99px; font-weight: 800; font-size: 0.8rem; border: 1px solid #fde68a; }
    
    /* Academic Path Styling */
    .aca-path { font-size: 0.8rem; color: #64748b; margin-top: 4px; display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
    .aca-path .ac-item { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-weight: 600; }
    
    .floating-actions {
        position: fixed; bottom: 2rem; left: 50%; transform: translateX(-50%);
        background: white; padding: 1rem 2rem; border-radius: 99px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);
        display: flex; gap: 1rem; align-items: center; z-index: 1000; border: 2px solid #f59e0b;
    }
    
    .role-badge { font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 6px; font-weight: 700; }
    .role-student { background: #e0e7ff; color: #4338ca; }
    .role-delegate { background: #fce7f3; color: #be185d; }
    .role-prac-delegate { background: #ecfccb; color: #4d7c0f; }
    .honor-board-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        overflow: hidden;
        margin-bottom: 1.5rem;
        box-shadow: 0 16px 42px rgba(15, 23, 42, 0.06);
    }
    .honor-board-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, #fff7ed, #ffffff);
        border-bottom: 1px solid #fed7aa;
    }
    .honor-board-title {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }
    .honor-board-title .icon {
        width: 46px;
        height: 46px;
        border-radius: 15px;
        display: grid;
        place-items: center;
        color: #b45309;
        background: #ffedd5;
    }
    .honor-board-title h2 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 900;
        color: #1e293b;
    }
    .honor-board-title p {
        margin: 0.15rem 0 0;
        font-size: 0.82rem;
        color: #64748b;
    }
    .honor-board-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
        justify-content: flex-end;
    }
    .honor-stat-pill {
        background: #fff;
        border: 1px solid #fde68a;
        color: #92400e;
        border-radius: 999px;
        padding: 0.42rem 0.75rem;
        font-size: 0.78rem;
        font-weight: 900;
        font-variant-numeric: tabular-nums;
    }
    .honor-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 0.85rem;
        padding: 1rem;
    }
    .honor-item-card {
        border: 1px solid #eef2f7;
        border-radius: 16px;
        padding: 0.9rem;
        background: #ffffff;
        display: flex;
        align-items: center;
        gap: 0.8rem;
        min-width: 0;
    }
    .honor-rank {
        width: 38px;
        height: 38px;
        border-radius: 13px;
        display: grid;
        place-items: center;
        flex: 0 0 38px;
        font-weight: 900;
        font-size: 0.9rem;
        background: #f1f5f9;
        color: #475569;
    }
    .honor-rank.rank-1 { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; }
    .honor-rank.rank-2 { background: linear-gradient(135deg, #94a3b8, #64748b); color: #fff; }
    .honor-rank.rank-3 { background: linear-gradient(135deg, #b45309, #92400e); color: #fff; }
    .honor-person {
        min-width: 0;
        flex: 1;
    }
    .honor-person strong {
        display: block;
        color: #0f172a;
        font-size: 0.9rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .honor-person small {
        display: block;
        color: #64748b;
        font-size: 0.75rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .honor-score {
        text-align: left;
        flex: 0 0 auto;
        color: #b45309;
        font-weight: 900;
        font-variant-numeric: tabular-nums;
    }
    .honor-score span {
        display: block;
        color: #94a3b8;
        font-size: 0.7rem;
        font-weight: 800;
    }
    .honor-empty {
        padding: 2rem;
        text-align: center;
        color: #64748b;
    }
</style>

<div class="star-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="fw-bold mb-1"><i class="fa-solid fa-star me-2"></i>إدارة النجوم والمكافآت</h1>
            <p class="mb-0 opacity-90">ابحث عن الطلاب أو المناديب وكافئهم بالنجوم، أو اخصم منهم لضبط السلوكيات.</p>
        </div>
        <div class="avatar-circle" style="width: 60px; height: 60px; font-size: 1.5rem; background: rgba(255,255,255,0.2); color: white; border: 2px solid rgba(255,255,255,0.3);">
            <i class="fa-solid fa-award"></i>
        </div>
    </div>
</div>

<div x-data="starManager()">
    {{-- Filters --}}
    <div class="filter-card">
        <form action="{{ route('admin.stars.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-12">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px;"><i class="fa-solid fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" style="border-radius: 0 12px 12px 0;" placeholder="ابحث بالاسم، الإيميل، رقم القيد..." value="{{ request('search') }}">
                    </div>
                </div>
                
                {{-- Cascading Academic Filters --}}
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">الجامعة</label>
                    <select name="university_id" class="form-select" style="border-radius: 12px;" x-model="university_id" @change="fetchColleges">
                        <option value="">كل الجامعات</option>
                        @foreach($universities as $uni) 
                            <option value="{{ $uni->id }}">{{ $uni->name }}</option> 
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">الكلية</label>
                    <select name="college_id" class="form-select" style="border-radius: 12px;" x-model="college_id" @change="fetchMajors" :disabled="!colleges.length">
                        <option value="">كل الكليات</option>
                        <template x-for="col in colleges" :key="col.id">
                            <option :value="col.id" x-text="col.name" :selected="col.id == college_id"></option>
                        </template>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">التخصص</label>
                    <select name="major_id" class="form-select" style="border-radius: 12px;" x-model="major_id" @change="fetchLevels" :disabled="!majors.length">
                        <option value="">كل التخصصات</option>
                        <template x-for="maj in majors" :key="maj.id">
                            <option :value="maj.id" x-text="maj.name" :selected="maj.id == major_id"></option>
                        </template>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">المستوى</label>
                    <select name="level_id" class="form-select" style="border-radius: 12px;" x-model="level_id" :disabled="!levels.length">
                        <option value="">كل المستويات</option>
                        <template x-for="lvl in levels" :key="lvl.id">
                            <option :value="lvl.id" x-text="lvl.name" :selected="lvl.id == level_id"></option>
                        </template>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-dark w-100" style="border-radius: 12px; height: 38px;">تصفية <i class="fa-solid fa-filter me-1"></i></button>
                    <a href="{{ route('admin.stars.index') }}" class="btn btn-light ms-2 border" style="border-radius: 12px; height: 38px;" title="إعادة ضبط"><i class="fa-solid fa-redo"></i></a>
                </div>
            </div>
        </form>
    </div>

    {{-- Honor Board --}}
    <section class="honor-board-card">
        <div class="honor-board-head">
            <div class="honor-board-title">
                <div class="icon"><i class="fa-solid fa-crown"></i></div>
                <div>
                    <h2>لوحة الشرف</h2>
                    <p>أفضل الطلاب والمناديب حسب الفلاتر الحالية، مرتبين حسب رصيد النجوم.</p>
                </div>
            </div>
            <div class="honor-board-stats">
                <span class="honor-stat-pill">المعروض: {{ number_format($honorStats['count']) }}</span>
                <span class="honor-stat-pill">أعلى رصيد: {{ number_format($honorStats['top_balance']) }}</span>
                <span class="honor-stat-pill">إجمالي المعروض: {{ number_format($honorStats['total_balance']) }}</span>
            </div>
        </div>

        @if($honorBoard->isNotEmpty())
            <div class="honor-grid">
                @foreach($honorBoard as $rank => $honorStudent)
                    <article class="honor-item-card">
                        <div class="honor-rank rank-{{ $rank + 1 <= 3 ? $rank + 1 : 'default' }}">{{ $rank + 1 }}</div>
                        <div class="avatar-circle">{{ mb_substr($honorStudent->name, 0, 1) }}</div>
                        <div class="honor-person">
                            <strong>{{ $honorStudent->name }}</strong>
                            <small>
                                {{ $honorStudent->student_number ?? $honorStudent->email }}
                                -
                                {{ $honorStudent->major->name ?? 'بدون تخصص' }}
                                /
                                {{ $honorStudent->level->name ?? 'بدون مستوى' }}
                            </small>
                        </div>
                        <div class="honor-score">
                            {{ number_format($honorStudent->stars_balance) }}
                            <span>نجمة</span>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="honor-empty">
                <i class="fa-solid fa-crown fa-2x mb-3 opacity-25"></i>
                <div class="fw-bold">لا توجد نتائج في لوحة الشرف ضمن الفلاتر الحالية.</div>
                <div class="small mt-1">جرّب توسيع نطاق الجامعة أو الكلية أو التخصص.</div>
            </div>
        @endif
    </section>

    {{-- Students Table --}}
    <form action="{{ route('admin.stars.grant') }}" method="POST">
        @csrf
        <div class="student-table-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="40"><input type="checkbox" x-model="selectAll" @change="toggleAll" class="form-check-input"></th>
                            <th>المستخدم</th>
                            <th>المسار الأكاديمي</th>
                            <th>الرصيد</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                        <tr>
                            <td><input type="checkbox" name="student_ids[]" value="{{ $student->id }}" x-model="selectedStudents" class="form-check-input student-checkbox"></td>
                            <td>
                                <div class="student-info">
                                    <div class="avatar-circle">{{ mb_substr($student->name, 0, 1) }}</div>
                                    <div>
                                        <div class="fw-bold d-flex align-items-center gap-2">
                                            {{ $student->name }}
                                            @if($student->role === \App\Enums\UserRole::DELEGATE)
                                                <span class="role-badge role-delegate">مندوب دفعة</span>
                                            @elseif($student->role === \App\Enums\UserRole::PRACTICAL_DELEGATE)
                                                <span class="role-badge role-prac-delegate">مندوب عملي</span>
                                            @else
                                                <span class="role-badge role-student">طالب</span>
                                            @endif
                                        </div>
                                        <div class="text-muted small dir-ltr text-start">{{ $student->student_number ?? $student->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="aca-path">
                                    <span class="ac-item" title="الجامعة">{{ $student->university->name ?? '—' }}</span> <i class="fa-solid fa-chevron-left fa-xs text-muted mx-1"></i>
                                    <span class="ac-item" title="الكلية">{{ $student->college->name ?? '—' }}</span> <i class="fa-solid fa-chevron-left fa-xs text-muted mx-1"></i>
                                    <span class="ac-item" title="التخصص">{{ $student->major->name ?? '—' }}</span> <i class="fa-solid fa-chevron-left fa-xs text-muted mx-1"></i>
                                    <span class="ac-item" title="المستوى">{{ $student->level->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td><span class="stars-badge"><i class="fa-solid fa-star me-1"></i> <span class="fs-6">{{ $student->stars_balance }}</span></span></td>
                            <td>
                                <span class="badge {{ $student->status === 'active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $student->status === 'active' ? 'نشط' : 'معطل' }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-users fa-3x mb-3 opacity-25"></i>
                                <h5>لا يوجد طلاب لعرضهم</h5>
                                <p>جرب تغيير خيارات الفلترة أو ابحث باسم آخر.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 bg-light border-top">{{ $students->appends(request()->query())->links() }}</div>
        </div>

        {{-- Floating Actions Panel (Visible when students selected) --}}
        <div class="floating-actions" x-show="selectedStudents.length > 0" x-transition style="display: none;">
            <div class="fw-bold text-dark me-2">
                تحديد: <span x-text="selectedStudents.length" class="text-danger fs-5">0</span>
            </div>
            
            <div class="input-group" style="width: 150px;">
                <span class="input-group-text bg-white text-muted border-end-0" style="border-radius: 12px 0 0 12px;"><i class="fa-solid fa-star text-warning"></i></span>
                <input type="number" name="amount" class="form-control border-start-0" style="border-radius: 0 12px 12px 0; font-weight: 800;" value="10" placeholder="المقدار" required>
            </div>
            
            <input type="text" name="description" class="form-control" style="width: 280px; border-radius: 12px;" placeholder="اكتب سبب العملية (مثال: مشاركة متميزة، إزعاج)..." required>
            
            <button type="submit" class="btn btn-warning fw-bold text-dark px-4" style="border-radius: 12px; height: 38px;">تنفيذ العملية <i class="fa-solid fa-bolt ms-1"></i></button>
            <div class="ms-2 small text-muted lh-sm text-end" style="width: 120px;">
                *يمكنك المنح (أرقام موجبة) أو الخصم (أرقام سالبة).
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('starManager', () => ({
            selectedStudents: [],
            selectAll: false,
            
            // Filters State
            university_id: '{{ request('university_id', '') }}',
            college_id: '{{ request('college_id', '') }}',
            major_id: '{{ request('major_id', '') }}',
            level_id: '{{ request('level_id', '') }}',

            colleges: [],
            majors: [],
            levels: [],

            init() {
                if (this.university_id) this.fetchColleges().then(() => {
                    if (this.college_id) this.fetchMajors().then(() => {
                        if (this.major_id) this.fetchLevels();
                    });
                });
            },

            toggleAll() {
                if (this.selectAll) {
                    this.selectedStudents = Array.from(document.querySelectorAll('.student-checkbox')).map(cb => cb.value);
                } else {
                    this.selectedStudents = [];
                }
            },

            async fetchColleges() {
                this.colleges = []; this.majors = []; this.levels = [];
                if (!this.university_id) return;
                const res = await fetch(`/api/public/colleges/${this.university_id}`);
                const result = await res.json();
                this.colleges = result.data || [];
            },

            async fetchMajors() {
                this.majors = []; this.levels = [];
                if (!this.college_id) return;
                const res = await fetch(`/api/public/majors/${this.college_id}`);
                const result = await res.json();
                this.majors = result.data || [];
            },

            async fetchLevels() {
                this.levels = [];
                if (!this.major_id) return;
                const res = await fetch(`/api/public/levels/${this.major_id}`);
                const result = await res.json();
                this.levels = result.data || [];
            }
        }));
    });
</script>
@endpush
@endsection
