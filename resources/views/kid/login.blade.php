<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head', ['title' => 'Kid Login - AllowanceLab'])
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        header {
            padding: 20px 60px;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo-container a {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .logo-container img {
            height: 70px;
            width: auto;
        }

        /* Main Content */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            padding: 50px;
            width: 100%;
            max-width: 460px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-icon {
            font-size: 64px;
            margin-bottom: 16px;
        }

        .login-title {
            font-size: 36px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .login-subtitle {
            font-size: 18px;
            color: #666;
            line-height: 1.5;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 16px;
            font-weight: 600;
            color: #444;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 16px 18px;
            border: 3px solid #e0e0e0;
            border-radius: 12px;
            font-size: 18px;
            transition: all 0.2s;
            font-family: inherit;
        }

        .form-input:focus {
            outline: none;
            border-color: #42a5f5;
            box-shadow: 0 0 0 4px rgba(66, 165, 245, 0.1);
        }

        .form-input.error {
            border-color: #ef5350;
            animation: shake 0.3s;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-8px);
            }

            75% {
                transform: translateX(8px);
            }
        }

        .error-message {
            color: #ef5350;
            font-size: 14px;
            margin-top: 6px;
            display: block;
            font-weight: 500;
        }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 18px;
            background: #42a5f5;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 20px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(66, 165, 245, 0.3);
            margin-top: 8px;
        }

        .submit-btn:hover {
            background: #1e88e5;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(66, 165, 245, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* Switch Login Link */
        .switch-login {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e0e0e0;
            font-size: 14px;
            color: #666;
        }

        .switch-login a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .switch-login a:hover {
            color: #45a049;
        }

        /* Session Status */
        .status-message {
            padding: 12px;
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #e65100;
            font-size: 14px;
            line-height: 1.5;
        }

        /* Responsive */
        @media (max-width: 900px) {
            header {
                padding: 16px 20px;
            }

            .logo-container img {
                height: 80px;
            }

            main {
                padding: 30px 16px;
            }

            .login-container {
                padding: 40px 30px;
                border-radius: 16px;
            }

            .login-icon {
                font-size: 56px;
            }

            .login-title {
                font-size: 32px;
            }

            .login-subtitle {
                font-size: 16px;
            }
        }

        /* PWA Mode Styles */
        body.pwa-mode header {
            display: none;
        }

        body.pwa-mode main {
            min-height: 100vh;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header>
        <div class="logo-container">
            <a href="{{ url('/') }}">
                <img src="{{ asset('/images/Allowance-Lab-logo.png') }}" alt="AllowanceLab">
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="login-container">
            <div class="login-header">
                <div class="login-icon">👋</div>
                <h1 class="login-title">Kid Login</h1>
                <p class="login-subtitle">Hey there! Enter your username and password.</p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="status-message">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('kid.login') }}">
                @csrf

                <!-- Username -->
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input id="username" class="form-input @error('username') error @enderror" type="text"
                        name="username" value="{{ old('username') }}" required autofocus autocomplete="username">
                    @error('username')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" class="form-input @error('password') error @enderror" type="password"
                        name="password" required autocomplete="current-password">
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Hidden Remember Me (for PWA mode) -->
                <input type="hidden" id="remember_me" name="remember" value="0">

                <!-- Forgot Password Message -->
                <div style="text-align: center; margin-top: -8px; margin-bottom: 16px;">
                    <p style="font-size: 14px; color: #888;">
                        Forgot your password? Ask your parent to reset it!
                    </p>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="submit-btn">
                    Let's Go! 🚀
                </button>

                <!-- Switch to Parent Login -->
                <div class="switch-login">
                    Are you a parent? <a href="{{ route('login') }}">Switch to Parent Login →</a>
                </div>
            </form>
        </div>
    </main>

    <script>
        // PWA Mode Detection
        const urlParams = new URLSearchParams(window.location.search);
        const isPWAParam = urlParams.has('pwa');
        const isPWA = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone || isPWAParam;

        if (isPWA) {
            // Add PWA mode class to body
            document.body.classList.add('pwa-mode');

            // Auto-check hidden remember me checkbox
            const rememberCheckbox = document.getElementById('remember_me');
            if (rememberCheckbox) {
                rememberCheckbox.value = '1';
            }

            // If logged in kid, redirect to dashboard
            @if (Auth::guard('kid')->check())
                window.location.href = '{{ route('kid.dashboard') }}';
            @endif
        }
    </script>
</body>

</html>