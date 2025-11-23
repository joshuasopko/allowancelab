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
function toggleForm(formId, buttonElement = null) {
    const form = document.getElementById(formId + 'Form');

    if (activeForm && activeForm !== form) {
        activeForm.classList.remove('active');
    }

    form.classList.toggle('active');
    activeForm = form.classList.contains('active') ? form : null;

    // Toggle button text if button element is provided
    if (buttonElement) {
        if (form.classList.contains('active')) {
            buttonElement.textContent = 'Close Ledger';
        } else {
            buttonElement.textContent = 'View Ledger';
        }
    }
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

    // Update preview
    document.getElementById('avatarPreviewManage').style.background = element.dataset.color;

    // Update color name
    const colorNames = {
        '#80d4b0': 'Mint',
        '#ff9999': 'Rose Pink',
        '#b19cd9': 'Lavender',
        '#87ceeb': 'Sky Blue',
        '#ffb380': 'Peach',
        '#e066a6': 'Magenta',
        '#ffd966': 'Sunshine',
        '#a8c686': 'Sage',
        '#5ab9b3': 'Teal',
        '#9bb7d4': 'Periwinkle',
        '#ff9966': 'Coral',
        '#d4a5d4': 'Orchid'
    };
    document.getElementById('colorNameManage').textContent = colorNames[element.dataset.color] || '';
}

// Set initial color name on manage page load
document.addEventListener('DOMContentLoaded', function () {
    const colorInputManage = document.getElementById('colorInputManage');
    if (colorInputManage) {
        const colorNames = {
            '#80d4b0': 'Mint',
            '#ff9999': 'Rose Pink',
            '#b19cd9': 'Lavender',
            '#87ceeb': 'Sky Blue',
            '#ffb380': 'Peach',
            '#e066a6': 'Magenta',
            '#ffd966': 'Sunshine',
            '#a8c686': 'Sage',
            '#5ab9b3': 'Teal',
            '#9bb7d4': 'Periwinkle',
            '#ff9966': 'Coral',
            '#d4a5d4': 'Orchid'
        };
        const currentColor = colorInputManage.value;
        const colorNameElement = document.getElementById('colorNameManage');
        if (colorNameElement) {
            colorNameElement.textContent = colorNames[currentColor] || '';
        }
    }
});

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

// Toggle invite options based on "Create account" checkbox
function toggleInviteOptions() {
    const createAccountCheckbox = document.getElementById('createAccount');
    const emailGroup = document.getElementById('emailGroup');
    const submitBtn = document.getElementById('submitBtn');

    if (createAccountCheckbox.checked) {
        // Show email field and change button text
        emailGroup.style.display = 'block';
        submitBtn.textContent = 'Add Kid & Send Invite';
    } else {
        // Hide email field and change button text
        emailGroup.style.display = 'none';
        submitBtn.textContent = 'Add Kid';
    }
}

// Variables to store the newly created kid info
let newKidId = null;
let newKidName = null;
let inviteToken = null;

// Intercept Add Kid form submission
document.addEventListener('DOMContentLoaded', function () {
    const addKidForm = document.getElementById('addKidForm');

    if (addKidForm) {
        addKidForm.addEventListener('submit', function (e) {
            e.preventDefault(); // Prevent normal form submission

            // Clear any existing error message
            const existingError = document.getElementById('formError');
            if (existingError) existingError.remove();

            // Validate required fields
            const name = document.querySelector('input[name="name"]').value.trim();
            const birthday = document.querySelector('input[name="birthday"]').value;
            const allowanceAmount = document.querySelector('input[name="allowance_amount"]').value.trim();
            const allowanceDay = document.querySelector('select[name="allowance_day"]').value;
            const color = document.querySelector('input[name="color"]').value;

            let errorMessage = '';

            if (!name) errorMessage = 'Please fill out Name';
            else if (!birthday) errorMessage = 'Please fill out Birthday';
            else if (birthday) {
                // Check if birthday is in the future
                const selectedDate = new Date(birthday);
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (selectedDate >= today) {
                    errorMessage = 'Birthday cannot be today or in the future';
                }
            }

            if (!errorMessage && !allowanceAmount) errorMessage = 'Please fill out Weekly Allowance';
            else if (!errorMessage && !allowanceDay) errorMessage = 'Please select Allowance Day';
            else if (!errorMessage && !color) errorMessage = 'Please select Avatar Color';

            // If there's an error, show toast and stop
            if (errorMessage) {
                const submitButton = document.querySelector('#addKidModal .modal-btn-submit');
                showToast(errorMessage, 'error', submitButton);
                return;
            }

            // Submit via AJAX
            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Store kid info
                        newKidId = data.kid.id;
                        newKidName = data.kid.name;

                        // Close Add Kid modal
                        const addKidModal = document.getElementById('addKidModal');
                        addKidModal.classList.remove('active');
                        addKidModal.style.display = 'none';

                        // Show success message
                        const successOverlay = document.createElement('div');
                        successOverlay.className = 'success-overlay';
                        successOverlay.innerHTML = `
    <div class="success-message">
        <div class="success-icon">✓</div>
        <h2>Success! Kid Added</h2>
        <p>Preparing invite options...</p>
    </div>
`;
                        document.body.appendChild(successOverlay);

                        // Wait 2.5 seconds, then show invite modal
                        setTimeout(() => {
                            successOverlay.remove();

                            const sendInviteModal = document.getElementById('sendInviteModal');
                            document.getElementById('inviteKidName').textContent = newKidName;
                            sendInviteModal.classList.add('active');
                            sendInviteModal.style.display = 'flex';
                        }, 2500);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error creating kid. Please try again.');
                });
        });
    }
});

// Close Send Invite Modal
function closeSendInviteModal() {
    const sendInviteModal = document.getElementById('sendInviteModal');
    sendInviteModal.classList.remove('active'); // Remove active class
    sendInviteModal.style.display = 'none';
    location.reload(); // Reload to show the new kid on dashboard
}

// Skip invite and close modal
function skipInvite() {
    closeSendInviteModal();
}

// Toggle invite method sections
let currentOpenMethod = null;
function toggleInviteMethod(method) {
    const methods = ['copyLink', 'email', 'qr'];

    // Close currently open method if clicking the same button
    if (currentOpenMethod === method) {
        document.getElementById(method + 'Content').style.display = 'none';
        currentOpenMethod = null;
        return;
    }

    // Close all methods
    methods.forEach(m => {
        document.getElementById(m + 'Content').style.display = 'none';
    });

    // Open selected method
    document.getElementById(method + 'Content').style.display = 'block';
    currentOpenMethod = method;

    // Generate content for the method
    if (method === 'copyLink') {
        generateInviteLink();
    } else if (method === 'qr') {
        generateQRCode();
    }
}

// Generate and display invite link
function generateInviteLink() {
    // Call backend to create invite and get token
    fetch(`/kids/${newKidId}/create-invite`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                inviteToken = data.token;
                const inviteUrl = `${window.location.origin}/invite/${inviteToken}`;
                document.getElementById('inviteLinkInput').value = inviteUrl;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error generating invite link.');
        });
}

// Copy invite link to clipboard
function copyInviteLink() {
    const input = document.getElementById('inviteLinkInput');
    input.select();
    document.execCommand('copy');

    // Get the copy button's position
    const copyButton = event.target;
    showToast('Copied!', 'success', copyButton);
}

// Toast notification helper
let activeErrorToast = null;

function showToast(message, type = 'success', nearElement = null) {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;

    if (nearElement) {
        const rect = nearElement.getBoundingClientRect();
        toast.style.position = 'fixed';
        toast.style.top = (rect.top - 50) + 'px';
        toast.style.left = rect.left + 'px';
    }

    document.body.appendChild(toast);

    // Trigger animation
    setTimeout(() => toast.classList.add('show'), 10);

    // Only auto-remove success toasts, keep error toasts until user fixes issue
    if (type === 'success') {
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 2000);
    } else if (type === 'error') {
        // Store reference to error toast so we can remove it later
        activeErrorToast = toast;
    }
}

// Remove error toast when user starts typing in any form field
document.addEventListener('DOMContentLoaded', function () {
    const addKidModal = document.getElementById('addKidModal');
    if (addKidModal) {
        addKidModal.addEventListener('input', function () {
            if (activeErrorToast) {
                activeErrorToast.classList.remove('show');
                setTimeout(() => {
                    if (activeErrorToast) {
                        activeErrorToast.remove();
                        activeErrorToast = null;
                    }
                }, 300);
            }
        });
    }
});

// Send email invite
function sendEmailInvite() {
    const email = document.getElementById('kidEmailInput').value;

    if (!email) {
        showToast('Please enter an email address', 'error');
        return;
    }

    // Disable button and show loading state
    const sendButton = event.target;
    const originalText = sendButton.innerHTML;
    sendButton.disabled = true;
    sendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

    fetch(`/kids/${newKidId}/send-email-invite`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ email: email })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Email sent successfully!', 'success', sendButton);
                setTimeout(() => {
                    closeSendInviteModal();
                }, 1500);
            } else {
                showToast('Failed to send email', 'error');
                sendButton.disabled = false;
                sendButton.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error sending email', 'error');
            sendButton.disabled = false;
            sendButton.innerHTML = originalText;
        });
}

// Generate QR Code
function generateQRCode() {
    fetch(`/kids/${newKidId}/qr-code`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                inviteToken = data.inviteUrl.split('/').pop();
                document.getElementById('qrCodeDisplay').innerHTML = `
                <div style="padding: 20px;">
                    <div style="display: inline-block; padding: 20px; background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        ${data.qrCode}
                    </div>
                    <p style="margin-top: 20px; color: #666; font-size: 14px;">Scan this code with your phone to create your account</p>
                    <p style="margin-top: 10px; color: #9ca3af; font-size: 12px;">QR code expires on ${data.expiresAt}</p>
                </div>
            `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('qrCodeDisplay').innerHTML = `
            <p style="color: #ef4444;">Error generating QR code. Please try Copy Link instead.</p>
        `;
        });
}

// Real-time username validation
function validateUsername(input) {
    const username = input.value.trim();
    const feedbackIcon = document.getElementById('usernameValidation');
    const errorMessage = document.getElementById('usernameError');

    // Clear previous state
    feedbackIcon.className = 'validation-icon';
    errorMessage.textContent = '';

    // Must have at least 3 characters to check
    if (username.length < 3) {
        feedbackIcon.className = 'validation-icon';
        return;
    }

    // Show loading state
    feedbackIcon.className = 'validation-icon loading';
    feedbackIcon.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    // Check username via AJAX
    fetch('/check-username', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ username: username })
    })
        .then(response => response.json())
        .then(data => {
            if (data.available) {
                feedbackIcon.className = 'validation-icon valid';
                feedbackIcon.innerHTML = '<i class="fas fa-check-circle"></i>';
                errorMessage.textContent = '';
            } else {
                feedbackIcon.className = 'validation-icon invalid';
                feedbackIcon.innerHTML = '<i class="fas fa-times-circle"></i>';
                errorMessage.textContent = data.message;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            feedbackIcon.className = 'validation-icon';
            feedbackIcon.innerHTML = '';
        });
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

// Invite modal functions
window.closeSendInviteModal = closeSendInviteModal;
window.skipInvite = skipInvite;
window.toggleInviteMethod = toggleInviteMethod;
window.copyInviteLink = copyInviteLink;
window.sendEmailInvite = sendEmailInvite;
window.generateQRCode = generateQRCode;
window.showToast = showToast;
window.validateUsername = validateUsername;