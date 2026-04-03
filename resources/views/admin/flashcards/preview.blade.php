<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معاينة - {{ $flashcard->title }}</title>
    
    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&family=Noto+Kufi+Arabic:wght@400;700;900&display=swap" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.4);
            --primary-gradient: linear-gradient(135deg, #4f46e5, #7c3aed);
            --success-gradient: linear-gradient(135deg, #10b981, #059669);
            --danger-gradient: linear-gradient(135deg, #ef4444, #dc2626);
            --body-bg: #f8fafc;
        }

        body {
            font-family: 'Noto Kufi Arabic', 'Outfit', sans-serif;
            background-color: var(--body-bg);
            background-image: 
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(124, 58, 237, 0.05) 0px, transparent 50%);
            min-height: 100vh;
        }

        .review-container {
            max-width: 850px;
            margin: 0 auto;
            min-height: 100vh;
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
            cursor: pointer;
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
            background: #ffffff;
            border: 1px solid #e2e8f0;
        }
        .review-card-back {
            background: var(--primary-gradient);
            color: white;
            transform: rotateY(180deg);
        }

        .action-btn {
            border-radius: 20px;
            padding: 1.25rem 2rem;
            font-weight: 900;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            font-size: 1.1rem;
        }
        .action-btn:hover:not(:disabled) {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px -5px rgba(0,0,0,0.2);
        }

        .progress-minimal {
            height: 6px;
            border-radius: 10px;
            background: rgba(0,0,0,0.05);
        }

        [x-cloak] { display: none !important; }

        /* MCQ Specifics */
        .mcq-option {
            border: 2px solid #e2e8f0;
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

        .fw-black { font-weight: 900; }
        .lh-base { line-height: 1.6; }
        
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }

        .answer-feedback {
            animation: slideUp 0.4s ease-out;
        }
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>

<div class="container review-container py-3" x-data="reviewSession()" x-cloak @keydown.space.window.prevent="handleSubmitAction()" @keydown.enter.window.prevent="handleSubmitAction()">
    
    <!-- Top Nav -->
    <div class="d-flex align-items-center justify-content-between mb-4 px-2">
        <a href="{{ route('admin.flashcards.show', $flashcard) }}" class="btn btn-white rounded-pill px-4 fw-black text-secondary border shadow-sm transition-all hvr-grow" style="background: white;">
            <i class="fas fa-times me-2"></i> إغلاق المعاينة
        </a>
        <div class="d-flex align-items-center gap-3">
            <i class="fas fa-layer-group text-secondary opacity-50"></i>
            <span class="fw-black text-dark fs-5">
                <span x-text="currentIndex + 1" class="text-primary"></span>
                <span class="text-secondary opacity-50 mx-1">/</span>
                <span x-text="totalItems"></span>
            </span>
        </div>
        <div class="rounded-pill px-4 py-2 fw-black small shadow-sm" style="background: {{ $flashcard->color ?? '#4f46e5' }}20; color: {{ $flashcard->color ?? '#4f46e5' }}; border: 1px solid {{ $flashcard->color ?? '#4f46e5' }}30;">
            {{ $flashcard->title }} <span class="opacity-50 ms-1">(معاينة الإدارة)</span>
        </div>
    </div>

    <div class="glass-card">
        <!-- Progress Bar -->
        <div class="progress-minimal">
            <div class="h-100 transition-all" :style="'width: ' + ((currentIndex + 1) / totalItems * 100) + '%; background: var(--primary-gradient); transition: width 0.5s;'"></div>
        </div>

        <div class="card-body p-4 p-md-5">
            @if($items->count() > 0)
                <div class="review-card mb-5">
                    
                    <!-- 1. FLASHCARD MODE -->
                    <template x-if="displayMode === 'flash_card'">
                        <div class="review-card-inner h-100" :class="{ 'flipped': isFlipped }" @click="flip()">
                            <div class="review-card-face review-card-front shadow-sm">
                                <span class="badge bg-primary px-3 py-2 text-white mb-4 fw-black">تذكرة - وجه</span>
                                <h2 class="fw-black mb-0 px-md-5 lh-base" style="font-size: 2.25rem;" x-text="currentItem?.front_content"></h2>
                                <div class="mt-auto text-secondary fw-black animate-pulse">
                                    <i class="fas fa-mouse-pointer me-2"></i> اضغط للقلب أو (مسافة)
                                </div>
                            </div>
                            <div class="review-card-face review-card-back shadow-lg">
                                <span class="badge bg-white text-primary px-3 py-2 mb-4 fw-black">تذكرة - خلف</span>
                                <h1 class="fw-black text-white mb-0 px-md-5 lh-base" style="font-size: 2.25rem;" x-text="currentItem?.back_content"></h1>
                                <div class="mt-auto text-white fw-black">
                                    قيم مستوى تذكرك للبطاقة
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 2. QA MODE -->
                    <template x-if="displayMode === 'qa'">
                        <div class="h-100 d-flex flex-column justify-content-center">
                            <div class="text-center mb-5">
                                <span class="badge bg-warning text-dark mb-4 px-3 py-2 fw-black">سؤال وجواب</span>
                                <h2 class="fw-black h2 mb-0 px-lg-5" x-text="currentItem?.front_content"></h2>
                            </div>

                            <div class="bg-white p-4 rounded-5 border shadow-sm">
                                <label class="form-label text-dark fw-black mb-3 d-flex justify-content-between">
                                    <span>إجابتك المتوقعة:</span>
                                    <span class="small opacity-50" x-show="!isAnswerChecked">اضغط Enter للتحقق</span>
                                </label>
                                <textarea x-model="userAnswer" :disabled="isAnswerChecked" 
                                          class="form-control border-0 bg-transparent fw-black fs-4 text-center shadow-none p-2"
                                          placeholder="..." rows="2" autofocus></textarea>
                                
                                <div x-show="isAnswerChecked" x-transition class="answer-feedback mt-4 pt-4 border-top">
                                    <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
                                        <div :class="isCorrectAnswer ? 'text-success' : 'text-danger'" class="fs-4 fw-black">
                                            <i class="fas" :class="isCorrectAnswer ? 'fa-check-circle' : 'fa-times-circle'"></i>
                                            <span x-text="isCorrectAnswer ? 'إجابة دقيقة!' : 'ليست مطابقة تماماً'"></span>
                                        </div>
                                    </div>
                                    <div class="p-4 bg-light rounded-4 text-center">
                                        <div class="text-secondary small fw-black mb-2">الإجابة النموذجية:</div>
                                        <div class="fw-black fs-4 text-primary" x-text="currentItem?.back_content"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 3. MCQ MODE -->
                    <template x-if="displayMode === 'mcq'">
                        <div class="h-100 d-flex flex-column p-2">
                            <div class="text-center mb-5">
                                <span class="badge bg-info text-white mb-4 px-3 py-2 fw-black">خيارات متعددة</span>
                                <h2 class="fw-black h3 mb-0 px-lg-4" x-text="currentItem?.front_content"></h2>
                            </div>
                            
                            <div class="row g-3">
                                <template x-for="(option, index) in currentItem?.options" :key="index">
                                    <div class="col-md-6">
                                        <div class="mcq-option d-flex align-items-center justify-content-between shadow-sm"
                                             :class="getMCQOptionClasses(index)"
                                             @click="handleMCQSelection(index)">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-black shadow-sm" 
                                                     style="width: 32px; height: 32px; background: #f1f5f9; color: #000; font-size: 0.9rem;" x-text="['A', 'B', 'C', 'D'][index]"></div>
                                                <span class="fw-bold fs-5" x-text="option"></span>
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
                            <span class="badge bg-secondary text-white mb-5 px-3 py-2 fw-black">تذكرة سريعة</span>
                            <div class="display-5 fw-black text-center lh-base px-md-5" x-text="currentItem?.front_content"></div>
                            <div class="mt-5 text-secondary fw-black">تذكر هذا النص جيداً</div>
                        </div>
                    </template>
                </div>

                <!-- Footer Nav -->
                <div class="d-flex gap-3 align-items-center mt-auto">
                    
                    <button @click="prev()" :disabled="currentIndex === 0" class="btn btn-white border rounded-4 shadow-sm d-flex align-items-center justify-content-center" style="width: 65px; height: 65px; background: white;">
                        <i class="fas fa-chevron-right fs-4"></i>
                    </button>

                    <div class="flex-grow-1">
                        <!-- Ratings (Shared across modes when reviewed) -->
                        <div class="row g-3" x-show="(displayMode === 'flash_card' && isFlipped) || displayMode === 'one_line' || (displayMode === 'qa' && isAnswerChecked)">
                            <div class="col-6">
                                <button @click="next()" class="action-btn w-100 text-white d-flex align-items-center justify-content-center gap-2" style="background: var(--success-gradient);">
                                    <i class="fas fa-check"></i> أعرفه - التالي
                                </button>
                            </div>
                            <div class="col-6">
                                <button @click="next()" class="action-btn w-100 text-white d-flex align-items-center justify-content-center gap-2" style="background: var(--danger-gradient);">
                                    <i class="fas fa-times"></i> لا أعرفه - التالي
                                </button>
                            </div>
                        </div>

                        <!-- QA Initial Check -->
                        <div x-show="displayMode === 'qa' && !isAnswerChecked">
                            <button @click="checkAnswer()" :disabled="!userAnswer.trim()" class="action-btn w-100 text-white d-flex align-items-center justify-content-center gap-2" style="background: var(--primary-gradient);">
                                <i class="fas fa-search"></i> تحقق من الإجابة
                            </button>
                        </div>

                        <!-- MCQ Continue -->
                        <div x-show="displayMode === 'mcq' && selectedOption !== null">
                            <button @click="next()" class="action-btn w-100 text-white d-flex align-items-center justify-content-center gap-2" style="background: var(--primary-gradient);">
                                التالي <i class="fas fa-arrow-left ms-1"></i>
                            </button>
                        </div>

                        <!-- Hints -->
                        <div class="text-center mt-3" x-show="displayMode === 'flash_card' && !isFlipped">
                            <span class="text-secondary small fw-black bg-white border px-4 py-2 rounded-pill shadow-sm">قم بقلب البطاقة للتقييم</span>
                        </div>
                        <div class="text-center mt-3" x-show="displayMode === 'mcq' && selectedOption === null">
                            <span class="text-secondary small fw-black bg-white border px-4 py-2 rounded-pill shadow-sm">اختر الإجابة الصحيحة</span>
                        </div>
                    </div>

                    <button @click="next()" :disabled="currentIndex >= totalItems - 1" class="btn btn-white border rounded-4 shadow-sm d-flex align-items-center justify-content-center" style="width: 65px; height: 65px; background: white;">
                        <i class="fas fa-chevron-left fs-4"></i>
                    </button>
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center p-5 mb-4 shadow-inner" style="width: 160px; height: 160px;">
                        <i class="fas fa-box-open fs-1 text-secondary opacity-25"></i>
                    </div>
                    <h2 class="fw-black text-dark mb-3">لا توجد بطاقات للمعاينة</h2>
                    <p class="text-secondary mb-5 h5">أضف بعض البطاقات في لوحة التحكم لتظهر هنا.</p>
                </div>
            @endif
        </div>
    </div>
</div>

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
        totalItems: {{ $items->count() }},

        get currentItem() {
            return this.items[this.currentIndex];
        },

        flip() { 
            if (this.displayMode === 'flash_card') {
                this.isFlipped = !this.isFlipped; 
            }
        },

        handleMCQSelection(index) {
            if (this.selectedOption !== null) return;
            this.selectedOption = index;
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

        next() {
            if (this.currentIndex < this.totalItems - 1) {
                this.currentIndex++;
                this.resetState();
            } else {
                alert('Preview finished! This is the end of the cards.');
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

</body>
</html>
