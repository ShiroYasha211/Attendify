{{-- This view is identical to create_checklist but with $checklist data --}}
@extends('layouts.doctor')
@section('title', 'تعديل قائمة التقييم')
@section('content')
<style>
    .clinical-page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.75rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .clinical-page-header .right-side h1 {
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--text-primary);
        margin: 0 0 0.15rem 0;
    }

    .clinical-page-header .right-side p {
        color: var(--text-secondary);
        font-size: 0.9rem;
        margin: 0;
    }

    .btn-back {
        background: white;
        color: var(--text-secondary);
        border: 1.5px solid #e2e8f0;
        padding: 0.55rem 1.1rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.88rem;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }

    .btn-back:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        color: var(--text-primary);
        text-decoration: none;
    }

    .card-section {
        background: white;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .form-group {
        margin-bottom: 1.15rem;
    }

    .form-label {
        display: block;
        font-weight: 600;
        font-size: 0.88rem;
        color: var(--text-primary);
        margin-bottom: 0.4rem;
    }

    .form-control,
    .form-select {
        width: 100%;
        padding: 0.65rem 0.85rem;
        font-size: 0.9rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
        font-family: inherit;
    }

    .form-control:focus,
    .form-select:focus {
        outline: none;
        border-color: var(--primary-color);
        background: white;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.08);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    .item-row {
        display: flex;
        gap: 0.75rem;
        align-items: center;
        margin-bottom: 0.75rem;
        background: #f8fafc;
        padding: 0.75rem;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
    }

    .item-row input {
        flex: 1;
    }

    .item-row .marks-input {
        width: 80px;
        flex: none;
    }

    .btn-remove-item {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #fef2f2;
        color: #ef4444;
        border: none;
        cursor: pointer;
        font-size: 1.1rem;
        flex: none;
    }

    .btn-add-item {
        background: #f1f5f9;
        color: var(--primary-color);
        border: 1.5px dashed #c7d2fe;
        padding: 0.6rem;
        border-radius: 10px;
        width: 100%;
        font-weight: 600;
        font-size: 0.88rem;
        cursor: pointer;
    }

    .btn-submit {
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        color: white;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.25);
    }
</style>

<div class="clinical-page-header">
    <div class="right-side">
        <h1>تعديل قائمة التقييم ✏️</h1>
        <p>تعديل عناصر وتفاصيل قائمة التقييم</p>
    </div>
    <div class="left-side"><a href="{{ route('doctor.clinical.evaluations.checklists') }}" class="btn-back"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg> قوائم التقييم</a></div>
</div>

<form action="{{ route('doctor.clinical.evaluations.checklists.update', $checklist->id) }}" method="POST">
    @csrf @method('PUT')
    <div class="card-section">
        <div class="form-row">
            <div class="form-group"><label class="form-label">عنوان القائمة *</label><input type="text" name="title" class="form-control" value="{{ $checklist->title }}" required></div>
            <div class="form-group"><label class="form-label">نوع المهارة *</label>
                <select name="skill_type" class="form-select" required>
                    <option value="history_taking" {{ $checklist->skill_type == 'history_taking' ? 'selected' : '' }}>أخذ قصة مرضية</option>
                    <option value="clinical_examination" {{ $checklist->skill_type == 'clinical_examination' ? 'selected' : '' }}>فحص سريري</option>
                    <option value="procedure" {{ $checklist->skill_type == 'procedure' ? 'selected' : '' }}>إجراء طبي</option>
                    <option value="communication" {{ $checklist->skill_type == 'communication' ? 'selected' : '' }}>مهارات تواصل</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group"><label class="form-label">المدة (دقائق) *</label><input type="number" name="time_limit_minutes" class="form-control" value="{{ $checklist->time_limit_minutes }}" min="1" max="120" required></div>
            <div class="form-group"><label class="form-label">وصف</label><input type="text" name="description" class="form-control" value="{{ $checklist->description }}"></div>
        </div>
    </div>
    <div class="card-section">
        <h3 style="font-weight:700; margin-bottom:1rem;">عناصر التقييم</h3>
        <div id="items-container"></div>
        <button type="button" class="btn-add-item" onclick="addItem()">+ إضافة عنصر</button>
    </div>
    <div style="text-align:left;margin-top:1rem;"><button type="submit" class="btn-submit">💾 تحديث القائمة</button></div>
</form>
@endsection
@push('scripts')
<script>
    let itemIndex = 0;
    const existingItems = @json($checklist - > items - > toArray());

    function addItem(desc = '', marks = 5) {
        const container = document.getElementById('items-container');
        const div = document.createElement('div');
        div.className = 'item-row';
        div.innerHTML = `<span style="font-weight:700;color:var(--text-secondary);min-width:28px;text-align:center;">${itemIndex+1}</span>
        <input type="text" name="items[${itemIndex}][description]" class="form-control" value="${desc}" required>
        <input type="number" name="items[${itemIndex}][marks]" class="form-control marks-input" value="${marks}" min="1" max="100" required>
        <button type="button" class="btn-remove-item" onclick="this.parentElement.remove()">×</button>`;
        container.appendChild(div);
        itemIndex++;
    }
    document.addEventListener('DOMContentLoaded', function() {
        existingItems.forEach(item => addItem(item.description, item.marks));
    });
</script>
@endpush