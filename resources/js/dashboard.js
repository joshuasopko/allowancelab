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
    const sidebar = document.getElementById('parentSidebar');
    const hamburger = document.querySelector('.hamburger');

    sidebar.classList.toggle('mobile-open');
    hamburger.classList.toggle('active');
}

// Toggle Lab Tools dropdown
function toggleLabTools(event) {
    event.preventDefault();
    const dropdown = document.getElementById('labToolsDropdown');
    const icon = event.currentTarget.querySelector('.dropdown-icon');

    dropdown.classList.toggle('open');
    icon.classList.toggle('rotated');
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

        body.innerHTML = data.map(entry => {
            const isKidInitiated = entry.initiated_by === 'kid';
            const kidColor = entry.kid_color || '#87ceeb';

            // Convert hex to RGB for background
            const hex = kidColor.replace('#', '');
            const r = parseInt(hex.substr(0, 2), 16);
            const g = parseInt(hex.substr(2, 2), 16);
            const b = parseInt(hex.substr(4, 2), 16);
            const lightBg = `rgba(${r}, ${g}, ${b}, 0.08)`;

            // Determine type class for coloring
            let typeClass = entry.type;
            if (entry.type === 'points') {
                typeClass = 'points';
            }

            return `
        <div class="ledger-row" style="${isKidInitiated ? `background: ${lightBg};` : ''}">
            <div class="ledger-kid-icon-cell">
                ${isKidInitiated ? `<span class="ledger-kid-icon" style="color: ${kidColor};">K</span>` : ''}
            </div>
            <div class="ledger-date">${entry.date} | ${entry.time}</div>
            <div class="ledger-type ${typeClass}">${entry.type_label}</div>
            <div class="ledger-amount ${entry.amount_class || entry.type}">${entry.amount_display}</div>
            <div class="ledger-note">${entry.note || 'No note'}</div>
        </div>
    `;
        }).join('');

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

// Toggle resend invite options
function toggleResendInvite() {
    const options = document.getElementById('resendInviteOptions');
    if (options.style.display === 'none') {
        options.style.display = 'block';
    } else {
        options.style.display = 'none';
    }
}

// Show specific invite method on manage page
let currentManageMethod = null;
function showInviteMethod(method) {
    const methods = ['copyLink', 'email', 'qr'];

    // Close currently open method if clicking the same button
    if (currentManageMethod === method) {
        document.getElementById(method + 'ContentManage').style.display = 'none';
        currentManageMethod = null;
        return;
    }

    // Close all methods
    methods.forEach(m => {
        const content = document.getElementById(m + 'ContentManage');
        if (content) content.style.display = 'none';
    });

    // Open selected method
    document.getElementById(method + 'ContentManage').style.display = 'block';
    currentManageMethod = method;

    // Generate content for the method
    if (method === 'copyLink') {
        const linkInput = document.getElementById('inviteLinkInputManage');
        if (!linkInput.value) {
            createInviteForManage();
        }
    } else if (method === 'qr') {
        // QR code will be generated when button is clicked
    }
}

// Create invite if none exists (for "skip for now" case)
function createInviteForManage() {
    const kidId = window.location.pathname.split('/').pop().split('/')[0];

    fetch(`/kids/${kidId}/create-invite`, {
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
                const inviteUrl = `${window.location.origin}/invite/${data.token}`;
                const linkInput = document.getElementById('inviteLinkInputManage');
                if (linkInput) {
                    linkInput.value = inviteUrl;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error generating invite link', 'error');
        });
}

// Copy invite link from manage page
function copyInviteLinkManage() {
    const input = document.getElementById('inviteLinkInputManage');
    input.select();
    document.execCommand('copy');
    const copyButton = event.target.closest('button');
    showToast('Copied!', 'success', copyButton);
}

// Send email invite from manage page
function sendEmailInviteManage() {
    const email = document.getElementById('kidEmailInputManage').value;
    const kidId = window.location.pathname.split('/').pop().split('/')[0];

    if (!email) {
        showToast('Please enter an email address', 'error');
        return;
    }

    const sendButton = event.target;
    const originalText = sendButton.innerHTML;
    sendButton.disabled = true;
    sendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

    fetch(`/kids/${kidId}/send-email-invite`, {
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
                sendButton.disabled = false;
                sendButton.innerHTML = originalText;
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

// Generate QR code from manage page
function generateQRCodeManage() {
    const kidId = window.location.pathname.split('/').pop().split('/')[0];

    fetch(`/kids/${kidId}/qr-code`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('qrCodeDisplayManage').innerHTML = `
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
            document.getElementById('qrCodeDisplayManage').innerHTML = `
            <p style="color: #ef4444;">Error generating QR code. Please try Copy Link instead.</p>
        `;
        });
}

// Change Username Modal
function openChangeUsernameModal() {
    const modal = document.getElementById('changeUsernameModal');
    const feedbackDiv = document.getElementById('usernameValidationChange');
    const errorDiv = document.getElementById('usernameChangeError');

    modal.style.display = 'flex';
    document.getElementById('newUsername').value = '';
    feedbackDiv.style.display = 'none';
    feedbackDiv.innerHTML = '';
    errorDiv.style.display = 'none';
}

function closeChangeUsernameModal() {
    document.getElementById('changeUsernameModal').style.display = 'none';
}

function changeUsername() {
    const newUsername = document.getElementById('newUsername').value.trim();
    const feedbackDiv = document.getElementById('usernameValidationChange');
    const errorDiv = document.getElementById('usernameChangeError');
    const kidId = window.location.pathname.split('/')[2];
    const submitBtn = event.target;

    // Clear previous errors
    feedbackDiv.style.display = 'none';
    errorDiv.style.display = 'none';

    if (!newUsername || newUsername.length < 3) {
        errorDiv.textContent = 'Username must be at least 3 characters';
        errorDiv.style.cssText = 'display: block !important; color: #ef4444 !important; font-size: 13px !important; margin-top: 8px !important; line-height: 1.4 !important; position: static !important; word-wrap: break-word !important; overflow-wrap: break-word !important; white-space: normal !important; max-width: 100% !important;';
        return;
    }

    // Disable button and show checking state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Changing...';

    // Show checking availability feedback
    feedbackDiv.style.display = 'flex';
    feedbackDiv.style.color = '#6b7280';
    feedbackDiv.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size: 16px;"></i> <span>Checking availability...</span>';

    const startTime = Date.now();

    fetch(`/kids/${kidId}/change-username`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ username: newUsername })
    })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const elapsed = Date.now() - startTime;
                const remainingTime = Math.max(0, 500 - elapsed);

                setTimeout(() => {
                    feedbackDiv.style.color = '#10b981';
                    feedbackDiv.innerHTML = '<i class="fas fa-check-circle" style="font-size: 18px;"></i> <span style="font-weight: 600;">Success! Username updated</span>';
                    feedbackDiv.classList.add('feedback-animate');

                    setTimeout(() => {
                        closeChangeUsernameModal();
                        location.reload();
                    }, 2000); // Changed from 1300 to 2000
                }, remainingTime);
            }
        })
        .catch(error => {
            const elapsed = Date.now() - startTime;
            const remainingTime = Math.max(0, 500 - elapsed);

            setTimeout(() => {
                let errorMessage = 'Error changing username. Please try again.';

                if (error.errors && error.errors.username) {
                    errorMessage = error.errors.username[0];
                } else if (error.message) {
                    errorMessage = error.message;
                }

                feedbackDiv.style.display = 'flex';
                feedbackDiv.style.color = '#ef4444';
                feedbackDiv.innerHTML = '<i class="fas fa-times-circle" style="font-size: 18px;"></i> <span style="font-weight: 600;">' + errorMessage + '</span>';
                feedbackDiv.classList.add('feedback-animate');

                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Change Username';
            }, remainingTime);
        });
}

// Toggle password visibility
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Reset Password Modal
function openResetPasswordModal() {
    document.getElementById('resetPasswordModal').style.display = 'flex';
    document.getElementById('newPassword').value = '';
    document.getElementById('confirmPassword').value = '';
    document.getElementById('passwordChangeError').style.display = 'none';

    // Reset password fields to hidden
    document.getElementById('newPassword').type = 'password';
    document.getElementById('confirmPassword').type = 'password';
    document.getElementById('eyeIcon1').classList.remove('fa-eye-slash');
    document.getElementById('eyeIcon1').classList.add('fa-eye');
    document.getElementById('eyeIcon2').classList.remove('fa-eye-slash');
    document.getElementById('eyeIcon2').classList.add('fa-eye');
}

function closeResetPasswordModal() {
    document.getElementById('resetPasswordModal').style.display = 'none';
}

function resetPassword() {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const feedbackDiv = document.getElementById('passwordResetFeedback');
    const errorDiv = document.getElementById('passwordChangeError');
    const kidId = window.location.pathname.split('/')[2];
    const submitBtn = event.target;

    // Clear previous messages
    feedbackDiv.style.display = 'none';
    errorDiv.style.display = 'none';

    if (!newPassword || newPassword.length < 4) {
        errorDiv.textContent = 'Password must be at least 4 characters';
        errorDiv.style.cssText = 'display: block !important; color: #ef4444 !important; font-size: 13px !important; margin-top: 8px !important; line-height: 1.4 !important; position: static !important; word-wrap: break-word !important; overflow-wrap: break-word !important; white-space: normal !important; max-width: 100% !important;';
        return;
    }

    if (newPassword !== confirmPassword) {
        errorDiv.textContent = 'Passwords do not match';
        errorDiv.style.cssText = 'display: block !important; color: #ef4444 !important; font-size: 13px !important; margin-top: 8px !important; line-height: 1.4 !important; position: static !important; word-wrap: break-word !important; overflow-wrap: break-word !important; white-space: normal !important; max-width: 100% !important;';
        return;
    }

    // Disable button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resetting...';

    fetch(`/kids/${kidId}/reset-password`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ password: newPassword })
    })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Show success message
                feedbackDiv.style.display = 'flex';
                feedbackDiv.style.color = '#10b981';
                feedbackDiv.innerHTML = '<i class="fas fa-check-circle" style="font-size: 18px;"></i> <span style="font-weight: 600;">Success! Password has been reset</span>';
                feedbackDiv.classList.add('feedback-animate');

                // Wait 2 seconds then close
                setTimeout(() => {
                    closeResetPasswordModal();
                }, 2000); // Changed from 1300 to 2000
            }
        })
        .catch(error => {
            console.error('Error:', error);

            let errorMessage = 'Error resetting password. Please try again.';

            if (error.errors && error.errors.password) {
                errorMessage = error.errors.password[0];
            } else if (error.message) {
                errorMessage = error.message;
            }

            errorDiv.textContent = errorMessage;
            errorDiv.style.cssText = 'display: block !important; color: #ef4444 !important; font-size: 13px !important; margin-top: 8px !important; line-height: 1.4 !important; position: static !important; word-wrap: break-word !important; overflow-wrap: break-word !important; white-space: normal !important; max-width: 100% !important;';

            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Reset Password';
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

// Manage kid page invite send / resend functions
window.toggleResendInvite = toggleResendInvite;
window.showInviteMethod = showInviteMethod;
window.copyInviteLinkManage = copyInviteLinkManage;
window.sendEmailInviteManage = sendEmailInviteManage;
window.generateQRCodeManage = generateQRCodeManage;

// Manage kid username / password change functions
window.openChangeUsernameModal = openChangeUsernameModal;
window.closeChangeUsernameModal = closeChangeUsernameModal;
window.changeUsername = changeUsername;
window.togglePasswordVisibility = togglePasswordVisibility;
window.openResetPasswordModal = openResetPasswordModal;
window.closeResetPasswordModal = closeResetPasswordModal;
window.resetPassword = resetPassword;

// Make it globally available
window.toggleLabTools = toggleLabTools;