<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <title>@yield('title', 'AllowanceLab')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            color: #1a1a1a;
            line-height: 1.6;
            background: #fff;
        }

        /* ===== HEADER ===== */
        .page-header {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 14px 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        }

        .page-header img { height: 52px; width: auto; }

        .header-nav { display: flex; gap: 10px; align-items: center; }

        .header-nav a {
            font-size: 14px;
            font-weight: 600;
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-nav-parent {
            background: #10b981;
            color: white;
            box-shadow: 0 2px 8px rgba(16,185,129,0.3);
        }

        .btn-nav-parent:hover { background: #059669; transform: translateY(-1px); }

        .btn-nav-kid {
            background: #3b82f6;
            color: white;
            box-shadow: 0 2px 8px rgba(59,130,246,0.3);
        }

        .btn-nav-kid:hover { background: #2563eb; transform: translateY(-1px); }

        .btn-nav-home {
            color: #64748b;
            border: 1.5px solid #e2e8f0;
        }

        .btn-nav-home:hover { color: #10b981; border-color: #10b981; }

        /* ===== PAGE HAMBURGER (always visible) ===== */
        .page-hamburger {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            background: none;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            color: #94a3b8;
            font-size: 16px;
            line-height: 1;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .page-hamburger:hover { color: #10b981; background: #f0fdf4; }

        /* ===== PAGE MOBILE MENU ===== */
        .page-mobile-menu {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            padding: 8px;
            min-width: 220px;
            z-index: 200;
        }

        .page-mobile-menu.open { display: block; }

        /* Login buttons inside dropdown (mobile only) */
        .page-menu-login {
            display: none;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 14px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            color: white;
            transition: all 0.2s;
            margin-bottom: 2px;
        }

        .page-menu-login-parent { background: #10b981; margin-bottom: 8px; }
        .page-menu-login-parent:hover { background: #059669; color: white; }
        .page-menu-login-kid { background: #3b82f6; }
        .page-menu-login-kid:hover { background: #2563eb; color: white; }

        .page-menu-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 6px 0;
            display: none;
        }

        .page-mobile-menu a:not(.page-menu-login) {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.15s;
        }

        .page-mobile-menu a:not(.page-menu-login):hover { background: #f0fdf4; color: #10b981; }

        /* ===== PAGE HERO ===== */
        .page-hero {
            background: linear-gradient(135deg, #0f172a 0%, #022c22 55%, #064e3b 100%);
            padding: 64px 60px 56px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .page-hero::before {
            content: '';
            position: absolute;
            top: -80px; left: -80px;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(16,185,129,0.15) 0%, transparent 70%);
            pointer-events: none;
        }

        .page-hero-label {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #6ee7b7;
            margin-bottom: 12px;
        }

        .page-hero-title {
            font-size: 42px;
            font-weight: 800;
            color: white;
            letter-spacing: -0.5px;
            position: relative;
            z-index: 1;
        }

        .page-hero-subtitle {
            font-size: 18px;
            color: #94a3b8;
            margin-top: 14px;
            position: relative;
            z-index: 1;
        }

        /* ===== CONTENT ===== */
        .page-content {
            max-width: 860px;
            margin: 0 auto;
            padding: 64px 40px 80px;
        }

        .page-content h2 {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            margin: 40px 0 12px;
            letter-spacing: -0.3px;
        }

        .page-content h2:first-child { margin-top: 0; }

        .page-content h3 {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin: 28px 0 8px;
        }

        .page-content p {
            color: #475569;
            font-size: 16px;
            line-height: 1.8;
            margin-bottom: 16px;
        }

        .page-content ul {
            padding-left: 20px;
            margin-bottom: 16px;
        }

        .page-content ul li {
            color: #475569;
            font-size: 16px;
            line-height: 1.8;
            margin-bottom: 6px;
        }

        .page-content a {
            color: #10b981;
            text-decoration: none;
            font-weight: 600;
        }

        .page-content a:hover { color: #059669; text-decoration: underline; }

        .content-divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 40px 0;
        }

        /* ===== FOOTER ===== */
        footer {
            background: #0f172a;
            color: white;
            padding: 56px 60px 28px;
        }

        .footer-content {
            max-width: 1100px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 48px;
            padding-bottom: 40px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .footer-brand {
            font-size: 20px;
            font-weight: 800;
            color: #10b981;
            margin-bottom: 10px;
        }

        .footer-tagline {
            font-size: 14px;
            color: #64748b;
            line-height: 1.7;
            max-width: 280px;
        }

        .footer-section-title {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 16px;
        }

        .footer-links { list-style: none; }

        .footer-links li { margin-bottom: 10px; }

        .footer-links a {
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s;
        }

        .footer-links a:hover { color: #10b981; }

        .footer-bottom {
            max-width: 1100px;
            margin: 24px auto 0;
            font-size: 13px;
            color: #374151;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 900px) {
            .page-header { padding: 12px 20px; }
            .page-header img { height: 44px; }
            .btn-nav-home { display: none; }
            .btn-nav-parent { display: none; }
            .btn-nav-kid { display: none; }
            .page-menu-login { display: flex; }
            .page-menu-divider { display: block; }
            .page-hero { padding: 48px 20px 40px; }
            .page-hero-title { font-size: 30px; }
            .page-hero-subtitle { font-size: 16px; }
            .page-content { padding: 40px 20px 60px; }
            footer { padding: 44px 20px 24px; }
            .footer-content { grid-template-columns: 1fr; gap: 28px; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <header class="page-header">
        <a href="{{ url('/') }}">
            <img src="{{ asset('/images/Allowance-Lab-logo.png') }}" alt="AllowanceLab">
        </a>
        <nav class="header-nav" style="position: relative;">
            <a href="{{ route('login') }}" class="btn-nav-parent"><i class="fas fa-user-shield"></i> Parent Login</a>
            <a href="{{ route('kid.login') }}" class="btn-nav-kid"><i class="fas fa-star"></i> Kid Login</a>
            <button class="page-hamburger" id="pageMenuBtn" onclick="togglePageMenu()" aria-label="Menu">
                <i class="fas fa-bars"></i>
            </button>
            <div class="page-mobile-menu" id="pageMenu">
                <a href="{{ route('login') }}" class="page-menu-login page-menu-login-parent"><i class="fas fa-user-shield"></i> Parent Login</a>
                <a href="{{ route('kid.login') }}" class="page-menu-login page-menu-login-kid"><i class="fas fa-star"></i> Kid Login</a>
                <div class="page-menu-divider"></div>
                <div class="nav-dropdown-label" style="font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;padding:6px 12px 2px;">Pages</div>
                <a href="{{ url('/') }}"><i class="fas fa-house"></i> Home</a>
                <a href="{{ route('about') }}"><i class="fas fa-info-circle"></i> About</a>
                <a href="{{ route('faq') }}"><i class="fas fa-question-circle"></i> FAQ</a>
                <a href="{{ route('contact') }}"><i class="fas fa-envelope"></i> Contact</a>
            </div>
        </nav>
    </header>

    @yield('page-hero')

    <main>
        @yield('content')
    </main>

    <footer>
        <div class="footer-content">
            <div>
                <div class="footer-brand">AllowanceLab</div>
                <p class="footer-tagline">Simple tools for real-world money lessons. Teaching kids financial responsibility one allowance at a time.</p>
            </div>
            <div>
                <h4 class="footer-section-title">Company</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('about') }}">About</a></li>
                    <li><a href="{{ route('faq') }}">FAQ</a></li>
                    <li><a href="{{ route('contact') }}">Contact</a></li>
                </ul>
            </div>
            <div>
                <h4 class="footer-section-title">Legal</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('privacy') }}">Privacy Policy</a></li>
                    <li><a href="{{ route('terms') }}">Terms of Service</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">&copy; {{ date('Y') }} AllowanceLab. All rights reserved.</div>
    </footer>

    <script>
        function togglePageMenu() {
            const menu = document.getElementById('pageMenu');
            menu.classList.toggle('open');
            if (menu.classList.contains('open')) {
                setTimeout(() => {
                    document.addEventListener('click', function handler(e) {
                        if (!menu.contains(e.target) && e.target !== document.getElementById('pageMenuBtn') && !document.getElementById('pageMenuBtn').contains(e.target)) {
                            menu.classList.remove('open');
                            document.removeEventListener('click', handler);
                        }
                    });
                }, 10);
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
