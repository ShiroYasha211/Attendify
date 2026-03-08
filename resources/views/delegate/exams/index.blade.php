@extends('layouts.delegate')

@section('title', 'جداول الاختبارات')

@section('content')

<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .header-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .header-icon {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 4px 12px -2px rgba(239, 68, 68, 0.4);
    }

    .header-text h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .header-text p {
        color: var(--text-secondary);
        font-size: 0.9rem;
    }

    .btn-create {
        padding: 0.875rem 1.5rem;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        border: none;
        border-radius: 12px;
        font-weight: 600;
        color: white;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-create:hover {
        box-shadow: 0 4px 12px -2px rgba(239, 68, 68, 0.4);
        transform: translateY(-1px);
    }

    /* Stats Row */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.25rem;
        border: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-card .icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-card .value {
        font-size: 1.75rem;
        font-weight: 700;
    }

    .stat-card .label {
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    /* Schedules Grid */
    .schedules-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 1.5rem;
    }

    .schedule-card {
        background: white;
        border-radius: 20px;
        border: 1px solid var(--border-color);
        overflow: hidden;
        transition: all 0.3s;
    }

    .schedule-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.12);
    }

    .schedule-header {
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, #fef2f2 0%, #fff 100%);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .schedule-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.35rem;
    }

    .schedule-term {
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    .schedule-status {
        padding: 0.35rem 0.875rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .schedule-status.published {
        background: #d1fae5;
        color: #065f46;
    }

    .schedule-status.draft {
        background: #fef3c7;
        color: #92400e;
    }

    .schedule-body {
        padding: 1.25rem 1.5rem;
    }

    .schedule-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .meta-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .meta-info .value {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .meta-info .label {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .schedule-description {
        font-size: 0.9rem;
        color: var(--text-secondary);
        padding: 0.75rem;
        background: #f8fafc;
        border-radius: 8px;
        margin-top: 0.5rem;
    }

    .schedule-actions {
        padding: 1rem 1.5rem;
        background: #fafafa;
        border-top: 1px solid var(--border-color);
        display: flex;
        gap: 0.5rem;
    }

    .action-btn {
        flex: 1;
        padding: 0.75rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .action-btn.view {
        background: #f1f5f9;
        color: var(--text-primary);
    }

    .action-btn.edit {
        background: #e0f2fe;
        color: #0284c7;
    }

    .action-btn.delete {
        background: #fee2e2;
        color: #dc2626;
    }

    .action-btn:hover {
        transform: translateY(-1px);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 20px;
        border: 1px solid var(--border-color);
    }

    .empty-state svg {
        margin-bottom: 1rem;
        opacity: 0.4;
    }

    .empty-state h3 {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: var(--text-secondary);
        margin-bottom: 1.5rem;
    }

    /* Modal */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-container {
        background: white;
        border-radius: 20px;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        overflow-y: auto;
        padding: 1.5rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
    }

    .modal-header h3 {
        font-size: 1.25rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--text-secondary);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .form-row.full {
        grid-template-columns: 1fr;
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .form-group label span {
        color: #ef4444;
    }

    .items-section {
        background: #f8fafc;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        max-height: 300px;
        overflow-y: auto;
    }

    .items-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .items-header h4 {
        font-size: 1rem;
        font-weight: 700;
    }

    .btn-add-item {
        padding: 0.4rem 0.75rem;
        background: #e0f2fe;
        color: #0284c7;
        border: none;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
    }

    .item-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        position: relative;
    }

    .item-remove {
        position: absolute;
        top: 0.5rem;
        left: 0.5rem;
        background: none;
        border: none;
        color: #ef4444;
        cursor: pointer;
    }

    .item-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        gap: 0.75rem;
    }

    .item-grid.row2 {
        grid-template-columns: 1fr 1fr 1fr;
        margin-top: 0.75rem;
    }

    .modal-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
    }

    .modal-actions .btn-primary {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }
</style>

<div x-data="{ 
    showCreateModal: false,
    viewMode: 'cards', // 'cards' or 'calendar'
    items: [{id: 1, subject_id: '', exam_date: '', start_time: '', end_time: '', location: ''}],
    addItem() {
        this.items.push({
            id: Date.now(),
            subject_id: '',
            exam_date: '',
            start_time: '',
            end_time: '',
            location: ''
        });
    },
    removeItem(index) {
        if (this.items.length > 1) {
            this.items.splice(index, 1);
        } else {
            alert('يجب إضافة مادة واحدة على الأقل');
        }
    }
}">

    <!-- Page Header -->
    <div class="page-header">
        <div class="header-info">
            <div class="header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
            </div>
            <div class="header-text">
                <h1>جداول الاختبارات</h1>
                <p>إدارة جداول اختبارات الدفعة</p>
            </div>
        </div>
        <button @click="showCreateModal = true" class="btn-create">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            إنشاء جدول جديد
        </button>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 1.5rem;">{{ session('success') }}</div>
    @endif

    @if($errors->any())
    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem;">
        <ul style="margin: 0; padding-right: 1rem; color: #dc2626;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
            <div>
                <div class="value" style="color: #ef4444;">{{ $schedules->count() }}</div>
                <div class="label">جدول اختبارات</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <div>
                <div class="value" style="color: #10b981;">{{ $schedules->where('is_published', true)->count() }}</div>
                <div class="label">جدول منشور</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
            </div>
            <div>
                <div class="value" style="color: #f59e0b;">{{ $schedules->where('is_published', false)->count() }}</div>
                <div class="label">مسودة</div>
            </div>
        </div>
    </div>

    @if($schedules->isEmpty())
    <div class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        <h3>لا توجد جداول اختبارات</h3>
        <p>ابدأ بإنشاء جدول اختبارات جديد للدفعة</p>
        <button @click="showCreateModal = true" class="btn-create" style="display: inline-flex; margin: 0 auto;">
            إنشاء أول جدول
        </button>
    </div>
    @else

    <!-- View Options -->
    <div style="display: flex; justify-content: flex-end; margin-bottom: 1.5rem;">
        <div style="display: flex; background: white; border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden; padding: 0.25rem;">
            <button @click="viewMode = 'cards'" :style="viewMode === 'cards' ? 'background: var(--primary-color); color: white;' : 'background: transparent; color: var(--text-secondary);'" style="padding: 0.5rem 1rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                بطاقات
            </button>
            <button @click="viewMode = 'calendar'" :style="viewMode === 'calendar' ? 'background: var(--primary-color); color: white;' : 'background: transparent; color: var(--text-secondary);'" style="padding: 0.5rem 1rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                مفكرة
            </button>
        </div>
    </div>

    <!-- Schedules Grid (Cards View) -->
    <div class="schedules-grid" x-show="viewMode === 'cards'">
        @foreach($schedules as $schedule)
        <div class="schedule-card">
            <div class="schedule-header">
                <div>
                    <div class="schedule-title">{{ $schedule->title }}</div>
                    <div class="schedule-term">{{ $schedule->term->name ?? '-' }}</div>
                </div>
                <span class="schedule-status {{ $schedule->is_published ? 'published' : 'draft' }}">
                    {{ $schedule->is_published ? 'منشور' : 'مسودة' }}
                </span>
            </div>

            <div class="schedule-body">
                <div class="schedule-meta">
                    <div class="meta-item">
                        <div class="meta-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                        </div>
                        <div class="meta-info">
                            <div class="value">{{ $schedule->items_count ?? $schedule->items()->count() }}</div>
                            <div class="label">مادة</div>
                        </div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </div>
                        <div class="meta-info">
                            <div class="value">{{ $schedule->created_at->format('d/m') }}</div>
                            <div class="label">تاريخ الإنشاء</div>
                        </div>
                    </div>
                </div>

                @if($schedule->description)
                <div class="schedule-description">{{ Str::limit($schedule->description, 80) }}</div>
                @endif
            </div>

            <div class="schedule-actions">
                <a href="{{ route('delegate.exams.show', $schedule->id) }}" class="action-btn view">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    عرض
                </a>
                <a href="{{ route('delegate.exams.edit', $schedule->id) }}" class="action-btn edit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    تعديل
                </a>
                <form action="{{ route('delegate.exams.destroy', $schedule->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟');" style="flex: 1;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-btn delete" style="width: 100%;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                        حذف
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    @if($schedules->hasPages() && false) <!-- Hidden in specific view mode if complex -->
    <div style="margin-top: 2rem;" x-show="viewMode === 'cards'">
        {{ $schedules->links() }}
    </div>
    @endif

    <!-- Calendar View -->
    <div x-show="viewMode === 'calendar'" style="display: none;">
        <div style="background: white; border-radius: 20px; border: 1px solid var(--border-color); padding: 1.5rem; overflow-x: auto;">
            <div class="table-responsive">
<table style="width: 100%; min-width: 800px; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="padding: 1rem; border-bottom: 2px solid var(--border-color); text-align: right; color: var(--text-secondary);">التاريخ</th>
                        <th style="padding: 1rem; border-bottom: 2px solid var(--border-color); text-align: right; color: var(--text-secondary);">المادة</th>
                        <th style="padding: 1rem; border-bottom: 2px solid var(--border-color); text-align: right; color: var(--text-secondary);">الوقت</th>
                        <th style="padding: 1rem; border-bottom: 2px solid var(--border-color); text-align: right; color: var(--text-secondary);">القاعة</th>
                        <th style="padding: 1rem; border-bottom: 2px solid var(--border-color); text-align: right; color: var(--text-secondary);">الجدول</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    // Flatten items and sort by date
                    $allItems = collect();
                    foreach($schedules as $sched) {
                    foreach($sched->items as $item) {
                    $item->parent_schedule = $sched;
                    $allItems->push($item);
                    }
                    }
                    $allItems = $allItems->sortBy('exam_date');
                    @endphp

                    @forelse($allItems as $item)
                    <tr>
                        <td style="padding: 1rem; border-bottom: 1px solid var(--border-color); font-weight: 600;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--primary-color);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                {{ \Carbon\Carbon::parse($item->exam_date)->translatedFormat('l, d F Y') }}
                            </div>
                        </td>
                        <td style="padding: 1rem; border-bottom: 1px solid var(--border-color); font-weight: 700;">{{ $item->subject->name ?? '-' }}</td>
                        <td style="padding: 1rem; border-bottom: 1px solid var(--border-color);">
                            <span style="background: #f1f5f9; padding: 0.25rem 0.75rem; border-radius: 99px; font-size: 0.85rem; font-weight: 600; color: #475569;">
                                {{ \Carbon\Carbon::parse($item->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($item->end_time)->format('h:i A') }}
                            </span>
                        </td>
                        <td style="padding: 1rem; border-bottom: 1px solid var(--border-color); color: var(--text-secondary);">{{ $item->location ?? 'غير محدد' }}</td>
                        <td style="padding: 1rem; border-bottom: 1px solid var(--border-color);">
                            <a href="{{ route('delegate.exams.show', $item->parent_schedule->id) }}" style="color: var(--primary-color); text-decoration: none; font-weight: 600; font-size: 0.9rem;">
                                {{ Str::limit($item->parent_schedule->title, 20) }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="padding: 2rem; text-align: center; color: var(--text-secondary);">لا توجد مواد مجدولة للوهلة الحالية.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
</div>
        </div>
    </div>

    @endif

    <!-- Create Modal -->
    <div x-show="showCreateModal" class="modal-overlay" style="display: none;" x-transition.opacity>
        <div class="modal-container" @click.away="showCreateModal = false">
            <div class="modal-header">
                <h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    إنشاء جدول اختبارات جديد
                </h3>
                <button @click="showCreateModal = false" class="modal-close">&times;</button>
            </div>

            <form action="{{ route('delegate.exams.store') }}" method="POST">
                @csrf

                <div class="form-row full">
                    <div class="form-group">
                        <label>عنوان الجدول <span>*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="مثال: جدول الاختبارات النهائية" required list="title_suggestions">
                        <datalist id="title_suggestions">
                            <option value="جدول الاختبارات النهائية">
                            <option value="جدول الاختبارات النصفية">
                            <option value="جدول اختبارات الدور الثاني">
                        </datalist>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>الفصل الدراسي <span>*</span></label>
                        <select name="term_id" class="form-control" required>
                            <option value="">اختر الفصل...</option>
                            @foreach($terms as $term)
                            <option value="{{ $term->id }}">{{ $term->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="display: flex; align-items: flex-end;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="is_published" value="1" style="width: 18px; height: 18px;">
                            <span style="font-weight: 600;">نشر الجدول للطلاب فوراً</span>
                        </label>
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label>ملاحظات</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="أي تعليمات إضافية..."></textarea>
                    </div>
                </div>

                <!-- Items Section -->
                <div class="items-section">
                    <div class="items-header">
                        <h4>📚 المواد الدراسية</h4>
                        <button type="button" @click="addItem()" class="btn-add-item">+ إضافة مادة</button>
                    </div>

                    <template x-for="(item, index) in items" :key="item.id">
                        <div class="item-card">
                            <button type="button" @click="removeItem(index)" class="item-remove" title="حذف المادة">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>

                            <div class="item-grid">
                                <div class="form-group">
                                    <label style="font-size: 0.85rem;">المادة</label>
                                    <select :name="'items[' + index + '][subject_id]'" class="form-control" required x-model="item.subject_id">
                                        <option value="">اختر المادة...</option>
                                        @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label style="font-size: 0.85rem;">التاريخ</label>
                                    <input type="date" :name="'items[' + index + '][exam_date]'" class="form-control" required x-model="item.exam_date">
                                </div>
                                <div class="form-group">
                                    <label style="font-size: 0.85rem;">القاعة</label>
                                    <input type="text" :name="'items[' + index + '][location]'" class="form-control" placeholder="اختياري" x-model="item.location">
                                </div>
                            </div>
                            <div class="item-grid row2">
                                <div class="form-group">
                                    <label style="font-size: 0.85rem;">من</label>
                                    <input type="time" :name="'items[' + index + '][start_time]'" class="form-control" required x-model="item.start_time">
                                </div>
                                <div class="form-group">
                                    <label style="font-size: 0.85rem;">إلى</label>
                                    <input type="time" :name="'items[' + index + '][end_time]'" class="form-control" required x-model="item.end_time">
                                </div>
                                <div></div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary">حفظ الجدول</button>
                    <button type="button" class="btn btn-secondary" @click="showCreateModal = false">إلغاء</button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection