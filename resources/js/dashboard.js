/**
 * AllowanceLab Dashboard JavaScript
 * Parent dashboard functionality
 */

// ============================================
// GLOBAL VARIABLES
// ============================================
let activeForm = null;
let currentKidId = null;
let currentTypeFilter = 'all';
let currentDateRange = '30';

// ============================================
// FORM TOGGLE FUNCTIONS
// ============================================

// Toggle form dropdowns
function toggleForm(formId) {
    const form = document.getElementById(formId + 'Form');

    if (activeForm && activeForm !== form) {
        activeForm.classList.remove('active');
    }

    form.classList.toggle('active');
    activeForm = form.classList.contains('active') ? form : null;
}

// ============================================
// POINTS FUNCTIONS
// ============================================

// Points adjuster
function adjustPoints(kidId, change) {
    const input = document.getElementById('points-' + kidId);
    let currentValue = parseInt(input.value) || 0;
    input.value = currentValue + change;
}

// Toggle max points visibility in Add Kid modal
function toggleMaxPoints() {
    const checkbox = document.getElementById('usePoints');
    const maxPointsGroup = document.getElementById('maxPointsGroup');

    if (checkbox.checked) {
        maxPointsGroup.style.display = 'block';
    } else {
        maxPointsGroup.style.display = 'none';
    }
}

// ============================================
// LEDGER FUNCTIONS
// ============================================

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

// ============================================
// ADD KID MODAL FUNCTIONS
// ============================================

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

// ============================================
// MOBILE MENU
// ============================================

function toggleMobileMenu() {
    document.querySelector('.header-nav').classList.toggle('mobile-open');
}

// ============================================
// TRANSACTION MODAL FUNCTIONS
// ============================================

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

// ============================================
// CURRENCY FORMATTING
// ============================================

function formatCurrencyInput(input) {
    let value = input.value.replace(/\D/g, '');
    let numericValue = parseInt(value) || 0;
    let dollars = (numericValue / 100).toFixed(2);
    input.value = '$' + dollars;
}

function addCurrencyListeners() {
    document.querySelectorAll('input[name="amount"], input[name="allowance_amount"]').forEach(input => {
        input.addEventListener('input', function () {
            formatCurrencyInput(this);
        });
    });

    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function (e) {
            this.querySelectorAll('input[name="amount"], input[name="allowance_amount"]').forEach(input => {
                input.value = input.value.replace(/[$,]/g, '');
            });
        });
    });
}

// ============================================
// AJAX FORM SUBMISSIONS
// ============================================

function handleFormSubmit(form, successMessage) {
    form.addEventListener('submit', async function (e) {
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

// ============================================
// SESSION STORAGE FOR FORM STATE
// ============================================

// Remember open forms before reload
window.addEventListener('beforeunload', function () {
    const activeForm = document.querySelector('.dropdown-form.active');
    if (activeForm) {
        sessionStorage.setItem('reopenForm', activeForm.id);
    }
});

// ============================================
// INITIALIZATION
// ============================================

document.addEventListener('DOMContentLoaded', function () {
    // Reopen form after page loads
    const formToReopen = sessionStorage.getItem('reopenForm');
    if (formToReopen) {
        const form = document.getElementById(formToReopen);
        if (form) {
            form.classList.add('active');
            activeForm = form;  // Set the activeForm variable
        }
        sessionStorage.removeItem('reopenForm');
    }

    // Update avatar preview when name changes
    const kidNameInput = document.getElementById('kidName');
    if (kidNameInput) {
        kidNameInput.addEventListener('input', function (e) {
            const preview = document.getElementById('avatarPreview');
            const name = e.target.value.trim();
            preview.textContent = name ? name.charAt(0).toUpperCase() : '?';
        });
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

    // Close modal on backdrop click
    const addKidModal = document.getElementById('addKidModal');
    if (addKidModal) {
        addKidModal.addEventListener('click', function (e) {
            if (e.target === this) {
                closeAddKidModal();
            }
        });
    }
});

// ============================================
// MANAGE KID PAGE FUNCTIONS
// ============================================

// Color selection for manage page
function selectColorManage(element) {
    document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('selected'));
    element.classList.add('selected');
    document.getElementById('colorInputManage').value = element.dataset.color;
}

// Toggle max points visibility on manage page
function toggleMaxPointsManage() {
    const checkbox = document.getElementById('usePointsManage');
    const maxPointsGroup = document.getElementById('maxPointsGroupManage');

    if (checkbox.checked) {
        maxPointsGroup.style.display = 'block';
    } else {
        maxPointsGroup.style.display = 'none';
    }
}

// Delete confirmation modal
function confirmDeleteKid() {
    document.getElementById('deleteModal').classList.add('active');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
}

// ============================================
// MAKE FUNCTIONS GLOBALLY ACCESSIBLE
// ============================================
window.toggleForm = toggleForm;
window.adjustPoints = adjustPoints;
window.toggleMaxPoints = toggleMaxPoints;
window.filterLedger = filterLedger;
window.openAddKidModal = openAddKidModal;
window.closeAddKidModal = closeAddKidModal;
window.selectAvatar = selectAvatar;
window.selectColor = selectColor;
window.toggleMobileMenu = toggleMobileMenu;
window.openTransactionModal = openTransactionModal;
window.closeTransactionModal = closeTransactionModal;
window.filterTransactionModal = filterTransactionModal;
window.filterByDateRange = filterByDateRange;

window.selectColorManage = selectColorManage;
window.toggleMaxPointsManage = toggleMaxPointsManage;
window.confirmDeleteKid = confirmDeleteKid;
window.closeDeleteModal = closeDeleteModal;