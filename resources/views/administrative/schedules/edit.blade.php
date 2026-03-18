@extends('layouts.administrative')

@section('title', 'تعديل موعد محاضرة')

@section('content')

<style>
    .form-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border-radius: 24px;
        padding: 2.5rem;
        color: white;
        margin-bottom: 2.5rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    .form-hero::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -10%;
        width: 80%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
        transform: rotate(-15deg);
    }

    .premium-card {
        background: white;
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        padding: 2.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .premium-input {
        background-color: #f8fafc;
        border: 2px solid #f1f5f9;
        border-radius: 14px;
        padding: 0.85rem 1.25rem;
        font-weight: 700;
        color: #1e293b;
        transition: all 0.2s;
        text-align: right;
    }

    .premium-input:focus {
        border-color: #6366f1;
        background: white;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        outline: none;
    }

    .sidebar-actions {
        background: white;
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        padding: 2rem;
        position: sticky;
        top: 2rem;
    }

    .doctor-display {
        background: #f0f9ff;
        border: 1px solid #e0f2fe;
        padding: 1.25rem;
        border-radius: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1rem;
    }

    .invalid-feedback {
        font-weight: 700;
        font-size: 0.8rem;
        margin-top: 0.5rem;
    }
</style>

<div class="form-hero">
    <div style="display: flex; gap: 1.5rem; align-items: center; position: relative; z-index: 2;">
        <a href="{{ route('administrative.schedules.index') }}" style="width: 50px; height: 50px; background: rgba(255,255,255,0.2); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; backdrop-filter: blur(10px); transition: all 0.3s;">
            <i class="fa-solid fa-arrow-right"></i>
        </a>
        <div>
            <span style="font-size: 0.85rem; font-weight: 800; opacity: 0.8; text-transform: uppercase; letter-spacing: 1px;">تحديث الجدول الدراسي</span>
            <h1 style="font-size: 2rem; font-weight: 900; margin: 0;">تعديل تفاصيل المحاضرة</h1>
        </div>
    </div>
</div>

<div class="container-fluid" x-data="scheduleForm()">
    <form action="{{ route('administrative.schedules.update', $schedule) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="premium-card text-end">
                    <h5 style="font-size: 1.25rem; font-weight: 900; color: #1e293b; margin-bottom: 2rem; border-right: 4px solid #0ea5e9; padding-right: 1rem;">تخصيص الموعد</h5>
                    
                    <div class="row g-4">
                        <div class="col-md-6 mb-3">
                            <label style="display: block; font-weight: 800; color: #64748b; font-size: 0.85rem; margin-bottom: 0.75rem;">التخصص الدراسي</label>
                            <select name="major_id" class="form-select premium-input @error('major_id') is-invalid @enderror" x-model="majorId" @change="loadLevels()" required>
                                <option value="">-- اختر التخصص --</option>
                                @foreach($majors as $major)
                                    <option value="{{ $major->id }}">{{ $major->name }}</option>
                                @endforeach
                            </select>
                            @error('major_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label style="display: block; font-weight: 800; color: #64748b; font-size: 0.85rem; margin-bottom: 0.75rem;">المستوى الدراسي</label>
                            <select name="level_id" class="form-select premium-input @error('level_id') is-invalid @enderror" x-model="levelId" @change="loadSubjects()" :disabled="!majorId" required>
                                <option value="">-- اختر المستوى --</option>
                                <template x-for="level in levels" :key="level.id">
                                    <option :value="level.id" x-text="level.name" :selected="level.id == levelId"></option>
                                </template>
                            </select>
                            @error('level_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label style="display: block; font-weight: 800; color: #64748b; font-size: 0.85rem; margin-bottom: 0.75rem;">المادة الدراسية</label>
                            <select name="subject_id" class="form-select premium-input @error('subject_id') is-invalid @enderror" x-model="subjectId" @change="updateDoctor()" :disabled="!levelId" required>
                                <option value="">-- اختر المادة --</option>
                                <template x-for="sub in subjects" :key="sub.id">
                                    <option :value="sub.id" x-text="sub.name" :selected="sub.id == subjectId"></option>
                                </template>
                            </select>
                            
                            <div class="doctor-display" x-show="doctorName">
                                <span style="font-weight: 800; color: #1e293b;"><i class="fa-solid fa-user-tie text-info me-2"></i> المحاضر المسؤول:</span>
                                <span style="font-weight: 900; color: #0284c7;" x-text="doctorName"></span>
                            </div>
                            @error('subject_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12"><hr style="opacity: 0.05; margin: 1.5rem 0;"></div>

                        <div class="col-md-4 mb-3">
                            <label style="display: block; font-weight: 800; color: #64748b; font-size: 0.85rem; margin-bottom: 0.75rem;">اليوم الأسبوعي</label>
                            <select name="day_of_week" class="form-select premium-input @error('day_of_week') is-invalid @enderror" required>
                                @php $days = [6 => 'السبت', 7 => 'الأحد', 1 => 'الإثنين', 2 => 'الثلاثاء', 3 => 'الأربعاء', 4 => 'الخميس', 5 => 'الجمعة']; @endphp
                                @foreach($days as $id => $name)
                                    <option value="{{ $id }}" {{ $schedule->day_of_week == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('day_of_week') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label style="display: block; font-weight: 800; color: #64748b; font-size: 0.85rem; margin-bottom: 0.75rem;">وقت البدء</label>
                            <input type="time" name="start_time" class="form-control premium-input @error('start_time') is-invalid @enderror" value="{{ substr($schedule->start_time, 0, 5) }}" required>
                            @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label style="display: block; font-weight: 800; color: #64748b; font-size: 0.85rem; margin-bottom: 0.75rem;">وقت الانتهاء</label>
                            <input type="time" name="end_time" class="form-control premium-input @error('end_time') is-invalid @enderror" value="{{ substr($schedule->end_time, 0, 5) }}" required>
                            @error('end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 mt-4">
                            <label style="display: block; font-weight: 800; color: #64748b; font-size: 0.85rem; margin-bottom: 0.75rem;">القاعة الدراسية</label>
                            <input type="text" name="hall_name" class="form-control premium-input @error('hall_name') is-invalid @enderror" value="{{ $schedule->hall_name }}" placeholder="مثلاً: قاعة 204، بنك المعلومات" required>
                            @error('hall_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sidebar-actions shadow-sm text-center">
                    <div style="margin-bottom: 2.5rem;">
                        <div style="width: 70px; height: 70px; background: #e0f2fe; color: #0284c7; border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2rem; transform: rotate(5deg);">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </div>
                        <h5 style="font-weight: 900; color: #1e293b;">حفظ التعديلات</h5>
                        <p style="color: #64748b; font-size: 0.9rem; font-weight: 500;">عند حفظ التعديلات، سيتم تحديث جدول المحاضرات لدى جميع الطلاب المشمولين فوراً.</p>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <button type="submit" style="width: 100%; padding: 1rem; background: #0f172a; color: white; border: none; border-radius: 14px; font-weight: 800; font-size: 1.1rem; box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.2); transition: all 0.3s;" onmouseover="this.style.background='#1e293b'; this.style.transform='translateY(-2px)'" onmouseout="this.style.background='#0f172a'; this.style.transform='none'">
                            <i class="fa-solid fa-check-double me-2"></i> تطبيق التغييرات
                        </button>
                        
                        <a href="{{ route('administrative.schedules.index') }}" style="display: block; padding: 1rem; background: #fff; color: #64748b; border: 1px solid #e2e8f0; border-radius: 14px; text-decoration: none; font-weight: 800; font-size: 0.95rem; transition: all 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">إلغاء التعديل</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function scheduleForm() {
    return {
        majorId: '{{ old('major_id', $schedule->subject->major_id) }}',
        levelId: '{{ old('level_id', $schedule->subject->level_id) }}',
        subjectId: '{{ old('subject_id', $schedule->subject_id) }}',
        levels: [],
        subjects: @json($subjects),
        doctorName: '{{ $schedule->subject->doctor->name ?? "غير محدد" }}',
        
        async loadLevels() {
            if (!this.majorId) {
                this.levels = [];
                this.levelId = '';
                return;
            }
            try {
                const response = await fetch(`/administrative/exams/helper/levels/${this.majorId}`);
                this.levels = await response.json();
                this.levelId = '';
                this.subjects = [];
                this.subjectId = '';
                this.doctorName = '';
            } catch (error) {
                console.error('Error fetching levels:', error);
            }
        },
        
        async loadSubjects() {
            if (!this.levelId) {
                this.subjects = [];
                this.subjectId = '';
                this.doctorName = '';
                return;
            }
            try {
                const response = await fetch(`/administrative/schedules/helper/subjects/${this.levelId}`);
                this.subjects = await response.json();
                this.subjectId = '';
                this.doctorName = '';
            } catch (error) {
                console.error('Error fetching subjects:', error);
            }
        },
        
        updateDoctor() {
            if (!this.subjectId) {
                this.doctorName = '';
                return;
            }
            const subject = this.subjects.find(s => s.id == this.subjectId);
            this.doctorName = subject && subject.doctor ? subject.doctor.name : 'غير محدد';
        },

        async init() {
            if (this.majorId) {
                // Fetch levels to populate the dropdown
                const response = await fetch(`/administrative/exams/helper/levels/${this.majorId}`);
                this.levels = await response.json();
                this.levelId = '{{ old('level_id', $schedule->subject->level_id) }}';
                
                // Load subjects for the current level
                if (this.levelId) {
                    const subResponse = await fetch(`/administrative/schedules/helper/subjects/${this.levelId}`);
                    this.subjects = await subResponse.json();
                    this.subjectId = '{{ old('subject_id', $schedule->subject_id) }}';
                }
            }
        }
    }
}
</script>
@endpush

@endsection
