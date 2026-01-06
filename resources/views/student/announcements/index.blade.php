@extends('layouts.student')

@section('title', 'الأخبار والإعلانات')

@section('content')

<!-- Header -->
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem;">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        الأخبار والإعلانات
    </h1>
    <p style="color: var(--text-secondary);">آخر المستجدات الأكاديمية والإعلانات الهامة</p>
</div>

<!-- Filters -->
<div class="mb-4 d-flex gap-2">
    <a href="{{ route('student.announcements.index') }}"
        class="btn {{ is_null($category) ? 'btn-primary' : 'btn-light button-outline' }}"
        style="border-radius: 20px; padding: 0.5rem 1.25rem;">
        الكل
    </a>
    <a href="{{ route('student.announcements.index', ['category' => 'academic']) }}"
        class="btn {{ $category == 'academic' ? 'btn-primary' : 'btn-light button-outline' }}"
        style="border-radius: 20px; padding: 0.5rem 1.25rem;">
        أكاديمي
    </a>
    <a href="{{ route('student.announcements.index', ['category' => 'general']) }}"
        class="btn {{ $category == 'general' ? 'btn-primary' : 'btn-light button-outline' }}"
        style="border-radius: 20px; padding: 0.5rem 1.25rem;">
        عام
    </a>
    <a href="{{ route('student.announcements.index', ['category' => 'urgent']) }}"
        class="btn {{ $category == 'urgent' ? 'btn-danger text-white' : 'btn-light button-outline' }}"
        style="border-radius: 20px; padding: 0.5rem 1.25rem;">
        عاجل
    </a>
</div>

<style>
    .button-outline {
        border: 1px solid var(--border-color);
        background: white;
        color: var(--text-secondary);
    }

    .button-outline:hover {
        background: #f8fafc;
        color: var(--text-primary);
    }
</style>

<!-- Announcements List -->
@if($announcements->count() > 0)
<div style="display: flex; flex-direction: column; gap: 1.5rem;">
    @foreach($announcements as $post)
    <div class="card" style="border: none; position: relative; overflow: hidden; padding: 1.5rem;">
        <!-- Category Stripe -->
        <div style="position: absolute; right: 0; top: 0; bottom: 0; width: 4px; background: {{ $post->category == 'urgent' ? 'var(--danger-color)' : ($post->category == 'academic' ? 'var(--info-color)' : 'var(--secondary-color)') }};"></div>

        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span class="badge {{ $post->category == 'urgent' ? 'badge-danger' : ($post->category == 'academic' ? 'badge-info' : 'badge-secondary') }}">
                    {{ $post->category == 'urgent' ? 'عاجل' : ($post->category == 'academic' ? 'أكاديمي' : 'عام') }}
                </span>
                <span style="font-size: 0.8rem; color: var(--text-secondary);">{{ $post->created_at->diffForHumans() }}</span>
            </div>
        </div>

        <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.75rem;">{{ $post->title }}</h3>

        <p style="color: var(--text-secondary); font-size: 1rem; line-height: 1.6; margin-bottom: 1.5rem;">
            {!! nl2br(e($post->content)) !!}
        </p>

        <div style="padding-top: 1rem; border-top: 1px solid #f1f5f9; display: flex; align-items: center; gap: 0.5rem;">
            <div style="width: 28px; height: 28px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: bold; color: var(--text-secondary);">
                {{ mb_substr($post->creator->name, 0, 1) }}
            </div>
            <span style="font-size: 0.9rem; color: var(--text-secondary);">بواسطة: {{ $post->creator->name }}</span>
        </div>
    </div>
    @endforeach

    <!-- Pagination -->
    <div class="mt-4">
        {{ $announcements->withQueryString()->links() }}
    </div>
</div>
@else
<div class="card" style="text-align: center; padding: 3rem;">
    <div style="width: 60px; height: 60px; background: #f3f4f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: var(--text-secondary);">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
    </div>
    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">لا توجد إعلانات</h3>
    <p style="color: var(--text-secondary);">لا توجد أخبار في هذا التصنيف حالياً.</p>
</div>
@endif

@endsection