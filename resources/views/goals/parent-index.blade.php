@extends('layouts.parent')

@section('title', $kid->name . '\'s Goals - AllowanceLab')

@section('content')
    @php
        // Convert hex to RGB for theming
        $hex = ltrim($kid->color, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        // Light background
        $lightR = round($r * 0.15 + 255 * 0.85);
        $lightG = round($g * 0.15 + 255 * 0.85);
        $lightB = round($b * 0.15 + 255 * 0.85);
        $lightShade = "rgb($lightR, $lightG, $lightB)";
    @endphp

    <style>
        .goals-header::after {
            background-color: {{ $kid->color }} !important;
        }

        .goal-card {
            border-left-color: {{ $kid->color }} !important;
        }

        .goal-card:hover {
            box-shadow: 0 4px 16px rgba({{ $r }}, {{ $g }}, {{ $b }}, 0.15) !important;
        }

        .goal-progress-ring {
            stroke: {{ $kid->color }} !important;
        }

        .goal-beaker-fill {
            background: linear-gradient(to top, {{ $kid->color }} 0%, color-mix(in srgb, {{ $kid->color }} 70%, white) 100%) !important;
        }

        .btn-add-goal {
            background: {{ $kid->color }} !important;
        }

        .btn-add-goal:hover {
            background: color-mix(in srgb, {{ $kid->color }} 85%, black) !important;
        }

        .goal-status-badge.ready {
            background: {{ $lightShade }} !important;
            color: {{ $kid->color }} !important;
            border: 1px solid {{ $kid->color }} !important;
        }

        .goal-btn-view {
            background: {{ $kid->color }} !important;
        }

        .goal-btn-view:hover {
            background: color-mix(in srgb, {{ $kid->color }} 85%, black) !important;
        }

        .kid-info-card {
            border-left-color: {{ $kid->color }} !important;
            background: {{ $lightShade }} !important;
        }

        .kid-avatar {
            background: {{ $kid->color }} !important;
        }
    </style>

    <!-- Kid Info Header -->
    <div class="kid-info-card">
        <div class="kid-avatar">{{ strtoupper(substr($kid->name, 0, 1)) }}</div>
        <div class="kid-details">
            <h2 class="kid-name">{{ $kid->name }}'s Goals</h2>
            <div class="kid-balance">Current Balance: ${{ number_format($kid->balance, 2) }}</div>
        </div>
        <a href="{{ route('dashboard') }}" class="btn-back-dashboard">← Back to Dashboard</a>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <!-- Header with Add Button -->
    <div class="goals-header">
        <h2 class="goals-section-title">Active Goals</h2>
        <button class="btn-add-goal" onclick="openCreateGoalModal()">+ Create Goal for {{ $kid->name }}</button>
    </div>

    <!-- Active Goals Grid -->
    @if($activeGoals->count() > 0)
        <div class="goals-grid">
            @foreach($activeGoals as $goal)
                @php
                    $progress = $goal->target_amount > 0 ? ($goal->current_amount / $goal->target_amount) * 100 : 0;
                    $progress = min($progress, 100);
                    $circumference = 2 * pi() * 54;
                    $offset = $circumference - ($progress / 100) * $circumference;
                @endphp

                <div class="goal-card">
                    <!-- Photo or Placeholder -->
                    <div class="goal-photo">
                        @if($goal->photo_path)
                            <img src="{{ asset('storage/' . $goal->photo_path) }}" alt="{{ $goal->title }}">
                        @else
                            <div class="goal-photo-placeholder">
                                <i class="fas fa-bullseye"></i>
                            </div>
                        @endif
                    </div>

                    <!-- Goal Info -->
                    <div class="goal-info">
                        <h3 class="goal-title">{{ $goal->title }}</h3>
                        @if($goal->description)
                            <p class="goal-description">{{ Str::limit($goal->description, 80) }}</p>
                        @endif

                        <!-- Progress Ring with Beaker -->
                        <div class="goal-progress-container">
                            <svg class="goal-progress-svg" viewBox="0 0 120 120">
                                <!-- Background circle -->
                                <circle cx="60" cy="60" r="54" fill="none" stroke="#e5e7eb" stroke-width="8"/>
                                <!-- Progress circle -->
                                <circle
                                    class="goal-progress-ring"
                                    cx="60" cy="60" r="54"
                                    fill="none"
                                    stroke-width="8"
                                    stroke-dasharray="{{ $circumference }}"
                                    stroke-dashoffset="{{ $offset }}"
                                    transform="rotate(-90 60 60)"
                                    stroke-linecap="round"
                                />

                                <!-- Beaker visualization -->
                                <g transform="translate(60, 60)">
                                    <!-- Beaker outline -->
                                    <path d="M -15,-20 L -12,-20 L -12,15 Q -12,22 0,22 Q 12,22 12,15 L 12,-20 L 15,-20"
                                          fill="none" stroke="#374151" stroke-width="1.5"/>
                                    <!-- Beaker fill -->
                                    <defs>
                                        <clipPath id="beaker-clip-{{ $goal->id }}">
                                            <path d="M -12,-20 L -12,15 Q -12,22 0,22 Q 12,22 12,15 L 12,-20 Z"/>
                                        </clipPath>
                                    </defs>
                                    <rect
                                        class="goal-beaker-fill"
                                        x="-12"
                                        y="{{ -20 + (42 * (1 - $progress / 100)) }}"
                                        width="24"
                                        height="{{ 42 * ($progress / 100) }}"
                                        clip-path="url(#beaker-clip-{{ $goal->id }})"
                                    />
                                </g>
                            </svg>
                            <div class="goal-progress-text">{{ number_format($progress, 0) }}%</div>
                        </div>

                        <!-- Amounts -->
                        <div class="goal-amounts">
                            <div class="goal-current">${{ number_format($goal->current_amount, 2) }}</div>
                            <div class="goal-target">of ${{ number_format($goal->target_amount, 2) }}</div>
                        </div>

                        <!-- Status Badge -->
                        @if($goal->status === 'ready_to_redeem')
                            <div class="goal-status-badge ready">
                                <i class="fas fa-check-circle"></i> Ready to Redeem!
                            </div>
                        @elseif($goal->status === 'pending_redemption')
                            <div class="goal-status-badge pending">
                                <i class="fas fa-hourglass-half"></i> {{ $kid->name }} Requested Redemption
                            </div>
                        @endif

                        <!-- Expected Completion -->
                        @if($goal->expected_completion_date && $goal->status === 'active')
                            <div class="goal-expected-date">
                                <i class="fas fa-calendar"></i> Expected: {{ $goal->expected_completion_date->format('M j, Y') }}
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="goal-card-actions">
                            <a href="{{ route('parent.goals.show', $goal) }}" class="goal-btn-view">View Details</a>
                            @if($goal->status === 'ready_to_redeem')
                                <form action="{{ route('parent.goals.redeem', $goal) }}" method="POST" onsubmit="return confirm('Redeem this goal? Funds will be returned to {{ $kid->name }}\'s main balance.');">
                                    @csrf
                                    <button type="submit" class="goal-btn-redeem">
                                        <i class="fas fa-gift"></i> Redeem
                                    </button>
                                </form>
                            @elseif($goal->status === 'pending_redemption')
                                <div class="goal-redemption-actions">
                                    <form action="{{ route('parent.goals.approve-redemption', $goal) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="goal-btn-approve" onclick="return confirm('Approve redemption? Funds will be returned to {{ $kid->name }}\'s main balance.');">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('parent.goals.deny-redemption', $goal) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="goal-btn-deny" onclick="return confirm('Deny redemption? Goal will remain active for {{ $kid->name }}.');">
                                            <i class="fas fa-times"></i> Deny
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="goals-empty-state">
            <i class="fas fa-bullseye"></i>
            <p>{{ $kid->name }} hasn't created any goals yet!</p>
            <button class="btn-add-goal" onclick="openCreateGoalModal()">Create First Goal</button>
        </div>
    @endif

    <!-- Past Goals Section -->
    @if($pastGoals->count() > 0)
        <div class="goals-header" style="margin-top: 48px;">
            <h2 class="goals-section-title">Past Goals</h2>
        </div>

        <div class="goals-grid">
            @foreach($pastGoals as $goal)
                <div class="goal-card goal-card-redeemed">
                    <!-- Photo or Placeholder -->
                    <div class="goal-photo">
                        @if($goal->photo_path)
                            <img src="{{ asset('storage/' . $goal->photo_path) }}" alt="{{ $goal->title }}">
                        @else
                            <div class="goal-photo-placeholder">
                                <i class="fas fa-trophy"></i>
                            </div>
                        @endif
                        <div class="goal-redeemed-overlay">
                            <i class="fas fa-check-circle"></i> Redeemed
                        </div>
                    </div>

                    <!-- Goal Info -->
                    <div class="goal-info">
                        <h3 class="goal-title">{{ $goal->title }}</h3>
                        <div class="goal-redeemed-date">
                            Redeemed on {{ $goal->redeemed_at->format('M j, Y') }}
                        </div>
                        <div class="goal-amounts">
                            <div class="goal-target">Goal: ${{ number_format($goal->target_amount, 2) }}</div>
                        </div>
                        <a href="{{ route('parent.goals.show', $goal) }}" class="goal-btn-view-past">View Details</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Create Goal Modal Placeholder -->
    <div class="modal" id="createGoalModal" style="display: none;">
        <div class="modal-backdrop" onclick="closeCreateGoalModal()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Create Goal for {{ $kid->name }}</h3>
                <button class="modal-close" onclick="closeCreateGoalModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form action="{{ route('parent.goals.store', $kid) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <p style="text-align: center; color: #6b7280; margin-bottom: 16px;">
                        Full goal creation form would go here. For now, please use the kid's account to create goals.
                    </p>
                    <button type="button" class="btn-modal-close" onclick="closeCreateGoalModal()">Close</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openCreateGoalModal() {
            document.getElementById('createGoalModal').style.display = 'flex';
        }

        function closeCreateGoalModal() {
            document.getElementById('createGoalModal').style.display = 'none';
        }
    </script>

@endsection

<style>
    .kid-info-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px;
        background: white;
        border-radius: 12px;
        border-left: 4px solid;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 32px;
    }

    .kid-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 28px;
        font-weight: 700;
    }

    .kid-details {
        flex: 1;
    }

    .kid-name {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 4px;
    }

    .kid-balance {
        font-size: 16px;
        color: #6b7280;
    }

    .btn-back-dashboard {
        padding: 10px 20px;
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        color: #6b7280;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-back-dashboard:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
    }

    .goals-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 12px;
        border-bottom: 3px solid #e5e7eb;
        position: relative;
    }

    .goals-header::after {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 0;
        width: 80px;
        height: 3px;
    }

    .goals-section-title {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
    }

    .btn-add-goal {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        color: white;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        font-size: 14px;
    }

    .goals-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .goal-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-left: 4px solid;
        transition: all 0.3s;
    }

    .goal-card:hover {
        transform: translateY(-4px);
    }

    .goal-photo {
        width: 100%;
        height: 180px;
        background: #f3f4f6;
        position: relative;
        overflow: hidden;
    }

    .goal-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .goal-photo-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        color: #9ca3af;
    }

    .goal-info {
        padding: 20px;
    }

    .goal-title {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 8px;
    }

    .goal-description {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 16px;
        line-height: 1.5;
    }

    .goal-progress-container {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 20px auto;
    }

    .goal-progress-svg {
        width: 100%;
        height: 100%;
    }

    .goal-progress-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 20px;
        font-weight: 700;
        color: #1f2937;
        margin-top: 20px;
    }

    .goal-amounts {
        text-align: center;
        margin: 16px 0;
    }

    .goal-current {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
    }

    .goal-target {
        font-size: 14px;
        color: #6b7280;
    }

    .goal-status-badge {
        padding: 8px 12px;
        border-radius: 6px;
        text-align: center;
        font-weight: 600;
        font-size: 14px;
        margin: 12px 0;
    }

    .goal-expected-date {
        text-align: center;
        font-size: 13px;
        color: #6b7280;
        margin: 12px 0;
    }

    .goal-card-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 16px;
    }

    .goal-btn-view,
    .goal-btn-redeem {
        display: block;
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        text-align: center;
        color: white;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .goal-btn-redeem {
        background: #10b981;
    }

    .goal-btn-redeem:hover {
        background: #059669;
    }

    .goal-btn-view-past {
        display: block;
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        text-align: center;
        color: #6b7280;
        font-weight: 600;
        text-decoration: none;
        margin-top: 16px;
        border: 2px solid #e5e7eb;
        background: white;
        transition: all 0.2s;
    }

    .goal-btn-view-past:hover {
        background: #f3f4f6;
    }

    .goals-empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .goals-empty-state i {
        font-size: 64px;
        color: #d1d5db;
        margin-bottom: 16px;
    }

    .goals-empty-state p {
        font-size: 18px;
        color: #6b7280;
        margin-bottom: 24px;
    }

    .goal-card-redeemed {
        opacity: 0.85;
    }

    .goal-redeemed-overlay {
        position: absolute;
        top: 0;
        right: 0;
        background: #10b981;
        color: white;
        padding: 8px 16px;
        font-weight: 600;
        font-size: 14px;
        border-radius: 0 0 0 8px;
    }

    .goal-redeemed-date {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 12px;
    }

    .alert {
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 24px;
        font-weight: 600;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #10b981;
    }

    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #ef4444;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal-backdrop {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        position: relative;
        background: white;
        border-radius: 12px;
        padding: 32px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .modal-header h3 {
        font-size: 20px;
        font-weight: 700;
        color: #1f2937;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 32px;
        color: #9ca3af;
        cursor: pointer;
        line-height: 1;
    }

    .modal-close:hover {
        color: #6b7280;
    }

    .btn-modal-close {
        width: 100%;
        padding: 12px;
        background: #6b7280;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 16px;
    }

    .btn-modal-close:hover {
        background: #4b5563;
    }

    @media (max-width: 768px) {
        .goals-grid {
            grid-template-columns: 1fr;
        }

        .goals-header {
            flex-direction: column;
            gap: 12px;
            align-items: flex-start;
        }

        .btn-add-goal {
            width: 100%;
            text-align: center;
        }

        .kid-info-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .btn-back-dashboard {
            width: 100%;
            text-align: center;
        }
    }
</style>
