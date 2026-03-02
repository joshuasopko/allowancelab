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
                            <a href="{{ route('kids.goals', $kidWithPending) }}" class="parent-redemption-kid-link">
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
                    $activeGoals = $kid->goals()->whereIn('status', ['active', 'ready_to_redeem', 'pending_redemption'])
                        ->orderByRaw("CASE status WHEN 'pending_redemption' THEN 0 WHEN 'ready_to_redeem' THEN 1 ELSE 2 END")
                        ->orderBy('created_at', 'desc')
                        ->get();
                @endphp

                <!-- 3-dot dropdown: inline in action buttons on desktop, absolute upper-right on mobile via CSS -->
                <div class="kid-card-dropdown kid-card-dropdown-mobile-only">
                    <button class="kid-card-dropdown-trigger" onclick="toggleKidDropdown('{{ $kid->id }}-mobile')">
                        <i class="fas fa-ellipsis-h"></i>
                    </button>
                    <div class="kid-card-dropdown-menu" id="kidDropdown{{ $kid->id }}-mobile">
                        <a href="{{ route('kids.overview', $kid) }}" class="kid-card-dropdown-item">
                            <i class="fas fa-chart-pie"></i> Overview
                        </a>
                        <a href="{{ route('kids.goals', $kid) }}" class="kid-card-dropdown-item">
                            <i class="fas fa-bullseye"></i> View Goals
                        </a>
                        <a href="{{ route('kids.wishes', $kid) }}" class="kid-card-dropdown-item">
                            <i class="fas fa-heart"></i> View Wishes
                        </a>
                        <a href="{{ route('kids.manage', $kid) }}" class="kid-card-dropdown-item">
                            <i class="fas fa-cog"></i> Manage Kid
                        </a>
                    </div>
                </div>

                <!-- Desktop Layout: Line 1 - Avatar | Name | Balance | Action Buttons -->
                <div class="kid-card-line-1">
                    <a href="{{ route('kids.overview', $kid) }}" class="avatar-compact" style="background: {{ $kid->color }}; text-decoration: none; color: white;">{{ strtoupper(substr($kid->name, 0, 1)) }}</a>
                    <div class="kid-name-compact">
                        <a href="{{ route('kids.overview', $kid) }}" style="text-decoration: none; color: inherit;">{{ $kid->name }}</a>
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

                <!-- Desktop Layout: Line 2 - Points Badge | Next Allowance | 3-dot menu -->
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
                        <button class="kid-card-dropdown-trigger" onclick="toggleKidDropdown('{{ $kid->id }}-footer')">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                        <div class="kid-card-dropdown-menu" id="kidDropdown{{ $kid->id }}-footer">
                            <a href="{{ route('kids.overview', $kid) }}" class="kid-card-dropdown-item">
                                <i class="fas fa-chart-pie"></i> Overview
                            </a>
                            <a href="{{ route('kids.goals', $kid) }}" class="kid-card-dropdown-item">
                                <i class="fas fa-bullseye"></i> View Goals
                            </a>
                            <a href="{{ route('kids.wishes', $kid) }}" class="kid-card-dropdown-item">
                                <i class="fas fa-heart"></i> View Wishes
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

                <!-- Category Pills Section -->
                @php
                    $recentWishes = $kid->getRecentWishes(4);
                    $pendingWishCount = $kid->getPendingWishRequestsCount();
                    $pendingGoalCount = $kid->goals()->where('status', 'pending_redemption')->count();
                    $displayGoals = $activeGoals->take(4);
                @endphp

                <!-- Pills Row (Always show for all kids) -->
                <div class="category-pills-row">
                    <button class="category-pill" onclick="toggleCategory({{ $kid->id }}, 'goals')">
                        <i class="fas fa-bullseye"></i> Goals
                        @if($pendingGoalCount > 0)
                            <span class="pill-badge pending">{{ $pendingGoalCount }}</span>
                        @endif
                    </button>

                    <button class="category-pill" onclick="toggleCategory({{ $kid->id }}, 'wishes')">
                        <i class="fas fa-heart"></i> Wishes
                        @if($pendingWishCount > 0)
                            <span class="pill-badge pending">{{ $pendingWishCount }}</span>
                        @endif
                    </button>

                    <!-- Placeholder for future Chores -->
                    <button class="category-pill category-pill-disabled" disabled>
                        <i class="fas fa-tasks"></i> Chores
                        <span class="pill-badge coming-soon">Soon</span>
                    </button>
                </div>

                <!-- Goals Content (Collapsible) -->
                <div class="category-content" id="goalsContent{{ $kid->id }}" style="display: none;">
                    @if($activeGoals->count() > 0)
                        @foreach($displayGoals as $goal)
                            @php
                                $progressPercent = $goal->target_amount > 0 ? ($goal->current_amount / $goal->target_amount) * 100 : 0;
                                $progressPercent = min($progressPercent, 100);
                                $truncatedGoalTitle = strlen($goal->title) > 45 ? substr($goal->title, 0, 45) . '...' : $goal->title;
                            @endphp
                            <div class="category-item-row {{ $goal->status === 'pending_redemption' ? 'pending-attention' : '' }}" style="--kid-color: {{ $kid->color }};">
                                <div class="item-name-cell">
                                    {{ $truncatedGoalTitle }}
                                    @if($goal->status === 'pending_redemption')
                                        <span class="item-pending-badge" style="background: #f59e0b;">
                                            <i class="fas fa-clock"></i> Requested
                                        </span>
                                    @endif
                                </div>
                                <div class="goal-progress-cell">
                                    <div class="goal-progress-bar">
                                        <div class="goal-progress-fill" style="width: {{ $progressPercent }}%; background: {{ $kid->color }};"></div>
                                    </div>
                                    <span class="goal-progress-text">{{ number_format($progressPercent, 0) }}%</span>
                                </div>
                                <div class="item-amount-cell" style="font-size: 13px; color: #6b7280;">${{ number_format($goal->current_amount, 2) }} of ${{ number_format($goal->target_amount, 2) }}</div>

                                @if($goal->status === 'pending_redemption')
                                    <div style="display: flex; align-items: center; gap: 6px; margin-left: auto;">
                                        <form id="approve-form-{{ $goal->id }}" action="{{ route('parent.goals.approve-redemption', $goal) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <button type="button" onclick="showApproveConfirmation('{{ $goal->id }}', '{{ $kid->name }}', '{{ addslashes($goal->title) }}', '{{ number_format($goal->current_amount, 2) }}')" class="btn-category-action" style="background: #10b981;">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <button type="button" class="btn-category-action" style="background: #ef4444;"
                                            onclick="showDashboardDenyModal('{{ $goal->id }}', '{{ $kid->name }}', '{{ addslashes($goal->title) }}')">
                                            <i class="fas fa-times"></i> Deny
                                        </button>
                                        <form id="dashboard-deny-form-{{ $goal->id }}" action="{{ route('parent.goals.deny-redemption', $goal) }}" method="POST" style="display:none;">
                                            @csrf
                                            <input type="hidden" name="denial_reason" id="dashboard-deny-reason-{{ $goal->id }}">
                                        </form>
                                    </div>
                                @elseif($goal->status === 'ready_to_redeem')
                                    <div style="display: flex; align-items: center; gap: 8px; margin-left: auto;">
                                        <form id="redeem-form-{{ $goal->id }}" action="{{ route('parent.goals.redeem', $goal) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <button type="button" onclick="showRedeemConfirmation('{{ $goal->id }}', '{{ $kid->name }}', '{{ addslashes($goal->title) }}', '{{ number_format($goal->current_amount, 2) }}')" class="btn-category-action" style="background: {{ $kid->color }};">
                                                <i class="fas fa-gift"></i> Redeem
                                            </button>
                                        </form>
                                        <a href="{{ route('parent.goals.show', $goal) }}" class="btn-category-action" style="background: #78909c;">
                                            View
                                        </a>
                                    </div>
                                @else
                                    <div style="display: flex; align-items: center; gap: 8px; margin-left: auto;">
                                        <button class="btn-category-action" onclick="toggleForm('goal-{{ $goal->id }}')" style="background: {{ $kid->color }};">
                                            Add Funds
                                        </button>
                                        <a href="{{ route('parent.goals.show', $goal) }}" class="btn-category-action" style="background: #78909c;">
                                            View Goal
                                        </a>
                                    </div>
                                @endif
                            </div>

                            @if($goal->status === 'active')
                                <!-- Add Funds Form -->
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

                        @if($activeGoals->count() > 4)
                            <div style="text-align: center;">
                                <a href="{{ route('kids.goals', $kid) }}" class="view-all-btn">
                                    View All Goals <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        @endif
                    @else
                        <!-- No Goals - Empty State -->
                        <div class="category-empty-state">
                            <span>No Goals created!</span>
                            <a href="{{ route('kids.goals', $kid) }}" class="btn-create-item">
                                <i class="fas fa-plus"></i> Create Goal
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Wishes Content (Collapsible) -->
                <div class="category-content" id="wishesContent{{ $kid->id }}" style="display: none;">
                    @if($recentWishes->count() > 0)
                        @foreach($recentWishes as $wish)
                            @php
                                $truncatedName = strlen($wish->item_name) > 50 ? substr($wish->item_name, 0, 50) . '...' : $wish->item_name;
                            @endphp
                            <div class="category-item-row {{ $wish->isPendingApproval() ? 'pending-attention' : '' }}">
                                @if($wish->image_path)
                                    <img src="{{ \Storage::url($wish->image_path) }}" alt="{{ $wish->item_name }}" class="item-thumbnail">
                                @endif
                                <div class="item-name-cell">
                                    {{ $truncatedName }}
                                    @if($wish->isPendingApproval())
                                        <span class="item-pending-badge">
                                            PENDING APPROVAL
                                        </span>
                                    @elseif($wish->isDeclined())
                                        <span class="item-declined-badge">
                                            DECLINED
                                        </span>
                                    @endif
                                </div>
                                <div class="item-amount-cell">${{ number_format($wish->price, 2) }}</div>

                                @if($wish->isPendingApproval())
                                    {{-- Hidden form submitted by approve modal --}}
                                    <form id="approve-wish-form-{{ $wish->id }}" action="{{ route('parent.wishes.approve', $wish) }}" method="POST" style="display: none;">
                                        @csrf
                                        <input type="hidden" name="adjusted_amount" id="approve-wish-amount-{{ $wish->id }}" value="{{ $wish->price }}">
                                    </form>
                                    <div style="display: flex; align-items: center; gap: 8px; margin-left: auto;">
                                        <button
                                            onclick="openApproveWishModal('{{ $wish->id }}', '{{ addslashes($wish->item_name) }}', '{{ number_format($wish->price, 2) }}', '{{ addslashes($kid->name) }}', '{{ number_format($kid->balance, 2) }}')"
                                            class="btn-category-action btn-approve">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <a href="{{ route('parent.wishes.show', $wish) }}" class="btn-category-action btn-view">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </div>
                                @elseif($wish->isSaved())
                                    {{-- Hidden form submitted by redeem modal --}}
                                    <form id="dashboard-redeem-form-{{ $wish->id }}" action="{{ route('parent.wishes.redeem', $wish) }}" method="POST" style="display: none;">
                                        @csrf
                                        <input type="hidden" name="adjusted_amount" id="dashboard-redeem-amount-{{ $wish->id }}" value="{{ $wish->price }}">
                                    </form>
                                    <div style="display: flex; align-items: center; gap: 8px; margin-left: auto;">
                                        <button
                                            onclick="openDashboardRedeemModal('{{ $wish->id }}', '{{ addslashes($wish->item_name) }}', {{ $wish->price }}, {{ $kid->balance }})"
                                            class="btn-category-action btn-redeem-wish">
                                            <i class="fas fa-gift"></i> Redeem
                                        </button>
                                        <a href="{{ route('parent.wishes.show', $wish) }}" class="btn-category-action btn-view">
                                            View
                                        </a>
                                    </div>
                                @else
                                    <a href="{{ route('parent.wishes.show', $wish) }}" class="btn-category-action btn-view">
                                        View
                                    </a>
                                @endif
                            </div>
                        @endforeach

                        @if($recentWishes->count() > 4)
                            <div style="text-align: center;">
                                <a href="{{ route('kids.wishes', $kid) }}" class="view-all-btn">
                                    View All Wishes <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        @endif
                    @else
                        <!-- No Wishes - Empty State -->
                        <div class="category-empty-state">
                            <span>No Wishes created!</span>
                            <a href="{{ route('parent.wishes.create', $kid) }}" class="btn-create-item">
                                <i class="fas fa-plus"></i> Create Wish
                            </a>
                        </div>
                    @endif
                </div>

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

    <!-- Deny Goal Redemption Modal -->
    <div id="dashboardDenyModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 10000; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 12px; padding: 24px; max-width: 440px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: #ef4444; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-times" style="color: white; font-size: 22px;"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 600; color: #111827;">Deny Fulfillment</h3>
                    <p id="dashboardDenyGoalTitle" style="margin: 4px 0 0 0; font-size: 14px; color: #6b7280;"></p>
                </div>
            </div>
            <p id="dashboardDenyMessage" style="margin: 0 0 16px 0; color: #374151; font-size: 14px; line-height: 1.5;"></p>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">Reason <span style="color:#9ca3af; font-weight:400;">(optional — shown to your kid)</span></label>
                <textarea id="dashboardDenyReasonInput" placeholder="e.g. Let's wait until after your birthday..." rows="3"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; color: #111827; resize: vertical; box-sizing: border-box; font-family: inherit;"></textarea>
            </div>
            <div style="display: flex; gap: 12px;">
                <button onclick="closeDashboardDenyModal()" style="flex: 1; padding: 10px 16px; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                    Cancel
                </button>
                <button onclick="confirmDashboardDeny()" style="flex: 1; padding: 10px 16px; background: #ef4444; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-times"></i> Deny
                </button>
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

    {{-- All modal JS is consolidated at the bottom of this section, after all modal HTML --}}

    <!-- Approve Wish Modal -->
    <div id="approveWishModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 16px; padding: 28px; width: 90%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.15);">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-check" style="color: white; font-size: 20px;"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #111827;">Approve Purchase</h3>
                    <p id="approveWishName" style="margin: 4px 0 0 0; font-size: 14px; color: #6b7280; font-weight: 500;"></p>
                </div>
            </div>

            <!-- Item price row -->
            <div style="background: #f9fafb; border-radius: 8px; padding: 12px 16px; margin-bottom: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 14px; font-weight: 600; color: #4b5563;">Item Price:</span>
                    <span id="approveWishPrice" style="font-size: 18px; font-weight: 700; color: #1f2937;"></span>
                </div>
            </div>

            <!-- Additional Fees Input -->
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">
                    Additional Fees <span style="font-weight: 400; color: #9ca3af;">(shipping, tax, etc.)</span>
                </label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280; font-weight: 600;">$</span>
                    <input type="text" id="approveWishFees" placeholder="0.00"
                        style="width: 100%; padding: 10px 10px 10px 24px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 15px; font-weight: 600; color: #1f2937; box-sizing: border-box;"
                        oninput="approveWishUpdateTotal()">
                </div>
            </div>

            <!-- Balance breakdown -->
            <div style="background: #f9fafb; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 5px 0;">
                    <span style="font-size: 14px; font-weight: 600; color: #4b5563;">Total Deducted:</span>
                    <span id="approveWishTotal" style="font-size: 18px; font-weight: 700; color: #ef4444;"></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 5px 0;">
                    <span style="font-size: 14px; font-weight: 600; color: #4b5563;"><span id="approveWishKidName"></span>'s Balance:</span>
                    <span id="approveWishBalance" style="font-size: 18px; font-weight: 700; color: #3b82f6;"></span>
                </div>
                <div style="border-top: 1px solid #e5e7eb; margin: 8px 0;"></div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 5px 0;">
                    <span style="font-size: 14px; font-weight: 600; color: #4b5563;">Balance After:</span>
                    <span id="approveWishAfter" style="font-size: 18px; font-weight: 700;"></span>
                </div>
            </div>

            <div style="display: flex; gap: 12px;">
                <button onclick="closeApproveWishModal()" style="flex: 1; padding: 11px 16px; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                    Cancel
                </button>
                <button onclick="confirmApproveWish()" style="flex: 1; padding: 11px 16px; background: #10b981; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-check"></i> Approve & Deduct
                </button>
            </div>
        </div>
    </div>

    <!-- Decline Wish Modal -->
    <div id="declineWishModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 16px; padding: 28px; width: 90%; max-width: 440px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.15);">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: #ef4444; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-times" style="color: white; font-size: 20px;"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #111827;">Decline Request</h3>
                    <p id="declineWishModalTitle" style="margin: 4px 0 0 0; font-size: 14px; color: #6b7280; font-weight: 500;"></p>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label id="declineWishModalSubtitle" style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;"></label>
                <textarea id="declineWishReasonInput" placeholder="e.g. Let's save up a bit more first..." rows="3"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; color: #111827; resize: vertical; box-sizing: border-box; font-family: inherit;"></textarea>
            </div>

            <div style="display: flex; gap: 12px;">
                <button onclick="closeDeclineWishModal()" style="flex: 1; padding: 11px 16px; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                    Cancel
                </button>
                <button onclick="confirmDeclineWish()" style="flex: 1; padding: 11px 16px; background: #ef4444; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-times"></i> Decline Request
                </button>
            </div>
        </div>
    </div>

    <!-- Redeem Wish Modal -->
    <div id="dashboardRedeemWishModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.75); z-index: 10000; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 16px; padding: 32px; width: 90%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); text-align: center;">
            <div style="width: 64px; height: 64px; margin: 0 auto 16px; background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 28px;">
                <i class="fas fa-gift"></i>
            </div>
            <h3 style="font-size: 24px; font-weight: 700; color: #1f2937; margin: 0 0 8px 0;">Redeem Wish?</h3>
            <p id="dashboardRedeemWishName" style="color: #6b7280; font-size: 15px; margin-bottom: 16px; font-weight: 500;"></p>

            <!-- Item Price Display -->
            <div style="background: #f9fafb; border-radius: 8px; padding: 12px; margin-bottom: 16px;">
                <div style="display: flex; justify-content: center; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <span style="font-weight: 600; color: #4b5563; font-size: 15px;">Item Price:</span>
                    <span id="dashboardRedeemItemPrice" style="font-size: 18px; font-weight: 700; color: #1f2937;"></span>
                    <div id="dashboardRedeemAdjustedContainer" style="display: none; align-items: center; gap: 8px;">
                        <span id="dashboardRedeemAdjustedPrice" style="font-size: 18px; font-weight: 700; color: #10b981;"></span>
                        <span style="font-size: 12px; color: #6b7280; white-space: nowrap;">(adjusted price)</span>
                    </div>
                </div>
            </div>

            <!-- Additional Fees Input -->
            <div style="margin-bottom: 20px; text-align: left;">
                <label style="display: block; font-weight: 600; color: #4b5563; font-size: 14px; margin-bottom: 8px;">
                    Additional Fees (shipping, tax, etc.)
                </label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280; font-weight: 600;">$</span>
                    <input type="text" id="dashboardRedeemFees" placeholder="0.00"
                        style="width: 100%; padding: 10px 10px 10px 24px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px; font-weight: 600; color: #1f2937; box-sizing: border-box;"
                        oninput="dashboardRedeemUpdateTotal()">
                </div>
            </div>

            <!-- Balance Preview -->
            <div style="background: #f9fafb; border-radius: 12px; padding: 20px; margin-bottom: 24px; text-align: left;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0;">
                    <span style="font-weight: 600; color: #4b5563; font-size: 15px;">Current Balance:</span>
                    <span id="dashboardRedeemCurrentBalance" style="font-size: 20px; font-weight: 700; color: #3b82f6;"></span>
                </div>
                <div style="display: flex; justify-content: center; padding: 8px 0; color: #9ca3af; font-size: 18px;">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0;">
                    <span style="font-weight: 600; color: #4b5563; font-size: 15px;">After Purchase:</span>
                    <span id="dashboardRedeemAfterBalance" style="font-size: 20px; font-weight: 700;"></span>
                </div>
            </div>

            <div style="display: flex; gap: 12px;">
                <button onclick="closeDashboardRedeemWishModal()" style="flex: 1; padding: 12px 16px; background: #e5e7eb; color: #4b5563; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;">
                    Cancel
                </button>
                <button onclick="confirmDashboardRedeemWish()" style="flex: 1; padding: 12px 16px; background: #8b5cf6; color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-gift"></i> Confirm Purchase
                </button>
            </div>
        </div>
    </div>

    <style>
        .btn-redeem-wish {
            background: #8b5cf6;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: background 0.2s;
        }
        .btn-redeem-wish:hover { background: #7c3aed; }
    </style>

    <script>
        let dashboardRedeemWishId = null;
        let dashboardRedeemData = { basePrice: 0, currentBalance: 0 };

        function openDashboardRedeemModal(wishId, wishName, price, balance) {
            dashboardRedeemWishId = wishId;
            dashboardRedeemData.basePrice = price;
            dashboardRedeemData.currentBalance = balance;

            document.getElementById('dashboardRedeemWishName').textContent = wishName;
            document.getElementById('dashboardRedeemItemPrice').textContent = '$' + price.toFixed(2);
            document.getElementById('dashboardRedeemCurrentBalance').textContent = '$' + balance.toFixed(2);
            document.getElementById('dashboardRedeemFees').value = '';
            document.getElementById('dashboardRedeemAdjustedContainer').style.display = 'none';

            const afterBalance = balance - price;
            const afterEl = document.getElementById('dashboardRedeemAfterBalance');
            afterEl.textContent = '$' + afterBalance.toFixed(2);
            afterEl.style.color = afterBalance < 0 ? '#ef4444' : '#10b981';

            document.getElementById('dashboard-redeem-amount-' + wishId).value = price.toFixed(2);
            document.getElementById('dashboardRedeemWishModal').style.display = 'flex';
        }

        function closeDashboardRedeemWishModal() {
            document.getElementById('dashboardRedeemWishModal').style.display = 'none';
            dashboardRedeemWishId = null;
            dashboardRedeemData = { basePrice: 0, currentBalance: 0 };
        }

        function dashboardRedeemUpdateTotal() {
            const input = document.getElementById('dashboardRedeemFees');
            let value = input.value.replace(/[^0-9]/g, '');

            if (value === '') {
                input.value = '';
                document.getElementById('dashboardRedeemAdjustedContainer').style.display = 'none';
                const afterBalance = dashboardRedeemData.currentBalance - dashboardRedeemData.basePrice;
                const afterEl = document.getElementById('dashboardRedeemAfterBalance');
                afterEl.textContent = '$' + afterBalance.toFixed(2);
                afterEl.style.color = afterBalance < 0 ? '#ef4444' : '#10b981';
                if (dashboardRedeemWishId) {
                    document.getElementById('dashboard-redeem-amount-' + dashboardRedeemWishId).value = dashboardRedeemData.basePrice.toFixed(2);
                }
                return;
            }

            const cents = parseInt(value);
            const dollars = (cents / 100).toFixed(2);
            input.value = dollars;

            const additionalFee = parseFloat(dollars);
            const adjustedTotal = dashboardRedeemData.basePrice + additionalFee;

            document.getElementById('dashboardRedeemAdjustedPrice').textContent = '$' + adjustedTotal.toFixed(2);
            document.getElementById('dashboardRedeemAdjustedContainer').style.display = 'flex';

            const afterBalance = dashboardRedeemData.currentBalance - adjustedTotal;
            const afterEl = document.getElementById('dashboardRedeemAfterBalance');
            afterEl.textContent = '$' + afterBalance.toFixed(2);
            afterEl.style.color = afterBalance < 0 ? '#ef4444' : '#10b981';

            if (dashboardRedeemWishId) {
                document.getElementById('dashboard-redeem-amount-' + dashboardRedeemWishId).value = adjustedTotal.toFixed(2);
            }
        }

        function confirmDashboardRedeemWish() {
            if (dashboardRedeemWishId) {
                document.getElementById('dashboard-redeem-form-' + dashboardRedeemWishId).submit();
            }
        }

        // Backdrop click — redeem wish
        const _redeemWishModal = document.getElementById('dashboardRedeemWishModal');
        if (_redeemWishModal) _redeemWishModal.addEventListener('click', function(e) {
            if (e.target === this) closeDashboardRedeemWishModal();
        });
    </script>

    {{-- ── Consolidated modal JS (runs after all modal HTML is in the DOM) ── --}}
    <script>
        // ── Goal Deny Modal ─────────────────────────────────────────────────
        let currentDashboardDenyGoalId = null;

        function showDashboardDenyModal(goalId, kidName, goalTitle) {
            currentDashboardDenyGoalId = goalId;
            document.getElementById('dashboardDenyGoalTitle').textContent = goalTitle;
            document.getElementById('dashboardDenyMessage').textContent =
                `The goal will remain active and ${kidName} can request again after 24 hours.`;
            document.getElementById('dashboardDenyReasonInput').value = '';
            document.getElementById('dashboardDenyModal').style.display = 'flex';
        }

        function closeDashboardDenyModal() {
            document.getElementById('dashboardDenyModal').style.display = 'none';
            currentDashboardDenyGoalId = null;
        }

        function confirmDashboardDeny() {
            if (currentDashboardDenyGoalId) {
                const reason = document.getElementById('dashboardDenyReasonInput').value.trim();
                document.getElementById('dashboard-deny-reason-' + currentDashboardDenyGoalId).value = reason;
                document.getElementById('dashboard-deny-form-' + currentDashboardDenyGoalId).submit();
            }
        }

        // ── Goal Redeem Confirmation Modal ──────────────────────────────────
        let currentRedeemFormId = null;

        function showRedeemConfirmation(goalId, kidName, goalTitle, amount) {
            currentRedeemFormId = 'redeem-form-' + goalId;
            document.getElementById('redeemGoalTitle').textContent = goalTitle;
            document.getElementById('redeemConfirmMessage').textContent =
                `This confirms that ${kidName} has received their item. The funds ($${amount}) will remain locked in the goal as a permanent record of the purchase.`;
            document.getElementById('redeemConfirmModal').style.display = 'flex';
        }

        function showApproveConfirmation(goalId, kidName, goalTitle, amount) {
            currentRedeemFormId = 'approve-form-' + goalId;
            document.getElementById('redeemGoalTitle').textContent = goalTitle;
            document.getElementById('redeemConfirmMessage').textContent =
                `${kidName} requested this goal! Approving confirms they have received their item. The funds ($${amount}) will remain locked in the goal as a permanent record.`;
            document.getElementById('redeemConfirmModal').style.display = 'flex';
        }

        function closeRedeemConfirmation() {
            const m = document.getElementById('redeemConfirmModal');
            if (m) m.style.display = 'none';
            currentRedeemFormId = null;
        }

        function confirmRedeem() {
            if (currentRedeemFormId) {
                document.getElementById(currentRedeemFormId).submit();
            }
        }

        // ── Approve Wish Modal ──────────────────────────────────────────────
        let currentApproveWishId = null;
        let approveWishBasePrice = 0;
        let approveWishBalance = 0;

        function openApproveWishModal(wishId, wishName, price, kidName, balance) {
            currentApproveWishId = wishId;
            approveWishBasePrice = parseFloat(price.replace(/,/g, ''));
            approveWishBalance   = parseFloat(balance.replace(/,/g, ''));

            document.getElementById('approveWishName').textContent    = wishName;
            document.getElementById('approveWishKidName').textContent = kidName;
            document.getElementById('approveWishPrice').textContent   = '$' + approveWishBasePrice.toFixed(2);
            document.getElementById('approveWishBalance').textContent = '$' + approveWishBalance.toFixed(2);
            document.getElementById('approveWishFees').value = '';

            document.getElementById('approveWishTotal').textContent = '$' + approveWishBasePrice.toFixed(2);
            const afterEl = document.getElementById('approveWishAfter');
            const after = approveWishBalance - approveWishBasePrice;
            afterEl.textContent = '$' + after.toFixed(2);
            afterEl.style.color = after >= 0 ? '#10b981' : '#ef4444';

            document.getElementById('approve-wish-amount-' + wishId).value = approveWishBasePrice.toFixed(2);
            document.getElementById('approveWishModal').style.display = 'flex';
        }

        function approveWishUpdateTotal() {
            const input = document.getElementById('approveWishFees');
            let value = input.value.replace(/[^0-9]/g, '');
            if (value === '') {
                input.value = '';
                const total = approveWishBasePrice;
                document.getElementById('approveWishTotal').textContent = '$' + total.toFixed(2);
                const after = approveWishBalance - total;
                const afterEl = document.getElementById('approveWishAfter');
                afterEl.textContent = '$' + after.toFixed(2);
                afterEl.style.color = after >= 0 ? '#10b981' : '#ef4444';
                if (currentApproveWishId) document.getElementById('approve-wish-amount-' + currentApproveWishId).value = total.toFixed(2);
                return;
            }
            const cents = parseInt(value);
            const dollars = (cents / 100).toFixed(2);
            input.value = dollars;
            const fees = parseFloat(dollars);
            const total = approveWishBasePrice + fees;
            document.getElementById('approveWishTotal').textContent = '$' + total.toFixed(2);
            const after = approveWishBalance - total;
            const afterEl = document.getElementById('approveWishAfter');
            afterEl.textContent = '$' + after.toFixed(2);
            afterEl.style.color = after >= 0 ? '#10b981' : '#ef4444';
            if (currentApproveWishId) document.getElementById('approve-wish-amount-' + currentApproveWishId).value = total.toFixed(2);
        }

        function closeApproveWishModal() {
            document.getElementById('approveWishModal').style.display = 'none';
            currentApproveWishId = null; approveWishBasePrice = 0; approveWishBalance = 0;
        }

        function confirmApproveWish() {
            if (currentApproveWishId) document.getElementById('approve-wish-form-' + currentApproveWishId).submit();
        }

        // ── Decline Wish Modal ──────────────────────────────────────────────
        let currentDeclineWishId = null;

        function openDeclineWishModal(wishId, kidName, wishName) {
            currentDeclineWishId = wishId;
            document.getElementById('declineWishModalTitle').textContent = wishName;
            document.getElementById('declineWishModalSubtitle').textContent = 'Let ' + kidName + ' know why (optional)';
            document.getElementById('declineWishReasonInput').value = '';
            document.getElementById('declineWishModal').style.display = 'flex';
        }

        function closeDeclineWishModal() {
            document.getElementById('declineWishModal').style.display = 'none';
            currentDeclineWishId = null;
        }

        function confirmDeclineWish() {
            if (currentDeclineWishId) {
                const reason = document.getElementById('declineWishReasonInput').value.trim();
                document.getElementById('decline-wish-reason-' + currentDeclineWishId).value = reason;
                document.getElementById('decline-wish-form-' + currentDeclineWishId).submit();
            }
        }

        // ── Backdrop click & ESC handlers (all modals safe — HTML exists above) ──
        document.getElementById('dashboardDenyModal').addEventListener('click', function(e) {
            if (e.target === this) closeDashboardDenyModal();
        });
        const _rcModal = document.getElementById('redeemConfirmModal');
        if (_rcModal) _rcModal.addEventListener('click', function(e) {
            if (e.target === this) closeRedeemConfirmation();
        });
        document.getElementById('approveWishModal').addEventListener('click', function(e) {
            if (e.target === this) closeApproveWishModal();
        });
        document.getElementById('declineWishModal').addEventListener('click', function(e) {
            if (e.target === this) closeDeclineWishModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Escape') return;
            const checks = [
                ['redeemConfirmModal',       closeRedeemConfirmation],
                ['dashboardDenyModal',        closeDashboardDenyModal],
                ['approveWishModal',          closeApproveWishModal],
                ['declineWishModal',          closeDeclineWishModal],
                ['dashboardRedeemWishModal',  closeDashboardRedeemWishModal],
            ];
            checks.forEach(([id, fn]) => {
                const el = document.getElementById(id);
                if (el && el.style.display === 'flex') fn();
            });
        });
    </script>
@endsection