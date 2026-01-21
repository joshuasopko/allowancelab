/**
 * AllowanceLab - Kid Dashboard JavaScript
 * All functions prefixed with 'kid' to avoid conflicts with parent dashboard
 */

// ============================================
// GLOBAL VARIABLES
// ============================================
let kidBalance = window.kidBalance || 0;
let kidPoints = window.kidPoints || 0;
let kidActiveForm = null;
let kidCurrentFilter = 'all';
let kidLedgerOpen = false;
let kidLedgerData = window.kidLedgerData || [];

// Points messages by zone
const kidPointsMessages = {
    high: [
        "Great job! Keep it up! 🌟",
        "You're crushing it this week!",
        "Awesome work! Stay on track!"
    ],
    medium: [
        "You're doing well. Keep working at it!",
        "Good progress! Keep going!",
        "Nice effort! You've got this!"
    ],
    low: [
        "This week needs improvement. Find an extra chore to help out where you can!",
        "Try to help out more this week!",
        "Let's turn this around! Look for ways to help!"
    ],
    zero: "Uh oh! You're at zero points. No allowance this week unless you can earn some points back!"
};

// ============================================
// INITIALIZATION
// ============================================

// Check if we're on mobile and hide sidebar by default
function kidCheckMobile() {
    const sidebar = document.getElementById('kidSidebar');
    if (!sidebar) return;

    if (window.innerWidth > 768) {
        sidebar.classList.remove('mobile-hidden');
    } else {
        sidebar.classList.add('mobile-hidden');
    }
}



// Check on resize
window.addEventListener('resize', kidCheckMobile);

// ============================================
// SIDEBAR TOGGLE
// ============================================

function kidToggleSidebar() {
    const sidebar = document.getElementById('kidSidebar');
    const hamburger = document.getElementById('kidHamburger');
    if (!sidebar || !hamburger) return;

    sidebar.classList.toggle('mobile-hidden');
    hamburger.classList.toggle('active');
}

// ============================================
// COMING SOON TOAST
// ============================================

function kidShowToast(message) {
    const toast = document.getElementById('kidToast');
    const toastMessage = document.getElementById('kidToastMessage');
    if (!toast || !toastMessage) return;

    toastMessage.textContent = message;
    toast.classList.add('show');
    setTimeout(() => {
        toast.classList.remove('show');
    }, 4000);
}

// Initialize coming soon items
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.kid-coming-soon-item').forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const featureName = item.textContent.trim().replace('Coming Soon', '').trim();
            kidShowToast(`${featureName} is coming soon! We're working on this feature and it'll be ready before you know it.`);
        });
    });
});

// ============================================
// POINTS DISPLAY
// ============================================

function kidUpdatePointsDisplay() {
    const pointsPill = document.getElementById('kidPointsPill');
    const pointsValue = document.getElementById('kidPointsValue');
    const pointsMessage = document.getElementById('kidPointsMessage');

    if (!pointsPill || !pointsValue || !pointsMessage) return;

    pointsValue.textContent = `${kidPoints} / 10`;

    if (kidPoints === 0) {
        pointsPill.className = 'kid-points-pill danger';
        pointsMessage.textContent = kidPointsMessages.zero;
    } else if (kidPoints <= 4) {
        // 1-4 points: Red/Danger
        pointsPill.className = 'kid-points-pill danger';
        const messages = kidPointsMessages.low;
        pointsMessage.textContent = messages[Math.floor(Math.random() * messages.length)];
    } else if (kidPoints <= 7) {
        // 5-7 points: Yellow/Warning
        pointsPill.className = 'kid-points-pill warning';
        const messages = kidPointsMessages.medium;
        pointsMessage.textContent = messages[Math.floor(Math.random() * messages.length)];
    } else {
        // 8-10 points: Green
        pointsPill.className = 'kid-points-pill';
        const messages = kidPointsMessages.high;
        pointsMessage.textContent = messages[Math.floor(Math.random() * messages.length)];
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function () {
    kidCheckMobile();
    kidUpdatePointsDisplay();
    kidRenderLedger();
});

// ============================================
// CURRENCY FORMATTING
// ============================================

function kidFormatCurrency(input) {
    // Get raw value
    let value = input.value.replace(/[^0-9]/g, '');

    if (value === '') {
        input.value = '';
        return;
    }

    // Convert to number and divide by 100 to get dollars.cents
    let numValue = parseInt(value) / 100;

    // Format as currency
    input.value = '$' + numValue.toFixed(2);
}

// ============================================
// BALANCE UPDATE
// ============================================

function kidUpdateBalance(amount) {
    kidBalance += amount;
    const balanceEl = document.getElementById('kidBalance');
    if (!balanceEl) return;

    balanceEl.textContent = '$' + kidBalance.toFixed(2);

    // Add/remove negative class
    if (kidBalance < 0) {
        balanceEl.classList.add('negative');
    } else {
        balanceEl.classList.remove('negative');
    }
}

// ============================================
// LEDGER FUNCTIONS
// ============================================

function kidToggleLedger() {
    const ledgerSection = document.getElementById('kidLedgerSection');
    const ledgerBtn = document.getElementById('kidLedgerBtn');
    if (!ledgerSection || !ledgerBtn) return;

    if (kidLedgerOpen) {
        ledgerSection.classList.add('closing');
        setTimeout(() => {
            ledgerSection.classList.remove('open');
            ledgerSection.classList.remove('closing');
        }, 300);
        ledgerBtn.textContent = 'View Ledger';
        kidLedgerOpen = false;
    } else {
        // Close any open forms first
        if (kidActiveForm) {
            kidCloseForm(kidActiveForm === 'deposit' ? 'kidDepositForm' : 'kidSpendForm');
        }
        ledgerSection.classList.add('open');
        ledgerBtn.textContent = 'Close Ledger';
        kidLedgerOpen = true;
        kidRenderLedger();
    }
}

function kidRenderLedger() {
    const table = document.getElementById('kidLedgerTable');
    if (!table) return;

    let filtered;
    if (kidCurrentFilter === 'all') {
        filtered = kidLedgerData;
    } else {
        filtered = kidLedgerData.filter(entry => entry.type === kidCurrentFilter);
    }

    if (filtered.length === 0) {
        table.innerHTML = '<div style="text-align: center; padding: 40px; color: #888; line-height: 1.6;">Ready to start tracking your money?<br>Record your first transaction above! 💰</div>';
        return;
    }

    // Show only first 8 entries (already sorted newest first from backend)
    const displayedEntries = filtered.slice(0, 8);
    const hasMore = filtered.length > 8;

    table.innerHTML = displayedEntries.map(entry => {
        let amountClass = entry.type;
        if (entry.type === 'points') {
            amountClass += entry.amount >= 0 ? ' positive' : ' negative';
        }

        const isParentInitiated = entry.parentInitiated || entry.initiated_by === 'parent';

        // Format date/time
        const dateObj = new Date(entry.timestamp * 1000);
        const dateTime = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) +
            ' | ' +
            dateObj.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });

        const isDeniedAllowance = entry.note === 'Allowance not earned - insufficient points';

        return `
<div class="kid-ledger-entry ${isParentInitiated ? 'parent-initiated' : ''} ${isDeniedAllowance ? 'denied-allowance' : ''}">
            <div class="kid-parent-icon-cell">
                ${isParentInitiated ? '<span class="kid-parent-icon">P</span>' : ''}
            </div>
            <div class="kid-entry-date">${dateTime}</div>
            <div class="kid-entry-type ${entry.type}">
                ${entry.type === 'deposit' ? 'Deposit' : entry.type === 'spend' ? 'Spend' : 'Points'}
            </div>
            <div class="kid-entry-note">${entry.note}</div>
            <div class="kid-entry-amount ${amountClass}">
                ${entry.type === 'points' ? (entry.amount >= 0 ? '+' : '') + entry.amount + ' pts' : (entry.amount >= 0 ? '+$' : '-$') + Math.abs(entry.amount).toFixed(2)}
            </div>
        </div>
        `;
    }).join('') + (hasMore ? `
        <button class="kid-view-all-btn" onclick="kidOpenTransactionModal()">
            View All ${filtered.length} Transactions →
        </button>
    ` : '');
}

// ... end of kidRenderLedger function

function kidFilterLedger(filter) {
    kidCurrentFilter = filter;
    const buttons = document.querySelectorAll('.kid-filter-btn');
    buttons.forEach(btn => btn.classList.remove('active'));

    // Find and activate the correct button
    buttons.forEach(btn => {
        if (btn.textContent.toLowerCase().includes(filter) || (filter === 'all' && btn.textContent === 'All')) {
            btn.classList.add('active');
        }
    });

    kidRenderLedger();
}

// ============================================
// FORM TOGGLE FUNCTIONS
// ============================================

// ============================================
// FORM TOGGLE FUNCTIONS
// ============================================

function kidOpenDepositForm() {
    // Close ledger if open
    if (kidLedgerOpen) {
        kidToggleLedger();
    }

    // Toggle: if deposit form is already open, close it
    if (kidActiveForm === 'deposit') {
        kidCloseForm('kidDepositForm');
        return;
    }

    // Close spend form if open
    if (kidActiveForm === 'spend') {
        kidCloseForm('kidSpendForm');
        setTimeout(() => {
            document.getElementById('kidDepositForm').classList.add('open');
            kidActiveForm = 'deposit';
            // Focus the amount input after form opens
            setTimeout(() => {
                document.getElementById('kidDepositAmount').focus();
            }, 50);
        }, 400);
    } else {
        document.getElementById('kidDepositForm').classList.add('open');
        kidActiveForm = 'deposit';
        // Focus the amount input after form opens
        setTimeout(() => {
            document.getElementById('kidDepositAmount').focus();
        }, 50);
    }
}

function kidOpenSpendForm() {
    // Close ledger if open
    if (kidLedgerOpen) {
        kidToggleLedger();
    }

    // Toggle: if spend form is already open, close it
    if (kidActiveForm === 'spend') {
        kidCloseForm('kidSpendForm');
        return;
    }

    // Close deposit form if open
    if (kidActiveForm === 'deposit') {
        kidCloseForm('kidDepositForm');
        setTimeout(() => {
            document.getElementById('kidSpendForm').classList.add('open');
            kidActiveForm = 'spend';
            // Focus the amount input after form opens
            setTimeout(() => {
                document.getElementById('kidSpendAmount').focus();
            }, 50);
        }, 400);
    } else {
        document.getElementById('kidSpendForm').classList.add('open');
        kidActiveForm = 'spend';
        // Focus the amount input after form opens
        setTimeout(() => {
            document.getElementById('kidSpendAmount').focus();
        }, 50);
    }
}

function kidCloseForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.classList.add('closing');
    setTimeout(() => {
        form.classList.remove('open');
        form.classList.remove('closing');
        kidActiveForm = null;
    }, 300);
}

// ============================================
// FORM SUBMISSION FUNCTIONS
// ============================================

function kidSubmitDeposit(event) {
    event.preventDefault();

    const amount = document.getElementById('kidDepositAmount').value;
    const note = document.getElementById('kidDepositNote').value;
    const amountInput = document.getElementById('kidDepositAmount');
    const amountError = document.getElementById('kidDepositAmountError');
    const noteInput = document.getElementById('kidDepositNote');
    const noteError = document.getElementById('kidDepositNoteError');
    const btn = document.querySelector('.kid-submit-deposit');

    if (!amount || amount === '$0.00' || parseFloat(amount.replace(/[$,]/g, '')) < 0.01) {
        amountInput.classList.add('error');
        amountError.classList.add('show');
        setTimeout(() => {
            amountInput.classList.remove('error');
        }, 400);
        return;
    }

    if (!note.trim()) {
        amountError.classList.remove('show');
        noteInput.classList.add('error');
        noteError.classList.add('show');
        setTimeout(() => {
            noteInput.classList.remove('error');
        }, 400);
        return;
    }

    amountError.classList.remove('show');
    noteError.classList.remove('show');

    const numAmount = parseFloat(amount.replace(/[$,]/g, ''));

    btn.innerHTML = '<span class="kid-spinner"></span>';
    btn.classList.add('loading');
    btn.disabled = true;

    // Make AJAX call to Laravel backend
    fetch('/kid/deposit', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            amount: numAmount,
            note: note
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                kidUpdateBalance(numAmount);

                kidLedgerData.unshift({
                    type: 'deposit',
                    amount: numAmount,
                    note: note,
                    timestamp: Math.floor(Date.now() / 1000),
                    date: new Date().toISOString().split('T')[0],
                    parentInitiated: false,
                    initiated_by: 'kid'
                });

                if (kidLedgerOpen) {
                    kidRenderLedger();
                }

                btn.classList.remove('loading');
                btn.textContent = '✓ Recorded!';
                btn.classList.add('success');

                setTimeout(() => {
                    const form = document.getElementById('kidDepositForm');
                    form.classList.add('closing');

                    setTimeout(() => {
                        form.classList.remove('open');
                        form.classList.remove('closing');
                        kidActiveForm = null;

                        document.getElementById('kidDepositAmount').value = '';
                        document.getElementById('kidDepositNote').value = '';
                        amountError.classList.remove('show');
                        noteError.classList.remove('show');
                        btn.textContent = 'Record Deposit';
                        btn.classList.remove('success');
                        btn.disabled = false;
                    }, 400);
                }, 1500);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btn.classList.remove('loading');
            btn.textContent = 'Error - Try Again';
            btn.disabled = false;
            setTimeout(() => {
                btn.textContent = 'Record Deposit';
            }, 2000);
        });
}

function kidSubmitSpend(event) {
    event.preventDefault();

    const amount = document.getElementById('kidSpendAmount').value;
    const note = document.getElementById('kidSpendNote').value;
    const amountInput = document.getElementById('kidSpendAmount');
    const amountError = document.getElementById('kidSpendAmountError');
    const noteInput = document.getElementById('kidSpendNote');
    const noteError = document.getElementById('kidSpendNoteError');
    const btn = document.querySelector('.kid-submit-spend');

    if (!amount || amount === '$0.00' || parseFloat(amount.replace(/[$,]/g, '')) < 0.01) {
        amountInput.classList.add('error');
        amountError.classList.add('show');
        setTimeout(() => {
            amountInput.classList.remove('error');
        }, 400);
        return;
    }

    if (!note.trim()) {
        amountError.classList.remove('show');
        noteInput.classList.add('error');
        noteError.classList.add('show');
        setTimeout(() => {
            noteInput.classList.remove('error');
        }, 400);
        return;
    }

    amountError.classList.remove('show');
    noteError.classList.remove('show');

    const numAmount = parseFloat(amount.replace(/[$,]/g, ''));

    btn.innerHTML = '<span class="kid-spinner"></span>';
    btn.classList.add('loading');
    btn.disabled = true;

    // Make AJAX call to Laravel backend
    fetch('/kid/spend', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            amount: numAmount,
            note: note
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                kidUpdateBalance(-numAmount);

                kidLedgerData.unshift({
                    type: 'spend',
                    amount: -numAmount,
                    note: note,
                    timestamp: Math.floor(Date.now() / 1000),
                    date: new Date().toISOString().split('T')[0],
                    parentInitiated: false,
                    initiated_by: 'kid'
                });

                if (kidLedgerOpen) {
                    kidRenderLedger();
                }

                btn.classList.remove('loading');
                btn.textContent = '✓ Recorded!';
                btn.classList.add('success');

                setTimeout(() => {
                    const form = document.getElementById('kidSpendForm');
                    form.classList.add('closing');

                    setTimeout(() => {
                        form.classList.remove('open');
                        form.classList.remove('closing');
                        kidActiveForm = null;

                        document.getElementById('kidSpendAmount').value = '';
                        document.getElementById('kidSpendNote').value = '';
                        amountError.classList.remove('show');
                        noteError.classList.remove('show');
                        btn.textContent = 'Record Spend';
                        btn.classList.remove('success');
                        btn.disabled = false;
                    }, 400);
                }, 1500);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btn.classList.remove('loading');
            btn.textContent = 'Error - Try Again';
            btn.disabled = false;
            setTimeout(() => {
                btn.textContent = 'Record Spend';
            }, 2000);
        });
}

// ============================================
// FORMAT DATE HELPER
// ============================================

function kidFormatDate(dateString) {
    const date = new Date(dateString);
    const options = { month: 'short', day: 'numeric', year: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

// ============================================
// TRANSACTION MODAL
// ============================================

let kidModalCurrentFilter = 'all';  // CHANGED from kidModalFilter
let kidModalTimeRange = 'all';

function kidOpenTransactionModal() {
    const modal = document.getElementById('kidTransactionModal');
    if (!modal) return;

    modal.classList.add('active');
    kidModalCurrentFilter = 'all';  // CHANGED
    kidModalTimeRange = 'all';
    kidRenderModalLedger();
}

function kidCloseTransactionModal() {
    const modal = document.getElementById('kidTransactionModal');
    if (!modal) return;

    modal.classList.remove('active');
}

function kidModalFilter(filter) {
    kidModalCurrentFilter = filter;  // CHANGED

    const buttons = document.querySelectorAll('.kid-modal-filter-btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');

    kidRenderModalLedger();
}

function kidModalTimeFilter() {
    const select = document.getElementById('kidModalTimeFilter');
    kidModalTimeRange = select.value;
    kidRenderModalLedger();
}

function kidRenderModalLedger() {
    const body = document.getElementById('kidModalBody');
    if (!body) return;

    let filtered = kidLedgerData;

    // Filter by type
    if (kidModalCurrentFilter !== 'all') {  // CHANGED
        filtered = filtered.filter(entry => entry.type === kidModalCurrentFilter);  // CHANGED
    }

    // Filter by time range
    const now = new Date();
    if (kidModalTimeRange !== 'all') {
        filtered = filtered.filter(entry => {
            const entryDate = new Date(entry.timestamp * 1000);

            if (kidModalTimeRange === 'week') {
                const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
                return entryDate >= weekAgo;
            } else if (kidModalTimeRange === 'month') {
                const monthAgo = new Date(now.getFullYear(), now.getMonth(), 1);
                return entryDate >= monthAgo;
            } else if (kidModalTimeRange === 'year') {
                const yearAgo = new Date(now.getFullYear(), 0, 1);
                return entryDate >= yearAgo;
            }
            return true;
        });
    }

    if (filtered.length === 0) {
        body.innerHTML = '<div style="text-align: center; padding: 60px 20px; color: #888;">No transactions found for this filter.</div>';
        return;
    }

    body.innerHTML = filtered.map(entry => {
        let amountClass = entry.type;
        if (entry.type === 'points') {
            amountClass += entry.amount >= 0 ? ' positive' : ' negative';
        }

        const isParentInitiated = entry.parentInitiated || entry.initiated_by === 'parent';

        const dateObj = new Date(entry.timestamp * 1000);
        const dateTime = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) +
            ' | ' +
            dateObj.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });

        const isDeniedAllowance = entry.note === 'Allowance not earned - insufficient points';

        return `
<div class="kid-modal-ledger-entry ${isParentInitiated ? 'parent-initiated' : ''} ${isDeniedAllowance ? 'denied-allowance' : ''}">
            <div class="kid-parent-icon-cell">
                ${isParentInitiated ? '<span class="kid-parent-icon">P</span>' : ''}
            </div>
            <div class="kid-entry-date">${dateTime}</div>
            <div class="kid-entry-type ${entry.type}">
                ${entry.type === 'deposit' ? 'Deposit' : entry.type === 'spend' ? 'Spend' : 'Points'}
            </div>
            <div class="kid-entry-note">${entry.note}</div>
            <div class="kid-entry-amount ${amountClass}">
                ${entry.type === 'points' ? (entry.amount >= 0 ? '+' : '') + entry.amount + ' pts' : (entry.amount >= 0 ? '+$' : '-$') + Math.abs(entry.amount).toFixed(2)}
            </div>
        </div>
        `;
    }).join('');
}

// Expose functions to window for inline onclick handlers
window.kidToggleSidebar = kidToggleSidebar;
window.kidShowToast = kidShowToast;
window.kidUpdatePointsDisplay = kidUpdatePointsDisplay;
window.kidToggleLedger = kidToggleLedger;
window.kidFilterLedger = kidFilterLedger;
window.kidOpenDepositForm = kidOpenDepositForm;
window.kidOpenSpendForm = kidOpenSpendForm;
window.kidSubmitDeposit = kidSubmitDeposit;
window.kidSubmitSpend = kidSubmitSpend;
window.kidFormatCurrency = kidFormatCurrency;
window.kidRenderLedger = kidRenderLedger;

window.kidOpenTransactionModal = kidOpenTransactionModal;
window.kidCloseTransactionModal = kidCloseTransactionModal;
window.kidModalFilter = kidModalFilter;
window.kidModalTimeFilter = kidModalTimeFilter;