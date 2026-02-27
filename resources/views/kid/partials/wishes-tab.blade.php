{{-- Wishes Tab - Card grid version for kid dashboard --}}
<div x-data="{ wishesActiveTab: 'current' }">

    {{-- Tab Header --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #e5e7eb; padding-bottom: 0;">
        <div style="display: flex; gap: 4px;">
            <button @click="wishesActiveTab = 'current'"
                    :class="wishesActiveTab === 'current' ? 'kid-inner-tab-active' : ''"
                    class="kid-inner-tab">
                Current Wishes
                @if($currentWishes->count() > 0)
                    <span class="kid-inner-tab-count" :style="wishesActiveTab === 'current' ? 'background: {{ $kid->color }}; color: white;' : ''">{{ $currentWishes->count() }}</span>
                @endif
            </button>
            <button @click="wishesActiveTab = 'redeemed'"
                    :class="wishesActiveTab === 'redeemed' ? 'kid-inner-tab-active' : ''"
                    class="kid-inner-tab">
                Wishes Come True
                @if($redeemedWishes->count() > 0)
                    <span class="kid-inner-tab-count" :style="wishesActiveTab === 'redeemed' ? 'background: {{ $kid->color }}; color: white;' : ''">{{ $redeemedWishes->count() }}</span>
                @endif
            </button>
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            <button onclick="kidOpenCreateWishModal()" class="kid-tab-action-btn" style="background: {{ $kid->color }};">
                <i class="fas fa-plus"></i> New Wish
            </button>
        </div>
    </div>

    {{-- Current Wishes --}}
    <div x-show="wishesActiveTab === 'current'" x-cloak>
        @if($currentWishes->isEmpty())
            <div class="kid-empty-state">
                <div style="font-size: 48px; margin-bottom: 12px;">🎁</div>
                <p>No wishes yet! Add something you're saving for.</p>
                <button class="kid-btn-create" onclick="kidOpenCreateWishModal()">Add Your First Wish</button>
            </div>
        @else
            <div class="kid-wishes-grid">
                @foreach($currentWishes as $wish)
                    <div class="kid-wish-card {{ $wish->isDeclined() ? 'kid-wish-card-declined' : '' }}" data-wish-id="{{ $wish->id }}" data-wish-name="{{ $wish->item_name }}" data-wish-price="{{ $wish->price }}">
                        {{-- Image --}}
                        <div class="kid-wish-card-img">
                            @if($wish->image_path)
                                <img src="{{ asset('storage/' . $wish->image_path) }}" alt="{{ $wish->item_name }}">
                            @else
                                <div class="kid-wish-card-placeholder">
                                    <i class="fas fa-box-open"></i>
                                    <span>No image</span>
                                </div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="kid-wish-card-body">
                            <div class="kid-wish-card-title">{{ $wish->item_name }}</div>
                            <div class="kid-wish-card-price" style="color: {{ $kid->color }};">${{ number_format($wish->price, 2) }}</div>

                            @if($wish->reason)
                                <div class="kid-wish-card-reason">{{ Str::limit($wish->reason, 80) }}</div>
                            @endif

                            @if($wish->isPendingApproval())
                                <div class="kid-wish-status-badge pending">
                                    <i class="fas fa-clock"></i> Pending Response
                                </div>
                            @elseif($wish->isDeclined())
                                @php
                                    $hoursLeft = $wish->hoursUntilReAsk();
                                    $canReAsk = $wish->canReAsk();
                                    $declinedAtMs = $wish->declined_at ? $wish->declined_at->timestamp * 1000 : null;
                                @endphp
                                <div class="kid-wish-status-badge declined">
                                    <i class="fas fa-times-circle"></i> Declined
                                </div>
                                @if(!$canReAsk && $declinedAtMs)
                                    <div class="kid-wish-reask-countdown" data-ready-at="{{ $wish->declined_at->timestamp + 86400 }}">
                                        <i class="fas fa-clock"></i>
                                        <span class="kid-wish-countdown-text">Ask again in {{ ceil($hoursLeft) }}h</span>
                                    </div>
                                @elseif($canReAsk)
                                    <div class="kid-wish-reask-ready">
                                        <i class="fas fa-paper-plane"></i> Ready to re-ask!
                                    </div>
                                @endif
                            @endif

                            {{-- Actions --}}
                            <div class="kid-wish-card-actions">
                                @if($wish->isSaved())
                                    @if($wish->canBeRequested())
                                        <button onclick="kidWishAskToBuy({{ $wish->id }}, '{{ addslashes($wish->item_name) }}', {{ $wish->price }})"
                                                class="kid-wish-btn kid-wish-btn-primary" style="background: {{ $kid->color }};"
                                                id="kidAskBtn{{ $wish->id }}">
                                            <i class="fas fa-paper-plane"></i> Ask to Buy
                                        </button>
                                    @else
                                        <div class="kid-wish-goal-nudge">
                                            <button class="kid-wish-btn kid-wish-btn-disabled" disabled style="width: 100%;">
                                                <i class="fas fa-lock"></i> Need More Money
                                            </button>
                                            <a href="{{ route('kid.dashboard') }}?tab=goals&prefill_title={{ urlencode($wish->item_name) }}&prefill_amount={{ $wish->price }}"
                                               class="kid-wish-btn kid-wish-btn-goal" style="width: 100%; justify-content: center;">
                                                <i class="fas fa-piggy-bank"></i> Save for This
                                            </a>
                                        </div>
                                    @endif
                                @elseif($wish->isPendingApproval())
                                    @if($wish->canRemindParent())
                                        <button onclick="kidWishRemind({{ $wish->id }})"
                                                class="kid-wish-btn kid-wish-btn-remind"
                                                id="kidRemindBtn{{ $wish->id }}">
                                            <i class="fas fa-bell"></i> Remind Parent
                                        </button>
                                    @else
                                        <button class="kid-wish-btn kid-wish-btn-disabled" disabled>
                                            <i class="fas fa-clock"></i> Pending
                                        </button>
                                    @endif
                                @endif
                                <a href="{{ route('kid.wishes.show', $wish) }}"
                                   class="kid-wish-btn kid-wish-btn-view">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Redeemed Wishes --}}
    <div x-show="wishesActiveTab === 'redeemed'" x-cloak>
        @if($redeemedWishes->isEmpty())
            <div class="kid-empty-state">
                <div style="font-size: 48px; margin-bottom: 12px;">✨</div>
                <p>No wishes come true yet. Keep saving!</p>
            </div>
        @else
            <div class="kid-wishes-grid">
                @foreach($redeemedWishes as $wish)
                    <div class="kid-wish-card kid-wish-card-purchased">
                        {{-- Image --}}
                        <div class="kid-wish-card-img">
                            @if($wish->image_path)
                                <img src="{{ asset('storage/' . $wish->image_path) }}" alt="{{ $wish->item_name }}">
                            @else
                                <div class="kid-wish-card-placeholder">
                                    <i class="fas fa-box-open"></i>
                                    <span>No image</span>
                                </div>
                            @endif
                            <div class="kid-wish-purchased-overlay">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="kid-wish-card-body">
                            <div class="kid-wish-card-title">{{ $wish->item_name }}</div>
                            <div class="kid-wish-card-price" style="color: #10b981;">${{ number_format($wish->price, 2) }}</div>
                            <div class="kid-wish-purchased-date">
                                <i class="fas fa-calendar-check"></i>
                                Purchased {{ $wish->purchased_at ? $wish->purchased_at->diffForHumans() : '' }}
                            </div>
                            <div class="kid-wish-card-actions">
                                <a href="{{ route('kid.wishes.show', $wish) }}"
                                   class="kid-wish-btn kid-wish-btn-view">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

{{-- Ask to Buy Confirmation Modal --}}
<div id="kidRequestPurchaseModal" style="display: none; position: fixed; inset: 0; z-index: 9999; align-items: center; justify-content: center;">
    <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.5);" onclick="kidCloseRequestModal()"></div>
    <div style="position: relative; background: white; border-radius: 16px; padding: 32px; max-width: 400px; width: 90%; margin: 0 16px; z-index: 1;">
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="font-size: 48px; margin-bottom: 8px;">🛒</div>
            <h2 style="font-size: 20px; font-weight: 700; color: #1f2937; margin: 0 0 4px 0;">Ask Parent to Buy?</h2>
            <p id="kidRequestWishName" style="color: #6b7280; font-size: 14px; margin: 0;"></p>
        </div>

        <div style="background: #f9fafb; border-radius: 12px; padding: 16px; margin-bottom: 16px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span style="color: #6b7280; font-size: 14px;">Item Price</span>
                <span id="kidRequestWishPrice" style="font-weight: 700; color: #1f2937;"></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span style="color: #6b7280; font-size: 14px;">Your Balance</span>
                <span id="kidRequestCurrentBalance" style="font-weight: 700; color: #1f2937;"></span>
            </div>
            <div style="border-top: 1px solid #e5e7eb; padding-top: 10px; display: flex; justify-content: space-between;">
                <span style="color: #6b7280; font-size: 14px;">Balance After Purchase</span>
                <span id="kidRequestAfterBalance" style="font-weight: 700;"></span>
            </div>
        </div>

        <div style="display: flex; gap: 12px;">
            <button onclick="kidCloseRequestModal()" style="flex: 1; padding: 12px; background: #e5e7eb; color: #374151; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;">
                Cancel
            </button>
            <button id="kidRequestConfirmBtn" onclick="kidWishConfirmRequest()" style="flex: 1; padding: 12px; background: {{ $kid->color }}; color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;">
                <i class="fas fa-paper-plane"></i> Yes, Ask Parent!
            </button>
        </div>
    </div>
</div>

{{-- Create Wish Modal (inline version) --}}
<div class="kid-form-modal" id="kidCreateWishModal" data-scrape-url="{{ route('kid.wishes.scrape-url') }}">
    <div class="kid-modal-backdrop" onclick="kidCloseInlineWishModal()"></div>
    <div class="kid-modal-content">
        <div class="kid-modal-header">
            <h3><i class="fas fa-heart" style="color: {{ $kid->color }};"></i> Add a Wish</h3>
            <button class="kid-modal-close" onclick="kidCloseInlineWishModal()">×</button>
        </div>
        <form action="{{ route('kid.wishes.store') }}" method="POST" enctype="multipart/form-data" class="kid-form" id="kidInlineWishForm"
              onsubmit="const p=document.getElementById('kidWishPrice'); if(p) p.value=p.value.replace(/[^0-9.]/g,'');">
            @csrf
            <div class="kid-form-group">
                <label class="kid-form-label">Item URL (optional)</label>
                <div style="display: flex; gap: 8px;">
                    <input type="url" id="kidWishUrl" name="item_url" class="kid-form-input" placeholder="https://amazon.com/..." style="flex: 1;">
                    <button type="button" id="kidScrapeBtn" onclick="kidScrapeWishUrl()" style="background: {{ $kid->color }}; color: white; padding: 0 14px; border-radius: 8px; border: none; cursor: pointer; font-size: 13px; font-weight: 600; white-space: nowrap;">
                        <i class="fas fa-magic"></i> Auto-fill
                    </button>
                </div>
                <div style="font-size: 12px; color: #9ca3af; margin-top: 5px;">
                    <i class="fas fa-info-circle"></i> We'll try to auto-fill the product name, price, and image — but please double-check the details before saving.
                </div>
                <div id="kidScrapeError" style="display: none; color: #ef4444; font-size: 12px; margin-top: 4px;"></div>
                <div id="kidScrapePartial" style="display: none; color: #f59e0b; font-size: 12px; margin-top: 4px;"></div>
            </div>
            <div class="kid-form-group">
                <label class="kid-form-label">Item Name <span style="color: #ef4444;">*</span></label>
                <input type="text" id="kidWishItemName" name="item_name" class="kid-form-input" placeholder="What do you wish for?" required>
            </div>
            <div class="kid-form-group">
                <label class="kid-form-label">Price <span style="color: #ef4444;">*</span></label>
                <input type="text" id="kidWishPrice" name="price" class="kid-form-input" placeholder="$0.00"
                       oninput="kidFormatCurrency(this)" required>
            </div>
            <div class="kid-form-group">
                <label class="kid-form-label">Upload Image (optional)</label>
                <label for="kidWishImageFile" class="kid-wish-upload-label">
                    <i class="fas fa-camera"></i>
                    <span id="kidWishImageFileName">Choose a photo...</span>
                </label>
                <input type="file" id="kidWishImageFile" name="image" accept="image/*"
                       style="display: none;"
                       onchange="document.getElementById('kidWishImageFileName').textContent = this.files[0] ? this.files[0].name : 'Choose a photo...'; kidWishPreviewFile(this);">
                <div id="kidWishImagePreviewGroup" style="display: none; margin-top: 8px;">
                    <img id="kidWishImagePreview" src="" alt="Preview" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #e5e7eb;">
                    <input type="hidden" id="kidWishScrapedImageUrl" name="scraped_image_url">
                </div>
            </div>
            <div class="kid-form-group">
                <label class="kid-form-label">Why do you want this?</label>
                <textarea name="reason" class="kid-form-input" rows="2" placeholder="Tell your parents why..." style="font-family: inherit; resize: vertical; min-height: 72px;"></textarea>
            </div>
            <div class="kid-form-actions">
                <button type="button" class="kid-btn kid-btn-secondary" onclick="kidCloseInlineWishModal()">Cancel</button>
                <button type="submit" class="kid-btn" style="background: {{ $kid->color }}; color: white; flex: 1;">
                    <i class="fas fa-heart"></i> Save Wish
                </button>
            </div>
        </form>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }

/* Inner tabs */
.kid-inner-tab {
    padding: 10px 16px;
    font-size: 14px;
    font-weight: 600;
    color: #6b7280;
    background: transparent;
    border: none;
    outline: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    position: relative;
    bottom: -2px;
    transition: all 0.2s;
}
.kid-inner-tab:hover { color: #374151; }
.kid-inner-tab-active {
    color: {{ $kid->color }} !important;
    border-bottom-color: {{ $kid->color }} !important;
}
.kid-inner-tab-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    font-size: 11px;
    font-weight: 700;
    color: #6b7280;
    background: #e5e7eb;
    border-radius: 10px;
}
.kid-tab-action-btn {
    padding: 8px 14px;
    border-radius: 8px;
    border: none;
    outline: none;
    font-size: 13px;
    font-weight: 600;
    color: white;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
}

/* Wishes Grid */
.kid-wishes-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

@media (max-width: 1100px) {
    .kid-wishes-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }
}

/* Wish Card */
.kid-wish-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: box-shadow 0.2s;
}
.kid-wish-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.kid-wish-card-purchased {
    opacity: 0.85;
}
.kid-wish-card-declined {
    opacity: 0.75;
    border-color: #fca5a5;
}

/* Card Image */
.kid-wish-card-img {
    width: 100%;
    aspect-ratio: 1 / 1;
    background: #f3f4f6;
    position: relative;
    overflow: hidden;
}
.kid-wish-card-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.kid-wish-card-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: #9ca3af;
    font-size: 13px;
}
.kid-wish-card-placeholder i { font-size: 32px; }
.kid-wish-purchased-overlay {
    position: absolute;
    inset: 0;
    background: rgba(16, 185, 129, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    color: white;
}

/* Card Body */
.kid-wish-card-body {
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
}
.kid-wish-card-title {
    font-size: 14px;
    font-weight: 700;
    color: #1f2937;
    line-height: 1.3;
}
.kid-wish-card-price {
    font-size: 18px;
    font-weight: 800;
}
.kid-wish-card-reason {
    font-size: 12px;
    color: #6b7280;
    line-height: 1.4;
}
.kid-wish-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 10px;
    width: fit-content;
}
.kid-wish-status-badge.pending { background: #fef3c7; color: #d97706; }
.kid-wish-status-badge.declined { background: #fee2e2; color: #991b1b; }
.kid-wish-reask-countdown {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 500;
    color: #b91c1c;
    margin-top: 3px;
}
.kid-wish-reask-ready {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 600;
    color: #1d4ed8;
    background: #dbeafe;
    border-radius: 6px;
    padding: 2px 8px;
    margin-top: 3px;
}
.kid-wish-purchased-date {
    font-size: 12px;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* Card Actions */
.kid-wish-card-actions {
    display: flex;
    gap: 6px;
    margin-top: auto;
    padding-top: 4px;
}
.kid-wish-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 7px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    border: none;
    outline: none;
    cursor: pointer;
    text-decoration: none;
    white-space: nowrap;
    flex: 1;
}
.kid-wish-btn-primary { color: white; }
.kid-wish-btn-view { background: #f3f4f6; color: #374151; }
.kid-wish-btn-remind { background: #fef3c7; color: #d97706; }
.kid-wish-btn-disabled { background: #f3f4f6; color: #9ca3af; cursor: not-allowed; }
.kid-wish-btn-cancel { background: #f3f4f6; color: #374151; }
.kid-wish-btn-goal {
    background: linear-gradient(135deg, #8b5cf6, #6d28d9);
    color: white;
    text-decoration: none;
}
.kid-wish-btn-goal:hover { opacity: 0.9; }
.kid-wish-goal-nudge {
    display: flex;
    flex-direction: column;
    gap: 6px;
    width: 100%;
}
.kid-wish-goal-nudge .kid-wish-btn {
    flex: none;
}

.kid-wish-confirm-row {
    background: #f9fafb;
    border-radius: 8px;
    padding: 10px;
    border: 1px solid #e5e7eb;
}

.kid-wish-upload-label {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    background: #f9fafb;
    border: 2px dashed #d1d5db;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
    transition: border-color 0.2s, color 0.2s;
}
.kid-wish-upload-label:hover {
    border-color: {{ $kid->color }};
    color: {{ $kid->color }};
}

@media (max-width: 768px) {
    .kid-wishes-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
}

@media (max-width: 500px) {
    .kid-wishes-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
}
</style>

<script>
// Live countdown for all declined wish cards
(function() {
    function formatCountdown(msLeft) {
        if (msLeft <= 0) return null;
        const totalSecs = Math.ceil(msLeft / 1000);
        const h = Math.floor(totalSecs / 3600);
        const m = Math.floor((totalSecs % 3600) / 60);
        const s = totalSecs % 60;
        if (h > 0) return `${h}h ${m}m`;
        if (m > 0) return `${m}m ${s}s`;
        return `${s}s`;
    }

    document.querySelectorAll('.kid-wish-reask-countdown').forEach(function(el) {
        const readyAt = parseInt(el.dataset.readyAt, 10) * 1000;
        const textEl = el.querySelector('.kid-wish-countdown-text');

        function tick() {
            const msLeft = readyAt - Date.now();
            if (msLeft <= 0) {
                // Swap countdown out for "ready" indicator, no full reload needed
                el.outerHTML = '<div class="kid-wish-reask-ready"><i class="fas fa-paper-plane"></i> Ready to re-ask!</div>';
                clearInterval(timer);
                return;
            }
            if (textEl) textEl.textContent = 'Ask again in ' + formatCountdown(msLeft);
        }

        tick();
        const timer = setInterval(tick, 1000);
    });
})();
</script>
