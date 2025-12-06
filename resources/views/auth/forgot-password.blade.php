<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head', ['title' => 'Reset Password - AllowanceLab'])
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

        .reset-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            padding: 50px;
            width: 100%;
            max-width: 460px;
        }

        .reset-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .reset-title {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .reset-subtitle {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
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

        /* Back to Login Link */
        .back-login {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e0e0e0;
            font-size: 14px;
            color: #666;
        }

        .back-login a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .back-login a:hover {
            color: #45a049;
        }

        /* Success Message */
        .success-message {
            padding: 12px;
            background: #e8f5e9;
            border-left: 4px solid #4CAF50;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #2e7d32;
            font-size: 14px;
            line-height: 1.5;
        }

        /* Responsive */
        @media (max-width: 900px) {
            header {
                padding: 16px 20px;
            }

            .logo-container img {
                height: 70px;
            }

            main {
                padding: 30px 16px;
            }

            .reset-container {
                padding: 40px 30px;
            }

            .reset-title {
                font-size: 28px;
            }

            .reset-subtitle {
                font-size: 15px;
            }
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
        <div class="reset-container">
            <div class="reset-header">
                <h1 class="reset-title">Reset Password</h1>
                <p class="reset-subtitle">Forgot your password? No problem. Just let us know your email address and
                    we'll send you a password reset link.</p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="success-message">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email Address -->
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" class="form-input @error('email') error @enderror" type="email" name="email"
                        value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="submit-btn">
                    Email Password Reset Link
                </button>

                <!-- Back to Login -->
                <div class="back-login">
                    Remember your password? <a href="{{ route('login') }}">Back to Login</a>
                </div>
            </form>
        </div>
    </main>
</body>

</html>