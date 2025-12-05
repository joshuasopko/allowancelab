@extends('layouts.parent')

@section('title', 'My Account - AllowanceLab')

@section('header-right')
    <a href="{{ route('dashboard') }}" class="back-to-dashboard-btn">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
@endsection

@section('content')
    <!-- Mobile Back Button (Top) -->
    <a href="{{ route('dashboard') }}" class="mobile-back-link mobile-back-top">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>

    <div class="manage-family-container">
        <div class="manage-family-header">
            <h1>My Account</h1>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                @foreach($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

        <!-- Account Stats Section -->
        <div class="accounts-section">
            <h2 class="section-title">Account Stats</h2>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-top">
                        <div class="stat-icon">
                            <i class="fas fa-child"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">{{ $totalKids }}</div>
                            <div class="stat-label">Kids in Family</div>
                        </div>
                    </div>
                    <div class="stat-action">
                        <a href="{{ route('manage-family') }}" class="stat-action-btn">
                            <i class="fas fa-cog"></i> Manage Family
                        </a>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-top">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">{{ $totalParents }}</div>
                            <div class="stat-label">Parent Accounts</div>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-top">
                        <div class="stat-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">${{ number_format($combinedBalance, 2) }}</div>
                            <div class="stat-label">Combined Balance</div>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-top">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">${{ number_format($totalUpcomingAllowance, 2) }}</div>
                            <div class="stat-label">Next Allowance on {{ $nextAllowanceDate ? $nextAllowanceDate->format('M j') : 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if($lowPointsKids->count() > 0)
                <div class="low-points-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Low Points Alert:</strong>
                    @foreach($lowPointsKids as $kid)
                        {{ $kid->name }} ({{ $kid->points }}/{{ $kid->max_points }}){{ !$loop->last ? ',' : '' }}
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Profile Settings Section -->
        <div class="accounts-section">
            <h2 class="section-title">Profile Settings</h2>

            <div class="profile-settings-grid">
                <!-- Left Column: Name Fields -->
                <div class="profile-column">
                    <form action="{{ route('parent.account.update-profile') }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="profile-field">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                        </div>

                        <div class="profile-field">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                        </div>

                        <button type="submit" class="profile-action-btn">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>

                <!-- Right Column: Email & Password -->
                <div class="profile-column profile-column-right">
                    <div class="profile-info-row">
                        <label>Email Address</label>
                        <div class="profile-info-inline">
                            <div class="info-value">{{ $user->email }}</div>
                            <button class="profile-action-btn" onclick="openChangeEmailModal()">
                                <i class="fas fa-envelope"></i> Change Email
                            </button>
                        </div>
                    </div>

                    <div class="profile-info-row">
                        <label>Password</label>
                        <div class="profile-info-inline">
                            <div class="info-value">••••••••</div>
                            <button class="profile-action-btn" onclick="openChangePasswordModal()">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Settings Section -->
        <div class="accounts-section invite-section-disabled">
            <h2 class="section-title">Notification Settings</h2>
            <p class="coming-soon-text">
                <i class="fas fa-clock"></i> This feature is coming soon. Check back later!
            </p>
        </div>

        <!-- Account Management Section -->
        <div class="accounts-section">
            <h2 class="section-title">Account Management</h2>

            <div class="account-management-container">
                <form action="{{ route('parent.account.update-timezone') }}" method="POST" class="account-form">
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <label for="timezone">Timezone</label>
                        <select id="timezone" name="timezone" required>
                            @foreach($timezones as $value => $label)
                                <option value="{{ $value }}" {{ $user->timezone === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="current-time-display">
                        Current time in your timezone: <strong id="currentTime">Loading...</strong>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> Update Timezone
                        </button>
                    </div>
                </form>

                <div class="danger-zone">
                    <h3 class="danger-zone-title">
                        <i class="fas fa-exclamation-triangle"></i> Danger Zone
                    </h3>
                    <p class="danger-zone-description">
                        Once you delete your account, all family data will be permanently removed. This action cannot be undone.
                    </p>
                    <button class="btn-danger" onclick="openDeleteAccountModal()">
                        <i class="fas fa-trash"></i> Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Email Modal -->
    <div id="changeEmailModal" class="confirm-modal-overlay" style="display: none;">
        <div class="confirm-modal">
            <h3 class="confirm-modal-title">Change Email Address</h3>
            <p class="confirm-modal-message">We'll send a verification link to your new email address.</p>

            <form action="{{ route('parent.account.request-email-change') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="new_email">New Email Address</label>
                    <input type="email" id="new_email" name="new_email" required>
                </div>
                <div class="form-group">
                    <label for="password_email">Confirm Your Password</label>
                    <input type="password" id="password_email" name="password" required>
                </div>

                <div class="confirm-modal-actions">
                    <button type="button" class="confirm-btn-cancel" onclick="closeChangeEmailModal()">Cancel</button>
                    <button type="submit" class="confirm-btn-confirm">Send Verification Email</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="changePasswordModal" class="confirm-modal-overlay" style="display: none;">
        <div class="confirm-modal">
            <h3 class="confirm-modal-title">Change Password</h3>
            <p class="confirm-modal-message">You will be logged out after changing your password.</p>

            <form action="{{ route('parent.account.change-password') }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" minlength="8" required>
                </div>
                <div class="form-group">
                    <label for="new_password_confirmation">Confirm New Password</label>
                    <input type="password" id="new_password_confirmation" name="new_password_confirmation" minlength="8" required>
                </div>

                <div class="confirm-modal-actions">
                    <button type="button" class="confirm-btn-cancel" onclick="closeChangePasswordModal()">Cancel</button>
                    <button type="submit" class="confirm-btn-confirm">Change Password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div id="deleteAccountModal" class="confirm-modal-overlay" style="display: none;">
        <div class="confirm-modal">
            <div class="confirm-modal-icon" style="color: #ef4444;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="confirm-modal-title">Delete Account</h3>
            <p class="confirm-modal-message" style="color: #ef4444; font-weight: 600;">
                This action cannot be undone. All family data, kid accounts, and transaction history will be permanently deleted and unrecoverable.
            </p>

            <form action="{{ route('parent.account.delete') }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="form-group">
                    <label for="confirmation_text">Type "CONFIRM DELETE" to proceed:</label>
                    <input type="text" id="confirmation_text" name="confirmation_text" oninput="validateDeleteConfirmation()" required>
                </div>

                <div class="confirm-modal-actions">
                    <button type="button" class="confirm-btn-cancel" onclick="closeDeleteAccountModal()">Cancel</button>
                    <button type="submit" id="deleteAccountBtn" class="confirm-btn-confirm" style="background: #ef4444;" disabled>Delete Account</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Current time display
        function updateCurrentTime() {
            const timezone = document.getElementById('timezone').value;
            const timeDisplay = document.getElementById('currentTime');

            const now = new Date();
            const options = {
                timeZone: timezone,
                hour: 'numeric',
                minute: '2-digit',
                second: '2-digit',
                hour12: true,
                weekday: 'short',
                month: 'short',
                day: 'numeric'
            };

            timeDisplay.textContent = now.toLocaleString('en-US', options);
        }

        // Update time on load and when timezone changes
        if (document.getElementById('timezone')) {
            updateCurrentTime();
            setInterval(updateCurrentTime, 1000);
            document.getElementById('timezone').addEventListener('change', updateCurrentTime);
        }

        // Modal functions
        function openChangeEmailModal() {
            document.getElementById('changeEmailModal').style.display = 'flex';
        }

        function closeChangeEmailModal() {
            document.getElementById('changeEmailModal').style.display = 'none';
        }

        function openChangePasswordModal() {
            document.getElementById('changePasswordModal').style.display = 'flex';
        }

        function closeChangePasswordModal() {
            document.getElementById('changePasswordModal').style.display = 'none';
        }

        function openDeleteAccountModal() {
            document.getElementById('deleteAccountModal').style.display = 'flex';
        }

        function closeDeleteAccountModal() {
            document.getElementById('deleteAccountModal').style.display = 'none';
            document.getElementById('confirmation_text').value = '';
            document.getElementById('deleteAccountBtn').disabled = true;
        }

        function validateDeleteConfirmation() {
            const input = document.getElementById('confirmation_text').value;
            const btn = document.getElementById('deleteAccountBtn');
            btn.disabled = input !== 'CONFIRM DELETE';
        }

        // Close modals on backdrop click
        document.querySelectorAll('.confirm-modal-overlay').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                }
            });
        });
    </script>
@endsection
