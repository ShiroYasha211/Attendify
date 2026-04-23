@extends('layouts.doctor')

@section('title', 'استفسارات الطلاب')

@section('content')
<style>
    .hero-shell {
        background:
            radial-gradient(circle at top right, rgba(52, 211, 153, 0.16), transparent 30%),
            linear-gradient(135deg, #0f172a 0%, #1f2937 48%, #0f766e 100%);
        border-radius: 28px;
        padding: 2rem;
        color: #fff;
        margin-bottom: 1.5rem;
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
        font-size: 2rem;
        font-weight: 900;
        margin: 0 0 0.6rem;
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }

    .hero-copy {
        color: rgba(255,255,255,0.82);
        line-height: 1.9;
        margin: 0;
        max-width: 820px;
    }

    .hero-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.55rem 0.95rem;
        border-radius: 999px;
        background: rgba(255,255,255,0.12);
        font-weight: 800;
        backdrop-filter: blur(12px);
    }

    .section-card {
        background: rgba(255,255,255,0.97);
        border: 1px solid rgba(148,163,184,0.16);
        border-radius: 24px;
        padding: 1.45rem;
        margin-bottom: 1.4rem;
        box-shadow: 0 22px 48px -36px rgba(15,23,42,0.45);
    }

    .section-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .section-title {
        font-size: 1.15rem;
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 0.35rem;
    }

    .section-copy {
        color: #64748b;
        line-height: 1.8;
        margin: 0;
        max-width: 800px;
    }

    .subject-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
    }

    .subject-card {
        border-radius: 20px;
        padding: 1.1rem;
        border: 1px solid rgba(148,163,184,0.16);
        background: linear-gradient(180deg, #f8fafc, #fff);
    }

    .subject-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.8rem;
        margin-bottom: 0.8rem;
    }

    .subject-name {
        font-size: 1rem;
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 0.25rem;
    }

    .subject-meta {
        font-size: 0.86rem;
        color: #64748b;
        line-height: 1.7;
    }

    .subject-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.42rem 0.75rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 800;
    }

    .subject-pill.open {
        background: #dcfce7;
        color: #166534;
    }

    .subject-pill.closed {
        background: #fee2e2;
        color: #991b1b;
    }

    .reason-box {
        background: #fff7ed;
        color: #9a3412;
        border: 1px solid rgba(251, 146, 60, 0.16);
        border-radius: 16px;
        padding: 0.85rem 0.95rem;
        line-height: 1.8;
        font-size: 0.88rem;
        margin-bottom: 0.85rem;
    }

    .settings-form textarea {
        width: 100%;
        min-height: 84px;
        resize: vertical;
        border-radius: 16px;
        border: 1px solid rgba(148,163,184,0.18);
        background: #fff;
        padding: 0.9rem;
        font-family: inherit;
        font-size: 0.94rem;
    }

    .settings-form textarea:focus {
        outline: none;
        border-color: #0f766e;
        box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1);
    }

    .settings-foot {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        margin-top: 0.85rem;
        flex-wrap: wrap;
    }

    .btn-toggle {
        border: none;
        border-radius: 14px;
        padding: 0.8rem 1rem;
        font-weight: 800;
        color: #fff;
        cursor: pointer;
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        box-shadow: 0 18px 34px -26px rgba(15,118,110,0.85);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin-bottom: 1.4rem;
    }

    .stat-card {
        background: rgba(255,255,255,0.97);
        border: 1px solid rgba(148,163,184,0.16);
        border-radius: 22px;
        padding: 1.2rem 1.3rem;
        box-shadow: 0 22px 48px -36px rgba(15,23,42,0.45);
    }

    .stat-label {
        font-size: 0.84rem;
        color: #64748b;
        font-weight: 800;
        margin-bottom: 0.55rem;
    }

    .stat-value {
        font-size: 1.85rem;
        font-weight: 900;
        color: #0f172a;
    }

    .filters-bar {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 1.4rem;
    }

    .filter-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.75rem 1rem;
        border-radius: 999px;
        text-decoration: none;
        font-weight: 800;
        color: #475569;
        background: rgba(255,255,255,0.88);
        border: 1px solid rgba(148,163,184,0.2);
    }

    .filter-pill.active {
        color: #fff;
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        border-color: transparent;
    }

    .inquiries-stack {
        display: flex;
        flex-direction: column;
        gap: 1.1rem;
    }

    .inquiry-card {
        background: rgba(255,255,255,0.97);
        border: 1px solid rgba(148,163,184,0.16);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 22px 48px -36px rgba(15,23,42,0.45);
    }

    .inquiry-topline {
        height: 5px;
        background: linear-gradient(90deg, #0f766e, #2dd4bf);
    }

    .status-forwarded .inquiry-topline { background: linear-gradient(90deg, #0f766e, #2dd4bf); }
    .status-answered .inquiry-topline { background: linear-gradient(90deg, #059669, #34d399); }
    .status-closed .inquiry-topline { background: linear-gradient(90deg, #64748b, #94a3b8); }

    .inquiry-body {
        padding: 1.35rem 1.45rem 1.1rem;
    }

    .inquiry-meta {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .student-box {
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }

    .student-avatar {
        width: 48px;
        height: 48px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        color: #0f766e;
        background: linear-gradient(135deg, #ccfbf1 0%, #99f6e4 100%);
    }

    .student-name {
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 0.2rem;
    }

    .student-submeta {
        color: #64748b;
        font-size: 0.88rem;
        display: flex;
        align-items: center;
        gap: 0.45rem;
        flex-wrap: wrap;
    }

    .chip-row {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.42rem 0.78rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 800;
    }

    .chip.subject { background: #ecfeff; color: #0f766e; }
    .chip.status { background: #f8fafc; color: #334155; border: 1px solid rgba(148,163,184,0.14); }
    .chip.actor { background: #dcfce7; color: #166534; }

    .inquiry-title {
        font-size: 1.08rem;
        font-weight: 900;
        color: #0f172a;
        margin: 0 0 0.55rem;
        line-height: 1.8;
    }

    .inquiry-question {
        color: #475569;
        line-height: 1.9;
        margin: 0 0 1rem;
    }

    .info-band {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.82rem 0.95rem;
        border-radius: 16px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 0.9rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .info-band.answered {
        background: #ecfdf5;
        color: #047857;
    }

    .inquiry-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        padding-top: 1rem;
        border-top: 1px solid rgba(226,232,240,0.85);
    }

    .time-meta {
        color: #94a3b8;
        font-size: 0.84rem;
        font-weight: 700;
    }

    .view-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        text-decoration: none;
        color: #fff;
        font-weight: 800;
        border-radius: 14px;
        padding: 0.8rem 1.1rem;
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        box-shadow: 0 18px 34px -26px rgba(15,118,110,0.85);
    }

    .empty-state {
        background: rgba(255,255,255,0.97);
        border: 1px solid rgba(148,163,184,0.16);
        border-radius: 28px;
        padding: 3.5rem 2rem;
        text-align: center;
        box-shadow: 0 22px 48px -36px rgba(15,23,42,0.45);
    }

    .empty-state h3 {
        margin: 1rem 0 0.5rem;
        font-size: 1.35rem;
        font-weight: 900;
        color: #0f172a;
    }

    .empty-state p {
        margin: 0;
        color: #64748b;
        line-height: 1.9;
    }
</style>

<div class="hero-shell">
    <div class="hero-top">
        <div>
            <h1 class="hero-title">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                استفسارات الطلاب
            </h1>
            <p class="hero-copy">
                هذه الصفحة تعرض فقط الاستفسارات التي وصلت إليك فعليًا بعد تحويلها من المندوب، أو الاستفسارات التي قمت أنت بالرد عليها سابقًا. الاستفسارات التي حُلّت عند المندوب لا تظهر هنا.
            </p>
        </div>

        <div class="hero-chip">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 8v4l3 3"></path>
                <circle cx="12" cy="12" r="10"></circle>
            </svg>
            {{ $stats['forwarded'] }} بانتظار ردك
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

<div class="section-card">
    <div class="section-head">
        <div>
            <div class="section-title">إدارة فتح وإغلاق الاستفسارات</div>
            <p class="section-copy">
                تحكم في استقبال الاستفسارات لكل مادة على حدة. إغلاق الاستفسارات يمنع الطلاب من إرسال طلبات جديدة على المادة، بينما تبقى السجلات السابقة محفوظة وقابلة للعرض.
            </p>
        </div>
    </div>

    @if($subjects->count())
        <div class="subject-grid">
            @foreach($subjects as $subject)
                @php
                    $enabled = (bool) $subject->inquiries_enabled;
                    $reason = $subject->inquiries_closed_reason;
                @endphp
                <div class="subject-card">
                    <div class="subject-top">
                        <div>
                            <div class="subject-name">{{ $subject->name }}</div>
                            <div class="subject-meta">
                                {{ $subject->code ?: 'لا يوجد كود للمادة' }}
                                @if($subject->level?->name)
                                    <br>{{ $subject->level->name }}
                                @endif
                            </div>
                        </div>

                        <span class="subject-pill {{ $enabled ? 'open' : 'closed' }}">
                            {{ $enabled ? 'مفتوحة' : 'مغلقة' }}
                        </span>
                    </div>

                    @if(!$enabled)
                        <div class="reason-box">
                            {{ $reason ?: 'لا يوجد سبب مسجل للإغلاق.' }}
                        </div>
                    @endif

                    <form class="settings-form" method="POST" action="{{ route('doctor.inquiries.settings.update', $subject->id) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="inquiries_enabled" value="{{ $enabled ? 0 : 1 }}">

                        <textarea
                            name="inquiries_closed_reason"
                            placeholder="سبب الإغلاق اختياري، ويظهر للطالب عند إغلاق الاستفسارات.">{{ old('inquiries_closed_reason', $reason) }}</textarea>

                        <div class="settings-foot">
                            <button type="submit" class="btn-toggle">
                                {{ $enabled ? 'إغلاق الاستفسارات' : 'فتح الاستفسارات' }}
                            </button>

                            <div style="font-size:0.82rem; color:#64748b;">
                                {{ $enabled ? 'سيتم إيقاف استقبال استفسارات جديدة لهذه المادة.' : 'سيتم السماح بإرسال استفسارات جديدة لهذه المادة.' }}
                            </div>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state" style="padding:2rem;">
            <h3>لا توجد مواد مرتبطة بهذا الحساب</h3>
            <p>لن تظهر أدوات إدارة الاستفسارات قبل ربط هذا الدكتور بمواد دراسية.</p>
        </div>
    @endif
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">إجمالي ما يظهر للطبيب</div>
        <div class="stat-value">{{ $stats['total'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">بانتظار الرد</div>
        <div class="stat-value" style="color:#0f766e;">{{ $stats['forwarded'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">تم الرد عليه</div>
        <div class="stat-value" style="color:#059669;">{{ $stats['answered'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">مغلق</div>
        <div class="stat-value" style="color:#64748b;">{{ $stats['closed'] }}</div>
    </div>
</div>

<div class="filters-bar">
    <a href="{{ route('doctor.inquiries.index') }}" class="filter-pill {{ $status === '' ? 'active' : '' }}">الكل</a>
    <a href="{{ route('doctor.inquiries.index', ['status' => 'forwarded']) }}" class="filter-pill {{ $status === 'forwarded' ? 'active' : '' }}">بانتظار الرد</a>
    <a href="{{ route('doctor.inquiries.index', ['status' => 'answered']) }}" class="filter-pill {{ $status === 'answered' ? 'active' : '' }}">تم الرد</a>
    <a href="{{ route('doctor.inquiries.index', ['status' => 'closed']) }}" class="filter-pill {{ $status === 'closed' ? 'active' : '' }}">مغلق</a>
</div>

@if($inquiries->count())
    <div class="inquiries-stack">
        @foreach($inquiries as $inquiry)
            <article class="inquiry-card status-{{ $inquiry->status }}">
                <div class="inquiry-topline"></div>
                <div class="inquiry-body">
                    <div class="inquiry-meta">
                        <div class="student-box">
                            <div class="student-avatar">{{ mb_substr($inquiry->student->name ?? '?', 0, 1) }}</div>
                            <div>
                                <div class="student-name">{{ $inquiry->student->name ?? 'طالب' }}</div>
                                <div class="student-submeta">
                                    <span>{{ $inquiry->student->student_number ?: 'بدون رقم قيد' }}</span>
                                    <span>•</span>
                                    <span>{{ $inquiry->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="chip-row">
                            <span class="chip subject">{{ $inquiry->subject->name ?? 'مادة غير محددة' }}</span>
                            <span class="chip status">{{ $inquiry->status_label }}</span>
                            @if($inquiry->answered_by_actor_label)
                                <span class="chip actor">الرد بواسطة {{ $inquiry->answered_by_actor_label }}</span>
                            @endif
                        </div>
                    </div>

                    <h3 class="inquiry-title">{{ $inquiry->title }}</h3>
                    <p class="inquiry-question">{{ Str::limit($inquiry->question, 180) }}</p>

                    @if($inquiry->status === 'forwarded')
                        <div class="info-band">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            هذا الاستفسار وصل إليك من المندوب وينتظر رد الدكتور.
                        </div>
                    @elseif($inquiry->answer)
                        <div class="info-band answered">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            {{ Str::limit($inquiry->answer, 150) }}
                        </div>
                    @endif

                    <div class="inquiry-footer">
                        <div class="time-meta">
                            آخر تحديث: {{ $inquiry->updated_at->diffForHumans() }}
                        </div>

                        <a href="{{ route('doctor.inquiries.show', $inquiry->id) }}" class="view-btn">
                            عرض التفاصيل
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </a>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    @if($inquiries->hasPages())
        <div style="margin-top: 1.75rem;">
            {{ $inquiries->appends(['status' => $status])->links() }}
        </div>
    @endif
@else
    <div class="empty-state">
        <svg width="70" height="70" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        <h3>لا توجد استفسارات معروضة للطبيب</h3>
        <p>لن تظهر هنا إلا الاستفسارات التي حُوّلت إليك من المندوب أو الاستفسارات التي قمت بالرد عليها أنت شخصيًا.</p>
    </div>
@endif
@endsection
