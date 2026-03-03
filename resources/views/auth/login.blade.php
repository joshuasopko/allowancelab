<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Parent Login - AllowanceLab</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
        }

        /* ===== LEFT PANEL ===== */
        .panel-left {
            width: 44%;
            background: linear-gradient(135deg, #0f172a 0%, #022c22 55%, #064e3b 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            padding: 48px 52px;
            overflow: hidden;
        }

        .panel-left::before {
            content: '';
            position: absolute;
            top: -120px;
            left: -120px;
            width: 480px;
            height: 480px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.18) 0%, transparent 70%);
            pointer-events: none;
        }

        .panel-left::after {
            content: '';
            position: absolute;
            bottom: -80px;
            right: -80px;
            width: 360px;
            height: 360px;
            background: radial-gradient(circle, rgba(5, 150, 105, 0.14) 0%, transparent 70%);
            pointer-events: none;
        }

        .left-logo {
            position: relative;
            z-index: 1;
            margin-bottom: auto;
        }

        .left-logo a {
            display: inline-block;
        }

        .left-logo img {
            height: 64px;
            width: auto;
        }

        .left-content {
            position: relative;
            z-index: 1;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 48px 0;
        }

        .left-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 100px;
            padding: 7px 16px;
            font-size: 13px;
            font-weight: 600;
            color: #6ee7b7;
            letter-spacing: 0.03em;
            width: fit-content;
            margin-bottom: 28px;
        }

        .left-headline {
            font-size: 36px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.2;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }

        .left-headline .highlight {
            background: linear-gradient(135deg, #6ee7b7, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .left-sub {
            font-size: 16px;
            color: #94a3b8;
            line-height: 1.7;
            margin-bottom: 40px;
        }

        .left-perks {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .perk-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        .perk-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.15));
            border: 1px solid rgba(16, 185, 129, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6ee7b7;
            font-size: 14px;
            flex-shrink: 0;
        }

        .perk-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .perk-title {
            font-size: 14px;
            font-weight: 700;
            color: #e2e8f0;
        }

        .perk-desc {
            font-size: 13px;
            color: #94a3b8;
            line-height: 1.5;
        }

        .left-footer {
            position: relative;
            z-index: 1;
            font-size: 13px;
            color: #94a3b8;
            text-align: right;
        }

        /* ===== RIGHT PANEL ===== */
        .panel-right {
            flex: 1;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
            overflow-y: auto;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            padding: 44px 44px;
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: linear-gradient(135deg, #10b981, #059669);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 22px;
            margin: 0 auto 18px;
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.35);
        }

        .login-title {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
            letter-spacing: -0.3px;
        }

        .login-subtitle {
            font-size: 15px;
            color: #64748b;
            line-height: 1.5;
        }

        /* Session status */
        .session-status {
            padding: 12px 16px;
            background: #f0fdf4;
            border-left: 3px solid #10b981;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #065f46;
            font-size: 14px;
        }

        /* Form */
        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 7px;
            letter-spacing: 0.01em;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            padding: 12px 14px 12px 38px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.2s;
            font-family: inherit;
            color: #0f172a;
            background: #fafafa;
        }

        .form-input:focus {
            outline: none;
            border-color: #10b981;
            background: white;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.12);
        }

        .form-input.error {
            border-color: #ef4444;
            background: #fff5f5;
        }

        .error-message {
            color: #ef4444;
            font-size: 12px;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Remember / forgot row */
        .form-extras {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #10b981;
            cursor: pointer;
        }

        .remember-me label {
            font-size: 13px;
            color: #64748b;
            cursor: pointer;
        }

        .forgot-password {
            color: #10b981;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: color 0.2s;
        }

        .forgot-password:hover {
            color: #059669;
        }

        /* Submit */
        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            letter-spacing: 0.01em;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* Bottom links */
        .bottom-links {
            margin-top: 22px;
            padding-top: 22px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            flex-direction: column;
            gap: 10px;
            text-align: center;
        }

        .bottom-links .link-line {
            font-size: 14px;
            color: #64748b;
        }

        .bottom-links a {
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s;
        }

        .link-register a {
            color: #10b981;
        }

        .link-register a:hover {
            color: #059669;
        }

        .link-kid a {
            color: #6366f1;
        }

        .link-kid a:hover {
            color: #4f46e5;
        }

        /* Trust line */
        .trust-line {
            margin-top: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #94a3b8;
        }

        .trust-line i {
            color: #10b981;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 900px) {
            body {
                flex-direction: column;
            }

            .panel-left {
                width: 100%;
                padding: 28px 24px 32px;
                min-height: auto;
            }

            .left-logo {
                text-align: center;
            }

            .left-logo img {
                height: 72px;
            }

            .left-content {
                padding: 28px 0 0;
            }

            .left-headline {
                font-size: 26px;
            }

            .left-sub {
                font-size: 15px;
                margin-bottom: 24px;
            }

            .left-perks {
                gap: 12px;
            }

            .left-footer {
                display: none;
            }

            .panel-right {
                padding: 32px 20px 48px;
            }

            .login-card {
                padding: 32px 24px;
                box-shadow: none;
                border: 1px solid #e2e8f0;
            }

            .form-extras {
                flex-direction: row;
            }
        }

        /* ===== PWA MODE ===== */
        body.pwa-mode .panel-left {
            display: none;
        }

        body.pwa-mode .panel-right {
            background: linear-gradient(135deg, #0f172a 0%, #022c22 55%, #064e3b 100%);
            padding: 48px 24px;
        }

        body.pwa-mode .login-card {
            margin-top: 0;
        }

        body.pwa-mode .link-register {
            display: none;
        }
    </style>
</head>

<body>
    <!-- Left branding panel -->
    <div class="panel-left">
        <div class="left-logo">
            <a href="{{ url('/') }}">
                <img src="{{ asset('/images/Allowance-Lab-logo-white.png') }}" alt="AllowanceLab">
            </a>
        </div>

        <div class="left-content">
            <div class="left-badge">
                <i class="fas fa-shield-halved"></i> Parent Dashboard
            </div>
            <h2 class="left-headline">Your family's finances, <span class="highlight">always on track.</span></h2>
            <p class="left-sub">Manage allowances, review transactions, and keep your kids accountable — all from one place.</p>

            <div class="left-perks">
                <div class="perk-item">
                    <div class="perk-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="perk-text">
                        <span class="perk-title">Automated allowance days</span>
                        <span class="perk-desc">Payouts run on schedule, every week.</span>
                    </div>
                </div>
                <div class="perk-item">
                    <div class="perk-icon"><i class="fas fa-star"></i></div>
                    <div class="perk-text">
                        <span class="perk-title">Points-based accountability</span>
                        <span class="perk-desc">Kids earn their allowance — you control the score.</span>
                    </div>
                </div>
                <div class="perk-item">
                    <div class="perk-icon"><i class="fas fa-piggy-bank"></i></div>
                    <div class="perk-text">
                        <span class="perk-title">Savings goals they can see</span>
                        <span class="perk-desc">Goals make saving feel real and rewarding.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="left-footer">
            &copy; 2026 AllowanceLab
        </div>
    </div>

    <!-- Right form panel -->
    <div class="panel-right">
        <div class="login-card">
            <div class="login-header">
                <div class="login-icon">
                    <i class="fas fa-house-user"></i>
                </div>
                <h1 class="login-title">Welcome Back</h1>
                <p class="login-subtitle">Sign in to manage your family's allowances.</p>
            </div>

            @if (session('status'))
                <div class="session-status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <input type="hidden" name="timezone" id="detected_timezone">

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input id="email" class="form-input @error('email') error @enderror"
                            type="email" name="email" value="{{ old('email') }}"
                            required autofocus autocomplete="username" placeholder="you@example.com">
                    </div>
                    @error('email')
                        <span class="error-message"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input id="password" class="form-input @error('password') error @enderror"
                            type="password" name="password" required autocomplete="current-password"
                            placeholder="Your password">
                    </div>
                    @error('password')
                        <span class="error-message"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                    @enderror
                </div>

                <!-- Remember me & forgot password -->
                <div class="form-extras">
                    <div class="remember-me">
                        <input id="remember_me" type="checkbox" name="remember">
                        <label for="remember_me">Remember me</label>
                    </div>
                    @if (Route::has('password.request'))
                        <a class="forgot-password" href="{{ route('password.request') }}">Forgot password?</a>
                    @endif
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-arrow-right-to-bracket"></i>
                    Sign In
                </button>

                <div class="bottom-links">
                    @if (Route::has('register'))
                        <div class="link-line link-register">
                            Don't have an account? <a href="{{ route('register') }}">Create one free</a>
                        </div>
                    @endif
                    <div class="link-line link-kid">
                        Not a parent? <a href="{{ route('kid.login') }}">Switch to Kid Login →</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="trust-line">
            <i class="fas fa-lock"></i>
            Free forever &mdash; no credit card required
        </div>
    </div>

    <script>
        // Auto-detect and set user's timezone
        document.addEventListener('DOMContentLoaded', function() {
            const timezoneField = document.getElementById('detected_timezone');
            if (timezoneField) {
                const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
                timezoneField.value = timezone;
            }
        });

        // PWA Mode Detection
        const urlParams = new URLSearchParams(window.location.search);
        const isPWAParam = urlParams.has('pwa');
        const isPWA = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone || isPWAParam;

        if (isPWA) {
            document.body.classList.add('pwa-mode');

            const rememberCheckbox = document.getElementById('remember_me');
            const rememberContainer = document.querySelector('.remember-me');
            if (rememberCheckbox) {
                rememberCheckbox.checked = true;
                if (rememberContainer) {
                    rememberContainer.style.display = 'none';
                }
            }

            @if (Auth::check())
                window.location.href = '{{ route('dashboard') }}';
            @endif
        }
    </script>
</body>

</html>
