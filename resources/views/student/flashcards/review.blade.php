@extends('layouts.student')

@section('title', 'مراجعة - Oneline Shot')

@push('styles')
<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.4);
        --primary-gradient: linear-gradient(135deg, #4f46e5, #7c3aed);
        --success-gradient: linear-gradient(135deg, #10b981, #059669);
        --danger-gradient: linear-gradient(135deg, #ef4444, #dc2626);
    }

    .review-container {
        max-width: 850px;
        margin: 0 auto;
        min-height: 80vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    /* Glassmorphism Card */
    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid var(--glass-border);
        border-radius: 40px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Flashcard 3D */
    .review-card {
        perspective: 2000px;
        min-height: 400px;
    }
    .review-card-inner {
        position: relative;
        width: 100%;
        height: 100%;
        transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        transform-style: preserve-3d;
    }
    .review-card-inner.flipped {
        transform: rotateY(180deg);
    }
    .review-card-face {
        position: absolute;
        width: 100%;
        height: 100%;
        backface-visibility: hidden;
        border-radius: 30px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 3rem;
        text-align: center;
    }
    .review-card-front {
        background: transparent;
    }
    .review-card-back {
        background: var(--primary-gradient);
        color: white;
        transform: rotateY(180deg);
    }

    /* Buttons */
    .btn-glass {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 18px;
        transition: all 0.3s;
        font-weight: 800;
    }
    .btn-glass:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-3px);
    }

    .action-btn {
        border-radius: 20px;
        padding: 1rem 2rem;
        font-weight: 900;
        letter-spacing: 0.5px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
    }
    .action-btn:hover:not(:disabled) {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px -5px rgba(0,0,0,0.2);
    }

    /* Progress and Misc */
    .progress-minimal {
        height: 6px;
        border-radius: 10px;
        background: rgba(0,0,0,0.05);
    }
    .mode-badge {
        font-size: 0.75rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 0.5rem 1.25rem;
        border-radius: 100px;
    }

    [x-cloak] { display: none !important; }

    /* MCQ Specifics */
    .mcq-option {
        border: 2px solid transparent;
        background: white;
        border-radius: 20px;
        padding: 1.25rem;
        transition: all 0.2s;
        cursor: pointer;
        position: relative;
    }
    .mcq-option:hover {
        border-color: #4f46e5;
        background: #f8fafc;
    }
    .mcq-option.selected {
        border-color: #4f46e5;
        background: #eef2ff;
    }
    .mcq-option.correct {
        border-color: #10b981;
        background: #ecfdf5;
    }
    .mcq-option.wrong {
        border-color: #ef4444;
        background: #fef2f2;
    }

    .answer-feedback {
        animation: slideUp 0.4s ease-out;
    }
    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
</style>
@endpush

@section('content')
<div class="review-container py-4" x-data="reviewSession()" x-cloak @keydown.space.window.prevent="handleSubmitAction()" @keydown.enter.window.prevent="handleSubmitAction()">
    
    <!-- Top Stats -->
    <div class="d-flex align-items-center justify-content-between mb-4 px-3">
        <a href="{{ route('student.flashcards.show', $flashcard) }}" class="btn btn-light rounded-pill px-3 fw-bold text-secondary border-0 shadow-sm">
            <i class="fas fa-times me-1"></i> إنهاء
        </a>
        <div class="d-flex align-items-center gap-3">
            <i class="fas fa-layer-group text-secondary opacity-50"></i>
            <span class="fw-black text-dark fs-5">
                <span x-text="currentIndex + 1" class="text-primary"></span>
                <span class="text-secondary opacity-50 mx-1">/</span>
                <span x-text="totalItems"></span>
            </span>
        </div>
        <div class="rounded-pill px-3 py-1 fw-bold small" style="background: {{ $flashcard->color ?? '#4f46e5' }}20; color: {{ $flashcard->color ?? '#4f46e5' }};">
            {{ $flashcard->title }}
        </div>
    </div>

    <div class="glass-card">
        <!-- Progress Bar (Top Edge) -->
        <div class="progress-minimal">
            <div class="h-100 transition-all" :style="'width: ' + ((currentIndex + 1) / totalItems * 100) + '%; background: var(--primary-gradient); transition: width 0.5s;'"></div>
        </div>

        <div class="card-body p-4 p-md-6">
            @if($items->count() > 0)
                <div class="review-card mb-5">
                    
                    <!-- 1. FLASHCARD MODE -->
                    <template x-if="displayMode === 'flash_card'">
                        <div class="review-card-inner h-100" :class="{ 'flipped': isFlipped }" @click="flip()">
                            <div class="review-card-face review-card-front" style="background: #ffffff !important; border: 2px solid #e2e8f0; border-radius: 30px;">
                                <span class="badge bg-primary px-3 py-2 text-white mb-4 fw-bold">تذكرة - وجه</span>
                                <h2 class="fw-black mb-0 px-md-5 lh-base" style="color: #000000 !important; font-size: 2.5rem;" x-text="currentItem?.front_content"></h2>
                                <div class="mt-auto text-dark fw-bold animate-pulse" style="color: #64748b !important;">
                                    <i class="fas fa-mouse-pointer me-2"></i> اضغط للقلب أو (مسافة)
                                </div>
                            </div>
                            <div class="review-card-face review-card-back" style="background: linear-gradient(135deg, #4f46e5, #7c3aed) !important;">
                                <span class="badge bg-white text-primary px-3 py-2 mb-4 fw-bold">تذكرة - خلف</span>
                                <h1 class="fw-black text-white mb-0 px-md-5 lh-base" style="color: #ffffff !important; font-size: 2.5rem;" x-text="currentItem?.back_content"></h1>
                                <div class="mt-auto text-white fw-bold">
                                    قيم مستوى تذكرك للبطاقة
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 2. QA MODE -->
                    <template x-if="displayMode === 'qa'">
                        <div class="h-100 d-flex flex-column justify-content-center">
                            <div class="text-center mb-5">
                                <span class="badge bg-warning text-dark mb-4 px-3 py-2 fw-bold">سؤال وجواب</span>
                                <h2 class="fw-black h2 mb-0 px-lg-5" style="color: #000000 !important;" x-text="currentItem?.front_content"></h2>
                            </div>

                            <div class="bg-white p-4 rounded-5 border border-primary-subtle shadow-sm">
                                <label class="form-label text-dark fw-bold mb-3 d-flex justify-content-between">
                                    <span>إجابتك:</span>
                                    <span class="small opacity-50" x-show="!isAnswerChecked">اضغط Enter للتحقق</span>
                                </label>
                                <textarea x-model="userAnswer" :disabled="isAnswerChecked" 
                                          class="form-control border-0 bg-transparent fw-bold fs-4 text-center shadow-none p-2"
                                          style="color: #000000 !important;"
                                          placeholder="..." rows="2" autofocus></textarea>
                                
                                <div x-show="isAnswerChecked" x-transition class="answer-feedback mt-4 pt-4 border-top">
                                    <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
                                        <div :class="isCorrectAnswer ? 'text-success' : 'text-danger'" class="fs-4 fw-black">
                                            <i class="fas" :class="isCorrectAnswer ? 'fa-check-circle' : 'fa-times-circle'"></i>
                                            <span x-text="isCorrectAnswer ? 'إجابة دقيقة!' : 'ليست مطابقة تماماً'"></span>
                                        </div>
                                    </div>
                                    <div class="p-3 bg-light rounded-4 text-center">
                                        <div class="text-secondary small fw-bold mb-1">الإجابة النموذجية:</div>
                                        <div class="fw-black fs-5" style="color: #4f46e5 !important;" x-text="currentItem?.back_content"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 3. MCQ MODE -->
                    <template x-if="displayMode === 'mcq'">
                        <div class="h-100 d-flex flex-column p-2">
                            <div class="text-center mb-5">
                                <span class="badge bg-info text-white mb-4 px-3 py-2 fw-bold">خيارات متعددة</span>
                                <h2 class="fw-black h3 mb-0 px-lg-4" style="color: #000000 !important;" x-text="currentItem?.front_content"></h2>
                            </div>
                            
                            <div class="row g-3">
                                <template x-for="(option, index) in currentItem?.options" :key="index">
                                    <div class="col-md-6">
                                        <div class="mcq-option d-flex align-items-center justify-content-between shadow-sm"
                                             style="background: #ffffff !important; border: 2px solid #e2e8f0;"
                                             :class="getMCQOptionClasses(index)"
                                             @click="handleMCQSelection(index)">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-black shadow-sm" 
                                                     style="width: 32px; height: 32px; background: #f1f5f9; color: #000; font-size: 0.9rem;" x-text="['A', 'B', 'C', 'D'][index]"></div>
                                                <span class="fw-bold fs-5" style="color: #000000 !important;" x-text="option"></span>
                                            </div>
                                            <div x-show="selectedOption !== null">
                                                <i class="fas" :class="index == currentItem?.correct_option ? 'fa-check-circle text-success' : (index == selectedOption ? 'fa-times-circle text-danger' : '')"></i>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div x-show="selectedOption !== null" x-transition class="answer-feedback text-center mt-5">
                                <div class="d-inline-flex align-items-center gap-2 px-4 py-2 rounded-pill fw-black text-white shadow-lg"
                                     :class="selectedOption == currentItem?.correct_option ? 'bg-success' : 'bg-danger'">
                                     <i class="fas" :class="selectedOption == currentItem?.correct_option ? 'fa-star' : 'fa-lightbulb'"></i>
                                     <span x-text="selectedOption == currentItem?.correct_option ? 'أحسنت، إجابة صحيحة' : 'خطأ، راجع الإجابة الصحيحة'"></span>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 4. ONE LINE MODE -->
                    <template x-if="displayMode === 'one_line'">
                        <div class="h-100 d-flex flex-column align-items-center justify-content-center p-4">
                            <span class="badge bg-secondary text-white mb-5 px-3 py-2 fw-bold">تذكرة سريعة</span>
                            <div class="display-5 fw-black text-center lh-base px-md-5" style="color: #000000 !important;" x-text="currentItem?.front_content"></div>
                            <div class="mt-5 text-dark fw-bold">تذكر هذا النص جيداً</div>
                        </div>
                    </template>
                </div>

                <!-- Unified Smart Footer -->
                <div class="d-flex gap-3 align-items-center">
                    
                    <!-- Back Button -->
                    <button @click="prev()" :disabled="currentIndex === 0 || isSubmitting" class="btn btn-light rounded-4 shadow-none d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fas fa-chevron-right fs-5"></i>
                    </button>

                    <!-- Main Action Area -->
                    <div class="flex-grow-1">
                        <!-- Flashcard / OneLine Ratings -->
                        <div class="row g-3" x-show="(displayMode === 'flash_card' && isFlipped) || displayMode === 'one_line' || (displayMode === 'qa' && isAnswerChecked)">
                            <div class="col-6">
                                <button @click="submitProgress(1)" :disabled="isSubmitting" class="action-btn w-100 bg-success text-white d-flex align-items-center justify-content-center gap-2" style="background: var(--success-gradient) !important;">
                                    <span x-show="!isSubmitting"><i class="fas fa-check"></i> أعرفه</span>
                                    <span x-show="isSubmitting" class="spinner-border spinner-border-sm"></span>
                                </button>
                            </div>
                            <div class="col-6">
                                <button @click="submitProgress(0)" :disabled="isSubmitting" class="action-btn w-100 bg-danger text-white d-flex align-items-center justify-content-center gap-2" style="background: var(--danger-gradient) !important;">
                                    <span x-show="!isSubmitting"><i class="fas fa-times"></i> لا أعرفه</span>
                                    <span x-show="isSubmitting" class="spinner-border spinner-border-sm"></span>
                                </button>
                            </div>
                        </div>

                        <!-- QA Initial Check -->
                        <div x-show="displayMode === 'qa' && !isAnswerChecked">
                            <button @click="checkAnswer()" :disabled="!userAnswer.trim()" class="action-btn w-100 btn-primary text-white d-flex align-items-center justify-content-center gap-2" style="background: var(--primary-gradient) !important;">
                                <i class="fas fa-search"></i> تحقق من الإجابة
                            </button>
                        </div>

                        <!-- MCQ Continue -->
                        <div x-show="displayMode === 'mcq' && selectedOption !== null">
                            <button @click="submitProgress(selectedOption == currentItem?.correct_option ? 1 : 0)" :disabled="isSubmitting" class="action-btn w-100 btn-primary text-white d-flex align-items-center justify-content-center gap-2" style="background: var(--primary-gradient) !important;">
                                <span x-show="!isSubmitting">التالي <i class="fas fa-arrow-left ms-1"></i></span>
                                <span x-show="isSubmitting" class="spinner-border spinner-border-sm"></span>
                            </button>
                        </div>

                        <!-- Hints -->
                        <div class="text-center mt-3" x-show="displayMode === 'flash_card' && !isFlipped">
                            <span class="text-secondary small fw-black bg-light px-4 py-2 rounded-pill">قم بقلب البطاقة للتقييم</span>
                        </div>
                        <div class="text-center mt-3" x-show="displayMode === 'mcq' && selectedOption === null">
                            <span class="text-secondary small fw-black bg-light px-4 py-2 rounded-pill">اختر الإجابة الصحيحة</span>
                        </div>
                    </div>

                    <!-- Next Placeholder/Arrow -->
                    <button @click="next()" :disabled="currentIndex >= totalItems - 1 || isSubmitting" class="btn btn-light rounded-4 shadow-none d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fas fa-chevron-left fs-5"></i>
                    </button>
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center p-5 mb-4 shadow-inner" style="width: 160px; height: 160px;">
                        <i class="fas fa-box-open fs-1 text-secondary opacity-25"></i>
                    </div>
                    <h2 class="fw-black text-dark mb-3">لا توجد بطاقات للمراجعة</h2>
                    <p class="text-secondary mb-5 px-4 h5 lh-base">هذه الحزمة فارغة تماماً، أضف بعض البطاقات لتبدأ رحلة التعلم مع Oneline Shot</p>
                    <a href="{{ route('student.flashcards.show', $flashcard) }}" class="btn btn-primary fw-black px-5 py-3 rounded-4 shadow-lg border-0 fs-5" style="background: var(--primary-gradient);">إضافة بطاقات الآن</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function reviewSession() {
    return {
        items: @json($items),
        displayMode: '{{ $flashcard->display_mode }}',
        currentIndex: 0,
        isFlipped: false,
        selectedOption: null,
        userAnswer: '',
        isAnswerChecked: false,
        isCorrectAnswer: null,
        isSubmitting: false,
        totalItems: {{ $items->count() }},
        progressUrl: "{{ route('student.flashcards.progress') }}",

        get currentItem() {
            return this.items[this.currentIndex];
        },

        flip() { 
            if (this.displayMode === 'flash_card') {
                this.isFlipped = !this.isFlipped; 
            }
        },

        handleMCQSelection(index) {
            if (this.selectedOption !== null || this.isSubmitting) return;
            this.selectedOption = index;
            // Play a subtle sound or haptic feedback if possible in future
        },

        getMCQOptionClasses(index) {
            if (this.selectedOption === null) return '';
            const correct = this.currentItem.correct_option;
            if (index == correct) return 'correct';
            if (index == this.selectedOption) return 'wrong';
            return 'opacity-50 grayscale';
        },

        checkAnswer() {
            if (!this.userAnswer.trim() || this.isAnswerChecked) return;
            const correct = this.currentItem.back_content.toString().trim().toLowerCase();
            const user = this.userAnswer.trim().toLowerCase();
            this.isCorrectAnswer = (correct === user);
            this.isAnswerChecked = true;
        },

        handleSubmitAction() {
            if (this.displayMode === 'flash_card') {
                if (!this.isFlipped) this.flip();
            } else if (this.displayMode === 'qa') {
                if (!this.isAnswerChecked) this.checkAnswer();
            }
        },

        async submitProgress(isCorrect) {
            if (this.isSubmitting) return;
            this.isSubmitting = true;
            try {
                const response = await fetch(this.progressUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        item_id: this.currentItem.id,
                        is_correct: isCorrect
                    })
                });

                if (response.ok) {
                    if (this.currentIndex < this.totalItems - 1) {
                        this.next();
                    } else {
                        window.location.href = "{{ route('student.flashcards.show', $flashcard) }}?finished=1";
                    }
                }
            } catch (error) {
                console.error('Error recording progress:', error);
            } finally {
                this.isSubmitting = false;
            }
        },

        next() {
            if (this.currentIndex < this.totalItems - 1) {
                this.currentIndex++;
                this.resetState();
            }
        },

        prev() {
            if (this.currentIndex > 0) {
                this.currentIndex--;
                this.resetState();
            }
        },

        resetState() {
            this.isFlipped = false;
            this.selectedOption = null;
            this.userAnswer = '';
            this.isAnswerChecked = false;
            this.isCorrectAnswer = null;
        }
    };
}
</script>
@endpush
