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

            /* Theme colored borders and text for allowance cards */
            .kid-allowance-card {
                border: 2px solid
                    {{ $kid->color }}
                    !important;
            }

            .kid-allowance-card .allowance-card-value {
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
            <a href="{{ route('kid.dashboard') }}" class="kid-back-btn-desktop">
                ← Back to Dashboard
            </a>
        </div>

        <!-- Family Name Block -->
        <div class="kid-family-block">
            You are part of the <strong>{{ $parent?->last_name ?? 'your' }}</strong> family!
        </div>

        <!-- Mobile Back Button (below header) -->
        <a href="{{ route('kid.dashboard') }}" class="kid-back-btn-mobile-top">
            ← Back to Dashboard
        </a>

        <!-- Account Stats Section -->
        <div class="kid-profile-section">
            <h3 class="kid-section-title">Account Stats</h3>

            <!-- Birthday and Member Stats - Side by Side -->
            <div class="kid-stats-row">
                <!-- Birthday Countdown -->
                @php
    $today = now();
    $nextBirthday = \Carbon\Carbon::parse($kid->birthday)->year($today->year);
    if ($nextBirthday->isPast()) {
        $nextBirthday->addYear();
    }
    $daysUntil = floor($today->diffInDays($nextBirthday));
                @endphp
                <div class="kid-stat-badge">
                    <div class="stat-badge-icon" style="background: {{ $kid->color }}20; color: {{ $kid->color }};">
                        <i class="fas fa-birthday-cake"></i>
                    </div>
                    <div class="stat-badge-content">
                        <div class="stat-badge-number" data-target="{{ $daysUntil }}" style="color: {{ $kid->color }};">0</div>
                        <div class="stat-badge-label">Days Until Birthday</div>
                        <div class="stat-badge-detail">{{ $nextBirthday->format('F j') }}</div>
                    </div>
                </div>

                <!-- Member Since -->
                <div class="kid-stat-badge">
                    <div class="stat-badge-icon" style="background: {{ $kid->color }}20; color: {{ $kid->color }};">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-badge-content">
                        <div class="stat-badge-number" data-target="{{ floor($kid->created_at->diffInDays(now())) }}"
                            style="color: {{ $kid->color }};">0</div>
                        <div class="stat-badge-label">Days on AllowanceLab</div>
                        <div class="stat-badge-detail">Member since {{ $kid->created_at->format('M Y') }}</div>
                    </div>
                </div>
            </div>

            <!-- Current Points Section -->
            <div class="kid-points-section">
                <div class="points-header 
                            @if($kid->points >= 8) 
                            @elseif($kid->points >= 5) warning
                            @elseif($kid->points >= 1) danger
                            @else danger
                            @endif">
                    <div class="points-pill-label">POINTS</div>
                    <div class="points-current-number" data-target="{{ $kid->points }}">0</div>
                    <div class="points-pill-message">
                        @if($kid->points >= 8)
                            You're crushing it! Keep up the awesome work!
                        @elseif($kid->points >= 5)
                            You're doing okay, but there is room to improve!
                        @elseif($kid->points >= 1)
                            Uh oh, you've got some work to do! Try to find ways to help out and earn some points!
                        @else
                            This means you won't get your allowance for the week unless you earn back some points! Grab some extra
                            chores and really get your room picked up. You got this!
                        @endif
                    </div>
                </div>

                <!-- Points Legend Grid -->
                <div class="points-legend-grid">
                    <div class="points-legend-item {{ $kid->points >= 8 && $kid->points <= 10 ? 'active' : '' }}">
                        <div class="legend-color-tab green"></div>
                        <div class="legend-content">
                            <div class="legend-range">8-10 Points</div>
                            <div class="legend-message">This means you're doing great, keep up the good work!</div>
                        </div>
                    </div>

                    <div class="points-legend-item {{ $kid->points >= 5 && $kid->points <= 7 ? 'active' : '' }}">
                        <div class="legend-color-tab yellow"></div>
                        <div class="legend-content">
                            <div class="legend-range">5-7 Points</div>
                            <div class="legend-message">This means you're doing okay, but there is a lot of room to improve!
                            </div>
                        </div>
                    </div>

                    <div class="points-legend-item {{ $kid->points >= 1 && $kid->points <= 4 ? 'active' : '' }}">
                        <div class="legend-color-tab red"></div>
                        <div class="legend-content">
                            <div class="legend-range">1-4 Points</div>
                            <div class="legend-message">Uh oh, you've got some work to do! Try to find ways to help out and earn
                                some points!</div>
                        </div>
                    </div>

                    <div class="points-legend-item {{ $kid->points == 0 ? 'active' : '' }}">
                        <div class="legend-color-tab danger"></div>
                        <div class="legend-content">
                            <div class="legend-range">0 Points</div>
                            <div class="legend-message">This means you won't get your allowance for the week unless you earn
                                back some points! Grab some extra chores and really get your room picked up. You got this!</div>
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
                        <div class="kid-color-option {{ $kid->color == '#f59e0b' ? 'selected' : '' }}"
                            style="background: #f59e0b;" data-color="#f59e0b" data-name="Sunshine"
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

        <!-- Mobile Back Button (bottom) -->
        <a href="{{ route('kid.dashboard') }}" class="kid-back-btn-mobile-bottom">
            ← Back to Dashboard
        </a>

        <!-- Toast Notification -->
        <div class="kid-toast" id="kidToast">
            <i class="fas fa-info-circle"></i>
            <span id="kidToastMessage"></span>
        </div>

        <style>
            /* Profile Header */
    .kid-profile-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
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

            /* Account Stats - Badge Style */
            .kid-stats-row {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 16px;
                margin-bottom: 24px;
            }

            .kid-stat-badge {
                display: flex;
                align-items: flex-start;
                gap: 16px;
                padding: 20px;
                background: white;
                border: 2px solid #e0e0e0;
                border-radius: 12px;
                transition: all 0.3s;
            }

            .kid-stat-badge:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            }

            .stat-badge-icon {
                width: 56px;
                height: 56px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                flex-shrink: 0;
            }

            .stat-badge-content {
                flex: 1;
                display: flex;
                flex-direction: column;
                gap: 4px;
            }

            .stat-badge-number {
                font-size: 36px;
                font-weight: 700;
                line-height: 1;
                margin-bottom: 4px;
            }

            .stat-badge-label {
                font-size: 14px;
                font-weight: 600;
                color: #666;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .stat-badge-detail {
                font-size: 13px;
                color: #999;
                margin-top: 2px;
            }

            /* Points Section */
            .kid-points-section {
                background: white;
                border-radius: 16px;
                padding: 32px;
                border: 2px solid #e0e0e0;
            }

            .points-header {
                background: #e8f5e9;
                padding: 20px 28px;
                border-radius: 16px;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 8px;
                max-width: 400px;
                text-align: center;
                margin: 0 auto 32px auto;
            }

            .points-header.warning {
                background: #fff3e0;
            }

            .points-header.danger {
                background: #ffebee;
            }

            .points-pill-label {
                font-size: 11px;
                color: #666;
                font-weight: 500;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .points-current-number {
                font-size: 36px;
                font-weight: 700;
                color: #4CAF50;
                line-height: 1;
            }

            .points-header.warning .points-current-number,
            .points-header.warning .points-pill-message {
                color: #FFA726;
            }

            .points-header.danger .points-current-number,
            .points-header.danger .points-pill-message {
                color: #ef5350;
            }

            .points-pill-message {
                font-size: 15px;
                color: #4CAF50;
                text-align: center;
                line-height: 1.4;
                margin-top: 4px;
                font-weight: 600;
            }

            /* Points Legend Grid */
            .points-legend-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 16px;
            }

            .points-legend-item {
                display: flex;
                gap: 12px;
                padding: 16px;
                background: #f8f9fa;
                border-radius: 10px;
                border: 2px solid transparent;
                transition: all 0.3s;
            }

            .legend-color-tab {
                width: 15px;
                border-radius: 4px;
                flex-shrink: 0;
            }

            .legend-color-tab.green {
                background: #4CAF50;
            }

            .legend-color-tab.yellow {
                background: #FFA726;
            }

            .legend-color-tab.red {
                background: #ef5350;
            }

            .legend-color-tab.danger {
                background: #c62828;
            }

            .legend-content {
                flex: 1;
            }

            .legend-range {
                font-size: 14px;
                font-weight: 700;
                color: #1a1a1a;
                margin-bottom: 4px;
            }

            .legend-message {
                font-size: 13px;
                color: #666;
                line-height: 1.4;
            }

            /* Mobile Responsive */
            @media (max-width: 768px) {
                .kid-stats-row {
                    grid-template-columns: 1fr;
                }

                .points-current-number {
                    font-size: 56px;
                }

                .points-status-message {
                    font-size: 16px;
                }

                .points-legend-grid {
                    grid-template-columns: 1fr;
                }

                .kid-points-section {
                    padding: 24px 20px;
                }
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
                height: 120px;
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

            /* Back to Dashboard Buttons */
            .kid-back-btn-desktop {
                padding: 10px 20px;
                background: white;
                border: 2px solid #e0e0e0;
                border-radius: 8px;
                color: #444;
                text-decoration: none;
                font-size: 14px;
                font-weight: 600;
                transition: all 0.2s;
                white-space: nowrap;
            }

            .kid-back-btn-desktop:hover {
                background: #f8f9fa;
                border-color: #c0c0c0;
            }

            .kid-back-btn-mobile-top,
            .kid-back-btn-mobile-bottom {
                display: none;
            }

            @media (max-width: 768px) {
                .kid-back-btn-desktop {
                    display: none;
                }

                .kid-back-btn-mobile-top {
                    display: block;
                    padding: 12px 16px;
                    background: transparent;
                    border: 1px solid #e0e0e0;
                    border-radius: 8px;
                    color: #666;
                    text-decoration: none;
                    font-size: 13px;
                    font-weight: 500;
                    text-align: center;
                    margin-bottom: 20px;
                    transition: all 0.2s;
                    width: 42%;
                    min-width: 135px;
                    margin-top: -10px;
                }

                .kid-back-btn-mobile-top:hover {
                    background: #f8f9fa;
                }

                .kid-back-btn-mobile-bottom {
                    display: block;
                    padding: 12px 16px;
                    background: transparent;
                    border: 1px solid #e0e0e0;
                    border-radius: 8px;
                    color: #666;
                    text-decoration: none;
                    font-size: 13px;
                    font-weight: 500;
                    text-align: center;
                    margin-top: 20px;
                    margin-bottom: 20px;
                    transition: all 0.2s;
                }

                .kid-back-btn-mobile-bottom:hover {
                    background: #f8f9fa;
                }
            }
        </style>

        <script>

            // Count-up animation for stat numbers
            function animateCounter(element) {
                const target = parseInt(element.getAttribute('data-target'));
                const duration = 1500; // 1.5 seconds
                const frameDuration = 1000 / 60; // 60 FPS
                const totalFrames = Math.round(duration / frameDuration);
                let frame = 0;

                const counter = setInterval(() => {
                    frame++;
                    const progress = frame / totalFrames;
                    const easeOutQuart = 1 - Math.pow(1 - progress, 4); // Easing function
                    const currentCount = Math.round(easeOutQuart * target);

                    element.textContent = currentCount;

                    if (frame === totalFrames) {
                        clearInterval(counter);
                        element.textContent = target; // Ensure final value is exact
                    }
                }, frameDuration);
            }

            // Intersection Observer to trigger animation when scrolled into view
            const observerOptions = {
                threshold: 0.3,
                rootMargin: '0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                        entry.target.classList.add('animated');
                        animateCounter(entry.target);
                    }
                });
            }, observerOptions);

            // Observe all stat badge numbers
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.stat-badge-number, .points-current-number').forEach(el => {
                    observer.observe(el);
                });
            });

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