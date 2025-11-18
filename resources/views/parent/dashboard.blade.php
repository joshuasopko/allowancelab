<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard - AllowanceLab</title>
    @vite('resources/css/dashboard.css')
</head>

<body>
    <!-- Top Header -->
    <header class="top-header">
        <div class="header-left">
            <div class="hamburger" onclick="toggleSidebar()">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div class="logo-section">
                <img src="{{ asset('images/Allowance-Lab-logo.png') }}" alt="AllowanceLab" class="logo-img">
            </div>
            <nav class="header-nav">
                <a href="#chore-list">Chore List</a>
                <a href="#goals">Goals</a>
                <a href="#loans">Loans</a>
                <a href="#jobs">Jobs</a>
            </nav>
        </div>
        <div class="header-right">
            <button class="add-kid-btn" onclick="openAddKidModal()">+ Add Kid</button>
        </div>
    </header>

    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Dashboard Container -->
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-welcome">Welcome, {{ $user->first_name }}!</div>
            <div class="sidebar-features">
                <a href="#chore-list" class="menu-item">Chore List</a>
                <a href="#goals" class="menu-item">Goals</a>
                <a href="#loans" class="menu-item">Loans</a>
                <a href="#jobs" class="menu-item">Jobs</a>
            </div>
            <nav class="sidebar-menu">
                <a href="#account-info" class="menu-item has-subtext">
                    Account Info
                    <div class="menu-subtext">{{ $user->last_name }} Family</div>
                </a>
                <a href="#dashboard" class="menu-item active">Dashboard</a>
                <a href="#settings" class="menu-item">Settings</a>
                <a href="#billing" class="menu-item">Billing</a>
                <a href="#help" class="menu-item">Help</a>
                <div class="menu-divider"></div>
                <a href="#family-settings" class="menu-item">Family Settings</a>
                <a href="#preferences" class="menu-item">Preferences</a>
                <div class="menu-divider"></div>
                <a href="#sign-out" class="menu-item sign-out">Sign Out</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-wrapper">
                @if($kids->count() > 0)
                    @foreach($kids as $kid)
                                                <!-- {{ $kid->name }} Card -->
                                                <div class="kid-card">
                                                    <div class="card-header">
                                                        <div class="kid-info">
                                                            <div class="avatar" style="background: {{ $kid->color }};">
                                                                {{ strtoupper(substr($kid->name, 0, 1)) }}
                                                            </div>
                                                            <div class="kid-details">
                                                                <h2>{{ $kid->name }}</h2>
                                                                <div class="kid-age">Age {{ \Carbon\Carbon::parse($kid->birthday)->age }}</div>
                                                            </div>
                                                        </div>
                                                        @if($kid->points_enabled)
                                                            @php
            $pointsClass = $kid->points >= 8 ? 'points-high' : ($kid->points >= 5 ? 'points-medium' : 'points-low');
                                                            @endphp
                                                            <div class="points-badge {{ $pointsClass }}">{{ $kid->points }} / 10</div>
                                                        @endif
                                                    </div>

                                                    <div class="balance-section">
                                                        <div class="balance {{ $kid->balance < 0 ? 'negative' : '' }}">
                                                            ${{ number_format($kid->balance, 2) }}</div>
                                                        <div class="next-allowance">Weekly allowance: ${{ number_format($kid->allowance_amount, 2) }}
                                                        </div>
                                                    </div>

                                                    <div class="action-buttons">
                                                        <button class="action-btn btn-deposit" onclick="toggleForm('deposit-{{ $kid->id }}')">Deposit
                                                            Money</button>

                                                        <!-- Deposit Form -->
                                                        <div class="dropdown-form" id="deposit-{{ $kid->id }}Form">
                                                            <div class="form-content">
                                                                <form action="{{ route('kids.deposit', $kid) }}" method="POST" class="inline-form">
                                                                    @csrf
                                                                    <div class="form-group">
                                                                        <label class="form-label">Amount</label>
                                                                        <input type="text" inputmode="decimal" class="form-input currency-input"
                                                                            name="amount" placeholder="0.00" required>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label class="form-label">Note:</label>
                                                                        <input type="text" class="form-input" name="note"
                                                                            placeholder="What was this for?">
                                                                    </div>
                                                                    <button type="submit" class="submit-btn submit-deposit">Record Deposit</button>
                                                                </form>
                                                            </div>
                                                        </div>

                                                        <button class="action-btn btn-spend" onclick="toggleForm('spend-{{ $kid->id }}')">Record
                                                            Spend</button>

                                                        <!-- Spend Form -->
                                                        <div class="dropdown-form" id="spend-{{ $kid->id }}Form">
                                                            <div class="form-content">
                                                                <form action="{{ route('kids.spend', $kid) }}" method="POST" class="inline-form">
                                                                    @csrf
                                                                    <div class="form-group">
                                                                        <label class="form-label">Amount</label>
                                                                        <input type="text" inputmode="decimal" class="form-input currency-input"
                                                                            name="amount" placeholder="0.00" required>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label class="form-label">Note:</label>
                                                                        <input type="text" class="form-input" name="note"
                                                                            placeholder="What did they buy?">
                                                                    </div>
                                                                    <button type="submit" class="submit-btn submit-spend">Record Spend</button>
                                                                </form>
                                                            </div>
                                                        </div>

                                                        @if($kid->points_enabled)
                                                            <button class="action-btn btn-points" onclick="toggleForm('points-{{ $kid->id }}')">Adjust
                                                                Points</button>

                                                            <!-- Points Form -->
                                                            <div class="dropdown-form" id="points-{{ $kid->id }}Form">
                                                                <div class="form-content">
                                                                    <div class="current-points">Current: {{ $kid->points }} / 10 points</div>
                                                                    <form action="{{ route('kids.points', $kid) }}" method="POST"
                                                                        class="points-form-inline">
                                                                        @csrf
                                                                        <div class="points-adjustment-row">
                                                                            <div class="points-control">
                                                                                <label class="points-label">Adjust</label>
                                                                                <div class="points-adjuster">
                                                                                    <button type="button" class="points-btn"
                                                                                        onclick="adjustPoints({{ $kid->id }}, -1)">−</button>
                                                                                    <input type="number" class="points-display" name="points"
                                                                                        id="points-{{ $kid->id }}" value="0" readonly>
                                                                                    <button type="button" class="points-btn"
                                                                                        onclick="adjustPoints({{ $kid->id }}, 1)">+</button>
                                                                                </div>
                                                                            </div>
                                                                            <div class="points-reason">
                                                                                <label class="points-label">Reason:</label>
                                                                                <input type="text" class="form-input" name="reason"
                                                                                    placeholder="Why are you adjusting points?" required>
                                                                            </div>
                                                                        </div>
                                                                        <button type="submit" class="submit-btn submit-points">Adjust Points</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        <button class="action-btn btn-ledger" onclick="toggleForm('ledger-{{ $kid->id }}')">View
                                                            Ledger</button>

                                                        <!-- Ledger -->
                                                        <div class="dropdown-form" id="ledger-{{ $kid->id }}Form">
                                                            <div class="form-content">
                                                                <div class="ledger-filters">
                                                                    <button class="filter-btn active" data-kid="{{ $kid->id }}"
                                                                        onclick="filterLedger({{ $kid->id }}, 'all')">All</button>
                                                                    <button class="filter-btn" data-kid="{{ $kid->id }}"
                                                                        onclick="filterLedger({{ $kid->id }}, 'deposit')">Deposits</button>
                                                                    <button class="filter-btn" data-kid="{{ $kid->id }}"
                                                                        onclick="filterLedger({{ $kid->id }}, 'spend')">Spends</button>
                                                                    <button class="filter-btn" data-kid="{{ $kid->id }}"
                                                                        onclick="filterLedger({{ $kid->id }}, 'points')">Point
                                                                        Adjustments</button>
                                                                </div>
                                                                <div class="ledger-table" id="ledger-{{ $kid->id }}-table">
                                                                    @php
        $transactions = $kid->transactions()->latest()->take(8)->get();
        $pointAdjustments = $kid->pointAdjustments()->latest()->take(8)->get();
        $allEntries = $transactions->concat($pointAdjustments)->sortByDesc('created_at')->take(8);
                                                                    @endphp

                                                                    @forelse($allEntries as $entry)
                                                                        <div class="ledger-row"
                                                                            data-type="{{ $entry instanceof \App\Models\Transaction ? $entry->type : 'points' }}">
                                                                            <div class="ledger-date">{{ $entry->created_at->format('M d, Y') }}</div>
                                                                            @if($entry instanceof \App\Models\Transaction)
                                                                                <div class="ledger-type">{{ ucfirst($entry->type) }}</div>
                                                                                <div class="ledger-amount {{ $entry->type }}">
                                                                                    ${{ number_format($entry->amount, 2) }}</div>
                                                                                <div class="ledger-note">{{ $entry->description ?? 'No note' }}</div>
                                                                            @else
                                                                                <div class="ledger-type">Points</div>
                                                                                <div
                                                                                    class="ledger-amount {{ $entry->points_change > 0 ? 'points-add' : 'points-deduct' }}">
                                                                                    {{ $entry->points_change > 0 ? '+' : '' }}{{ $entry->points_change }} pts
                                                                                </div>
                                                                                <div class="ledger-note">{{ $entry->reason ?? 'No reason' }}</div>
                                                                            @endif
                                                                        </div>
                                                                    @empty
                                                                        <div class="ledger-empty">No transactions yet</div>
                                                                    @endforelse
                                                                </div>

                        <button type="button" class="view-all-btn" onclick="openTransactionModal({{ $kid->id }})">View All Transactions</button>

                                                            </div>
                                                        </div>

                                                    </div>

                                                    <div class="card-footer">
                                                        <a href="#manage-{{ $kid->id }}" class="manage-link">Manage Kid</a>
                                                    </div>
                                                </div>
                    @endforeach
                @else
                    <div class="empty-state">
                        <h1 class="empty-state-title">Let's get started!</h1>
                        <button class="empty-state-btn" onclick="openAddKidModal()">+ Add Kid</button>
                    </div>
                @endif
            </div>
        </main>
    </div>

    <script>

        let currentFilter = 'all';
        let activeForm = null;

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        function toggleForm(formId) {
            const form = document.getElementById(formId + 'Form');

            if (activeForm && activeForm !== form) {
                activeForm.classList.remove('active');
            }

            form.classList.toggle('active');
            activeForm = form.classList.contains('active') ? form : null;
        }

        function formatCurrency(input) {
            let value = input.value.replace(/[^0-9]/g, '');
            if (value === '') {
                input.value = '';
                return;
            }
            let num = parseInt(value) / 100;
            input.value = '$' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function adjustPoints(delta) {
            const input = document.getElementById('pointsInput');
            let current = parseInt(input.value) || 7;
            let newValue = Math.max(0, Math.min(10, current + delta));
            input.value = newValue;
        }

        function updatePointsBadge(points) {
            const badge = document.getElementById('pointsBadge');
            badge.textContent = points + ' / 10';

            if (points >= 8) {
                badge.className = 'points-badge points-high';
            } else if (points >= 5) {
                badge.className = 'points-badge points-medium';
            } else {
                badge.className = 'points-badge points-low';
            }
        }

        function updateBalance(amount) {
            const balanceEl = document.getElementById('balance');
            let current = parseFloat(balanceEl.textContent.replace(/[$,]/g, ''));
            let newBalance = current + amount;
            balanceEl.textContent = '$' + newBalance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // Add or remove negative class
            if (newBalance < 0) {
                balanceEl.classList.add('negative');
            } else {
                balanceEl.classList.remove('negative');
            }
        }

        function submitDeposit() {
            const amount = document.getElementById('depositAmount').value;
            const note = document.getElementById('depositNote').value;
            const amountInput = document.getElementById('depositAmount');
            const amountError = document.getElementById('depositAmountError');
            const noteInput = document.getElementById('depositNote');
            const noteError = document.getElementById('depositNoteError');
            const btn = document.querySelector('.submit-deposit');

            if (!amount || amount === '$0.00' || parseFloat(amount.replace(/[$,]/g, '')) < 0.01) {
                amountInput.classList.add('error');
                amountError.classList.add('show');
                setTimeout(() => {
                    amountInput.classList.remove('error');
                }, 400);
                return;
            }

            if (!note.trim()) {
                amountError.classList.remove('show');  // Clear amount error if it was showing
                noteInput.classList.add('error');
                noteError.classList.add('show');
                setTimeout(() => {
                    noteInput.classList.remove('error');
                }, 400);
                return;
            }

            // Clear any previous errors
            amountError.classList.remove('show');
            noteError.classList.remove('show');

            const numAmount = parseFloat(amount.replace(/[$,]/g, ''));

            // Show loading spinner
            btn.innerHTML = '<span class="spinner"></span>';
            btn.classList.add('loading');
            btn.disabled = true;

            setTimeout(() => {
                updateBalance(numAmount);

                ledgerData.unshift({
                    type: 'deposit',
                    amount: numAmount,
                    note: note,
                    date: new Date().toISOString().split('T')[0]
                });

                // Show success feedback
                btn.classList.remove('loading');
                btn.textContent = '✓ Recorded!';
                btn.classList.add('success');

                // Wait, then close form with animation
                setTimeout(() => {
                    const form = document.getElementById('depositForm');
                    form.classList.add('closing');

                    setTimeout(() => {
                        form.classList.remove('open');
                        form.classList.remove('closing');
                        activeForm = null;

                        // Reset button and form
                        document.getElementById('depositAmount').value = '';
                        document.getElementById('depositNote').value = '';
                        amountError.classList.remove('show');
                        noteError.classList.remove('show');
                        btn.textContent = 'Record Deposit';
                        btn.classList.remove('success');
                        btn.disabled = false;
                    }, 400);
                }, 1500);
            }, 800);
        }

        function submitSpend() {
            const amount = document.getElementById('spendAmount').value;
            const note = document.getElementById('spendNote').value;
            const amountInput = document.getElementById('spendAmount');
            const amountError = document.getElementById('spendAmountError');
            const noteInput = document.getElementById('spendNote');
            const noteError = document.getElementById('spendNoteError');
            const btn = document.querySelector('.submit-spend');

            if (!amount || amount === '$0.00' || parseFloat(amount.replace(/[$,]/g, '')) < 0.01) {
                amountInput.classList.add('error');
                amountError.classList.add('show');
                setTimeout(() => {
                    amountInput.classList.remove('error');
                }, 400);
                return;
            }

            if (!note.trim()) {
                amountError.classList.remove('show');  // Clear amount error if it was showing
                noteInput.classList.add('error');
                noteError.classList.add('show');
                setTimeout(() => {
                    noteInput.classList.remove('error');
                }, 400);
                return;
            }

            // Clear any previous errors
            amountError.classList.remove('show');
            noteError.classList.remove('show');

            const numAmount = parseFloat(amount.replace(/[$,]/g, ''));

            // Show loading spinner
            btn.innerHTML = '<span class="spinner"></span>';
            btn.classList.add('loading');
            btn.disabled = true;

            setTimeout(() => {
                updateBalance(-numAmount);

                ledgerData.unshift({
                    type: 'spend',
                    amount: -numAmount,
                    note: note,
                    date: new Date().toISOString().split('T')[0]
                });

                // Show success feedback
                btn.classList.remove('loading');
                btn.textContent = '✓ Recorded!';
                btn.classList.add('success');

                // Wait, then close form with animation
                setTimeout(() => {
                    const form = document.getElementById('spendForm');
                    form.classList.add('closing');

                    setTimeout(() => {
                        form.classList.remove('open');
                        form.classList.remove('closing');
                        activeForm = null;

                        // Reset button and form
                        document.getElementById('spendAmount').value = '';
                        document.getElementById('spendNote').value = '';
                        amountError.classList.remove('show');
                        noteError.classList.remove('show');
                        btn.textContent = 'Record Spend';
                        btn.classList.remove('success');
                        btn.disabled = false;
                    }, 400);
                }, 1500);
            }, 800);
        }

        function submitPoints() {
            const input = document.getElementById('pointsInput').value;
            const reason = document.getElementById('pointsReason').value;
            const reasonInput = document.getElementById('pointsReason');
            const reasonError = document.getElementById('pointsReasonError');
            const btn = document.querySelector('.submit-points');

            if (!reason.trim()) {
                reasonInput.classList.add('error');
                reasonError.classList.add('show');
                setTimeout(() => {
                    reasonInput.classList.remove('error');
                }, 400);
                return;
            }

            // Clear any previous errors
            reasonError.classList.remove('show');

            let newPoints;
            if (input.startsWith('+') || input.startsWith('-')) {
                const delta = parseInt(input);
                newPoints = Math.max(0, Math.min(10, 7 + delta));
            } else {
                newPoints = Math.max(0, Math.min(10, parseInt(input)));
            }

            // Show loading spinner
            btn.innerHTML = '<span class="spinner"></span>';
            btn.classList.add('loading');
            btn.disabled = true;

            setTimeout(() => {
                updatePointsBadge(newPoints);

                ledgerData.unshift({
                    type: 'points',
                    amount: input.startsWith('+') || input.startsWith('-') ? input : (newPoints - 7 > 0 ? '+' : '') + (newPoints - 7),
                    note: reason,
                    date: new Date().toISOString().split('T')[0]
                });

                // Show success feedback
                btn.classList.remove('loading');
                btn.textContent = '✓ Adjusted!';
                btn.classList.add('success');

                // Wait, then close form with animation
                setTimeout(() => {
                    const form = document.getElementById('pointsForm');
                    form.classList.add('closing');

                    setTimeout(() => {
                        form.classList.remove('open');
                        form.classList.remove('closing');
                        activeForm = null;

                        // Reset button and form
                        document.getElementById('pointsInput').value = newPoints;
                        document.getElementById('pointsReason').value = '';
                        reasonError.classList.remove('show');
                        btn.textContent = 'Adjust Points';
                        btn.classList.remove('success');
                        btn.disabled = false;
                    }, 400);
                }, 1500);
            }, 800);
        }

        function filterLedger(filter) {
            currentFilter = filter;
            const buttons = document.querySelectorAll('.filter-btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            renderLedger();
        }

        function renderLedger() {
            const table = document.getElementById('ledgerTable');
            let filtered = currentFilter === 'all' ? ledgerData : ledgerData.filter(entry => entry.type === currentFilter);
            let displayed = filtered.slice(0, 8);

            table.innerHTML = displayed.map(entry => `
                <div class="ledger-entry">
                    <div class="entry-details">
                        <div class="entry-type ${entry.type}">
                            ${entry.type === 'deposit' ? 'Deposit' : entry.type === 'spend' ? 'Spend' : 'Point Adjustment'}
                        </div>
                        <div class="entry-note">${entry.note}</div>
                        <div class="entry-date">${formatDate(entry.date)}</div>
                    </div>
                    <div class="entry-amount ${entry.type}">
                        ${entry.type === 'points' ? entry.amount + ' pts' : (entry.amount >= 0 ? '+$' : '-$') + Math.abs(entry.amount).toFixed(2)}
                    </div>
                </div>
            `).join('');
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const options = { month: 'short', day: 'numeric', year: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        }

        function viewAllTransactions() {
            openTransactionModal();
        }

        // Modal Functions
        function closeTransactionModal() {
            const modal = document.getElementById('transactionModal');
            modal.classList.remove('active');
        }
        let modalTimeFilter = 'all';

        function filterByTimeRange(entry) {
            if (modalTimeFilter === 'all') return true;

            const entryDate = new Date(entry.date);
            const today = new Date();
            const currentMonth = today.getMonth();
            const currentYear = today.getFullYear();

            if (modalTimeFilter === 'thisMonth') {
                return entryDate.getMonth() === currentMonth && entryDate.getFullYear() === currentYear;
            } else if (modalTimeFilter === 'lastMonth') {
                const lastMonth = new Date(currentYear, currentMonth - 1);
                return entryDate.getMonth() === lastMonth.getMonth() && entryDate.getFullYear() === lastMonth.getFullYear();
            } else if (modalTimeFilter === 'last3Months') {
                const threeMonthsAgo = new Date(currentYear, currentMonth - 3);
                return entryDate >= threeMonthsAgo;
            }
            return true;
        }

        

        // Close modal when clicking outside
        document.addEventListener('click', function (e) {
            const modal = document.getElementById('transactionModal');
            if (e.target === modal) {
                closeTransactionModal();
            }
        });

// transaction modal
    let currentKidId = null;
    let currentTypeFilter = 'all';
    let currentDateRange = '30';

    function openTransactionModal(kidId) {
        currentKidId = kidId;
        document.getElementById('transactionModal').classList.add('active');
        loadTransactions();
    }

    function closeTransactionModal() {
        document.getElementById('transactionModal').classList.remove('active');
    }

    function filterTransactionModal(type) {
        currentTypeFilter = type;

        // Update active button
        document.querySelectorAll('.modal-filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');

        loadTransactions();
    }

    function filterByDateRange(range) {
        currentDateRange = range;
        loadTransactions();
    }

    async function loadTransactions() {
        const url = `/kids/${currentKidId}/transactions?type=${currentTypeFilter}&days=${currentDateRange}`;

        try {
            const response = await fetch(url);
            const data = await response.json();

            const body = document.getElementById('transactionModalBody');

            if (data.length === 0) {
                body.innerHTML = '<div style="text-align: center; padding: 40px; color: #999;">No transactions found</div>';
                return;
            }

            body.innerHTML = data.map(entry => `
            <div class="ledger-row">
                <div class="ledger-date">${entry.date}</div>
                <div class="ledger-type">${entry.type_label}</div>
                <div class="ledger-amount ${entry.amount_class || entry.type}">${entry.amount_display}</div>
                <div class="ledger-note">${entry.note || 'No note'}</div>
            </div>
        `).join('');

        } catch (error) {
            console.error('Error loading transactions:', error);
        }
    }

    </script>

    <!-- Add Kid Modal -->
    <div id="addKidModal" class="modal-overlay" onclick="closeModalOnOverlay(event)">
        <div class="modal-container">
            <div class="modal-header">
                <h2 class="modal-title">Add a Kid</h2>
                <p class="modal-subtitle">Enter your child's information to get started</p>
            </div>

            <form action="{{ route('kids.store') }}" method="POST" id="addKidForm">
                @csrf
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="kidName">Name</label>
                            <input type="text" name="name" id="kidName" class="form-input" placeholder="Enter name"
                                required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="kidUsername">Username</label>
                            <input type="text" name="username" id="kidUsername" class="form-input"
                                placeholder="For login" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="kidPassword">Password</label>
                            <input type="password" name="password" id="kidPassword" class="form-input"
                                placeholder="Kid's password" required minlength="4">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="kidBirthday">Birthday</label>
                            <input type="date" name="birthday" id="kidBirthday" class="form-input" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="allowanceAmount">Weekly Allowance</label>
                        <input type="text" inputmode="decimal" class="form-input currency-input" name="allowance_amount"
                            placeholder="0.00" required>
                    </div>

                    <div class="color-picker-section">
                        <label class="form-label">Avatar Color</label>
                        <div class="color-preview">
                            <div id="avatarPreview" class="avatar-preview" style="background: #80d4b0;">?</div>
                            <span id="colorLabel" style="font-size: 15px; color: #666;">Mint Green</span>
                        </div>
                        <input type="hidden" name="color" id="selectedColor" value="#80d4b0">
                        <input type="hidden" name="avatar" value="avatar1">
                        <div class="color-grid">
                            <div class="color-option selected" style="background: #80d4b0;" data-color="#80d4b0"
                                data-name="Mint Green" onclick="selectColor(this)"></div>
                            <div class="color-option" style="background: #ff9999;" data-color="#ff9999"
                                data-name="Coral" onclick="selectColor(this)"></div>
                            <div class="color-option" style="background: #b19cd9;" data-color="#b19cd9"
                                data-name="Lavender" onclick="selectColor(this)"></div>
                            <div class="color-option" style="background: #87ceeb;" data-color="#87ceeb"
                                data-name="Sky Blue" onclick="selectColor(this)"></div>
                            <div class="color-option" style="background: #ffb380;" data-color="#ffb380"
                                data-name="Peach" onclick="selectColor(this)"></div>
                            <div class="color-option" style="background: #e066a6;" data-color="#e066a6"
                                data-name="Magenta" onclick="selectColor(this)"></div>
                            <div class="color-option" style="background: #ffd966;" data-color="#ffd966"
                                data-name="Butter Yellow" onclick="selectColor(this)"></div>
                            <div class="color-option" style="background: #a8c686;" data-color="#a8c686"
                                data-name="Sage Green" onclick="selectColor(this)"></div>
                            <div class="color-option" style="background: #5ab9b3;" data-color="#5ab9b3" data-name="Teal"
                                onclick="selectColor(this)"></div>
                            <div class="color-option" style="background: #9bb7d4;" data-color="#9bb7d4"
                                data-name="Periwinkle" onclick="selectColor(this)"></div>
                            <div class="color-option" style="background: #ff9966;" data-color="#ff9966"
                                data-name="Tangerine" onclick="selectColor(this)"></div>
                            <div class="color-option" style="background: #d4a5d4;" data-color="#d4a5d4"
                                data-name="Lilac" onclick="selectColor(this)"></div>
                        </div>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" name="points_enabled" id="usePoints" class="checkbox-input" value="1"
                            checked>
                        <label class="checkbox-label" for="usePoints">
                            Use point system?
                            <span class="tooltip-icon">
                                ?
                                <span class="tooltip-text">Points encourage accountability. Kids start each week with
                                    full
                                    points.</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="modal-btn modal-btn-cancel"
                        onclick="closeAddKidModal()">Cancel</button>
                    <button type="submit" class="modal-btn modal-btn-submit">Add Kid</button>
                </div>
            </form>
        </div>
    </div>

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

    <script>
        let selectedColor = '#80d4b0';

        function openAddKidModal() {
            document.getElementById('addKidModal').classList.add('active');
        }

        function closeAddKidModal() {
            document.getElementById('addKidModal').classList.remove('active');
        }

        function closeModalOnOverlay(event) {
            if (event.target.id === 'addKidModal') {
                closeAddKidModal();
            }
        }

        function selectColor(element) {
            // Remove selected class from all color options
            document.querySelectorAll('.color-option').forEach(option => {
                option.classList.remove('selected');
            });

            // Add selected class to clicked option
            element.classList.add('selected');

            // Get color and name
            const color = element.getAttribute('data-color');
            const name = element.getAttribute('data-name');

            // Update preview
            document.getElementById('avatarPreview').style.background = color;
            document.getElementById('colorLabel').textContent = name;
            document.getElementById('selectedColor').value = color;

            selectedColor = color;
        }

        // Update avatar preview when name changes
        document.getElementById('kidName')?.addEventListener('input', function (e) {
            const preview = document.getElementById('avatarPreview');
            const name = e.target.value.trim();
            preview.textContent = name ? name.charAt(0).toUpperCase() : '?';
        });

        // Toggle forms (for deposit, spend, points)
        function toggleForm(formType) {
            const form = document.getElementById(formType + 'Form');
            const isOpen = form.classList.contains('active');

            // Close all forms
            document.querySelectorAll('.dropdown-form').forEach(f => {
                f.classList.remove('active');
            });

            // Toggle this form
            if (!isOpen) {
                form.classList.add('active');
            }
        }

        function filterLedger(kidId, type) {
            const table = document.getElementById(`ledger-${kidId}-table`);
            const rows = table.querySelectorAll('.ledger-row');
            const buttons = document.querySelectorAll(`.filter-btn[data-kid="${kidId}"]`);

            // Update active button
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            // Filter rows
            rows.forEach(row => {
                if (type === 'all' || row.dataset.type === type) {
                    row.style.display = 'grid';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function adjustPoints(kidId, change) {
            const input = document.getElementById('points-' + kidId);
            let currentValue = parseInt(input.value) || 0;
            input.value = currentValue + change;
        }

        function formatCurrencyInput(input) {
            // Get just the numbers
            let value = input.value.replace(/\D/g, '');

            // Convert to cents, then to dollars
            let numericValue = parseInt(value) || 0;
            let dollars = (numericValue / 100).toFixed(2);

            // Format with dollar sign
            input.value = '$' + dollars;
        }

        function addCurrencyListeners() {
            document.querySelectorAll('input[name="amount"], input[name="allowance_amount"]').forEach(input => {
                input.addEventListener('input', function () {
                    formatCurrencyInput(this);
                });
            });

            // Remove $ before any form submits
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function (e) {
                    // Find all currency inputs in this form
                    this.querySelectorAll('input[name="amount"], input[name="allowance_amount"]').forEach(input => {
                        input.value = input.value.replace(/[$,]/g, '');
                    });
                });
            });
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', addCurrencyListeners);

        function handleFormSubmit(form, successMessage) {
            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                const submitBtn = this.querySelector('.submit-btn');
                const originalText = submitBtn.textContent;

                // Show loading
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;

                // Get form data
                const formData = new FormData(this);

                try {
                    // Add minimum delay to show spinner
                    const [response] = await Promise.all([
                        fetch(this.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        }),
                        new Promise(resolve => setTimeout(resolve, 800)) // Minimum 800ms for spinner
                    ]);

                    const data = await response.json();

                    if (response.ok) {
                        // Transition from loading to success
                        submitBtn.classList.remove('loading');

                        // Small delay for smooth transition
                        setTimeout(() => {
                            submitBtn.classList.add('success');
                            submitBtn.textContent = successMessage;
                        }, 100);

                        // Update balance on page
                        if (data.new_balance !== undefined) {
                            const balanceEl = document.querySelector('.balance');
                            balanceEl.textContent = '$' + parseFloat(data.new_balance).toFixed(2);
                            balanceEl.classList.toggle('negative', data.new_balance < 0);
                        }

                        // Update points badge if points were adjusted
                        if (data.new_points !== undefined) {
                            const pointsBadge = document.querySelector('.points-badge');
                            if (pointsBadge) {
                                pointsBadge.textContent = data.new_points + ' / 10';

                                // Update color class
                                pointsBadge.classList.remove('points-low', 'points-medium', 'points-high');
                                if (data.new_points >= 8) {
                                    pointsBadge.classList.add('points-high');
                                } else if (data.new_points >= 5) {
                                    pointsBadge.classList.add('points-medium');
                                } else {
                                    pointsBadge.classList.add('points-low');
                                }
                            }
                        }

                        // Reset form after showing success for 1.5 seconds
                        setTimeout(() => {
                            // Clear all text inputs (not readonly ones)
                            this.querySelectorAll('input[type="text"]:not([readonly]), input[type="number"]:not([readonly])').forEach(input => {
                                input.value = '';
                            });

                            // Reset the points display specifically
                            const pointsDisplay = this.querySelector('input[name="points"]');
                            if (pointsDisplay) {
                                pointsDisplay.value = '0';
                            }

                            // Reset button with fade
                            submitBtn.style.color = 'transparent';

                            setTimeout(() => {
                                submitBtn.classList.remove('success');
                                submitBtn.textContent = originalText;
                                submitBtn.style.transform = 'scale(1)';

                                setTimeout(() => {
                                    submitBtn.style.color = '';
                                    submitBtn.disabled = false;

                                    // Reload page to update ledger
                                    setTimeout(() => {
                                        location.reload();
                                    }, 100);
                                }, 50);
                            }, 300);
                        }, 1500);

                    } else {
                        throw new Error(data.message || 'Something went wrong');
                    }
                } catch (error) {
                    submitBtn.classList.remove('loading');
                    submitBtn.textContent = 'Error - Try Again';
                    submitBtn.style.background = '#f44336';

                    setTimeout(() => {
                        submitBtn.textContent = originalText;
                        submitBtn.style.background = '';
                        submitBtn.disabled = false;
                    }, 2000);
                }
            });
        }

        // Initialize AJAX forms when page loads
        document.addEventListener('DOMContentLoaded', function () {
            // Deposit forms
            document.querySelectorAll('form[action*="deposit"]').forEach(form => {
                handleFormSubmit(form, '✓ Recorded!');
            });

            // Spend forms
            document.querySelectorAll('form[action*="spend"]').forEach(form => {
                handleFormSubmit(form, '✓ Recorded!');
            });

            // Points forms
            document.querySelectorAll('form[action*="points"]').forEach(form => {
                handleFormSubmit(form, '✓ Adjusted!');
            });
        });

        // Remember open forms before reload
        window.addEventListener('beforeunload', function () {
            const activeForm = document.querySelector('.dropdown-form.active');
            if (activeForm) {
                sessionStorage.setItem('reopenForm', activeForm.id);
            }
        });

        // Reopen form after page loads
        document.addEventListener('DOMContentLoaded', function () {
            const formToReopen = sessionStorage.getItem('reopenForm');
            if (formToReopen) {
                const form = document.getElementById(formToReopen);
                if (form) {
                    form.classList.add('active');
                }
                sessionStorage.removeItem('reopenForm');
            }
        });

    </script>

</body>

</html>