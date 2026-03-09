<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <title>@yield('title', 'AllowanceLab')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/css/page.css', 'resources/js/app.js'])
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
