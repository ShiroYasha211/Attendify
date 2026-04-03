@extends('layouts.student')

@section('title', 'تعديل Oneline Shot')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="h3 fw-black text-dark mb-1">تعديل حزمة Oneline Shot</h1>
            <p class="text-secondary mb-0 fw-bold small">{{ $flashcard->title }}</p>
        </div>
        <a href="{{ route('student.flashcards.show', $flashcard) }}" class="btn btn-light fw-bold rounded-3 px-3 py-2 shadow-sm border d-inline-flex align-items-center gap-2">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            العودة للبطاقات
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4 p-3 px-4">
            <div class="fw-black mb-2"><i class="fa-solid fa-circle-exclamation me-2"></i> حدثت بعض الأخطاء:</div>
            <ul class="mb-0 small fw-bold">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('student.flashcards.update', $flashcard) }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-4">
            <!-- Main Content Area -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-5 p-4 p-md-5 bg-white h-100">
                    <div class="mb-4">
                        <label class="form-label fw-black text-secondary small mb-2">عنوان الحزمة <span class="text-danger">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $flashcard->title) }}" required 
                               class="form-control form-control-lg border-2 bg-light rounded-4 fw-bold shadow-none" 
                               placeholder="مثال: أهم النقاط في علم الأدوية">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-black text-secondary small mb-2">وصف مختصر</label>
                        <textarea name="description" rows="2" 
                                  class="form-control border-2 bg-light rounded-4 fw-bold shadow-none" 
                                  placeholder="وصف يساعدك على تذكر هدف الحزمة...">{{ old('description', $flashcard->description) }}</textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-black text-secondary small mb-2">وضع العرض <span class="text-danger">*</span></label>
                            <select name="display_mode" required class="form-select border-2 bg-light rounded-4 fw-bold shadow-none p-3">
                                <option value="flash_card" {{ old('display_mode', $flashcard->display_mode) == 'flash_card' ? 'selected' : '' }}>بطاقة (وجهين)</option>
                                <option value="one_line" {{ old('display_mode', $flashcard->display_mode) == 'one_line' ? 'selected' : '' }}>نص واحد</option>
                                <option value="qa" {{ old('display_mode', $flashcard->display_mode) == 'qa' ? 'selected' : '' }}>سؤال وجواب</option>
                                <option value="mcq" {{ old('display_mode', $flashcard->display_mode) == 'mcq' ? 'selected' : '' }}>اختيار من متعدد</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-black text-secondary small mb-2">دورة التكرار <span class="text-danger">*</span></label>
                            <select name="repeat_cycle" required class="form-select border-2 bg-light rounded-4 fw-bold shadow-none p-3">
                                <option value="daily" {{ old('repeat_cycle', $flashcard->repeat_cycle) == 'daily' ? 'selected' : '' }}>يومي</option>
                                <option value="weekly" {{ old('repeat_cycle', $flashcard->repeat_cycle) == 'weekly' ? 'selected' : '' }}>أسبوعي</option>
                                <option value="monthly" {{ old('repeat_cycle', $flashcard->repeat_cycle) == 'monthly' ? 'selected' : '' }}>شهري</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-black text-secondary small mb-2">عدد إشعارات اليوم</label>
                            <input type="number" name="daily_notification_count" value="{{ old('daily_notification_count', $flashcard->daily_notification_count) }}" min="1" max="50"
                                   class="form-control border-2 bg-light rounded-4 fw-bold shadow-none p-3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-black text-secondary small mb-2">تمييز باللون</label>
                            <div class="d-flex align-items-center gap-2 bg-light border-2 border rounded-4 p-2">
                                <input type="color" name="color" value="{{ old('color', $flashcard->color) }}"
                                       class="form-control form-control-color border-0 rounded-3 shadow-none p-0 bg-transparent" style="width: 45px; height: 35px;">
                                <span class="text-secondary small fw-bold">اختر لوناً مميزاً</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Side Panel Area -->
            <div class="col-lg-4">
                <div class="d-flex flex-column gap-4 h-100">
                    <div class="card border-0 shadow-sm rounded-5 p-4 bg-white">
                        <h4 class="h6 fw-black text-dark mb-2">فترة الصمت (عدم الإزعاج)</h4>
                        <p class="text-secondary small fw-bold mb-4">لن يتم إرسال إشعارات تلقائية خلال هذه الفترة المحددة</p>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small mb-1">بداية التوقف</label>
                            <input type="time" name="quiet_start" value="{{ old('quiet_start', $flashcard->quiet_start ? \Carbon\Carbon::parse($flashcard->quiet_start)->format('H:i') : '23:00') }}"
                                   class="form-control border-2 bg-light rounded-3 fw-bold shadow-none">
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold text-secondary small mb-1">نهاية التوقف</label>
                            <input type="time" name="quiet_end" value="{{ old('quiet_end', $flashcard->quiet_end ? \Carbon\Carbon::parse($flashcard->quiet_end)->format('H:i') : '07:00') }}"
                                   class="form-control border-2 bg-light rounded-3 fw-bold shadow-none">
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-5 p-4 bg-white">
                        <div class="form-check form-switch p-0 d-flex align-items-center justify-content-between">
                            <label class="form-check-label fw-black text-dark cursor-pointer" for="notifySwitch">تفعيل الإشعارات</label>
                            <input type="hidden" name="notifications_enabled" value="0">
                            <input class="form-check-input ms-0 border-0 shadow-none bg-secondary" type="checkbox" role="switch" id="notifySwitch" 
                                   name="notifications_enabled" value="1" {{ old('notifications_enabled', $flashcard->notifications_enabled) ? 'checked' : '' }}
                                   style="width: 45px; height: 24px;">
                        </div>
                    </div>

                    <div class="mt-auto">
                        <button type="submit" class="btn btn-primary w-100 rounded-4 py-3 fw-black fs-5 shadow-lg border-0 transition-all d-flex align-items-center justify-content-center gap-2">
                            حفظ التعديلات 
                            <i class="fas fa-save"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .fw-black { font-weight: 900; }
    .btn-primary { background: linear-gradient(135deg, #4f46e5, #7c3aed); }
    .btn-primary:hover { opacity: 0.9; transform: translateY(-2px); }
    .form-switch .form-check-input:checked { background-color: #10b981 !important; }
    .cursor-pointer { cursor: pointer; }
</style>
@endsection
