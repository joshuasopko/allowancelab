<!-- Send Invite Modal (Step 2) -->
<div class="modal-overlay" id="sendInviteModal" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Send Invite to <span id="inviteKidName"></span></h2>
            <button class="close-btn" onclick="closeSendInviteModal()">&times;</button>
        </div>

        <div class="modal-body">
            <p style="color: #666; margin-bottom: 25px; text-align: center;">Choose how you'd like to send the invite:
            </p>

            <!-- Invite Method Buttons (Horizontal) -->
            <div class="invite-buttons-row">
                <button type="button" class="invite-btn invite-btn-blue" onclick="toggleInviteMethod('copyLink')">
                    <i class="fas fa-link"></i>
                    <span>Copy Link</span>
                </button>
                <button type="button" class="invite-btn invite-btn-green" onclick="toggleInviteMethod('email')">
                    <i class="fas fa-envelope"></i>
                    <span>Email Invite</span>
                </button>
                <button type="button" class="invite-btn invite-btn-purple" onclick="toggleInviteMethod('qr')">
                    <i class="fas fa-qrcode"></i>
                    <span>QR Code</span>
                </button>
            </div>

            <!-- Copy Link Content -->
            <div class="invite-method-content" id="copyLinkContent" style="display: none;">
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="text" class="form-input" id="inviteLinkInput" readonly style="flex: 1;">
                    <button type="button" class="modal-btn modal-btn-submit" onclick="copyInviteLink()">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
            </div>

            <!-- Email Invite Content -->
            <div class="invite-method-content" id="emailContent" style="display: none;">
                <label class="form-label">Kid's Email</label>
                <input type="email" class="form-input" id="kidEmailInput" placeholder="kid@example.com">
                <button type="button" class="modal-btn modal-btn-submit" style="margin-top: 10px;"
                    onclick="sendEmailInvite()">
                    <i class="fas fa-paper-plane"></i> Send Email
                </button>
            </div>

            <!-- QR Code Content -->
            <div class="invite-method-content" id="qrContent" style="display: none;">
                <div id="qrCodeDisplay" style="text-align: center;">
                    <!-- QR code will be generated here -->
                </div>
            </div>
        </div>

        <div class="modal-footer-invite">
            <div class="skip-section">
                <button type="button" class="skip-link" onclick="skipInvite()">
                    Skip for Now →
                </button>
                <p class="skip-notice">You can send an invite later from the Manage Kid section</p>
            </div>
        </div>
    </div>
</div>