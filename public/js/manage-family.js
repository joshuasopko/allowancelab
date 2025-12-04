let confirmCallback = null;

function switchTab(tabName) {
    // Remove active class from all tabs and content
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

    // Add active class to clicked tab and corresponding content
    event.target.classList.add('active');
    document.getElementById(tabName + '-tab').classList.add('active');
}

function toggleCoParentForm() {
    const form = document.getElementById('coParentForm');
    const button = document.querySelector('#coParentFormContainer .invite-toggle-btn');

    if (form.style.display === 'none') {
        form.style.display = 'block';
        button.style.display = 'none';
    } else {
        form.style.display = 'none';
        button.style.display = 'block';
    }
}

function showConfirmModal(title, message, callback, isDanger = true) {
    const modal = document.getElementById('confirmModal');
    const icon = document.getElementById('confirmIcon');
    const titleEl = document.getElementById('confirmTitle');
    const messageEl = document.getElementById('confirmMessage');
    const confirmBtn = document.getElementById('confirmButton');

    titleEl.textContent = title;
    messageEl.textContent = message;
    confirmCallback = callback;

    // Style based on danger level
    if (isDanger) {
        icon.classList.add('danger');
        confirmBtn.classList.remove('secondary');
    } else {
        icon.classList.remove('danger');
        confirmBtn.classList.add('secondary');
    }

    modal.style.display = 'flex';
}

function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
    confirmCallback = null;
}

function executeConfirm() {
    if (confirmCallback) {
        confirmCallback();
    }
    closeConfirmModal();
}

function confirmRemoveMember(userId, userName) {
    showConfirmModal(
        'Revoke Access',
        `Are you sure you want to revoke access for ${userName}? This action cannot be undone.`,
        function () {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/family/member/${userId}`;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';

            form.appendChild(csrfInput);
            form.appendChild(methodInput);
            document.body.appendChild(form);
            form.submit();
        },
        true
    );
}

function resendInvite(inviteId) {
    showConfirmModal(
        'Resend Invitation',
        'Send another invitation email to this address?',
        function () {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/family/invite/${inviteId}/resend`;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;

            form.appendChild(csrfInput);
            document.body.appendChild(form);
            form.submit();
        },
        false
    );
}

function cancelInvite(inviteId) {
    showConfirmModal(
        'Cancel Invitation',
        'Cancel this invitation? The recipient will no longer be able to accept it.',
        function () {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/family/invite/${inviteId}`;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';

            form.appendChild(csrfInput);
            form.appendChild(methodInput);
            document.body.appendChild(form);
            form.submit();
        },
        true
    );
}

// Page load initialization
document.addEventListener('DOMContentLoaded', function () {
    // Bind confirm button click
    document.getElementById('confirmButton').onclick = executeConfirm;

    // Check for tab parameter in URL and activate correct tab
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');

    if (tabParam) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

        const targetTab = document.querySelector(`.tab-btn[onclick*="${tabParam}"]`);
        const targetContent = document.getElementById(tabParam + '-tab');

        if (targetTab && targetContent) {
            targetTab.classList.add('active');
            targetContent.classList.add('active');
        }
    }
});