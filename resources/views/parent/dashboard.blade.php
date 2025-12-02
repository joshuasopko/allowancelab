@extends('layouts.parent')

@section('title', 'Parent Dashboard - AllowanceLab')

@section('content')
    @if($kids->count() > 0)
        @foreach($kids as $kid)
            <div class="kid-card">
                <!-- Card Header -->
                <div class="card-header">
                    <div class="kid-info">
                        <div class="avatar" style="background: {{ $kid->color }};">{{ strtoupper(substr($kid->name, 0, 1)) }}</div>
                        <div>
                            <h2 class="kid-name">{{ $kid->name }}</h2>
                            <div class="kid-age">Age {{ \Carbon\Carbon::parse($kid->birthday)->age }}</div>

                            @php
                                $invite = $kid->invite;
                                $showPendingBadge = $invite && $invite->status === 'pending' && !$invite->isExpired();
                            @endphp

                            @if($showPendingBadge)
                                <div class="status-badge status-pending">
                                    <i class="fas fa-clock"></i> Invite Pending
                                </div>
                            @endif
                        </div>
                    </div>
                    @if($kid->points_enabled)
                        @php
                            $pointsPercent = $kid->max_points > 0 ? ($kid->points / $kid->max_points) * 100 : 0;
                            $pointsClass = $pointsPercent >= 80 ? 'points-high' : ($pointsPercent >= 50 ? 'points-medium' : 'points-low');
                        @endphp
                        <div class="points-badge {{ $pointsClass }}">{{ $kid->points }} / {{ $kid->max_points }}</div>
                    @endif
                </div>

                <!-- Balance Section -->
                <div class="balance-section">
                    <div class="balance {{ $kid->balance < 0 ? 'negative' : '' }}">${{ number_format($kid->balance, 2) }}</div>
                    @php
                        $daysOfWeek = ['sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6];
                        $targetDay = $daysOfWeek[$kid->allowance_day] ?? 5;
                        $today = now();
                        $daysUntil = ($targetDay - $today->dayOfWeek + 7) % 7;
                        if ($daysUntil === 0)
                            $daysUntil = 7;
                        $nextAllowance = $today->copy()->addDays($daysUntil);
                    @endphp
                    <div class="next-allowance">
                        @if($kid->points_enabled && $kid->points === 0)
                            <span style="color: #ef4444; font-weight: 600;">
                                {{ $kid->name }} is at 0 points. No allowance on {{ $nextAllowance->format('l, M j') }}.<br>Help them
                                find ways
                                to earn points back!
                            </span>
                        @else
                            Next allowance: ${{ number_format($kid->allowance_amount, 2) }} on
                            {{ ucfirst($kid->allowance_day) }}, {{ $nextAllowance->format('M j') }}
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button class="action-btn btn-deposit" onclick="toggleForm('deposit-{{ $kid->id }}')">Deposit Money</button>
                    <button class="action-btn btn-spend" onclick="toggleForm('spend-{{ $kid->id }}')">Record Spend</button>
                    @if($kid->points_enabled)
                        <button class="action-btn btn-points" onclick="toggleForm('points-{{ $kid->id }}')">Adjust Points</button>
                    @endif
                    <button class="action-btn btn-ledger" onclick="toggleForm('ledger-{{ $kid->id }}', this)">View Ledger</button>
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

                <!-- Card Footer -->
                <div class="card-footer">
                    <a href="{{ route('kids.manage', $kid) }}" class="manage-link">Manage Kid</a>
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
@endsection