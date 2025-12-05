<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Login - AllowanceLab</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
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
            height: 80px;
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
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            padding: 50px;
            width: 100%;
            max-width: 460px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-title {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .login-subtitle {
            font-size: 16px;
            color: #666;
            line-height: 1.5;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #444;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.2s;
            font-family: inherit;
        }

        .form-input:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }

        .form-input.error {
            border-color: #ef5350;
        }

        .error-message {
            color: #ef5350;
            font-size: 13px;
            margin-top: 6px;
            display: block;
        }

        /* Remember Me & Forgot Password */
        .form-row {
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
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .remember-me label {
            font-size: 14px;
            color: #555;
            cursor: pointer;
        }

        .forgot-password {
            color: #4CAF50;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s;
        }

        .forgot-password:hover {
            color: #45a049;
        }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 16px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        }

        .submit-btn:hover {
            background: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(76, 175, 80, 0.4);
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
            color: #42a5f5;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .switch-login a:hover {
            color: #1e88e5;
        }

        /* Register Link */
        .register-link {
            text-align: center;
            margin-top: 16px;
            font-size: 14px;
            color: #666;
        }

        .register-link a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .register-link a:hover {
            color: #45a049;
        }

        /* Responsive */
        @media (max-width: 768px) {
            header {
                padding: 16px 20px;
            }

            .logo-container img {
                height: 70px;
            }

            main {
                padding: 30px 16px;
            }

            .login-container {
                padding: 40px 30px;
            }

            .login-title {
                font-size: 28px;
            }

            .login-subtitle {
                font-size: 15px;
            }

            .form-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .forgot-password {
                align-self: flex-start;
            }
        }

        /* PWA Mode Styles */
        body.pwa-mode header {
            display: none;
        }

        body.pwa-mode main {
            min-height: 100vh;
        }

        body.pwa-mode .register-link {
            display: none;
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
                <h1 class="login-title">Parent Login</h1>
                <p class="login-subtitle">Welcome back! Sign in to manage your family's allowances.</p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div
                    style="padding: 12px; background: #e8f5e9; border-left: 4px solid #4CAF50; border-radius: 8px; margin-bottom: 20px; color: #2e7d32; font-size: 14px;">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" class="form-input @error('email') error @enderror" type="email" name="email"
                        value="{{ old('email') }}" required autofocus autocomplete="username">
                    @error('email')
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

                <!-- Remember Me & Forgot Password -->
                <div class="form-row">
                    <div class="remember-me">
                        <input id="remember_me" type="checkbox" name="remember">
                        <label for="remember_me">Remember me</label>
                    </div>

                    @if (Route::has('password.request'))
                        <a class="forgot-password" href="{{ route('password.request') }}">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <!-- Submit Button -->
                <button type="submit" class="submit-btn">
                    Sign In
                </button>

                <!-- Register Link -->
                @if (Route::has('register'))
                    <div class="register-link">
                        Don't have an account? <a href="{{ route('register') }}">Create one here</a>
                    </div>
                @endif

                <!-- Switch to Kid Login -->
                <div class="switch-login">
                    Not a parent? <a href="{{ route('kid.login') }}">Switch to Kid Login →</a>
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

            // Auto-check and hide remember me checkbox
            const rememberCheckbox = document.getElementById('remember_me');
            const rememberContainer = document.querySelector('.remember-me');
            if (rememberCheckbox) {
                rememberCheckbox.checked = true;
                if (rememberContainer) {
                    rememberContainer.style.display = 'none';
                }
            }

            // If logged in parent, redirect to dashboard
            @if (Auth::check())
                window.location.href = '{{ route('dashboard') }}';
            @endif
        }
    </script>
</body>

</html>