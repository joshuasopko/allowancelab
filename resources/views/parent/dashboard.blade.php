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
                                    <div class="points-badge points-medium">{{ $kid->points }} / 10</div>
                                @endif
                            </div>

                            <div class="balance-section">
                                <div class="balance">${{ number_format($kid->balance, 2) }}</div>
                                <div class="next-allowance">Weekly allowance: ${{ number_format($kid->allowance_amount, 2) }}
                                </div>
                            </div>

                            <div class="action-buttons">
                                <button class="action-btn btn-deposit" onclick="toggleForm('deposit-{{ $kid->id }}')">Deposit
                                    Money</button>

                                <!-- Deposit Form -->
                                <div class="dropdown-form" id="deposit-{{ $kid->id }}Form">
                                    <div class="form-content">
                                        <form action="{{ route('kids.updateBalance', $kid) }}" method="POST"
                                            class="inline-form">
                                            @csrf
                                            @method('PATCH')
                                            <div class="form-group">
                                                <label class="form-label">Amount</label>
                                                <input type="number" step="0.01" class="form-input" name="amount"
                                                    placeholder="0.00" required>
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
                                                <input type="number" step="0.01" class="form-input" name="amount"
                                                    placeholder="0.00" required>
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
                                            <form action="{{ route('kids.updatePoints', $kid) }}" method="POST" class="inline-form">
                                                @csrf
                                                @method('PATCH')
                                                <div class="form-group">
                                                    <label class="form-label">Adjust (+/- number)</label>
                                                    <input type="number" class="form-input" name="points_change"
                                                        placeholder="+2 or -1" required>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">Reason:</label>
                                                    <input type="text" class="form-input" name="reason"
                                                        placeholder="Why are you adjusting points?" required>
                                                </div>
                                                <button type="submit" class="submit-btn submit-points">Adjust Points</button>
                                            </form>
                                        </div>
                                    </div>
                                @endif
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
        // Sample ledger data
        let ledgerData = [
            { type: 'deposit', amount: 10.00, note: 'Weekly allowance', date: '2025-11-08' },
            { type: 'spend', amount: -5.25, note: 'Ice cream at school', date: '2025-11-07' },
            { type: 'points', amount: '+2', note: 'Extra help with dishes', date: '2025-11-06' },
            { type: 'deposit', amount: 15.00, note: 'Birthday money from grandma', date: '2025-11-05' },
            { type: 'spend', amount: -12.50, note: 'New book from bookstore', date: '2025-11-04' },
            { type: 'points', amount: '-1', note: 'Forgot to feed the dog', date: '2025-11-03' },
            { type: 'deposit', amount: 10.00, note: 'Weekly allowance', date: '2025-11-01' },
            { type: 'spend', amount: -7.75, note: 'Movie ticket', date: '2025-10-30' }
        ];

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
        function openTransactionModal() {
            const modal = document.getElementById('transactionModal');
            modal.classList.add('active');
            renderModalLedger();
        }

        function closeTransactionModal() {
            const modal = document.getElementById('transactionModal');
            modal.classList.remove('active');
        }

        let modalTypeFilter = 'all';
        let modalTimeFilter = 'all';

        function filterModalByType(type) {
            modalTypeFilter = type;
            const buttons = document.querySelectorAll('.modal-filter-btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            renderModalLedger();
        }

        function filterModalByTime() {
            const select = document.getElementById('modalTimeFilter');
            modalTimeFilter = select.value;
            renderModalLedger();
        }

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

        function renderModalLedger() {
            const tbody = document.getElementById('modalLedgerBody');
            let filtered = ledgerData;

            // Filter by type
            if (modalTypeFilter !== 'all') {
                filtered = filtered.filter(entry => entry.type === modalTypeFilter);
            }

            // Filter by time
            filtered = filtered.filter(filterByTimeRange);

            if (filtered.length === 0) {
                tbody.innerHTML = '<div style="text-align: center; padding: 40px; color: #888;">No transactions found</div>';
                return;
            }

            tbody.innerHTML = filtered.map(entry => `
                <div class="modal-ledger-entry">
                    <div class="modal-entry-details">
                        <div class="modal-entry-type ${entry.type}">
                            ${entry.type === 'deposit' ? 'Deposit' : entry.type === 'spend' ? 'Spend' : 'Point Adjustment'}
                        </div>
                        <div class="modal-entry-note">${entry.note}</div>
                        <div class="modal-entry-date">${formatDate(entry.date)}</div>
                    </div>
                    <div class="modal-entry-amount ${entry.type}">
                        ${entry.type === 'points' ? entry.amount + ' pts' : (entry.amount >= 0 ? '+$' : '-$') + Math.abs(entry.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                    </div>
                </div>
            `).join('');
        }

        // Close modal when clicking outside
        document.addEventListener('click', function (e) {
            const modal = document.getElementById('transactionModal');
            if (e.target === modal) {
                closeTransactionModal();
            }
        });
    </script>

    <!-- Transaction Modal -->
    <div id="transactionModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">All Transactions</h2>
                <button class="modal-close" onclick="closeTransactionModal()">&times;</button>
            </div>
            <div class="modal-filters">
                <div class="modal-filter-tabs">
                    <button class="modal-filter-btn active" onclick="filterModalByType('all')">All</button>
                    <button class="modal-filter-btn" onclick="filterModalByType('deposit')">Deposits</button>
                    <button class="modal-filter-btn" onclick="filterModalByType('spend')">Spends</button>
                    <button class="modal-filter-btn" onclick="filterModalByType('points')">Points</button>
                </div>
                <select id="modalTimeFilter" class="modal-time-filter" onchange="filterModalByTime()">
                    <option value="all">All Time</option>
                    <option value="thisMonth">This Month</option>
                    <option value="lastMonth">Last Month</option>
                    <option value="last3Months">Last 3 Months</option>
                </select>
            </div>
            <div class="modal-body">
                <div id="modalLedgerBody"></div>
            </div>
        </div>
    </div>

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
                        <input type="number" name="allowance_amount" step="0.01" id="allowanceAmount" class="form-input"
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
    </script>

</body>

</html>