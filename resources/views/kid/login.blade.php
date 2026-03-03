<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Kid Login - AllowanceLab</title>
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
            flex-direction: column;
            background: linear-gradient(145deg, #ecfdf5 0%, #eff6ff 50%, #faf5ff 100%);
            position: relative;
            overflow-x: hidden;
        }

        /* Decorative blobs */
        body::before {
            content: '';
            position: fixed;
            top: -100px;
            left: -100px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.12) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        body::after {
            content: '';
            position: fixed;
            bottom: -80px;
            right: -80px;
            width: 360px;
            height: 360px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        /* Header */
        header {
            padding: 16px 32px 8px;
            position: relative;
            z-index: 1;
        }

        .logo-wrap a {
            display: inline-block;
        }

        .logo-wrap img {
            height: 60px;
            width: auto;
        }

        /* Main */
        main {
            flex: 1;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 33px 20px 48px;
            position: relative;
            z-index: 1;
        }

        .login-card {
            background: white;
            border-radius: 28px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.10), 0 2px 8px rgba(0,0,0,0.04);
            padding: 48px 44px 44px;
            width: 100%;
            max-width: 440px;
            position: relative;
            overflow: hidden;
        }

        /* Colorful top accent strip */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #10b981 0%, #6366f1 50%, #ec4899 100%);
        }

        /* Header area */
        .card-header {
            text-align: center;
            margin-bottom: 36px;
        }

        .card-icon {
            width: 72px;
            height: 72px;
            border-radius: 22px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 30px;
            margin: 0 auto 20px;
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35);
        }

        .card-title {
            font-size: 32px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .card-subtitle {
            font-size: 16px;
            color: #64748b;
            line-height: 1.5;
        }

        /* Form */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 15px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 16px;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            padding: 15px 16px 15px 44px;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            font-size: 17px;
            transition: all 0.2s;
            font-family: inherit;
            color: #0f172a;
            background: #fafafa;
        }

        .form-input:focus {
            outline: none;
            border-color: #6366f1;
            background: white;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
        }

        .form-input.error {
            border-color: #ef4444;
            background: #fff5f5;
            animation: shake 0.3s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25%       { transform: translateX(-8px); }
            75%        { transform: translateX(8px); }
        }

        .error-message {
            color: #ef4444;
            font-size: 13px;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
        }

        /* Forgot password hint */
        .forgot-hint {
            text-align: center;
            margin-top: -8px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #94a3b8;
        }

        /* Submit button */
        .submit-btn {
            width: 100%;
            padding: 17px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 19px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.25s;
            box-shadow: 0 4px 16px rgba(99, 102, 241, 0.4);
            letter-spacing: 0.01em;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.5);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* Switch login */
        .switch-login {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
            font-size: 14px;
            color: #64748b;
        }

        .switch-login a {
            color: #10b981;
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s;
        }

        .switch-login a:hover {
            color: #059669;
        }

        /* Session status */
        .status-message {
            padding: 12px 16px;
            background: #fff7ed;
            border-left: 3px solid #f97316;
            border-radius: 10px;
            margin-bottom: 20px;
            color: #c2410c;
            font-size: 14px;
            line-height: 1.5;
        }

        /* Responsive */
        @media (max-width: 520px) {
            .login-card {
                padding: 40px 24px 36px;
                border-radius: 24px;
            }

            .card-title {
                font-size: 28px;
            }

            header {
                padding: 16px 20px;
                text-align: center;
            }

            .logo-wrap img {
                height: 72px;
            }
        }

        /* PWA mode */
        body.pwa-mode header {
            display: none;
        }

        body.pwa-mode main {
            min-height: 100vh;
            padding-top: 48px;
        }
    </style>
</head>

<body>
    <header>
        <div class="logo-wrap">
            <a href="{{ url('/') }}">
                <img src="{{ asset('/images/Allowance-Lab-logo.png') }}" alt="AllowanceLab">
            </a>
        </div>
    </header>

    <main>
        <div class="login-card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-star"></i>
                </div>
                <h1 class="card-title">Hey, welcome back!</h1>
                <p class="card-subtitle">Enter your username and password to see your balance.</p>
            </div>

            @if (session('status'))
                <div class="status-message">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('kid.login') }}">
                @csrf
                <input type="hidden" id="remember_me" name="remember" value="0">

                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input id="username"
                            class="form-input @error('username') error @enderror"
                            type="text" name="username" value="{{ old('username') }}"
                            required autofocus autocomplete="username" placeholder="Your username">
                    </div>
                    @error('username')
                        <span class="error-message"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input id="password"
                            class="form-input @error('password') error @enderror"
                            type="password" name="password"
                            required autocomplete="current-password" placeholder="Your secret password">
                    </div>
                    @error('password')
                        <span class="error-message"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                    @enderror
                </div>

                <p class="forgot-hint">Forgot your password? Ask your parent to reset it!</p>

                <button type="submit" class="submit-btn">
                    Let's Go! 🚀
                </button>

                <div class="switch-login">
                    Are you a parent? <a href="{{ route('login') }}">Switch to Parent Login →</a>
                </div>
            </form>
        </div>
    </main>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const isPWAParam = urlParams.has('pwa');
        const isPWA = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone || isPWAParam;

        if (isPWA) {
            document.body.classList.add('pwa-mode');

            const rememberCheckbox = document.getElementById('remember_me');
            if (rememberCheckbox) {
                rememberCheckbox.value = '1';
            }

            @if (Auth::guard('kid')->check())
                window.location.href = '{{ route('kid.dashboard') }}';
            @endif
        }
    </script>
</body>

</html>
