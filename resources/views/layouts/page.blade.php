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
        <nav class="header-nav">
            <a href="{{ url('/') }}" class="btn-nav-home"><i class="fas fa-arrow-left"></i> Home</a>
            <a href="{{ route('login') }}" class="btn-nav-parent"><i class="fas fa-user-shield"></i> Parent Login</a>
            <a href="{{ route('kid.login') }}" class="btn-nav-kid"><i class="fas fa-star"></i> Kid Login</a>
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

    @stack('scripts')
</body>
</html>
