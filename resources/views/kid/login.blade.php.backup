<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kid Login - AllowanceLab</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 48px;
            width: 100%;
            max-width: 420px;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo {
            font-size: 64px;
            margin-bottom: 16px;
        }

        .brand-name {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .subtitle {
            font-size: 16px;
            color: #666;
        }

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
        }

        .form-input:focus {
            outline: none;
            border-color: #87ceeb;
        }

        .error-message {
            color: #f44336;
            font-size: 13px;
            margin-top: 6px;
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: #87ceeb;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .login-btn:hover {
            background: #6fb8e0;
        }

        .login-btn:active {
            transform: scale(0.98);
        }

        .back-link {
            text-align: center;
            margin-top: 24px;
        }

        .back-link a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link a:hover {
            color: #87ceeb;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 32px 24px;
            }

            .brand-name {
                font-size: 24px;
            }

            .logo {
                font-size: 48px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="logo-section">
            <div class="logo">💰</div>
            <div class="brand-name">AllowanceLab</div>
            <div class="subtitle">Kid Login</div>
        </div>

        <form method="POST" action="{{ route('kid.login') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input" placeholder="Enter your username"
                    value="{{ old('username') }}" required autofocus>
                @error('username')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" placeholder="Enter your password" required>
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="login-btn">Sign In</button>
        </form>

        <div class="back-link">
            <a href="/">← Back to Home</a>
        </div>
    </div>
</body>

</html>