<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Your Account - AllowanceLab</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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

        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            padding: 50px;
            width: 100%;
            max-width: 500px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .welcome-icon {
            font-size: 56px;
            margin-bottom: 12px;
        }

        .register-title {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .kid-name {
            color: #42a5f5;
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

        .input-with-validation {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-with-validation input {
            flex: 1;
            padding-right: 50px;
        }

        .validation-icon {
            position: absolute;
            right: 16px;
            font-size: 20px;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .validation-icon.valid {
            color: #10b981;
            animation: scaleIn 0.3s ease;
        }

        .validation-icon.invalid {
            color: #ef4444;
            animation: shakeIcon 0.3s ease;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0.5);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes shakeIcon {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        .validation-icon.loading {
            color: #10b981;
            animation: pulse 1s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        /* Avatar Color Picker */
        .avatar-section {
            margin-bottom: 28px;
        }

        .avatar-preview-row {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
        }

        .avatar-preview {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }

        .color-name {
            font-size: 18px;
            font-weight: 600;
            color: #444;
        }

        .color-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 12px;
        }

        .color-option {
            width: 100%;
            aspect-ratio: 1;
            border: 3px solid transparent;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.2s;
        }

        .color-option:hover {
            transform: scale(1.1);
        }

        .color-option.selected {
            border-color: #1a1a1a;
            transform: scale(1.15);
        }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #42a5f5 0%, #8b5cf6 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 20px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(66, 165, 245, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(66, 165, 245, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* Responsive */
        @media (max-width: 768px) {
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
                border-radius: 16px;
            }

            .welcome-icon {
                font-size: 48px;
            }

            .register-title {
                font-size: 28px;
            }

            .register-subtitle {
                font-size: 15px;
            }

            .avatar-preview {
                width: 60px;
                height: 60px;
                font-size: 28px;
            }

            .color-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 10px;
            }
        }

        @media (max-width: 400px) {
            .register-container {
                padding: 30px 20px;
            }

            .color-grid {
                grid-template-columns: repeat(3, 1fr);
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
                <div class="welcome-icon">👋</div>
                <h1 class="register-title">Welcome, <span class="kid-name">{{ $kid->name }}</span>!</h1>
                <p class="register-subtitle">Create your username and password to get started</p>
            </div>

            <form action="{{ route('invite.accept', $invite->token) }}" method="POST" id="registerForm">
                @csrf

                <!-- Username -->
                <div class="form-group">
                    <label for="username" class="form-label">Choose a Username</label>
                    <div class="input-with-validation">
                        <input type="text" id="username" name="username"
                            class="form-input @error('username') error @enderror" placeholder="Enter a unique username"
                            value="{{ old('username') }}" onblur="validateUsername(this)" required minlength="3"
                            maxlength="20">
                        <span class="validation-icon" id="usernameValidation"></span>
                    </div>
                    <small class="error-message" id="usernameError"></small>
                    @error('username')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">Create a Password</label>
                    <input type="password" id="password" name="password"
                        class="form-input @error('password') error @enderror" placeholder="At least 4 characters"
                        required minlength="4">
                    @error('password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="form-input @error('password_confirmation') error @enderror"
                        placeholder="Re-enter your password" required minlength="4">
                </div>

                <!-- Avatar Color Picker -->
                <div class="avatar-section">
                    <label class="form-label">Choose Your Avatar Color</label>
                    <div class="avatar-preview-row">
                        <div class="avatar-preview" id="avatarPreview" style="background: {{ $kid->color }};">
                            {{ strtoupper(substr($kid->name, 0, 1)) }}
                        </div>
                        <span class="color-name" id="colorName"></span>
                    </div>
                    <input type="hidden" name="color" id="selectedColor" value="{{ $kid->color }}">
                    <div class="color-grid">
                        <div class="color-option" style="background: #80d4b0;" data-color="#80d4b0" data-name="Mint"
                            onclick="selectColorRegister(this)"></div>
                        <div class="color-option" style="background: #ff9999;" data-color="#ff9999" data-name="Coral"
                            onclick="selectColorRegister(this)"></div>
                        <div class="color-option" style="background: #b19cd9;" data-color="#b19cd9" data-name="Lavender"
                            onclick="selectColorRegister(this)"></div>
                        <div class="color-option" style="background: #87ceeb;" data-color="#87ceeb" data-name="Sky Blue"
                            onclick="selectColorRegister(this)"></div>
                        <div class="color-option" style="background: #ffb380;" data-color="#ffb380" data-name="Peach"
                            onclick="selectColorRegister(this)"></div>
                        <div class="color-option" style="background: #e066a6;" data-color="#e066a6" data-name="Magenta"
                            onclick="selectColorRegister(this)"></div>
                        <div class="color-option" style="background: #ffd966;" data-color="#ffd966" data-name="Sunshine"
                            onclick="selectColorRegister(this)"></div>
                        <div class="color-option" style="background: #a8c686;" data-color="#a8c686" data-name="Sage"
                            onclick="selectColorRegister(this)"></div>
                        <div class="color-option" style="background: #5ab9b3;" data-color="#5ab9b3" data-name="Teal"
                            onclick="selectColorRegister(this)"></div>
                        <div class="color-option" style="background: #9bb7d4;" data-color="#9bb7d4"
                            data-name="Periwinkle" onclick="selectColorRegister(this)"></div>
                        <div class="color-option" style="background: #ff9966;" data-color="#ff9966"
                            data-name="Tangerine" onclick="selectColorRegister(this)"></div>
                        <div class="color-option" style="background: #d4a5d4;" data-color="#d4a5d4" data-name="Lilac"
                            onclick="selectColorRegister(this)"></div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="submit-btn">
                    <i class="fas fa-rocket"></i> Create My Account
                </button>
            </form>
        </div>
    </main>

    <script>
        // Color selection
        function selectColorRegister(element) {
            document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('selected'));
            element.classList.add('selected');
            document.getElementById('selectedColor').value = element.dataset.color;
            document.getElementById('avatarPreview').style.background = element.dataset.color;
            document.getElementById('colorName').textContent = element.dataset.name;
        }

        // Set initial selected color on page load
        document.addEventListener('DOMContentLoaded', function () {
            const currentColor = '{{ $kid->color }}';
            const colorOptions = document.querySelectorAll('.color-option');

            colorOptions.forEach(option => {
                if (option.dataset.color === currentColor) {
                    option.classList.add('selected');
                    document.getElementById('colorName').textContent = option.dataset.name;
                }
            });

            // Form validation
            const form = document.getElementById('registerForm');
            const password = document.getElementById('password');
            const passwordConfirm = document.getElementById('password_confirmation');

            // Add error message element for password mismatch
            const passwordError = document.createElement('small');
            passwordError.id = 'passwordMatchError';
            passwordError.className = 'error-message';
            passwordError.style.display = 'none';
            passwordConfirm.parentElement.appendChild(passwordError);

            // Clear error when typing in password fields
            password.addEventListener('input', function () {
                passwordError.style.display = 'none';
                passwordConfirm.style.borderColor = '#e0e0e0';
            });

            passwordConfirm.addEventListener('input', function () {
                passwordError.style.display = 'none';
                passwordConfirm.style.borderColor = '#e0e0e0';
            });

            form.addEventListener('submit', function (e) {
                // Check if passwords match
                if (password.value !== passwordConfirm.value) {
                    e.preventDefault();
                    passwordError.textContent = 'Passwords do not match. Please make sure both passwords are the same.';
                    passwordError.style.display = 'block';
                    passwordConfirm.style.borderColor = '#ef5350';
                    passwordConfirm.focus();
                    return false;
                }
            });
        });

        // Real-time username validation
        function validateUsername(input) {
            const username = input.value.trim();
            const feedbackIcon = document.getElementById('usernameValidation');
            const errorMessage = document.getElementById('usernameError');

            // Clear previous state
            feedbackIcon.className = 'validation-icon';
            errorMessage.textContent = '';

            // Must have at least 3 characters to check
            if (username.length < 3) {
                feedbackIcon.className = 'validation-icon';
                return;
            }

            // Show loading state
            feedbackIcon.className = 'validation-icon loading';
            feedbackIcon.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            // Check username via AJAX (with minimum 1.2 second loading state)
            const startTime = Date.now();

            fetch('/check-username', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ username: username })
            })
                .then(response => response.json())
                .then(data => {
                    // Calculate how long the request took
                    const elapsed = Date.now() - startTime;
                    const remainingTime = Math.max(0, 1200 - elapsed);

                    // Delay showing result to ensure visible loading state
                    setTimeout(() => {
                        if (data.available) {
                            feedbackIcon.className = 'validation-icon valid';
                            feedbackIcon.innerHTML = '<i class="fas fa-check-circle"></i>';
                            errorMessage.textContent = '';
                        } else {
                            feedbackIcon.className = 'validation-icon invalid';
                            feedbackIcon.innerHTML = '<i class="fas fa-times-circle"></i>';
                            errorMessage.textContent = data.message;
                        }
                    }, remainingTime);
                })
                .catch(error => {
                    console.error('Error:', error);
                    feedbackIcon.className = 'validation-icon';
                    feedbackIcon.innerHTML = '';
                });
        }
    </script>
</body>

</html>