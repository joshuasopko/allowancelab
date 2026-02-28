@extends('layouts.kid')

@section('title', 'My Wish List')

@section('content')
    <!-- Mobile Welcome Section -->
    <div class="mobile-kid-welcome">
        <h1 class="mobile-kid-welcome-title">
            Your wish list,<br><span class="kid-name-colored" style="color: {{ $kid->color }};">{{ $kid->name }}</span>.
        </h1>
        <p class="mobile-kid-welcome-subtitle">Dream big and save smart!</p>
    </div>

    <!-- Mobile Back Link -->
    <a href="{{ route('kid.dashboard') }}" class="mobile-back-link">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>

    <!-- Desktop Page Header -->
    <div class="desktop-page-header">
        <h1 class="desktop-page-title">Your Wish List</h1>
    </div>

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

    <!-- Tabs with Create Button -->
    <div class="kid-tabs">
        <div class="kid-tabs-left">
            <button class="kid-tab active" onclick="switchTab('current')">
                Current Wishes
                @if($currentWishes->count() > 0)
                    <span class="kid-tab-badge">{{ $currentWishes->count() }}</span>
                @endif
            </button>
            <button class="kid-tab" onclick="switchTab('redeemed')">
                Wishes Come True
                @if($redeemedWishes->total() > 0)
                    <span class="kid-tab-badge">{{ $redeemedWishes->total() }}</span>
                @endif
            </button>
        </div>
        <button onclick="openCreateWishModal()" class="btn-create-wish">
            <i class="fas fa-plus-circle"></i> Create New Wish
        </button>
    </div>

    <!-- Current Wishes Tab Content -->
    <div id="currentTab" class="kid-tab-content active">
        @if($currentWishes->isEmpty())
            <div class="kid-empty-state">
                <div class="kid-empty-icon">🎁</div>
                <h3>No wishes yet!</h3>
                <p>Create your first wish by clicking the button above.</p>
            </div>
        @else
            <div class="wishes-grid">
                @foreach($currentWishes as $wish)
                    <div class="wish-card" data-wish-id="{{ $wish->id }}">
                        <!-- Wish Image -->
                        <div class="wish-card-image-container">
                            @if($wish->image_path)
                                <img src="{{ \Storage::url($wish->image_path) }}"
                                     alt="{{ $wish->item_name }}"
                                     class="wish-card-image">
                            @else
                                <div class="wish-card-placeholder">
                                    <i class="fas fa-box-open"></i>
                                    <span>No image</span>
                                </div>
                            @endif
                        </div>

                        <!-- Wish Info -->
                        <div class="wish-card-content">
                            <h3 class="wish-card-title">{{ $wish->item_name }}</h3>
                            <div class="wish-card-price">${{ number_format($wish->price, 2) }}</div>

                            @if($wish->reason)
                                <p class="wish-card-reason">{{ Str::limit($wish->reason, 80) }}</p>
                            @endif

                            <!-- Status Badge -->
                            @if($wish->isPendingApproval())
                                <div class="wish-status-badge wish-status-pending">
                                    <i class="fas fa-clock"></i> Pending Response
                                </div>
                            @endif

                            <!-- Action Buttons -->
                            <div class="wish-card-actions">
                                @if($wish->isSaved())
                                    @if($wish->canBeRequested())
                                        <button onclick="showRequestConfirmation({{ $wish->id }})"
                                                class="btn-wish-action btn-wish-request"
                                                id="askBtn{{ $wish->id }}">
                                            <i class="fas fa-paper-plane"></i> Ask to Buy
                                        </button>
                                    @else
                                        <button class="btn-wish-action btn-wish-request"
                                                disabled
                                                title="You need ${{ number_format($wish->price, 2) }} in your account">
                                            <i class="fas fa-lock"></i> Need More Money
                                        </button>
                                    @endif
                                @elseif($wish->isPendingApproval())
                                    @if($wish->canRemindParent())
                                        <button onclick="remindParent({{ $wish->id }})"
                                                class="btn-wish-action btn-wish-remind">
                                            <i class="fas fa-bell"></i> Remind Parent
                                        </button>
                                    @else
                                        <button class="btn-wish-action btn-wish-remind" disabled>
                                            <i class="fas fa-clock"></i> Pending
                                        </button>
                                    @endif
                                @endif

                                <a href="{{ route('kid.wishes.show', $wish) }}"
                                   class="btn-wish-action btn-wish-view">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>

                            <!-- Inline Confirmation (Hidden by default) -->
                            <div class="wish-confirm-row" id="confirmRow{{ $wish->id }}" style="display: none;">
                                <p class="wish-confirm-text">Are you sure you want to ask your parent to buy this item?</p>
                                <button onclick="confirmRequest({{ $wish->id }})" class="btn-wish-confirm" id="confirmBtn{{ $wish->id }}">
                                    <i class="fas fa-check"></i> Confirm
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Redeemed Wishes Tab Content -->
    <div id="redeemedTab" class="kid-tab-content">
        @if($redeemedWishes->isEmpty())
            <div class="kid-empty-state">
                <div class="kid-empty-icon">✨</div>
                <h3>No wishes come true yet!</h3>
                <p>When your parent approves a purchase, it will appear here.</p>
            </div>
        @else
            <div class="wishes-grid">
                @foreach($redeemedWishes as $wish)
                    <div class="wish-card wish-card-purchased">
                        <!-- Wish Image -->
                        <div class="wish-card-image-container">
                            @if($wish->image_path)
                                <img src="{{ \Storage::url($wish->image_path) }}"
                                     alt="{{ $wish->item_name }}"
                                     class="wish-card-image">
                            @else
                                <div class="wish-card-placeholder">
                                    <i class="fas fa-box-open"></i>
                                    <span>No image</span>
                                </div>
                            @endif
                            <div class="wish-purchased-overlay">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>

                        <!-- Wish Info -->
                        <div class="wish-card-content">
                            <h3 class="wish-card-title">{{ $wish->item_name }}</h3>
                            <div class="wish-card-price">${{ number_format($wish->price, 2) }}</div>

                            <div class="wish-purchased-date">
                                <i class="fas fa-calendar-check"></i>
                                Purchased {{ $wish->purchased_at->diffForHumans() }}
                            </div>

                            <a href="{{ route('kid.wishes.show', $wish) }}"
                               class="btn-wish-action btn-wish-view">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($redeemedWishes->hasPages())
                <div class="kid-pagination">
                    {{ $redeemedWishes->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

<!-- Create Wish Modal -->
<div id="createWishModal" class="kid-modal" style="display: none;">
    <div class="kid-modal-content">
        <div class="kid-modal-header">
            <h2>Create New Wish</h2>
            <button onclick="closeCreateWishModal()" class="kid-modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="{{ route('kid.wishes.store') }}" method="POST" enctype="multipart/form-data" id="createWishForm">
            @csrf

            <div class="kid-modal-body">
                <!-- URL Input with Scrape Button -->
                <div class="kid-form-group">
                    <label for="wish_url" class="kid-label">Item URL (optional)</label>
                    <div class="kid-input-with-button">
                        <input type="url"
                               id="wish_url"
                               name="item_url"
                               class="kid-input"
                               placeholder="https://www.amazon.com/...">
                        <button type="button" onclick="scrapeWishUrl()" class="btn-scrape" id="scrapeBtn">
                            <i class="fas fa-magic"></i> Auto-fill
                        </button>
                    </div>
                    <p class="kid-input-hint">Paste a link and we'll try to auto-fill the details! Works best with Target, Walmart, and most online stores.</p>
                    <div id="scrapeError" class="kid-input-error" style="display: none;"></div>
                    <div id="scrapePartialSuccess" class="kid-input-hint" style="display: none; color: #f59e0b; margin-top: 8px;">
                        <i class="fas fa-info-circle"></i> <span id="scrapePartialMessage"></span>
                    </div>
                </div>

                <!-- Item Name -->
                <div class="kid-form-group">
                    <label for="item_name" class="kid-label">What do you want? *</label>
                    <input type="text"
                           id="item_name"
                           name="item_name"
                           class="kid-input"
                           placeholder="e.g., New Bike"
                           required>
                </div>

                <!-- Price -->
                <div class="kid-form-group">
                    <label for="price" class="kid-label">How much does it cost? *</label>
                    <div class="kid-input-prefix">
                        <span class="kid-input-prefix-text">$</span>
                        <input type="number"
                               id="price"
                               name="price"
                               class="kid-input kid-input-with-prefix"
                               placeholder="0.00"
                               step="0.01"
                               min="0"
                               max="99999.99"
                               required>
                    </div>
                </div>

                <!-- Image Preview & Upload -->
                <div class="kid-form-group">
                    <label class="kid-label">Image</label>
                    <div id="imagePreviewContainer" style="display: none;">
                        <img id="imagePreview" src="" alt="Preview" class="wish-image-preview">
                        <button type="button" onclick="removeImagePreview()" class="btn-remove-image">
                            <i class="fas fa-times"></i> Remove
                        </button>
                    </div>
                    <input type="hidden" id="scraped_image_url" name="scraped_image_url">
                    <input type="file"
                           id="image"
                           name="image"
                           class="kid-input-file"
                           accept="image/*"
                           onchange="previewUploadedImage(this)">
                    <label for="image" class="kid-file-label">
                        <i class="fas fa-cloud-upload-alt"></i> Upload Image
                    </label>
                </div>

                <!-- Reason -->
                <div class="kid-form-group">
                    <label for="reason" class="kid-label">Why do you want this? (optional)</label>
                    <textarea id="reason"
                              name="reason"
                              class="kid-textarea"
                              rows="3"
                              placeholder="Tell your parent why you want this..."></textarea>
                </div>

                <!-- Current Balance Info -->
                <div class="kid-balance-display">
                    <i class="fas fa-wallet"></i>
                    Your balance: <strong>${{ number_format($kid->balance, 2) }}</strong>
                </div>
            </div>

            <div class="kid-modal-footer">
                <button type="button" onclick="closeCreateWishModal()" class="btn-modal-cancel">
                    Cancel
                </button>
                <button type="submit" name="action" value="save" class="btn-modal-save">
                    <i class="fas fa-heart"></i> Save to Wish List
                </button>
                <button type="submit" name="action" value="request" class="btn-modal-request">
                    <i class="fas fa-paper-plane"></i> Ask Parent to Buy
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Mobile Back Link (Bottom) -->
<a href="{{ route('kid.dashboard') }}" class="mobile-back-link mobile-back-link-bottom">
    <i class="fas fa-arrow-left"></i> Back to Dashboard
</a>

<style>
/* Wish List Styles */
/* Override layout wrapper to allow full width */
.kid-content-wrapper {
    max-width: none !important;
    padding: 0 !important;
}

/* Mobile Welcome Section */
.mobile-kid-welcome {
    display: none; /* Hidden on desktop */
}

@media (max-width: 768px) {
    .mobile-kid-welcome {
        display: block;
        padding: 0 16px;
        margin-bottom: 16px;
    }
}

.mobile-kid-welcome-title {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    line-height: 1.3;
}

.mobile-kid-welcome-subtitle {
    font-size: 15px;
    color: #6b7280;
    margin-top: 4px;
    line-height: 1.4;
}

/* Desktop Page Header */
.desktop-page-header {
    display: none; /* Hidden on mobile */
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

@media (min-width: 769px) {
    .desktop-page-header {
        display: flex;
    }
}

.desktop-page-title {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.btn-create-wish {
    background: {{ $kid->color }};
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-create-wish:hover {
    background: color-mix(in srgb, {{ $kid->color }} 85%, black);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

/* Tabs */
.kid-tabs {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    border-bottom: 2px solid #e5e7eb;
    margin-bottom: 24px;
    padding-bottom: 0;
}

.kid-tabs-left {
    display: flex;
    gap: 8px;
}

.kid-tab {
    background: none;
    border: none;
    padding: 12px 20px;
    font-size: 16px;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    position: relative;
    transition: color 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.kid-tab:hover {
    color: {{ $kid->color }};
}

.kid-tab.active {
    color: {{ $kid->color }};
}

.kid-tab.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 2px;
    background: {{ $kid->color }};
}

.kid-tab-badge {
    background: {{ $kid->color }};
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.kid-tab-content {
    display: none;
}

.kid-tab-content.active {
    display: block;
}

/* Wishes Grid */
.wishes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

@media (max-width: 768px) {
    .wishes-grid {
        grid-template-columns: 1fr;
    }
}

.wish-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(59, 130, 246, 0.15);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.wish-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.25);
}

.wish-card-image-container {
    position: relative;
    width: 100%;
    height: 200px;
    background: #f3f4f6;
    overflow: hidden;
}

.wish-card-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.wish-card-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #9ca3af;
}

.wish-card-placeholder i {
    font-size: 48px;
    margin-bottom: 8px;
}

.wish-purchased-overlay {
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
    font-size: 64px;
}

.wish-card-content {
    padding: 16px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.wish-card-title {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 8px 0;
}

.wish-card-price {
    font-size: 24px;
    font-weight: 700;
    color: {{ $kid->color }};
    margin-bottom: 12px;
}

.wish-card-reason {
    font-size: 14px;
    color: #6b7280;
    margin: 0 0 12px 0;
    line-height: 1.5;
}

.wish-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 12px;
}

.wish-status-pending {
    background: #fef3c7;
    color: #92400e;
}

.wish-purchased-date {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #10b981;
    font-weight: 600;
    margin-bottom: 12px;
}

.wish-card-actions {
    display: flex;
    gap: 8px;
    margin-top: auto;
}

.btn-wish-action {
    flex: 1;
    padding: 10px 16px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.2s;
    text-decoration: none;
}

.btn-wish-request {
    background: {{ $kid->color }};
    color: white;
}

.btn-wish-request:hover:not(:disabled) {
    background: color-mix(in srgb, {{ $kid->color }} 85%, black);
}

.btn-wish-request:disabled {
    background: #cbd5e1;
    cursor: not-allowed;
}

.btn-wish-remind {
    background: #f59e0b;
    color: white;
}

.btn-wish-remind:hover:not(:disabled) {
    background: #d97706;
}

.btn-wish-remind:disabled {
    background: #cbd5e1;
    cursor: not-allowed;
}

.btn-wish-view {
    background: #e5e7eb;
    color: #374151;
}

.btn-wish-view:hover {
    background: #d1d5db;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .kid-page-header {
        flex-direction: column;
        align-items: stretch;
        gap: 16px;
    }

    .btn-create-wish {
        width: 100%;
        justify-content: center;
    }

    .wishes-grid {
        grid-template-columns: 1fr;
    }
}

/* Inline Confirmation Styles with Kid's Color */
.wish-confirm-row {
    background: color-mix(in srgb, {{ $kid->color }} 10%, white);
    border: 2px solid {{ $kid->color }};
}

.wish-confirm-text {
    color: color-mix(in srgb, {{ $kid->color }} 80%, black);
}

.btn-wish-confirm {
    background: {{ $kid->color }};
}

.btn-wish-confirm:hover:not(:disabled) {
    background: color-mix(in srgb, {{ $kid->color }} 85%, black);
    box-shadow: 0 4px 12px color-mix(in srgb, {{ $kid->color }} 30%, transparent);
}
</style>

<script>
// Tab switching
function switchTab(tab) {
    // Update tab buttons
    document.querySelectorAll('.kid-tab').forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');

    // Update tab content
    document.querySelectorAll('.kid-tab-content').forEach(c => c.classList.remove('active'));
    document.getElementById(tab + 'Tab').classList.add('active');
}

// Modal functions
function openCreateWishModal() {
    document.getElementById('createWishModal').style.display = 'flex';
}

function closeCreateWishModal() {
    document.getElementById('createWishModal').style.display = 'none';
    document.getElementById('createWishForm').reset();
    removeImagePreview();
    // Clear error and partial success messages
    document.getElementById('scrapeError').style.display = 'none';
    document.getElementById('scrapeError').textContent = '';
    const partialDiv = document.getElementById('scrapePartialSuccess');
    if (partialDiv) {
        partialDiv.style.display = 'none';
    }
}

// URL scraping
async function scrapeWishUrl() {
    const urlInput = document.getElementById('wish_url');
    const url = urlInput.value.trim();

    if (!url) {
        alert('Please enter a URL first');
        return;
    }

    const scrapeBtn = document.getElementById('scrapeBtn');
    const errorDiv = document.getElementById('scrapeError');

    scrapeBtn.disabled = true;
    scrapeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    errorDiv.style.display = 'none';

    try {
        const response = await fetch('{{ route("kid.wishes.scrape-url") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ url })
        });

        const result = await response.json();

        if (result.success) {
            // Auto-fill form fields
            let fieldsFound = [];
            let fieldsMissing = [];

            if (result.data.title) {
                document.getElementById('item_name').value = result.data.title;
                fieldsFound.push('title');
            } else {
                fieldsMissing.push('title');
            }

            if (result.data.price) {
                document.getElementById('price').value = result.data.price;
                fieldsFound.push('price');
            } else {
                fieldsMissing.push('price');
            }

            if (result.data.image_url) {
                document.getElementById('scraped_image_url').value = result.data.image_url;
                document.getElementById('imagePreview').src = result.data.image_url;
                document.getElementById('imagePreviewContainer').style.display = 'block';
                fieldsFound.push('image');
            } else {
                fieldsMissing.push('image');
            }

            // Show partial success message if some fields are missing
            if (fieldsMissing.length > 0 && fieldsFound.length > 0) {
                const partialDiv = document.getElementById('scrapePartialSuccess');
                const partialMsg = document.getElementById('scrapePartialMessage');
                partialMsg.textContent = `Found ${fieldsFound.join(', ')}. Please fill in the remaining details.`;
                partialDiv.style.display = 'block';
            }
        } else {
            errorDiv.textContent = result.error || 'Failed to scrape URL. Please fill in details manually.';
            errorDiv.style.display = 'block';
        }
    } catch (error) {
        console.error('Scrape error:', error);
        errorDiv.textContent = 'An error occurred. Please fill in details manually.';
        errorDiv.style.display = 'block';
    } finally {
        scrapeBtn.disabled = false;
        scrapeBtn.innerHTML = '<i class="fas fa-magic"></i> Auto-fill';
    }
}

// Image preview
function previewUploadedImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('imagePreviewContainer').style.display = 'block';
            document.getElementById('scraped_image_url').value = ''; // Clear scraped URL
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImagePreview() {
    document.getElementById('imagePreviewContainer').style.display = 'none';
    document.getElementById('imagePreview').src = '';
    document.getElementById('scraped_image_url').value = '';
    document.getElementById('image').value = '';
}

// Show inline confirmation
function showRequestConfirmation(wishId) {
    const confirmRow = document.getElementById(`confirmRow${wishId}`);
    const askBtn = document.getElementById(`askBtn${wishId}`);

    // Hide the Ask button
    askBtn.style.display = 'none';

    // Show confirmation row with slide-down animation
    confirmRow.style.display = 'block';
    setTimeout(() => {
        confirmRow.classList.add('active');
    }, 10);
}

// Confirm request purchase
async function confirmRequest(wishId) {
    const confirmBtn = document.getElementById(`confirmBtn${wishId}`);
    const originalContent = confirmBtn.innerHTML;

    // Show loading state
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

    try {
        const response = await fetch(`/kid/wishes/${wishId}/request-purchase`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const result = await response.json();

        if (result.success) {
            // Show success state
            confirmBtn.innerHTML = '<i class="fas fa-check"></i> Request Sent!';
            confirmBtn.classList.add('success');

            // Reload after a short delay
            setTimeout(() => {
                location.reload();
            }, 1200);
        } else {
            // Show error
            confirmBtn.innerHTML = '<i class="fas fa-times"></i> Failed';
            confirmBtn.classList.add('error');

            // Reset after delay
            setTimeout(() => {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalContent;
                confirmBtn.classList.remove('error');
            }, 2000);
        }
    } catch (error) {
        console.error('Request error:', error);
        confirmBtn.innerHTML = '<i class="fas fa-times"></i> Error';
        confirmBtn.classList.add('error');

        setTimeout(() => {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalContent;
            confirmBtn.classList.remove('error');
        }, 2000);
    }
}

// Remind parent
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
