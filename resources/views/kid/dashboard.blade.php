@extends('layouts.kid')

@section('title', 'My Dashboard - AllowanceLab')

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

        // Create medium shade for parent transactions (70% white + 30% color)
        $mediumR = round($r * 0.28 + 255 * 0.72);
        $mediumG = round($g * 0.28 + 255 * 0.72);
        $mediumB = round($b * 0.28 + 255 * 0.72);
        $mediumShade = "rgb($mediumR, $mediumG, $mediumB)";
    @endphp

    <style>
        /* Dynamic theme color based on kid's selection */
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

        .kid-next-allowance .days-away {
            color:
                {{ $kid->color }}
                !important;
        }

        .kid-ledger-entry {
            border-left-color:
                {{ $kid->color }}
                !important;
            background:
                {{ $lightShade }}
                !important;
        }

        .kid-ledger-entry.parent-initiated {
            background:
                {{ $mediumShade }}
                !important;
        }

        .kid-ledger-entry.parent-initiated {
            background:
                {{ $mediumShade }}
                !important;
        }

        .kid-modal-ledger-entry.parent-initiated {
            background:
                {{ $mediumShade }}
                !important;
        }

        .kid-modal-ledger-entry.denied-allowance {
            background: #ffebee !important;
            border-left-color: #ef5350 !important;
        }

        .kid-parent-icon {
            font-size: 22px;
            font-weight: 900;
            color:
                {{ $kid->color }}
                !important;
            line-height: 1;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* Menu active state with theme color */
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

        /* Coming soon badge with theme color */
        .kid-sidebar .kid-coming-soon-badge {
            background:
                {{ $lightShade }}
                !important;
            color:
                {{ $kid->color }}
                !important;
        }

        /* Dynamic theme color based on kid's selection */
        .kid-header::after {
            background-color:
                {{ $kid->color }}
                !important;
        }

        .kid-modal-ledger-entry {
            border-left-color:
                {{ $kid->color }}
                !important;
            background:
                {{ $lightShade }}
                !important;
        }

        .kid-modal-ledger-entry:hover {
            background: color-mix(in srgb,
                    {{ $kid->color }}
                    20%, white) !important;
        }

        /* Theme colored border and shadow for main card */
        .kid-card {
            /* border: 1px solid
                                                                                                                                                                                                                {{ $kid->color }}
            !important;
            */ box-shadow: 0 4px 16px rgba({{ $r }},
                    {{ $g }}
                    ,
                    {{ $b }}
                    , 0.50) !important;
        }

        /* Parent legend styling */
        .kid-ledger-filters {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .kid-filter-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .kid-parent-legend {
            font-size: 13px;
            color: #666;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .kid-parent-icon-sample {
            font-size: 18px;
            font-weight: 900;
            line-height: 1;
        }

        .kid-ledger-entry.denied-allowance {
            background: #ffebee !important;
            border-left-color: #ef5350 !important;
        }

        @media (max-width: 900px) {
            .kid-parent-legend {
                width: 100%;
                justify-content: center;
                margin-top: 8px;
            }
        }
    </style>

    <!-- Mobile Welcome Section -->
    <div class="mobile-kid-welcome">
        <h1 class="mobile-kid-welcome-title">
            Step into the lab,<br><span class="kid-name-colored" style="color: {{ $kid->color }};">{{ $kid->name }}</span>.
        </h1>
        <p class="mobile-kid-welcome-subtitle">Let's grow that allowance beaker!</p>
    </div>

    <!-- Kid Card -->
    <div class="kid-card">
        <!-- Card Header -->
        <div class="kid-card-header">
            <div class="kid-avatar" style="background: {{ $kid->color }};">{{ strtoupper(substr($kid->name, 0, 1)) }}</div>
            <div class="kid-info">
                <h2 class="kid-name">{{ $kid->name }}</h2>
                <div class="kid-age">Age {{ \Carbon\Carbon::parse($kid->birthday)->age }}</div>
            </div>
        </div>

        @if($kid->points_enabled)
            <!-- Points Pill (upper right) -->
            <div class="kid-points-pill" id="kidPointsPill">
                <div class="kid-points-pill-label">Points</div>
                <div class="kid-points-pill-value" id="kidPointsValue">{{ $kid->points }} / {{ $kid->max_points }}</div>
                <div class="kid-points-pill-message" id="kidPointsMessage"></div>
            </div>
        @endif

        <!-- Balance Section -->
        <div class="kid-balance-section">
            <div class="kid-balance-amount {{ $kid->balance < 0 ? 'negative' : '' }}" id="kidBalance">
                ${{ number_format($kid->balance, 2) }}</div>
            @php
                $daysOfWeek = ['sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6];
                $targetDay = $daysOfWeek[$kid->allowance_day] ?? 5;
                $today = now();
                $daysUntil = ($targetDay - $today->dayOfWeek + 7) % 7;
                if ($daysUntil === 0)
                    $daysUntil = 7;
                $nextAllowance = $today->copy()->addDays($daysUntil);
            @endphp
            <div class="kid-next-allowance">
                @if($kid->points_enabled && $kid->points === 0)
                    <span style="color: #ef4444; font-weight: 600;">
                        Uh oh! You're at zero points. No allowance will be posted this {{ $nextAllowance->format('l, M j') }}.
                        <br>Do some extra chores or help out to earn points back!
                    </span>
                @else
                    Next allowance: ${{ number_format($kid->allowance_amount, 2) }} on {{ ucfirst($kid->allowance_day) }},
                    {{ $nextAllowance->format('M j') }}<br>
                    Only <span class="days-away">{{ $daysUntil }}</span> more days away!
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="kid-action-buttons">
            <button class="kid-action-btn kid-deposit-btn" onclick="kidOpenDepositForm()">Record Deposit</button>
            <button class="kid-action-btn kid-spend-btn" onclick="kidOpenSpendForm()">Record Spend</button>
            <button class="kid-action-btn kid-ledger-btn" id="kidLedgerBtn" onclick="kidToggleLedger()">View Ledger</button>
        </div>

        <!-- Deposit Form -->
        <div class="kid-form-container" id="kidDepositForm">
            <form onsubmit="kidSubmitDeposit(event)" class="kid-form-row">
                <div class="kid-form-group">
                    <label class="kid-form-label">Amount</label>
                    <input type="text" id="kidDepositAmount" class="kid-form-input" placeholder="$0.00"
                        oninput="kidFormatCurrency(this)">
                    <div id="kidDepositAmountError" class="kid-error-message">Please enter an amount</div>
                </div>
                <div class="kid-form-group">
                    <label class="kid-form-label">Note</label>
                    <input type="text" id="kidDepositNote" class="kid-form-input" placeholder="What's this for?">
                    <div id="kidDepositNoteError" class="kid-error-message">Please add a note</div>
                </div>
                <div class="kid-submit-btn-wrapper">
                    <button type="submit" class="kid-submit-btn kid-submit-deposit">Record Deposit</button>
                </div>
            </form>
        </div>

        <!-- Spend Form -->
        <div class="kid-form-container" id="kidSpendForm">
            <form onsubmit="kidSubmitSpend(event)" class="kid-form-row">
                <div class="kid-form-group">
                    <label class="kid-form-label">Amount</label>
                    <input type="text" id="kidSpendAmount" class="kid-form-input" placeholder="$0.00"
                        oninput="kidFormatCurrency(this)">
                    <div id="kidSpendAmountError" class="kid-error-message">Please enter an amount</div>
                </div>
                <div class="kid-form-group">
                    <label class="kid-form-label">Note</label>
                    <input type="text" id="kidSpendNote" class="kid-form-input" placeholder="What did you buy?">
                    <div id="kidSpendNoteError" class="kid-error-message">Please add a note</div>
                </div>
                <div class="kid-submit-btn-wrapper">
                    <button type="submit" class="kid-submit-btn kid-submit-spend">Record Spend</button>
                </div>
            </form>
        </div>

        <!-- Ledger Section -->
        <div class="kid-ledger-section" id="kidLedgerSection">
            <div class="kid-ledger-header">
                <h2 class="kid-ledger-title">All Transactions</h2>
            </div>

            <div class="kid-ledger-filters">
                <div class="kid-filter-buttons">
                    <button class="kid-filter-btn active" onclick="kidFilterLedger('all')">All</button>
                    <button class="kid-filter-btn" onclick="kidFilterLedger('deposit')">Deposits</button>
                    <button class="kid-filter-btn" onclick="kidFilterLedger('spend')">Spends</button>
                    @if($kid->points_enabled)
                        <button class="kid-filter-btn" onclick="kidFilterLedger('points')">Points</button>
                    @endif
                </div>
                <div class="kid-parent-legend">
                    <span class="kid-parent-icon-sample" style="color: {{ $kid->color }};">P</span> = Parent initiated this
                    transaction
                </div>
            </div>

            <div class="kid-ledger-table" id="kidLedgerTable">
                <!-- Transactions will be rendered here by JavaScript -->
            </div>
        </div>
    </div>



    <script>
        // Initialize kid data from Laravel - SET BEFORE JS MODULE LOADS
        window.kidBalance = {{ $kid->balance }};
        @if($kid->points_enabled)
            window.kidPoints = {{ $kid->points }};
        @endif
        window.kidLedgerData = @json($transactions);

        // Wait for DOMContentLoaded to ensure functions are loaded
        document.addEventListener('DOMContentLoaded', function () {
            // Force update balance display on load
            if (window.kidBalance !== undefined) {
                kidBalance = window.kidBalance;
                const balanceEl = document.getElementById('kidBalance');
                if (balanceEl && kidBalance !== 0) {
                    balanceEl.textContent = '$' + kidBalance.toFixed(2);
                }
            }

            // Load ledger data
            if (window.kidLedgerData !== undefined) {
                kidLedgerData = window.kidLedgerData;
            }

            if (typeof window.kidUpdatePointsDisplay === 'function') {
                window.kidUpdatePointsDisplay();
            }
            if (typeof window.kidRenderLedger === 'function') {
                window.kidRenderLedger();
            }
        });
    </script>

    <!-- Transaction Modal -->
    <div class="kid-transaction-modal" id="kidTransactionModal">
        <div class="kid-modal-backdrop" onclick="kidCloseTransactionModal()"></div>
        <div class="kid-modal-content">
            <div class="kid-modal-header">
                <h2>All Transactions</h2>
                <button class="kid-modal-close" onclick="kidCloseTransactionModal()">×</button>
            </div>

            <div class="kid-modal-filters">
                <div class="kid-modal-filter-tabs">
                    <button class="kid-modal-filter-btn active" onclick="kidModalFilter('all')">All</button>
                    <button class="kid-modal-filter-btn" onclick="kidModalFilter('deposit')">Deposits</button>
                    <button class="kid-modal-filter-btn" onclick="kidModalFilter('spend')">Spends</button>
                    <button class="kid-modal-filter-btn" onclick="kidModalFilter('points')">Points</button>
                </div>
                <select class="kid-modal-time-filter" id="kidModalTimeFilter" onchange="kidModalTimeFilter()">
                    <option value="all">All Time</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="year">This Year</option>
                </select>
            </div>

            <div class="kid-modal-body" id="kidModalBody">
                <!-- Transactions will be rendered here -->
            </div>
        </div>
    </div>

@endsection