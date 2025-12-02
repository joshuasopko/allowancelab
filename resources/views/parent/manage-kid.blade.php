@extends('layouts.parent')

@section('title', 'Manage ' . $kid->name . ' - AllowanceLab')

@section('header-right')
    <a href="{{ route('dashboard') }}" class="back-btn">← Back to Dashboard</a>
@endsection

@section('sidebar-active')
    <div class="menu-item active">Manage {{ $kid->name }}</div>
@endsection

@section('content')
    <!-- Page Header -->
    <div class="manage-header">
        <div class="manage-kid-info">
            <div class="avatar" style="background: {{ $kid->color }};">{{ strtoupper(substr($kid->name, 0, 1)) }}</div>
            <div>
                <h1 class="manage-title">Manage {{ $kid->name }}</h1>
                <div class="manage-subtitle">Edit profile, settings, and more</div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="manage-tabs">
        <button class="manage-tab active" onclick="switchTab('profile')">Profile</button>
        <button class="manage-tab" onclick="switchTab('chores')" disabled>Chores</button>
        <button class="manage-tab" onclick="switchTab('goals')" disabled>Goals</button>
        <button class="manage-tab" onclick="switchTab('loans')" disabled>Loans</button>
    </div>

    <!-- Profile Tab Content -->
    <div class="tab-content" id="profileTab">

        <!-- Account Status Section -->
        <div class="account-status-section">
            @if($kid->username)
                <!-- Account Active -->
                <div class="status-display active">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Account Active</strong>
                        @if($kid->last_login_at)
                            <span class="last-used">Last used: {{ $kid->last_login_at->diffForHumans() }}</span>
                        @else
                            <span class="last-used">Never logged in</span>
                        @endif
                    </div>
                </div>
            @elseif($kid->invite && $kid->invite->status === 'pending' && !$kid->invite->isExpired())
                <!-- Invite Pending -->
                <div class="status-display pending">
                    <i class="fas fa-clock"></i>
                    <div>
                        <strong>Invite Pending</strong>
                        <span class="last-used">Expires: {{ $kid->invite->expires_at->format('F j, Y') }}</span>
                    </div>
                </div>

                <button type="button" class="resend-invite-btn" onclick="toggleResendInvite()">
                    <i class="fas fa-paper-plane"></i> Resend Invite
                </button>

                <!-- Invite Options (Hidden by default) -->
                <div class="invite-options" id="resendInviteOptions" style="display: none;">
                    <div class="invite-buttons-row-manage">
                        <button type="button" class="invite-btn-manage invite-btn-blue" onclick="showInviteMethod('copyLink')">
                            <i class="fas fa-link"></i>
                            <span>Copy Link</span>
                        </button>
                        <button type="button" class="invite-btn-manage invite-btn-green" onclick="showInviteMethod('email')">
                            <i class="fas fa-envelope"></i>
                            <span>Email Invite</span>
                        </button>
                        <button type="button" class="invite-btn-manage invite-btn-purple" onclick="showInviteMethod('qr')">
                            <i class="fas fa-qrcode"></i>
                            <span>QR Code</span>
                        </button>
                    </div>

                    <!-- Copy Link Content -->
                    <div class="invite-method-content-manage" id="copyLinkContentManage" style="display: none;">
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="text" class="form-input" id="inviteLinkInputManage"
                                value="{{ url('/invite/' . $kid->invite->token) }}" readonly style="flex: 1;">
                            <button type="button" class="modal-btn modal-btn-submit" onclick="copyInviteLinkManage()">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>

                    <!-- Email Content -->
                    <div class="invite-method-content-manage" id="emailContentManage" style="display: none;">
                        <label class="form-label">Kid's Email</label>
                        <input type="email" class="form-input" id="kidEmailInputManage" value="{{ $kid->email }}"
                            placeholder="kid@example.com">
                        <button type="button" class="modal-btn modal-btn-submit" style="margin-top: 10px;"
                            onclick="sendEmailInviteManage()">
                            <i class="fas fa-paper-plane"></i> Send Email
                        </button>
                    </div>

                    <!-- QR Code Content -->
                    <div class="invite-method-content-manage" id="qrContentManage" style="display: none;">
                        <div id="qrCodeDisplayManage" style="text-align: center;">
                            <button type="button" class="modal-btn modal-btn-submit" onclick="generateQRCodeManage()">
                                Generate QR Code
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <!-- No Account Created -->
                <div class="status-display no-account">
                    <i class="fas fa-user-plus"></i>
                    <div>
                        <strong>No Account Created</strong>
                        <span class="last-used">Want to invite them now?</span>
                    </div>
                </div>

                <!-- Invite Options -->
                <div class="invite-options">
                    <div class="invite-buttons-row-manage">
                        <button type="button" class="invite-btn-manage invite-btn-blue" onclick="showInviteMethod('copyLink')">
                            <i class="fas fa-link"></i>
                            <span>Copy Link</span>
                        </button>
                        <button type="button" class="invite-btn-manage invite-btn-green" onclick="showInviteMethod('email')">
                            <i class="fas fa-envelope"></i>
                            <span>Email Invite</span>
                        </button>
                        <button type="button" class="invite-btn-manage invite-btn-purple" onclick="showInviteMethod('qr')">
                            <i class="fas fa-qrcode"></i>
                            <span>QR Code</span>
                        </button>
                    </div>

                    <!-- Copy Link Content -->
                    <div class="invite-method-content-manage" id="copyLinkContentManage" style="display: none;">
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="text" class="form-input" id="inviteLinkInputManage" readonly style="flex: 1;">
                            <button type="button" class="modal-btn modal-btn-submit" onclick="copyInviteLinkManage()">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>

                    <!-- Email Content -->
                    <div class="invite-method-content-manage" id="emailContentManage" style="display: none;">
                        <label class="form-label">Kid's Email</label>
                        <input type="email" class="form-input" id="kidEmailInputManage" value="{{ $kid->email }}"
                            placeholder="kid@example.com">
                        <button type="button" class="modal-btn modal-btn-submit" style="margin-top: 10px;"
                            onclick="sendEmailInviteManage()">
                            <i class="fas fa-paper-plane"></i> Send Email
                        </button>
                    </div>

                    <!-- QR Code Content -->
                    <div class="invite-method-content-manage" id="qrContentManage" style="display: none;">
                        <div id="qrCodeDisplayManage" style="text-align: center;">
                            <button type="button" class="modal-btn modal-btn-submit" onclick="generateQRCodeManage()">
                                Generate QR Code
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <form action="{{ route('kids.update', $kid) }}" method="POST" class="manage-form">

            <form action="{{ route('kids.update', $kid) }}" method="POST" class="manage-form">
                @csrf
                @method('PATCH')

                <!-- Basic Info Section -->
                <div class="form-section">
                    <h3 class="section-title">Basic Information</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-input" name="name" value="{{ $kid->name }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Birthday</label>
                            <input type="date" class="form-input" name="birthday"
                                value="{{ $kid->birthday->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    @if($kid->username)
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Username</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="text" class="form-input" value="{{ $kid->username }}" readonly
                                        style="flex: 1;">
                                    <button type="button" class="btn-change-credential" onclick="openChangeUsernameModal()">
                                        <i class="fas fa-edit"></i> Change
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Password</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="password" class="form-input" id="kidPasswordField"
                                        value="{{ $kid->password_plaintext ?? '********' }}" readonly style="flex: 1;">
                                    <button type="button" class="btn-show-password"
                                        onclick="togglePasswordVisibility('kidPasswordField', 'togglePasswordIcon')"
                                        id="togglePasswordBtn">
                                        <i class="fas fa-eye" id="togglePasswordIcon"></i>
                                    </button>
                                    <button type="button" class="btn-change-credential" onclick="openResetPasswordModal()">
                                        <i class="fas fa-key"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Appearance Section -->
                <div class="form-section">
                    <h3 class="section-title">Appearance</h3>

                    <div class="form-group">
                        <label class="form-label">Avatar Color</label>
                        <div class="avatar-preview-row">
                            <div class="avatar-preview-circle" id="avatarPreviewManage"
                                style="background: {{ $kid->color }};">
                                {{ strtoupper(substr($kid->name, 0, 1)) }}
                            </div>
                            <span class="color-name" id="colorNameManage"></span>
                        </div>
                        <div class="color-grid">
                            <div class="color-option {{ $kid->color == '#80d4b0' ? 'selected' : '' }}"
                                style="background: #80d4b0;" data-color="#80d4b0" onclick="selectColorManage(this)">
                            </div>
                            <div class="color-option {{ $kid->color == '#ff9999' ? 'selected' : '' }}"
                                style="background: #ff9999;" data-color="#ff9999" onclick="selectColorManage(this)">
                            </div>
                            <div class="color-option {{ $kid->color == '#b19cd9' ? 'selected' : '' }}"
                                style="background: #b19cd9;" data-color="#b19cd9" onclick="selectColorManage(this)">
                            </div>
                            <div class="color-option {{ $kid->color == '#87ceeb' ? 'selected' : '' }}"
                                style="background: #87ceeb;" data-color="#87ceeb" onclick="selectColorManage(this)">
                            </div>
                            <div class="color-option {{ $kid->color == '#ffb380' ? 'selected' : '' }}"
                                style="background: #ffb380;" data-color="#ffb380" onclick="selectColorManage(this)">
                            </div>
                            <div class="color-option {{ $kid->color == '#e066a6' ? 'selected' : '' }}"
                                style="background: #e066a6;" data-color="#e066a6" onclick="selectColorManage(this)">
                            </div>
                            <div class="color-option {{ $kid->color == '#ffd966' ? 'selected' : '' }}"
                                style="background: #ffd966;" data-color="#ffd966" onclick="selectColorManage(this)">
                            </div>
                            <div class="color-option {{ $kid->color == '#a8c686' ? 'selected' : '' }}"
                                style="background: #a8c686;" data-color="#a8c686" onclick="selectColorManage(this)">
                            </div>
                            <div class="color-option {{ $kid->color == '#5ab9b3' ? 'selected' : '' }}"
                                style="background: #5ab9b3;" data-color="#5ab9b3" onclick="selectColorManage(this)">
                            </div>
                            <div class="color-option {{ $kid->color == '#9bb7d4' ? 'selected' : '' }}"
                                style="background: #9bb7d4;" data-color="#9bb7d4" onclick="selectColorManage(this)">
                            </div>
                            <div class="color-option {{ $kid->color == '#ff9966' ? 'selected' : '' }}"
                                style="background: #ff9966;" data-color="#ff9966" onclick="selectColorManage(this)">
                            </div>
                            <div class="color-option {{ $kid->color == '#d4a5d4' ? 'selected' : '' }}"
                                style="background: #d4a5d4;" data-color="#d4a5d4" onclick="selectColorManage(this)">
                            </div>
                        </div>
                        <input type="hidden" name="color" id="colorInputManage" value="{{ $kid->color }}">
                    </div>
                </div>

                <!-- Allowance Section -->
                <div class="form-section">
                    <h3 class="section-title">Allowance Settings</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Weekly Allowance</label>
                            <input type="text" inputmode="decimal" class="form-input" name="allowance_amount"
                                value="{{ number_format($kid->allowance_amount, 2) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Allowance Day</label>
                            <select class="form-input" name="allowance_day" id="allowanceDaySelect"
                                data-original="{{ $kid->allowance_day }}" required>
                                <option value="monday" {{ $kid->allowance_day == 'monday' ? 'selected' : '' }}>Monday
                                </option>
                                <option value="tuesday" {{ $kid->allowance_day == 'tuesday' ? 'selected' : '' }}>Tuesday
                                </option>
                                <option value="wednesday" {{ $kid->allowance_day == 'wednesday' ? 'selected' : '' }}>
                                    Wednesday
                                </option>
                                <option value="thursday" {{ $kid->allowance_day == 'thursday' ? 'selected' : '' }}>
                                    Thursday
                                </option>
                                <option value="friday" {{ $kid->allowance_day == 'friday' ? 'selected' : '' }}>Friday
                                </option>
                                <option value="saturday" {{ $kid->allowance_day == 'saturday' ? 'selected' : '' }}>
                                    Saturday
                                </option>
                                <option value="sunday" {{ $kid->allowance_day == 'sunday' ? 'selected' : '' }}>Sunday
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Points Section -->
                <div class="form-section">
                    <h3 class="section-title">Points System</h3>

                    <div class="checkbox-group">
                        <input type="checkbox" name="points_enabled" id="usePointsManage" class="checkbox-input" value="1"
                            {{ $kid->points_enabled ? 'checked' : '' }} onchange="toggleMaxPointsManage()">
                        <label class="checkbox-label" for="usePointsManage">
                            Use point system?
                        </label>
                    </div>

                    <div class="form-group max-points-group" id="maxPointsGroupManage"
                        style="{{ $kid->points_enabled ? '' : 'display: none;' }}">
                        <label class="form-label">Max Points (per week)</label>
                        <input type="number" class="form-input" name="max_points" value="{{ $kid->max_points }}" min="1"
                            max="100">
                        <small style="color: #666;">Recommended: 10 points</small>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="form-actions">
                    <button type="submit" class="save-btn">Save Changes</button>
                </div>
            </form>

            <!-- Remove Account -->
            <div class="form-section danger-zone">
                <h3 class="section-title" style="color: #f44336;">Remove Account</h3>
                <p style="color: #666; margin-bottom: 16px;">Permanently remove this child's account and all their
                    transaction
                    history, point adjustments, and data.</p>
                <button type="button" class="delete-btn" onclick="confirmDeleteKid()">Remove Child Account</button>
            </div>
            <!-- Allowance Day Change Confirmation Modal -->
            <div class="allowance-confirm-modal" id="allowanceDayModal">
                <div class="modal-backdrop-allow" onclick="cancelAllowanceDayChange()"></div>
                <div class="modal-content-allow">
                    <h3>Change Allowance Day?</h3>
                    <p id="allowanceDayMessage"></p>
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="cancelAllowanceDayChange()">Cancel</button>
                        <button type="button" class="btn-confirm" onclick="confirmAllowanceDayChange()">Continue</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('modals')
    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal-container delete-confirm-modal">
            <div class="modal-header">
                <h2 class="modal-title" style="color: #f44336;">Remove {{ $kid->name }}'s Account?</h2>
                <button class="close-btn" onclick="closeDeleteModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p style="color: #666;">This will permanently remove {{ $kid->name }}'s account and all their
                    transaction
                    history, point adjustments, and data.</p>
                <p style="color: #f44336; font-weight: 600; margin-top: 16px;">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <form action="{{ route('kids.destroy', $kid) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="modal-btn remove-confirm-btn">REMOVE ACCOUNT</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Username Modal -->
    <div class="credential-modal" id="changeUsernameModal" style="display: none;">
        <div class="credential-modal-content">
            <div class="credential-modal-header">
                <h3>Change Username</h3>
                <button class="close-btn" onclick="closeChangeUsernameModal()">&times;</button>
            </div>
            <div class="credential-modal-body">
                <p style="color: #6b7280; margin-bottom: 20px;">Enter a new username for {{ $kid->name }}</p>
                <label class="form-label">New Username</label>
                <input type="text" class="form-input" id="newUsername" placeholder="Enter new username">
                <div id="usernameValidationChange"
                    style="margin-top: 8px; font-size: 14px; display: none; align-items: center; gap: 8px;"></div>
                <small class="error-message" id="usernameChangeError" style="display: none;"></small>
            </div>
            <div class="credential-modal-footer">
                <button type="button" class="modal-btn modal-btn-cancel"
                    onclick="closeChangeUsernameModal()">Cancel</button>
                <button type="button" class="modal-btn modal-btn-submit" onclick="changeUsername()">Change
                    Username</button>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="credential-modal" id="resetPasswordModal" style="display: none;">
        <div class="credential-modal-content">
            <div class="credential-modal-header">
                <h3>Reset Password</h3>
                <button class="close-btn" onclick="closeResetPasswordModal()">&times;</button>
            </div>
            <div class="credential-modal-body">
                <p style="color: #6b7280; margin-bottom: 20px;">Enter a new password for {{ $kid->name }}</p>
                <label class="form-label">New Password</label>
                <div style="position: relative;">
                    <input type="password" class="form-input" id="newPassword" placeholder="Enter new password"
                        minlength="4" style="padding-right: 45px;">
                    <button type="button" onclick="togglePasswordVisibility('newPassword', 'eyeIcon1')"
                        style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #6b7280;">
                        <i class="fas fa-eye" id="eyeIcon1"></i>
                    </button>
                </div>
                <label class="form-label" style="margin-top: 15px;">Confirm Password</label>
                <div style="position: relative;">
                    <input type="password" class="form-input" id="confirmPassword" placeholder="Confirm new password"
                        minlength="4" style="padding-right: 45px;">
                    <button type="button" onclick="togglePasswordVisibility('confirmPassword', 'eyeIcon2')"
                        style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #6b7280;">
                        <i class="fas fa-eye" id="eyeIcon2"></i>
                    </button>
                </div>
                <div id="passwordResetFeedback"
                    style="margin-top: 12px; font-size: 14px; display: none; align-items: center; gap: 8px;"></div>
                <small class="error-message" id="passwordChangeError" style="display: none;"></small>
            </div>
            <div class="credential-modal-footer">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeResetPasswordModal()">Cancel</button>
                <button type="button" class="modal-btn modal-btn-submit" onclick="resetPassword()">Reset
                    Password</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Allowance Day Change Confirmation
            let originalAllowanceDay = '{{ $kid->allowance_day }}';
            let pendingAllowanceDay = null;

            const selectElement = document.getElementById('allowanceDaySelect');
            if (selectElement) {
                selectElement.addEventListener('change', function (e) {
                    const newDay = this.value;

                    if (newDay === originalAllowanceDay) {
                        return;
                    }

                    pendingAllowanceDay = newDay;
                    const nextDate = getNextAllowanceDate(newDay);

                    const message = `Allowance will now post on <strong>${nextDate}</strong>. This change takes effect immediately and may result in double allowance if changed after this week's post.`;
                    document.getElementById('allowanceDayMessage').innerHTML = message;
                    document.getElementById('allowanceDayModal').classList.add('active');

                    this.value = originalAllowanceDay;
                });
            }

            function getNextAllowanceDate(dayName) {
                const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                const targetDay = days.indexOf(dayName.toLowerCase());
                const today = new Date();
                const currentDay = today.getDay();

                let daysUntil = (targetDay - currentDay + 7) % 7;
                if (daysUntil === 0) daysUntil = 7;

                const nextDate = new Date(today);
                nextDate.setDate(today.getDate() + daysUntil);

                const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

                return `${dayNames[nextDate.getDay()]}, ${months[nextDate.getMonth()]} ${nextDate.getDate()}`;
            }

            window.confirmAllowanceDayChange = function () {
                if (pendingAllowanceDay) {
                    document.getElementById('allowanceDaySelect').value = pendingAllowanceDay;
                    originalAllowanceDay = pendingAllowanceDay;
                    pendingAllowanceDay = null;
                }
                document.getElementById('allowanceDayModal').classList.remove('active');
            };

            window.cancelAllowanceDayChange = function () {
                pendingAllowanceDay = null;
                document.getElementById('allowanceDayModal').classList.remove('active');
            };
        });
    </script>

@endsection