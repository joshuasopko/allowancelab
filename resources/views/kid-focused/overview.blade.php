@extends('layouts.kid-focused')

@section('tab-title', 'Overview')

@section('header-right')
    <a href="{{ route('dashboard') }}" class="add-kid-btn" style="text-decoration: none;">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
@endsection

@section('content')
<div class="overview-grid">
    <!-- Balance Card -->
    <div class="overview-card balance-card">
        <div class="card-header">
            <h3><i class="fas fa-wallet"></i> Current Balance</h3>
        </div>
        <div class="card-body">
            <div class="balance-amount">${{ number_format($kid->balance, 2) }}</div>
            <div class="balance-actions">
                <button onclick="openDepositModal()" class="btn-quick-action btn-deposit">
                    <i class="fas fa-plus"></i> Add Money
                </button>
                <button onclick="openSpendModal()" class="btn-quick-action btn-spend">
                    <i class="fas fa-minus"></i> Spend
                </button>
            </div>
        </div>
    </div>

    <!-- Allowance Card -->
    <div class="overview-card allowance-card">
        <div class="card-header">
            <h3><i class="fas fa-calendar-alt"></i> Allowance</h3>
        </div>
        <div class="card-body">
            @if($kid->allowance_amount > 0)
                <div class="allowance-amount">${{ number_format($kid->allowance_amount, 2) }}</div>
                <div class="allowance-schedule">
                    Every {{ ucfirst($kid->allowance_day) }}
                </div>
                @php
                    $daysOfWeek = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                    $todayIndex = now()->dayOfWeek;
                    $allowanceDayIndex = array_search(strtolower($kid->allowance_day), $daysOfWeek);
                    $daysUntil = ($allowanceDayIndex - $todayIndex + 7) % 7;
                    if ($daysUntil === 0) {
                        $nextText = 'Today!';
                    } elseif ($daysUntil === 1) {
                        $nextText = 'Tomorrow';
                    } else {
                        $nextText = "In {$daysUntil} days";
                    }
                @endphp
                <div class="next-allowance">Next: <strong>{{ $nextText }}</strong></div>
            @else
                <p style="color: #6b7280;">No allowance configured</p>
                <a href="{{ route('kids.manage', $kid) }}" class="btn-settings">
                    <i class="fas fa-cog"></i> Set Up Allowance
                </a>
            @endif
        </div>
    </div>

    <!-- Goals Card -->
    <div class="overview-card goals-card">
        <div class="card-header">
            <h3><i class="fas fa-bullseye"></i> Active Goals <span class="goal-count-badge">{{ $activeGoals->count() }}</span></h3>
            <a href="{{ route('kids.goals', $kid) }}" class="view-all-link">View All</a>
        </div>
        <div class="card-body">
            @if($activeGoals->count() > 0)
                @foreach($activeGoals as $goal)
                    @php
                        $gProgress = $goal->target_amount > 0 ? ($goal->current_amount / $goal->target_amount) * 100 : 0;
                        $gProgress = min($gProgress, 100);
                        $gIsComplete = $gProgress >= 100;
                        $gIsDenied = $goal->denied_at && $goal->denial_reason;
                        $gBarColor = $gIsComplete ? '#10b981' : $kid->color;
                    @endphp
                    <div class="goal-card-item">
                        <div class="goal-card-inner">
                            @if($goal->photo_path)
                                <img src="{{ asset('storage/' . $goal->photo_path) }}" alt="{{ $goal->title }}" class="goal-card-thumb">
                            @else
                                <div class="goal-card-thumb goal-card-thumb-placeholder">
                                    <i class="fas fa-bullseye" style="color: {{ $gBarColor }};"></i>
                                </div>
                            @endif
                            <div class="goal-card-details">
                                <div class="goal-card-title">{{ Str::limit($goal->title, 40) }}</div>
                                <div class="goal-card-progress-bar">
                                    <div class="goal-card-progress-fill" style="width: {{ $gProgress }}%; background: {{ $gBarColor }};"></div>
                                </div>
                                <div class="goal-card-amounts">${{ number_format($goal->current_amount, 2) }} / ${{ number_format($goal->target_amount, 2) }}</div>
                                <div class="goal-card-actions">
                                    @if($gIsDenied)
                                        <span class="goal-card-badge goal-card-badge-denied">
                                            <i class="fas fa-ban"></i> Denied
                                        </span>
                                    @elseif($goal->status === 'pending_redemption')
                                        <span class="goal-card-badge goal-card-badge-pending">
                                            <i class="fas fa-clock"></i> Requested
                                        </span>
                                    @elseif($gIsComplete)
                                        <span class="goal-card-badge goal-card-badge-complete">
                                            <i class="fas fa-check-circle"></i> Complete!
                                        </span>
                                    @endif
                                    <a href="{{ route('parent.goals.show', $goal) }}" class="goal-card-btn goal-card-btn-view">
                                        <i class="fas fa-eye"></i> View Goal
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <p style="color: #6b7280; text-align: center; padding: 20px 0;">No active goals</p>
                <a href="{{ route('kids.goals', $kid) }}" class="btn-settings">
                    <i class="fas fa-plus"></i> Create Goal
                </a>
            @endif
        </div>
    </div>

    <!-- Wishes Card -->
    <div class="overview-card wishes-card">
        <div class="card-header">
            <h3><i class="fas fa-gift"></i> Wish List</h3>
            <a href="{{ route('kids.wishes', $kid) }}" class="view-all-link">View All</a>
        </div>
        <div class="card-body">
            @if($recentWishes->count() > 0)
                @foreach($recentWishes as $wish)
                    <div class="wish-card-item">
                        <div class="wish-card-inner">
                            {{-- Thumbnail --}}
                            @if($wish->image_path)
                                <img src="{{ asset('storage/' . $wish->image_path) }}" alt="{{ $wish->item_name }}" class="wish-card-thumb">
                            @else
                                <div class="wish-card-thumb wish-card-thumb-placeholder">
                                    <i class="fas fa-gift"></i>
                                </div>
                            @endif
                            {{-- Details --}}
                            <div class="wish-card-details">
                                <div class="wish-card-title">{{ Str::limit($wish->item_name, 40) }}</div>
                                <div class="wish-card-price">${{ number_format($wish->price, 2) }}</div>
                                <div class="wish-card-actions">
                                    @if($wish->status === 'pending_approval')
                                        <span class="wish-card-badge wish-card-badge-pending">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    @elseif($wish->status === 'declined')
                                        <span class="wish-card-badge wish-card-badge-declined">
                                            <i class="fas fa-times-circle"></i> Declined
                                        </span>
                                    @endif
                                    <a href="{{ route('parent.wishes.show', $wish) }}" class="wish-card-btn wish-card-btn-view">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <p style="color: #6b7280; text-align: center; padding: 20px 0;">No active wishes</p>
            @endif
            <a href="{{ route('kids.wishes', $kid) }}" class="btn-settings" style="margin-top: 12px;">
                <i class="fas fa-eye"></i> View All Wishes
            </a>
        </div>
    </div>

    <!-- Recent Activity Card -->
    <div class="overview-card activity-card">
        <div class="card-header">
            <h3><i class="fas fa-history"></i> Recent Activity</h3>
            <a href="{{ route('kids.allowance', $kid) }}" class="view-all-link">View All</a>
        </div>
        <div class="card-body">
            @if($recentTransactions->count() > 0)
                @foreach($recentTransactions as $transaction)
                    <div class="activity-item">
                        <div class="activity-icon {{ $transaction->type === 'deposit' ? 'deposit' : 'spend' }}">
                            <i class="fas {{ $transaction->type === 'deposit' ? 'fa-plus' : 'fa-minus' }}"></i>
                        </div>
                        <div class="activity-info">
                            <div class="activity-description">{{ $transaction->description }}</div>
                            <div class="activity-date">{{ $transaction->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="activity-amount {{ $transaction->type === 'deposit' ? 'positive' : 'negative' }}">
                            {{ $transaction->type === 'deposit' ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                        </div>
                    </div>
                @endforeach
            @else
                <p style="color: #6b7280; text-align: center; padding: 20px 0;">No recent activity</p>
            @endif
        </div>
    </div>
</div>

<!-- Manage Kid Settings Button -->
<div class="manage-settings-section">
    <a href="{{ route('kids.manage', $kid) }}" class="btn-manage-settings">
        <i class="fas fa-cog"></i>
        <span>Manage Kid Settings</span>
        <i class="fas fa-chevron-right"></i>
    </a>
</div>

<style>
.overview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
    margin-top: 32px;
    margin-bottom: 32px;
}

.overview-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.card-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.view-all-link {
    color: #3b82f6;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
}

.view-all-link:hover {
    text-decoration: underline;
}

.card-body {
    padding: 24px;
}

/* Balance Card */
.balance-amount {
    font-size: 48px;
    font-weight: 700;
    color: #10b981;
    text-align: center;
    margin-bottom: 24px;
}

.balance-actions {
    display: flex;
    gap: 12px;
}

.btn-quick-action {
    flex: 1;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-deposit {
    background: #10b981;
    color: white;
}

.btn-deposit:hover {
    background: #059669;
}

.btn-spend {
    background: #ef4444;
    color: white;
}

.btn-spend:hover {
    background: #dc2626;
}

/* Allowance Card */
.allowance-amount {
    font-size: 36px;
    font-weight: 700;
    color: #3b82f6;
    text-align: center;
    margin-bottom: 12px;
}

.allowance-schedule {
    text-align: center;
    color: #6b7280;
    font-size: 14px;
    margin-bottom: 12px;
}

.next-allowance {
    text-align: center;
    color: #6b7280;
    font-size: 14px;
}

/* Goals Card */
.goal-count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #e5e7eb;
    color: #6b7280;
    font-size: 12px;
    font-weight: 700;
    border-radius: 999px;
    padding: 2px 8px;
    margin-left: 6px;
    vertical-align: middle;
}

.goal-card-item {
    margin-bottom: 12px;
    border: 1px solid #f3f4f6;
    border-radius: 12px;
    overflow: hidden;
    background: #fafafa;
}
.goal-card-item:last-child {
    margin-bottom: 0;
}

.goal-card-inner {
    display: flex;
    gap: 12px;
    padding: 14px;
    align-items: flex-start;
}

.goal-card-thumb {
    width: 64px;
    height: 64px;
    border-radius: 8px;
    object-fit: cover;
    flex-shrink: 0;
}

.goal-card-thumb-placeholder {
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    font-size: 22px;
}

.goal-card-details {
    flex: 1;
    min-width: 0;
}

.goal-card-title {
    font-weight: 700;
    font-size: 14px;
    color: #1f2937;
    margin-bottom: 8px;
    line-height: 1.3;
}

.goal-card-progress-bar {
    height: 6px;
    background: #e5e7eb;
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 6px;
}

.goal-card-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #10b981);
    border-radius: 3px;
    transition: width 0.3s;
}

.goal-card-amounts {
    font-size: 13px;
    color: #6b7280;
    font-weight: 600;
    margin-bottom: 10px;
}

.goal-card-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.goal-card-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 14px;
    border-radius: 7px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    border: none;
    transition: opacity 0.15s;
}
.goal-card-btn:hover { opacity: 0.85; }

.goal-card-btn-view {
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #e5e7eb;
}

.goal-card-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 10px; border-radius: 6px;
    font-size: 11px; font-weight: 700;
    white-space: nowrap;
}
.goal-card-badge-denied  { background: #fee2e2; color: #dc2626; }
.goal-card-badge-pending { background: #fef3c7; color: #92400e; }
.goal-card-badge-complete { background: #d1fae5; color: #059669; }

/* Wishes Card */
.wish-card-item {
    margin-bottom: 12px;
    border: 1px solid #f3f4f6;
    border-radius: 12px;
    overflow: hidden;
    background: #fafafa;
}
.wish-card-item:last-child { margin-bottom: 0; }

.wish-card-inner {
    display: flex;
    gap: 12px;
    padding: 14px;
    align-items: flex-start;
}

.wish-card-thumb {
    width: 64px;
    height: 64px;
    border-radius: 8px;
    object-fit: cover;
    flex-shrink: 0;
}

.wish-card-thumb-placeholder {
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    font-size: 22px;
}

.wish-card-details {
    flex: 1;
    min-width: 0;
}

.wish-card-title {
    font-weight: 700;
    font-size: 14px;
    color: #1f2937;
    margin-bottom: 4px;
    line-height: 1.3;
}

.wish-card-price {
    font-size: 13px;
    font-weight: 600;
    color: #3b82f6;
    margin-bottom: 8px;
}

.wish-card-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
}

.wish-card-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 14px;
    border-radius: 7px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    border: none;
    transition: opacity 0.15s;
}
.wish-card-btn:hover { opacity: 0.85; }

.wish-card-btn-view {
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #e5e7eb;
}

.wish-card-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}
.wish-card-badge-pending  { background: #fef3c7; color: #92400e; }
.wish-card-badge-declined { background: #fee2e2; color: #991b1b; }

/* Activity Card */
.activity-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid #f3f4f6;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.activity-icon.deposit {
    background: #d1fae5;
    color: #059669;
}

.activity-icon.spend {
    background: #fee2e2;
    color: #dc2626;
}

.activity-info {
    flex: 1;
    min-width: 0;
}

.activity-description {
    font-weight: 500;
    color: #1f2937;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.activity-date {
    color: #9ca3af;
    font-size: 12px;
}

.activity-amount {
    font-weight: 700;
    font-size: 16px;
}

.activity-amount.positive {
    color: #10b981;
}

.activity-amount.negative {
    color: #ef4444;
}

.btn-settings {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: #f3f4f6;
    color: #4b5563;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    transition: background 0.2s;
}

.btn-settings:hover {
    background: #e5e7eb;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .overview-grid {
        grid-template-columns: 1fr;
    }

    .balance-amount {
        font-size: 36px;
    }

    .allowance-amount {
        font-size: 28px;
    }
}

/* Manage Settings Section */
.manage-settings-section {
    margin-top: 48px;
    margin-bottom: 32px;
    text-align: center;
}

.btn-manage-settings {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 16px 32px;
    background: #f3f4f6;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    color: #6b7280;
    text-decoration: none;
    font-size: 15px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.btn-manage-settings:hover {
    background: #e5e7eb;
    border-color: #d1d5db;
    color: #4b5563;
    transform: translateY(-1px);
}

.btn-manage-settings i:first-child {
    font-size: 16px;
}

.btn-manage-settings i:last-child {
    font-size: 12px;
    margin-left: auto;
}

@media (max-width: 768px) {
    .btn-manage-settings {
        width: 100%;
        justify-content: space-between;
        padding: 14px 20px;
    }
}
</style>
@endsection

@section('modals')
<!-- Deposit Modal -->
<div id="depositModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.75); z-index: 99999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 16px; padding: 32px; width: 90%; max-width: 440px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3);">
        <h3 style="font-size: 24px; font-weight: 700; margin-bottom: 16px; color: #1f2937;">Add Money</h3>
        <form action="{{ route('kids.deposit', $kid) }}" method="POST" class="kid-focused-modal-form">
            @csrf
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #4b5563; font-size: 14px; margin-bottom: 8px;">Amount</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280; font-weight: 600;">$</span>
                    <input type="text" name="amount" required oninput="handleDepositInput(event)" placeholder="0.00"
                           style="width: 100%; padding: 10px 10px 10px 24px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px;">
                </div>
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #4b5563; font-size: 14px; margin-bottom: 8px;">Description</label>
                <input type="text" name="note" placeholder="e.g., Birthday gift"
                       style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px;">
            </div>
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="button" onclick="closeDepositModal()"
                        style="flex: 1; padding: 12px; border: none; border-radius: 8px; font-weight: 600; background: #e5e7eb; color: #4b5563; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit"
                        style="flex: 1; padding: 12px; border: none; border-radius: 8px; font-weight: 600; background: #10b981; color: white; cursor: pointer;">
                    Add Money
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Spend Modal -->
<div id="spendModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.75); z-index: 99999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 16px; padding: 32px; width: 90%; max-width: 440px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3);">
        <h3 style="font-size: 24px; font-weight: 700; margin-bottom: 16px; color: #1f2937;">Record Spending</h3>
        <form action="{{ route('kids.spend', $kid) }}" method="POST" class="kid-focused-modal-form">
            @csrf
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #4b5563; font-size: 14px; margin-bottom: 8px;">Amount</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280; font-weight: 600;">$</span>
                    <input type="text" name="amount" required oninput="handleSpendInput(event)" placeholder="0.00"
                           style="width: 100%; padding: 10px 10px 10px 24px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px;">
                </div>
                <p style="font-size: 12px; color: #6b7280; margin-top: 4px;">Available: ${{ number_format($kid->balance, 2) }}</p>
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #4b5563; font-size: 14px; margin-bottom: 8px;">What did they buy?</label>
                <input type="text" name="note" placeholder="e.g., Toy, Snack" required
                       style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px;">
            </div>
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="button" onclick="closeSpendModal()"
                        style="flex: 1; padding: 12px; border: none; border-radius: 8px; font-weight: 600; background: #e5e7eb; color: #4b5563; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit"
                        style="flex: 1; padding: 12px; border: none; border-radius: 8px; font-weight: 600; background: #ef4444; color: white; cursor: pointer;">
                    Record Spending
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openDepositModal() {
    document.getElementById('depositModal').style.display = 'flex';
}

function closeDepositModal() {
    document.getElementById('depositModal').style.display = 'none';
}

function openSpendModal() {
    document.getElementById('spendModal').style.display = 'flex';
}

function closeSpendModal() {
    document.getElementById('spendModal').style.display = 'none';
}

// Currency input handler for deposit modal
function handleDepositInput(event) {
    const input = event.target;
    let value = input.value.replace(/[^0-9]/g, '');

    if (value === '') {
        input.value = '';
        return;
    }

    const numValue = parseInt(value);
    const dollars = Math.floor(numValue / 100);
    const cents = numValue % 100;
    input.value = `${dollars}.${cents.toString().padStart(2, '0')}`;
}

// Currency input handler for spend modal
function handleSpendInput(event) {
    const input = event.target;
    let value = input.value.replace(/[^0-9]/g, '');

    if (value === '') {
        input.value = '';
        return;
    }

    const numValue = parseInt(value);
    const dollars = Math.floor(numValue / 100);
    const cents = numValue % 100;
    input.value = `${dollars}.${cents.toString().padStart(2, '0')}`;
}

// Close modals on overlay click
document.addEventListener('DOMContentLoaded', function() {
    const depositModal = document.getElementById('depositModal');
    const spendModal = document.getElementById('spendModal');

    if (depositModal) {
        depositModal.addEventListener('click', function(e) {
            if (e.target === depositModal) {
                closeDepositModal();
            }
        });
    }

    if (spendModal) {
        spendModal.addEventListener('click', function(e) {
            if (e.target === spendModal) {
                closeSpendModal();
            }
        });
    }
});
</script>
@endsection
