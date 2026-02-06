@extends('layouts.parent')

@section('title', 'Parent Dashboard - AllowanceLab')

@section('content')
@section('content')
    <!-- Mobile Welcome Section -->
    <div class="mobile-welcome">
        @php
            $hour = now()->hour;
            if ($hour < 12) {
                $greeting = 'Good morning';
            } elseif ($hour < 18) {
                $greeting = 'Good afternoon';
            } else {
                $greeting = 'Good evening';
            }
        @endphp
        <h2 class="mobile-welcome-greeting">{{ $greeting }}, {{ $user->first_name ?? explode(' ', $user->name)[0] }}!</h2>
        <p class="mobile-welcome-subtitle">Manage your family of {{ $kids->count() }} below.</p>
    </div>

    <!-- Pending Redemption Notification Banner -->
    @if($pendingRedemptionCount > 0)
        <div class="parent-redemption-notification-banner">
            <div class="parent-redemption-notification-content">
                <div class="parent-redemption-notification-icon">🎁</div>
                <div class="parent-redemption-notification-text">
                    <strong>{{ $pendingRedemptionCount }} Goal{{ $pendingRedemptionCount > 1 ? 's' : '' }} Pending Redemption</strong>
                    <div class="parent-redemption-kids-list">
                        @foreach($kidsWithPendingRedemptions as $kidWithPending)
                            <a href="{{ route('parent.goals.index', $kidWithPending) }}" class="parent-redemption-kid-link">
                                {{ $kidWithPending->name }} ({{ $kidWithPending->goals()->where('status', 'pending_redemption')->count() }})
                            </a>{{ !$loop->last ? ',' : '' }}
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($kids->count() > 0)
        @foreach($kids as $kid)
            <div class="kid-card-compact" data-kid-id="{{ $kid->id }}">
                @php
                    $invite = $kid->invite;
                    $showPendingBadge = $invite && $invite->status === 'pending' && !$invite->isExpired();
                    $daysOfWeek = ['sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6];
                    $targetDay = $daysOfWeek[$kid->allowance_day] ?? 5;
                    $today = now();
                    $daysUntil = ($targetDay - $today->dayOfWeek + 7) % 7;
                    if ($daysUntil === 0) $daysUntil = 7;
                    $nextAllowance = $today->copy()->addDays($daysUntil);
                    $pointsPercent = $kid->max_points > 0 ? ($kid->points / $kid->max_points) * 100 : 0;
                    $pointsClass = $pointsPercent >= 80 ? 'points-high' : ($pointsPercent >= 50 ? 'points-medium' : 'points-low');
                    $activeGoals = $kid->goals()->whereIn('status', ['active', 'ready_to_redeem', 'pending_redemption'])->get();
                @endphp

                <!-- Desktop Layout: Line 1 - Avatar | Name | Balance | Action Buttons -->
                <div class="kid-card-line-1">
                    <div class="avatar-compact" style="background: {{ $kid->color }};">{{ strtoupper(substr($kid->name, 0, 1)) }}</div>
                    <div class="kid-name-compact">
                        {{ $kid->name }}
                        @if($showPendingBadge)
                            <span class="status-badge-compact status-pending">
                                <i class="fas fa-clock"></i> Invite Pending
                            </span>
                        @endif
                    </div>
                    <div class="balance-compact {{ $kid->balance < 0 ? 'negative' : '' }}">${{ number_format($kid->balance, 2) }}</div>
                    <div class="action-buttons-compact">
                        <button class="btn-compact btn-deposit" onclick="toggleForm('deposit-{{ $kid->id }}')">Deposit</button>
                        <button class="btn-compact btn-spend" onclick="toggleForm('spend-{{ $kid->id }}')">Spend</button>
                        @if($kid->points_enabled)
                            <button class="btn-compact btn-points" onclick="toggleForm('points-{{ $kid->id }}')">Points</button>
                        @endif
                        <button class="btn-compact btn-ledger" onclick="toggleForm('ledger-{{ $kid->id }}', this)">Ledger</button>
                    </div>
                </div>

                <!-- Desktop Layout: Line 2 - Points Badge | Next Allowance | Dropdown Menu -->
                <div class="kid-card-line-2">
                    <div class="kid-card-line-2-left">
                        @if($kid->points_enabled)
                            <div class="points-badge-compact {{ $pointsClass }}">{{ $kid->points }}/{{ $kid->max_points }}</div>
                        @endif
                    </div>
                    <div class="next-allowance-compact">
                        @if($kid->points_enabled && $kid->points === 0)
                            <span style="color: #ef4444; font-weight: 600;">
                                ⚠️ 0 points - No allowance on {{ $nextAllowance->format('l, M j') }}
                            </span>
                        @else
                            Next: ${{ number_format($kid->allowance_amount, 2) }} on {{ ucfirst($kid->allowance_day) }}, {{ $nextAllowance->format('M j') }}
                        @endif
                    </div>
                    <div class="kid-card-dropdown">
                        <button class="kid-card-dropdown-trigger" onclick="toggleKidDropdown({{ $kid->id }})">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                        <div class="kid-card-dropdown-menu" id="kidDropdown{{ $kid->id }}">
                            <a href="{{ route('parent.goals.index', $kid) }}" class="kid-card-dropdown-item">
                                <i class="fas fa-bullseye"></i> View Goals
                            </a>
                            <a href="{{ route('kids.manage', $kid) }}" class="kid-card-dropdown-item">
                                <i class="fas fa-cog"></i> Manage Kid
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Mobile Layout: Row 1 - Avatar | Name on left, Balance on right -->
                <div class="kid-card-row-1">
                    <div class="avatar-compact" style="background: {{ $kid->color }};">{{ strtoupper(substr($kid->name, 0, 1)) }}</div>
                    <div class="kid-name-compact">{{ $kid->name }}</div>
                    <div class="balance-compact {{ $kid->balance < 0 ? 'negative' : '' }}">${{ number_format($kid->balance, 2) }}</div>
                </div>
                @if($showPendingBadge)
                    <div style="margin-bottom: 8px; margin-top: -4px;">
                        <span class="status-badge-compact status-pending">
                            <i class="fas fa-clock"></i> Pending
                        </span>
                    </div>
                @endif

                <!-- Row 2: Points Badge | Next Allowance Info -->
                <div class="kid-card-row-2">
                    <div class="kid-card-row-2-left">
                        @if($kid->points_enabled)
                            <div class="points-badge-compact {{ $pointsClass }}">{{ $kid->points }}/{{ $kid->max_points }}</div>
                        @endif
                    </div>
                    <div class="next-allowance-compact">
                        @if($kid->points_enabled && $kid->points === 0)
                            <span style="color: #ef4444; font-weight: 600; font-size: 11px;">
                                ⚠️ 0 pts - No allowance
                            </span>
                        @else
                            <span style="font-size: 11px;">Next: ${{ number_format($kid->allowance_amount, 2) }} | {{ ucfirst(substr($kid->allowance_day, 0, 3)) }}, {{ $nextAllowance->format('M j') }}</span>
                        @endif
                    </div>
                </div>

                <!-- Row 3: Action Buttons with More expand/collapse -->
                <div class="kid-card-row-3">
                    <button class="btn-compact btn-deposit" onclick="toggleForm('deposit-{{ $kid->id }}')">
                        <i class="fas fa-plus-circle"></i> Deposit
                    </button>
                    <button class="btn-compact btn-spend" onclick="toggleForm('spend-{{ $kid->id }}')">
                        <i class="fas fa-minus-circle"></i> Spend
                    </button>
                    <button class="btn-compact btn-more" onclick="toggleMoreActions({{ $kid->id }})" id="moreBtn{{ $kid->id }}">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>

                <!-- More Actions (collapsed by default) -->
                <div class="kid-card-more-actions" id="moreActions{{ $kid->id }}" style="display: none;">
                    @if($kid->points_enabled)
                        <button class="btn-compact btn-points" onclick="toggleForm('points-{{ $kid->id }}')">
                            <i class="fas fa-star"></i> Points
                        </button>
                    @endif
                    <button class="btn-compact btn-ledger" onclick="toggleForm('ledger-{{ $kid->id }}', this)">
                        <i class="fas fa-list"></i> Ledger
                    </button>
                </div>

                <!-- Deposit Form -->
                <div class="dropdown-form" id="deposit-{{ $kid->id }}Form">
                    <div class="form-content">
                        <form action="{{ route('kids.deposit', $kid) }}" method="POST" class="inline-form">
                            @csrf
                            <div class="form-group">
                                <label class="form-label">Amount</label>
                                <input type="text" inputmode="decimal" class="form-input currency-input" name="amount"
                                    placeholder="0.00" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Note:</label>
                                <input type="text" class="form-input" name="note" placeholder="What was this for?">
                            </div>
                            <button type="submit" class="submit-btn submit-deposit">Record Deposit</button>
                        </form>
                    </div>
                </div>

                <!-- Spend Form -->
                <div class="dropdown-form" id="spend-{{ $kid->id }}Form">
                    <div class="form-content">
                        <form action="{{ route('kids.spend', $kid) }}" method="POST" class="inline-form">
                            @csrf
                            <div class="form-group">
                                <label class="form-label">Amount</label>
                                <input type="text" inputmode="decimal" class="form-input currency-input" name="amount"
                                    placeholder="0.00" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Note:</label>
                                <input type="text" class="form-input" name="note" placeholder="What did they buy?">
                            </div>
                            <button type="submit" class="submit-btn submit-spend">Record Spend</button>
                        </form>
                    </div>
                </div>

                <!-- Points Form -->
                @if($kid->points_enabled)
                    <div class="dropdown-form" id="points-{{ $kid->id }}Form">
                        <div class="form-content">
                            <div class="current-points">Current: {{ $kid->points }} / {{ $kid->max_points }} points</div>
                            <form action="{{ route('kids.points', $kid) }}" method="POST" class="points-form-inline">
                                @csrf
                                <div class="points-adjustment-row">
                                    <div class="points-control">
                                        <label class="points-label">Adjust</label>
                                        <div class="points-adjuster">
                                            <button type="button" class="points-btn"
                                                onclick="adjustPoints({{ $kid->id }}, -1)">−</button>
                                            <input type="number" class="points-display" name="points" id="points-{{ $kid->id }}"
                                                value="0" readonly>
                                            <button type="button" class="points-btn"
                                                onclick="adjustPoints({{ $kid->id }}, 1)">+</button>
                                        </div>
                                    </div>
                                    <div class="points-reason">
                                        <label class="points-label">Reason:</label>
                                        <input type="text" class="form-input" name="reason" placeholder="Why are you adjusting points?"
                                            required>
                                    </div>
                                </div>
                                <button type="submit" class="submit-btn submit-points">Adjust Points</button>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Ledger -->
                <div class="dropdown-form" id="ledger-{{ $kid->id }}Form">
                    <div class="form-content">
                        <div class="ledger-filters">
                            <div class="ledger-filter-buttons">
                                <button class="filter-btn active" data-kid="{{ $kid->id }}"
                                    onclick="filterLedger({{ $kid->id }}, 'all')">All</button>
                                <button class="filter-btn" data-kid="{{ $kid->id }}"
                                    onclick="filterLedger({{ $kid->id }}, 'deposit')">Deposits</button>
                                <button class="filter-btn" data-kid="{{ $kid->id }}"
                                    onclick="filterLedger({{ $kid->id }}, 'spend')">Spends</button>
                                @if($kid->points_enabled)
                                    <button class="filter-btn" data-kid="{{ $kid->id }}"
                                        onclick="filterLedger({{ $kid->id }}, 'points')">Point
                                        Adjustments</button>
                                @endif
                            </div>
                            <div class="ledger-kid-legend">
                                <span class="ledger-kid-icon-sample" style="color: {{ $kid->color }};">K</span> = Kid initiated this
                                transaction
                            </div>
                        </div>
                        <div class="ledger-table" id="ledger-{{ $kid->id }}-table">
                            @php
                                $transactions = $kid->transactions()->latest()->take(8)->get();
                                $pointAdjustments = $kid->pointAdjustments()->latest()->take(8)->get();
                                $allEntries = $transactions->concat($pointAdjustments)->sortByDesc('created_at')->take(8);
                            @endphp

                            @forelse($allEntries as $entry)
                                @php
                                    $isKidInitiated = ($entry instanceof \App\Models\Transaction) && ($entry->initiated_by === 'kid');

                                    // Convert hex to RGB for background
                                    $hex = ltrim($kid->color, '#');
                                    $r = hexdec(substr($hex, 0, 2));
                                    $g = hexdec(substr($hex, 2, 2));
                                    $b = hexdec(substr($hex, 4, 2));
                                    $lightBg = "rgba($r, $g, $b, 0.08)";
                                @endphp

                                <div class="ledger-row {{ $entry instanceof \App\Models\Transaction && $entry->description === 'Allowance not earned - insufficient points' ? 'allowance-not-earned' : '' }}"
                                    data-type="{{ $entry instanceof \App\Models\Transaction ? $entry->type : 'points' }}"
                                    style="{{ $isKidInitiated ? 'background: ' . $lightBg . ';' : '' }}">

                                    <div class="ledger-kid-icon-cell">
                                        @if($isKidInitiated)
                                            <span class="ledger-kid-icon" style="color: {{ $kid->color }};">K</span>
                                        @endif
                                    </div>

                                    <div class="ledger-date">{{ $entry->created_at->format('M j') }} |
                                        {{ $entry->created_at->format('g:i A') }}
                                    </div>

                                    @if($entry instanceof \App\Models\Transaction)
                                        <div class="ledger-type {{ $entry->type }}">
                                            @if($entry->description === 'Weekly Allowance')
                                                Allowance
                                            @elseif($entry->description === 'Allowance not earned - insufficient points')
                                                Allowance
                                            @else
                                                {{ ucfirst($entry->type) }}
                                            @endif
                                        </div>
                                        <div class="ledger-amount {{ $entry->type }}">${{ number_format($entry->amount, 2) }}</div>
                                        <div class="ledger-note">{{ $entry->description ?? 'No note' }}</div>
                                    @else
                                        <div class="ledger-type points">
                                            {{ $entry->reason === 'Weekly points reset' ? 'Point Reset' : 'Points' }}
                                        </div>
                                        <div class="ledger-amount {{ $entry->points_change > 0 ? 'points-add' : 'points-deduct' }}">
                                            {{ $entry->points_change > 0 ? '+' : '' }}{{ $entry->points_change }} pts
                                        </div>
                                        <div class="ledger-note">{{ $entry->reason ?? 'No reason' }}</div>
                                    @endif
                                </div>
                            @empty
                                <div class="ledger-empty">No transactions yet</div>
                            @endforelse
                        </div>
                        <button type="button" class="view-all-btn" onclick="openTransactionModal({{ $kid->id }})">View All
                            Transactions</button>
                    </div>
                </div>

                <!-- Goals Section (Conditional & Collapsible) -->
                @if($activeGoals->count() > 0)
                    <div class="kid-card-goals-header">
                        <div class="goals-header-left" onclick="event.stopPropagation(); toggleGoals({{ $kid->id }})">
                            <span class="goal-count-badge">Goals: {{ $activeGoals->count() }}</span>
                            <i class="fas fa-chevron-down goals-chevron" id="goalsChevron{{ $kid->id }}"></i>
                        </div>
                        <div class="kid-card-dropdown">
                            <button class="kid-card-dropdown-trigger" onclick="event.stopPropagation(); toggleKidDropdown('{{ $kid->id }}-footer')">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                            <div class="kid-card-dropdown-menu" id="kidDropdown{{ $kid->id }}-footer">
                                <a href="{{ route('parent.goals.index', $kid) }}" class="kid-card-dropdown-item">
                                    <i class="fas fa-bullseye"></i> View Goals
                                </a>
                                <a href="{{ route('kids.manage', $kid) }}" class="kid-card-dropdown-item">
                                    <i class="fas fa-cog"></i> Manage Kid
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="kid-card-goals-container" id="goalsContainer{{ $kid->id }}">
                    @foreach($activeGoals as $index => $goal)
                        @php
                            $progressPercent = $goal->target_amount > 0 ? ($goal->current_amount / $goal->target_amount) * 100 : 0;
                            $progressPercent = min($progressPercent, 100);
                        @endphp
                        <div class="kid-card-goal-row" style="--kid-color: {{ $kid->color }};">
                            @if($index === 0)
                                <div class="goal-header-cell">
                                    <span class="goal-count-badge">Goals: {{ $activeGoals->count() }}</span>
                                </div>
                            @else
                                <div class="goal-header-cell"></div>
                            @endif
                            <div class="goal-name-cell">
                                {{ $goal->title }}
                                @if($goal->status === 'pending_redemption')
                                    <span class="goal-pending-badge" style="background: {{ $kid->color }}; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; margin-left: 8px;">
                                        NEEDS REDEMPTION
                                    </span>
                                @endif
                            </div>
                            <div class="goal-progress-cell">
                                <div class="goal-progress-bar">
                                    <div class="goal-progress-fill" style="width: {{ $progressPercent }}%;"></div>
                                </div>
                                <span class="goal-progress-text">{{ number_format($progressPercent, 0) }}%</span>
                            </div>
                            <div class="goal-amount-cell">${{ number_format($goal->current_amount, 2) }} of ${{ number_format($goal->target_amount, 2) }}</div>

                            @if(in_array($goal->status, ['ready_to_redeem', 'pending_redemption']))
                                <!-- Goal is complete - show redemption button -->
                                <div style="display: flex; align-items: center; gap: 8px; margin-left: auto;">
                                    <a href="{{ route('parent.goals.index', $kid) }}" style="background: #10b981; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-block; transition: background 0.2s;">
                                        <i class="fas fa-check-circle"></i> GOAL COMPLETE
                                    </a>
                                    <form id="redeem-form-{{ $goal->id }}" action="{{ route('parent.goals.redeem', $goal) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button type="button" onclick="showRedeemConfirmation('{{ $goal->id }}', '{{ $kid->name }}', '{{ $goal->title }}', '{{ number_format($goal->current_amount, 2) }}')" class="btn-goal-view" style="background: {{ $kid->color }}; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">
                                            <i class="fas fa-gift"></i> Redeem Goal
                                        </button>
                                    </form>
                                </div>
                            @else
                                <!-- Goal is active - show add funds button -->
                                <button class="btn-goal-add-funds" onclick="toggleForm('goal-{{ $goal->id }}')">Add Funds</button>
                                <a href="{{ route('parent.goals.index', $kid) }}" class="btn-goal-view">View Goals</a>
                            @endif
                        </div>

                        @if($goal->status === 'active')
                            <!-- Add Funds Form for this goal (only for active goals) -->
                            <div class="dropdown-form" id="goal-{{ $goal->id }}Form" style="padding: 12px 16px 12px 0; background: #f9fafb;">
                                <form action="{{ route('parent.goals.add-funds', $goal) }}" method="POST" class="inline-form goal-add-funds-form" data-goal-id="{{ $goal->id }}" data-kid-id="{{ $kid->id }}" style="margin-left: auto; max-width: fit-content;">
                                    @csrf
                                    <div style="display: flex; gap: 14px; align-items: center;">
                                        <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
                                            <div style="display: flex; align-items: center; gap: 6px; font-size: 14px; color: #374151; font-weight: 600;">
                                                <i class="fas fa-wallet" style="color: {{ $kid->color }};"></i>
                                                ${{ number_format($kid->balance, 2) }}
                                            </div>
                                            <div style="font-size: 11px; color: #6b7280; font-weight: 500;">Available</div>
                                        </div>
                                        <input type="text" inputmode="decimal" class="form-input currency-input" name="amount"
                                            placeholder="0.00" required style="width: 140px; padding: 8px 12px; font-size: 14px; border: 1px solid #d1d5db; border-radius: 6px;">
                                        <button type="submit" class="submit-btn submit-goal-funds" style="background-color: {{ $kid->color }}; padding: 8px 16px; font-size: 13px; white-space: nowrap;">Transfer</button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    @endforeach
                    </div>
                @else
                    <!-- No goals - show 3-dot menu at bottom right (Mobile only) -->
                    <div class="kid-card-footer">
                        <div class="kid-card-dropdown">
                            <button class="kid-card-dropdown-trigger" onclick="toggleKidDropdown('{{ $kid->id }}-footer')">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                            <div class="kid-card-dropdown-menu" id="kidDropdown{{ $kid->id }}-footer">
                                <a href="{{ route('parent.goals.index', $kid) }}" class="kid-card-dropdown-item">
                                    <i class="fas fa-bullseye"></i> View Goals
                                </a>
                                <a href="{{ route('kids.manage', $kid) }}" class="kid-card-dropdown-item">
                                    <i class="fas fa-cog"></i> Manage Kid
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        @endforeach
    @else
        <div class="empty-state">
            <h1 class="empty-state-title">Let's get started!</h1>
            <button class="empty-state-btn" onclick="openAddKidModal()">+ Add Kid</button>
        </div>
    @endif
@endsection

@section('modals')
    <!-- Add Kid Modal -->
    @include('partials.add-kid-modal')
    @include('partials.send-invite-modal')

    <!-- Transaction Modal -->
    <div class="transaction-modal" id="transactionModal">
        <div class="modal-backdrop" onclick="closeTransactionModal()"></div>
        <div class="transaction-modal-content">
            <div class="transaction-modal-header">
                <h2>All Transactions</h2>
                <button class="close-btn" onclick="closeTransactionModal()">&times;</button>
            </div>

            <div class="transaction-filters">
                <div class="filter-tabs">
                    <button class="modal-filter-btn active" onclick="filterTransactionModal('all')">All</button>
                    <button class="modal-filter-btn" onclick="filterTransactionModal('deposit')">Deposits</button>
                    <button class="modal-filter-btn" onclick="filterTransactionModal('spend')">Spends</button>
                    <button class="modal-filter-btn" onclick="filterTransactionModal('points')">Points</button>
                </div>
                <select class="date-range-select" onchange="filterByDateRange(this.value)">
                    <option value="30">Last 30 Days</option>
                    <option value="90">Last 3 Months</option>
                    <option value="180">Last 6 Months</option>
                    <option value="all">All Time</option>
                </select>
            </div>

            <div class="transaction-modal-body" id="transactionModalBody">
                <!-- Transactions will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Add Funds to Goal Modal -->
    <div class="goal-fund-modal" id="goalFundModal" style="display: none;">
        <div class="modal-backdrop" onclick="closeGoalFundModal()"></div>
        <div class="goal-fund-modal-content">
            <div class="goal-fund-modal-header">
                <h2>Add Funds to Goal</h2>
                <button class="close-btn" onclick="closeGoalFundModal()">&times;</button>
            </div>
            <div class="goal-fund-modal-body" id="goalFundModalBody">
                <!-- Form will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Redeem Goal Confirmation Modal -->
    <div id="redeemConfirmModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 10000; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 12px; padding: 24px; max-width: 440px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-gift" style="color: white; font-size: 24px;"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 600; color: #111827;">Redeem Goal</h3>
                    <p id="redeemGoalTitle" style="margin: 4px 0 0 0; font-size: 14px; color: #6b7280;"></p>
                </div>
            </div>
            <p id="redeemConfirmMessage" style="margin: 16px 0; color: #374151; font-size: 14px; line-height: 1.5;"></p>
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button onclick="closeRedeemConfirmation()" style="flex: 1; padding: 10px 16px; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                    Cancel
                </button>
                <button onclick="confirmRedeem()" style="flex: 1; padding: 10px 16px; background: #10b981; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                    <i class="fas fa-check"></i> Confirm Redemption
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentRedeemFormId = null;

        function showRedeemConfirmation(goalId, kidName, goalTitle, amount) {
            currentRedeemFormId = 'redeem-form-' + goalId;
            document.getElementById('redeemGoalTitle').textContent = goalTitle;
            document.getElementById('redeemConfirmMessage').textContent =
                `This confirms that ${kidName} has received their item. The funds ($${amount}) will remain locked in the goal as a permanent record of the purchase.`;
            document.getElementById('redeemConfirmModal').style.display = 'flex';
        }

        function closeRedeemConfirmation() {
            document.getElementById('redeemConfirmModal').style.display = 'none';
            currentRedeemFormId = null;
        }

        function confirmRedeem() {
            if (currentRedeemFormId) {
                document.getElementById(currentRedeemFormId).submit();
            }
        }

        // Close modal on backdrop click
        document.getElementById('redeemConfirmModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRedeemConfirmation();
            }
        });

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('redeemConfirmModal').style.display === 'flex') {
                closeRedeemConfirmation();
            }
        });
    </script>
@endsection