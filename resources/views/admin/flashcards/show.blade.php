@extends('layouts.admin')

@section('title', $flashcard->title . ' - تفاصيل الحزمة')

@section('content')
<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.85);
        --glass-border: rgba(255, 255, 255, 0.5);
        --primary-gradient: linear-gradient(135deg, #4f46e5, #7c3aed);
    }

    .glass-header {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: 30px;
        padding: 2.5rem;
        box-shadow: 0 10px 30px -5px rgba(0,0,0,0.05);
        margin-bottom: 2.5rem;
    }

    .premium-badge {
        padding: 0.5rem 1rem;
        border-radius: 12px;
        font-weight: 800;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
    }

    .item-card {
        background: white;
        border: 1px solid #f1f5f9;
        border-radius: 24px;
        box-shadow: 0 4px 20px -5px rgba(0,0,0,0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .item-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1);
        border-color: #4f46e5;
    }

    .card-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        font-weight: 900;
        margin-bottom: 0.75rem;
        display: block;
        opacity: 0.7;
    }

    .btn-action-sm {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        font-size: 1rem;
    }

    .form-panel {
        background: rgba(248, 250, 252, 0.8);
        backdrop-filter: blur(5px);
        border: 2px dashed #e2e8f0;
        border-radius: 28px;
        padding: 2.5rem;
        margin-bottom: 3rem;
        transition: all 0.3s;
    }
    
    .premium-input-sm {
        background: white;
        border: 2px solid #f1f5f9;
        border-radius: 16px;
        padding: 0.85rem 1.25rem;
        font-size: 1rem;
        font-weight: 600;
        transition: all 0.2s;
    }

    .premium-input-sm:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 5px rgba(79, 70, 229, 0.1);
        outline: none;
    }

    .fw-black { font-weight: 900; }
    .btn-premium { 
        background: var(--primary-gradient); 
        color: white;
        border: none;
        transition: all 0.3s;
    }
    .btn-premium:hover { 
        transform: translateY(-3px); 
        box-shadow: 0 12px 25px rgba(79,70,229,0.3); 
        color: white;
        filter: brightness(1.1);
    }

    .empty-state-icon {
        background: #f8fafc;
        width: 120px;
        height: 120px;
        border-radius: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
        color: #cbd5e1;
        font-size: 3rem;
    }

    [x-cloak] { display: none !important; }
</style>

<div x-data="{ 
    showAdd: false, 
    showImport: false,
    showEdit: false,
    editData: {
        id: null,
        front: '',
        back: '',
        options: ['', '', '', ''],
        correct: 0,
        priority: 'normal',
        action: ''
    },
    openEdit(item) {
        this.editData.id = item.id;
        this.editData.front = item.front_content;
        this.editData.back = item.back_content || '';
        this.editData.options = item.options || ['', '', '', ''];
        this.editData.correct = item.correct_option || 0;
        this.editData.priority = item.priority || 'normal';
        this.editData.action = '{{ url('admin/flashcards/items') }}/' + item.id;
        this.showEdit = true;
    }
}">
    <!-- Header Section -->
    <div class="glass-header d-flex justify-content-between align-items-center flex-wrap gap-4">
        <div class="d-flex align-items-center gap-4">
            <div style="width: 84px; height: 84px; border-radius: 24px; background: {{ hexToRgba($flashcard->color ?? '#4f46e5', 0.1) }}; color: {{ $flashcard->color ?? '#4f46e5' }}; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.05);">
                📚
            </div>
            <div>
                <h1 class="fw-black mb-2" style="font-size: 2rem; color: #1e293b;">{{ $flashcard->title }}</h1>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <span class="premium-badge" style="background: #eef2ff; color: #4f46e5;">
                        <i class="fa-solid fa-shield-check"></i>
                        رسمي
                    </span>
                    <span class="premium-badge" style="background: #eff6ff; color: #2563eb;">
                        <i class="fa-solid fa-tags"></i>
                        {{ $flashcard->category ?? 'بدون تصنيف' }}
                    </span>
                    <span class="premium-badge" style="background: #f0fdf4; color: #16a34a;">
                        <i class="fa-solid fa-layer-group"></i>
                        {{ $items->count() }} بطاقة
                    </span>
                    @php
                        $modes = [
                            'flash_card' => ['label' => 'بِطاقة تعليمية', 'icon' => 'fa-clone', 'color' => '#6366f1', 'bg' => '#f5f3ff'],
                            'one_line' => ['label' => 'رسالة نصية', 'icon' => 'fa-message', 'color' => '#10b981', 'bg' => '#f0fdf4'],
                            'qa' => ['label' => 'سؤال وجواب', 'icon' => 'fa-comments-question', 'color' => '#f59e0b', 'bg' => '#fffbeb'],
                            'mcq' => ['label' => 'اختيارات', 'icon' => 'fa-list-check', 'color' => '#ef4444', 'bg' => '#fef2f2'],
                        ];
                        $m = $modes[$flashcard->display_mode] ?? $modes['flash_card'];
                    @endphp
                    <span class="premium-badge" style="background: {{ $m['bg'] }}; color: {{ $m['color'] }};">
                        <i class="fa-solid {{ $m['icon'] }}"></i>
                        {{ $m['label'] }}
                    </span>
                    
                    <div class="ms-3 d-flex align-items-center gap-2" x-data="{ 
                        is_public: {{ $flashcard->is_public ? 'true' : 'false' }},
                        loading: false,
                        toggle() {
                            if (this.loading) return;
                            this.loading = true;
                            fetch('{{ route('admin.flashcards.toggle-visibility', $flashcard) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            })
                            .then(async res => {
                                const data = await res.json();
                                if (!res.ok) {
                                    alert(data.message || 'حدث خطأ غير متوقع');
                                    this.is_public = !this.is_public;
                                    return;
                                }
                                this.is_public = data.is_public;
                            })
                            .catch(err => {
                                alert('حدث خطأ في الاتصال');
                                this.is_public = !this.is_public;
                            })
                            .finally(() => this.loading = false);
                        }
                    }">
                        <div class="form-check form-switch pt-1">
                            <input class="form-check-input" type="checkbox" role="switch" :checked="is_public" @change="toggle()" :disabled="loading">
                            <label class="form-label ms-2 small fw-bold" :class="is_public ? 'text-success' : 'text-secondary'" x-text="is_public ? 'منشور في المتجر' : 'مخفي في المتجر'"></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-flex gap-2">
            <a href="{{ route('admin.flashcards.preview', $flashcard) }}" class="btn btn-premium rounded-4 px-4 py-2 fw-black shadow-sm hvr-grow">
                <i class="fas fa-play-circle me-2"></i> معاينة
            </a>
            <button @click="showAdd = !showAdd; showImport = false" class="btn btn-white border rounded-4 px-4 fw-black shadow-sm hvr-grow">
                <i class="fa-solid fa-plus-circle me-2 text-primary"></i>
                إضافة بطاقة
            </button>
            <button @click="showImport = !showImport; showAdd = false" class="btn btn-white border rounded-4 px-4 fw-black shadow-sm hvr-grow">
                <i class="fa-solid fa-file-excel me-2 text-success"></i>
                استيراد
            </button>
            <a href="{{ route('admin.flashcards.edit', $flashcard) }}" class="btn btn-white border rounded-4 px-3 shadow-sm hvr-grow text-secondary"><i class="fa-solid fa-pen-to-square"></i></a>
            <a href="{{ route('admin.flashcards.index') }}" class="btn btn-white border rounded-4 px-3 shadow-sm hvr-grow text-secondary"><i class="fa-solid fa-arrow-left"></i></a>
        </div>
    </div>

    <!-- Add Card Panel -->
    <div x-show="showAdd" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="form-panel border-primary" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h5 class="fw-black m-0 text-primary d-flex align-items-center gap-2 fs-4">
                <i class="fa-solid fa-sparkles"></i>
                إضافة محتوى جديد للحزمة
            </h5>
            <button @click="showAdd = false" class="btn-close shadow-none"></button>
        </div>
        <form action="{{ route('admin.flashcards.items.store', $flashcard) }}" method="POST">
            @csrf
            <div class="row g-4">
                @switch($flashcard->display_mode)
                    @case('one_line')
                        <div class="col-12">
                            <label class="form-label fw-black text-secondary small">محتوى الإشعار / الرسالة *</label>
                            <textarea name="front_content" rows="3" required class="form-control premium-input-sm" 
                                      placeholder="اكتب نص الإشعار هنا..."></textarea>
                        </div>
                        @break

                    @case('mcq')
                        <div class="col-12">
                            <label class="form-label fw-black text-secondary small">السؤال *</label>
                            <textarea name="front_content" rows="2" required class="form-control premium-input-sm" 
                                      placeholder="اكتب السؤال هنا..."></textarea>
                        </div>
                        <div class="col-12">
                            <div class="row g-3">
                                @for($i = 0; $i < 4; $i++)
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-secondary">خيار {{ $i + 1 }} {{ $i < 2 ? '*' : '(اختياري)' }}</label>
                                        <input type="text" name="options[]" {{ $i < 2 ? 'required' : '' }} class="form-control premium-input-sm" placeholder="نص الخيار...">
                                    </div>
                                @endfor
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-black text-secondary small">الخيار الصحيح</label>
                            <select name="correct_option" class="form-select premium-input-sm fw-bold">
                                @for($i = 0; $i < 4; $i++)
                                    <option value="{{ $i }}">الخيار {{ $i + 1 }}</option>
                                @endfor
                            </select>
                        </div>
                        @break

                    @default
                        <div class="col-md-6">
                            <label class="form-label fw-black text-secondary small">
                                {{ $flashcard->display_mode === 'qa' ? 'السؤال *' : 'الوجه الأمامي *' }}
                            </label>
                            <textarea name="front_content" rows="4" required class="form-control premium-input-sm" 
                                      placeholder="اكتب المحتوى هنا..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-black text-secondary small">
                                {{ $flashcard->display_mode === 'qa' ? 'الإجابة *' : 'الوجه الخلفي *' }}
                            </label>
                            <textarea name="back_content" rows="4" required class="form-control premium-input-sm" 
                                      placeholder="اكتب المحتوى هنا..."></textarea>
                        </div>
                @endswitch

                <div class="col-12 pt-4 mt-2 border-top">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-4">
                         <div class="d-flex gap-4">
                            <div class="form-check custom-option">
                                <input class="form-check-input" type="radio" name="priority" id="p-normal" value="normal" checked>
                                <label class="form-check-label fw-bold" for="p-normal">أولوية عادية</label>
                            </div>
                            <div class="form-check custom-option">
                                <input class="form-check-input" type="radio" name="priority" id="p-high" value="high">
                                <label class="form-check-label fw-bold text-warning" for="p-high">أولوية عالية</label>
                            </div>
                            <div class="form-check custom-option">
                                <input class="form-check-input" type="radio" name="priority" id="p-critical" value="critical">
                                <label class="form-check-label fw-bold text-danger" for="p-critical">أولوية حرجة</label>
                            </div>
                         </div>
                         <div class="d-flex gap-3">
                            <button type="button" @click="showAdd = false" class="btn btn-light rounded-4 px-4 fw-bold">إلغاء</button>
                            <button type="submit" class="btn btn-premium rounded-4 px-5 py-2 fw-black shadow-sm">حفظ البطاقة</button>
                         </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Import Panel -->
    <div x-show="showImport" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="form-panel border-success" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h5 class="fw-black m-0 text-success d-flex align-items-center gap-2 fs-4">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                استيراد بطاقات دفعة واحدة
            </h5>
            <button @click="showImport = false" class="btn-close shadow-none"></button>
        </div>
        <form action="{{ route('admin.flashcards.import', $flashcard) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="p-4 bg-white rounded-4 border mb-5 shadow-sm">
                <div class="d-flex align-items-center gap-4">
                    <div class="fs-1 text-success opacity-25"><i class="fa-solid fa-file-invoice"></i></div>
                    <div>
                        <h6 class="fw-black mb-1">تعليمات الملف (Excel/CSV)</h6>
                        <p class="text-secondary fw-bold small m-0">
                            <strong>التنسيق المطلوب:</strong>
                            @php
                                $instructions = [
                                    'flash_card' => 'العمود A = الأمام، العمود B = الخلف',
                                    'qa' => 'العمود A = السؤال، العمود B = الإجابة',
                                    'one_line' => 'العمود A = المحتوى النصي',
                                    'mcq' => 'A=السؤال، B-E=خيارات، F=المؤشر الصحيح (0-3)',
                                ];
                            @endphp
                            {{ $instructions[$flashcard->display_mode] ?? $instructions['flash_card'] }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="row align-items-center g-4">
                <div class="col-md-9">
                    <input type="file" name="file" required class="form-control premium-input-sm" accept=".xlsx,.xls,.csv">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-success w-100 rounded-4 py-3 fw-black shadow-sm transition-all hvr-grow">بدء الاستيراد</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Description -->
    @if($flashcard->description)
        <div class="premium-card p-4 mb-5 border-0 shadow-sm" style="background: #f8fafc; border-radius: 20px; border-r-4 border-primary">
            <p class="text-secondary m-0 lead fw-bold" style="font-size: 1.1rem; line-height: 1.8; color: #475569 !important;">{{ $flashcard->description }}</p>
        </div>
    @endif

    <!-- Items Grid -->
    <div class="row g-4">
        @forelse($items as $index => $item)
        <div class="col-xl-4 col-lg-6">
            <div class="item-card shadow-sm border-0">
                <div class="p-4 flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <span class="badge bg-light text-secondary rounded-pill px-3 py-2 fw-black" style="font-size: 0.75rem;">#{{ $index + 1 }}</span>
                        @if($item->priority !== 'normal')
                            <span class="premium-badge" style="background: {{ $item->priority == 'critical' ? '#fee2e2' : '#fef3c7' }}; color: {{ $item->priority == 'critical' ? '#ef4444' : '#d97706' }}; font-size: 0.7rem;">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ $item->priority_text }}
                            </span>
                        @endif
                    </div>
                    
                    <div class="mb-4">
                        <span class="card-label text-primary">المحتوى الأمامي</span>
                        <p class="fw-black mb-0 text-dark" style="font-size: 1.2rem; line-height: 1.6; color: #1e293b !important;">{{ $item->front_content }}</p>
                    </div>
                    
                    @if($flashcard->display_mode === 'mcq' && $item->options)
                        <div class="mt-3">
                            <span class="card-label text-secondary mb-3">خيارات الإجابة</span>
                            <div class="row g-2">
                                @foreach($item->options as $idx => $option)
                                    <div class="col-6">
                                        <div class="p-3 border rounded-4 small fw-bold text-center d-flex align-items-center gap-2 {{ $idx == $item->correct_option ? 'bg-success-subtle text-success border-success' : 'bg-light text-secondary border-transparent' }}">
                                            <span class="opacity-40 fw-black" style="width: 20px;">{{ ['A','B','C','D'][$idx] ?? $idx }}</span>
                                            <span class="text-truncate">{{ $option }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @elseif($item->back_content)
                        <div>
                            <span class="card-label text-success">المحتوى الخلفي</span>
                            <div class="bg-light p-4 rounded-4 border-end border-primary border-4 text-dark fst-italic shadow-none fw-bold" style="color: #334155 !important; background: #f1f5f9 !important;">
                                {{ $item->back_content }}
                            </div>
                        </div>
                    @endif
                </div>
                
                <div class="px-4 py-3 bg-light border-top d-flex justify-content-end gap-2">
                    <button type="button" @click='openEdit(@json($item))' class="btn-action-sm bg-white border text-primary shadow-sm hvr-grow" title="تعديل">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <form action="{{ route('admin.flashcards.items.destroy', $item) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه البطاقة؟')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-action-sm bg-white border text-danger shadow-sm hvr-grow" title="حذف">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 py-5 text-center">
            <div class="empty-state-icon">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <h3 class="fw-black text-dark mb-2">لا توجد بطاقات تعليمية</h3>
            <p class="text-secondary mb-5 h5 fw-bold">ابدأ بإضافة محتوى للحزمة عبر الأزرار في الأعلى</p>
            <button @click="showAdd = true" class="btn btn-premium rounded-pill px-5 py-3 fw-black shadow-lg">
                أضف بطاقتك الأولى الآن
            </button>
        </div>
        @endforelse
    </div>
    <!-- Edit Item Modal (Single Instance) -->
    <div x-show="showEdit" x-cloak class="position-fixed inset-0 w-100 h-100" style="background: rgba(0,0,0,0.6); z-index: 9999; backdrop-filter: blur(4px); top: 0; left: 0;">
        <div class="d-flex align-items-center justify-content-center w-100 h-100">
            <div @click.away="showEdit = false" class="premium-card p-4 bg-white" style="max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-black m-0 text-primary d-flex align-items-center gap-2 fs-5">
                    <i class="fa-solid fa-pen"></i>
                    تعديل محتوى البطاقة
                </h5>
                <button type="button" @click="showEdit = false" class="btn-close shadow-none"></button>
            </div>

            <form :action="editData.action" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-4">
                    @switch($flashcard->display_mode)
                        @case('one_line')
                            <div class="col-12">
                                <label class="form-label fw-black text-secondary small">محتوى الإشعار / الرسالة *</label>
                                <textarea name="front_content" x-model="editData.front" rows="3" required class="form-control premium-input-sm"></textarea>
                            </div>
                            @break

                        @case('mcq')
                            <div class="col-12">
                                <label class="form-label fw-black text-secondary small">السؤال *</label>
                                <textarea name="front_content" x-model="editData.front" rows="2" required class="form-control premium-input-sm"></textarea>
                            </div>
                            <div class="col-12">
                                <div class="row g-3">
                                    <template x-for="(opt, index) in editData.options" :key="index">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small text-secondary" x-text="'خيار ' + (index + 1) + (index < 2 ? ' *' : ' (اختياري)')"></label>
                                            <input type="text" name="options[]" :required="index < 2" x-model="editData.options[index]" class="form-control premium-input-sm">
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-black text-secondary small">الخيار الصحيح</label>
                                <select name="correct_option" x-model="editData.correct" class="form-select premium-input-sm fw-bold">
                                    <template x-for="(opt, index) in editData.options" :key="'correct-'+index">
                                        <option :value="index" x-text="'الخيار ' + (index + 1)" :selected="editData.correct == index"></option>
                                    </template>
                                </select>
                            </div>
                            @break

                        @default
                            <div class="col-md-6">
                                <label class="form-label fw-black text-secondary small">
                                    {{ $flashcard->display_mode === 'qa' ? 'السؤال *' : 'الوجه الأمامي *' }}
                                </label>
                                <textarea name="front_content" x-model="editData.front" rows="4" required class="form-control premium-input-sm"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-black text-secondary small">
                                    {{ $flashcard->display_mode === 'qa' ? 'الإجابة *' : 'الوجه الخلفي *' }}
                                </label>
                                <textarea name="back_content" x-model="editData.back" rows="4" required class="form-control premium-input-sm"></textarea>
                            </div>
                    @endswitch

                    <div class="col-12 pt-3 mt-3 border-top">
                        <label class="form-label fw-black text-secondary small mb-3">أولوية البطاقة</label>
                        <div class="d-flex gap-4 mb-4">
                            <div class="form-check custom-option">
                                <input class="form-check-input" type="radio" name="priority" id="ep-normal-main" value="normal" x-model="editData.priority">
                                <label class="form-check-label fw-bold" for="ep-normal-main">عادية</label>
                            </div>
                            <div class="form-check custom-option">
                                <input class="form-check-input" type="radio" name="priority" id="ep-high-main" value="high" x-model="editData.priority">
                                <label class="form-check-label fw-bold text-warning" for="ep-high-main">عالية</label>
                            </div>
                            <div class="form-check custom-option">
                                <input class="form-check-input" type="radio" name="priority" id="ep-critical-main" value="critical" x-model="editData.priority">
                                <label class="form-check-label fw-bold text-danger" for="ep-critical-main">حرجة</label>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-3">
                            <button type="button" @click="showEdit = false" class="btn btn-light rounded-4 px-4 fw-bold">إلغاء</button>
                            <button type="submit" class="btn btn-premium rounded-4 px-5 py-2 fw-black shadow-sm">حفظ التغييرات</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        </div>
    </div>
</div>

@php
function hexToRgba($hex, $alpha = 1) {
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    return "rgba($r, $g, $b, $alpha)";
}
@endphp
@endsection
