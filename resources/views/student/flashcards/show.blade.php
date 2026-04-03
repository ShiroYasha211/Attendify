@extends('layouts.student')

@section('title', $flashcard->title . ' - Oneline Shot')

@section('content')
<div class="container-fluid px-0" x-data="{ showAddForm: false, editingItem: null }">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-4 d-flex align-items-center justify-content-center fs-2 shadow-sm" style="width: 56px; height: 56px; background: {{ $flashcard->color ?? '#4f46e5' }}20; color: {{ $flashcard->color ?? '#4f46e5' }};">
                {{ $flashcard->icon ?? '📚' }}
            </div>
            <div>
                <h1 class="h3 fw-black text-dark mb-1">{{ $flashcard->title }}</h1>
                <p class="text-secondary mb-0 small">{{ $flashcard->items_count }} بطاقة · {{ $highPriorityCount }} مهمة</p>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('student.flashcards.review', $flashcard) }}" class="btn btn-purple fw-bold d-inline-flex align-items-center gap-2 shadow-sm border-0 px-4 py-2" style="background: linear-gradient(135deg, #a855f7, #7c3aed); color: white; border-radius: 12px;">
                <i class="fas fa-play"></i>
                بدء المراجعة
            </a>
            <button @click="showAddForm = !showAddForm" class="btn btn-primary fw-bold d-inline-flex align-items-center gap-2 shadow-sm border-0 px-4 py-2" style="border-radius: 12px;">
                <i class="fas fa-plus"></i>
                إضافة بطاقة
            </button>
            <a href="{{ route('student.flashcards.index') }}" class="btn btn-light fw-bold d-inline-flex align-items-center gap-2 border-0 px-3 py-2" style="border-radius: 12px; background: #f1f5f9;">
                <i class="fas fa-arrow-right"></i>
                العودة
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">{{ session('error') }}</div>
    @endif

    <!-- Import & Add Form -->
    <div x-show="showAddForm" x-transition class="mb-5">
        <div class="row g-4">
            <!-- Add Card Form -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <h4 class="fw-black text-dark mb-4 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-plus-circle text-primary"></i>
                            إضافة بطاقة جديدة
                        </h4>
                        <form action="{{ route('student.flashcards.items.store', $flashcard) }}" method="POST">
                            @csrf
                            @switch($flashcard->display_mode)
                                @case('one_line')
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-secondary text-uppercase small mb-2">محتوى الإشعار / الرسالة *</label>
                                        <textarea name="front_content" required rows="3" class="form-control border-2 bg-light rounded-3" placeholder="اكتب نص الإشعار هنا..."></textarea>
                                    </div>
                                    @break

                                @case('mcq')
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-secondary text-uppercase small mb-2">السؤال *</label>
                                        <textarea name="front_content" required rows="2" class="form-control border-2 bg-light rounded-3"></textarea>
                                    </div>
                                    <div class="row g-3 mb-4">
                                        @for($i = 0; $i < 4; $i++)
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-secondary small mb-1">خيار {{ $i + 1 }} {{ $i < 2 ? '*' : '(اختياري)' }}</label>
                                                <input type="text" name="options[]" {{ $i < 2 ? 'required' : '' }} class="form-control border-2 bg-light rounded-3">
                                            </div>
                                        @endfor
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-secondary small mb-2">الخيار الصحيح</label>
                                        <select name="correct_option" class="form-select border-2 bg-light rounded-3 fw-bold">
                                            @for($i = 0; $i < 4; $i++)
                                                <option value="{{ $i }}">الخيار {{ $i + 1 }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    @break

                                @default
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-secondary text-uppercase small mb-2">
                                            {{ $flashcard->display_mode === 'qa' ? 'السؤال *' : 'الوجه الأمامي *' }}
                                        </label>
                                        <textarea name="front_content" required rows="2" class="form-control border-2 bg-light rounded-3"></textarea>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-secondary text-uppercase small mb-2">
                                            {{ $flashcard->display_mode === 'qa' ? 'الإجابة *' : 'الوجه الخلفي *' }}
                                        </label>
                                        <textarea name="back_content" required rows="2" class="form-control border-2 bg-light rounded-3"></textarea>
                                    </div>
                            @endswitch

                            <div class="row g-3 align-items-end">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold text-secondary text-uppercase small mb-2">الأهمية</label>
                                    <select name="priority" class="form-select border-2 bg-light rounded-3 fw-bold">
                                        <option value="normal">عادية (زرقاء)</option>
                                        <option value="high">مهمة (برتقالية)</option>
                                        <option value="critical">حرجة (حمراء)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary w-100 fw-black py-2 rounded-3">حفظ البطاقة</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Import Excel -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-body p-4 d-flex flex-column">
                        <h4 class="fw-black text-dark mb-4 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-file-import text-success"></i>
                            استيراد سريع
                        </h4>
                        <form action="{{ route('student.flashcards.import', $flashcard) }}" method="POST" enctype="multipart/form-data" class="flex-grow-1 d-flex flex-column">
                            @csrf
                            <div class="flex-grow-1 border-2 border-dashed border-light bg-light rounded-4 d-flex flex-column align-items-center justify-content-center p-4 text-center mb-4 transition-all hover-border-primary" style="border-width: 3px !important; cursor: pointer;" onclick="document.getElementById('file-input').click()">
                                <i class="fas fa-cloud-upload-alt text-secondary opacity-50 mb-3" style="font-size: 3rem;"></i>
                                <p class="text-dark fw-bold mb-1">اسحب ملف Excel أو CSV هنا</p>
                                <p class="text-secondary small mb-0">أو اضغط لاختيار الملف من جهازك</p>
                                <input type="file" id="file-input" name="file" accept=".xlsx,.csv,.xls" required class="d-none">
                            </div>
                            
                            <div class="alert alert-info border-0 rounded-3 small p-3 mb-4">
                                <i class="fa-solid fa-circle-info me-1"></i>
                                <strong>تنسيق الملف:</strong> 
                                @php
                                    $instructions = [
                                        'flash_card' => 'A = أمام، B = خلف',
                                        'qa' => 'A = سؤال، B = إجابة',
                                        'one_line' => 'A = نص واحد',
                                        'mcq' => 'A = سؤال، B-E = خيارات، F = الرقم الصحيح (0-3)',
                                    ];
                                @endphp
                                {{ $instructions[$flashcard->display_mode] ?? $instructions['flash_card'] }}
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100 fw-black py-2 rounded-3 mt-auto">بدء الاستيراد</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 mt-2">
        <h3 class="h5 fw-black text-dark mb-0">قائمة البطاقات ({{ $items->count() }})</h3>
        <div class="text-secondary small fw-bold">رتب حسب: التاريخ</div>
    </div>

    <!-- Cards List -->
    <div class="row g-3">
        @forelse($items as $item)
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden transition-all hover-translate-x">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start gap-4">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge rounded-pill px-3 py-1 fw-bold" style="font-size: 0.7rem;
                                    {{ $item->priority === 'critical' ? 'background: #fff1f2; color: #ef4444;' : ($item->priority === 'high' ? 'background: #fff7ed; color: #f59e0b;' : 'background: #f0f9ff; color: #3b82f6;') }}">
                                    {{ $item->getPriorityTextAttribute() }}
                                </span>
                                <span class="text-secondary small fw-bold">#{{ $loop->iteration }}</span>
                            </div>
                            <h4 class="h6 fw-black text-dark mb-3 lh-base">{{ $item->front_content }}</h4>
                            
                            @if($flashcard->display_mode === 'mcq' && $item->options)
                                <div class="row g-2 mt-2">
                                    @foreach($item->options as $idx => $option)
                                        <div class="col-md-6 col-lg-3">
                                            <div class="p-2 border rounded-3 small fw-bold text-center d-flex align-items-center justify-content-center gap-2 {{ $idx == $item->correct_option ? 'bg-success-subtle text-success border-success-subtle' : 'bg-light text-secondary border-light' }}">
                                                <span class="opacity-50 fs-xs fw-black">[{{ ['A','B','C','D'][$idx] ?? $idx }}]</span>
                                                {{ Str::limit($option, 40) }}
                                                @if($idx == $item->correct_option) <i class="fa-solid fa-check-circle"></i> @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($item->back_content)
                                <div class="bg-light p-3 rounded-4 border-end border-primary border-4 text-dark fst-italic shadow-sm small">
                                    {{ $item->back_content }}
                                </div>
                            @endif
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <form action="{{ route('student.flashcards.items.destroy', $item) }}" method="POST" onsubmit="return confirm('حذف هذه البطاقة؟')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light text-danger p-2 border-0 rounded-3 shadow-none d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="حذف">
                                    <i class="fas fa-trash-alt" style="font-size: 0.9rem;"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center p-4 mb-3" style="width: 100px; height: 100px;">
                <i class="fas fa-clone text-secondary opacity-50" style="font-size: 2.5rem;"></i>
            </div>
            <h4 class="fw-bold text-dark mb-2">لا توجد بطاقات في هذه الحزمة</h4>
            <p class="text-secondary mb-4">ابدأ بإضافة بطاقاتك يدوياً أو استخدم خاصية الاستيراد</p>
            <button @click="showAddForm = true" class="btn btn-primary fw-black px-5 rounded-3 shadow-sm py-2">إضافة بطاقتك الأولى</button>
        </div>
        @endforelse
    </div>

    <!-- Back Link -->
    <div class="text-center mt-5 mb-4 border-top pt-4">
        <a href="{{ route('student.flashcards.index') }}" class="text-secondary text-decoration-none fw-black small d-inline-flex align-items-center gap-2 hover-text-primary transition-all">
            <i class="fas fa-arrow-right"></i>
            العودة لـ Oneline Shot
        </a>
    </div>
</div>

<style>
    .fw-black { font-weight: 900; }
    .btn-purple { transition: all 0.3s; }
    .btn-purple:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(168,85,247,0.4) !important; filter: brightness(1.1); }
    .hover-border-primary:hover { border-color: var(--primary-color) !important; background: rgba(79,70,229,0.02) !important; }
    .hover-translate-x:hover { transform: translateX(-5px); }
    .fs-xs { font-size: 0.65rem; }
    .hover-text-primary:hover { color: var(--primary-color) !important; }
</style>
@endsection
