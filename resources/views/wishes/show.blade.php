@extends('layouts.kid')

@section('title', $wish->item_name)

@section('content')
<div class="kid-content-wrapper">
    <!-- Back Button -->
    <a href="{{ route('kid.dashboard') }}?tab=wishes" class="btn-back">
        <i class="fas fa-arrow-left"></i> Back to Wish List
    </a>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="kid-alert kid-alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="kid-alert kid-alert-error">
            <i class="fas fa-exclamation-circle"></i>
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Wish Detail Card -->
    <div class="wish-detail-card">
        <!-- Wish Image -->
        <div class="wish-detail-image-container">
            @if($wish->image_path)
                <img src="{{ asset('storage/' . $wish->image_path) }}"
                     alt="{{ $wish->item_name }}"
                     class="wish-detail-image">
            @else
                <div class="wish-detail-placeholder">
                    <i class="fas fa-box-open"></i>
                    <span>No image available</span>
                </div>
            @endif
        </div>

        <!-- Wish Info -->
        <div class="wish-detail-content">
            <h1 class="wish-detail-title">{{ $wish->item_name }}</h1>
            <div class="wish-detail-price">${{ number_format($wish->price, 2) }}</div>

            <!-- Status Badge -->
            @if($wish->isPendingApproval())
                <div class="wish-status-badge wish-status-pending">
                    <i class="fas fa-clock"></i> Waiting for Parent Response
                </div>
            @elseif($wish->isPurchased())
                <div class="wish-status-badge wish-status-purchased">
                    <i class="fas fa-check-circle"></i> Purchased {{ $wish->purchased_at->diffForHumans() }}
                </div>
            @endif

            <!-- URL Link -->
            @if($wish->item_url)
                <div class="wish-detail-url">
                    <a href="{{ $wish->item_url }}" target="_blank" rel="noopener noreferrer">
                        <i class="fas fa-external-link-alt"></i> View Item Online
                    </a>
                </div>
            @endif

            <!-- Reason -->
            @if($wish->reason)
                <div class="wish-detail-section">
                    <h3>Why I Want This:</h3>
                    <p>{{ $wish->reason }}</p>
                </div>
            @endif

            <!-- Action Buttons -->
            @if($wish->canBeEdited())
                @if($wish->isSaved() && !$wish->canBeRequested())
                    <div class="wish-goal-nudge-banner">
                        <div class="wish-goal-nudge-icon"><i class="fas fa-piggy-bank"></i></div>
                        <div class="wish-goal-nudge-text">
                            <strong>This is a big purchase!</strong>
                            <span>You need <strong>${{ number_format($wish->price - $kid->balance, 2) }}</strong> more. Try creating a savings goal to work toward it!</span>
                        </div>
                    </div>
                @endif
                <div class="wish-detail-actions">
                    @if($wish->isSaved())
                        @if($wish->canBeRequested())
                            <button onclick="openRequestModal()" class="btn-wish-primary">
                                <i class="fas fa-paper-plane"></i> Ask Parent to Buy
                            </button>
                        @else
                            <button class="btn-wish-primary" disabled title="You need ${{ number_format($wish->price, 2) }} in your account">
                                <i class="fas fa-lock"></i> Need More Money
                            </button>
                            <a href="{{ route('kid.dashboard') }}?tab=goals&prefill_title={{ urlencode($wish->item_name) }}&prefill_amount={{ $wish->price }}"
                               class="btn-wish-save-goal">
                                <i class="fas fa-piggy-bank"></i> Save for This with a Goal
                            </a>
                        @endif
                    @elseif($wish->isPendingApproval() && $wish->canRemindParent())
                        <button onclick="remindParent({{ $wish->id }})" class="btn-wish-primary">
                            <i class="fas fa-bell"></i> Remind Parent
                        </button>
                    @endif

                    <button onclick="openEditModal()" class="btn-wish-secondary">
                        <i class="fas fa-edit"></i> Edit Wish
                    </button>

                    <button type="button" onclick="openDeleteModal()" class="btn-wish-danger">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Edit Modal (simplified for now) -->
<div id="editWishModal" class="kid-modal" style="display: none;">
    <div class="kid-modal-content">
        <div class="kid-modal-header">
            <h2>Edit Wish</h2>
            <button onclick="closeEditModal()" class="kid-modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="{{ route('kid.wishes.update', $wish) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="kid-modal-body">
                <div class="kid-form-group">
                    <label class="kid-label">What do you want? *</label>
                    <input type="text" name="item_name" class="kid-input" value="{{ $wish->item_name }}" required>
                </div>

                <div class="kid-form-group">
                    <label class="kid-label">Item URL (optional)</label>
                    <input type="url" name="item_url" class="kid-input" value="{{ $wish->item_url }}">
                </div>

                <div class="kid-form-group">
                    <label class="kid-label">How much does it cost? *</label>
                    <div class="kid-input-prefix">
                        <span class="kid-input-prefix-text">$</span>
                        <input type="number" name="price" class="kid-input kid-input-with-prefix"
                               value="{{ $wish->price }}" step="0.01" min="0" required>
                    </div>
                </div>

                <div class="kid-form-group">
                    <label class="kid-label">Why do you want this? (optional)</label>
                    <textarea name="reason" class="kid-textarea" rows="3">{{ $wish->reason }}</textarea>
                </div>

                <div class="kid-form-group">
                    <label class="kid-label">Update Image (optional)</label>
                    <input type="file" name="image" class="kid-input-file" accept="image/*">
                    <label for="image" class="kid-file-label">
                        <i class="fas fa-cloud-upload-alt"></i> Upload New Image
                    </label>
                </div>
            </div>

            <div class="kid-modal-footer">
                <button type="button" onclick="closeEditModal()" class="btn-modal-cancel">Cancel</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteWishModal" style="display: none; position: fixed; inset: 0; z-index: 1000; align-items: center; justify-content: center;">
    <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.5);" onclick="closeDeleteModal()"></div>
    <div style="position: relative; background: white; border-radius: 16px; padding: 32px; max-width: 380px; width: 90%; margin: 0 16px; text-align: center; z-index: 1;">
        <div style="font-size: 48px; margin-bottom: 16px;">🗑️</div>
        <h2 style="font-size: 20px; font-weight: 700; color: #1f2937; margin: 0 0 8px 0;">Delete this wish?</h2>
        <p style="color: #6b7280; margin: 0 0 24px 0;">This can't be undone. "{{ $wish->item_name }}" will be removed from your wish list.</p>
        <div style="display: flex; gap: 12px; justify-content: center;">
            <button onclick="closeDeleteModal()" style="padding: 10px 24px; background: #e5e7eb; color: #374151; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;">
                Cancel
            </button>
            <form action="{{ route('kid.wishes.destroy', $wish) }}" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" style="padding: 10px 24px; background: #ef4444; color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-trash"></i> Yes, Delete
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Request Purchase Confirmation Modal -->
<div id="requestPurchaseModal" style="display: none; position: fixed; inset: 0; z-index: 1000; align-items: center; justify-content: center;">
    <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.5);" onclick="closeRequestModal()"></div>
    <div style="position: relative; background: white; border-radius: 16px; padding: 32px; max-width: 400px; width: 90%; margin: 0 16px; z-index: 1;">
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="font-size: 48px; margin-bottom: 8px;">🛒</div>
            <h2 style="font-size: 20px; font-weight: 700; color: #1f2937; margin: 0 0 4px 0;">Ask Parent to Buy?</h2>
            <p style="color: #6b7280; font-size: 14px; margin: 0;">{{ $wish->item_name }}</p>
        </div>

        <div style="background: #f9fafb; border-radius: 12px; padding: 16px; margin-bottom: 16px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span style="color: #6b7280; font-size: 14px;">Item Price</span>
                <span style="font-weight: 700; color: #1f2937;">${{ number_format($wish->price, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span style="color: #6b7280; font-size: 14px;">Your Balance</span>
                <span style="font-weight: 700; color: #1f2937;">${{ number_format($kid->balance, 2) }}</span>
            </div>
            <div style="border-top: 1px solid #e5e7eb; padding-top: 10px; display: flex; justify-content: space-between;">
                <span style="color: #6b7280; font-size: 14px;">Balance After Purchase</span>
                @php $afterBalance = $kid->balance - $wish->price; @endphp
                <span style="font-weight: 700; color: {{ $afterBalance >= 0 ? '#10b981' : '#ef4444' }};">
                    ${{ number_format($afterBalance, 2) }}
                </span>
            </div>
        </div>

        <div style="background: #fefce8; border: 1px solid #fde047; border-radius: 8px; padding: 12px; margin-bottom: 20px; font-size: 12px; color: #854d0e; line-height: 1.5;">
            <i class="fas fa-info-circle"></i> <strong>Heads up:</strong> Your parent may add taxes or shipping fees, so the final amount deducted may differ from the price shown above.
        </div>

        <div style="display: flex; gap: 12px;">
            <button onclick="closeRequestModal()" style="flex: 1; padding: 12px; background: #e5e7eb; color: #374151; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;">
                Cancel
            </button>
            <button onclick="confirmRequestPurchase({{ $wish->id }})" style="flex: 1; padding: 12px; background: #3b82f6; color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;">
                <i class="fas fa-paper-plane"></i> Yes, Ask Parent!
            </button>
        </div>
    </div>
</div>

<style>
.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 0;
    background: transparent;
    color: #6b7280;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
    transition: color 0.2s;
    margin-bottom: 20px;
}
.btn-back:hover {
    color: #374151;
    text-decoration: none;
}

.wish-detail-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(59, 130, 246, 0.2);
    margin-top: 24px;
}

.wish-detail-image-container {
    width: 100%;
    max-height: 400px;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
}

.wish-detail-image {
    width: 100%;
    height: auto;
    max-height: 400px;
    object-fit: contain;
}

.wish-detail-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 300px;
    color: #9ca3af;
}

.wish-detail-placeholder i {
    font-size: 64px;
    margin-bottom: 12px;
}

.wish-detail-content {
    padding: 32px;
}

.wish-detail-title {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 16px 0;
}

.wish-detail-price {
    font-size: 36px;
    font-weight: 700;
    color: #3b82f6;
    margin-bottom: 16px;
}

.wish-detail-url {
    margin: 16px 0;
}

.wish-detail-url a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #3b82f6;
    text-decoration: none;
    font-weight: 600;
}

.wish-detail-url a:hover {
    text-decoration: underline;
}

.wish-detail-section {
    margin: 24px 0;
}

.wish-detail-section h3 {
    font-size: 18px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
}

.wish-detail-section p {
    color: #6b7280;
    line-height: 1.6;
}

.wish-detail-actions {
    display: flex;
    gap: 12px;
    margin-top: 32px;
    flex-wrap: wrap;
}

.btn-wish-primary,
.btn-wish-secondary,
.btn-wish-danger {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-wish-primary {
    background: #3b82f6;
    color: white;
}

.btn-wish-primary:hover:not(:disabled) {
    background: #2563eb;
}

.btn-wish-primary:disabled {
    background: #cbd5e1;
    cursor: not-allowed;
}

.btn-wish-secondary {
    background: #e5e7eb;
    color: #374151;
}

.btn-wish-secondary:hover {
    background: #d1d5db;
}

.btn-wish-danger {
    background: #ef4444;
    color: white;
}

.btn-wish-danger:hover {
    background: #dc2626;
}

.wish-goal-nudge-banner {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    background: linear-gradient(135deg, #f5f3ff, #ede9fe);
    border: 1px solid #c4b5fd;
    border-radius: 12px;
    padding: 16px 20px;
    margin: 24px 0 8px 0;
}
.wish-goal-nudge-icon {
    font-size: 28px;
    color: #7c3aed;
    flex-shrink: 0;
    line-height: 1;
}
.wish-goal-nudge-text {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 14px;
    color: #4c1d95;
    line-height: 1.5;
}
.wish-goal-nudge-text strong { font-size: 15px; }

.btn-wish-save-goal {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
    background: linear-gradient(135deg, #8b5cf6, #6d28d9);
    color: white;
    text-decoration: none;
}
.btn-wish-save-goal:hover {
    opacity: 0.9;
    color: white;
    text-decoration: none;
}

.wish-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 16px;
}

.wish-status-pending {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.wish-status-purchased {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #6ee7b7;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
</style>

<script>
function openEditModal() {
    document.getElementById('editWishModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editWishModal').style.display = 'none';
}

function openDeleteModal() {
    const modal = document.getElementById('deleteWishModal');
    modal.style.display = 'flex';
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteWishModal');
    modal.style.display = 'none';
}

function openRequestModal() {
    document.getElementById('requestPurchaseModal').style.display = 'flex';
}

function closeRequestModal() {
    document.getElementById('requestPurchaseModal').style.display = 'none';
}

async function confirmRequestPurchase(wishId) {
    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.textContent = 'Sending...';

    try {
        const response = await fetch(`/kid/wishes/${wishId}/request-purchase`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            closeRequestModal();
            location.reload();
        } else {
            alert(result.message || 'Failed to send request');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Yes, Ask Parent!';
        }
    } catch (error) {
        console.error('Request error:', error);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Yes, Ask Parent!';
    }
}

async function remindParent(wishId) {
    try {
        const response = await fetch(`/kid/wishes/${wishId}/remind`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            location.reload();
        } else {
            alert(result.message || 'Cannot send reminder yet');
        }
    } catch (error) {
        console.error('Reminder error:', error);
    }
}
</script>
@endsection
