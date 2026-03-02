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
        <div class="accounts-section" id="notificationSettingsSection">
            <h2 class="section-title">Notification Settings</h2>

            {{-- Push subscription status row --}}
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap;
                        gap:12px; padding:14px 16px; background:#f8fafc; border-radius:10px;
                        border:1px solid #e2e8f0; margin-bottom:20px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <i class="fas fa-bell" style="color:#6366f1; font-size:18px;"></i>
                    <div>
                        <div style="font-weight:600; font-size:14px; color:#1e293b;">
                            Browser Push Notifications
                        </div>
                        <div id="pushStatusText" style="font-size:12px; color:#64748b;">
                            @if($hasPushSubscription)
                                Active on this device
                            @else
                                Not enabled on this device
                            @endif
                        </div>
                    </div>
                </div>
                <div style="display:flex; gap:8px; align-items:center;">
                    <button id="pushToggleBtn"
                            onclick="togglePushSubscription()"
                            style="padding:8px 16px; border-radius:8px; font-size:13px; font-weight:600;
                                   border:none; cursor:pointer; transition:background 0.2s;
                                   {{ $hasPushSubscription ? 'background:#fee2e2; color:#dc2626;' : 'background:#e0e7ff; color:#4f46e5;' }}">
                        @if($hasPushSubscription)
                            <i class="fas fa-bell-slash"></i> Disable
                        @else
                            <i class="fas fa-bell"></i> Enable
                        @endif
                    </button>
                </div>
            </div>

            <p style="font-size:13px; color:#64748b; margin-bottom:12px;">
                Choose how you want to be notified for each event. <strong>Push</strong> sends an instant
                browser notification. <strong>Email</strong> sends a message to {{ $user->email }}.
            </p>

            {{-- Bulk toggle pills --}}
            <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
                <button type="button" id="toggleAllPushBtn" onclick="toggleAllChannel('push')"
                        style="display:inline-flex; align-items:center; gap:6px;
                               padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600;
                               border:1.5px solid #6366f1; background:#f5f3ff; color:#4f46e5;
                               cursor:pointer; transition:background 0.15s, color 0.15s;">
                    <i class="fas fa-bell"></i> Enable All Push
                </button>
                <button type="button" id="toggleAllEmailBtn" onclick="toggleAllChannel('email')"
                        style="display:inline-flex; align-items:center; gap:6px;
                               padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600;
                               border:1.5px solid #0ea5e9; background:#f0f9ff; color:#0284c7;
                               cursor:pointer; transition:background 0.15s, color 0.15s;">
                    <i class="fas fa-envelope"></i> Enable All Email
                </button>
            </div>

            @php
                $eventLabels = [
                    'goal_created'              => ['label' => 'Kid creates a goal',           'icon' => 'fa-bullseye'],
                    'goal_redemption_requested' => ['label' => 'Kid requests goal redemption', 'icon' => 'fa-gift'],
                    'goal_completed'            => ['label' => 'Kid reaches their goal',        'icon' => 'fa-trophy'],
                    'kid_deposited'             => ['label' => 'Kid adds money',                'icon' => 'fa-arrow-up',  'threshold' => true],
                    'kid_spent'                 => ['label' => 'Kid spends money',              'icon' => 'fa-arrow-down','threshold' => true],
                    'allowance_processed'       => ['label' => 'Allowance posts (or is denied)','icon' => 'fa-calendar-check'],
                    'points_low_warning'        => ['label' => "Kid's points are critically low",'icon' => 'fa-exclamation-triangle'],
                    'wish_created'              => ['label' => 'Kid adds a wish',                'icon' => 'fa-star'],
                    'wish_purchase_requested'   => ['label' => 'Kid requests to buy a wish',     'icon' => 'fa-shopping-cart'],
                ];
            @endphp

            <form id="notificationPrefsForm">
                @csrf
                {{-- Header row --}}
                <div style="display:grid; grid-template-columns:1fr 64px 64px; gap:8px;
                            padding:8px 12px; font-size:11px; font-weight:700; text-transform:uppercase;
                            color:#94a3b8; letter-spacing:0.05em; border-bottom:1px solid #e2e8f0;
                            margin-bottom:4px;">
                    <div>Event</div>
                    <div style="text-align:center;">Push</div>
                    <div style="text-align:center;">Email</div>
                </div>

                @foreach($eventLabels as $event => $meta)
                    @php
                        $pref   = $notificationPreferences[$event] ?? [];
                        $push   = $pref['push']  ?? false;
                        $email  = $pref['email'] ?? false;
                        $thresh = $pref['threshold'] ?? null;
                    @endphp
                    <div style="display:grid; grid-template-columns:1fr 64px 64px; gap:8px;
                                align-items:center; padding:12px 12px;
                                border-bottom:1px solid #f1f5f9;">
                        <div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <i class="fas {{ $meta['icon'] }}" style="color:#6366f1; width:16px; text-align:center;"></i>
                                <span style="font-size:14px; color:#1e293b; font-weight:500;">{{ $meta['label'] }}</span>
                            </div>
                            @if(!empty($meta['threshold']))
                                <div style="display:flex; align-items:center; gap:6px; margin-top:6px; padding-left:24px;">
                                    <span style="font-size:12px; color:#64748b;">Notify when over $</span>
                                    <input type="number"
                                           name="threshold_{{ $event }}"
                                           value="{{ $thresh ?? 20 }}"
                                           min="0" step="1"
                                           style="width:64px; padding:4px 8px; border:1px solid #d1d5db; border-radius:6px;
                                                  font-size:13px; color:#1e293b;">
                                </div>
                            @endif
                        </div>
                        <div style="text-align:center;">
                            <label class="notif-toggle" title="Push">
                                <input type="checkbox"
                                       name="push_{{ $event }}"
                                       {{ $push ? 'checked' : '' }}>
                                <span class="notif-toggle-slider"></span>
                            </label>
                        </div>
                        <div style="text-align:center;">
                            <label class="notif-toggle" title="Email">
                                <input type="checkbox"
                                       name="email_{{ $event }}"
                                       {{ $email ? 'checked' : '' }}>
                                <span class="notif-toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                @endforeach

                <div style="margin-top:16px; display:flex; align-items:center; gap:12px;">
                    <button type="button" onclick="saveNotificationPrefs()"
                            id="saveNotifsBtn"
                            style="background:#6366f1; color:#fff; border:none; border-radius:8px;
                                   padding:10px 20px; font-size:14px; font-weight:600; cursor:pointer;">
                        <i class="fas fa-save"></i> Save Preferences
                    </button>
                    <span id="notifSaveStatus" style="font-size:13px; display:none;"></span>
                </div>
            </form>
        </div>

        {{-- Toggle switch CSS (inline, scoped) --}}
        <style>
            .notif-toggle {
                position: relative;
                display: inline-block;
                width: 40px;
                height: 22px;
                cursor: pointer;
            }
            .notif-toggle input {
                opacity: 0;
                width: 0;
                height: 0;
                position: absolute;
            }
            .notif-toggle-slider {
                position: absolute;
                inset: 0;
                background: #cbd5e1;
                border-radius: 22px;
                transition: background 0.2s;
            }
            .notif-toggle-slider::before {
                content: '';
                position: absolute;
                width: 16px;
                height: 16px;
                left: 3px;
                top: 3px;
                background: #fff;
                border-radius: 50%;
                transition: transform 0.2s;
                box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            }
            .notif-toggle input:checked + .notif-toggle-slider {
                background: #6366f1;
            }
            .notif-toggle input:checked + .notif-toggle-slider::before {
                transform: translateX(18px);
            }
        </style>

        <script>
        // ── Push subscription toggle ────────────────────────────────────────────
        async function togglePushSubscription() {
            if (!window.PushManager) {
                alert('Push notifications are not supported on this browser.');
                return;
            }

            const isSubscribed = await window.PushManager.isSubscribed();

            if (isSubscribed) {
                await window.PushManager.unsubscribe();
                document.getElementById('pushStatusText').textContent = 'Not enabled on this device';
                const btn = document.getElementById('pushToggleBtn');
                btn.style.background = '#e0e7ff';
                btn.style.color = '#4f46e5';
                btn.innerHTML = '<i class="fas fa-bell"></i> Enable';
            } else {
                await window.PushManager.init({
                    subscribeUrl:   '{{ route("notifications.subscribe") }}',
                    unsubscribeUrl: '{{ route("notifications.unsubscribe") }}',
                });
                const success = await window.PushManager.subscribe();
                if (success) {
                    document.getElementById('pushStatusText').textContent = 'Active on this device';
                    const btn = document.getElementById('pushToggleBtn');
                    btn.style.background = '#fee2e2';
                    btn.style.color = '#dc2626';
                    btn.innerHTML = '<i class="fas fa-bell-slash"></i> Disable';
                }
            }
        }

        // ── Bulk channel toggle ─────────────────────────────────────────────────
        function syncBulkPillState(channel) {
            const checkboxes = document.querySelectorAll('[name^="' + channel + '_"]');
            const btn = document.getElementById('toggleAll' + channel.charAt(0).toUpperCase() + channel.slice(1) + 'Btn');
            if (!btn || !checkboxes.length) return;
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            const icon = channel === 'push'
                ? (allChecked ? 'fas fa-bell-slash' : 'fas fa-bell')
                : (allChecked ? 'fas fa-envelope-open' : 'fas fa-envelope');
            const label = (allChecked ? 'Disable All ' : 'Enable All ') + (channel === 'push' ? 'Push' : 'Email');
            btn.innerHTML = '<i class="' + icon + '"></i> ' + label;
        }
        document.addEventListener('DOMContentLoaded', function () {
            syncBulkPillState('push');
            syncBulkPillState('email');
        });

        function toggleAllChannel(channel) {
            const checkboxes = document.querySelectorAll('[name^="' + channel + '_"]');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => { cb.checked = !allChecked; });
            syncBulkPillState(channel);
        }

        // ── Notification preference save ────────────────────────────────────────
        async function saveNotificationPrefs() {
            const btn    = document.getElementById('saveNotifsBtn');
            const status = document.getElementById('notifSaveStatus');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';

            @php
                $eventKeys = array_keys($eventLabels);
            @endphp
            const events = @json($eventKeys);

            const preferences = {};
            events.forEach(function (event) {
                const pushInput  = document.querySelector('[name="push_'  + event + '"]');
                const emailInput = document.querySelector('[name="email_' + event + '"]');
                const threshInput= document.querySelector('[name="threshold_' + event + '"]');

                preferences[event] = {
                    push:  pushInput  ? pushInput.checked  : false,
                    email: emailInput ? emailInput.checked : false,
                };
                if (threshInput) {
                    preferences[event].threshold = parseFloat(threshInput.value) || 0;
                }
            });

            try {
                const resp = await fetch('{{ route("notifications.preferences.update") }}', {
                    method:  'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ preferences }),
                });

                if (resp.ok) {
                    status.style.display = 'inline';
                    status.style.color   = '#16a34a';
                    status.textContent   = '✓ Saved!';
                    setTimeout(() => { status.style.display = 'none'; }, 3000);
                } else {
                    throw new Error('Server error');
                }
            } catch (e) {
                status.style.display = 'inline';
                status.style.color   = '#dc2626';
                status.textContent   = 'Failed to save. Please try again.';
            }

            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Save Preferences';
        }
        </script>

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
