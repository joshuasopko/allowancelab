/**
 * AllowanceLab - Kid Dashboard JavaScript
 * All functions prefixed with 'kid' to avoid conflicts with parent dashboard
 */

// ============================================
// GLOBAL VARIABLES
// ============================================
let kidBalance = 0;
let kidPoints = window.kidPoints || 0;
let kidActiveForm = null;
let kidCurrentFilter = 'all';
let kidLedgerOpen = false;
let kidLedgerData = [];

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
    } else if (kidPoints >= 8) {
        pointsPill.className = 'kid-points-pill';
        const messages = kidPointsMessages.high;
        pointsMessage.textContent = messages[Math.floor(Math.random() * messages.length)];
    } else if (kidPoints >= 5) {
        pointsPill.className = 'kid-points-pill';
        const messages = kidPointsMessages.medium;
        pointsMessage.textContent = messages[Math.floor(Math.random() * messages.length)];
    } else {
        pointsPill.className = 'kid-points-pill warning';
        const messages = kidPointsMessages.low;
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

function kidFilterLedger(filter) {
    kidCurrentFilter = filter;
    const buttons = document.querySelectorAll('.kid-filter-btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    kidRenderLedger();
}

function kidRenderLedger() {
    const table = document.getElementById('kidLedgerTable');
    if (!table) return;

    let filtered = kidCurrentFilter === 'all' ? kidLedgerData : kidLedgerData.filter(entry => entry.type === kidCurrentFilter);

    if (filtered.length === 0) {
        table.innerHTML = '<div style="text-align: center; padding: 40px; color: #888; line-height: 1.6;">Ready to start tracking your money?<br>Record your first transaction above! 💰</div>';
        return;
    }

    table.innerHTML = filtered.map(entry => {
        let amountClass = entry.type;
        if (entry.type === 'points') {
            amountClass += entry.amount >= 0 ? ' positive' : ' negative';
        }

        const isParentInitiated = entry.parentInitiated || entry.initiated_by === 'parent';

        return `
        <div class="kid-ledger-entry">
            <div class="kid-parent-icon-cell">
                ${isParentInitiated ? '<span class="kid-parent-icon">👤</span>' : ''}
            </div>
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
        }, 400);
    } else {
        document.getElementById('kidDepositForm').classList.add('open');
        kidActiveForm = 'deposit';
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
        }, 400);
    } else {
        document.getElementById('kidSpendForm').classList.add('open');
        kidActiveForm = 'spend';
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