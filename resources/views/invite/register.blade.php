<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Your Account - AllowanceLab</title>
    @vite(['resources/css/dashboard.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .register-container {
            background: white;
            padding: 50px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .welcome-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        h1 {
            color: #1f2937;
            margin: 0 0 10px 0;
            font-size: 28px;
        }

        .kid-name {
            color: #667eea;
            font-weight: 700;
        }

        .subtitle {
            color: #6b7280;
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #374151;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
        }

        .avatar-section {
            margin: 30px 0;
        }

        .avatar-preview-row {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .avatar-preview-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 700;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .color-name {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
        }

        .color-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
        }

        .color-option {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.2s;
        }

        .color-option:hover {
            transform: scale(1.1);
        }

        .color-option.selected {
            border-color: #1f2937;
            box-shadow: 0 0 0 2px white, 0 0 0 4px #1f2937;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
        }

        .error-message {
            color: #ef4444;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="header">
            <div class="welcome-icon">👋</div>
            <h1>Welcome, <span class="kid-name">{{ $kid->name }}</span>!</h1>
            <p class="subtitle">Create your username and password to get started</p>
        </div>

        <form action="{{ route('invite.accept', $invite->token) }}" method="POST" id="registerForm">
            @csrf

            <div class="form-group">
                <label for="username">Choose a Username</label>
                <input type="text" id="username" name="username" placeholder="Enter a unique username"
                    value="{{ old('username') }}" required minlength="3" maxlength="20">
                @error('username')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Create a Password</label>
                <input type="password" id="password" name="password" placeholder="At least 4 characters" required
                    minlength="4">
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                    placeholder="Re-enter your password" required minlength="4">
            </div>

            <div class="avatar-section">
                <label>Choose Your Avatar Color</label>
                <div class="avatar-preview-row">
                    <div class="avatar-preview-circle" id="avatarPreview" style="background: {{ $kid->color }};">
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
                    <div class="color-option" style="background: #9bb7d4;" data-color="#9bb7d4" data-name="Periwinkle"
                        onclick="selectColorRegister(this)"></div>
                    <div class="color-option" style="background: #ff9966;" data-color="#ff9966" data-name="Tangerine"
                        onclick="selectColorRegister(this)"></div>
                    <div class="color-option" style="background: #d4a5d4;" data-color="#d4a5d4" data-name="Lilac"
                        onclick="selectColorRegister(this)"></div>
                </div>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-rocket"></i> Create My Account
            </button>
        </form>
    </div>

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

            form.addEventListener('submit', function (e) {
                // Check if passwords match
                if (password.value !== passwordConfirm.value) {
                    e.preventDefault();
                    alert('Passwords do not match! Please make sure both passwords are the same.');
                    passwordConfirm.focus();
                    return false;
                }
            });
        });
    </script>
</body>

</html>