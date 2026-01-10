@extends('layouts.admin')

@section('title', 'عن المطور')

@section('content')

<style>
    .about-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .hero-card {
        background: white;
        border-radius: 24px;
        border: 1px solid var(--border-color);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .hero-banner {
        height: 180px;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
        position: relative;
        overflow: hidden;
    }

    .hero-banner::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .hero-banner .floating-shapes {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
    }

    .floating-shapes .shape {
        position: absolute;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .floating-shapes .shape:nth-child(1) {
        width: 100px;
        height: 100px;
        top: -30px;
        right: 10%;
    }

    .floating-shapes .shape:nth-child(2) {
        width: 60px;
        height: 60px;
        bottom: 20px;
        left: 15%;
    }

    .floating-shapes .shape:nth-child(3) {
        width: 40px;
        height: 40px;
        top: 40%;
        right: 25%;
    }

    .hero-content {
        padding: 0 2.5rem 2.5rem;
        text-align: center;
        position: relative;
    }

    .avatar-wrapper {
        position: relative;
        margin-top: -70px;
        margin-bottom: 1.5rem;
    }

    .avatar {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        background: white;
        padding: 5px;
        margin: 0 auto;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }

    .avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .avatar-badge {
        position: absolute;
        bottom: 8px;
        right: calc(50% - 70px);
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid white;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }

    .developer-name {
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .developer-title {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        padding: 0.5rem 1.25rem;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .developer-bio {
        color: var(--text-secondary);
        font-size: 1.05rem;
        line-height: 1.8;
        max-width: 600px;
        margin: 0 auto 2rem;
    }

    .contact-cards {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .contact-card {
        background: white;
        border-radius: 20px;
        border: 1px solid var(--border-color);
        padding: 2rem;
        text-align: center;
        text-decoration: none;
        color: inherit;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }

    .contact-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
    }

    .contact-card.whatsapp::before {
        background: linear-gradient(90deg, #10b981, #22c55e);
    }

    .contact-card.email::before {
        background: linear-gradient(90deg, #6366f1, #8b5cf6);
    }

    .contact-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.1);
    }

    .contact-card.whatsapp:hover {
        border-color: #10b981;
    }

    .contact-card.email:hover {
        border-color: #6366f1;
    }

    .contact-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.25rem;
    }

    .contact-card.whatsapp .contact-icon {
        background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        color: #059669;
    }

    .contact-card.email .contact-icon {
        background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
        color: #4f46e5;
    }

    .contact-title {
        font-size: 1.15rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .contact-value {
        font-size: 1rem;
        color: var(--text-secondary);
        font-family: 'Courier New', monospace;
        direction: ltr;
    }

    .contact-hint {
        font-size: 0.85rem;
        color: var(--text-light);
        margin-top: 0.75rem;
    }

    .footer-section {
        text-align: center;
        padding: 2rem;
        background: #f8fafc;
        border-radius: 20px;
    }

    .footer-section p {
        color: var(--text-secondary);
        margin-bottom: 1rem;
    }

    .footer-section .copyright {
        font-size: 0.9rem;
        color: var(--text-light);
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        background: #f3f4f6;
        border-radius: 10px;
        color: var(--text-primary);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
        margin-bottom: 1.5rem;
    }

    .back-btn:hover {
        background: #e5e7eb;
    }
</style>

<div class="about-container">
    <!-- Back Button -->
    <a href="{{ route('admin.dashboard') }}" class="back-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        العودة للوحة القيادة
    </a>

    <!-- Hero Card -->
    <div class="hero-card">
        <div class="hero-banner">
            <div class="floating-shapes">
                <div class="shape"></div>
                <div class="shape"></div>
                <div class="shape"></div>
            </div>
        </div>
        <div class="hero-content">
            <div class="avatar-wrapper">
                <div class="avatar">
                    <img src="https://ui-avatars.com/api/?name=Mohammed+Alhemyari&background=6366f1&color=fff&size=200&bold=true" alt="Developer Avatar">
                </div>
                <div class="avatar-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
            </div>

            <h1 class="developer-name">Mohammed Alhemyari</h1>

            <div class="developer-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="16 18 22 12 16 6"></polyline>
                    <polyline points="8 6 2 12 8 18"></polyline>
                </svg>
                مطور أنظمة ومبرمج
            </div>

            <p class="developer-bio">
                نقدم حلولاً برمجية متطورة ومتكاملة للمؤسسات والشركات، مع التركيز على الجودة العالية، الأداء الممتاز، وتجربة المستخدم الاستثنائية.
            </p>
        </div>
    </div>

    <!-- Contact Cards -->
    <div class="contact-cards">
        <a href="https://wa.me/967773468708" target="_blank" class="contact-card whatsapp">
            <div class="contact-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
            </div>
            <h3 class="contact-title">واتساب</h3>
            <p class="contact-value">+967 773 468 708</p>
            <p class="contact-hint">متاح للرد على استفساراتكم</p>
        </a>

        <a href="mailto:alhemyarimohammed211@gmail.com" class="contact-card email">
            <div class="contact-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
            </div>
            <h3 class="contact-title">البريد الإلكتروني</h3>
            <p class="contact-value">alhemyarimohammed211@gmail.com</p>
            <p class="contact-hint">للتواصل الرسمي والعروض</p>
        </a>
    </div>

    <!-- Footer -->
    <div class="footer-section">
        <p>نسعى دائماً لتقديم الأفضل لعملائنا</p>
        <p class="copyright">&copy; {{ date('Y') }} Mohammed Alhemyari - جميع الحقوق محفوظة</p>
    </div>
</div>

@endsection