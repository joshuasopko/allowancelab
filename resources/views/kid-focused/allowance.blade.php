@extends('layouts.kid-focused')

@section('tab-title', 'Allowance')

@section('header-right')
    <a href="{{ route('dashboard') }}" class="add-kid-btn" style="text-decoration: none;">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
@endsection

@section('content')
<div class="allowance-section">
    <!-- Quick Actions -->
    <div class="quick-actions-card">
        <h3>Quick Actions</h3>
        <div class="action-buttons-row">
            <button onclick="openDepositModal()" class="btn-action btn-deposit">
                <i class="fas fa-plus"></i> Add Money
            </button>
            <button onclick="openSpendModal()" class="btn-action btn-spend">
                <i class="fas fa-minus"></i> Record Spending
            </button>
        </div>
    </div>

    <!-- Transactions Ledger -->
    <div class="transactions-card">
        <div class="transactions-header">
            <h3>Recent Transactions</h3>
            @if($transactions->total() > 0)
                <span class="transaction-count-header">Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }}</span>
            @endif
        </div>
        @if($transactions->count() > 0)
            <div class="transactions-list">
                @foreach($transactions as $transaction)
                    <div class="transaction-item">
                        <div class="transaction-icon {{ $transaction->type === 'deposit' ? 'deposit' : 'spend' }}">
                            <i class="fas {{ $transaction->type === 'deposit' ? 'fa-plus' : 'fa-minus' }}"></i>
                        </div>
                        <div class="transaction-details">
                            <div class="transaction-description">{{ $transaction->description }}</div>
                            <div class="transaction-meta">
                                {{ $transaction->created_at->format('M j, Y') }} •
                                {{ $transaction->initiated_by === 'parent' ? 'Parent' : 'Kid' }}
                            </div>
                        </div>
                        <div class="transaction-amount {{ $transaction->type === 'deposit' ? 'positive' : 'negative' }}">
                            {{ $transaction->type === 'deposit' ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                        </div>
                    </div>
                @endforeach
            </div>

            @if($transactions->hasPages())
                <div class="pagination-wrapper">
                    {{ $transactions->links() }}
                </div>
            @endif
        @else
            <p class="empty-state">No transactions yet</p>
        @endif
    </div>

    <!-- Settings Link -->
    <div class="settings-link-section">
        <a href="{{ route('kids.manage', $kid) }}" class="btn-settings-link">
            <i class="fas fa-cog"></i> Allowance Settings
        </a>
    </div>
</div>

<style>
.allowance-section {
    display: flex;
    flex-direction: column;
    gap: 24px;
    margin-top: 32px;
}

.quick-actions-card,
.transactions-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.quick-actions-card h3 {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 20px 0;
}

.transactions-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.transactions-header h3 {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.transaction-count-header {
    font-size: 14px;
    font-weight: 500;
    color: #6b7280;
}

.action-buttons-row {
    display: flex;
    gap: 12px;
}

.btn-action {
    flex: 1;
    padding: 14px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-deposit {
    background: #10b981;
    color: white;
}

.btn-deposit:hover {
    background: #059669;
}

.btn-spend {
    background: #ef4444;
    color: white;
}

.btn-spend:hover {
    background: #dc2626;
}

.transactions-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.transaction-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: #f9fafb;
    border-radius: 8px;
    transition: background 0.2s;
}

.transaction-item:hover {
    background: #f3f4f6;
}

.transaction-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.transaction-icon.deposit {
    background: #d1fae5;
    color: #059669;
}

.transaction-icon.spend {
    background: #fee2e2;
    color: #dc2626;
}

.transaction-details {
    flex: 1;
    min-width: 0;
}

.transaction-description {
    font-weight: 600;
    color: #1f2937;
    font-size: 15px;
    margin-bottom: 4px;
}

.transaction-meta {
    color: #6b7280;
    font-size: 13px;
}

.transaction-amount {
    font-weight: 700;
    font-size: 18px;
}

.transaction-amount.positive {
    color: #10b981;
}

.transaction-amount.negative {
    color: #ef4444;
}

.empty-state {
    text-align: center;
    color: #6b7280;
    padding: 40px 20px;
}

.settings-link-section {
    text-align: center;
}

.btn-settings-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: #f3f4f6;
    color: #4b5563;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: background 0.2s;
}

.btn-settings-link:hover {
    background: #e5e7eb;
}

/* Pagination Styles */
.pagination-wrapper {
    margin-top: 24px;
    display: flex;
    justify-content: center;
}

.pagination-wrapper nav {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Hide the default Laravel pagination text (Showing X to Y of Z results) */
.pagination-wrapper p {
    display: none;
}

/* Hide Previous/Next text buttons, keep only arrows and page numbers */
.pagination-wrapper a[rel="prev"]:not([aria-label]),
.pagination-wrapper a[rel="next"]:not([aria-label]),
.pagination-wrapper span[aria-disabled="true"]:first-child,
.pagination-wrapper span[aria-disabled="true"]:last-child {
    display: none;
}

.pagination-wrapper .relative {
    display: flex;
    align-items: center;
    gap: 4px;
}

.pagination-wrapper a,
.pagination-wrapper span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}

.pagination-wrapper a {
    background: white;
    color: #6b7280;
    border: 2px solid #e5e7eb;
}

.pagination-wrapper a:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
    color: #3b82f6;
}

.pagination-wrapper span[aria-current="page"] {
    background: #3b82f6;
    color: white;
    border: 2px solid #3b82f6;
}

.pagination-wrapper span[aria-disabled="true"] {
    background: #f9fafb;
    color: #d1d5db;
    border: 2px solid #f3f4f6;
    cursor: not-allowed;
}

.pagination-wrapper svg {
    width: 16px;
    height: 16px;
}

@media (max-width: 768px) {
    .action-buttons-row {
        flex-direction: column;
    }

    .transaction-item {
        padding: 12px;
    }

    .transaction-amount {
        font-size: 16px;
    }
}
</style>
@endsection

@section('modals')
<!-- Deposit Modal -->
<div id="depositModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.75); z-index: 99999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 16px; padding: 32px; width: 90%; max-width: 440px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3);">
        <h3 style="font-size: 24px; font-weight: 700; margin-bottom: 16px; color: #1f2937;">Add Money</h3>
        <form action="{{ route('kids.deposit', $kid) }}" method="POST" class="kid-focused-modal-form">
            @csrf
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #4b5563; font-size: 14px; margin-bottom: 8px;">Amount</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280; font-weight: 600;">$</span>
                    <input type="text" name="amount" required oninput="handleDepositInput(event)" placeholder="0.00"
                           style="width: 100%; padding: 10px 10px 10px 24px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px;">
                </div>
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #4b5563; font-size: 14px; margin-bottom: 8px;">Description</label>
                <input type="text" name="note" placeholder="e.g., Birthday gift"
                       style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px;">
            </div>
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="button" onclick="closeDepositModal()"
                        style="flex: 1; padding: 12px; border: none; border-radius: 8px; font-weight: 600; background: #e5e7eb; color: #4b5563; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit"
                        style="flex: 1; padding: 12px; border: none; border-radius: 8px; font-weight: 600; background: #10b981; color: white; cursor: pointer;">
                    Add Money
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Spend Modal -->
<div id="spendModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.75); z-index: 99999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 16px; padding: 32px; width: 90%; max-width: 440px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3);">
        <h3 style="font-size: 24px; font-weight: 700; margin-bottom: 16px; color: #1f2937;">Record Spending</h3>
        <form action="{{ route('kids.spend', $kid) }}" method="POST" class="kid-focused-modal-form">
            @csrf
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #4b5563; font-size: 14px; margin-bottom: 8px;">Amount</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280; font-weight: 600;">$</span>
                    <input type="text" name="amount" required oninput="handleSpendInput(event)" placeholder="0.00"
                           style="width: 100%; padding: 10px 10px 10px 24px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px;">
                </div>
                <p style="font-size: 12px; color: #6b7280; margin-top: 4px;">Available: ${{ number_format($kid->balance, 2) }}</p>
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #4b5563; font-size: 14px; margin-bottom: 8px;">What did they buy?</label>
                <input type="text" name="note" placeholder="e.g., Toy, Snack" required
                       style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px;">
            </div>
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="button" onclick="closeSpendModal()"
                        style="flex: 1; padding: 12px; border: none; border-radius: 8px; font-weight: 600; background: #e5e7eb; color: #4b5563; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit"
                        style="flex: 1; padding: 12px; border: none; border-radius: 8px; font-weight: 600; background: #ef4444; color: white; cursor: pointer;">
                    Record Spending
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openDepositModal() {
    document.getElementById('depositModal').style.display = 'flex';
}

function closeDepositModal() {
    document.getElementById('depositModal').style.display = 'none';
}

function openSpendModal() {
    document.getElementById('spendModal').style.display = 'flex';
}

function closeSpendModal() {
    document.getElementById('spendModal').style.display = 'none';
}

// Currency input handler for deposit modal
function handleDepositInput(event) {
    const input = event.target;
    let value = input.value.replace(/[^0-9]/g, '');

    if (value === '') {
        input.value = '';
        return;
    }

    const numValue = parseInt(value);
    const dollars = Math.floor(numValue / 100);
    const cents = numValue % 100;
    input.value = `${dollars}.${cents.toString().padStart(2, '0')}`;
}

// Currency input handler for spend modal
function handleSpendInput(event) {
    const input = event.target;
    let value = input.value.replace(/[^0-9]/g, '');

    if (value === '') {
        input.value = '';
        return;
    }

    const numValue = parseInt(value);
    const dollars = Math.floor(numValue / 100);
    const cents = numValue % 100;
    input.value = `${dollars}.${cents.toString().padStart(2, '0')}`;
}
</script>
@endsection
