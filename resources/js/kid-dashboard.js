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

    // Auto-switch to tab specified in URL param (e.g. after goal create/edit)
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    const editGoalParam = urlParams.get('edit_goal');
    if (tabParam && ['goals', 'wishes', 'activity'].includes(tabParam)) {
        // Clean URL before switching so params don't persist
        history.replaceState({}, '', window.location.pathname);
        setTimeout(() => {
            kidSwitchTab(tabParam, { editGoalId: editGoalParam ? parseInt(editGoalParam) : null });
        }, 0);
    }
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
    const badgeEl = document.getElementById('kidBalanceBadge');
    if (badgeEl) badgeEl.textContent = '$' + kidBalance.toFixed(2);

    // Add/remove negative class
    if (kidBalance < 0) {
        balanceEl.classList.add('negative');
    } else {
        balanceEl.classList.remove('negative');
    }
}

// ============================================
// TAB SWITCHING
// ============================================

const kidLoadedTabs = new Set(['overview']);

function kidSwitchTab(tabName, options = {}) {
    // Update tab buttons
    document.querySelectorAll('.kid-dashboard-tab').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.kid-dashboard-tab').forEach(btn => {
        if (btn.getAttribute('onclick') === `kidSwitchTab('${tabName}')`) {
            btn.classList.add('active');
        }
    });

    // Show/hide panels
    document.querySelectorAll('.kid-tab-panel').forEach(panel => {
        panel.style.display = 'none';
    });
    const targetPanel = document.getElementById(`kid-tab-${tabName}`);
    if (!targetPanel) return;
    targetPanel.style.display = '';

    // If already loaded, just show it
    if (kidLoadedTabs.has(tabName)) {
        if (tabName === 'activity') kidRenderActivityTab();
        return;
    }

    // Show loading state
    targetPanel.innerHTML = '<div class="kid-tab-loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

    // Lazy-fetch the tab content
    const urls = {
        goals: '/kid/tab/goals',
        wishes: '/kid/tab/wishes',
        activity: '/kid/tab/activity',
    };

    fetch(urls[tabName], {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(r => r.text())
        .then(html => {
            targetPanel.innerHTML = html;
            kidLoadedTabs.add(tabName);
            // Re-init Alpine on injected content
            if (window.Alpine) Alpine.initTree(targetPanel);
            if (tabName === 'activity') kidRenderActivityTab();
            // Handle post-load actions for goals tab
            if (tabName === 'goals') {
                // Priority 1: open edit modal (passed via options or URL param before URL was cleaned)
                const editGoalId = options.editGoalId;
                // Priority 2: prefill create modal
                const urlParams = new URLSearchParams(window.location.search);
                const prefillTitle = urlParams.get('prefill_title');
                const prefillAmount = urlParams.get('prefill_amount');
                if (editGoalId) {
                    setTimeout(() => window.kidOpenEditGoalModal(editGoalId), 100);
                } else if (prefillTitle || prefillAmount) {
                    setTimeout(() => {
                        window.kidOpenCreateGoalModal({ title: prefillTitle || '', amount: prefillAmount || '' });
                    }, 100);
                    history.replaceState({}, '', window.location.pathname);
                }
            }
        })
        .catch(() => {
            targetPanel.innerHTML = '<div class="kid-empty-state"><p>Failed to load. Please try again.</p></div>';
        });
}

// ============================================
// LEDGER FUNCTIONS
// ============================================

function kidToggleLedger() {
    const ledgerModal = document.getElementById('kidLedgerSection');
    if (!ledgerModal) return;

    if (ledgerModal.classList.contains('active')) {
        ledgerModal.classList.remove('active');
        kidLedgerOpen = false;
    } else {
        // Close any open forms first
        if (kidActiveForm === 'deposit') {
            kidCloseDepositForm();
        } else if (kidActiveForm === 'spend') {
            kidCloseSpendForm();
        }
        ledgerModal.classList.add('active');
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

function kidToggleInlineForm(type) {
    const depositInline = document.getElementById('kidDepositInline');
    const spendInline = document.getElementById('kidSpendInline');

    if (type === 'deposit') {
        const isOpen = depositInline && depositInline.style.display !== 'none';
        if (spendInline) spendInline.style.display = 'none';
        if (depositInline) {
            depositInline.style.display = isOpen ? 'none' : 'block';
            if (!isOpen) setTimeout(() => { const el = document.getElementById('kidDepositNote'); if (el) el.focus(); }, 50);
        }
        kidActiveForm = isOpen ? null : 'deposit';
    } else if (type === 'spend') {
        const isOpen = spendInline && spendInline.style.display !== 'none';
        if (depositInline) depositInline.style.display = 'none';
        if (spendInline) {
            spendInline.style.display = isOpen ? 'none' : 'block';
            if (!isOpen) setTimeout(() => { const el = document.getElementById('kidSpendNote'); if (el) el.focus(); }, 50);
        }
        kidActiveForm = isOpen ? null : 'spend';
    } else {
        if (depositInline) depositInline.style.display = 'none';
        if (spendInline) spendInline.style.display = 'none';
        kidActiveForm = null;
    }
}

function kidOpenDepositForm() { kidToggleInlineForm('deposit'); }
function kidCloseDepositForm() { kidToggleInlineForm(null); }
function kidOpenSpendForm() { kidToggleInlineForm('spend'); }
function kidCloseSpendForm() { kidToggleInlineForm(null); }

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
    const btn = event.submitter || event.target.querySelector('[type=submit]');

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
    const depositStart = Date.now();

    fetch('/kid/deposit', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ amount: numAmount, note: note })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                kidUpdateBalance(numAmount);
                kidLedgerData.unshift({
                    type: 'deposit', amount: numAmount, note: note,
                    timestamp: Math.floor(Date.now() / 1000),
                    date: new Date().toISOString().split('T')[0],
                    parentInitiated: false, initiated_by: 'kid'
                });
                if (kidLedgerOpen) kidRenderLedger();

                // Ensure spinner shows for at least 1400ms
                const remaining = Math.max(0, 1400 - (Date.now() - depositStart));
                setTimeout(() => {
                    btn.classList.remove('loading');
                    btn.innerHTML = '<i class="fas fa-check"></i> Added!';
                    btn.classList.add('success');

                    // Show success for 1100ms then fade out
                    setTimeout(() => {
                        const container = document.getElementById('kidDepositInline');
                        if (container) container.classList.add('fading-out');
                        setTimeout(() => {
                            kidCloseDepositForm();
                            if (container) container.classList.remove('fading-out');
                            btn.innerHTML = '+ Record';
                            btn.classList.remove('success');
                            btn.disabled = false;
                        }, 400);
                    }, 1100);
                }, remaining);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btn.classList.remove('loading');
            btn.innerHTML = 'Try Again';
            btn.disabled = false;
            setTimeout(() => { btn.innerHTML = '+ Record'; }, 2000);
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
    const btn = event.submitter || event.target.querySelector('[type=submit]');

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
    const spendStart = Date.now();

    fetch('/kid/spend', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ amount: numAmount, note: note })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                kidUpdateBalance(-numAmount);
                kidLedgerData.unshift({
                    type: 'spend', amount: -numAmount, note: note,
                    timestamp: Math.floor(Date.now() / 1000),
                    date: new Date().toISOString().split('T')[0],
                    parentInitiated: false, initiated_by: 'kid'
                });
                if (kidLedgerOpen) kidRenderLedger();

                // Ensure spinner shows for at least 1400ms
                const remaining = Math.max(0, 1400 - (Date.now() - spendStart));
                setTimeout(() => {
                    btn.classList.remove('loading');
                    btn.innerHTML = '<i class="fas fa-check"></i> Recorded!';
                    btn.classList.add('success');

                    // Show success for 1100ms then fade out
                    setTimeout(() => {
                        const container = document.getElementById('kidSpendInline');
                        if (container) container.classList.add('fading-out');
                        setTimeout(() => {
                            kidCloseSpendForm();
                            if (container) container.classList.remove('fading-out');
                            btn.innerHTML = '− Record';
                            btn.classList.remove('success');
                            btn.disabled = false;
                        }, 400);
                    }, 1100);
                }, remaining);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btn.classList.remove('loading');
            btn.innerHTML = 'Try Again';
            btn.disabled = false;
            setTimeout(() => { btn.innerHTML = '− Record'; }, 2000);
        });
}

// ============================================
// ACTIVITY TAB RENDERING
// ============================================

let activityTabFilter = 'all';
let activityTabTimeRange = 'all';
let activityTabPage = 1;
const ACTIVITY_PAGE_SIZE = 20;

function kidFriendlyTime(timestamp) {
    const now = new Date();
    const dateObj = new Date(timestamp * 1000);
    const todayStr = now.toDateString();
    const dateStr = dateObj.toDateString();
    const timeStr = dateObj.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });

    const yesterday = new Date(now);
    yesterday.setDate(yesterday.getDate() - 1);

    if (dateStr === todayStr) return 'Today at ' + timeStr;
    if (dateStr === yesterday.toDateString()) return 'Yesterday at ' + timeStr;
    return dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + ' at ' + timeStr;
}

function kidActivityTypeBadge(entry) {
    const isDenied = entry.note === 'Allowance not earned - insufficient points';
    if (isDenied) return '<span class="kid-activity-type-badge denied">Denied</span>';
    const isAllowance = entry.note && entry.note.toLowerCase().includes('allowance');
    if (isAllowance && entry.type === 'deposit') return '<span class="kid-activity-type-badge allowance">Allowance</span>';
    if (entry.type === 'deposit') return '<span class="kid-activity-type-badge deposit">Deposit</span>';
    if (entry.type === 'spend' || entry.type === 'withdrawal') return '<span class="kid-activity-type-badge withdrawal">Spend</span>';
    if (entry.type === 'points') return '<span class="kid-activity-type-badge points">Points</span>';
    return '';
}

function kidActivityIcon(entry) {
    const isDenied = entry.note === 'Allowance not earned - insufficient points';
    if (isDenied) return '<i class="fas fa-times"></i>';
    if (entry.type === 'points') return '<i class="fas fa-star"></i>';
    if (entry.type === 'deposit') return '<i class="fas fa-arrow-down"></i>';
    return '<i class="fas fa-arrow-up"></i>';
}

function kidRenderActivityTab() {
    const container = document.getElementById('kidActivityTabList');
    if (!container) return;

    let filtered = kidLedgerData;

    if (activityTabFilter !== 'all') {
        filtered = filtered.filter(e => e.type === activityTabFilter);
    }

    const now = new Date();
    if (activityTabTimeRange !== 'all') {
        filtered = filtered.filter(entry => {
            const entryDate = new Date(entry.timestamp * 1000);
            if (activityTabTimeRange === 'week') return entryDate >= new Date(now - 7 * 86400000);
            if (activityTabTimeRange === 'month') return entryDate >= new Date(now.getFullYear(), now.getMonth(), 1);
            if (activityTabTimeRange === 'year') return entryDate >= new Date(now.getFullYear(), 0, 1);
            return true;
        });
    }

    if (filtered.length === 0) {
        container.innerHTML = '<div class="kid-empty-state"><p>No transactions found for this filter.</p></div>';
        const pag = document.getElementById('kidActivityPagination');
        if (pag) pag.innerHTML = '';
        const countBar = document.getElementById('kidActivityCountBar');
        if (countBar) countBar.textContent = '';
        return;
    }

    const totalPages = Math.ceil(filtered.length / ACTIVITY_PAGE_SIZE);
    if (activityTabPage > totalPages) activityTabPage = totalPages;

    const start = (activityTabPage - 1) * ACTIVITY_PAGE_SIZE;
    const pageItems = filtered.slice(start, start + ACTIVITY_PAGE_SIZE);

    // Update count bar
    const countBar = document.getElementById('kidActivityCountBar');
    if (countBar) {
        const showStart = start + 1;
        const showEnd = Math.min(start + ACTIVITY_PAGE_SIZE, filtered.length);
        const label = filtered.length === 1 ? 'transaction' : 'transactions';
        countBar.textContent = `Showing ${showStart}–${showEnd} of ${filtered.length} ${label}`;
    }

    container.innerHTML = pageItems.map(entry => {
        const isParentInitiated = entry.parentInitiated || entry.initiated_by === 'parent';
        const isDenied = entry.note === 'Allowance not earned - insufficient points';
        const friendlyDate = kidFriendlyTime(entry.timestamp);
        const badge = kidActivityTypeBadge(entry);
        const icon = kidActivityIcon(entry);

        let amountStr = '';
        const amountClass = entry.type === 'deposit' ? 'deposit' : entry.type === 'spend' || entry.type === 'withdrawal' ? 'withdrawal' : 'points';
        if (entry.type === 'points') {
            amountStr = (entry.amount >= 0 ? '+' : '') + entry.amount + ' pts';
        } else {
            amountStr = (entry.amount >= 0 ? '+$' : '-$') + Math.abs(entry.amount).toFixed(2);
        }

        return `<div class="kid-activity-item${isParentInitiated ? ' parent-initiated' : ''}${isDenied ? ' denied-allowance' : ''}">
            <div class="kid-activity-icon ${amountClass}">${icon}</div>
            <div class="kid-activity-details">
                <div class="kid-activity-note">${entry.note}${isParentInitiated ? ' <span class="kid-parent-badge">P</span>' : ''}</div>
                <div class="kid-activity-meta">
                    <span class="kid-activity-date">${friendlyDate}</span>
                    ${badge}
                </div>
            </div>
            <div class="kid-activity-amount ${amountClass}">${amountStr}</div>
        </div>`;
    }).join('');

    // Render pagination
    const pag = document.getElementById('kidActivityPagination');
    if (pag) {
        if (totalPages <= 1) {
            pag.innerHTML = '';
        } else {
            pag.innerHTML = `
                <div class="kid-activity-pagination">
                    <button class="kid-activity-page-btn" onclick="kidActivityPagePrev()" ${activityTabPage <= 1 ? 'disabled' : ''}>
                        <i class="fas fa-chevron-left"></i> Prev
                    </button>
                    <span class="kid-activity-page-info">${activityTabPage} of ${totalPages}</span>
                    <button class="kid-activity-page-btn" onclick="kidActivityPageNext(${totalPages})" ${activityTabPage >= totalPages ? 'disabled' : ''}>
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>`;
        }
    }
}

function kidActivityPagePrev() {
    if (activityTabPage > 1) { activityTabPage--; kidRenderActivityTab(); }
}

function kidActivityPageNext(totalPages) {
    if (activityTabPage < totalPages) { activityTabPage++; kidRenderActivityTab(); }
}

function kidActivityTabFilter(filter) {
    activityTabFilter = filter;
    activityTabPage = 1;
    document.querySelectorAll('.kid-activity-filter-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    kidRenderActivityTab();
}

function kidActivityTabTimeFilter() {
    const select = document.getElementById('kidActivityTimeFilter');
    activityTabTimeRange = select.value;
    activityTabPage = 1;
    kidRenderActivityTab();
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
window.kidToggleInlineForm = kidToggleInlineForm;
window.kidOpenDepositForm = kidOpenDepositForm;
window.kidCloseDepositForm = kidCloseDepositForm;
window.kidOpenSpendForm = kidOpenSpendForm;
window.kidCloseSpendForm = kidCloseSpendForm;
window.kidSubmitDeposit = kidSubmitDeposit;
window.kidSubmitSpend = kidSubmitSpend;
window.kidFormatCurrency = kidFormatCurrency;
window.kidRenderLedger = kidRenderLedger;

window.kidOpenTransactionModal = kidOpenTransactionModal;
window.kidCloseTransactionModal = kidCloseTransactionModal;
window.kidModalFilter = kidModalFilter;
window.kidModalTimeFilter = kidModalTimeFilter;
window.kidUpdateBalance = kidUpdateBalance;
window.kidSwitchTab = kidSwitchTab;
window.kidReloadTab = function(tabName) { kidLoadedTabs.delete(tabName); kidSwitchTab(tabName); };
window.kidRenderActivityTab = kidRenderActivityTab;
window.kidActivityTabFilter = kidActivityTabFilter;
window.kidActivityTabTimeFilter = kidActivityTabTimeFilter;
window.kidActivityPagePrev = kidActivityPagePrev;
window.kidActivityPageNext = kidActivityPageNext;

// Wish card interaction functions
let kidRequestActiveWishId = null;

window.kidWishAskToBuy = function(wishId, wishName, wishPrice) {
    kidRequestActiveWishId = wishId;

    // Populate modal
    const balance = kidBalance || 0;
    const afterBalance = balance - wishPrice;

    const nameEl = document.getElementById('kidRequestWishName');
    const priceEl = document.getElementById('kidRequestWishPrice');
    const balEl = document.getElementById('kidRequestCurrentBalance');
    const afterEl = document.getElementById('kidRequestAfterBalance');

    if (nameEl) nameEl.textContent = wishName;
    if (priceEl) priceEl.textContent = '$' + parseFloat(wishPrice).toFixed(2);
    if (balEl) balEl.textContent = '$' + parseFloat(balance).toFixed(2);
    if (afterEl) {
        afterEl.textContent = '$' + afterBalance.toFixed(2);
        afterEl.style.color = afterBalance >= 0 ? '#10b981' : '#ef4444';
    }

    const modal = document.getElementById('kidRequestPurchaseModal');
    if (modal) modal.style.display = 'flex';
};

window.kidCloseRequestModal = function() {
    const modal = document.getElementById('kidRequestPurchaseModal');
    if (modal) modal.style.display = 'none';
    kidRequestActiveWishId = null;
};

window.kidWishConfirmRequest = async function() {
    const wishId = kidRequestActiveWishId;
    if (!wishId) return;

    const confirmBtn = document.getElementById('kidRequestConfirmBtn');
    if (confirmBtn) { confirmBtn.disabled = true; confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...'; }

    try {
        const response = await fetch('/kid/wishes/' + wishId + '/request-purchase', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        const result = await response.json();
        if (result.success) {
            window.kidCloseRequestModal();
            kidLoadedTabs.delete('wishes');
            kidSwitchTab('wishes');
        } else {
            alert(result.message || 'Could not send request.');
            if (confirmBtn) { confirmBtn.disabled = false; confirmBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Yes, Ask Parent!'; }
        }
    } catch (e) {
        alert('An error occurred.');
        if (confirmBtn) { confirmBtn.disabled = false; confirmBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Yes, Ask Parent!'; }
    }
};

window.kidWishRemind = async function(wishId) {
    const btn = document.getElementById('kidRemindBtn' + wishId);
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; }

    try {
        const response = await fetch('/kid/wishes/' + wishId + '/remind', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        const result = await response.json();
        if (result.success) {
            if (btn) { btn.innerHTML = '<i class="fas fa-check"></i> Sent!'; }
            setTimeout(() => {
                if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-clock"></i> Pending'; }
            }, 2000);
        } else {
            alert(result.message || 'Could not send reminder.');
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-bell"></i> Remind Parent'; }
        }
    } catch (e) {
        alert('An error occurred.');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-bell"></i> Remind Parent'; }
    }
};

window.kidOpenCreateWishModal = function() {
    const modal = document.getElementById('kidCreateWishModal');
    if (modal) modal.classList.add('active');
};

window.kidCloseInlineWishModal = function() {
    const modal = document.getElementById('kidCreateWishModal');
    if (!modal) return;
    modal.classList.remove('active');
    const form = document.getElementById('kidInlineWishForm');
    if (form) form.reset();
    const errDiv = document.getElementById('kidScrapeError');
    const partDiv = document.getElementById('kidScrapePartial');
    const imgGroup = document.getElementById('kidWishImagePreviewGroup');
    const imgPreview = document.getElementById('kidWishImagePreview');
    const imgUrl = document.getElementById('kidWishScrapedImageUrl');
    const fileName = document.getElementById('kidWishImageFileName');
    if (errDiv) errDiv.style.display = 'none';
    if (partDiv) partDiv.style.display = 'none';
    if (imgGroup) imgGroup.style.display = 'none';
    if (imgPreview) imgPreview.src = '';
    if (imgUrl) imgUrl.value = '';
    if (fileName) fileName.textContent = 'Choose a photo...';
};

window.kidWishPreviewFile = function(input) {
    const imgGroup = document.getElementById('kidWishImagePreviewGroup');
    const imgPreview = document.getElementById('kidWishImagePreview');
    const imgUrl = document.getElementById('kidWishScrapedImageUrl');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            if (imgPreview) imgPreview.src = e.target.result;
            if (imgGroup) imgGroup.style.display = 'block';
            // Clear any previously scraped URL since we're uploading a file
            if (imgUrl) imgUrl.value = '';
        };
        reader.readAsDataURL(input.files[0]);
    }
};

window.kidScrapeWishUrl = async function() {
    const urlInput = document.getElementById('kidWishUrl');
    let url = urlInput ? urlInput.value.trim() : '';
    if (!url) { alert('Please enter a URL first'); return; }
    // Auto-prepend https:// if no scheme present
    if (!/^https?:\/\//i.test(url)) { url = 'https://' + url; }

    const modal = document.getElementById('kidCreateWishModal');
    const scrapeUrl = modal ? modal.dataset.scrapeUrl : '/kid/wishes/scrape-url';
    const btn = document.getElementById('kidScrapeBtn');
    const errorDiv = document.getElementById('kidScrapeError');
    const partialDiv = document.getElementById('kidScrapePartial');

    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...'; }
    if (errorDiv) errorDiv.style.display = 'none';
    if (partialDiv) partialDiv.style.display = 'none';

    try {
        const response = await fetch(scrapeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ url })
        });
        const result = await response.json();

        if (result.success) {
            let found = [], missing = [];
            const nameEl = document.getElementById('kidWishItemName');
            const priceEl = document.getElementById('kidWishPrice');
            const imgPreview = document.getElementById('kidWishImagePreview');
            const imgGroup = document.getElementById('kidWishImagePreviewGroup');
            const imgUrl = document.getElementById('kidWishScrapedImageUrl');
            if (result.data.title && nameEl) { nameEl.value = result.data.title; found.push('name'); } else { missing.push('name'); }
            if (result.data.price && priceEl) { priceEl.value = result.data.price; found.push('price'); } else { missing.push('price'); }
            if (result.data.image_url && imgUrl) {
                imgUrl.value = result.data.image_url;
                if (imgPreview) imgPreview.src = result.data.image_url;
                if (imgGroup) imgGroup.style.display = 'block';
                found.push('image');
            } else { missing.push('image'); }
            if (missing.length > 0 && found.length > 0 && partialDiv) {
                partialDiv.textContent = 'Found ' + found.join(', ') + '. Please fill in the remaining details.';
                partialDiv.style.display = 'block';
            }
        } else {
            if (errorDiv) { errorDiv.textContent = result.error || 'Could not auto-fill. Please enter details manually.'; errorDiv.style.display = 'block'; }
        }
    } catch (e) {
        console.error('Scrape error:', e);
        if (errorDiv) { errorDiv.textContent = 'Could not auto-fill. Please enter details manually.'; errorDiv.style.display = 'block'; }
    } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-magic"></i> Auto-fill'; }
    }
};
// ============================================
// GOAL MODAL FUNCTIONS
// ============================================

window.kidOpenCreateGoalModal = function(prefill = {}) {
    window.dispatchEvent(new CustomEvent('kid-open-goal-modal', { detail: { mode: 'create', prefill } }));
};

window.kidOpenEditGoalModal = function(goalId) {
    window.dispatchEvent(new CustomEvent('kid-open-goal-modal', { detail: { mode: 'edit', goalId } }));
};

// Expose as a global function so Alpine can find it via x-data="kidGoalModal()"
// Scripts in AJAX-injected HTML don't execute, so this must be pre-registered here
window.kidGoalModal = kidGoalModalComponent;

function kidGoalModalComponent() {
    return {
        showModal: false,
        isEditMode: false,
        editGoalId: null,
        currentGoalAmount: 0,
        currentGoalAllocation: 0,
        balance: 0,
        totalAllocated: 0,
        weeklyAllowanceAmount: 0,
        submitting: false,
        photoPreview: null,
        photoFile: null,
        showModalAddFunds: false,
        showModalRemoveFunds: false,
        modalAddAmount: '',
        modalRemoveAmount: '',
        modalAddLoading: false,
        modalAddSuccess: false,
        modalAddError: '',
        modalRemoveLoading: false,
        modalRemoveSuccess: false,
        modalRemoveError: '',
        scrapeError: '',
        scrapePartial: '',
        scrapeLoading: false,
        formData: {
            title: '',
            description: '',
            product_url: '',
            target_amount: '',
            auto_allocation_percentage: 0,
        },

        get weeklyAllowance() { return this.weeklyAllowanceAmount; },

        get remainingAllocation() {
            if (this.isEditMode) {
                const other = this.totalAllocated - this.currentGoalAllocation;
                return 100 - other - (parseFloat(this.formData.auto_allocation_percentage) || 0);
            }
            return 100 - this.totalAllocated - (parseFloat(this.formData.auto_allocation_percentage) || 0);
        },

        get allocationExceedsLimit() { return this.remainingAllocation < 0; },

        get maxAllowedAllocation() {
            if (this.isEditMode) {
                return 100 - (this.totalAllocated - this.currentGoalAllocation);
            }
            return 100 - this.totalAllocated;
        },

        get autoAllocationAmount() {
            if (!this.formData.auto_allocation_percentage || this.formData.auto_allocation_percentage <= 0) return 0;
            return (this.weeklyAllowance * parseFloat(this.formData.auto_allocation_percentage)) / 100;
        },

        get weeksToComplete() {
            if (!this.formData.target_amount || !this.autoAllocationAmount || this.autoAllocationAmount <= 0) return 0;
            const target = parseFloat(this.formData.target_amount) || 0;
            const current = this.isEditMode ? this.currentGoalAmount : 0;
            const remaining = target - current;
            if (remaining <= 0) return 0;
            return Math.ceil(remaining / this.autoAllocationAmount);
        },

        get timeToCompleteText() {
            if (!this.weeksToComplete) return '';
            if (this.weeksToComplete > 51) {
                const months = Math.round(this.weeksToComplete / 4.33);
                return months + ' month' + (months !== 1 ? 's' : '');
            }
            return this.weeksToComplete + ' week' + (this.weeksToComplete !== 1 ? 's' : '');
        },

        get modalHasSufficientFunds() {
            if (!this.modalAddAmount) return false;
            return this.balance >= parseInt(this.modalAddAmount) / 100;
        },

        get modalHasSufficientGoalFunds() {
            if (!this.modalRemoveAmount) return false;
            return this.currentGoalAmount >= parseInt(this.modalRemoveAmount) / 100;
        },

        init() {
            // Read data values from DOM attributes (set in Blade template)
            const el = this.$el;
            this.balance = parseFloat(el.dataset.kidBalance || 0);
            this.totalAllocated = parseFloat(el.dataset.totalAllocated || 0);
            this.weeklyAllowanceAmount = parseFloat(el.dataset.allowance || 0);

            window.addEventListener('kid-open-goal-modal', (event) => {
                if (event.detail.mode === 'create') {
                    this.openCreate(event.detail.prefill || {});
                } else if (event.detail.mode === 'edit') {
                    this.openEdit(event.detail.goalId);
                }
            });
        },

        openCreate(prefill = {}) {
            this.isEditMode = false;
            this.editGoalId = null;
            this.resetForm();
            if (prefill.title) this.formData.title = prefill.title;
            if (prefill.amount) this.formData.target_amount = prefill.amount;
            this.showModal = true;
        },

        openEdit(goalId) {
            this.isEditMode = true;
            this.editGoalId = goalId;
            this.loadGoalData(goalId);
            this.showModal = true;
        },

        loadGoalData(goalId) {
            fetch(`/kid/goals/${goalId}/edit-data`, {
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
            })
            .then(r => r.json())
            .then(data => {
                this.formData.title = data.title || '';
                this.formData.description = data.description || '';
                this.formData.product_url = data.product_url || '';
                this.formData.target_amount = data.target_amount || '';
                this.formData.auto_allocation_percentage = data.auto_allocation_percentage || '';
                this.currentGoalAmount = parseFloat(data.current_amount) || 0;
                this.currentGoalAllocation = parseFloat(data.auto_allocation_percentage) || 0;
                if (data.photo_path) this.photoPreview = `/storage/${data.photo_path}`;
            })
            .catch(() => alert('Error loading goal data'));
        },

        closeModal() {
            this.showModal = false;
            this.resetForm();
        },

        handleModalCurrencyInput(event, type) {
            let input = event.target.value.replace(/[^0-9]/g, '');
            if (type === 'add') this.modalAddAmount = input;
            else this.modalRemoveAmount = input;
            if (input === '') { event.target.value = ''; }
            else { event.target.value = '$' + (parseInt(input) / 100).toFixed(2); }
        },

        submitModalAddFunds() {
            const cents = parseInt(this.modalAddAmount);
            if (isNaN(cents) || cents <= 0) { alert('Please enter a valid amount'); return; }
            const amount = cents / 100;
            this.modalAddLoading = true;
            fetch(`/kid/goals/${this.editGoalId}/add-funds`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ amount })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.balance -= amount;
                    this.currentGoalAmount += amount;
                    this.modalAddLoading = false;
                    this.modalAddSuccess = true;
                    setTimeout(() => { this.modalAddSuccess = false; this.showModalAddFunds = false; this.modalAddAmount = ''; location.reload(); }, 1500);
                } else { alert(data.message || 'Failed to add funds'); this.modalAddLoading = false; }
            })
            .catch(() => { alert('An error occurred'); this.modalAddLoading = false; });
        },

        submitModalRemoveFunds() {
            const cents = parseInt(this.modalRemoveAmount);
            if (isNaN(cents) || cents <= 0) { alert('Please enter a valid amount'); return; }
            const amount = cents / 100;
            this.modalRemoveLoading = true;
            fetch(`/kid/goals/${this.editGoalId}/remove-funds`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ amount })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.balance += amount;
                    this.currentGoalAmount -= amount;
                    this.modalRemoveLoading = false;
                    this.modalRemoveSuccess = true;
                    setTimeout(() => { this.modalRemoveSuccess = false; this.showModalRemoveFunds = false; this.modalRemoveAmount = ''; location.reload(); }, 1500);
                } else { alert(data.message || 'Failed to remove funds'); this.modalRemoveLoading = false; }
            })
            .catch(() => { alert('An error occurred'); this.modalRemoveLoading = false; });
        },

        resetForm() {
            this.formData = { title: '', description: '', product_url: '', target_amount: '', auto_allocation_percentage: 0 };
            this.currentGoalAllocation = 0;
            this.photoPreview = null;
            this.photoFile = null;
            this.showModalAddFunds = false;
            this.showModalRemoveFunds = false;
            this.modalAddAmount = '';
            this.modalRemoveAmount = '';
            this.scrapeError = '';
            this.scrapePartial = '';
            this.scrapeLoading = false;
            this._scrapedImageUrl = null;
        },

        async scrapeGoalUrl() {
            let url = (this.formData.product_url || '').trim();
            if (!url) { this.scrapeError = 'Please enter a URL first'; return; }
            if (!/^https?:\/\//i.test(url)) { url = 'https://' + url; this.formData.product_url = url; }

            const form = this.$el.closest('form[data-scrape-url]') || document.querySelector('form[data-scrape-url]');
            const scrapeUrl = form ? form.dataset.scrapeUrl : '/kid/goals/scrape-url';
            const btn = document.getElementById('kidGoalScrapeBtn');

            this.scrapeError = '';
            this.scrapePartial = '';
            this.scrapeLoading = true;
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...'; }

            try {
                const response = await fetch(scrapeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ url })
                });
                const result = await response.json();

                if (result.success) {
                    let found = [], missing = [];
                    if (result.data.title) { this.formData.title = result.data.title; found.push('name'); } else { missing.push('name'); }
                    if (result.data.price) { this.formData.target_amount = result.data.price; found.push('price'); } else { missing.push('price'); }
                    if (result.data.image_url) {
                        this.photoPreview = result.data.image_url;
                        this.photoFile = null; // will be handled via URL on submit
                        this._scrapedImageUrl = result.data.image_url;
                        found.push('image');
                    } else { missing.push('image'); }
                    if (missing.length > 0 && found.length > 0) {
                        this.scrapePartial = 'Found ' + found.join(', ') + '. Please fill in the remaining details.';
                    }
                } else {
                    this.scrapeError = result.error || 'Could not auto-fill. Please enter details manually.';
                }
            } catch (e) {
                this.scrapeError = 'Could not auto-fill. Please enter details manually.';
            } finally {
                this.scrapeLoading = false;
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-magic"></i> Auto-fill'; }
            }
        },

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) this.processFile(file);
        },

        handleFileDrop(event) {
            const file = event.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) this.processFile(file);
        },

        processFile(file) {
            this.photoFile = file;
            const reader = new FileReader();
            reader.onload = (e) => { this.photoPreview = e.target.result; };
            reader.readAsDataURL(file);
        },

        removePhoto() {
            this.photoPreview = null;
            this.photoFile = null;
            const el = document.getElementById('kidGoalPhoto');
            if (el) el.value = '';
        },

        submitForm() {
            if (this.submitting) return;
            this.submitting = true;
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name=csrf-token]').content);
            formData.append('title', this.formData.title);
            formData.append('description', this.formData.description || '');
            formData.append('product_url', this.formData.product_url || '');
            formData.append('target_amount', (this.formData.target_amount || '').replace(/[^0-9.]/g, ''));
            formData.append('auto_allocation_percentage', this.formData.auto_allocation_percentage || '0');
            if (this.photoFile) formData.append('photo', this.photoFile);
            else if (this._scrapedImageUrl) formData.append('scraped_image_url', this._scrapedImageUrl);
            const url = this.isEditMode ? `/kid/goals/${this.editGoalId}` : '/kid/goals';
            if (this.isEditMode) formData.append('_method', 'PUT');
            fetch(url, {
                method: 'POST',
                body: formData,
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => {
                if (!r.ok) {
                    return r.text().then(t => {
                        let msg = 'Server error: ' + r.status;
                        try { msg = JSON.parse(t).message || msg; } catch(e) {}
                        throw new Error(msg);
                    });
                }
                // Navigate to dashboard goals tab regardless of response body
                window.location.href = '/kid/dashboard?tab=goals';
            })
            .catch(error => { alert(error.message); this.submitting = false; });
        },

        deleteGoal() {
            window.dispatchEvent(new CustomEvent('kid-open-delete-goal-modal', { detail: { callback: () => this.performDelete() } }));
        },

        performDelete() {
            fetch(`/kid/goals/${this.editGoalId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ _method: 'DELETE' })
            })
            .then(r => {
                if (!r.ok) return r.json().then(d => { throw new Error(d.error || d.message || 'Error'); });
                window.location.href = '/kid/dashboard?tab=goals';
            })
            .catch(error => { alert(error.message || 'An error occurred. Please try again.'); });
        },

        formatCurrency(value) {
            let numValue = value.replace(/[^0-9]/g, '');
            if (numValue === '') return '';
            return '$' + (parseInt(numValue) / 100).toFixed(2);
        }
    };
}
