@extends('layouts.kid')

@section('title', 'Profile Settings - AllowanceLab')

@section('content')
                @php
    // Convert hex to RGB and create lighter shade
    $hex = ltrim($kid->color, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    // Mix with white (85% white + 15% color for light background)
    $lightR = round($r * 0.15 + 255 * 0.85);
    $lightG = round($g * 0.15 + 255 * 0.85);
    $lightB = round($b * 0.15 + 255 * 0.85);
    $lightShade = "rgb($lightR, $lightG, $lightB)";
                @endphp

                <style>
                    /* Dynamic theme color for sidebar/header */
                    .kid-header::after {
                        background-color:
                            {{ $kid->color }}
                            !important;
                    }

                    .kid-birthday-countdown {
                        color:
                            {{ $kid->color }}
                            !important;
                        border-bottom-color:
                            {{ $kid->color }}
                            !important;
                    }

                    .kid-birthday-icon {
                        background-color:
                            {{ $kid->color }}
                            !important;
                    }

                    .kid-menu-divider {
                        background-color:
                            {{ $kid->color }}
                            !important;
                    }

                    .kid-sidebar .kid-menu-item.active {
                        background:
                            {{ $lightShade }}
                            !important;
                        color:
                            {{ $kid->color }}
                            !important;
                        border-left-color:
                            {{ $kid->color }}
                            !important;
                    }

                    .kid-sidebar .kid-coming-soon-badge {
                        background:
                            {{ $lightShade }}
                            !important;
                        color:
                            {{ $kid->color }}
                            !important;
                    }
                </style>

                    <!-- Page Header -->
                    <div class="kid-profile-header">
                        <div class="kid-profile-info">
                            <div class="kid-profile-avatar" style="background: {{ $kid->color }};">
                                {{ strtoupper(substr($kid->name, 0, 1)) }}
                            </div>
                            <div>
                                <h1 class="kid-profile-title">My Profile</h1>
                                <div class="kid-profile-subtitle">View and update your settings</div>
                            </div>
                        </div>
                    </div>

                    <!-- Family Name Block -->
                    <div class="kid-family-block">
                        You are part of the <strong>{{ $parent->last_name }}</strong> family!
                    </div>

                    <!-- Account Stats Section -->
                    <div class="kid-profile-section">
                        <h3 class="kid-section-title">Account Stats 📊</h3>

                        <div class="kid-stats-grid">
                            <!-- Birthday Countdown -->
                            <div class="kid-stat-card birthday-card">
                                <div class="stat-icon">🎂</div>
                                <div class="stat-content">
                                    @php
    $today = now();
    $nextBirthday = \Carbon\Carbon::parse($kid->birthday)->year($today->year);
    if ($nextBirthday->isPast()) {
        $nextBirthday->addYear();
    }
    $daysUntil = floor($today->diffInDays($nextBirthday));
                                    @endphp
                                    <div class="stat-number">{{ $daysUntil }}</div>
                                    <div class="stat-label">Days Until Birthday!</div>
                                    <div class="stat-detail">{{ $nextBirthday->format('F j') }}</div>
                                </div>
                            </div>

                            <!-- Member Since -->
                            <div class="kid-stat-card">
                                <div class="stat-icon">⭐</div>
                                <div class="stat-content">
                                    <div class="stat-number">{{ floor($kid->created_at->diffInDays(now())) }}</div>
                                    <div class="stat-label">Days on AllowanceLab</div>
                                    <div class="stat-detail">Member since {{ $kid->created_at->format('M Y') }}</div>
                                </div>
                            </div>

                            <!-- Current Points -->
                            <div class="kid-stat-card points-card">
                                <div class="stat-icon">
                                    @if($kid->points >= 8)
                                        🌟
                                    @elseif($kid->points >= 5)
                                        💪
                                    @else
                                        🎯
                                    @endif
                                </div>
                                <div class="stat-content">
                                    <div class="stat-number" style="color: 
                                            @if($kid->points >= 8) #4CAF50
                                            @elseif($kid->points >= 5) #FFA726
                                            @else #ef5350
                                            @endif
                                        ">{{ $kid->points }}/{{ $kid->max_points }}</div>
                                    <div class="stat-label">Current Points</div>
                                    <div class="stat-message">
                                        @if($kid->points >= 8)
                                            Great work! You're crushing it! Keep up the awesome effort!
                                        @elseif($kid->points >= 5)
                                            You're doing well! Stay focused and keep trying your best!
                                        @else
                                            Stay sharp! Focus on your chores, goals, and listening to earn back your points!
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Information Section -->
                    <div class="kid-profile-section">
                        <h3 class="kid-section-title">Account Information</h3>

                        <div class="kid-info-row">
                            <div class="kid-info-field">
                                <label class="kid-label">Name</label>
                                <input type="text" class="kid-readonly-input" value="{{ $kid->name }}" readonly>
                            </div>
                            <div class="kid-info-field">
                                <label class="kid-label">Birthday</label>
                                <input type="text" class="kid-readonly-input" value="{{ $kid->birthday->format('F j, Y') }}" readonly>
                            </div>
                        </div>

                        <div class="kid-info-row">
                            <div class="kid-info-field">
                                <label class="kid-label">Username</label>
                                <div class="kid-field-with-button">
                                    <input type="text" class="kid-readonly-input" value="{{ $kid->username }}" readonly>
                                    <button type="button" class="kid-request-btn" onclick="requestUsernameChange()">
                                        Request Change
                                    </button>
                                </div>
                            </div>
                            <div class="kid-info-field">
                                <label class="kid-label">Password</label>
                                <div class="kid-field-with-button">
                                    <input type="password" class="kid-readonly-input" value="••••••••" readonly>
                                    <button type="button" class="kid-request-btn" onclick="requestPasswordReset()">
                                        Request Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Allowance Information Section -->
                    <div class="kid-profile-section">
                        <h3 class="kid-section-title">Allowance Information</h3>

                        <div class="kid-allowance-cards">
                            <div class="kid-allowance-card">
                                <div class="allowance-card-label">Your allowance is currently</div>
                                <div class="allowance-card-value">${{ number_format($kid->allowance_amount, 2) }}</div>
                            </div>

                            <div class="kid-allowance-card">
                                <div class="allowance-card-label">Your allowance will post</div>
                                <div class="allowance-card-value">EVERY {{ strtoupper($kid->allowance_day) }}</div>
                            </div>

                            <div class="kid-allowance-card">
                                @php
    $today = now();
    $daysOfWeek = ['sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6];
    $targetDay = $daysOfWeek[$kid->allowance_day];
    $currentDay = $today->dayOfWeek;

    if ($currentDay <= $targetDay) {
        $daysUntilAllowance = $targetDay - $currentDay;
    } else {
        $daysUntilAllowance = 7 - ($currentDay - $targetDay);
    }

    $nextAllowanceDate = $today->copy()->addDays($daysUntilAllowance);
                                @endphp
                                <div class="allowance-card-label">Your next allowance is in</div>
                                <div class="allowance-card-value">
                                    @if($daysUntilAllowance == 0)
                                        TODAY! 🎉
                                    @elseif($daysUntilAllowance == 1)
                                        1 MORE DAY
                                    @else
                                        {{ $daysUntilAllowance }} MORE DAYS
                                    @endif
                                </div>
                                <div class="allowance-card-date">{{ $nextAllowanceDate->format('M j') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Avatar & Theme Section -->
                    <div class="kid-profile-section">
                        <h3 class="kid-section-title">Avatar & Theme</h3>

                        <form action="{{ route('kid.update-color') }}" method="POST" id="colorForm">
                            @csrf
                            @method('PATCH')

                            <div class="kid-avatar-preview-section">
                                <div class="kid-avatar-preview-row">
                                    <div class="kid-avatar-preview-circle" id="avatarPreviewKid" style="background: {{ $kid->color }};">
                                        {{ strtoupper(substr($kid->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="kid-color-name" id="colorNameKid"></span>
                                        <div class="kid-color-hint">Choose your favorite color!</div>
                                    </div>
                                </div>

                                <div class="kid-color-grid">
                                    <div class="kid-color-option {{ $kid->color == '#80d4b0' ? 'selected' : '' }}"
                                        style="background: #80d4b0;" data-color="#80d4b0" data-name="Mint" onclick="selectColorKid(this)">
                                    </div>
                                    <div class="kid-color-option {{ $kid->color == '#ff9999' ? 'selected' : '' }}"
                                        style="background: #ff9999;" data-color="#ff9999" data-name="Coral" onclick="selectColorKid(this)">
                                    </div>
                                    <div class="kid-color-option {{ $kid->color == '#b19cd9' ? 'selected' : '' }}"
                                        style="background: #b19cd9;" data-color="#b19cd9" data-name="Lavender"
                                        onclick="selectColorKid(this)"></div>
                                    <div class="kid-color-option {{ $kid->color == '#87ceeb' ? 'selected' : '' }}"
                                        style="background: #87ceeb;" data-color="#87ceeb" data-name="Sky Blue"
                                        onclick="selectColorKid(this)"></div>
                                    <div class="kid-color-option {{ $kid->color == '#ffb380' ? 'selected' : '' }}"
                                        style="background: #ffb380;" data-color="#ffb380" data-name="Peach" onclick="selectColorKid(this)">
                                    </div>
                                    <div class="kid-color-option {{ $kid->color == '#e066a6' ? 'selected' : '' }}"
                                        style="background: #e066a6;" data-color="#e066a6" data-name="Magenta"
                                        onclick="selectColorKid(this)"></div>
                                    <div class="kid-color-option {{ $kid->color == '#ffd966' ? 'selected' : '' }}"
                                        style="background: #ffd966;" data-color="#ffd966" data-name="Sunshine"
                                        onclick="selectColorKid(this)"></div>
                                    <div class="kid-color-option {{ $kid->color == '#a8c686' ? 'selected' : '' }}"
                                        style="background: #a8c686;" data-color="#a8c686" data-name="Sage" onclick="selectColorKid(this)">
                                    </div>
                                    <div class="kid-color-option {{ $kid->color == '#5ab9b3' ? 'selected' : '' }}"
                                        style="background: #5ab9b3;" data-color="#5ab9b3" data-name="Teal" onclick="selectColorKid(this)">
                                    </div>
                                    <div class="kid-color-option {{ $kid->color == '#9bb7d4' ? 'selected' : '' }}"
                                        style="background: #9bb7d4;" data-color="#9bb7d4" data-name="Periwinkle"
                                        onclick="selectColorKid(this)"></div>
                                    <div class="kid-color-option {{ $kid->color == '#ff9966' ? 'selected' : '' }}"
                                        style="background: #ff9966;" data-color="#ff9966" data-name="Tangerine"
                                        onclick="selectColorKid(this)"></div>
                                    <div class="kid-color-option {{ $kid->color == '#d4a5d4' ? 'selected' : '' }}"
                                        style="background: #d4a5d4;" data-color="#d4a5d4" data-name="Lilac" onclick="selectColorKid(this)">
                                    </div>
                                </div>

                                <input type="hidden" name="color" id="colorInputKid" value="{{ $kid->color }}">
                            </div>

                            <div class="kid-form-actions">
                                <button type="submit" class="kid-save-btn">
                                    <i class="fas fa-check"></i> Save Theme Color
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Toast Notification -->
                    <div class="kid-toast" id="kidToast">
                        <i class="fas fa-info-circle"></i>
                        <span id="kidToastMessage"></span>
                    </div>

                    <style>

                        /* Profile Header */
                        .kid-profile-header {
                            margin-bottom: 30px;
                        }

                        .kid-profile-info {
                            display: flex;
                            align-items: center;
                            gap: 20px;
                        }

                        .kid-profile-avatar {
                            width: 80px;
                            height: 80px;
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 36px;
                            font-weight: 700;
                            color: white;
                            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                        }

                        .kid-profile-title {
                            font-size: 32px;
                            font-weight: 700;
                            color: #1a1a1a;
                            margin: 0 0 4px 0;
                        }

                        .kid-profile-subtitle {
                            font-size: 16px;
                            color: #666;
                        }

                        /* Profile Sections */
                        .kid-profile-section {
                            background: white;
                            border-radius: 16px;
                            padding: 30px;
                            margin-bottom: 24px;
                            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
                        }

                        .kid-section-title {
                            font-size: 22px;
                            font-weight: 700;
                            color: #1a1a1a;
                            margin: 0 0 24px 0;
                        }

                        /* Stats Grid */
                        .kid-stats-grid {
                            display: grid;
                            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                            gap: 20px;
                        }

                        .kid-stat-card {
                            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
                            border-radius: 12px;
                            padding: 24px;
                            display: flex;
                            align-items: center;
                            gap: 16px;
                        }

                        .kid-stat-card.birthday-card {
                            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
                        }

                        .kid-stat-card.points-card {
                            background: linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%);
                        }

                        .stat-icon {
                            font-size: 48px;
                            flex-shrink: 0;
                        }

                        .stat-content {
                            flex: 1;
                        }

                        .stat-number {
                            font-size: 36px;
                            font-weight: 700;
                            color: #1a1a1a;
                            line-height: 1;
                            margin-bottom: 4px;
                        }

                        .stat-label {
                            font-size: 14px;
                            font-weight: 600;
                            color: #555;
                            margin-bottom: 4px;
                        }

                        .stat-detail {
                            font-size: 13px;
                            color: #666;
                        }

                        .stat-message {
                            font-size: 13px;
                            color: #555;
                            margin-top: 8px;
                            font-style: italic;
                        }

                        /* Info Rows */
                        .kid-info-row {
                            display: grid;
                            grid-template-columns: 1fr 1fr;
                            gap: 20px;
                            margin-bottom: 20px;
                        }

                        .kid-info-row:last-child {
                            margin-bottom: 0;
                        }

                        .kid-info-field {
                            display: flex;
                            flex-direction: column;
                            gap: 8px;
                        }

                        .kid-label {
                            font-size: 14px;
                            font-weight: 600;
                            color: #666;
                        }

                        .kid-readonly-input {
                            padding: 14px 16px;
                            border: 2px solid #e0e0e0;
                            border-radius: 8px;
                            font-size: 16px;
                            background: #f5f5f5;
                            color: #1a1a1a;
                            font-weight: 500;
                        }

                        .kid-field-with-button {
                            display: flex;
                            gap: 10px;
                            align-items: flex-end;
                        }

                        .kid-field-with-button .kid-readonly-input {
                            flex: 1;
                        }

                        .kid-request-btn {
                            padding: 14px 20px;
                            background: #42a5f5;
                            color: white;
                            border: none;
                            border-radius: 8px;
                            font-size: 14px;
                            font-weight: 600;
                            cursor: pointer;
                            transition: all 0.2s;
                            white-space: nowrap;
                        }

                        .kid-request-btn:hover {
                            background: #1e88e5;
                            transform: translateY(-2px);
                        }

                        /* Allowance Cards */
                        .kid-allowance-cards {
                            display: grid;
                            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                            gap: 20px;
                        }

                        .kid-allowance-card {
                            background: white;
                            border: 2px solid #e0e0e0;
                            border-radius: 12px;
                            padding: 24px;
                            text-align: center;
                        }

                        .allowance-card-label {
                            font-size: 14px;
                            color: #666;
                            margin-bottom: 8px;
                            font-weight: 500;
                        }

                        .allowance-card-value {
                            font-size: 28px;
                            font-weight: 700;
                            color: #1a1a1a;
                            margin-bottom: 4px;
                        }

                        .allowance-card-date {
                            font-size: 14px;
                            color: #888;
                            margin-top: 4px;
                        }

                        /* Avatar & Theme */
                        .kid-avatar-preview-section {
                            display: flex;
                            flex-direction: column;
                            gap: 24px;
                        }

                        .kid-avatar-preview-row {
                            display: flex;
                            align-items: center;
                            gap: 20px;
                        }

                        .kid-avatar-preview-circle {
                            width: 80px;
                            height: 80px;
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 36px;
                            font-weight: 700;
                            color: white;
                            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                        }

                        .kid-color-name {
                            font-size: 20px;
                            font-weight: 700;
                            color: #1a1a1a;
                        }

                        .kid-color-hint {
                            font-size: 14px;
                            color: #666;
                        }

                        .kid-color-grid {
                            display: grid;
                            grid-template-columns: repeat(6, 1fr);
                            gap: 16px;
                            margin-bottom: 20px;
                        }

                        .kid-color-option {
                            height:120px;
                            width: 120px;
                            border-radius: 50%;
                            cursor: pointer;
                            border: 4px solid transparent;
                            transition: all 0.2s;
                        }

                        .kid-color-option:hover {
                            transform: scale(1.1);
                        }

                        .kid-color-option.selected {
                            border-color: #1a1a1a;
                            transform: scale(1.15);
                        }

                        /* Form Actions */
                        .kid-form-actions {
                            margin-top: 24px;
                            display: flex;
                            justify-content: center;
                        }

                        .kid-save-btn {
                            width: 100%;
                            max-width: 400px;
                            padding: 16px;
                            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
                            color: white;
                            border: none;
                            border-radius: 12px;
                            font-size: 18px;
                            font-weight: 700;
                            cursor: pointer;
                            transition: all 0.3s;
                            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
                        }

                        .kid-save-btn:hover {
                            transform: translateY(-2px);
                            box-shadow: 0 6px 16px rgba(76, 175, 80, 0.4);
                        }

                        /* Toast Notification */
                        .kid-toast {
                            position: fixed;
                            bottom: 30px;
                            right: 30px;
                            background: #1a1a1a;
                            color: white;
                            padding: 16px 24px;
                            border-radius: 12px;
                            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
                            display: flex;
                            align-items: center;
                            gap: 12px;
                            font-size: 16px;
                            font-weight: 500;
                            opacity: 0;
                            transform: translateY(20px);
                            transition: all 0.3s;
                            pointer-events: none;
                            z-index: 10000;
                        }

                        .kid-toast.show {
                            opacity: 1;
                            transform: translateY(0);
                            pointer-events: auto;
                        }

                        /* Responsive */
                        @media (max-width: 768px) {
                            .kid-profile-header {
                                margin-bottom: 20px;
                            }

                            .kid-profile-avatar {
                                width: 60px;
                                height: 60px;
                                font-size: 28px;
                            }

                            .kid-profile-title {
                                font-size: 24px;
                            }

                            .kid-profile-section {
                                padding: 20px;
                            }

                            .kid-section-title {
                                font-size: 20px;
                            }

                            .kid-stats-grid {
                                grid-template-columns: 1fr;
                            }

                            .stat-icon {
                                font-size: 40px;
                            }

                            .stat-number {
                                font-size: 28px;
                            }

                            .kid-info-row {
                                grid-template-columns: 1fr;
                            }

                            .kid-field-with-button {
                                flex-direction: column;
                                align-items: stretch;
                            }

                            .kid-request-btn {
                                width: 100%;
                            }

                            .kid-allowance-cards {
                                grid-template-columns: 1fr;
                            }

                            .kid-color-grid {
                                grid-template-columns: repeat(4, 1fr);
                                gap: 12px;
                            }

                            .kid-color-option {
                                width: 80%;
                                aspect-ratio: 1;
                                height: auto;
                            }

                            .kid-toast {
                                bottom: 20px;
                                right: 20px;
                                left: 20px;
                            }
                        }
                    </style>

                    <script>
                        // Set initial color name on page load
                        document.addEventListener('DOMContentLoaded', function () {
                            const currentColor = '{{ $kid->color }}';
                            const colorOptions = document.querySelectorAll('.kid-color-option');

                            colorOptions.forEach(option => {
                                if (option.dataset.color === currentColor) {
                                    document.getElementById('colorNameKid').textContent = option.dataset.name;
                                }
                            });

                            // Show success message if redirected after color update
                            @if(session('success'))
                                showToast('{{ session('success') }}');
                            @endif
                            });

                        // Color selection
                        function selectColorKid(element) {
                            document.querySelectorAll('.kid-color-option').forEach(opt => opt.classList.remove('selected'));
                            element.classList.add('selected');
                            document.getElementById('colorInputKid').value = element.dataset.color;
                            document.getElementById('avatarPreviewKid').style.background = element.dataset.color;
                            document.getElementById('colorNameKid').textContent = element.dataset.name;
                        }

                        // Request username change (toast)
                        function requestUsernameChange() {
                            showToast('Ask your parent to help you change your username!');
                        }

                        // Request password reset (toast)
                        function requestPasswordReset() {
                            showToast('Ask your parent to help you reset your password!');
                        }

                        // Show toast notification
                        function showToast(message) {
                            const toast = document.getElementById('kidToast');
                            const toastMessage = document.getElementById('kidToastMessage');

                            toastMessage.textContent = message;
                            toast.classList.add('show');

                            setTimeout(() => {
                                toast.classList.remove('show');
                            }, 4000);
                        }
                    </script>
@endsection