@extends('layouts.delegate')

@section('title', 'أخبار الدفعة')

@section('content')

<style>
    /* Premium Feed Styles */
    .feed-container {
        max-width: 800px;
        margin: 0 auto;
    }

    .news-card {
        background: white;
        border-radius: 20px;
        border: 1px solid rgba(226, 232, 240, 0.6);
        padding: 0;
        margin-bottom: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
    }

    .news-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.01);
    }

    /* Category Strip */
    .news-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        width: 6px;
        background: var(--primary-color);
        z-index: 1;
    }

    .news-card.type-academic::before {
        background: var(--info-color);
    }

    .news-card.type-urgent::before {
        background: var(--danger-color);
    }

    .news-card.type-general::before {
        background: var(--secondary-color);
    }

    .card-body {
        padding: 1.5rem 2rem;
    }

    .news-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .author-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .author-avatar {
        width: 42px;
        height: 42px;
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-secondary);
        font-weight: 700;
        font-size: 1rem;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .author-details {
        display: flex;
        flex-direction: column;
        line-height: 1.3;
        gap: 0.1rem;
    }

    .author-name {
        font-weight: 700;
        color: var(--text-primary);
        font-size: 0.95rem;
    }

    .post-date {
        font-size: 0.75rem;
        color: var(--text-light);
    }

    .news-title {
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 0.75rem;
        line-height: 1.4;
    }

    .news-content {
        color: var(--text-secondary);
        font-size: 0.95rem;
        line-height: 1.8;
        white-space: pre-wrap;
        margin-bottom: 1.5rem;
        position: relative;
    }

    .news-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 1rem;
        border-top: 1px solid #f1f5f9;
        margin-top: auto;
    }

    .category-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.8rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
        background: #f8fafc;
        color: var(--text-secondary);
        transition: all 0.2s;
    }

    .category-badge:hover {
        background: #f1f5f9;
    }

    .type-academic .category-badge {
        color: var(--info-color);
        background: rgba(59, 130, 246, 0.08);
    }

    .type-urgent .category-badge {
        color: var(--danger-color);
        background: rgba(244, 63, 94, 0.08);
    }

    .action-btn {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        color: var(--text-light);
        transition: all 0.2s;
        border: none;
        background: transparent;
        cursor: pointer;
    }

    .action-btn:hover {
        background: #f1f5f9;
        color: var(--primary-color);
    }

    /* Modal Styles */
    .modal-overlay {
        display: flex;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.6);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(8px);
    }

    .modal-container {
        background: white;
        width: 90%;
        max-width: 600px;
        border-radius: 24px;
        padding: 2.5rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        animation: modalSlideUp 0.3s ease-out;
    }

    @keyframes modalSlideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="container" x-data="newsManager()">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.5px;">أخبار الدفعة</h1>
            <p style="color: var(--text-secondary); font-size: 1rem;">آخر التحديثات والإعلانات الرسمية للطلاب</p>
        </div>
        <button @click="openCreateModal()" class="btn btn-primary" style="padding: 0.8rem 1.5rem; box-shadow: 0 4px 12px rgba(67, 56, 202, 0.2); border-radius: 12px; display: flex; align-items: center; gap: 0.5rem; font-weight: 700; transition: transform 0.2s;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            خبر جديد
        </button>
    </div>

    @if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4" style="border-radius: 12px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="feed-container">
        @forelse($announcements as $post)
        <div class="news-card type-{{ $post->category }}">
            <div class="card-body">
                <div class="news-header">
                    <div class="author-info">
                        <div class="author-avatar">
                            {{ mb_substr($post->creator->name, 0, 1) }}
                        </div>
                        <div class="author-details">
                            <span class="author-name">{{ $post->creator->name }}</span>
                            <span class="post-date" title="{{ $post->created_at }}">{{ $post->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    <div style="display: flex; gap: 0.5rem;">
                        <button class="action-btn" @click="openEditModal({{ json_encode($post) }})" title="تعديل">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                        <form action="{{ route('delegate.announcements.destroy', $post->id) }}" method="POST" onsubmit="return confirm('حذف هذا الخبر؟')" style="margin: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn" title="حذف" style="color: var(--danger-color);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                <h3 class="news-title">{{ $post->title }}</h3>
                <div class="news-content">{{ $post->content }}</div>

                <div class="news-footer">
                    <div class="category-badge">
                        @if($post->category == 'urgent')
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        عاجل
                        @elseif($post->category == 'academic')
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                            <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                        </svg>
                        أكاديمي
                        @else
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                        </svg>
                        عام
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5" style="background: white; border-radius: 20px; border: 2px dashed var(--border-color);">
            <div style="width: 80px; height: 80px; background: #f8fafc; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: #cbd5e1;">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"></path>
                    <path d="M18 14h-8"></path>
                    <path d="M15 18h-5"></path>
                    <path d="M10 6h8v4h-8V6Z"></path>
                </svg>
            </div>
            <h3 class="font-weight-bold" style="color: var(--text-primary); margin-bottom: 0.5rem;">لا توجد أخبار حالياً</h3>
            <p style="color: var(--text-secondary);">يمكنك نشر إعلانات وتعاميم للطلاب وستظهر هنا.</p>
        </div>
        @endforelse
    </div>

    @if($announcements->hasPages())
    <div class="mt-4">
        {{ $announcements->links() }}
    </div>
    @endif


    <!-- Create/Edit Modal (Updated Design) -->
    <div x-show="showModal" class="modal-overlay" style="display: none;" x-transition.opacity>
        <div class="modal-container" @click.away="showModal = false" x-transition.scale>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h3 class="font-weight-800" style="margin: 0; font-size: 1.5rem;" x-text="modalTitle"></h3>
                <button type="button" @click="showModal = false" style="background: #f1f5f9; border: none; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-secondary);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <form :action="formAction" method="POST">
                @csrf
                <input type="hidden" name="_method" :value="formMethod">

                <div class="row g-4" style="text-align: right;">
                    <div class="col-md-12">
                        <label class="form-label" style="font-size: 0.9rem; color: var(--text-secondary);">نوع الخبر</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                            <!-- General -->
                            <label style="cursor: pointer; position: relative;">
                                <input type="radio" name="category" value="general" x-model="formData.category" class="d-none peer">
                                <div class="p-3 text-center border rounded-3 transition-all"
                                    :style="formData.category === 'general' ? 'border-color: var(--primary-color); background: #eff6ff; color: var(--primary-color); font-weight: 700;' : 'border-color: var(--border-color); color: var(--text-secondary);'">
                                    عام
                                </div>
                            </label>
                            <!-- Academic -->
                            <label style="cursor: pointer; position: relative;">
                                <input type="radio" name="category" value="academic" x-model="formData.category" class="d-none peer">
                                <div class="p-3 text-center border rounded-3 transition-all"
                                    :style="formData.category === 'academic' ? 'border-color: var(--info-color); background: #eff6ff; color: var(--info-color); font-weight: 700;' : 'border-color: var(--border-color); color: var(--text-secondary);'">
                                    أكاديمي
                                </div>
                            </label>
                            <!-- Urgent -->
                            <label style="cursor: pointer; position: relative;">
                                <input type="radio" name="category" value="urgent" x-model="formData.category" class="d-none peer">
                                <div class="p-3 text-center border rounded-3 transition-all"
                                    :style="formData.category === 'urgent' ? 'border-color: var(--danger-color); background: #fef2f2; color: var(--danger-color); font-weight: 700;' : 'border-color: var(--border-color); color: var(--text-secondary);'">
                                    عاجل
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label" style="font-size: 0.9rem; color: var(--text-secondary);">عنوان الخبر</label>
                        <input type="text" name="title" x-model="formData.title" class="form-control form-control-lg" required placeholder="مثال: تغيير موعد محاضرة البرمجة" style="font-weight: 600;">
                    </div>

                    <div class="col-12">
                        <label class="form-label" style="font-size: 0.9rem; color: var(--text-secondary);">التفاصيل</label>
                        <textarea name="content" x-model="formData.content" class="form-control" rows="5" required placeholder="اكتب تفاصيل الخبر هنا..." style="resize: none;"></textarea>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px dashed var(--border-color);">
                    <button type="button" class="btn btn-secondary px-4" @click="showModal = false" style="background: white; border: 1px solid var(--border-color);">إلغاء</button>
                    <button type="submit" class="btn btn-primary px-5 py-2.5" style="border-radius: 10px; font-weight: 700;">نشر الخبر</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function newsManager() {
        return {
            showModal: false,
            modalTitle: 'نشر خبر جديد',
            formAction: "{{ route('delegate.announcements.store') }}",
            formMethod: 'POST',
            formData: {
                category: 'general',
                title: '',
                content: ''
            },
            openCreateModal() {
                this.modalTitle = 'نشر خبر جديد';
                this.formAction = "{{ route('delegate.announcements.store') }}";
                this.formMethod = 'POST';
                this.formData = {
                    category: 'general',
                    title: '',
                    content: ''
                };
                this.showModal = true;
            },
            openEditModal(post) {
                this.modalTitle = 'تعديل الخبر';
                this.formAction = `/delegate/announcements/${post.id}`;
                this.formMethod = 'PUT';
                this.formData = {
                    category: post.category,
                    title: post.title,
                    content: post.content
                };
                this.showModal = true;
            }
        }
    }
</script>

<!-- Ensure Bootstrap JS for Dropdowns -->
@if(!View::hasSection('scripts_loaded'))
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endif

@endsection