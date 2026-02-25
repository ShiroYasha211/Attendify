@extends('layouts.admin')

@section('title', 'إعدادات النظام')

@section('content')

<style>
    .page-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .page-header-icon {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 4px 12px -2px rgba(99, 102, 241, 0.4);
    }

    .page-header-text h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .page-header-text p {
        color: var(--text-secondary);
        font-size: 0.9rem;
    }

    .settings-content {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        max-width: 800px;
    }

    .settings-section {
        background: white;
        border-radius: 20px;
        border: 1px solid var(--border-color);
        overflow: hidden;
    }

    .section-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .section-header .icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .section-header h3 {
        font-size: 1.1rem;
        font-weight: 700;
    }

    .section-header p {
        font-size: 0.85rem;
        color: var(--text-secondary);
        margin-top: 0.15rem;
    }

    .settings-list {
        padding: 0.5rem 0;
    }

    .setting-item {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 2rem;
        align-items: center;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f8fafc;
        transition: background 0.2s;
    }

    .setting-item:hover {
        background: #fafafa;
    }

    .setting-item:last-child {
        border-bottom: none;
    }

    .setting-info h4 {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .setting-info p {
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    .setting-control {
        min-width: 200px;
    }

    .setting-control input[type="text"],
    .setting-control input[type="number"] {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        font-size: 0.9rem;
        background: #fafafa;
        transition: all 0.2s;
    }

    .setting-control input:focus {
        border-color: var(--primary-color);
        background: white;
        outline: none;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    /* Toggle Switch */
    .toggle-switch {
        position: relative;
        width: 52px;
        height: 28px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: #e2e8f0;
        border-radius: 28px;
        transition: 0.3s;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 22px;
        width: 22px;
        left: 3px;
        bottom: 3px;
        background: white;
        border-radius: 50%;
        transition: 0.3s;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .toggle-switch input:checked+.toggle-slider {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .toggle-switch input:checked+.toggle-slider:before {
        transform: translateX(24px);
    }

    .toggle-status {
        font-size: 0.85rem;
        font-weight: 500;
        margin-right: 0.75rem;
    }

    .toggle-status.on {
        color: #10b981;
    }

    .toggle-status.off {
        color: var(--text-secondary);
    }

    .save-bar {
        background: white;
        border-radius: 16px;
        border: 1px solid var(--border-color);
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.05);
    }

    .save-bar p {
        color: var(--text-secondary);
        font-size: 0.9rem;
    }

    .save-actions {
        display: flex;
        gap: 0.75rem;
    }

    .btn-reset {
        padding: 0.75rem 1.5rem;
        background: #f1f5f9;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        color: var(--text-secondary);
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-reset:hover {
        background: #e2e8f0;
    }

    .btn-save {
        padding: 0.75rem 2rem;
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        border: none;
        border-radius: 10px;
        font-weight: 600;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }

    .btn-save:hover {
        box-shadow: 0 4px 12px -2px rgba(99, 102, 241, 0.4);
    }

    .success-alert {
        background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        border: 1px solid #10b981;
        color: #065f46;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .success-alert svg {
        flex-shrink: 0;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="3"></circle>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
        </svg>
    </div>
    <div class="page-header-text">
        <h1>إعدادات النظام</h1>
        <p>تخصيص وإدارة الإعدادات العامة والأكاديمية للنظام</p>
    </div>
</div>

@if(session('success'))
<div class="success-alert">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
        <polyline points="22 4 12 14.01 9 11.01"></polyline>
    </svg>
    <span>{{ session('success') }}</span>
</div>
@endif

<form action="{{ route('admin.settings.update') }}" method="POST">
    @csrf
    @method('PUT')

    <div class="settings-content">
        @foreach($settings as $group => $groupSettings)
        <div class="settings-section" id="{{ $group }}">
            <div class="section-header">
                <div class="icon" style="background: {{ $group === 'general' ? 'rgba(99, 102, 241, 0.1)' : ($group === 'academic' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(245, 158, 11, 0.1)') }}; color: {{ $group === 'general' ? '#6366f1' : ($group === 'academic' ? '#10b981' : '#f59e0b') }};">
                    @if($group === 'general')
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4"></path>
                    </svg>
                    @elseif($group === 'academic')
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                    </svg>
                    @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <polyline points="17 11 19 13 23 9"></polyline>
                    </svg>
                    @endif
                </div>
                <div>
                    <h3>{{ \App\Models\Setting::getGroupLabel($group) }}</h3>
                    <p>
                        @if($group === 'general')
                        إعدادات عامة للنظام والمظهر
                        @elseif($group === 'academic')
                        إعدادات متعلقة بالنظام الأكاديمي
                        @else
                        إعدادات أخرى للنظام
                        @endif
                    </p>
                </div>
            </div>

            <div class="settings-list">
                @foreach($groupSettings as $setting)
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>{{ $setting->label }}</h4>
                        <p>{{ $setting->description }}</p>
                    </div>
                    <div class="setting-control">
                        @if($setting->type === 'boolean')
                        <div style="display: flex; align-items: center; justify-content: flex-end;">
                            <span class="toggle-status {{ $setting->value ? 'on' : 'off' }}">
                                {{ $setting->value ? 'مفعّل' : 'معطّل' }}
                            </span>
                            <label class="toggle-switch">
                                <input type="checkbox" name="{{ $setting->key }}" value="1" {{ $setting->value ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        @elseif($setting->type === 'number')
                        <input type="number" name="{{ $setting->key }}" value="{{ $setting->value }}">
                        @else
                        <input type="text" name="{{ $setting->key }}" value="{{ $setting->value }}">
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <!-- Save Bar -->
        <div class="save-bar">
            <p>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-left: 0.25rem;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                سيتم حفظ جميع التغييرات في الإعدادات
            </p>
            <div class="save-actions">
                <button type="reset" class="btn-reset">إعادة تعيين</button>
                <button type="submit" class="btn-save">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    حفظ الإعدادات
                </button>
            </div>
        </div>
    </div>
</form>

<script>
    // Update toggle status text on change
    document.querySelectorAll('.toggle-switch input').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const statusSpan = this.closest('.setting-control').querySelector('.toggle-status');
            if (this.checked) {
                statusSpan.textContent = 'مفعّل';
                statusSpan.classList.remove('off');
                statusSpan.classList.add('on');
            } else {
                statusSpan.textContent = 'معطّل';
                statusSpan.classList.remove('on');
                statusSpan.classList.add('off');
            }
        });
    });
</script>

@endsection