@extends('layouts.parent')

@section('title', $wish->item_name . ' - ' . $kid->name)

@section('content')
<div class="page-header">
    <h1>{{ $kid->name }}'s Wish</h1>
    <a href="{{ route('kids.wishes', $kid) }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> Back to Wish List
    </a>
</div>

<!-- Success/Error Messages -->
@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        {{ $errors->first() }}
    </div>
@endif

<!-- Wish Detail Card -->
<div class="wish-detail-container">
    <div class="wish-detail-card">
        <!-- Wish Image -->
        <div class="wish-detail-image-container">
            @if($wish->image_path)
                <img src="{{ \Storage::url($wish->image_path) }}"
                     alt="{{ $wish->item_name }}"
                     class="wish-detail-image">
            @else
                <div class="wish-detail-placeholder">
                    <i class="fas fa-box-open"></i>
                    <span>No image available</span>
                </div>
            @endif

            @if($wish->isPurchased())
                <div class="purchased-badge">
                    <i class="fas fa-check-circle"></i> Purchased
                </div>
            @endif
        </div>

        <!-- Wish Info -->
        <div class="wish-detail-content">
            <h2 class="wish-detail-title">{{ $wish->item_name }}</h2>
            <div class="wish-detail-price">${{ number_format($wish->price, 2) }}</div>

            <!-- Status Badge -->
            @if($wish->isPendingApproval())
                <div class="wish-status-badge wish-status-pending">
                    <i class="fas fa-clock"></i> Requested {{ $wish->requested_at->diffForHumans() }}
                </div>
            @elseif($wish->isPurchased())
                <div class="wish-status-badge wish-status-purchased">
                    <i class="fas fa-check-circle"></i> Purchased {{ $wish->purchased_at->format('M j, Y') }}
                </div>
            @elseif($wish->isDeclined())
                <div class="wish-status-badge wish-status-declined">
                    <i class="fas fa-times-circle"></i> Declined
                </div>
            @endif

            <!-- URL Link -->
            @if($wish->item_url)
                <div class="wish-detail-url">
                    <a href="{{ $wish->item_url }}" target="_blank" rel="noopener noreferrer" class="btn-view-online">
                        <i class="fas fa-external-link-alt"></i> View Item Online
                    </a>
                </div>
            @endif

            <!-- Reason -->
            @if($wish->reason)
                <div class="wish-detail-section">
                    <h3>Why {{ $kid->name }} wants this:</h3>
                    <p>{{ $wish->reason }}</p>
                </div>
            @endif

            <!-- Balance Info -->
            <div class="wish-detail-section balance-info">
                <div class="balance-item">
                    <span class="balance-label">{{ $kid->name }}'s Balance:</span>
                    <span class="balance-value">${{ number_format($kid->balance, 2) }}</span>
                </div>
                @if(!$wish->isPurchased())
                    <div class="balance-item">
                        <span class="balance-label">After Purchase:</span>
                        <span class="balance-value {{ $kid->balance >= $wish->price ? 'text-green' : 'text-red' }}">
                            ${{ number_format($kid->balance - $wish->price, 2) }}
                        </span>
                    </div>
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="wish-detail-actions">
                @if($wish->isPendingApproval())
                    <!-- Approve (deducts from balance) -->
                    <button type="button" class="btn-action btn-approve" onclick="openApproveModal()">
                        <i class="fas fa-check"></i> Approve & Deduct
                    </button>

                    <!-- Hidden form submitted by modal -->
                    <form id="approveWishForm" action="{{ route('parent.wishes.approve', $wish) }}" method="POST" style="display: none;">
                        @csrf
                        <input type="hidden" name="adjusted_amount" id="approve_adjusted_amount" value="{{ $wish->price }}">
                    </form>

                    <!-- Decline -->
                    <button onclick="openDeclineModal()" class="btn-action btn-decline">
                        <i class="fas fa-times"></i> Decline Request
                    </button>
                @elseif($wish->isSaved())
                    <!-- Redeem (deducts from balance) -->
                    <button type="button" class="btn-action btn-redeem" onclick="openRedeemModal()">
                        <i class="fas fa-gift"></i> Redeem Wish
                    </button>
                    <!-- Delete saved wish -->
                    <button type="button" onclick="openWishDeleteModal()" class="btn-action btn-delete">
                        <i class="fas fa-trash"></i> Delete Wish
                    </button>
                @elseif($wish->isDeclined())
                    <!-- Delete declined wish -->
                    <button type="button" onclick="openWishDeleteModal()" class="btn-action btn-delete">
                        <i class="fas fa-trash"></i> Delete Wish
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.page-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.btn-back {
    color: #6b7280;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: color 0.2s;
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-back:hover {
    color: #3b82f6;
}

.alert {
    padding: 16px;
    border-radius: 12px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #10b981;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #ef4444;
}

.wish-detail-container {
    max-width: 1000px;
}

.wish-detail-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    display: grid;
    grid-template-columns: 400px 1fr;
    gap: 32px;
}

.wish-detail-image-container {
    position: relative;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 400px;
}

.wish-detail-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.wish-detail-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    color: #9ca3af;
}

.wish-detail-placeholder i {
    font-size: 64px;
}

.purchased-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    background: #10b981;
    color: white;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}

.wish-detail-content {
    padding: 32px 32px 32px 0;
}

.wish-detail-title {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 12px;
}

.wish-detail-price {
    font-size: 32px;
    font-weight: 700;
    color: #3b82f6;
    margin-bottom: 16px;
}

.wish-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    margin-bottom: 16px;
}

.wish-status-pending {
    background: #fef3c7;
    color: #92400e;
}

.wish-status-purchased {
    background: #d1fae5;
    color: #065f46;
}

.wish-status-declined {
    background: #fee2e2;
    color: #991b1b;
}

.wish-detail-url {
    margin: 20px 0;
}

.btn-view-online {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: #3b82f6;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: background 0.2s;
}

.btn-view-online:hover {
    background: #2563eb;
}

.wish-detail-section {
    margin: 24px 0;
    padding: 20px;
    background: #f9fafb;
    border-radius: 12px;
}

.wish-detail-section h3 {
    font-size: 16px;
    font-weight: 600;
    color: #4b5563;
    margin-bottom: 12px;
}

.wish-detail-section p {
    color: #6b7280;
    line-height: 1.6;
}

.balance-info {
    background: #eff6ff;
    border: 2px solid #3b82f6;
}

.balance-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
}

.balance-label {
    font-weight: 600;
    color: #1f2937;
}

.balance-value {
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
}

.text-green {
    color: #10b981 !important;
}

.text-red {
    color: #ef4444 !important;
}

.wish-detail-actions {
    display: flex;
    gap: 12px;
    margin-top: 32px;
    flex-wrap: wrap;
}

.btn-action {
    padding: 14px 28px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-approve {
    background: #10b981;
    color: white;
}

.btn-approve:hover {
    background: #059669;
}

.btn-redeem {
    background: #8b5cf6;
    color: white;
}

.btn-redeem:hover {
    background: #7c3aed;
}

.btn-decline {
    background: #ef4444;
    color: white;
}

.btn-decline:hover {
    background: #dc2626;
}

.btn-cancel {
    background: #e5e7eb;
    color: #4b5563;
}

.btn-cancel:hover {
    background: #d1d5db;
}

.btn-delete {
    background: #ef4444;
    color: white;
}

.btn-delete:hover {
    background: #dc2626;
}


/* Responsive */
@media (max-width: 768px) {
    .wish-detail-card {
        grid-template-columns: 1fr;
    }

    .wish-detail-content {
        padding: 24px;
    }

    .wish-detail-image-container {
        min-height: 300px;
    }

    .wish-detail-actions {
        flex-direction: column;
    }

    .btn-action {
        width: 100%;
        justify-content: center;
    }
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.75);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
}

.modal-content {
    background: white;
    border-radius: 16px;
    padding: 32px;
    width: 90%;
    max-width: 440px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
}

.modal-content h3 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 16px;
    color: #1f2937;
}

.modal-content p {
    color: #6b7280;
    margin-bottom: 12px;
}

.modal-content textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-family: inherit;
    font-size: 14px;
    resize: vertical;
}

.modal-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
    justify-content: flex-end;
}

.redeem-modal {
    text-align: center;
    max-width: 440px;
}

.modal-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 16px;
    background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 28px;
}

.redeem-modal h3 {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 8px 0;
}

.wish-name {
    color: #6b7280;
    font-size: 16px;
    margin-bottom: 24px;
    font-weight: 500;
}

.balance-preview {
    background: #f9fafb;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
}

.balance-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
}

.balance-label {
    font-weight: 600;
    color: #4b5563;
    font-size: 15px;
}

.balance-amount {
    font-size: 20px;
    font-weight: 700;
}

.balance-amount.current {
    color: #3b82f6;
}

.balance-amount.after {
    color: #10b981;
}

.balance-amount.negative {
    color: #ef4444;
}

.balance-divider {
    display: flex;
    justify-content: center;
    padding: 8px 0;
    color: #9ca3af;
    font-size: 18px;
}
</style>

<script>
const basePrice = {{ $wish->price }};
const currentBalance = {{ $kid->balance }};

function openApproveModal() {
    document.getElementById('approveWishModal').style.display = 'flex';
    updateApproveTotal();
}

function closeApproveModal() {
    document.getElementById('approveWishModal').style.display = 'none';
    const feesInput = document.getElementById('approve_fees');
    if (feesInput) feesInput.value = '';
    updateApproveTotal();
}

function confirmApproveWish() {
    document.getElementById('approveWishForm').submit();
}

function updateApproveTotal() {
    const input = document.getElementById('approve_fees');
    if (!input) return;
    let value = input.value.replace(/[^0-9]/g, '');

    // Cash-register style: digits become cents
    if (value === '') value = '0';
    const cents = parseInt(value);
    const dollars = (cents / 100).toFixed(2);
    input.value = dollars;

    const additionalFee = parseFloat(dollars);
    const adjustedTotal = basePrice + additionalFee;

    // Update Total Deducted (always red)
    document.getElementById('approveTotalDeducted').textContent = '$' + adjustedTotal.toFixed(2);

    // Update Balance After with colour
    const afterBalance = currentBalance - adjustedTotal;
    const afterEl = document.getElementById('approveBalanceAfter');
    afterEl.textContent = '$' + afterBalance.toFixed(2);
    afterEl.style.color = afterBalance >= 0 ? '#10b981' : '#ef4444';

    // Update hidden input
    document.getElementById('approve_adjusted_amount').value = adjustedTotal.toFixed(2);
}

function openDeclineModal() {
    document.getElementById('declineModal').style.display = 'flex';
}

function closeDeclineModal() {
    document.getElementById('declineModal').style.display = 'none';
}

function openWishDeleteModal() {
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeWishDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

function openRedeemModal() {
    document.getElementById('redeemModal').style.display = 'flex';
    updateRedeemTotal(); // Initialize on open
}

function closeRedeemModal() {
    document.getElementById('redeemModal').style.display = 'none';
    // Reset the form
    document.getElementById('additional_fees').value = '';
    updateRedeemTotal();
}

function updateRedeemTotal() {
    const input = document.getElementById('additional_fees');
    let value = input.value.replace(/[^0-9]/g, '');

    // Cash-register style: digits become cents
    if (value === '') {
        value = '0';
    }
    const cents = parseInt(value);
    const dollars = (cents / 100).toFixed(2);
    input.value = dollars;

    // Calculate adjusted total
    const additionalFee = parseFloat(dollars);
    const adjustedTotal = basePrice + additionalFee;

    // Update Total Deducted (always red)
    document.getElementById('modalAdjustedPrice').textContent = '$' + adjustedTotal.toFixed(2);

    // Update Balance After with colour: green if positive, red if negative
    const afterBalance = currentBalance - adjustedTotal;
    const afterBalanceEl = document.getElementById('modalAfterBalance');
    afterBalanceEl.textContent = '$' + afterBalance.toFixed(2);
    afterBalanceEl.style.color = afterBalance >= 0 ? '#10b981' : '#ef4444';

    // Update hidden input for form submission
    document.getElementById('adjusted_amount_input').value = adjustedTotal.toFixed(2);
}

// Close modals when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const redeemModal = document.getElementById('redeemModal');
    if (redeemModal) {
        redeemModal.addEventListener('click', function(e) {
            if (e.target === redeemModal) {
                closeRedeemModal();
            }
        });
    }

    const approveWishModal = document.getElementById('approveWishModal');
    if (approveWishModal) {
        approveWishModal.addEventListener('click', function(e) {
            if (e.target === approveWishModal) {
                closeApproveModal();
            }
        });
    }

    const declineModal = document.getElementById('declineModal');
    if (declineModal) {
        declineModal.addEventListener('click', function(e) {
            if (e.target === declineModal) {
                closeDeclineModal();
            }
        });
    }

    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal) {
                closeWishDeleteModal();
            }
        });
    }
});
</script>
@endsection

@section('modals')
<!-- Approve Wish Modal -->
@if($wish->isPendingApproval())
<div id="approveWishModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.75); z-index: 99999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 16px; padding: 32px; width: 90%; max-width: 440px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3);">
        {{-- Header --}}
        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
            <div style="width: 56px; height: 56px; border-radius: 50%; background: #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-check" style="color: white; font-size: 22px;"></i>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 20px; font-weight: 700; color: #111827;">Approve Purchase</h3>
                <p style="margin: 4px 0 0 0; font-size: 14px; color: #6b7280; line-height: 1.4;">{{ Str::limit($wish->item_name, 60) }}</p>
            </div>
        </div>

        {{-- Item Price row --}}
        <div style="background: #f9fafb; border-radius: 12px; padding: 18px; margin-bottom: 4px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 14px; font-weight: 600; color: #4b5563;">Item Price:</span>
                <span style="font-size: 18px; font-weight: 700; color: #111827;">${{ number_format($wish->price, 2) }}</span>
            </div>
        </div>

        {{-- Additional Fees Input --}}
        <div style="margin-bottom: 4px;">
            <label style="display: block; font-size: 14px; font-weight: 600; color: #4b5563; margin: 12px 0 8px 0;">
                Additional Fees <span style="font-weight: 400; color: #9ca3af;">(shipping, tax, etc.)</span>
            </label>
            <div style="position: relative;">
                <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #6b7280; font-weight: 600; font-size: 15px;">$</span>
                <input
                    type="text"
                    id="approve_fees"
                    placeholder="0.00"
                    inputmode="numeric"
                    style="width: 100%; box-sizing: border-box; padding: 12px 12px 12px 28px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px; font-weight: 600; color: #1f2937; outline: none;"
                    oninput="updateApproveTotal()"
                    onfocus="this.style.borderColor='#10b981'"
                    onblur="this.style.borderColor='#e5e7eb'"
                />
            </div>
        </div>

        {{-- Balance breakdown --}}
        <div style="background: #f9fafb; border-radius: 12px; padding: 18px; margin: 12px 0 24px 0;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0;">
                <span style="font-size: 14px; font-weight: 600; color: #4b5563;">Total Deducted:</span>
                <span id="approveTotalDeducted" style="font-size: 18px; font-weight: 700; color: #ef4444;">${{ number_format($wish->price, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0;">
                <span style="font-size: 14px; font-weight: 600; color: #4b5563;">{{ $kid->name }}'s Balance:</span>
                <span style="font-size: 18px; font-weight: 700; color: #3b82f6;">${{ number_format($kid->balance, 2) }}</span>
            </div>
            <div style="border-top: 1px solid #e5e7eb; margin: 8px 0;"></div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0;">
                <span style="font-size: 14px; font-weight: 600; color: #4b5563;">Balance After:</span>
                <span id="approveBalanceAfter" style="font-size: 18px; font-weight: 700; color: {{ $kid->balance >= $wish->price ? '#10b981' : '#ef4444' }};">${{ number_format($kid->balance - $wish->price, 2) }}</span>
            </div>
        </div>

        <div style="display: flex; gap: 12px;">
            <button onclick="closeApproveModal()" style="flex: 1; padding: 13px 16px; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;">
                Cancel
            </button>
            <button onclick="confirmApproveWish()" style="flex: 1; padding: 13px 16px; background: #10b981; color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;">
                <i class="fas fa-check"></i> Approve & Deduct
            </button>
        </div>
    </div>
</div>
@endif

<!-- Delete Confirmation Modal -->
@if($wish->isDeclined() || $wish->isSaved())
<div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.75); z-index: 99999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 16px; padding: 32px; width: 90%; max-width: 440px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); text-align: center;">
        <div style="width: 64px; height: 64px; margin: 0 auto 16px; background: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 28px;">
            <i class="fas fa-trash"></i>
        </div>
        <h3 style="font-size: 24px; font-weight: 700; color: #1f2937; margin: 0 0 8px 0;">Delete This Wish?</h3>
        <p style="color: #6b7280; font-size: 16px; margin-bottom: 24px;">This action cannot be undone. The wish will be permanently removed.</p>

        <form action="{{ route('parent.wishes.destroy', $wish) }}" method="POST">
            @csrf
            @method('DELETE')
            <div style="display: flex; gap: 12px; justify-content: center;">
                <button type="button" onclick="closeWishDeleteModal()" style="padding: 12px 28px; border: none; border-radius: 8px; font-weight: 600; font-size: 16px; cursor: pointer; background: #e5e7eb; color: #4b5563; transition: background 0.2s;">Cancel</button>
                <button type="submit" style="padding: 12px 28px; border: none; border-radius: 8px; font-weight: 600; font-size: 16px; cursor: pointer; background: #ef4444; color: white; transition: background 0.2s;">Delete Wish</button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Decline Modal -->
@if($wish->isPendingApproval())
<div id="declineModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.75); z-index: 99999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 16px; padding: 32px; width: 90%; max-width: 440px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3);">
        <h3 style="font-size: 24px; font-weight: 700; margin-bottom: 16px; color: #1f2937;">Decline Purchase Request</h3>
        <form action="{{ route('parent.wishes.decline', $wish) }}" method="POST">
            @csrf
            <p style="color: #6b7280; margin-bottom: 12px;">Let {{ $kid->name }} know why you're declining this request (optional):</p>
            <textarea name="reason" rows="3" placeholder="e.g., Let's save up a bit more first..." style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-family: inherit; font-size: 14px; resize: vertical;"></textarea>
            <div style="display: flex; gap: 12px; margin-top: 24px; justify-content: flex-end;">
                <button type="button" onclick="closeDeclineModal()" style="padding: 14px 28px; border: none; border-radius: 8px; font-weight: 600; font-size: 16px; cursor: pointer; background: #e5e7eb; color: #4b5563;">Cancel</button>
                <button type="submit" style="padding: 14px 28px; border: none; border-radius: 8px; font-weight: 600; font-size: 16px; cursor: pointer; background: #ef4444; color: white;">Decline Request</button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Redeem Confirmation Modal -->
@if($wish->isSaved())
<div id="redeemModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.75); z-index: 99999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 16px; padding: 32px; width: 90%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3);">

        {{-- Header --}}
        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
            <div style="width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-gift" style="color: white; font-size: 22px;"></i>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 20px; font-weight: 700; color: #111827;">Redeem Wish</h3>
                <p style="margin: 4px 0 0 0; font-size: 14px; color: #6b7280; line-height: 1.4;">{{ Str::limit($wish->item_name, 60) }}</p>
            </div>
        </div>

        {{-- Item Price row --}}
        <div style="background: #f9fafb; border-radius: 12px; padding: 18px; margin-bottom: 4px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 14px; font-weight: 600; color: #4b5563;">Item Price:</span>
                <span style="font-size: 18px; font-weight: 700; color: #111827;">${{ number_format($wish->price, 2) }}</span>
            </div>
        </div>

        {{-- Additional Fees Input --}}
        <div style="margin-bottom: 4px;">
            <label style="display: block; font-size: 14px; font-weight: 600; color: #4b5563; margin: 12px 0 8px 0;">
                Additional Fees <span style="font-weight: 400; color: #9ca3af;">(shipping, tax, etc.)</span>
            </label>
            <div style="position: relative;">
                <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #6b7280; font-weight: 600; font-size: 15px;">$</span>
                <input
                    type="text"
                    id="additional_fees"
                    placeholder="0.00"
                    inputmode="numeric"
                    style="width: 100%; box-sizing: border-box; padding: 12px 12px 12px 28px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px; font-weight: 600; color: #1f2937; outline: none;"
                    oninput="updateRedeemTotal()"
                    onfocus="this.style.borderColor='#8b5cf6'"
                    onblur="this.style.borderColor='#e5e7eb'"
                />
            </div>
        </div>

        {{-- Balance breakdown --}}
        <div style="background: #f9fafb; border-radius: 12px; padding: 18px; margin: 12px 0 24px 0;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0;">
                <span style="font-size: 14px; font-weight: 600; color: #4b5563;">Total Deducted:</span>
                <span id="modalAdjustedPrice" style="font-size: 18px; font-weight: 700; color: #ef4444;">${{ number_format($wish->price, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0;">
                <span style="font-size: 14px; font-weight: 600; color: #4b5563;">{{ $kid->name }}'s Balance:</span>
                <span style="font-size: 18px; font-weight: 700; color: #3b82f6;">${{ number_format($kid->balance, 2) }}</span>
            </div>
            <div style="border-top: 1px solid #e5e7eb; margin: 8px 0;"></div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0;">
                <span style="font-size: 14px; font-weight: 600; color: #4b5563;">Balance After:</span>
                <span id="modalAfterBalance" style="font-size: 18px; font-weight: 700; color: {{ $kid->balance >= $wish->price ? '#10b981' : '#ef4444' }};">${{ number_format($kid->balance - $wish->price, 2) }}</span>
            </div>
        </div>

        <form id="redeemForm" action="{{ route('parent.wishes.redeem', $wish) }}" method="POST">
            @csrf
            <input type="hidden" name="adjusted_amount" id="adjusted_amount_input" value="{{ $wish->price }}">
            <div style="display: flex; gap: 12px;">
                <button type="button" onclick="closeRedeemModal()" style="flex: 1; padding: 13px 16px; border: none; border-radius: 8px; font-weight: 600; font-size: 15px; cursor: pointer; background: #f3f4f6; color: #374151;">
                    Cancel
                </button>
                <button type="submit" style="flex: 1; padding: 13px 16px; border: none; border-radius: 8px; font-weight: 600; font-size: 15px; cursor: pointer; background: #8b5cf6; color: white;">
                    <i class="fas fa-gift"></i> Redeem Wish
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
