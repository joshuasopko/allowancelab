@extends($isParent ? 'layouts.parent' : 'layouts.kid')

@section('title', $goal->title . ' - Goal Details')

@section('content')
    @php
        $kid = $goal->kid;
        $progress = $goal->target_amount > 0 ? ($goal->current_amount / $goal->target_amount) * 100 : 0;
        $progress = min($progress, 100);
        $isComplete = $progress >= 100;

        // Denial / cooldown state
        $hasDenial = $goal->denied_at && $goal->denial_reason;
        $denialAcknowledged = (bool) $goal->denial_acknowledged_at;
        $showDenialBanner = $hasDenial && !$denialAcknowledged;
        $hoursElapsedSinceDenial = $hasDenial && $denialAcknowledged ? $goal->denied_at->diffInHours(now()) : 0;
        $cooldownHoursLeft = (int) ceil(max(0, 24 - $hoursElapsedSinceDenial));
        $cooldownActive = $hasDenial && $denialAcknowledged && $cooldownHoursLeft > 0;

        // Convert hex to RGB for theming
        $hex = ltrim($kid->color, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    @endphp

    <style>
        .goal-detail-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .goal-detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .goal-detail-title {
            font-size: 32px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .btn-back {
            padding: 10px 20px;
            background: #6b7280;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-back:hover {
            background: #4b5563;
        }

        .btn-edit {
            padding: 10px 20px;
            background: {{ $kid->color }};
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-edit:hover {
            background: color-mix(in srgb, {{ $kid->color }} 85%, black);
        }

        .goal-detail-card {
            background: white;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 16px rgba({{ $r }}, {{ $g }}, {{ $b }}, 0.50);
            margin-bottom: 24px;
        }

        .goal-detail-top {
            display: flex;
            gap: 24px;
            margin-bottom: 32px;
        }

        .goal-detail-photo {
            width: 150px;
            height: 150px;
            border-radius: 12px;
            object-fit: cover;
            flex-shrink: 0;
            background: #f3f4f6;
        }

        .goal-detail-photo-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 12px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 48px;
            flex-shrink: 0;
        }

        .goal-detail-info {
            flex: 1;
        }

        .goal-detail-info h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 12px 0;
        }

        .goal-detail-description {
            font-size: 16px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .goal-detail-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #ec4899;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            margin-top: 12px;
        }

        .goal-detail-link:hover {
            text-decoration: underline;
        }

        .goal-detail-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 12px;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            margin-top: 12px;
        }

        .goal-detail-status.complete {
            background: #d1fae5;
            color: #065f46;
        }

        .goal-detail-status.active {
            background: #e0e7ff;
            color: #3730a3;
        }

        .goal-detail-status.pending {
            background: #fef3c7;
            color: #92400e;
        }

        .goal-detail-progress {
            padding: 24px;
            background: #f9fafb;
            border-radius: 12px;
            margin-bottom: 32px;
        }

        .goal-detail-amounts {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 16px;
        }

        .goal-current {
            font-size: 36px;
            font-weight: 700;
            color: {{ $isComplete ? '#10b981' : $kid->color }};
        }

        .goal-target {
            font-size: 20px;
            font-weight: 600;
            color: #6b7280;
        }

        .goal-progress-bar-container {
            height: 18px;
            background: #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 12px;
        }

        .goal-progress-bar-fill {
            height: 100%;
            background: {{ $isComplete ? '#10b981' : $kid->color }};
            border-radius: 12px;
            transition: width 0.3s ease;
        }

        .goal-progress-percent {
            font-size: 13px;
            font-weight: 700;
            color: {{ $isComplete ? '#10b981' : $kid->color }};
        }

        .goal-remaining {
            font-size: 14px;
            color: #6b7280;
            font-weight: 600;
        }

        .goal-redeem-cta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 14px;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
        }
        .goal-redeem-cta.btn {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(16,185,129,0.35);
            transition: opacity 0.15s, transform 0.1s;
            width: 100%;
            justify-content: center;
        }
        .goal-redeem-cta.btn:hover { opacity: 0.9; transform: translateY(-1px); }
        .goal-redeem-cta.btn:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
        .goal-redeem-cta.pending {
            background: #fef3c7;
            color: #92400e;
            font-size: 14px;
        }

        .goal-detail-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            padding: 20px;
            background: #f9fafb;
            border-radius: 12px;
            margin-bottom: 32px;
        }

        .goal-meta-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .goal-meta-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
        }

        .goal-meta-value {
            font-size: 16px;
            color: #1f2937;
            font-weight: 600;
        }

        /* Add/Remove Funds Section */
        .goal-fund-actions {
            border-top: 1px solid #f3f4f6;
            padding-top: 24px;
            margin-top: 8px;
        }

        .goal-fund-buttons {
            display: flex;
            gap: 12px;
        }

        .btn-fund-add {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: #d1fae5;
            color: #065f46;
            border: 2px solid #a7f3d0;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-fund-add:hover, .btn-fund-add.active {
            background: #10b981;
            color: white;
            border-color: #10b981;
        }

        .btn-fund-remove {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: #fee2e2;
            color: #991b1b;
            border: 2px solid #fca5a5;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-fund-remove:hover, .btn-fund-remove.active {
            background: #ef4444;
            color: white;
            border-color: #ef4444;
        }

        .goal-fund-form-wrap {
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            transition: max-height 0.35s ease, opacity 0.25s ease, margin-top 0.25s ease;
            margin-top: 0;
        }
        .goal-fund-form-wrap.open {
            max-height: 200px;
            opacity: 1;
            margin-top: 16px;
        }

        .goal-fund-form-inner {
            background: #f9fafb;
            border-radius: 10px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .goal-fund-form-inner label {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            white-space: nowrap;
        }

        .goal-fund-input {
            width: 130px;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 7px;
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            outline: none;
            transition: border-color 0.2s;
        }
        .goal-fund-input:focus {
            border-color: {{ $kid->color }};
        }

        .goal-fund-note {
            flex: 1;
            min-width: 160px;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 7px;
            font-size: 14px;
            color: #374151;
            outline: none;
            transition: border-color 0.2s;
        }
        .goal-fund-note:focus {
            border-color: {{ $kid->color }};
        }

        .btn-fund-submit {
            padding: 8px 20px;
            background: {{ $kid->color }};
            color: white;
            border: none;
            border-radius: 7px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: opacity 0.15s;
            white-space: nowrap;
        }
        .btn-fund-submit:hover { opacity: 0.85; }
        .btn-fund-submit:disabled { opacity: 0.6; cursor: not-allowed; }

        .goal-fund-cancel {
            background: transparent;
            border: none;
            color: #9ca3af;
            font-size: 13px;
            cursor: pointer;
            padding: 4px 8px;
            transition: color 0.2s;
        }
        .goal-fund-cancel:hover { color: #374151; }

        .goal-transactions-section {
            margin-top: 32px;
        }

        .goal-transactions-header {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 16px;
        }

        .goal-transaction-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            background: white;
            border-left: 4px solid {{ $kid->color }};
            border-radius: 8px;
            margin-bottom: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .goal-transaction-left {
            flex: 1;
        }

        .goal-transaction-description {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .goal-transaction-meta {
            font-size: 13px;
            color: #6b7280;
        }

        .goal-transaction-amount {
            font-size: 18px;
            font-weight: 700;
            color: #10b981;
        }

        .goal-transaction-amount.withdrawal {
            color: #ef4444;
        }

        .goal-empty-transactions {
            text-align: center;
            padding: 48px;
            color: #9ca3af;
        }

        .goal-empty-transactions i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        /* Pagination */
        .goal-tx-count-bar {
            font-size: 12px;
            color: #9ca3af;
            margin-bottom: 12px;
            min-height: 16px;
        }

        .goal-tx-pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .goal-tx-page-btn {
            padding: 8px 18px;
            border-radius: 8px;
            border: 1.5px solid #e5e7eb;
            background: white;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            cursor: pointer;
            transition: all 0.15s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .goal-tx-page-btn:hover {
            border-color: {{ $kid->color }};
            color: {{ $kid->color }};
        }

        .goal-tx-page-btn.disabled {
            opacity: 0.35;
            cursor: default;
            pointer-events: none;
        }

        .goal-tx-page-info {
            font-size: 13px;
            color: #6b7280;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .goal-detail-top {
                flex-direction: column;
            }

            .goal-detail-amounts {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>

    <div class="goal-detail-container">
        <!-- Header -->
        <div class="goal-detail-header">
            <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                <h1 class="goal-detail-title">
                    @if($isParent){{ $kid->name }}@else Goal Details @endif
                </h1>
                @if($isParent)
                    <div style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: white; border: 2px solid {{ $kid->color }}33; border-radius: 999px; font-size: 15px; font-weight: 600; color: {{ $kid->color }};">
                        <i class="fas fa-wallet" style="font-size: 14px;"></i>
                        ${{ number_format($kid->balance, 2) }} available
                    </div>
                @endif
            </div>
            <div style="display: flex; gap: 12px;">
                @if(!in_array($goal->status, ['pending_redemption', 'redeemed']))
                    @if($isParent)
                        <button onclick="openEditGoalModal({{ $goal->id }})" class="btn-edit">
                            <i class="fas fa-edit"></i> Edit Goal
                        </button>
                    @else
                        <a href="{{ route('kid.dashboard') }}?tab=goals&edit_goal={{ $goal->id }}" class="btn-edit">
                            <i class="fas fa-edit"></i> Edit Goal
                        </a>
                    @endif
                @endif
                <a href="{{ $isParent ? route('kids.goals', $kid) : route('kid.dashboard') . '?tab=goals' }}" class="btn-back">
                    ← Back to Goals
                </a>
            </div>
        </div>

        <!-- Goal Card -->
        <div class="goal-detail-card">
            <div class="goal-detail-top">
                @if($goal->photo_path)
                    <img src="{{ \Storage::url($goal->photo_path) }}" alt="{{ $goal->title }}" class="goal-detail-photo">
                @else
                    <div class="goal-detail-photo-placeholder">
                        <i class="fas fa-bullseye"></i>
                    </div>
                @endif

                <div class="goal-detail-info">
                    <h2>{{ $goal->title }}</h2>
                    @if($goal->description)
                        <p class="goal-detail-description">{{ $goal->description }}</p>
                    @endif

                    <div>
                        @if($goal->status === 'pending_redemption')
                            <div class="goal-detail-status pending">
                                <i class="fas fa-clock"></i> Pending Parent Approval
                            </div>
                        @elseif($goal->status === 'ready_to_redeem' || $isComplete)
                            <div class="goal-detail-status complete">
                                <i class="fas fa-check-circle"></i> Goal Complete!
                            </div>
                        @else
                            <div class="goal-detail-status active">
                                <i class="fas fa-bullseye"></i> Active Goal
                            </div>
                        @endif
                    </div>

                    @if($goal->denial_reason && $goal->denied_at)
                        <div style="background:#fef2f2; border:1px solid #fca5a5; border-radius:10px; padding:12px 14px; margin-top:12px;">
                            <div style="font-size:13px; font-weight:700; color:#dc2626; margin-bottom:4px;">
                                <i class="fas fa-ban"></i> Fulfillment Denied
                                <span style="font-weight:400; color:#9ca3af; font-size:12px; margin-left:6px;">{{ $goal->denied_at->format('M j, Y') }}</span>
                            </div>
                            <div style="font-size:13px; color:#7f1d1d;">{{ $goal->denial_reason }}</div>
                        </div>
                    @endif

                    @if($goal->product_url)
                        <div>
                            <a href="{{ $goal->product_url }}" target="_blank" class="goal-detail-link">
                                <i class="fas fa-external-link-alt"></i> View on {{ parse_url($goal->product_url, PHP_URL_HOST) }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Progress Section -->
            <div class="goal-detail-progress">
                <div class="goal-detail-amounts">
                    <span class="goal-current">${{ number_format($goal->current_amount, 2) }}</span>
                    <div style="display: flex; align-items: baseline; gap: 8px;">
                        <span class="goal-progress-percent">{{ number_format($progress, 0) }}%</span>
                        <span class="goal-target">of ${{ number_format($goal->target_amount, 2) }}</span>
                    </div>
                </div>

                <div class="goal-progress-bar-container">
                    <div class="goal-progress-bar-fill" style="width: {{ $progress }}%;"></div>
                </div>

                @if(!$isComplete)
                    <div class="goal-remaining">
                        ${{ number_format($goal->target_amount - $goal->current_amount, 2) }} remaining to complete
                    </div>
                @else
                    <div class="goal-remaining" style="color: #10b981;">
                        <i class="fas fa-check-circle"></i> Goal completed!
                    </div>
                    @if(!$isParent)
                        @if($goal->status === 'pending_redemption')
                            <div class="goal-redeem-cta pending">
                                <i class="fas fa-clock"></i> Awaiting parent approval
                            </div>
                        @elseif($showDenialBanner)
                            {{-- Denial banner with "I Understand" button --}}
                            <div id="goalDenialBanner" style="background:#fef2f2; border:1px solid #fca5a5; border-radius:10px; padding:14px 16px; margin-top:14px;">
                                <div style="font-size:14px; font-weight:700; color:#dc2626; margin-bottom:4px;">
                                    <i class="fas fa-ban"></i> Parent Denied Fulfillment
                                </div>
                                <div style="font-size:14px; color:#7f1d1d; margin-bottom:12px;">{{ $goal->denial_reason }}</div>
                                <button id="goalAcknowledgeBtn" onclick="acknowledgeDenial()" style="width:100%; padding:10px 16px; background:#dc2626; color:white; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer;">
                                    <i class="fas fa-check"></i> I Understand
                                </button>
                            </div>
                            <div id="goalCooldownNotice" style="display:none; background:#fef3c7; border:1px solid #fcd34d; border-radius:10px; padding:12px 16px; margin-top:14px; text-align:center; font-size:14px; font-weight:600; color:#92400e;">
                                <i class="fas fa-clock"></i> You can ask again in <span id="cooldownHoursDisplay">{{ $cooldownHoursLeft }}</span> hour{{ $cooldownHoursLeft !== 1 ? 's' : '' }}
                            </div>
                            <div id="goalRedeemBtnWrap" style="display:none;">
                                <button class="goal-redeem-cta btn" id="goalRedeemBtn" onclick="requestRedemption()" style="margin-top:14px;">
                                    <i class="fas fa-gift"></i> Ask Parent to Fulfill! 🎉
                                </button>
                            </div>
                        @elseif($cooldownActive)
                            {{-- Cooldown active after acknowledging --}}
                            <div style="background:#fef3c7; border:1px solid #fcd34d; border-radius:10px; padding:12px 16px; margin-top:14px; text-align:center; font-size:14px; font-weight:600; color:#92400e;">
                                <i class="fas fa-clock"></i> You can ask again in {{ $cooldownHoursLeft }} hour{{ $cooldownHoursLeft !== 1 ? 's' : '' }}
                            </div>
                        @else
                            <button class="goal-redeem-cta btn" id="goalRedeemBtn" onclick="requestRedemption()" style="margin-top:14px;">
                                <i class="fas fa-gift"></i> Ask Parent to Fulfill! 🎉
                            </button>
                        @endif
                    @endif
                @endif
            </div>

            <!-- Meta Information -->
            <div class="goal-detail-meta">
                <div class="goal-meta-item">
                    <span class="goal-meta-label">Auto-Allocation</span>
                    <span class="goal-meta-value">{{ number_format($goal->auto_allocation_percentage, 0) }}% of allowance</span>
                </div>
                <div class="goal-meta-item">
                    <span class="goal-meta-label">Expected Completion</span>
                    <span class="goal-meta-value">{{ $goal->expected_completion_date ? $goal->expected_completion_date->format('M j, Y') : 'N/A' }}</span>
                </div>
                <div class="goal-meta-item">
                    <span class="goal-meta-label">Created</span>
                    <span class="goal-meta-value">{{ $goal->created_at->format('M j, Y') }}</span>
                </div>
                @if($goal->redeemed_at)
                    <div class="goal-meta-item">
                        <span class="goal-meta-label">Redeemed</span>
                        <span class="goal-meta-value">{{ $goal->redeemed_at->format('M j, Y') }}</span>
                    </div>
                @endif
            </div>

            @if(!$isParent && $goal->status !== 'redeemed')
                <!-- Kid: Add/Remove Funds -->
                <div class="goal-fund-actions" id="kidGoalFundActions">
                    <div class="goal-fund-buttons">
                        @if(!$isComplete)
                            <button class="btn-fund-add" id="kidBtnAddFunds" onclick="kidToggleFundForm('add')">
                                <i class="fas fa-plus-circle"></i> Add Funds
                            </button>
                        @endif
                        <button class="btn-fund-remove" id="kidBtnRemoveFunds" onclick="kidToggleFundForm('remove')">
                            <i class="fas fa-minus-circle"></i> Remove Funds
                        </button>
                    </div>

                    <!-- Add Funds Form -->
                    <div class="goal-fund-form-wrap" id="kidAddFundsWrap">
                        <div class="goal-fund-form-inner">
                            <label><i class="fas fa-plus-circle" style="color:#10b981;"></i> Amount</label>
                            <div style="display:flex;flex-direction:column;gap:4px;flex:1;">
                                <input type="text" id="kidAddAmount" class="goal-fund-input" placeholder="$0.00" inputmode="numeric" oninput="kidFormatFundInput(this,'add')" onkeydown="if(event.key==='Enter') kidSubmitFunds('add')">
                                <span id="kidAddHint" style="font-size:12px;color:#6b7280;display:none;">Balance: ${{ number_format($kid->balance, 2) }}</span>
                            </div>
                            <button class="btn-fund-submit" id="kidBtnSubmitAdd" onclick="kidSubmitFunds('add')">Add</button>
                            <button class="goal-fund-cancel" onclick="kidToggleFundForm(null)">Cancel</button>
                        </div>
                    </div>

                    <!-- Remove Funds Form -->
                    <div class="goal-fund-form-wrap" id="kidRemoveFundsWrap">
                        <div class="goal-fund-form-inner">
                            <label><i class="fas fa-minus-circle" style="color:#ef4444;"></i> Amount</label>
                            <div style="display:flex;flex-direction:column;gap:4px;flex:1;">
                                <input type="text" id="kidRemoveAmount" class="goal-fund-input" placeholder="$0.00" inputmode="numeric" oninput="kidFormatFundInput(this,'remove')" onkeydown="if(event.key==='Enter') kidSubmitFunds('remove')">
                                <span id="kidRemoveHint" style="font-size:12px;color:#6b7280;display:none;">Saved: ${{ number_format($goal->current_amount, 2) }}</span>
                            </div>
                            <button class="btn-fund-submit" id="kidBtnSubmitRemove" onclick="kidSubmitFunds('remove')" style="background:#ef4444;">Remove</button>
                            <button class="goal-fund-cancel" onclick="kidToggleFundForm(null)">Cancel</button>
                        </div>
                    </div>
                </div>
                <script>
                    let kidActiveFundForm = null;
                    const KID_BALANCE = {{ $kid->balance }};
                    const GOAL_SAVED = {{ $goal->current_amount }};
                    const GOAL_REMAINING = {{ max(0, $goal->target_amount - $goal->current_amount) }};

                    function kidFormatFundInput(el, type) {
                        let digits = el.value.replace(/\D/g, '');
                        if (!digits) { el.value = ''; delete el.dataset.cents; return; }
                        let cents = parseInt(digits, 10);
                        let dollars = (cents / 100).toFixed(2);
                        el.value = '$' + parseFloat(dollars).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        el.dataset.cents = cents;

                        // Show contextual hint
                        const hintEl = document.getElementById(type === 'add' ? 'kidAddHint' : 'kidRemoveHint');
                        if (hintEl) {
                            const amount = cents / 100;
                            if (type === 'add') {
                                const newBalance = KID_BALANCE - amount;
                                if (amount > KID_BALANCE) {
                                    hintEl.textContent = 'Insufficient balance';
                                    hintEl.style.color = '#ef4444';
                                } else if (amount > GOAL_REMAINING) {
                                    hintEl.textContent = 'Max you can add: $' + GOAL_REMAINING.toFixed(2);
                                    hintEl.style.color = '#f59e0b';
                                } else {
                                    hintEl.textContent = '$' + newBalance.toFixed(2) + ' remaining in balance';
                                    hintEl.style.color = '#6b7280';
                                }
                            } else {
                                const newGoal = GOAL_SAVED - amount;
                                if (amount > GOAL_SAVED) {
                                    hintEl.textContent = 'Max you can remove: $' + GOAL_SAVED.toFixed(2);
                                    hintEl.style.color = '#ef4444';
                                } else {
                                    hintEl.textContent = '$' + newGoal.toFixed(2) + ' will remain in goal';
                                    hintEl.style.color = '#6b7280';
                                }
                            }
                            hintEl.style.display = 'block';
                        }
                    }

                    function kidToggleFundForm(type) {
                        const addWrap = document.getElementById('kidAddFundsWrap');
                        const removeWrap = document.getElementById('kidRemoveFundsWrap');
                        const btnAdd = document.getElementById('kidBtnAddFunds');
                        const btnRemove = document.getElementById('kidBtnRemoveFunds');

                        if (kidActiveFundForm === type || type === null) {
                            if (addWrap) addWrap.classList.remove('open');
                            if (removeWrap) removeWrap.classList.remove('open');
                            if (btnAdd) btnAdd.classList.remove('active');
                            if (btnRemove) btnRemove.classList.remove('active');
                            ['kidAddAmount','kidRemoveAmount'].forEach(id => {
                                const el = document.getElementById(id);
                                if (el) { el.value = ''; delete el.dataset.cents; el.style.borderColor = ''; }
                            });
                            ['kidAddHint','kidRemoveHint'].forEach(id => {
                                const el = document.getElementById(id);
                                if (el) el.style.display = 'none';
                            });
                            kidActiveFundForm = null;
                        } else {
                            if (addWrap) addWrap.classList.toggle('open', type === 'add');
                            if (removeWrap) removeWrap.classList.toggle('open', type === 'remove');
                            if (btnAdd) btnAdd.classList.toggle('active', type === 'add');
                            if (btnRemove) btnRemove.classList.toggle('active', type === 'remove');
                            kidActiveFundForm = type;
                            setTimeout(() => {
                                const inp = document.getElementById(type === 'add' ? 'kidAddAmount' : 'kidRemoveAmount');
                                if (inp) inp.focus();
                            }, 100);
                        }
                    }

                    function kidSubmitFunds(type) {
                        const amountEl = document.getElementById(type === 'add' ? 'kidAddAmount' : 'kidRemoveAmount');
                        const submitBtn = document.getElementById(type === 'add' ? 'kidBtnSubmitAdd' : 'kidBtnSubmitRemove');
                        const cents = parseInt(amountEl.dataset.cents || '0', 10);
                        const amount = cents / 100;

                        if (!cents || cents <= 0) {
                            amountEl.style.borderColor = '#ef4444';
                            amountEl.focus();
                            return;
                        }
                        amountEl.style.borderColor = '';
                        submitBtn.disabled = true;
                        submitBtn.textContent = '...';

                        const url = type === 'add'
                            ? '{{ route('kid.goals.add-funds', $goal) }}'
                            : '{{ route('kid.goals.remove-funds', $goal) }}';

                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                            },
                            body: JSON.stringify({ amount: amount })
                        })
                        .then(r => r.json())
                        .then(d => {
                            if (d.success) {
                                window.location.reload();
                            } else {
                                // Show inline error
                                const hintEl = document.getElementById(type === 'add' ? 'kidAddHint' : 'kidRemoveHint');
                                if (hintEl) { hintEl.textContent = d.message || 'Error'; hintEl.style.color = '#ef4444'; hintEl.style.display = 'block'; }
                                submitBtn.disabled = false;
                                submitBtn.textContent = type === 'add' ? 'Add' : 'Remove';
                            }
                        })
                        .catch(() => {
                            const hintEl = document.getElementById(type === 'add' ? 'kidAddHint' : 'kidRemoveHint');
                            if (hintEl) { hintEl.textContent = 'Network error. Please try again.'; hintEl.style.color = '#ef4444'; hintEl.style.display = 'block'; }
                            submitBtn.disabled = false;
                            submitBtn.textContent = type === 'add' ? 'Add' : 'Remove';
                        });
                    }
                </script>
            @endif

            @if($isParent && $goal->status !== 'redeemed')
                <!-- Parent: Add/Remove Funds + Redeem -->
                <div class="goal-fund-actions" id="goalFundActions">
                    <div class="goal-fund-buttons">
                        <button class="btn-fund-add" id="btnAddFunds" onclick="toggleFundForm('add')">
                            <i class="fas fa-plus-circle"></i> Add Funds
                        </button>
                        <button class="btn-fund-remove" id="btnRemoveFunds" onclick="toggleFundForm('remove')">
                            <i class="fas fa-minus-circle"></i> Remove Funds
                        </button>
                        @if($isComplete)
                            {{-- Hidden form submitted by modal --}}
                            <form id="redeemGoalForm" action="{{ route('parent.goals.redeem', $goal) }}" method="POST" style="display:none;">
                                @csrf
                            </form>
                            <button type="button" class="btn-fund-add" style="background:#10b981; color:white; border-color:#10b981;" onclick="openRedeemGoalModal()">
                                <i class="fas fa-gift"></i> Redeem Goal
                            </button>
                        @endif
                    </div>

                    <!-- Add Funds Form -->
                    <div class="goal-fund-form-wrap" id="addFundsWrap">
                        <div class="goal-fund-form-inner">
                            <label for="addAmount"><i class="fas fa-plus-circle" style="color:#10b981;"></i> Amount</label>
                            <input type="text" id="addAmount" class="goal-fund-input" placeholder="$0.00" inputmode="numeric" oninput="formatFundInput(this)" onkeydown="fundInputKeydown(event,'add')">
                            <input type="text" id="addNote" class="goal-fund-note" placeholder="Note (optional)">
                            <button class="btn-fund-submit" id="btnSubmitAdd" onclick="submitFunds('add')">Add</button>
                            <button class="goal-fund-cancel" onclick="toggleFundForm(null)">Cancel</button>
                        </div>
                    </div>

                    <!-- Remove Funds Form -->
                    <div class="goal-fund-form-wrap" id="removeFundsWrap">
                        <div class="goal-fund-form-inner">
                            <label for="removeAmount"><i class="fas fa-minus-circle" style="color:#ef4444;"></i> Amount</label>
                            <input type="text" id="removeAmount" class="goal-fund-input" placeholder="$0.00" inputmode="numeric" oninput="formatFundInput(this)" onkeydown="fundInputKeydown(event,'remove')">
                            <input type="text" id="removeNote" class="goal-fund-note" placeholder="Note (optional)">
                            <button class="btn-fund-submit" id="btnSubmitRemove" onclick="submitFunds('remove')">Remove</button>
                            <button class="goal-fund-cancel" onclick="toggleFundForm(null)">Cancel</button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Transaction History -->
        <div class="goal-transactions-section">
            <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:16px; flex-wrap:wrap; gap:8px;">
                <h3 class="goal-transactions-header" style="margin-bottom:0;">
                    <i class="fas fa-history"></i> Transaction History
                </h3>
                @if($totalTransactionCount > 0)
                    <span class="goal-tx-count-bar" style="margin-bottom:0;">
                        Showing {{ $goalTransactions->firstItem() }}–{{ $goalTransactions->lastItem() }} of {{ $totalTransactionCount }} {{ $totalTransactionCount === 1 ? 'transaction' : 'transactions' }}
                    </span>
                @endif
            </div>

            @if($goalTransactions->count() > 0)
                @foreach($goalTransactions as $transaction)
                    @php
                        $typeLabels = [
                            'auto_allocation'     => ['label' => 'Auto-Allocation',      'icon' => 'fa-sync-alt',      'color' => '#10b981', 'showAmount' => true,  'isCredit' => true],
                            'manual_deposit'      => ['label' => 'Manual Deposit',        'icon' => 'fa-plus-circle',   'color' => '#10b981', 'showAmount' => true,  'isCredit' => true],
                            'manual_withdrawal'   => ['label' => 'Withdrawal',            'icon' => 'fa-minus-circle',  'color' => '#ef4444', 'showAmount' => true,  'isCredit' => false],
                            'redemption'          => ['label' => 'Redeemed',              'icon' => 'fa-gift',          'color' => '#6b7280', 'showAmount' => false, 'isCredit' => false],
                            'redemption_requested'=> ['label' => 'Fulfillment Requested', 'icon' => 'fa-hand-holding',  'color' => '#f59e0b', 'showAmount' => false, 'isCredit' => null],
                            'redemption_denied'   => ['label' => 'Fulfillment Denied',    'icon' => 'fa-ban',           'color' => '#ef4444', 'showAmount' => false, 'isCredit' => null],
                        ];
                        $meta = $typeLabels[$transaction->transaction_type] ?? ['label' => ucfirst(str_replace('_', ' ', $transaction->transaction_type)), 'icon' => 'fa-circle', 'color' => '#6b7280', 'showAmount' => true, 'isCredit' => true];
                    @endphp
                    <div class="goal-transaction-item">
                        <div class="goal-transaction-left">
                            <div class="goal-transaction-description" style="display:flex; align-items:center; gap:6px;">
                                <i class="fas {{ $meta['icon'] }}" style="color:{{ $meta['color'] }}; font-size:13px; flex-shrink:0;"></i>
                                <span style="font-weight:600; color:{{ $meta['color'] }};">{{ $meta['label'] }}</span>
                                @if($transaction->description && !in_array($transaction->transaction_type, ['manual_deposit', 'auto_allocation', 'manual_withdrawal']))
                                    <span style="color:#6b7280; font-weight:400; font-size:12px;">— {{ $transaction->description }}</span>
                                @endif
                            </div>
                            <div class="goal-transaction-meta">
                                {{ $transaction->created_at->format('M j, Y g:i A') }}
                                @if($transaction->performedBy)
                                    · by {{ $transaction->performedBy->name }}
                                @endif
                            </div>
                        </div>
                        <div class="goal-transaction-amount {{ !$meta['isCredit'] && $meta['showAmount'] ? 'withdrawal' : '' }}">
                            @if($meta['showAmount'])
                                {{ $meta['isCredit'] ? '+' : '-' }}${{ number_format(abs($transaction->amount), 2) }}
                            @else
                                <span style="font-size:12px; color:#9ca3af;">—</span>
                            @endif
                        </div>
                    </div>
                @endforeach

                @if($goalTransactions->lastPage() > 1)
                    <div class="goal-tx-pagination">
                        @if($goalTransactions->onFirstPage())
                            <span class="goal-tx-page-btn disabled">
                                <i class="fas fa-chevron-left"></i> Prev
                            </span>
                        @else
                            <a href="{{ $goalTransactions->previousPageUrl() }}" class="goal-tx-page-btn">
                                <i class="fas fa-chevron-left"></i> Prev
                            </a>
                        @endif

                        <span class="goal-tx-page-info">{{ $goalTransactions->currentPage() }} of {{ $goalTransactions->lastPage() }}</span>

                        @if($goalTransactions->hasMorePages())
                            <a href="{{ $goalTransactions->nextPageUrl() }}" class="goal-tx-page-btn">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        @else
                            <span class="goal-tx-page-btn disabled">
                                Next <i class="fas fa-chevron-right"></i>
                            </span>
                        @endif
                    </div>
                @endif
            @else
                <div class="goal-empty-transactions">
                    <i class="fas fa-inbox"></i>
                    <p>No transactions yet for this goal</p>
                </div>
            @endif
        </div>
    </div>

    @if($isParent)
        <script>
            function openEditGoalModal(goalId) {
                window.location.href = '{{ route('kids.goals', $kid) }}?edit=' + goalId;
            }

            let activeFundForm = null;

            // Currency input formatter: digits only → $X.XX (cash register style)
            function formatFundInput(el) {
                // Strip everything except digits
                let digits = el.value.replace(/\D/g, '');
                if (!digits) { el.value = ''; return; }
                // Treat as cents
                let cents = parseInt(digits, 10);
                let dollars = (cents / 100).toFixed(2);
                el.value = '$' + parseFloat(dollars).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                // Store raw cents on element for easy retrieval
                el.dataset.cents = cents;
            }

            function fundInputKeydown(e, type) {
                // Allow Enter to submit
                if (e.key === 'Enter') submitFunds(type);
            }

            function toggleFundForm(type) {
                const addWrap = document.getElementById('addFundsWrap');
                const removeWrap = document.getElementById('removeFundsWrap');
                const btnAdd = document.getElementById('btnAddFunds');
                const btnRemove = document.getElementById('btnRemoveFunds');

                if (activeFundForm === type || type === null) {
                    // Close current
                    addWrap.classList.remove('open');
                    removeWrap.classList.remove('open');
                    btnAdd.classList.remove('active');
                    btnRemove.classList.remove('active');
                    // Clear inputs
                    ['addAmount','removeAmount','addNote','removeNote'].forEach(id => {
                        const el = document.getElementById(id);
                        if (el) { el.value = ''; delete el.dataset.cents; el.style.borderColor = ''; }
                    });
                    activeFundForm = null;
                } else {
                    // Switch/open
                    addWrap.classList.toggle('open', type === 'add');
                    removeWrap.classList.toggle('open', type === 'remove');
                    btnAdd.classList.toggle('active', type === 'add');
                    btnRemove.classList.toggle('active', type === 'remove');
                    activeFundForm = type;
                    // Focus the amount input
                    setTimeout(() => {
                        document.getElementById(type === 'add' ? 'addAmount' : 'removeAmount').focus();
                    }, 100);
                }
            }

            function submitFunds(type) {
                const amountEl = document.getElementById(type === 'add' ? 'addAmount' : 'removeAmount');
                const noteEl = document.getElementById(type === 'add' ? 'addNote' : 'removeNote');
                const submitBtn = document.getElementById(type === 'add' ? 'btnSubmitAdd' : 'btnSubmitRemove');
                const cents = parseInt(amountEl.dataset.cents || '0', 10);
                const amount = cents / 100;

                if (!cents || cents <= 0) {
                    amountEl.style.borderColor = '#ef4444';
                    amountEl.focus();
                    return;
                }
                amountEl.style.borderColor = '';

                submitBtn.disabled = true;
                submitBtn.textContent = '...';

                const url = type === 'add'
                    ? '{{ route('parent.goals.add-funds', $goal) }}'
                    : '{{ route('parent.goals.remove-funds', $goal) }}';

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ amount: amount, note: noteEl.value || '' })
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        window.location.reload();
                    } else {
                        alert(d.message || 'Error processing request');
                        submitBtn.disabled = false;
                        submitBtn.textContent = type === 'add' ? 'Add' : 'Remove';
                    }
                })
                .catch(() => {
                    alert('Network error, please try again.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = type === 'add' ? 'Add' : 'Remove';
                });
            }
        </script>
    @endif

    @if(!$isParent && $isComplete && !in_array($goal->status, ['pending_redemption', 'redeemed']))
        <script>
            function requestRedemption() {
                const btn = document.getElementById('goalRedeemBtn');
                if (!btn) return;
                btn.disabled = true;
                btn.innerHTML = '<span style="display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,0.4);border-top-color:white;border-radius:50%;animation:spin 0.7s linear infinite;margin-right:6px;vertical-align:middle;"></span> Sending...';
                fetch('/kid/goals/{{ $goal->id }}/request-redemption', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}
                }).then(r => r.json()).then(d => {
                    if (d.success) {
                        btn.outerHTML = '<div class="goal-redeem-cta pending" style="margin-top:14px;"><i class="fas fa-clock"></i> Awaiting parent approval</div>';
                    } else if (d.cooldown) {
                        // Show cooldown notice inline instead of alert
                        btn.style.display = 'none';
                        const hours = Math.ceil(d.hours_left || 24);
                        const notice = document.createElement('div');
                        notice.style.cssText = 'background:#fef3c7; border:1px solid #fcd34d; border-radius:10px; padding:12px 16px; margin-top:14px; text-align:center; font-size:14px; font-weight:600; color:#92400e;';
                        notice.innerHTML = '<i class="fas fa-clock"></i> You can ask again in ' + hours + ' hour' + (hours !== 1 ? 's' : '');
                        btn.parentNode.insertBefore(notice, btn);
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-gift"></i> Ask Parent to Fulfill! 🎉';
                        // Show inline error instead of alert
                        showRedeemError(d.message || 'Something went wrong. Please try again.');
                    }
                }).catch(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-gift"></i> Ask Parent to Fulfill! 🎉';
                    showRedeemError('Network error. Please try again.');
                });
            }

            function showRedeemError(msg) {
                let existing = document.getElementById('goalRedeemError');
                if (existing) existing.remove();
                const el = document.createElement('div');
                el.id = 'goalRedeemError';
                el.style.cssText = 'background:#fee2e2; border:1px solid #fca5a5; border-radius:8px; padding:10px 14px; margin-top:10px; font-size:13px; font-weight:600; color:#dc2626; text-align:center;';
                el.textContent = msg;
                const btn = document.getElementById('goalRedeemBtn');
                if (btn) btn.parentNode.insertBefore(el, btn.nextSibling);
            }

            function acknowledgeDenial() {
                const btn = document.getElementById('goalAcknowledgeBtn');
                if (!btn) return;
                btn.disabled = true;
                btn.innerHTML = '<span style="display:inline-block;width:13px;height:13px;border:2px solid rgba(255,255,255,0.4);border-top-color:white;border-radius:50%;animation:spin 0.7s linear infinite;vertical-align:middle;"></span>';
                fetch('/kid/goals/{{ $goal->id }}/acknowledge-denial', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}
                }).then(r => r.json()).then(d => {
                    if (d.success) {
                        const banner = document.getElementById('goalDenialBanner');
                        if (banner) banner.style.display = 'none';
                        if (d.cooldown_active) {
                            const cooldownEl = document.getElementById('goalCooldownNotice');
                            const hoursEl = document.getElementById('cooldownHoursDisplay');
                            const hours = Math.ceil(d.hours_left || 24);
                            if (hoursEl) hoursEl.textContent = hours;
                            // Update plural text node after the span
                            if (cooldownEl) {
                                cooldownEl.style.display = 'block';
                                // Refresh the "hour/hours" text
                                cooldownEl.innerHTML = '<i class="fas fa-clock"></i> You can ask again in ' + hours + ' hour' + (hours !== 1 ? 's' : '');
                            }
                        } else {
                            // No cooldown — show redeem button
                            const redeemWrap = document.getElementById('goalRedeemBtnWrap');
                            if (redeemWrap) redeemWrap.style.display = 'block';
                        }
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-check"></i> I Understand';
                    }
                }).catch(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check"></i> I Understand';
                });
            }
        </script>
        <style>
            @keyframes spin { to { transform: rotate(360deg); } }
        </style>
    @endif

    @if($isParent && $isComplete && $goal->status !== 'redeemed')
    <!-- Redeem Goal Confirmation Modal -->
    <div id="redeemGoalModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.75); z-index: 99999; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 16px; padding: 32px; width: 90%; max-width: 440px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3);">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-gift" style="color: white; font-size: 22px;"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 20px; font-weight: 700; color: #111827;">Redeem Goal</h3>
                    <p style="margin: 4px 0 0 0; font-size: 14px; color: #6b7280; font-weight: 500;">{{ $goal->title }}</p>
                </div>
            </div>

            <!-- Goal summary -->
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 18px; margin-bottom: 20px; text-align: center;">
                <p style="margin: 0 0 4px 0; font-size: 12px; font-weight: 700; color: #16a34a; text-transform: uppercase; letter-spacing: 0.06em;">Goal Complete!</p>
                <p style="margin: 0; font-size: 30px; font-weight: 800; color: #111827;">${{ number_format($goal->current_amount, 2) }} <span style="font-size: 16px; font-weight: 600; color: #6b7280;">saved</span></p>
            </div>

            <p style="font-size: 14px; color: #374151; margin: 0 0 24px 0; line-height: 1.6;">
                Are you sure you want to redeem this goal? Confirming means the item has been purchased and is ready for <strong>{{ $kid->name }}</strong> to enjoy!
            </p>

            <div style="display: flex; gap: 12px;">
                <button onclick="closeRedeemGoalModal()" style="flex: 1; padding: 12px 16px; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;">
                    Cancel
                </button>
                <button onclick="document.getElementById('redeemGoalForm').submit()" style="flex: 1; padding: 12px 16px; background: #10b981; color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-gift"></i> Confirm Redemption
                </button>
            </div>
        </div>
    </div>

    <script>
        function openRedeemGoalModal() {
            document.getElementById('redeemGoalModal').style.display = 'flex';
        }

        function closeRedeemGoalModal() {
            document.getElementById('redeemGoalModal').style.display = 'none';
        }

        document.getElementById('redeemGoalModal').addEventListener('click', function(e) {
            if (e.target === this) closeRedeemGoalModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('redeemGoalModal').style.display === 'flex') {
                closeRedeemGoalModal();
            }
        });
    </script>
    @endif
@endsection
