@extends('layouts.parent')

@section('title', $kid->name . '\'s Wish List - AllowanceLab')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h1 style="font-size: 28px; font-weight: 700; color: #1f2937; margin: 0;">{{ $kid->name }}'s Wish List</h1>
        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="{{ route('parent.wishes.create', $kid) }}"
               class="btn-primary"
               style="background: #2563eb; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; font-size: 14px;">
                <i class="fas fa-plus"></i> Create Wish
            </a>
            <a href="{{ route('dashboard') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">{{ $errors->first() }}</div>
            @endif

            <!-- Tabs -->
            <div class="wish-tabs">
                <button class="wish-tab active" onclick="switchWishTab('pending')">
                    Pending Requests
                    @if($pendingWishes->count() > 0)
                        <span class="tab-badge">{{ $pendingWishes->count() }}</span>
                    @endif
                </button>
                <button class="wish-tab" onclick="switchWishTab('all')">
                    All Wishes
                    <span class="tab-badge">{{ $allWishes->count() }}</span>
                </button>
                <button class="wish-tab" onclick="switchWishTab('purchased')">
                    Purchased
                    <span class="tab-badge">{{ $purchasedWishes->total() }}</span>
                </button>
            </div>

            <!-- Pending Requests Tab -->
            <div id="pendingTab" class="wish-tab-content active">
                @if($pendingWishes->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-clipboard-list"></i>
                        <p>No pending wish requests</p>
                    </div>
                @else
                    <div class="wishes-list">
                        @foreach($pendingWishes as $wish)
                            <div class="wish-row">
                                <div class="wish-image">
                                    @if($wish->image_path)
                                        <img src="{{ asset('storage/' . $wish->image_path) }}" alt="{{ $wish->item_name }}">
                                    @else
                                        <div class="wish-placeholder"><i class="fas fa-box"></i></div>
                                    @endif
                                </div>
                                <div class="wish-info">
                                    <h3>{{ $wish->item_name }}</h3>
                                    <div class="wish-price">${{ number_format($wish->price, 2) }}</div>
                                    @if($wish->reason)
                                        <p class="wish-reason">{{ Str::limit($wish->reason, 100) }}</p>
                                    @endif
                                    @if($wish->item_url)
                                        <a href="{{ $wish->item_url }}" target="_blank" rel="noopener noreferrer" class="wish-link">
                                            <i class="fas fa-external-link-alt"></i> View Online
                                        </a>
                                    @endif
                                    <div class="wish-meta">
                                        <span><i class="fas fa-clock"></i> Requested {{ $wish->requested_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <div class="wish-actions">
                                    <a href="{{ route('parent.wishes.show', $wish) }}" class="btn-view">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <form action="{{ route('parent.wishes.approve', $wish) }}" method="POST" style="display: inline;" onsubmit="console.log('Form submitting...'); return true;">
                                        @csrf
                                        <button type="submit" class="btn-approve" onclick="console.log('Approve clicked'); return confirm('Approve this purchase? ${{ number_format($wish->price, 2) }} will be deducted from {{ $kid->name }}\'s balance.')">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    <button onclick="openDeclineModal({{ $wish->id }})" class="btn-decline">
                                        <i class="fas fa-times"></i> Decline
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- All Wishes Tab -->
            <div id="allTab" class="wish-tab-content">
                @if($allWishes->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-heart"></i>
                        <p>{{ $kid->name }} hasn't created any wishes yet</p>
                    </div>
                @else
                    <div class="wishes-grid">
                        @foreach($allWishes as $wish)
                            <div class="wish-card">
                                <div class="wish-card-image">
                                    @if($wish->image_path)
                                        <img src="{{ asset('storage/' . $wish->image_path) }}" alt="{{ $wish->item_name }}">
                                    @else
                                        <div class="wish-placeholder"><i class="fas fa-box"></i></div>
                                    @endif
                                </div>
                                <div class="wish-card-content">
                                    <h4>{{ $wish->item_name }}</h4>
                                    <div class="wish-price">${{ number_format($wish->price, 2) }}</div>
                                    @if($wish->reason)
                                        <p class="wish-reason">{{ Str::limit($wish->reason, 80) }}</p>
                                    @endif
                                    @if($wish->item_url)
                                        <a href="{{ $wish->item_url }}" target="_blank" rel="noopener noreferrer" class="wish-link">
                                            <i class="fas fa-external-link-alt"></i> View Online
                                        </a>
                                    @endif
                                    @if($wish->isPendingApproval())
                                        <span class="badge-pending">Pending Response</span>
                                    @endif
                                    <div class="wish-card-actions">
                                        @if($wish->isSaved())
                                            <button type="button"
                                                    class="btn-card-action btn-card-redeem"
                                                    data-wish-id="{{ $wish->id }}"
                                                    data-wish-name="{{ $wish->item_name }}"
                                                    data-wish-price="{{ $wish->price }}"
                                                    data-kid-balance="{{ $kid->balance }}"
                                                    onclick="openRedeemModal(event, this.dataset.wishId, this.dataset.wishName, parseFloat(this.dataset.wishPrice), parseFloat(this.dataset.kidBalance))"
                                                    style="flex: 1;">
                                                <i class="fas fa-gift"></i> Redeem Wish
                                            </button>
                                        @endif
                                        <a href="{{ route('parent.wishes.show', $wish) }}" class="btn-card-action btn-card-view" style="flex: 1;">
                                            <i class="fas fa-eye"></i> View Wish
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Purchased Tab -->
            <div id="purchasedTab" class="wish-tab-content">
                @if($purchasedWishes->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <p>No purchased wishes yet</p>
                    </div>
                @else
                    <div class="wishes-grid">
                        @foreach($purchasedWishes as $wish)
                            <div class="wish-card wish-purchased">
                                <div class="wish-card-image">
                                    @if($wish->image_path)
                                        <img src="{{ asset('storage/' . $wish->image_path) }}" alt="{{ $wish->item_name }}">
                                    @else
                                        <div class="wish-placeholder"><i class="fas fa-box"></i></div>
                                    @endif
                                    <div class="purchased-overlay"><i class="fas fa-check-circle"></i></div>
                                </div>
                                <div class="wish-card-content">
                                    <h4>{{ $wish->item_name }}</h4>
                                    <div class="wish-price">
                                        ${{ number_format($wish->price, 2) }}
                                        @php
                                            $purchaseTransaction = $wish->wishTransactions()->where('transaction_type', 'purchased')->first();
                                        @endphp
                                        @if($purchaseTransaction && $purchaseTransaction->note)
                                            <span style="color: #10b981; font-size: 14px; margin-left: 4px;">
                                                ({{ $purchaseTransaction->note }})
                                            </span>
                                        @endif
                                    </div>
                                    @if($wish->reason)
                                        <p class="wish-reason">{{ Str::limit($wish->reason, 80) }}</p>
                                    @endif
                                    @if($wish->item_url)
                                        <a href="{{ $wish->item_url }}" target="_blank" rel="noopener noreferrer" class="wish-link">
                                            <i class="fas fa-external-link-alt"></i> View Online
                                        </a>
                                    @endif
                                    <div class="purchased-date">
                                        <i class="fas fa-calendar"></i> {{ $wish->purchased_at->format('M j, Y') }}
                                    </div>
                                    <div class="wish-card-actions">
                                        <a href="{{ route('parent.wishes.show', $wish) }}" class="btn-card-action btn-card-view" style="width: 100%;">
                                            <i class="fas fa-eye"></i> View Wish
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    {{ $purchasedWishes->links() }}
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Decline Modal -->
@foreach($pendingWishes as $wish)
    <div id="declineModal{{ $wish->id }}" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>Decline Wish Request</h3>
            <form action="{{ route('parent.wishes.decline', $wish) }}" method="POST">
                @csrf
                <p>Why are you declining "{{ $wish->item_name }}"? (optional)</p>
                <textarea name="reason" rows="3" placeholder="Let them know why..."></textarea>
                <div class="modal-actions">
                    <button type="button" onclick="closeDeclineModal({{ $wish->id }})">Cancel</button>
                    <button type="submit" class="btn-decline">Decline Request</button>
                </div>
            </form>
        </div>
    </div>
@endforeach

<!-- Redeem Confirmation Modal -->
<div id="redeemModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.75); z-index: 10000;">
    <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 16px; padding: 32px; width: 90%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); text-align: center;">
        <div style="width: 64px; height: 64px; margin: 0 auto 16px; background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 28px;">
            <i class="fas fa-gift"></i>
        </div>
        <h3 style="font-size: 24px; font-weight: 700; color: #1f2937; margin: 0 0 8px 0;">Redeem Wish?</h3>
        <p id="redeemWishName" style="color: #6b7280; font-size: 16px; margin-bottom: 16px; font-weight: 500;"></p>

        <!-- Item Price Display -->
        <div style="background: #f9fafb; border-radius: 8px; padding: 12px; margin-bottom: 16px;">
            <div style="display: flex; justify-content: center; align-items: center; gap: 12px;">
                <span style="font-weight: 600; color: #4b5563; font-size: 15px;">Item Price:</span>
                <span id="itemPrice" style="font-size: 18px; font-weight: 700; color: #1f2937;"></span>
                <div id="adjustedPriceContainer" style="display: none; align-items: center; gap: 8px;">
                    <span id="adjustedPrice" style="font-size: 18px; font-weight: 700; color: #10b981;"></span>
                    <span style="font-size: 12px; color: #6b7280; white-space: nowrap;">
                        (adjusted item price)
                    </span>
                </div>
            </div>
        </div>

        <!-- Additional Fees Input -->
        <div style="margin-bottom: 20px; text-align: left;">
            <label style="display: block; font-weight: 600; color: #4b5563; font-size: 14px; margin-bottom: 8px;">
                Additional Fees (shipping, tax, etc.)
            </label>
            <div style="position: relative;">
                <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280; font-weight: 600;">$</span>
                <input
                    type="text"
                    id="additionalFees"
                    placeholder="0.00"
                    style="width: 100%; padding: 10px 10px 10px 24px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px; font-weight: 600; color: #1f2937;"
                    oninput="updateRedeemTotal()"
                />
            </div>
        </div>

        <!-- Balance Preview -->
        <div style="background: #f9fafb; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0;">
                <span style="font-weight: 600; color: #4b5563; font-size: 15px;">Current Balance:</span>
                <span id="currentBalance" style="font-size: 20px; font-weight: 700; color: #3b82f6;"></span>
            </div>
            <div style="display: flex; justify-content: center; padding: 8px 0; color: #9ca3af; font-size: 18px;">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0;">
                <span style="font-weight: 600; color: #4b5563; font-size: 15px;">After Purchase:</span>
                <span id="afterBalance" style="font-size: 20px; font-weight: 700;"></span>
            </div>
        </div>

        <div class="modal-actions">
            <button type="button" onclick="closeRedeemModal()" class="btn-modal btn-cancel">Cancel</button>
            <button type="button" onclick="confirmRedeem()" class="btn-modal btn-confirm">Confirm Purchase</button>
        </div>
    </div>
</div>

<!-- Hidden forms for redeem actions -->
@foreach($allWishes->where('status', 'saved') as $wish)
    <form id="redeemForm{{ $wish->id }}" action="{{ route('parent.wishes.redeem', $wish) }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="adjusted_amount" id="adjustedAmount{{ $wish->id }}" value="">
    </form>
@endforeach

<style>
/* Override content wrapper width to match goals page */
.content-wrapper {
    max-width: 1200px !important;
}

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

.wish-tabs {
    display: flex;
    gap: 8px;
    border-bottom: 2px solid #e5e7eb;
    margin-bottom: 24px;
}

.wish-tab {
    background: none;
    border: none;
    padding: 12px 20px;
    font-size: 16px;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
}

.wish-tab.active {
    color: #3b82f6;
}

.wish-tab.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 2px;
    background: #3b82f6;
}

.tab-badge {
    background: #3b82f6;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
}

.wish-tab-content {
    display: none;
}

.wish-tab-content.active {
    display: block;
}

.wishes-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.wish-row {
    background: white;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    gap: 20px;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.wish-image {
    width: 100px;
    height: 100px;
    flex-shrink: 0;
}

.wish-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}

.wish-placeholder {
    width: 100%;
    height: 100%;
    background: #f3f4f6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    font-size: 32px;
}

.wish-info {
    flex: 1;
}

.wish-info h3 {
    margin: 0 0 8px 0;
    font-size: 20px;
}

.wish-price {
    font-size: 24px;
    font-weight: 700;
    color: #3b82f6;
    margin-bottom: 8px;
}

.wish-reason {
    color: #6b7280;
    margin: 8px 0;
}

.wish-meta {
    font-size: 14px;
    color: #9ca3af;
}

.wish-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #3b82f6;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    margin-top: 8px;
}

.wish-link:hover {
    color: #2563eb;
}

.wish-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-view {
    padding: 10px 20px;
    border: 2px solid #e5e7eb;
    background: white;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    color: #6b7280;
    text-decoration: none;
}

.btn-view:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.btn-approve, .btn-decline {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-approve {
    background: #10b981;
    color: white;
}

.btn-decline {
    background: #ef4444;
    color: white;
}

.wishes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
}

.wish-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    height: 100%;
}

.wish-card-image {
    height: 200px;
    position: relative;
    flex-shrink: 0;
}

.wish-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.purchased-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(16, 185, 129, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 48px;
}

.wish-card-content {
    padding: 16px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.badge-pending {
    background: #fef3c7;
    color: #92400e;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.purchased-date {
    font-size: 14px;
    color: #10b981;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 12px;
}

.wish-card-actions {
    display: flex;
    gap: 8px;
    margin-top: auto;
    padding-top: 12px;
}

.btn-card-action {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 12px 16px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
    white-space: nowrap;
    box-sizing: border-box;
    height: 44px;
}

.btn-card-redeem {
    background: #8b5cf6;
    color: white;
    width: 100%;
    border: 2px solid transparent;
}

.btn-card-redeem:hover {
    background: #7c3aed;
}

.btn-card-view {
    background: white;
    color: #3b82f6;
    border: 2px solid #3b82f6;
}

.btn-card-view:hover {
    background: #eff6ff;
}

/* Redeem Modal Styles */
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
    z-index: 10000;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background: white !important;
    border-radius: 16px;
    padding: 32px;
    max-width: 440px;
    width: 90%;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 10px 10px -5px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease;
    position: relative !important;
    z-index: 1000000 !important;
}

@keyframes slideUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.redeem-modal {
    text-align: center;
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

.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.btn-modal {
    padding: 12px 28px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancel {
    background: #e5e7eb;
    color: #4b5563;
}

.btn-cancel:hover {
    background: #d1d5db;
}

.btn-confirm {
    background: #8b5cf6;
    color: white;
}

.btn-confirm:hover {
    background: #7c3aed;
}
</style>

<script>
function switchWishTab(tab) {
    document.querySelectorAll('.wish-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.wish-tab-content').forEach(c => c.classList.remove('active'));

    event.target.classList.add('active');
    document.getElementById(tab + 'Tab').classList.add('active');
}

function openDeclineModal(wishId) {
    document.getElementById('declineModal' + wishId).style.display = 'flex';
}

function closeDeclineModal(wishId) {
    document.getElementById('declineModal' + wishId).style.display = 'none';
}

let currentRedeemWishId = null;
let redeemModalData = {
    basePrice: 0,
    currentBalance: 0
};

function openRedeemModal(event, wishId, wishName, price, currentBalance) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    currentRedeemWishId = wishId;
    redeemModalData.basePrice = price;
    redeemModalData.currentBalance = currentBalance;

    document.getElementById('redeemWishName').textContent = wishName;
    document.getElementById('itemPrice').textContent = '$' + price.toFixed(2);
    document.getElementById('currentBalance').textContent = '$' + currentBalance.toFixed(2);
    document.getElementById('additionalFees').value = '';
    document.getElementById('adjustedPriceContainer').style.display = 'none';

    const afterBalance = currentBalance - price;
    const afterBalanceEl = document.getElementById('afterBalance');
    afterBalanceEl.textContent = '$' + afterBalance.toFixed(2);
    afterBalanceEl.style.color = afterBalance < 0 ? '#ef4444' : '#10b981';

    document.getElementById('redeemModal').style.display = 'block';
}

function updateRedeemTotal() {
    const input = document.getElementById('additionalFees');
    let value = input.value.replace(/[^0-9]/g, '');

    if (value === '') {
        input.value = '';
        document.getElementById('adjustedPriceContainer').style.display = 'none';

        // Reset to base price
        const afterBalance = redeemModalData.currentBalance - redeemModalData.basePrice;
        const afterBalanceEl = document.getElementById('afterBalance');
        afterBalanceEl.textContent = '$' + afterBalance.toFixed(2);
        afterBalanceEl.style.color = afterBalance < 0 ? '#ef4444' : '#10b981';
        return;
    }

    // Format currency: 500 becomes 5.00
    const cents = parseInt(value);
    const dollars = (cents / 100).toFixed(2);
    input.value = dollars;

    const additionalFee = parseFloat(dollars);
    const adjustedTotal = redeemModalData.basePrice + additionalFee;

    // Show adjusted price in green with label
    document.getElementById('adjustedPrice').textContent = '$' + adjustedTotal.toFixed(2);
    document.getElementById('adjustedPriceContainer').style.display = 'flex';

    // Update after balance with adjusted total
    const afterBalance = redeemModalData.currentBalance - adjustedTotal;
    const afterBalanceEl = document.getElementById('afterBalance');
    afterBalanceEl.textContent = '$' + afterBalance.toFixed(2);
    afterBalanceEl.style.color = afterBalance < 0 ? '#ef4444' : '#10b981';
}

function closeRedeemModal() {
    document.getElementById('redeemModal').style.display = 'none';
    currentRedeemWishId = null;
    redeemModalData = { basePrice: 0, currentBalance: 0 };
}

function confirmRedeem() {
    if (currentRedeemWishId) {
        // Calculate the final amount (base price + additional fees)
        const additionalFeesInput = document.getElementById('additionalFees').value;
        const additionalFees = additionalFeesInput ? parseFloat(additionalFeesInput) : 0;
        const finalAmount = redeemModalData.basePrice + additionalFees;

        // Set the adjusted amount in the hidden form field
        document.getElementById('adjustedAmount' + currentRedeemWishId).value = finalAmount.toFixed(2);

        // Submit the form
        document.getElementById('redeemForm' + currentRedeemWishId).submit();
    }
}

// Auto-select the correct tab on page load
document.addEventListener('DOMContentLoaded', function() {
    const hasPendingWishes = {{ $pendingWishes->count() > 0 ? 'true' : 'false' }};

    if (!hasPendingWishes) {
        // No pending wishes, switch to All Wishes tab
        const allWishesButton = document.querySelectorAll('.wish-tab')[1]; // Second button is "All Wishes"
        allWishesButton.click();
    }
});
</script>
@endsection
