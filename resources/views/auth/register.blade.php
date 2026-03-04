<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Create Account - AllowanceLab</title>
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
            color: #10b981;
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

        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            padding: 44px 44px;
            width: 100%;
            max-width: 420px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .register-icon {
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

        .register-title {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
            letter-spacing: -0.3px;
        }

        .register-subtitle {
            font-size: 15px;
            color: #64748b;
            line-height: 1.5;
        }

        /* Form */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 16px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group:last-of-type {
            margin-bottom: 24px;
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

        /* No icon for name fields (side by side) */
        .form-input.no-icon {
            padding-left: 14px;
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

        /* Login link */
        .login-link {
            text-align: center;
            margin-top: 22px;
            padding-top: 22px;
            border-top: 1px solid #f1f5f9;
            font-size: 14px;
            color: #64748b;
        }

        .login-link a {
            color: #10b981;
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s;
        }

        .login-link a:hover {
            color: #059669;
        }

        /* Trust line below card */
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

            .register-card {
                padding: 32px 24px;
                box-shadow: none;
                border: 1px solid #e2e8f0;
            }
        }

        @media (max-width: 480px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
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
                <i class="fas fa-heart"></i> Free for Families
            </div>
            <h2 class="left-headline">Teach kids that money is <span class="highlight">earned.</span></h2>
            <p class="left-sub">Set up automated allowances, track savings goals, and build real financial habits — all in one place.</p>

            <div class="left-perks">
                <div class="perk-item">
                    <div class="perk-icon"><i class="fas fa-bolt"></i></div>
                    <div class="perk-text">
                        <span class="perk-title">Set up in 5 minutes</span>
                        <span class="perk-desc">Add your kids, set their allowance day and amount.</span>
                    </div>
                </div>
                <div class="perk-item">
                    <div class="perk-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="perk-text">
                        <span class="perk-title">Allowances run automatically</span>
                        <span class="perk-desc">No more forgetting payday — it just works.</span>
                    </div>
                </div>
                <div class="perk-item">
                    <div class="perk-icon"><i class="fas fa-piggy-bank"></i></div>
                    <div class="perk-text">
                        <span class="perk-title">Kids track their own goals</span>
                        <span class="perk-desc">Savings goals that make responsibility visible.</span>
                    </div>
                </div>
                <div class="perk-item">
                    <div class="perk-icon"><i class="fas fa-lock"></i></div>
                    <div class="perk-text">
                        <span class="perk-title">Free to start</span>
                        <span class="perk-desc">Core features included at no cost. No credit card needed to get going.</span>
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
        <div class="register-card">
            <div class="register-header">
                <div class="register-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <h1 class="register-title">Create Your Account</h1>
                <p class="register-subtitle">Start teaching your kids smart money habits today.</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <input type="hidden" name="timezone" id="detected_timezone">

                <!-- Name row -->
                <div class="form-row">
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="first_name" class="form-label">First Name</label>
                        <input id="first_name" class="form-input no-icon @error('first_name') error @enderror"
                            type="text" name="first_name" value="{{ old('first_name') }}"
                            required autofocus autocomplete="given-name" placeholder="Jane">
                        @error('first_name')
                            <span class="error-message"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input id="last_name" class="form-input no-icon @error('last_name') error @enderror"
                            type="text" name="last_name" value="{{ old('last_name') }}"
                            required autocomplete="family-name" placeholder="Smith">
                        @error('last_name')
                            <span class="error-message"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input id="email" class="form-input @error('email') error @enderror"
                            type="email" name="email" value="{{ old('email') }}"
                            required autocomplete="username" placeholder="you@example.com">
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
                            type="password" name="password" required autocomplete="new-password"
                            placeholder="At least 8 characters">
                    </div>
                    @error('password')
                        <span class="error-message"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-shield-halved input-icon"></i>
                        <input id="password_confirmation"
                            class="form-input @error('password_confirmation') error @enderror"
                            type="password" name="password_confirmation" required
                            autocomplete="new-password" placeholder="Same password again">
                    </div>
                    @error('password_confirmation')
                        <span class="error-message"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-rocket"></i>
                    Create My Account
                </button>

                <div class="login-link">
                    Already have an account? <a href="{{ route('login') }}">Sign in here</a>
                </div>
            </form>
        </div>

        <div class="trust-line">
            <i class="fas fa-lock"></i>
            Free to start &mdash; no credit card required
        </div>
    </div>

    <script>
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
