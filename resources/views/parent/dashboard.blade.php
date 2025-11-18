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
                    <div class="next-allowance">Weekly allowance: ${{ number_format($kid->allowance_amount, 2) }}</div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button class="action-btn btn-deposit" onclick="toggleForm('deposit-{{ $kid->id }}')">Deposit Money</button>
                    <button class="action-btn btn-spend" onclick="toggleForm('spend-{{ $kid->id }}')">Record Spend</button>
                    @if($kid->points_enabled)
                        <button class="action-btn btn-points" onclick="toggleForm('points-{{ $kid->id }}')">Adjust Points</button>
                    @endif
                    <button class="action-btn btn-ledger" onclick="toggleForm('ledger-{{ $kid->id }}')">View Ledger</button>
                </div>

                <!-- Deposit Form -->
                <div class="dropdown-form" id="deposit-{{ $kid->id }}Form">
                    <div class="form-content">
                        <form action="{{ route('kids.deposit', $kid) }}" method="POST" class="inline-form">
                            @csrf
                            <div class="form-group">
                                <label class="form-label">Amount</label>
                                <input type="text" inputmode="decimal" class="form-input currency-input" name="amount" placeholder="0.00" required>
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
                                <input type="text" inputmode="decimal" class="form-input currency-input" name="amount" placeholder="0.00" required>
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
                                            <button type="button" class="points-btn" onclick="adjustPoints({{ $kid->id }}, -1)">−</button>
                                            <input type="number" class="points-display" name="points" id="points-{{ $kid->id }}" value="0" readonly>
                                            <button type="button" class="points-btn" onclick="adjustPoints({{ $kid->id }}, 1)">+</button>
                                        </div>
                                    </div>
                                    <div class="points-reason">
                                        <label class="points-label">Reason:</label>
                                        <input type="text" class="form-input" name="reason" placeholder="Why are you adjusting points?" required>
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
                            <button class="filter-btn active" data-kid="{{ $kid->id }}" onclick="filterLedger({{ $kid->id }}, 'all')">All</button>
                            <button class="filter-btn" data-kid="{{ $kid->id }}" onclick="filterLedger({{ $kid->id }}, 'deposit')">Deposits</button>
                            <button class="filter-btn" data-kid="{{ $kid->id }}" onclick="filterLedger({{ $kid->id }}, 'spend')">Spends</button>
                            <button class="filter-btn" data-kid="{{ $kid->id }}" onclick="filterLedger({{ $kid->id }}, 'points')">Point Adjustments</button>
                        </div>
                        <div class="ledger-table" id="ledger-{{ $kid->id }}-table">
                            @php
        $transactions = $kid->transactions()->latest()->take(8)->get();
        $pointAdjustments = $kid->pointAdjustments()->latest()->take(8)->get();
        $allEntries = $transactions->concat($pointAdjustments)->sortByDesc('created_at')->take(8);
                            @endphp

                            @forelse($allEntries as $entry)
                                <div class="ledger-row" data-type="{{ $entry instanceof \App\Models\Transaction ? $entry->type : 'points' }}">
                                    <div class="ledger-date">{{ $entry->created_at->format('M d, Y') }}</div>
                                    @if($entry instanceof \App\Models\Transaction)
                                        <div class="ledger-type">{{ ucfirst($entry->type) }}</div>
                                        <div class="ledger-amount {{ $entry->type }}">${{ number_format($entry->amount, 2) }}</div>
                                        <div class="ledger-note">{{ $entry->description ?? 'No note' }}</div>
                                    @else
                                        <div class="ledger-type">Points</div>
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
                        <button type="button" class="view-all-btn" onclick="openTransactionModal({{ $kid->id }})">View All Transactions</button>
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
    <div class="modal-overlay" id="addKidModal">
        <div class="modal-container">
            <div class="modal-header">
                <h2 class="modal-title">Add a New Kid</h2>
                <button class="close-btn" onclick="closeAddKidModal()">&times;</button>
            </div>
            <form action="{{ route('kids.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-input" name="name" id="kidName" placeholder="First name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-input" name="username" placeholder="Choose a unique username for login" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-input" name="password" placeholder="Simple PIN or password" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Birthday</label>
                            <input type="date" class="form-input" name="birthday" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Weekly Allowance</label>
                            <input type="text" inputmode="decimal" class="form-input" name="allowance_amount" placeholder="0.00" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Allowance Day
                                <span class="tooltip-icon">?
                                    <span class="tooltip-text">Select the day of the week to post allowance.</span>
                                </span>
                            </label>
                            <select class="form-input" name="allowance_day" required>
                                <option value="monday">Monday</option>
                                <option value="tuesday">Tuesday</option>
                                <option value="wednesday">Wednesday</option>
                                <option value="thursday">Thursday</option>
                                <option value="friday" selected>Friday</option>
                                <option value="saturday">Saturday</option>
                                <option value="sunday">Sunday</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="avatar-label">Choose Avatar Color</label>
                        <div class="avatar-preview-container">
                            <div id="avatarPreview" class="avatar-preview" style="background: #80d4b0;">?</div>
                            <span id="colorLabel" style="font-size: 15px; color: #666;">Mint Green</span>
                        </div>
                        <input type="hidden" name="color" id="selectedColor" value="#80d4b0">
                        <input type="hidden" name="avatar" value="avatar1">
                        <div class="color-grid">
                            <div class="color-option selected" style="background: #80d4b0;" data-color="#80d4b0" data-name="Mint Green"
                                onclick="selectColor(this)"></div>
                            <div class="color-option" style="background: #ff9999;" data-color="#ff9999" data-name="Coral"
                                onclick="selectColor(this)"></div>
                            <div class="color-option" style="background: #b19cd9;" data-color="#b19cd9" data-name="Lavender"
                                onclick="selectColor(this)"></div>
                            <div class="color-option" style="background: #87ceeb;" data-color="#87ceeb" data-name="Sky Blue"
                                onclick="selectColor(this)"></div>
                            <div class="color-option" style="background: #ffb380;" data-color="#ffb380" data-name="Peach"
                                onclick="selectColor(this)"></div>
                            <div class="color-option" style="background: #e066a6;" data-color="#e066a6" data-name="Magenta"
                                onclick="selectColor(this)"></div>
                            <div class="color-option" style="background: #ffd966;" data-color="#ffd966" data-name="Butter Yellow"
                                onclick="selectColor(this)"></div>
                            <div class="color-option" style="background: #a8c686;" data-color="#a8c686" data-name="Sage Green"
                                onclick="selectColor(this)"></div>
                            <div class="color-option" style="background: #5ab9b3;" data-color="#5ab9b3" data-name="Teal"
                                onclick="selectColor(this)"></div>
                            <div class="color-option" style="background: #9bb7d4;" data-color="#9bb7d4" data-name="Periwinkle"
                                onclick="selectColor(this)"></div>
                            <div class="color-option" style="background: #ff9966;" data-color="#ff9966" data-name="Tangerine"
                                onclick="selectColor(this)"></div>
                            <div class="color-option" style="background: #d4a5d4;" data-color="#d4a5d4" data-name="Lilac"
                                onclick="selectColor(this)"></div>
                        </div>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" name="points_enabled" id="usePoints" class="checkbox-input" value="1" checked onchange="toggleMaxPoints()">
                        <label class="checkbox-label" for="usePoints">
                            Use point system?
                            <span class="tooltip-icon">
                                ?
                                <span class="tooltip-text">Points encourage accountability. Kids start each week with full points.</span>
                            </span>
                        </label>
                    </div>

                    <div class="form-group max-points-group" id="maxPointsGroup">
                        <label class="form-label">Starting Points (per week)</label>
                        <input type="number" class="form-input" name="max_points" value="10" min="1" max="100">
                        <small style="color: #666; font-size: 12px;">Recommended: 10 points</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="modal-btn modal-btn-cancel" onclick="closeAddKidModal()">Cancel</button>
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
@endsection

@section('scripts')
        // Toggle form dropdowns
        let activeForm = null;

        function toggleForm(formId) {
            const form = document.getElementById(formId + 'Form');

            if (activeForm && activeForm !== form) {
                activeForm.classList.remove('active');
            }

            form.classList.toggle('active');
            activeForm = form.classList.contains('active') ? form : null;
        }

        // Points adjuster
        function adjustPoints(kidId, change) {
            const input = document.getElementById('points-' + kidId);
            let currentValue = parseInt(input.value) || 0;
            input.value = currentValue + change;
        }

        // Ledger filtering
        function filterLedger(kidId, type) {
            const table = document.getElementById(`ledger-${kidId}-table`);
            const rows = table.querySelectorAll('.ledger-row');
            const buttons = document.querySelectorAll(`.filter-btn[data-kid="${kidId}"]`);

            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            rows.forEach(row => {
                if (type === 'all' || row.dataset.type === type) {
                    row.style.display = 'grid';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Add Kid Modal
        function openAddKidModal() {
            document.getElementById('addKidModal').classList.add('active');
        }

        function closeAddKidModal() {
            document.getElementById('addKidModal').classList.remove('active');
        }

        function selectAvatar(avatar) {
            document.querySelectorAll('.avatar-option').forEach(opt => opt.classList.remove('selected'));
            document.querySelector(`[data-avatar="${avatar}"]`).classList.add('selected');
            document.getElementById('avatarInput').value = avatar;
        }

    function selectColor(element) {
    document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('selected'));
    element.classList.add('selected');

    const color = element.dataset.color;
    const name = element.dataset.name;

    document.getElementById('avatarPreview').style.background = color;
    document.getElementById('colorLabel').textContent = name;
    document.getElementById('selectedColor').value = color;
    }

    // Update avatar preview when name changes
    document.getElementById('kidName')?.addEventListener('input', function(e) {
    const preview = document.getElementById('avatarPreview');
    const name = e.target.value.trim();
    preview.textContent = name ? name.charAt(0).toUpperCase() : '?';
    });

        function toggleMaxPoints() {
            const checkbox = document.getElementById('usePoints');
            const maxPointsGroup = document.getElementById('maxPointsGroup');

            if (checkbox.checked) {
                maxPointsGroup.style.display = 'block';
            } else {
                maxPointsGroup.style.display = 'none';
            }
        }

        function toggleMobileMenu() {
            document.querySelector('.header-nav').classList.toggle('mobile-open');
        }

        // Transaction Modal
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

        // Currency formatting
        function formatCurrencyInput(input) {
            let value = input.value.replace(/\D/g, '');
            let numericValue = parseInt(value) || 0;
            let dollars = (numericValue / 100).toFixed(2);
            input.value = '$' + dollars;
        }

        function addCurrencyListeners() {
            document.querySelectorAll('input[name="amount"], input[name="allowance_amount"]').forEach(input => {
                input.addEventListener('input', function() {
                    formatCurrencyInput(this);
                });
            });

            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    this.querySelectorAll('input[name="amount"], input[name="allowance_amount"]').forEach(input => {
                        input.value = input.value.replace(/[$,]/g, '');
                    });
                });
            });
        }

        // AJAX form submissions
        function handleFormSubmit(form, successMessage) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const submitBtn = this.querySelector('.submit-btn');
                const originalText = submitBtn.textContent;

                submitBtn.classList.add('loading');
                submitBtn.disabled = true;

                const formData = new FormData(this);

                try {
                    const [response] = await Promise.all([
                        fetch(this.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        }),
                        new Promise(resolve => setTimeout(resolve, 800))
                    ]);

                    const data = await response.json();

                    if (response.ok) {
                        submitBtn.classList.remove('loading');

                        setTimeout(() => {
                            submitBtn.classList.add('success');
                            submitBtn.textContent = successMessage;
                        }, 100);

                        if (data.new_balance !== undefined) {
                            const balanceEl = document.querySelector('.balance');
                            balanceEl.textContent = '$' + parseFloat(data.new_balance).toFixed(2);
                            balanceEl.classList.toggle('negative', data.new_balance < 0);
                        }

                        if (data.new_points !== undefined) {
                            const pointsBadge = document.querySelector('.points-badge');
                            if (pointsBadge) {
                                pointsBadge.textContent = data.new_points + ' / 10';

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

                        setTimeout(() => {
                            this.querySelectorAll('input[type="text"]:not([readonly]), input[type="number"]:not([readonly])').forEach(input => {
                                input.value = '';
                            });

                            const pointsDisplay = this.querySelector('input[name="points"]');
                            if (pointsDisplay) {
                                pointsDisplay.value = '0';
                            }

                            submitBtn.style.color = 'transparent';

                            setTimeout(() => {
                                submitBtn.classList.remove('success');
                                submitBtn.textContent = originalText;
                                submitBtn.style.transform = 'scale(1)';

                                setTimeout(() => {
                                    submitBtn.style.color = '';
                                    submitBtn.disabled = false;

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

        // Remember open forms before reload
        window.addEventListener('beforeunload', function() {
            const activeForm = document.querySelector('.dropdown-form.active');
            if (activeForm) {
                sessionStorage.setItem('reopenForm', activeForm.id);
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Reopen form after page loads
            const formToReopen = sessionStorage.getItem('reopenForm');
            if (formToReopen) {
                const form = document.getElementById(formToReopen);
                if (form) {
                    form.classList.add('active');
                }
                sessionStorage.removeItem('reopenForm');
            }

            // Initialize currency listeners
            addCurrencyListeners();

            // Initialize AJAX forms
            document.querySelectorAll('form[action*="deposit"]').forEach(form => {
                handleFormSubmit(form, '✓ Recorded!');
            });

            document.querySelectorAll('form[action*="spend"]').forEach(form => {
                handleFormSubmit(form, '✓ Recorded!');
            });

            document.querySelectorAll('form[action*="points"]').forEach(form => {
                handleFormSubmit(form, '✓ Adjusted!');
            });
        });

        // Close modal on backdrop click
        document.getElementById('addKidModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddKidModal();
            }
        });
@endsection