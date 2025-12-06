<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head', ['title' => 'Accept Family Invitation - AllowanceLab'])
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

        /* Invite Info Banner */
        .invite-info {
            background: #e8f5e9;
            border: 2px solid #4CAF50;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            text-align: center;
        }

        .invite-info p {
            color: #2e7d32;
            font-size: 15px;
            font-weight: 600;
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

        .form-input:read-only {
            background: #f5f5f5;
            cursor: not-allowed;
        }

        .error-message {
            color: #ef5350;
            font-size: 13px;
            margin-top: 6px;
            display: block;
        }

        /* Alert Messages */
        .alert {
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 2px solid #ef5350;
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

        /* Expired State */
        .expired-container {
            text-align: center;
            padding: 40px 20px;
        }

        .expired-icon {
            font-size: 64px;
            color: #ef5350;
            margin-bottom: 20px;
        }

        .expired-title {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 12px;
        }

        .expired-text {
            font-size: 16px;
            color: #666;
            margin-bottom: 28px;
            line-height: 1.5;
        }

        .back-link {
            display: inline-block;
            padding: 12px 32px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .back-link:hover {
            background: #45a049;
            transform: translateY(-2px);
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
            @if($invite->isExpired() || $invite->status !== 'pending')
                <!-- Expired State -->
                <div class="expired-container">
                    <div class="expired-icon">⏰</div>
                    <h1 class="expired-title">Invitation Expired</h1>
                    <p class="expired-text">This invitation has expired or has already been used.</p>
                    <a href="{{ url('/') }}" class="back-link">← Back to Home</a>
                </div>
            @else
                <!-- Active Invite -->
                <div class="register-header">
                    <h1 class="register-title">Accept Family Invitation</h1>
                    <p class="register-subtitle">Create your account to join the family.</p>
                </div>

                <!-- Invite Info -->
                <div class="invite-info">
                    <p>{{ $family->owner->name }} has invited you to join their family as a Parent Account with full access.
                    </p>
                </div>

                @if($errors->any())
                    <div class="alert alert-error">
                        @foreach($errors->all() as $error)
                            {{ $error }}<br>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('family.process-invite', ['token' => $invite->token]) }}">
                    @csrf

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

                    <!-- Email (readonly) -->
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input id="email" class="form-input" type="email" name="email" value="{{ $invite->email }}"
                            readonly>
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
                        Accept & Create Account
                    </button>
                </form>
            @endif
        </div>
    </main>
</body>

</html>