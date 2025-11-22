<!-- Send Invite Modal (Step 2) -->
<div class="modal-overlay" id="sendInviteModal" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Send Invite to <span id="inviteKidName"></span></h2>
            <button class="close-btn" onclick="closeSendInviteModal()">&times;</button>
        </div>

        <div class="modal-body">
            <p style="color: #666; margin-bottom: 20px;">Choose how you'd like to send the invite:</p>

            <!-- Copy Link Button -->
            <div class="invite-option">
                <button type="button" class="invite-method-btn" onclick="toggleInviteMethod('copyLink')">
                    <span>📋</span> Copy Link
                </button>
                <div class="invite-method-content" id="copyLinkContent" style="display: none;">
                    <div style="display: flex; gap: 10px; align-items: center; margin-top: 10px;">
                        <input type="text" class="form-input" id="inviteLinkInput" readonly style="flex: 1;">
                        <button type="button" class="modal-btn modal-btn-submit"
                            onclick="copyInviteLink()">Copy</button>
                    </div>
                </div>
            </div>

            <!-- Email Invite Button -->
            <div class="invite-option">
                <button type="button" class="invite-method-btn" onclick="toggleInviteMethod('email')">
                    <span>📧</span> Email Invite
                </button>
                <div class="invite-method-content" id="emailContent" style="display: none;">
                    <div style="margin-top: 10px;">
                        <label class="form-label">Kid's Email</label>
                        <input type="email" class="form-input" id="kidEmailInput" placeholder="kid@example.com">
                        <button type="button" class="modal-btn modal-btn-submit" style="margin-top: 10px;"
                            onclick="sendEmailInvite()">Send Email</button>
                    </div>
                </div>
            </div>

            <!-- QR Code Button -->
            <div class="invite-option">
                <button type="button" class="invite-method-btn" onclick="toggleInviteMethod('qr')">
                    <span>📱</span> QR Code
                </button>
                <div class="invite-method-content" id="qrContent" style="display: none;">
                    <div id="qrCodeDisplay" style="margin-top: 10px; text-align: center;">
                        <!-- QR code will be generated here -->
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer" style="border-top: 1px solid #e5e7eb; padding-top: 15px;">
            <button type="button" class="modal-btn modal-btn-cancel" onclick="skipInvite()">
                Skip for Now
            </button>
            <p style="font-size: 12px; color: #666; margin-top: 10px;">
                You can send an invite later from the Manage Kid section
            </p>
        </div>
    </div>
</div>