<div class="row g-4">
    <!-- Main Content Area -->
    <div class="col-lg-8">
        <div class="premium-card p-4 p-md-5">
            <h5 class="fw-black mb-4 d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-info text-primary"></i>
                معلومات الحزمة
            </h5>

            <div class="mb-4">
                <label class="form-label fw-black text-secondary small mb-2">عنوان الحزمة <span class="text-danger">*</span></label>
                <input type="text" name="title" value="{{ old('title', $flashcard->title ?? '') }}" required 
                       class="form-control form-control-lg border-2 bg-light rounded-4 fw-bold shadow-none p-3" 
                       placeholder="مثال: أهم النقاط في علم الأدوية">
            </div>

            <div class="mb-4">
                <label class="form-label fw-black text-secondary small mb-2">وصف مختصر</label>
                <textarea name="description" rows="3" 
                          class="form-control border-2 bg-light rounded-4 fw-bold shadow-none p-3" 
                          placeholder="اكتب وصفاً ليساعد الطلاب على فهم محتوى الحزمة...">{{ old('description', $flashcard->description ?? '') }}</textarea>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-black text-secondary small mb-2">وضع العرض <span class="text-danger">*</span></label>
                    <select name="display_mode" required class="form-select border-2 bg-light rounded-4 fw-bold shadow-none p-3">
                        <option value="flash_card" {{ old('display_mode', $flashcard->display_mode ?? 'flash_card') == 'flash_card' ? 'selected' : '' }}>بطاقة (وجهين)</option>
                        <option value="one_line" {{ old('display_mode', $flashcard->display_mode ?? '') == 'one_line' ? 'selected' : '' }}>نص واحد (إشعار)</option>
                        <option value="qa" {{ old('display_mode', $flashcard->display_mode ?? '') == 'qa' ? 'selected' : '' }}>سؤال وجواب</option>
                        <option value="mcq" {{ old('display_mode', $flashcard->display_mode ?? '') == 'mcq' ? 'selected' : '' }}>اختيار من متعدد</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-black text-secondary small mb-2">دورة التكرار <span class="text-danger">*</span></label>
                    <select name="repeat_cycle" required class="form-select border-2 bg-light rounded-4 fw-bold shadow-none p-3">
                        <option value="daily" {{ old('repeat_cycle', $flashcard->repeat_cycle ?? 'daily') == 'daily' ? 'selected' : '' }}>يومي</option>
                        <option value="weekly" {{ old('repeat_cycle', $flashcard->repeat_cycle ?? '') == 'weekly' ? 'selected' : '' }}>أسبوعي</option>
                        <option value="monthly" {{ old('repeat_cycle', $flashcard->repeat_cycle ?? '') == 'monthly' ? 'selected' : '' }}>شهري</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-black text-secondary small mb-2">عدد إشعارات اليوم</label>
                    <input type="number" name="daily_notification_count" value="{{ old('daily_notification_count', $flashcard->daily_notification_count ?? 5) }}" min="1" max="50"
                           class="form-control border-2 bg-light rounded-4 fw-bold shadow-none p-3">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-black text-secondary small mb-2">التصنيف (للمتجر)</label>
                    <input type="text" name="category" value="{{ old('category', $flashcard->storeEntry->category ?? '') }}" 
                           class="form-control border-2 bg-light rounded-4 fw-bold shadow-none p-3" placeholder="مثل: طب عام، تشريح...">
                </div>
            </div>

            <div class="mb-0">
                <label class="form-label fw-black text-secondary small mb-2">تمييز باللون</label>
                <div class="d-flex align-items-center gap-3 bg-light border-2 border rounded-4 p-2 px-3">
                    <input type="color" name="color" value="{{ old('color', $flashcard->color ?? '#4f46e5') }}"
                           class="form-control form-control-color border-0 rounded-3 shadow-none p-0 bg-transparent" style="width: 50px; height: 40px; cursor: pointer;">
                    <span class="text-secondary small fw-bold">اختر لوناً مميزاً لهذه الحزمة</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Side Panel Area -->
    <div class="col-lg-4">
        <div class="d-flex flex-column gap-4 h-100">
            <div class="premium-card p-4">
                <h4 class="h6 fw-black text-dark mb-2">فترة الصمت (Quiet Hours)</h4>
                <p class="text-secondary small fw-bold mb-4">لن تصل إشعارات للطلاب خلال هذه الفترة</p>
                
                <div class="mb-3">
                    <label class="form-label fw-bold text-secondary small mb-1">بداية التوقف</label>
                    <input type="time" name="quiet_start" value="{{ old('quiet_start', $flashcard->quiet_start ?? '23:00') }}"
                           class="form-control border-2 bg-light rounded-3 fw-bold shadow-none p-2 px-3">
                </div>
                <div class="mb-0">
                    <label class="form-label fw-bold text-secondary small mb-1">نهاية التوقف</label>
                    <input type="time" name="quiet_end" value="{{ old('quiet_end', $flashcard->quiet_end ?? '07:00') }}"
                           class="form-control border-2 bg-light rounded-3 fw-bold shadow-none p-2 px-3">
                </div>
            </div>

            <div class="premium-card p-4">
                <div class="form-check form-switch p-0 d-flex align-items-center justify-content-between mb-3">
                    <label class="form-check-label fw-black text-dark cursor-pointer" for="notifySwitch">تفعيل الإشعارات</label>
                    <input type="hidden" name="notifications_enabled" value="0">
                    <input class="form-check-input ms-0 border-0 shadow-none bg-secondary" type="checkbox" role="switch" id="notifySwitch" 
                           name="notifications_enabled" value="1" {{ old('notifications_enabled', $flashcard->notifications_enabled ?? 1) ? 'checked' : '' }}
                           style="width: 45px; height: 24px; cursor: pointer;">
                </div>
                <div class="form-check form-switch p-0 d-flex align-items-center justify-content-between">
                    <label class="form-check-label fw-black text-dark cursor-pointer" for="activeSwitch">الحزمة نشطة الآن</label>
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input ms-0 border-0 shadow-none bg-secondary" type="checkbox" role="switch" id="activeSwitch" 
                           name="is_active" value="1" {{ old('is_active', $flashcard->is_active ?? 1) ? 'checked' : '' }}
                           style="width: 45px; height: 24px; cursor: pointer;">
                </div>
            </div>

            <div class="mt-auto">
                <button type="submit" class="btn btn-primary w-100 rounded-4 py-3 fw-black fs-5 shadow-lg border-0 transition-all d-flex align-items-center justify-content-center gap-2 text-white" 
                        style="background: linear-gradient(135deg, #4f46e5, #7c3aed);">
                    {{ isset($flashcard) ? 'حفظ التعديلات' : 'إتمام الإنشاء الآن' }}
                    <i class="fa-solid fa-check-circle"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .form-switch .form-check-input:checked { background-color: #10b981 !important; }
    .cursor-pointer { cursor: pointer; }
    .transition-all { transition: all 0.3s; }
    .transition-all:hover { opacity: 0.9; transform: translateY(-2px); }
</style>
