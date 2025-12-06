<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Create Account - AllowanceLab</title>
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

        .register-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            padding: 50px;
            width: 100%;
            max-width: 460px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .register-title {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .register-subtitle {
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

        /* Login Link */
        .login-link {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e0e0e0;
            font-size: 14px;
            color: #666;
        }

        .login-link a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .login-link a:hover {
            color: #45a049;
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

            .register-container {
                padding: 40px 30px;
            }

            .register-title {
                font-size: 28px;
            }

            .register-subtitle {
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
        <div class="register-container">
            <div class="register-header">
                <h1 class="register-title">Create Your Account</h1>
                <p class="register-subtitle">Start teaching your kids smart money habits today.</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Hidden timezone field -->
                <input type="hidden" name="timezone" id="detected_timezone">

                <!-- First Name -->
                <div class="form-group">
                    <label for="first_name" class="form-label">First Name</label>
                    <input id="first_name" class="form-input @error('first_name') error @enderror" type="text"
                        name="first_name" value="{{ old('first_name') }}" required autofocus autocomplete="given-name">
                    @error('first_name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Last Name -->
                <div class="form-group">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input id="last_name" class="form-input @error('last_name') error @enderror" type="text"
                        name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name">
                    @error('last_name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" class="form-input @error('email') error @enderror" type="email" name="email"
                        value="{{ old('email') }}" required autocomplete="username">
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" class="form-input @error('password') error @enderror" type="password"
                        name="password" required autocomplete="new-password">
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input id="password_confirmation" class="form-input @error('password_confirmation') error @enderror"
                        type="password" name="password_confirmation" required autocomplete="new-password">
                    @error('password_confirmation')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="submit-btn">
                    Create Account
                </button>

                <!-- Login Link -->
                <div class="login-link">
                    Already have an account? <a href="{{ route('login') }}">Sign in here</a>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Auto-detect and set user's timezone
        document.addEventListener('DOMContentLoaded', function() {
            const timezoneField = document.getElementById('detected_timezone');
            if (timezoneField) {
                const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
                timezoneField.value = timezone;
            }
        });
    </script>
</body>

</html>