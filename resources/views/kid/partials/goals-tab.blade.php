{{-- Goals Tab - Inline version for kid dashboard --}}
<div x-data="{ goalsActiveTab: 'active' }">

    {{-- Tab Header --}}
    <div class="kid-inner-tabs" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #e5e7eb; padding-bottom: 0;">
        <div style="display: flex; gap: 4px;">
            <button @click="goalsActiveTab = 'active'"
                    :class="goalsActiveTab === 'active' ? 'kid-inner-tab-active' : ''"
                    class="kid-inner-tab">
                Active Goals
                @if($activeGoals->count() > 0)
                    <span class="kid-inner-tab-count" :style="goalsActiveTab === 'active' ? 'background: {{ $kid->color }}; color: white;' : ''">{{ $activeGoals->count() }}</span>
                @endif
            </button>
            <button @click="goalsActiveTab = 'completed'"
                    :class="goalsActiveTab === 'completed' ? 'kid-inner-tab-active' : ''"
                    class="kid-inner-tab">
                Completed
                @if($completedGoals->count() > 0)
                    <span class="kid-inner-tab-count" :style="goalsActiveTab === 'completed' ? 'background: {{ $kid->color }}; color: white;' : ''">{{ $completedGoals->count() }}</span>
                @endif
            </button>
        </div>
        <button onclick="kidOpenCreateGoalModal()" class="kid-tab-action-btn" style="background: {{ $kid->color }};">
            <i class="fas fa-plus"></i> New Goal
        </button>
    </div>

    {{-- Active Goals --}}
    <div x-show="goalsActiveTab === 'active'" x-cloak>
        @if($activeGoals->count() > 0)
            <div class="kid-goals-card-grid">
                @foreach($activeGoals as $goal)
                    @php
                        $progress = $goal->target_amount > 0 ? min(100, ($goal->current_amount / $goal->target_amount) * 100) : 0;
                        $isReady = $goal->isReadyToRedeem();
                        $accentColor = $isReady ? '#10b981' : $kid->color;
                    @endphp
                    <div class="kid-goal-card {{ $isReady ? 'kid-goal-card-ready' : '' }} {{ $goal->status === 'pending_redemption' ? 'kid-goal-card-pending' : '' }}"
                         x-data="{
                                showAdd: false, addAmount: '', adding: false, addSuccess: false,
                                requesting: false, requested: {{ $goal->status === 'pending_redemption' ? 'true' : 'false' }},
                                goalCurrent: {{ $goal->current_amount }},
                                goalTarget: {{ $goal->target_amount }},
                                get addCents() { const v = this.addAmount.replace(/[^0-9]/g,''); return v === '' ? 0 : parseInt(v); },
                                get addDollars() { return this.addCents / 100; },
                                get remaining() { return Math.max(0, this.goalTarget - this.goalCurrent); },
                                get isOverLimit() { return this.addDollars > this.remaining && this.addDollars > 0; },
                                get newPct() { if (!this.addDollars || this.goalTarget <= 0) return null; return Math.min(100, Math.round(((this.goalCurrent + this.addDollars) / this.goalTarget) * 100)); },
                                get hintText() {
                                    if (!this.addDollars) return '';
                                    if (this.isOverLimit) return 'You can only add $' + this.remaining.toFixed(2) + ' to complete this goal.';
                                    if (this.newPct === 100) return 'This will complete your goal! 🎉';
                                    return 'This will get you to ' + this.newPct + '% of your goal!';
                                }
                            }"
                         @submit.prevent="
                            if (!addAmount || isOverLimit) return;
                            adding = true;
                            const addStart = Date.now();
                            const capturedAmount = addDollars;
                            fetch('/kid/goals/{{ $goal->id }}/add-funds', {
                                method: 'POST',
                                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                                body: JSON.stringify({amount: capturedAmount})
                            }).then(r=>r.json()).then(d=>{
                                if(d.success){
                                    kidUpdateBalance(-capturedAmount);
                                    goalCurrent = Math.min(goalTarget, goalCurrent + capturedAmount);
                                    const newPctVal = goalTarget > 0 ? Math.min(100, Math.round((goalCurrent / goalTarget) * 100)) : 0;
                                    if ($refs.savedAmt) $refs.savedAmt.textContent = '$' + goalCurrent.toFixed(2);
                                    if ($refs.pctAmt) $refs.pctAmt.textContent = newPctVal + '%';
                                    if ($refs.barFill) $refs.barFill.style.width = newPctVal + '%';
                                    const remaining = Math.max(0, 1400 - (Date.now() - addStart));
                                    setTimeout(() => {
                                        adding = false;
                                        addSuccess = true;
                                        setTimeout(() => {
                                            $refs.addForm.classList.add('kid-goal-card-fading');
                                            setTimeout(() => {
                                                addSuccess = false;
                                                addAmount = '';
                                                showAdd = false;
                                                $refs.addForm.classList.remove('kid-goal-card-fading');
                                            }, 400);
                                        }, 1100);
                                    }, remaining);
                                } else { adding=false; alert(d.message||'Error'); }
                            }).catch(()=>{adding=false;});
                         ">

                        {{-- Card Image / Banner --}}
                        <div class="kid-goal-card-image" style="background: {{ $accentColor }}18;">
                            @if($goal->photo_path)
                                <img src="{{ asset('storage/' . $goal->photo_path) }}" alt="{{ $goal->title }}">
                            @else
                                <div class="kid-goal-card-icon-placeholder">
                                    <i class="fas fa-bullseye" style="color: {{ $accentColor }};"></i>
                                </div>
                            @endif
                            {{-- Edit button top-right --}}
                            <button onclick="kidOpenEditGoalModal({{ $goal->id }})" class="kid-goal-card-edit-btn" title="Edit Goal">
                                <i class="fas fa-pen"></i>
                            </button>
                            {{-- Status badge top-left --}}
                            @if($isReady)
                                <div class="kid-goal-card-status-badge kid-goal-card-status-ready" x-show="!requested">
                                    <i class="fas fa-check-circle"></i> Ready!
                                </div>
                                <div class="kid-goal-card-status-badge kid-goal-card-status-pending" x-show="requested" x-cloak>
                                    <i class="fas fa-clock"></i> Pending
                                </div>
                            @elseif($goal->status === 'pending_redemption')
                                <div class="kid-goal-card-status-badge kid-goal-card-status-pending">
                                    <i class="fas fa-clock"></i> Pending
                                </div>
                            @endif
                        </div>

                        {{-- Card Body --}}
                        <div class="kid-goal-card-body">
                            <div class="kid-goal-card-title">{{ $goal->title }}</div>

                            {{-- Progress --}}
                            <div class="kid-goal-card-progress-section">
                                <div class="kid-goal-card-amounts">
                                    <span x-ref="savedAmt" class="kid-goal-card-saved" style="color: {{ $accentColor }};">${{ number_format($goal->current_amount, 2) }}</span>
                                    <span class="kid-goal-card-target"> of ${{ number_format($goal->target_amount, 2) }}</span>
                                    <span x-ref="pctAmt" class="kid-goal-card-pct">{{ round($progress) }}%</span>
                                </div>
                                <div class="kid-goal-card-bar">
                                    <div x-ref="barFill" class="kid-goal-card-bar-fill" style="width: {{ $progress }}%; background: {{ $accentColor }};"></div>
                                </div>
                            </div>

                            @if($goal->auto_allocation_percentage > 0)
                                <div class="kid-goal-card-auto">
                                    <i class="fas fa-sync-alt"></i> {{ $goal->auto_allocation_percentage }}% auto-saved from allowance
                                </div>
                            @endif

                            {{-- Actions --}}
                            <div class="kid-goal-card-actions">
                                @if($isReady)
                                    {{-- Ready to redeem: show Ask Parent button (reactive) --}}
                                    <button x-show="!requested" @click="
                                        requesting = true;
                                        fetch('/kid/goals/{{ $goal->id }}/request-redemption', {
                                            method: 'POST',
                                            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}
                                        }).then(r=>r.json()).then(d=>{
                                            requesting = false;
                                            if(d.success){ requested = true; }
                                            else { alert(d.message||'Error'); }
                                        }).catch(()=>{ requesting=false; });
                                    " class="kid-goal-card-redeem-btn" :disabled="requesting">
                                        <span x-show="!requesting"><i class="fas fa-gift"></i> Ask Parent to Fulfill! 🎉</span>
                                        <span x-show="requesting" x-cloak><span class="kid-goal-add-spinner"></span> Sending...</span>
                                    </button>
                                    <span x-show="requested" x-cloak class="kid-goal-card-pending-label">
                                        <i class="fas fa-clock"></i> Awaiting parent approval
                                    </span>
                                @elseif($goal->status === 'pending_redemption')
                                    <span class="kid-goal-card-pending-label"><i class="fas fa-clock"></i> Awaiting parent approval</span>
                                @else
                                    <button @click="showAdd = !showAdd" class="kid-goal-card-add-btn" style="background: {{ $accentColor }};">
                                        <i class="fas fa-plus"></i> Add Funds
                                    </button>
                                @endif
                                <a href="{{ route('kid.goals.show', $goal) }}" class="kid-goal-card-view-btn">
                                    <i class="fas fa-eye"></i> View Goal
                                </a>
                            </div>

                            <form x-ref="addForm" :class="showAdd && !addSuccess ? 'kid-goal-card-add-form kid-goal-add-open' : (addSuccess ? 'kid-goal-card-add-form kid-goal-add-open kid-goal-card-fading' : 'kid-goal-card-add-form')">
                                <div class="kid-goal-add-row">
                                    <input type="text" x-model="addAmount"
                                           @input="let v=addAmount.replace(/[^0-9]/g,''); addAmount=v===''?'':'$'+(parseInt(v)/100).toFixed(2);"
                                           placeholder="$0.00" class="kid-goal-card-add-input" :class="isOverLimit ? 'input-error' : ''">
                                    <button type="submit" class="kid-goal-card-add-submit"
                                        :style="addSuccess ? 'background:#10b981;' : (isOverLimit ? 'background:#ef4444;' : 'background:{{ $accentColor }};')"
                                        :disabled="adding || addSuccess || isOverLimit || !addDollars">
                                        <span x-show="!adding && !addSuccess">Add</span>
                                        <span x-show="adding && !addSuccess" x-cloak><span class="kid-goal-add-spinner"></span></span>
                                        <span x-show="addSuccess" x-cloak><i class="fas fa-check"></i> Added!</span>
                                    </button>
                                    <button type="button" @click="showAdd=false;addAmount=''" class="kid-goal-card-add-cancel"><i class="fas fa-times"></i></button>
                                </div>
                                <div x-show="hintText" x-cloak class="kid-goal-add-hint" :class="isOverLimit ? 'error' : 'success'" x-text="hintText"></div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="kid-empty-state">
                <div style="font-size: 48px; margin-bottom: 12px;">🎯</div>
                <p>No active goals yet!</p>
                <button onclick="kidOpenCreateGoalModal()" class="kid-btn-create">Create Your First Goal</button>
            </div>
        @endif
    </div>

    {{-- Completed Goals --}}
    <div x-show="goalsActiveTab === 'completed'" x-cloak>
        @if($completedGoals->count() > 0)
            <div class="kid-goals-card-grid">
                @foreach($completedGoals as $goal)
                    <div class="kid-goal-card kid-goal-card-completed">
                        <div class="kid-goal-card-image" style="background: #d1fae5;">
                            @if($goal->photo_path)
                                <img src="{{ asset('storage/' . $goal->photo_path) }}" alt="{{ $goal->title }}">
                            @else
                                <div class="kid-goal-card-icon-placeholder">
                                    <i class="fas fa-check-circle" style="color: #10b981;"></i>
                                </div>
                            @endif
                            <div class="kid-goal-card-status-badge kid-goal-card-status-redeemed">
                                <i class="fas fa-gift"></i> Redeemed
                            </div>
                        </div>
                        <div class="kid-goal-card-body">
                            <div class="kid-goal-card-title">{{ $goal->title }}</div>
                            <div class="kid-goal-card-redeemed-info">
                                <span style="font-size: 20px; font-weight: 800; color: #10b981;">${{ number_format($goal->target_amount, 2) }}</span>
                                @if($goal->redeemed_at)
                                    <span style="font-size: 12px; color: #9ca3af; margin-left: 8px;">{{ $goal->redeemed_at->format('M j, Y') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="kid-empty-state">
                <div style="font-size: 48px; margin-bottom: 12px;">🏆</div>
                <p>No completed goals yet. Keep saving!</p>
            </div>
        @endif
    </div>

</div>

{{-- Create/Edit Goal Modal --}}
<div x-data="kidGoalModal()" x-show="showModal" x-cloak
     class="kid-goal-modal-overlay" @click.self="closeModal()"
     data-kid-color="{{ $kid->color }}"
     data-kid-balance="{{ $kid->balance }}"
     data-total-allocated="{{ $totalAllocated }}"
     data-allowance="{{ $kid->allowance_amount }}">
    <div class="kid-goal-modal-container" @click.stop>
        <div class="kid-goal-modal-header">
            <h2 x-text="isEditMode ? 'Edit Goal' : 'New Goal'"></h2>
            <button @click="closeModal()" class="kid-goal-modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Add/Remove Funds (edit mode only) --}}
        <div x-show="isEditMode" style="padding: 20px 22px 0;">
            <div style="display: flex; gap: 12px; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #e5e7eb;">
                <button type="button"
                        @click="showModalAddFunds = !showModalAddFunds; showModalRemoveFunds = false;"
                        class="kid-goal-modal-fund-btn kid-goal-modal-fund-add">
                    <i class="fas fa-plus"></i> Add Funds
                </button>
                <button type="button"
                        @click="showModalRemoveFunds = !showModalRemoveFunds; showModalAddFunds = false;"
                        class="kid-goal-modal-fund-btn kid-goal-modal-fund-remove">
                    <i class="fas fa-minus"></i> Remove Funds
                </button>
            </div>
            <div x-show="showModalAddFunds" x-cloak style="margin-bottom: 16px;">
                <form @submit.prevent="submitModalAddFunds()" style="display: flex; gap: 8px; align-items: center;">
                    <input type="text"
                           @input="handleModalCurrencyInput($event, 'add')"
                           placeholder="$0.00"
                           class="kid-goal-modal-inline-input"
                           style="flex: 1;"
                           required>
                    <button type="submit" class="kid-goal-modal-inline-submit kid-goal-modal-inline-add" :disabled="modalAddLoading">
                        <span x-show="!modalAddLoading && !modalAddSuccess">+ Add</span>
                        <span x-show="modalAddLoading" x-cloak><i class="fas fa-spinner fa-spin"></i></span>
                        <span x-show="modalAddSuccess" x-cloak><i class="fas fa-check"></i></span>
                    </button>
                </form>
                <div x-show="modalAddAmount && !modalAddError" class="kid-goal-inline-preview" :class="{ 'insufficient': !modalHasSufficientFunds }" x-cloak>
                    <span x-show="modalHasSufficientFunds" x-text="'Adding $' + (parseInt(modalAddAmount) / 100).toFixed(2) + ' · $' + (balance - parseInt(modalAddAmount)/100).toFixed(2) + ' will remain'"></span>
                    <span x-show="!modalHasSufficientFunds">Insufficient funds available</span>
                </div>
            </div>
            <div x-show="showModalRemoveFunds" x-cloak style="margin-bottom: 16px;">
                <form @submit.prevent="submitModalRemoveFunds()" style="display: flex; gap: 8px; align-items: center;">
                    <input type="text"
                           @input="handleModalCurrencyInput($event, 'remove')"
                           placeholder="$0.00"
                           class="kid-goal-modal-inline-input"
                           style="flex: 1;"
                           required>
                    <button type="submit" class="kid-goal-modal-inline-submit kid-goal-modal-inline-remove" :disabled="modalRemoveLoading">
                        <span x-show="!modalRemoveLoading && !modalRemoveSuccess">- Remove</span>
                        <span x-show="modalRemoveLoading" x-cloak><i class="fas fa-spinner fa-spin"></i></span>
                        <span x-show="modalRemoveSuccess" x-cloak><i class="fas fa-check"></i></span>
                    </button>
                </form>
                <div x-show="modalRemoveAmount && !modalRemoveError" class="kid-goal-inline-preview" :class="{ 'insufficient': !modalHasSufficientGoalFunds }" x-cloak>
                    <span x-show="modalHasSufficientGoalFunds" x-text="'Removing $' + (parseInt(modalRemoveAmount) / 100).toFixed(2) + ' · $' + (balance + parseInt(modalRemoveAmount)/100).toFixed(2) + ' will be available'"></span>
                    <span x-show="!modalHasSufficientGoalFunds">Insufficient goal funds to remove</span>
                </div>
            </div>
        </div>

        {{-- Main Form --}}
        <form @submit.prevent="submitForm()" class="kid-goal-modal-form" data-scrape-url="{{ route('kid.goals.scrape-url') }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <div class="kid-goal-form-group">
                <label>Product URL <span style="color: #9ca3af; font-weight: 400;">(optional)</span></label>
                <div style="display: flex; gap: 8px;">
                    <input type="url" id="kidGoalUrl" x-model="formData.product_url" maxlength="500" class="kid-goal-form-input" placeholder="https://amazon.com/..." style="flex: 1;">
                    <button type="button" id="kidGoalScrapeBtn" @click="scrapeGoalUrl()" style="background: {{ $kid->color }}; color: white; padding: 0 14px; border-radius: 8px; border: none; cursor: pointer; font-size: 13px; font-weight: 600; white-space: nowrap; flex-shrink: 0;">
                        <i class="fas fa-magic"></i> Auto-fill
                    </button>
                </div>
                <div style="font-size: 12px; color: #9ca3af; margin-top: 5px;">
                    <i class="fas fa-info-circle"></i> We'll try to auto-fill the name, price, and image — double-check before saving.
                </div>
                <div id="kidGoalScrapeError" x-show="scrapeError" x-text="scrapeError" style="color: #ef4444; font-size: 12px; margin-top: 4px;"></div>
                <div id="kidGoalScrapePartial" x-show="scrapePartial" x-text="scrapePartial" style="color: #f59e0b; font-size: 12px; margin-top: 4px;"></div>
            </div>

            <div class="kid-goal-form-group">
                <label>Goal Title <span style="color:#ef4444">*</span></label>
                <input type="text" x-model="formData.title" required maxlength="255" class="kid-goal-form-input" placeholder="What are you saving for?">
            </div>

            <div class="kid-goal-form-group">
                <label>Description <span style="color: #9ca3af; font-weight: 400;">(optional)</span></label>
                <textarea x-model="formData.description" maxlength="1000" rows="2" class="kid-goal-form-input" style="font-family: inherit; resize: vertical;" placeholder="Why do you want this?"></textarea>
            </div>

            <div class="kid-goal-form-group">
                <label>Target Amount <span style="color:#ef4444">*</span></label>
                <input type="text" x-model="formData.target_amount" required class="kid-goal-form-input"
                       @input="formData.target_amount = formatCurrency($event.target.value)"
                       placeholder="$0.00">
            </div>

            <div class="kid-goal-form-group">
                <label>Auto-Allocation</label>
                <small style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 8px; font-weight: 400;">What % of your weekly allowance should auto-save to this goal?</small>
                <div x-show="totalAllocated > 0 || isEditMode" style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px; padding: 8px 12px; background: #f8fafc; border-radius: 8px; border: 1px solid #e5e7eb; font-size: 12px;">
                    <i class="fas fa-info-circle" style="color: #9ca3af; flex-shrink: 0;"></i>
                    <span style="color: #6b7280;">
                        <span x-text="maxAllowedAllocation + '%'" style="font-weight: 700; color: #374151;"></span> available to allocate
                        <span x-show="totalAllocated > 0" x-cloak>
                            · <span x-text="totalAllocated + '%'" style="color: #9ca3af;"></span> already used by other goals
                        </span>
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="flex: 1;">
                        <input type="range"
                               x-model="formData.auto_allocation_percentage"
                               min="0"
                               :max="maxAllowedAllocation"
                               step="5"
                               class="kid-goal-allocation-slider"
                               style="--slider-color: {{ $kid->color }}; width: 100%;">
                        <div style="display: flex; justify-content: space-between; font-size: 11px; color: #9ca3af; margin-top: 4px;">
                            <span>0%</span>
                            <span x-text="Math.round(maxAllowedAllocation / 2) + '%'"></span>
                            <span x-text="maxAllowedAllocation + '%'"></span>
                        </div>
                    </div>
                    <div style="font-size: 24px; font-weight: 800; color: {{ $kid->color }}; min-width: 52px; text-align: center;" x-text="(parseInt(formData.auto_allocation_percentage) || 0) + '%'"></div>
                </div>
                <div x-show="autoAllocationAmount > 0" class="kid-goal-allocation-preview" x-cloak>
                    <i class="fas fa-sync-alt"></i>
                    <span x-text="'$' + autoAllocationAmount.toFixed(2) + '/week'"></span>
                    <span x-show="weeksToComplete > 0" x-text="' · Done in ~' + timeToCompleteText" x-cloak></span>
                </div>
                <div x-show="allocationExceedsLimit" class="kid-goal-allocation-error" x-cloak>
                    <i class="fas fa-exclamation-circle"></i> Exceeds limit — max <span x-text="maxAllowedAllocation"></span>% available
                </div>
            </div>

            <div class="kid-goal-form-group">
                <label>Photo <span style="color: #9ca3af; font-weight: 400;">(optional)</span></label>
                <div class="kid-goal-photo-area" @drop.prevent="handleFileDrop($event)" @dragover.prevent @dragenter.prevent>
                    <input type="file" id="kidGoalPhoto" @change="handleFileSelect($event)" accept="image/jpeg,image/png,image/jpg,image/gif" style="position:absolute;inset:0;opacity:0;cursor:pointer;">
                    <div class="kid-goal-photo-content">
                        <template x-if="photoPreview">
                            <img :src="photoPreview" alt="Preview" style="max-width:100%;max-height:160px;border-radius:8px;">
                        </template>
                        <template x-if="!photoPreview">
                            <div style="color:#9ca3af;text-align:center;">
                                <i class="fas fa-cloud-upload-alt" style="font-size:36px;margin-bottom:8px;color:#d1d5db;display:block;"></i>
                                <p style="margin:0;font-size:13px;">Drag & drop or click to upload</p>
                            </div>
                        </template>
                    </div>
                </div>
                <template x-if="photoPreview">
                    <button type="button" @click="removePhoto()" style="margin-top:8px;padding:6px 12px;background:#ef4444;color:white;border:none;border-radius:6px;font-size:13px;cursor:pointer;">Remove Photo</button>
                </template>
            </div>

            <div class="kid-goal-modal-footer">
                <button type="button" @click="closeModal()" class="kid-goal-modal-btn-cancel">Cancel</button>
                <template x-if="isEditMode">
                    <button type="button" @click="deleteGoal()" class="kid-goal-modal-btn-delete">Delete Goal</button>
                </template>
                <button type="submit" class="kid-goal-modal-btn-save" :disabled="submitting || allocationExceedsLimit" style="background: {{ $kid->color }};">
                    <span x-show="!submitting && !allocationExceedsLimit" x-text="isEditMode ? 'Update Goal' : 'Create Goal'"></span>
                    <span x-show="submitting">Saving...</span>
                    <span x-show="allocationExceedsLimit && !submitting" style="opacity:0.7;">Allocation Exceeds Limit</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Delete Confirm Modal --}}
<div x-data="{ showDeleteModal: false, deleteCallback: null }"
     x-show="showDeleteModal"
     x-cloak
     class="kid-goal-modal-overlay"
     @click.self="showDeleteModal = false"
     @kid-open-delete-goal-modal.window="showDeleteModal = true; deleteCallback = $event.detail.callback">
    <div class="kid-goal-modal-container" style="max-width: 460px;" @click.stop>
        <div class="kid-goal-modal-header">
            <h2>Delete Goal</h2>
            <button @click="showDeleteModal = false" class="kid-goal-modal-close"><i class="fas fa-times"></i></button>
        </div>
        <div style="padding: 24px 22px;">
            <div style="display:flex;align-items:flex-start;gap:16px;margin-bottom:20px;">
                <i class="fas fa-exclamation-triangle" style="color:#f59e0b;font-size:28px;flex-shrink:0;margin-top:2px;"></i>
                <div>
                    <p style="margin:0 0 8px;font-size:16px;font-weight:600;color:#1a1a1a;">Delete this goal?</p>
                    <p style="margin:0;font-size:14px;color:#6b7280;">Any saved funds will be returned to your balance. This can't be undone.</p>
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:12px;">
                <button type="button" @click="showDeleteModal = false" class="kid-goal-modal-btn-cancel">Cancel</button>
                <button type="button" @click="deleteCallback && deleteCallback(); showDeleteModal = false" class="kid-goal-modal-btn-delete">Delete Goal</button>
            </div>
        </div>
    </div>
</div>

{{-- kidGoalModal Alpine component is registered in kid-dashboard.js via Alpine.data() --}}
{{-- Scripts in AJAX-injected HTML don't execute, so the component must be pre-registered --}}

<style>
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
    transition: background 0.2s, color 0.2s;
}
.kid-inner-tab-active .kid-inner-tab-count {
    color: white !important;
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
/* Goal Cards Grid */
.kid-goals-card-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    align-items: stretch;
}
@media (max-width: 900px) {
    .kid-goals-card-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 540px) {
    .kid-goals-card-grid { grid-template-columns: 1fr; }
}
.kid-goal-card {
    background: white;
    border-radius: 16px;
    border: 1.5px solid #e5e7eb;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: box-shadow 0.2s, transform 0.2s;
    display: flex;
    flex-direction: column;
}
.kid-goal-card:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}
.kid-goal-card-ready {
    border-color: #10b981;
    box-shadow: 0 2px 8px rgba(16,185,129,0.15);
}
.kid-goal-card-completed { opacity: 0.85; }

/* Card Image Area */
.kid-goal-card-image {
    position: relative;
    height: 140px;
    overflow: hidden;
    flex-shrink: 0;
}
.kid-goal-card-image img {
    width: 100%; height: 100%;
    object-fit: cover;
}
.kid-goal-card-icon-placeholder {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    font-size: 48px;
}
.kid-goal-card-edit-btn {
    position: absolute; top: 8px; right: 8px;
    background: rgba(255,255,255,0.9);
    border: none; border-radius: 8px;
    width: 30px; height: 30px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 12px; color: #6b7280;
    box-shadow: 0 1px 4px rgba(0,0,0,0.15);
    transition: background 0.15s;
}
.kid-goal-card-edit-btn:hover { background: white; color: #374151; }
.kid-goal-card-status-badge {
    position: absolute; top: 8px; left: 8px;
    padding: 3px 9px; border-radius: 20px;
    font-size: 11px; font-weight: 700;
    display: inline-flex; align-items: center; gap: 4px;
}
.kid-goal-card-status-ready { background: #10b981; color: white; }
.kid-goal-card-status-pending { background: #f59e0b; color: white; }
.kid-goal-card-status-redeemed { background: #6b7280; color: white; }

/* Card Body */
.kid-goal-card-body {
    padding: 16px 16px 16px;
    display: flex; flex-direction: column; gap: 10px;
    flex: 1;
}
.kid-goal-card-title {
    font-size: 15px; font-weight: 700; color: #1f2937;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 2.6em;
}
.kid-goal-card-desc {
    font-size: 13px; color: #6b7280; line-height: 1.5;
}
.kid-goal-card-progress-section { margin-top: 4px; }
.kid-goal-card-amounts {
    display: flex; align-items: baseline; gap: 2px;
    margin-bottom: 10px;
}
.kid-goal-card-saved { font-size: 22px; font-weight: 800; }
.kid-goal-card-target { font-size: 14px; color: #9ca3af; }
.kid-goal-card-pct {
    margin-left: auto;
    font-size: 14px; font-weight: 700; color: #9ca3af;
}
.kid-goal-card-bar {
    height: 10px; background: #e5e7eb; border-radius: 5px; overflow: hidden;
}
.kid-goal-card-bar-fill {
    height: 100%; border-radius: 5px;
    transition: width 0.4s ease;
}
.kid-goal-card-auto {
    font-size: 12px; color: #9ca3af;
    display: flex; align-items: center; gap: 4px;
}
.kid-goal-card-actions {
    margin-top: auto;
    padding-top: 8px;
    display: flex; flex-direction: column; gap: 8px; align-items: stretch;
}
.kid-goal-card-add-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 16px; border-radius: 10px; border: none;
    font-size: 14px; font-weight: 600; color: white; cursor: pointer;
    width: 100%; justify-content: center;
    transition: opacity 0.15s;
}
.kid-goal-card-add-btn:hover { opacity: 0.88; }
.kid-goal-card-redeem-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 16px; border-radius: 10px; border: none;
    font-size: 14px; font-weight: 700; color: white; cursor: pointer;
    width: 100%; justify-content: center;
    background: linear-gradient(135deg, #10b981, #059669);
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.35);
    transition: opacity 0.15s, transform 0.1s;
}
.kid-goal-card-redeem-btn:hover:not(:disabled) { opacity: 0.9; transform: translateY(-1px); }
.kid-goal-card-redeem-btn:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
.kid-goal-card-view-btn {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    padding: 10px 16px; border-radius: 10px;
    font-size: 14px; font-weight: 600; color: #374151;
    background: #f3f4f6; text-decoration: none;
    box-sizing: border-box;
    transition: background 0.15s;
}
.kid-goal-card-view-btn:hover { background: #e5e7eb; }
.kid-goal-card-pending-label {
    font-size: 13px; color: #f59e0b; font-weight: 600;
    display: flex; align-items: center; justify-content: center; gap: 4px;
    padding: 10px;
}
.kid-goal-card-add-form {
    display: flex; flex-direction: column; gap: 4px;
    overflow: hidden;
    max-height: 0; padding-top: 0; opacity: 0;
    transition: max-height 0.35s ease, opacity 0.3s ease, padding-top 0.35s ease;
    pointer-events: none;
}
.kid-goal-add-open {
    max-height: 80px; padding-top: 8px; opacity: 1;
    pointer-events: auto;
}
.kid-goal-add-row {
    display: flex; gap: 6px; align-items: center;
}
.kid-goal-add-hint {
    font-size: 11px; font-weight: 600; padding: 0 2px;
}
.kid-goal-add-hint.success { color: #059669; }
.kid-goal-add-hint.error { color: #dc2626; }
.kid-goal-card-add-input.input-error {
    border-color: #ef4444;
}
.kid-goal-card-add-input {
    flex: 1; padding: 7px 10px;
    border: 2px solid #e5e7eb; border-radius: 8px;
    font-size: 14px; font-family: inherit;
    min-width: 0;
}
.kid-goal-card-add-input:focus { outline: none; border-color: currentColor; }
.kid-goal-card-add-submit {
    padding: 7px 14px; border-radius: 8px; border: none;
    font-size: 13px; font-weight: 600; color: white; cursor: pointer;
    white-space: nowrap; flex-shrink: 0;
}
.kid-goal-card-add-submit:disabled { opacity: 0.6; cursor: not-allowed; }
.kid-goal-add-spinner {
    display: inline-block;
    width: 13px; height: 13px;
    border: 2px solid rgba(255,255,255,0.4);
    border-top-color: white;
    border-radius: 50%;
    animation: kid-goal-spin 0.7s linear infinite;
    vertical-align: middle;
}
@keyframes kid-goal-spin { to { transform: rotate(360deg); } }
.kid-goal-card-fading {
    opacity: 0 !important;
    max-height: 0 !important;
    padding-top: 0 !important;
}
.kid-goal-card-add-cancel {
    padding: 7px 9px; border-radius: 8px; border: none;
    background: #e5e7eb; color: #6b7280; cursor: pointer;
    font-size: 13px; flex-shrink: 0; line-height: 1;
}
.kid-goal-card-redeemed-info {
    display: flex; align-items: baseline; gap: 4px; margin-top: 4px;
}
@media (max-width: 480px) {
    .kid-goals-card-grid { grid-template-columns: 1fr; }
}

/* Goal Modal */
.kid-goal-modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex; align-items: center; justify-content: center;
    z-index: 9999; padding: 20px;
}
.kid-goal-modal-container {
    background: white; border-radius: 16px;
    max-width: 580px; width: 100%; max-height: 90vh;
    overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}
.kid-goal-modal-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 20px 22px; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; background: white; z-index: 1;
}
.kid-goal-modal-header h2 { font-size: 20px; font-weight: 700; color: #1f2937; margin: 0; }
.kid-goal-modal-close {
    background: none; border: none; font-size: 20px; color: #6b7280; cursor: pointer;
    width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px;
}
.kid-goal-modal-close:hover { background: #f3f4f6; color: #1f2937; }
.kid-goal-modal-form { padding: 20px 22px 4px; }
.kid-goal-form-group { margin-bottom: 18px; }
.kid-goal-form-group label { display: block; font-weight: 600; color: #374151; margin-bottom: 6px; font-size: 13px; }
.kid-goal-form-input {
    width: 100%; padding: 10px 14px; border: 2px solid #e5e7eb; border-radius: 8px;
    font-size: 16px; font-family: inherit; transition: border-color 0.2s; box-sizing: border-box;
}
.kid-goal-form-input:focus { outline: none; border-color: {{ $kid->color }}; }
.kid-goal-allocation-slider {
    -webkit-appearance: none; appearance: none; width: 100%; height: 6px;
    border-radius: 3px; background: #e5e7eb; outline: none;
}
.kid-goal-allocation-slider::-webkit-slider-thumb {
    -webkit-appearance: none; appearance: none; width: 20px; height: 20px;
    border-radius: 50%; background: var(--slider-color, #8b5cf6); cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}
.kid-goal-allocation-preview {
    display: flex; align-items: center; gap: 6px;
    margin-top: 8px; padding: 8px 12px; background: #eff6ff; border-radius: 6px;
    font-size: 13px; font-weight: 600; color: #1e40af;
}
.kid-goal-allocation-error {
    margin-top: 8px; padding: 8px 12px; background: #fee2e2; border-radius: 6px;
    font-size: 13px; font-weight: 600; color: #dc2626;
}
.kid-goal-photo-area {
    border: 2px dashed #d1d5db; border-radius: 8px; padding: 20px;
    text-align: center; cursor: pointer; position: relative; transition: all 0.2s;
}
.kid-goal-photo-area:hover { border-color: {{ $kid->color }}; background: #f9fafb; }
.kid-goal-photo-content { pointer-events: none; }
.kid-goal-modal-footer {
    display: flex; justify-content: flex-end; gap: 10px;
    padding: 16px 22px; border-top: 1px solid #e5e7eb; margin-top: 4px;
}
.kid-goal-modal-btn-cancel {
    padding: 10px 18px; border-radius: 8px; background: white; border: 2px solid #e5e7eb;
    color: #374151; font-weight: 600; cursor: pointer; font-size: 14px;
}
.kid-goal-modal-btn-cancel:hover { background: #f3f4f6; }
.kid-goal-modal-btn-delete {
    padding: 10px 18px; border-radius: 8px; background: #ef4444; border: none;
    color: white; font-weight: 600; cursor: pointer; font-size: 14px;
}
.kid-goal-modal-btn-delete:hover { background: #dc2626; }
.kid-goal-modal-btn-save {
    padding: 10px 20px; border-radius: 8px; border: none;
    color: white; font-weight: 600; cursor: pointer; font-size: 14px;
}
.kid-goal-modal-btn-save:disabled { opacity: 0.6; cursor: not-allowed; }
.kid-goal-modal-fund-btn {
    flex: 1; padding: 10px 16px; border-radius: 8px; font-weight: 600;
    font-size: 13px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;
}
.kid-goal-modal-fund-add { background: #10b981; color: white; }
.kid-goal-modal-fund-add:hover { background: #059669; }
.kid-goal-modal-fund-remove { background: #ef4444; color: white; }
.kid-goal-modal-fund-remove:hover { background: #dc2626; }
.kid-goal-modal-inline-input {
    padding: 10px 14px; border: 2px solid #e5e7eb; border-radius: 8px;
    font-size: 16px; font-weight: 600; font-family: inherit;
}
.kid-goal-modal-inline-input:focus { outline: none; border-color: #10b981; }
.kid-goal-modal-inline-submit {
    padding: 10px 20px; border: none; border-radius: 8px; font-weight: 600;
    font-size: 13px; color: white; cursor: pointer; white-space: nowrap; flex-shrink: 0;
}
.kid-goal-modal-inline-add { background: #10b981; }
.kid-goal-modal-inline-remove { background: #ef4444; }
.kid-goal-modal-inline-submit:disabled { opacity: 0.6; cursor: not-allowed; }
.kid-goal-inline-preview {
    margin-top: 8px; padding: 8px 12px; background: #f0fdf4;
    color: #166534; border-radius: 6px; font-size: 13px; font-weight: 500;
}
.kid-goal-inline-preview.insufficient { background: #fee2e2; color: #991b1b; }
</style>
