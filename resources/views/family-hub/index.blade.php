@extends('layouts.parent')

@section('title', 'Family Hub - AllowanceLab')

@section('header-right')
    <a href="{{ route('dashboard') }}" class="back-to-dashboard-btn">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
@endsection

@section('content')
<style>
    .hub-container {
        padding: 32px 24px 60px;
        max-width: 1100px;
    }

    .hub-page-title {
        font-size: 26px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 24px;
        letter-spacing: -0.3px;
    }

    .hub-page-title span {
        color: #10b981;
    }

    /* ===== STATS BAR ===== */
    .hub-stats-bar {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 32px;
    }

    .hub-stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    }

    .hub-stat-label {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #94a3b8;
        margin-bottom: 8px;
    }

    .hub-stat-value {
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.5px;
    }

    .hub-stat-value.green { color: #10b981; }
    .hub-stat-value.alert { color: #f59e0b; }

    .hub-stat-sub {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 4px;
    }

    /* ===== KID SNAPSHOT CARDS ===== */
    .hub-kids-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 16px;
        margin-bottom: 32px;
    }

    .hub-kid-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 20px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        text-decoration: none;
        display: block;
        transition: box-shadow 0.2s, transform 0.2s;
    }

    .hub-kid-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        transform: translateY(-1px);
    }

    .hub-kid-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 16px;
    }

    .hub-kid-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 800;
        color: white;
        flex-shrink: 0;
    }

    .hub-kid-name {
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
    }

    .hub-kid-balance {
        font-size: 13px;
        font-weight: 600;
        color: #10b981;
    }

    .hub-kid-stats {
        display: flex;
        gap: 10px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }

    .hub-kid-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }

    .hub-kid-badge.points-high { background: #d1fae5; color: #059669; }
    .hub-kid-badge.points-mid  { background: #fef3c7; color: #d97706; }
    .hub-kid-badge.points-low  { background: #fee2e2; color: #dc2626; }
    .hub-kid-badge.allowance   { background: #f0fdf4; color: #16a34a; }

    .hub-kid-links {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .hub-kid-link {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        text-decoration: none;
        padding: 4px 10px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        transition: all 0.15s;
    }

    .hub-kid-link:hover {
        background: #f0fdf4;
        border-color: #10b981;
        color: #10b981;
    }

    /* ===== BOTTOM ROW ===== */
    .hub-bottom-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .hub-panel {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .hub-panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 20px;
        border-bottom: 1px solid #e5e7eb;
    }

    .hub-panel-title {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .hub-panel-title i { color: #10b981; }

    /* ===== ACTIVITY FEED ===== */
    .hub-activity-list { padding: 8px 0; }

    .hub-activity-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 20px;
        transition: background 0.15s;
    }

    .hub-activity-row:hover { background: #f8fafc; }

    .hub-activity-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .hub-activity-info { flex: 1; min-width: 0; }

    .hub-activity-kid {
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
    }

    .hub-activity-desc {
        font-size: 13px;
        color: #334155;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .hub-activity-right { text-align: right; flex-shrink: 0; }

    .hub-activity-amount {
        font-size: 14px;
        font-weight: 700;
    }

    .hub-activity-amount.deposit    { color: #10b981; }
    .hub-activity-amount.withdrawal { color: #ef4444; }

    .hub-activity-time {
        font-size: 11px;
        color: #94a3b8;
    }

    /* ===== GOAL PROGRESS ===== */
    .hub-goals-list { padding: 8px 0; }

    .hub-goal-row {
        padding: 12px 20px;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.15s;
    }

    .hub-goal-row:last-child { border-bottom: none; }
    .hub-goal-row:hover { background: #f8fafc; }

    .hub-goal-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
    }

    .hub-goal-kid-info {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .hub-goal-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .hub-goal-kid-name {
        font-size: 11px;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .hub-goal-status-badge {
        font-size: 10px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .hub-goal-status-badge.active           { background: #dbeafe; color: #2563eb; }
    .hub-goal-status-badge.ready_to_redeem  { background: #d1fae5; color: #059669; }
    .hub-goal-status-badge.pending_redemption { background: #fef3c7; color: #d97706; }

    .hub-goal-title {
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 6px;
    }

    .hub-goal-progress-bar {
        height: 6px;
        background: #e5e7eb;
        border-radius: 999px;
        overflow: hidden;
        margin-bottom: 4px;
    }

    .hub-goal-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: #10b981;
        transition: width 0.3s;
    }

    .hub-goal-amounts {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        color: #94a3b8;
    }

    /* ===== EMPTY STATE ===== */
    .hub-empty {
        text-align: center;
        padding: 40px 20px;
        color: #94a3b8;
    }

    .hub-empty i { font-size: 32px; margin-bottom: 10px; display: block; }
    .hub-empty p { font-size: 14px; }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 900px) {
        .hub-container { padding: 24px 16px 80px; }
        .hub-stats-bar { grid-template-columns: repeat(2, 1fr); }
        .hub-bottom-row { grid-template-columns: 1fr; }
    }

    @media (max-width: 540px) {
        .hub-stats-bar { grid-template-columns: repeat(2, 1fr); }
        .hub-stat-value { font-size: 22px; }
    }
</style>
<div class="hub-container">

    <h1 class="hub-page-title">Family <span>Hub</span></h1>

    {{-- ===== STATS BAR ===== --}}
    <div class="hub-stats-bar">
        <div class="hub-stat-card">
            <div class="hub-stat-label"><i class="fas fa-piggy-bank"></i> Total Balance</div>
            <div class="hub-stat-value green">${{ number_format($totalBalance, 2) }}</div>
            <div class="hub-stat-sub">across all kids</div>
        </div>
        <div class="hub-stat-card">
            <div class="hub-stat-label"><i class="fas fa-calendar-check"></i> Weekly Going Out</div>
            <div class="hub-stat-value">${{ number_format($weeklyAllowance, 2) }}</div>
            <div class="hub-stat-sub">in weekly allowances</div>
        </div>
        <div class="hub-stat-card">
            <div class="hub-stat-label"><i class="fas fa-bullseye"></i> Active Goals</div>
            <div class="hub-stat-value {{ $activeGoalsCount > 0 ? 'green' : '' }}">{{ $activeGoalsCount }}</div>
            <div class="hub-stat-sub">{{ $activeGoalsCount === 1 ? 'goal in progress' : 'goals in progress' }}</div>
        </div>
        <div class="hub-stat-card">
            <div class="hub-stat-label"><i class="fas fa-gift"></i> Pending Wishes</div>
            <div class="hub-stat-value {{ $pendingWishesCount > 0 ? 'alert' : '' }}">{{ $pendingWishesCount }}</div>
            <div class="hub-stat-sub">{{ $pendingWishesCount === 1 ? 'needs your approval' : 'need your approval' }}</div>
        </div>
    </div>

    {{-- ===== KID SNAPSHOT CARDS ===== --}}
    <div class="hub-kids-grid">
        @foreach($kids as $kid)
            @php
                $daysOfWeek = ['sunday'=>0,'monday'=>1,'tuesday'=>2,'wednesday'=>3,'thursday'=>4,'friday'=>5,'saturday'=>6];
                $targetDay  = $daysOfWeek[$kid->allowance_day] ?? 5;
                $daysUntil  = ($targetDay - now()->dayOfWeek + 7) % 7;
                if ($daysUntil === 0) $daysUntil = 7;
                $nextDate   = now()->addDays($daysUntil)->format('M j');
                $pointsPct  = $kid->max_points > 0 ? ($kid->points / $kid->max_points) * 100 : 0;
                $ptClass    = $pointsPct >= 70 ? 'points-high' : ($pointsPct >= 30 ? 'points-mid' : 'points-low');
            @endphp
            <div class="hub-kid-card">
                <div class="hub-kid-header">
                    <div class="hub-kid-avatar" style="background: {{ $kid->color }}">
                        {{ strtoupper(substr($kid->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="hub-kid-name">{{ $kid->name }}</div>
                        <div class="hub-kid-balance">${{ number_format($kid->balance, 2) }} available</div>
                    </div>
                </div>
                <div class="hub-kid-stats">
                    @if($kid->points_enabled)
                        <span class="hub-kid-badge {{ $ptClass }}">
                            <i class="fas fa-star" style="font-size:10px;"></i>
                            {{ $kid->points }}/{{ $kid->max_points }} pts
                        </span>
                    @endif
                    <span class="hub-kid-badge allowance">
                        <i class="fas fa-calendar" style="font-size:10px;"></i>
                        ${{ number_format($kid->allowance_amount, 2) }} on {{ $nextDate }}
                    </span>
                </div>
                <div class="hub-kid-links">
                    <a href="{{ route('kids.overview',  $kid) }}" class="hub-kid-link"><i class="fas fa-chart-pie"></i> Overview</a>
                    <a href="{{ route('kids.allowance', $kid) }}" class="hub-kid-link"><i class="fas fa-coins"></i> Allowance</a>
                    <a href="{{ route('kids.goals',     $kid) }}" class="hub-kid-link"><i class="fas fa-bullseye"></i> Goals</a>
                    <a href="{{ route('kids.wishes',    $kid) }}" class="hub-kid-link"><i class="fas fa-gift"></i> Wishes</a>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ===== BOTTOM ROW: Activity + Goals ===== --}}
    <div class="hub-bottom-row">

        {{-- Recent Activity Feed --}}
        <div class="hub-panel">
            <div class="hub-panel-header">
                <div class="hub-panel-title"><i class="fas fa-bolt"></i> Recent Activity</div>
            </div>
            @if($recentTransactions->isEmpty())
                <div class="hub-empty">
                    <i class="fas fa-receipt"></i>
                    <p>No transactions yet</p>
                </div>
            @else
                <div class="hub-activity-list">
                    @foreach($recentTransactions as $txn)
                        <div class="hub-activity-row">
                            <div class="hub-activity-dot" style="background: {{ $txn->kid->color }}"></div>
                            <div class="hub-activity-info">
                                <div class="hub-activity-kid">{{ $txn->kid->name }}</div>
                                <div class="hub-activity-desc">{{ $txn->description }}</div>
                            </div>
                            <div class="hub-activity-right">
                                <div class="hub-activity-amount {{ $txn->type }}">
                                    {{ $txn->type === 'deposit' ? '+' : '-' }}${{ number_format($txn->amount, 2) }}
                                </div>
                                <div class="hub-activity-time">{{ $txn->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Goal Progress Overview --}}
        <div class="hub-panel">
            <div class="hub-panel-header">
                <div class="hub-panel-title"><i class="fas fa-bullseye"></i> Goal Progress</div>
            </div>
            @if($activeGoals->isEmpty())
                <div class="hub-empty">
                    <i class="fas fa-flag"></i>
                    <p>No active goals yet</p>
                </div>
            @else
                <div class="hub-goals-list">
                    @foreach($activeGoals as $goal)
                        @php
                            $pct = $goal->getProgressPercentage();
                            $statusLabels = [
                                'active'             => 'Active',
                                'ready_to_redeem'    => 'Ready!',
                                'pending_redemption' => 'Pending',
                            ];
                        @endphp
                        <div class="hub-goal-row">
                            <div class="hub-goal-top">
                                <div class="hub-goal-kid-info">
                                    <div class="hub-goal-dot" style="background: {{ $goal->kid->color }}"></div>
                                    <span class="hub-goal-kid-name">{{ $goal->kid->name }}</span>
                                </div>
                                <span class="hub-goal-status-badge {{ $goal->status }}">
                                    {{ $statusLabels[$goal->status] ?? $goal->status }}
                                </span>
                            </div>
                            <div class="hub-goal-title">{{ $goal->title }}</div>
                            <div class="hub-goal-progress-bar">
                                <div class="hub-goal-progress-fill" style="width: {{ min($pct, 100) }}%; background: {{ $goal->kid->color }};"></div>
                            </div>
                            <div class="hub-goal-amounts">
                                <span>${{ number_format($goal->current_amount, 2) }} saved</span>
                                <span>{{ number_format($pct, 0) }}% of ${{ number_format($goal->target_amount, 2) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
