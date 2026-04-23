@extends('layouts.doctor')

@section('title', 'تفاصيل الاستفسار')

@section('content')
<style>
    .page-shell {
        max-width: 1080px;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        color: #64748b;
        font-weight: 800;
        margin-bottom: 1.2rem;
    }

    .back-link:hover { color: #0f766e; }

    .hero-card {
        background:
            radial-gradient(circle at top right, rgba(45, 212, 191, 0.18), transparent 28%),
            linear-gradient(135deg, #0f172a 0%, #1f2937 48%, #0f766e 100%);
        color: #fff;
        border-radius: 28px;
        padding: 1.75rem 1.85rem;
        margin-bottom: 1.4rem;
        box-shadow: 0 28px 60px -36px rgba(15, 23, 42, 0.6);
    }

    .hero-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .hero-title {
        font-size: 1.8rem;
        font-weight: 900;
        margin: 0 0 0.5rem;
    }

    .hero-copy {
        margin: 0;
        color: rgba(255,255,255,0.82);
        line-height: 1.8;
        max-width: 760px;
    }

    .status-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.6rem 0.95rem;
        border-radius: 999px;
        background: rgba(255,255,255,0.12);
        backdrop-filter: blur(12px);
        font-weight: 800;
    }

    .layout-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.65fr) 320px;
        gap: 1.25rem;
    }

    .panel {
        background: rgba(255,255,255,0.97);
        border: 1px solid rgba(148,163,184,0.16);
        border-radius: 24px;
        box-shadow: 0 22px 48px -36px rgba(15,23,42,0.45);
        overflow: hidden;
    }

    .panel-body { padding: 1.45rem; }

    .student-box {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .student-avatar {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        background: linear-gradient(135deg, #ccfbf1 0%, #99f6e4 100%);
        color: #0f766e;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 1.15rem;
    }

    .student-name {
        font-size: 1.05rem;
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 0.2rem;
    }

    .student-submeta {
        color: #64748b;
        font-size: 0.88rem;
    }

    .meta-row {
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    .meta-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.46rem 0.8rem;
        border-radius: 999px;
        background: #f8fafc;
        color: #475569;
        font-size: 0.8rem;
        font-weight: 800;
        border: 1px solid rgba(148,163,184,0.14);
    }

    .section-card {
        background: #f8fafc;
        border: 1px solid rgba(226,232,240,0.92);
        border-radius: 20px;
        padding: 1.2rem;
    }

    .section-label {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        font-size: 0.78rem;
        font-weight: 900;
        color: #64748b;
        margin-bottom: 0.8rem;
    }

    .question-title {
        font-size: 1.18rem;
        font-weight: 900;
        color: #0f172a;
        margin: 0 0 0.7rem;
        line-height: 1.7;
    }

    .question-text,
    .answer-text {
        color: #334155;
        line-height: 1.95;
        white-space: pre-wrap;
        margin: 0;
    }

    .answer-card {
        margin-top: 1rem;
        background: #ecfdf5;
        border: 1px solid rgba(16,185,129,0.16);
    }

    .answer-card .section-label {
        color: #047857;
    }

    .actor-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        margin-top: 0.9rem;
        padding: 0.48rem 0.85rem;
        border-radius: 999px;
        background: rgba(16, 185, 129, 0.12);
        color: #065f46;
        font-size: 0.82rem;
        font-weight: 800;
    }

    .side-panel {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .notice-card,
    .reply-card {
        padding: 1.35rem;
    }

    .card-title {
        font-size: 1rem;
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 0.45rem;
    }

    .card-copy {
        color: #64748b;
        line-height: 1.8;
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }

    .reply-card textarea {
        width: 100%;
        min-height: 190px;
        border-radius: 18px;
        border: 1px solid rgba(148,163,184,0.18);
        background: #f8fafc;
        padding: 1rem 1.05rem;
        font-family: inherit;
        font-size: 0.98rem;
        resize: vertical;
        margin-bottom: 0.9rem;
    }

    .reply-card textarea:focus {
        outline: none;
        background: #fff;
        border-color: #0f766e;
        box-shadow: 0 0 0 3px rgba(15,118,110,0.1);
    }

    .btn-submit {
        width: 100%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        border: none;
        border-radius: 14px;
        padding: 0.92rem 1rem;
        font-weight: 800;
        cursor: pointer;
        color: #fff;
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        box-shadow: 0 18px 34px -26px rgba(15,118,110,0.85);
    }

    .notice-card {
        background: linear-gradient(180deg, #eff6ff, #f8fbff);
        border: 1px solid rgba(59,130,246,0.16);
    }

    .notice-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.85rem;
        background: rgba(37,99,235,0.1);
        color: #1d4ed8;
    }

    @media (max-width: 980px) {
        .layout-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="page-shell">
    <a href="{{ route('doctor.inquiries.index') }}" class="back-link">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
        العودة إلى الاستفسارات
    </a>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="hero-card">
        <div class="hero-top">
            <div>
                <h1 class="hero-title">{{ $inquiry->title }}</h1>
                <p class="hero-copy">
                    هذا الاستفسار وصل إلى مساحة الدكتور لأنه حُوّل إليك من المندوب أو لأنك سبق أن أجبت عليه. إذا كانت حالته بانتظار الرد فهذه هي نقطة الحسم النهائية على مستوى الدكتور.
                </p>
            </div>

            <div class="status-chip">{{ $inquiry->status_label }}</div>
        </div>
    </div>

    <div class="layout-grid">
        <div class="panel">
            <div class="panel-body">
                <div class="student-box">
                    <div class="student-avatar">{{ mb_substr($inquiry->student->name ?? '?', 0, 1) }}</div>
                    <div>
                        <div class="student-name">{{ $inquiry->student->name ?? 'طالب' }}</div>
                        <div class="student-submeta">{{ $inquiry->student->student_number ?: 'بدون رقم قيد' }}</div>
                    </div>
                </div>

                <div class="meta-row">
                    <span class="meta-chip">{{ $inquiry->subject->name ?? 'مادة غير محددة' }}</span>
                    <span class="meta-chip">أُرسل {{ $inquiry->created_at->format('Y-m-d H:i') }}</span>
                    @if($inquiry->answered_at)
                        <span class="meta-chip">آخر رد {{ $inquiry->answered_at->format('Y-m-d H:i') }}</span>
                    @endif
                </div>

                <div class="section-card">
                    <div class="section-label">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        سؤال الطالب
                    </div>
                    <h2 class="question-title">{{ $inquiry->title }}</h2>
                    <p class="question-text">{{ $inquiry->question }}</p>
                </div>

                @if($inquiry->answer)
                    <div class="section-card answer-card">
                        <div class="section-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            الرد المعتمد
                        </div>
                        <p class="answer-text">{{ $inquiry->answer }}</p>

                        @if($inquiry->answered_by_actor_name)
                            <div class="actor-pill">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                تم الرد بواسطة {{ $inquiry->answered_by_actor_label }}: {{ $inquiry->answered_by_actor_name }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="side-panel">
            @if($inquiry->status === 'forwarded')
                <div class="panel reply-card">
                    <div class="card-title">رد الدكتور</div>
                    <div class="card-copy">
                        هذا الاستفسار حُوّل إليك من المندوب وينتظر إجابتك. بمجرد الرد سيتحول إلى حالة "تم الرد" ويظهر للطالب مع اسم الجهة التي أجابت.
                    </div>

                    <form action="{{ route('doctor.inquiries.answer', $inquiry->id) }}" method="POST">
                        @csrf
                        <textarea name="answer" placeholder="اكتب إجابة الدكتور هنا..." required>{{ old('answer') }}</textarea>
                        @error('answer')
                            <div class="text-danger mb-3">{{ $message }}</div>
                        @enderror
                        <button type="submit" class="btn-submit">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                            إرسال الإجابة
                        </button>
                    </form>
                </div>
            @else
                <div class="panel notice-card">
                    <div class="notice-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="card-title">السجل النهائي المعروض للطبيب</div>
                    <div class="card-copy" style="margin-bottom:0;">
                        هذا الاستفسار لم يعد بانتظار الرد. ما يظهر هنا هو السجل النهائي الذي يخص الدكتور فقط، لذلك لن تظهر في هذه الصفحة استفسارات حُلّت عند المندوب من الأساس.
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
