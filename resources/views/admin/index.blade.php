<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AllowanceLab Admin</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; padding: 32px 24px; }
        .header { margin-bottom: 32px; }
        .header h1 { font-size: 22px; font-weight: 700; color: #f8fafc; }
        .header p { font-size: 13px; color: #64748b; margin-top: 4px; }
        .section-title { font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #475569; margin-bottom: 12px; margin-top: 28px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; }
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 18px 20px; }
        .card-label { font-size: 12px; color: #64748b; margin-bottom: 6px; }
        .card-value { font-size: 28px; font-weight: 700; color: #f8fafc; line-height: 1; }
        .card-sub { font-size: 11px; color: #475569; margin-top: 4px; }
        .card.accent { border-color: #4f46e5; }
        .card.accent .card-value { color: #818cf8; }
        .card.green { border-color: #16a34a; }
        .card.green .card-value { color: #4ade80; }
        .footer { margin-top: 40px; font-size: 12px; color: #334155; text-align: center; }
    </style>
</head>
<body>

<div class="header">
    <h1>🔒 AllowanceLab Admin</h1>
    <p>Platform overview — {{ now()->format('F j, Y g:i A') }}</p>
</div>

{{-- Accounts --}}
<div class="section-title">Accounts</div>
<div class="grid">
    <div class="card accent">
        <div class="card-label">Parent Accounts</div>
        <div class="card-value">{{ number_format($stats['total_parents']) }}</div>
        <div class="card-sub">+{{ $stats['new_parents_7d'] }} last 7d &nbsp;·&nbsp; +{{ $stats['new_parents_30d'] }} last 30d</div>
    </div>
    <div class="card accent">
        <div class="card-label">Kid Accounts</div>
        <div class="card-value">{{ number_format($stats['total_kids']) }}</div>
        <div class="card-sub">+{{ $stats['new_kids_7d'] }} last 7d &nbsp;·&nbsp; +{{ $stats['new_kids_30d'] }} last 30d</div>
    </div>
    <div class="card">
        <div class="card-label">Families</div>
        <div class="card-value">{{ number_format($stats['total_families']) }}</div>
        <div class="card-sub">Unique family units</div>
    </div>
</div>

{{-- Active Users --}}
<div class="section-title">Active Users</div>
<div class="grid">
    <div class="card green">
        <div class="card-label">Parents — Last 7 Days</div>
        <div class="card-value">{{ number_format($stats['parents_active_7d']) }}</div>
        <div class="card-sub">Logged in within 7 days</div>
    </div>
    <div class="card green">
        <div class="card-label">Kids — Last 7 Days</div>
        <div class="card-value">{{ number_format($stats['kids_active_7d']) }}</div>
        <div class="card-sub">Logged in within 7 days</div>
    </div>
    <div class="card">
        <div class="card-label">Parents — Last 30 Days</div>
        <div class="card-value">{{ number_format($stats['parents_active_30d']) }}</div>
        <div class="card-sub">Logged in within 30 days</div>
    </div>
    <div class="card">
        <div class="card-label">Kids — Last 30 Days</div>
        <div class="card-value">{{ number_format($stats['kids_active_30d']) }}</div>
        <div class="card-sub">Logged in within 30 days</div>
    </div>
</div>

{{-- Platform Activity --}}
<div class="section-title">Platform Activity</div>
<div class="grid">
    <div class="card">
        <div class="card-label">Total Money Tracked</div>
        <div class="card-value">${{ number_format($stats['total_money_tracked'], 2) }}</div>
        <div class="card-sub">Sum of all deposits</div>
    </div>
    <div class="card">
        <div class="card-label">Total Transactions</div>
        <div class="card-value">{{ number_format($stats['total_transactions']) }}</div>
        <div class="card-sub">Deposits + withdrawals</div>
    </div>
    <div class="card">
        <div class="card-label">Goals Created</div>
        <div class="card-value">{{ number_format($stats['total_goals']) }}</div>
        <div class="card-sub">{{ number_format($stats['active_goals']) }} currently active</div>
    </div>
    <div class="card">
        <div class="card-label">Push Subscriptions</div>
        <div class="card-value">{{ number_format($stats['push_subscriptions']) }}</div>
        <div class="card-sub">Parent + kid devices</div>
    </div>
</div>

<div class="footer">AllowanceLab · Admin only · <a href="{{ route('dashboard') }}" style="color:#475569;">Back to Dashboard</a></div>

</body>
</html>
