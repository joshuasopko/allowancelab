@extends('layouts.kid')

@section('title', $wish->item_name)

@section('content')
<div class="kid-content-wrapper">
    <!-- Back Button -->
    <a href="{{ route('kid.wishes.index') }}" class="kid-back-link">
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
                <div class="wish-detail-actions">
                    @if($wish->isSaved())
                        @if($wish->canBeRequested())
                            <button onclick="requestPurchase({{ $wish->id }})" class="btn-wish-primary">
                                <i class="fas fa-paper-plane"></i> Ask Parent to Buy
                            </button>
                        @else
                            <button class="btn-wish-primary" disabled title="You need ${{ number_format($wish->price, 2) }} in your account">
                                <i class="fas fa-lock"></i> Need More Money
                            </button>
                        @endif
                    @elseif($wish->isPendingApproval() && $wish->canRemindParent())
                        <button onclick="remindParent({{ $wish->id }})" class="btn-wish-primary">
                            <i class="fas fa-bell"></i> Remind Parent
                        </button>
                    @endif

                    <button onclick="openEditModal()" class="btn-wish-secondary">
                        <i class="fas fa-edit"></i> Edit Wish
                    </button>

                    <form action="{{ route('kid.wishes.destroy', $wish) }}" method="POST" style="display: inline;"
                          onsubmit="return confirm('Are you sure you want to delete this wish?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-wish-danger">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
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

<style>
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
</style>

<script>
function openEditModal() {
    document.getElementById('editWishModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editWishModal').style.display = 'none';
}

async function requestPurchase(wishId) {
    if (!confirm('Ask your parent to buy this item?')) {
        return;
    }

    try {
        const response = await fetch(`/kid/wishes/${wishId}/request-purchase`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message || 'Failed to send request');
        }
    } catch (error) {
        console.error('Request error:', error);
        alert('An error occurred. Please try again.');
    }
}

async function remindParent(wishId) {
    try {
        const response = await fetch(`/kid/wishes/${wishId}/remind`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message || 'Cannot send reminder yet');
        }
    } catch (error) {
        console.error('Reminder error:', error);
        alert('An error occurred. Please try again.');
    }
}
</script>
@endsection
