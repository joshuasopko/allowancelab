@extends($isParent ? 'layouts.parent' : 'layouts.kid')

@section('title', $goal->title . ' - Goal Details')

@section('content')
    @php
        $kid = $goal->kid;
        $progress = $goal->target_amount > 0 ? ($goal->current_amount / $goal->target_amount) * 100 : 0;
        $progress = min($progress, 100);
        $isComplete = $progress >= 100;

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
        }

        .goal-detail-link:hover {
            text-decoration: underline;
        }

        .goal-detail-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
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
            height: 24px;
            background: #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 12px;
        }

        .goal-progress-bar-fill {
            height: 100%;
            background: {{ $isComplete ? '#10b981' : $kid->color }};
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 12px;
        }

        .goal-progress-percent {
            color: white;
            font-weight: 700;
            font-size: 14px;
        }

        .goal-remaining {
            font-size: 14px;
            color: #6b7280;
            font-weight: 600;
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
            <h1 class="goal-detail-title">Goal Details</h1>
            <div style="display: flex; gap: 12px;">
                @if($isParent && !in_array($goal->status, ['pending_redemption', 'redeemed']))
                    <button onclick="openEditGoalModal({{ $goal->id }})" class="btn-edit">
                        <i class="fas fa-edit"></i> Edit Goal
                    </button>
                @endif
                <a href="{{ $isParent ? route('kids.goals', $kid) : route('kid.goals.index') }}" class="btn-back">
                    ← Back to Goals
                </a>
            </div>
        </div>

        <!-- Goal Card -->
        <div class="goal-detail-card">
            <div class="goal-detail-top">
                @if($goal->photo_path)
                    <img src="{{ asset('storage/' . $goal->photo_path) }}" alt="{{ $goal->title }}" class="goal-detail-photo">
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
                    @if($goal->product_url)
                        <a href="{{ $goal->product_url }}" target="_blank" class="goal-detail-link">
                            <i class="fas fa-external-link-alt"></i> View on {{ parse_url($goal->product_url, PHP_URL_HOST) }}
                        </a>
                    @endif

                    @if($goal->status === 'ready_to_redeem' || $isComplete)
                        <div class="goal-detail-status complete">
                            <i class="fas fa-check-circle"></i> Goal Complete!
                        </div>
                    @elseif($goal->status === 'pending_redemption')
                        <div class="goal-detail-status pending">
                            <i class="fas fa-clock"></i> Pending Redemption
                        </div>
                    @else
                        <div class="goal-detail-status active">
                            <i class="fas fa-bullseye"></i> Active Goal
                        </div>
                    @endif
                </div>
            </div>

            <!-- Progress Section -->
            <div class="goal-detail-progress">
                <div class="goal-detail-amounts">
                    <span class="goal-current">${{ number_format($goal->current_amount, 2) }}</span>
                    <span class="goal-target">of ${{ number_format($goal->target_amount, 2) }}</span>
                </div>

                <div class="goal-progress-bar-container">
                    <div class="goal-progress-bar-fill" style="width: {{ $progress }}%;">
                        @if($progress >= 15)
                            <span class="goal-progress-percent">{{ number_format($progress, 0) }}%</span>
                        @endif
                    </div>
                </div>

                @if(!$isComplete)
                    <div class="goal-remaining">
                        ${{ number_format($goal->target_amount - $goal->current_amount, 2) }} remaining to complete
                    </div>
                @else
                    <div class="goal-remaining" style="color: #10b981;">
                        <i class="fas fa-check-circle"></i> Goal completed!
                    </div>
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
        </div>

        <!-- Transaction History -->
        <div class="goal-transactions-section">
            <h3 class="goal-transactions-header">
                <i class="fas fa-history"></i> Transaction History
            </h3>

            @if($goal->goalTransactions->count() > 0)
                @foreach($goal->goalTransactions as $transaction)
                    <div class="goal-transaction-item">
                        <div class="goal-transaction-left">
                            <div class="goal-transaction-description">
                                {{ $transaction->description }}
                            </div>
                            <div class="goal-transaction-meta">
                                {{ $transaction->created_at->format('M j, Y g:i A') }}
                                @if($transaction->performedBy)
                                    • by {{ $transaction->performedBy->name }}
                                @endif
                            </div>
                        </div>
                        <div class="goal-transaction-amount {{ $transaction->transaction_type === 'redemption' ? 'withdrawal' : '' }}">
                            {{ $transaction->transaction_type === 'redemption' ? '-' : '+' }}${{ number_format($transaction->amount, 2) }}
                        </div>
                    </div>
                @endforeach
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
                // Redirect to parent goals index with edit intent
                // We'll use a session flag or URL parameter to open the modal
                window.location.href = '{{ route('kids.goals', $kid) }}?edit=' + goalId;
            }
        </script>
    @endif
@endsection
