@extends('layouts.admin')

@section('title', 'عن المطور')

@section('content')
<div class="container" style="max-width: 1000px; padding: 2rem;">

    <div class="card" style="padding: 3rem; text-align: center; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.06); overflow: hidden; position: relative; background: #fff;">

        <!-- Decorative Background Shape -->
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 150px; background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%); z-index: 1;"></div>

        <!-- Avatar Container -->
        <div style="position: relative; z-index: 2; margin-top: 40px; margin-bottom: 2rem;">
            <div style="width: 140px; height: 140px; border-radius: 50%; background: #fff; padding: 6px; margin: 0 auto; box-shadow: 0 8px 16px rgba(0,0,0,0.1);">
                <div style="width: 100%; height: 100%; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; overflow: hidden; font-size: 3rem; color: #4b5563;">
                    <!-- Placeholder or Real Image -->
                    <img src="https://ui-avatars.com/api/?name=Mohammed+Alhemyari&background=random&color=fff&size=200" alt="Developer" style="width: 100%; height: 100%;">
                </div>
            </div>
        </div>

        <!-- Info -->
        <div style="position: relative; z-index: 2;">
            <h1 style="font-size: 2rem; font-weight: 800; color: #111827; margin-bottom: 0.5rem;">Mohammed Alhemyari</h1>
            <p style="font-size: 1.1rem; color: #6b7280; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">
                نقدم حلول برمجية متطورة للأنظمه، مع التركيز على الجودة، الأداء، وتجربة المستخدم الاستثنائية.
            </p>

            <!-- Contact Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-top: 3rem;">

                <!-- Phone / Whatsapp -->
                <a href="https://wa.me/967773468708" target="_blank" style="text-decoration: none; color: inherit;">
                    <div style="background: #f9fafb; padding: 1.5rem; border-radius: 12px; border: 1px solid #e5e7eb; transition: all 0.2s ease;" onmouseover="this.style.borderColor='#22c55e'; this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='#e5e7eb'; this.style.transform='translateY(0)'">
                        <div style="width: 48px; height: 48px; background: #dcfce7; color: #16a34a; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                        </div>
                        <h3 style="font-weight: 700; font-size: 1.1rem; color: #374151;">تواصل معنا واتساب</h3>
                        <p style="color: #6b7280; font-family: monospace; font-size: 1.1rem; direction: ltr;">+967 773 468 708</p>
                    </div>
                </a>

                <!-- Email -->
                <a href="mailto:alhemyarimohammed211@gmail.com" style="text-decoration: none; color: inherit;">
                    <div style="background: #f9fafb; padding: 1.5rem; border-radius: 12px; border: 1px solid #e5e7eb; transition: all 0.2s ease;" onmouseover="this.style.borderColor='#6366f1'; this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='#e5e7eb'; this.style.transform='translateY(0)'">
                        <div style="width: 48px; height: 48px; background: #e0e7ff; color: #4f46e5; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                        </div>
                        <h3 style="font-weight: 700; font-size: 1.1rem; color: #374151;">البريد الإلكتروني</h3>
                        <p style="color: #6b7280; font-family: monospace; font-size: 1.1rem;">alhemyarimohammed211@gmail.com</p>
                    </div>
                </a>

            </div>

            <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #f3f4f6; color: #9ca3af; font-size: 0.9rem;">
                &copy; {{ date('Y') }} جميع الحقوق محفوظة للمطور.
            </div>

        </div>
    </div>
</div>
@endsection