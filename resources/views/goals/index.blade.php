@extends('layouts.kid')

@section('title', 'My Goals - AllowanceLab')

@section('content')
    @php
        // Convert hex to RGB and create lighter shade
        $hex = ltrim($kid->color, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        // Mix with white (85% white + 15% color for light background)
        $lightR = round($r * 0.15 + 255 * 0.85);
        $lightG = round($g * 0.15 + 255 * 0.85);
        $lightB = round($b * 0.15 + 255 * 0.85);
        $lightShade = "rgb($lightR, $lightG, $lightB)";
    @endphp

    <style>
        /* Dynamic theme color based on kid's selection */
        .goals-header::after {
            background-color: {{ $kid->color }} !important;
        }

        .goal-card {
            box-shadow: 0 4px 16px rgba({{ $r }}, {{ $g }}, {{ $b }}, 0.50) !important;
        }

        .goal-card.ready-to-redeem {
            box-shadow: 0 4px 24px rgba(16, 185, 129, 0.4) !important;
        }

        .goal-beaker-fill {
            background: linear-gradient(to top, {{ $kid->color }} 0%, color-mix(in srgb, {{ $kid->color }} 70%, white) 100%) !important;
        }

        .btn-add-goal {
            background: {{ $kid->color }} !important;
        }

        .btn-add-goal:hover {
            background: color-mix(in srgb, {{ $kid->color }} 85%, black) !important;
        }

        .btn-primary {
            background: {{ $kid->color }} !important;
        }

        .btn-primary:hover {
            background: color-mix(in srgb, {{ $kid->color }} 85%, black) !important;
        }

        .goal-complete-banner {
            background: {{ $lightShade }} !important;
            color: #10b981 !important;
            border: 2px solid #10b981 !important;
        }

        /* Tab Navigation Styles */
        .goals-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            border-bottom: 2px solid #e5e7eb;
        }

        .goals-tab {
            padding: 12px 20px;
            font-size: 16px;
            font-weight: 600;
            color: #6b7280;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            position: relative;
            bottom: -2px;
        }

        .goals-tab:hover {
            color: #374151;
        }

        .goals-tab-active {
            color: {{ $kid->color }} !important;
            border-bottom-color: {{ $kid->color }} !important;
        }

        .goals-tab-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 24px;
            height: 24px;
            padding: 0 8px;
            font-size: 12px;
            font-weight: 700;
            color: white;
            background-color: #9ca3af;
            border-radius: 12px;
            transition: background-color 0.2s ease;
        }

        .goal-card-description {
            font-size: 14px;
            color: #6b7280;
            margin-top: 4px;
            line-height: 1.4;
        }
    </style>

    <!-- Mobile Welcome Section -->
    <div class="mobile-kid-welcome">
        <h1 class="mobile-kid-welcome-title">
            Your savings goals,<br><span class="kid-name-colored" style="color: {{ $kid->color }};">{{ $kid->name }}</span>.
        </h1>
        <p class="mobile-kid-welcome-subtitle">Watch your beakers fill up!</p>
    </div>

    <!-- Success/Error Messages -->
    <div x-data="{ showSuccess: {{ session('success') ? 'true' : 'false' }}, showError: {{ session('error') ? 'true' : 'false' }} }">
        <div x-show="showSuccess" x-transition class="alert alert-success" style="display: none;">
            {{ session('success') }}
        </div>
        <div x-show="showError" x-transition class="alert alert-error" style="display: none;">
            {{ session('error') }}
        </div>
    </div>

    {{-- Goal Redemption Notifications removed - now shown on dashboard instead --}}

    <!-- Tab Navigation and Content -->
    <div x-data="{ activeTab: 'active' }">
        <!-- Tab Headers -->
        <div class="goals-tabs">
            <button
                @click="activeTab = 'active'"
                :class="{ 'goals-tab-active': activeTab === 'active' }"
                class="goals-tab">
                Active Goals
                @if($activeGoals->count() > 0)
                    <span class="goals-tab-count" :style="activeTab === 'active' ? 'background-color: {{ $kid->color }};' : ''">{{ $activeGoals->count() }}</span>
                @endif
            </button>
            <button
                @click="activeTab = 'completed'"
                :class="{ 'goals-tab-active': activeTab === 'completed' }"
                class="goals-tab">
                Completed Goals
                @if($completedGoals->count() > 0)
                    <span class="goals-tab-count" :style="activeTab === 'completed' ? 'background-color: {{ $kid->color }};' : ''">{{ $completedGoals->count() }}</span>
                @endif
            </button>
        </div>

        <!-- Active Tab Content -->
        <div x-show="activeTab === 'active'" x-cloak>
            <!-- Header with Add Button -->
            <div class="goals-header">
                <div class="goals-header-left">
                    <div class="goals-header-funds" style="color: {{ $kid->color }};">
                        <i class="fas fa-wallet"></i> ${{ number_format($kid->balance, 2) }} available
                    </div>
                </div>
                <button onclick="openCreateModal()" class="btn-add-goal">+ New Goal</button>
            </div>

            <!-- Active Goals Grid -->
    @if($activeGoals->count() > 0)
        <div class="goals-grid">
            @foreach($activeGoals as $goal)
                @php
                    $progress = $goal->getProgressPercentage();
                    $isReady = $goal->isReadyToRedeem();
                @endphp

                <div class="goal-card goal-card-{{ $goal->id }}"
                     data-goal-id="{{ $goal->id }}"
                     x-data="{
                         expanded: false,
                         showAddFunds: false,
                         addFundsAmount: '',
                         addFundsLoading: false,
                         addFundsSuccess: false,
                         addFundsError: '',
                         goalId: {{ $goal->id }},
                         currentAmount: {{ $goal->current_amount }},
                         targetAmount: {{ $goal->target_amount }},
                         balance: {{ $kid->balance }},
                         get isComplete() {
                             return this.currentAmount >= this.targetAmount;
                         },
                         get progress() {
                             const exactProgress = (this.currentAmount / this.targetAmount) * 100;
                             // Show 2 decimal places for the last 1% (99% to 100%)
                             if (exactProgress >= 99 && exactProgress < 100) {
                                 return Math.min(100, parseFloat(exactProgress.toFixed(2)));
                             }
                             return Math.min(100, Math.round(exactProgress));
                         },
                         get progressDisplay() {
                             const prog = this.progress;
                             // Show 2 decimal places for the last 1%
                             if (prog >= 99 && prog < 100) {
                                 return prog.toFixed(2) + '%';
                             }
                             return Math.round(prog) + '%';
                         },
                         get amountRemaining() {
                             return Math.max(0, this.targetAmount - this.currentAmount);
                         },
                         get newProgressAfterAdd() {
                             if (!this.addFundsAmount || this.addFundsAmount === '') return this.progress;
                             const addAmount = parseInt(this.addFundsAmount) / 100;
                             const newAmount = this.currentAmount + addAmount;
                             const exactProgress = (newAmount / this.targetAmount) * 100;
                             // Show 2 decimal places for the last 1% (99% to 100%)
                             if (exactProgress >= 99 && exactProgress < 100) {
                                 return Math.min(100, parseFloat(exactProgress.toFixed(2)));
                             }
                             return Math.min(100, Math.round(exactProgress));
                         },
                         get newProgressDisplay() {
                             const prog = this.newProgressAfterAdd;
                             // Show 2 decimal places for the last 1%
                             if (prog >= 99 && prog < 100) {
                                 return prog.toFixed(2) + '%';
                             }
                             return Math.round(prog) + '%';
                         },
                         get hasSufficientFunds() {
                             if (!this.addFundsAmount || this.addFundsAmount === '') return true;
                             const addAmount = parseInt(this.addFundsAmount) / 100;
                             return addAmount <= this.balance;
                         },
                         get exceedsGoalAmount() {
                             if (!this.addFundsAmount || this.addFundsAmount === '') return false;
                             const addAmount = parseInt(this.addFundsAmount) / 100;
                             // Use a small tolerance (0.001) to account for floating point precision
                             return (this.currentAmount + addAmount) > (this.targetAmount + 0.001);
                         },
                         get maxAddableAmount() {
                             return this.targetAmount - this.currentAmount;
                         },
                         formatCurrency(value) {
                             let numValue = value.replace(/[^0-9]/g, '');
                             if (numValue === '') return '';
                             let cents = parseInt(numValue);
                             let dollars = (cents / 100).toFixed(2);
                             return '$' + dollars;
                         },
                         handleAddFundsInput(event) {
                             let input = event.target.value.replace(/[^0-9]/g, '');
                             this.addFundsAmount = input;
                             this.addFundsError = '';
                             if (input === '') {
                                 event.target.value = '';
                             } else {
                                 let cents = parseInt(input);
                                 let dollars = (cents / 100).toFixed(2);
                                 event.target.value = '$' + dollars;
                             }
                         }
                     }"
                     :class="{ 'ready-to-redeem': isComplete }">

                    <!-- Card Header: Icon, Title/Link, Buttons -->
                    <div class="goal-card-header">
                        @if($goal->photo_path)
                            <img src="{{ asset('storage/' . $goal->photo_path) }}" alt="{{ $goal->title }}" class="goal-icon">
                        @else
                            <div class="goal-icon goal-icon-placeholder">
                                <i class="fas fa-bullseye"></i>
                            </div>
                        @endif

                        <div class="goal-title-section">
                            <h3 class="goal-title">{{ $goal->title }}</h3>
                            @if($goal->product_url)
                                <a href="{{ $goal->product_url }}" target="_blank" class="goal-link">
                                    <i class="fas fa-external-link-alt"></i> View on {{ parse_url($goal->product_url, PHP_URL_HOST) }}
                                </a>
                            @endif
                        </div>

                        <!-- Goal Complete Overlay -->
                        <div x-show="isComplete && ('{{ $goal->status }}' === 'active' || '{{ $goal->status }}' === 'ready_to_redeem')" class="goal-complete-overlay">
                            <div class="goal-complete-badge">
                                <i class="fas fa-check-circle"></i> GOAL COMPLETE!
                            </div>
                            <button @click="requestRedemption{{ $goal->id }}()"
                                    class="btn-ask-redeem-subtle"
                                    :disabled="requesting{{ $goal->id }}"
                                    x-data="{ requesting{{ $goal->id }}: false }">
                                <span x-show="!requesting{{ $goal->id }}">
                                    <i class="fas fa-gift"></i> Ask Parent to Redeem
                                </span>
                                <span x-show="requesting{{ $goal->id }}" x-cloak>
                                    <i class="fas fa-spinner fa-spin"></i> Requesting...
                                </span>
                            </button>
                            <button @click.stop="openEditModal({{ $goal->id }})"
                                    class="goal-dont-redeem-link"
                                    style="background: none; border: none; padding: 0; cursor: pointer;">
                                Don't redeem right now / edit goal
                            </button>
                        </div>

                        <div class="goal-actions" x-show="!isComplete || ('{{ $goal->status }}' !== 'active' && '{{ $goal->status }}' !== 'ready_to_redeem')">
                            @if($goal->status === 'pending_redemption')
                                <button class="btn-pending-redemption" disabled>
                                    <i class="fas fa-clock"></i> Pending Parent Approval
                                </button>
                            @else
                                <button @click="showAddFunds = !showAddFunds; $nextTick(() => { if(showAddFunds) $refs.addFundsInput.focus(); })"
                                        class="btn-add-funds"
                                        style="background-color: {{ $kid->color }};">
                                    + Add Funds
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Inline Add Funds Form -->
                    <div x-show="showAddFunds" x-collapse class="goal-inline-add-funds">
                        <form @submit.prevent="submitAddFunds{{ $goal->id }}()" class="goal-inline-form">
                            <input type="text"
                                   @input="handleAddFundsInput($event)"
                                   placeholder="$0.00"
                                   class="goal-inline-input"
                                   x-ref="addFundsInput"
                                   required>
                            <button type="submit"
                                    class="goal-inline-submit"
                                    style="background-color: {{ $kid->color }};"
                                    :disabled="addFundsLoading || !hasSufficientFunds || exceedsGoalAmount">
                                <span x-show="!addFundsLoading && !addFundsSuccess">+ Add</span>
                                <span x-show="addFundsLoading" x-cloak>
                                    <i class="fas fa-spinner fa-spin"></i>
                                </span>
                                <span x-show="addFundsSuccess" x-cloak>
                                    <i class="fas fa-check"></i>
                                </span>
                            </button>
                        </form>
                        <div x-show="addFundsError" x-text="addFundsError" class="goal-inline-error" x-cloak></div>
                        <div x-show="addFundsAmount && !addFundsError"
                             class="goal-inline-preview"
                             :class="{ 'insufficient': !hasSufficientFunds || exceedsGoalAmount }"
                             x-cloak>
                            <div x-show="hasSufficientFunds && !exceedsGoalAmount">
                                <div x-text="'Adding $' + (parseInt(addFundsAmount) / 100).toFixed(2) + ' gets you to ' + newProgressDisplay + ' completed!'"></div>
                                <div class="goal-inline-preview-remaining" x-text="'$' + (balance - (parseInt(addFundsAmount) / 100)).toFixed(2) + ' will remain available'"></div>
                            </div>
                            <span x-show="!hasSufficientFunds">Insufficient funds available</span>
                            <span x-show="hasSufficientFunds && exceedsGoalAmount" x-text="'You can only add up to $' + maxAddableAmount.toFixed(2) + ' to complete this goal'"></span>
                        </div>
                    </div>

                    <!-- Progress Section with Circular Beaker -->
                    <div class="goal-progress-section" :style="{ opacity: (isComplete && ('{{ $goal->status }}' === 'active' || '{{ $goal->status }}' === 'ready_to_redeem')) ? '0.3' : '1' }">
                        <!-- Left Column: Completion Message -->
                        <div class="goal-progress-message">
                            <div>You are</div>
                            <div style="font-weight: 700;" :style="{ color: isComplete ? '#10b981' : '{{ $kid->color }}' }" x-text="progressDisplay + ' completed'"></div>
                            <div>to your goal!</div>
                        </div>

                        <!-- Center Column: Flask Circle -->
                        <div class="goal-beaker-circle">
                            <svg viewBox="0 0 100 100" class="beaker-svg">
                                <!-- Background circle -->
                                <circle cx="50" cy="50" r="45" fill="none" stroke="#e5e7eb" stroke-width="8"/>
                                <!-- Progress circle -->
                                <circle
                                    cx="50"
                                    cy="50"
                                    r="45"
                                    fill="none"
                                    :stroke="isComplete ? '#10b981' : '{{ $kid->color }}'"
                                    stroke-width="8"
                                    stroke-dasharray="{{ 2 * 3.14159 * 45 }}"
                                    :stroke-dashoffset="(2 * 3.14159 * 45 * (1 - progress / 100))"
                                    transform="rotate(-90 50 50)"
                                    class="beaker-progress"
                                />
                                <!-- Beaker Flask SVG in center (20% larger) -->
                                <g transform="translate(50, 50) scale(1.2)">
                                    <!-- Define clipping path for liquid -->
                                    <defs>
                                        <clipPath id="beaker-clip-{{ $goal->id }}">
                                            <!-- Flask outline path for clipping -->
                                            <path d="M -4,-15 L -4,-5 L -12,10 Q -12,13 -9,13 L 9,13 Q 12,13 12,10 L 4,-5 L 4,-15 Q 4,-16 0,-16 Q -4,-16 -4,-15 Z"/>
                                        </clipPath>
                                    </defs>

                                    <!-- Liquid fill (clipped to flask shape) -->
                                    <g clip-path="url(#beaker-clip-{{ $goal->id }})">
                                        <rect
                                            x="-12"
                                            :y="(13 - (29 * progress / 100))"
                                            width="24"
                                            height="29"
                                            :fill="isComplete ? '#10b981' : '{{ $kid->color }}'"
                                        />
                                    </g>

                                    <!-- Flask outline -->
                                    <path
                                        d="M -4,-15 L -4,-5 L -12,10 Q -12,13 -9,13 L 9,13 Q 12,13 12,10 L 4,-5 L 4,-15 Q 4,-16 0,-16 Q -4,-16 -4,-15 Z"
                                        fill="none"
                                        stroke="#DBD8D7"
                                        stroke-width="2.5"
                                    />

                                    <!-- Measurement lines on flask -->
                                    <line x1="-6.5" y1="2" x2="-4" y2="2" stroke="#DBD8D7" stroke-width="1.5"/>
                                    <line x1="-8" y1="5" x2="-4.5" y2="5" stroke="#DBD8D7" stroke-width="1.5"/>
                                    <line x1="-10" y1="8" x2="-6" y2="8" stroke="#DBD8D7" stroke-width="1.5"/>
                                </g>
                            </svg>
                        </div>

                        <!-- Right Column: Amount Display -->
                        <div class="goal-progress-amount">
                            <div class="goal-current-amount" x-text="'$' + currentAmount.toFixed(2)"></div>
                            <div class="goal-target-amount" x-text="'of $' + targetAmount.toFixed(2)"></div>
                            <div x-show="!isComplete" class="goal-amount-remaining" style="color: {{ $kid->color }};" x-text="'$' + amountRemaining.toFixed(2) + ' to complete'" x-cloak></div>
                        </div>
                    </div>

                    <!-- See Details Link (collapsed state) -->
                    <div class="goal-see-details-container goal-see-details-{{ $goal->id }}">
                        <button onclick="showDetails{{ $goal->id }}()"
                                class="goal-see-details"
                                :style="{ color: isComplete ? '#10b981' : '{{ $kid->color }}' }">
                            See Details
                        </button>
                    </div>

                    <!-- Expanded Details Section -->
                    <div class="goal-expanded-details goal-expanded-{{ $goal->id }} hidden">
                        <!-- Auto-allocation Info Box -->
                        @if($goal->auto_allocation_percentage && $goal->auto_allocation_percentage > 0)
                        @php
                            $weeklyAmount = ($kid->allowance_amount * $goal->auto_allocation_percentage) / 100;
                        @endphp
                        <div class="goal-auto-allocation-box">
                            <div>{{ number_format($goal->auto_allocation_percentage, 0) }}% of weekly allowance (${{ number_format($weeklyAmount, 2) }}/week)</div>
                            @if($goal->expected_completion_date && !$isReady)
                                <div class="goal-expected-date">Expected completion: {{ $goal->expected_completion_date->format('M j, Y') }}</div>
                            @endif
                        </div>
                    @else
                        <div class="goal-no-allocation-box">
                            <div>No auto-allocation set</div>
                            <div class="goal-expected-unavailable">Expected completion date unavailable</div>
                        </div>
                    @endif

                    <!-- Description -->
                    <div class="goal-description-container" x-data="{ descExpanded: false }">
                        @if($goal->description)
                            <div class="goal-description" :class="{ 'expanded': descExpanded }">
                                {{ $goal->description }}
                            </div>
                            @if(strlen($goal->description) > 120)
                                <button @click="descExpanded = !descExpanded" class="goal-description-toggle" style="color: {{ $kid->color }};">
                                    <span x-text="descExpanded ? 'Show less' : 'Read more'"></span>
                                </button>
                            @endif
                        @else
                            <div class="goal-description goal-description-empty"></div>
                        @endif
                    </div>

                    <!-- Transaction History (Expandable) -->
                    <div class="goal-transaction-section">
                        <button @click="expanded = !expanded" class="goal-transaction-toggle">
                            <span>Transaction History</span>
                            <i :class="expanded ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
                        </button>

                        <div x-show="expanded" x-collapse class="goal-transaction-list">
                            @forelse($goal->goalTransactions as $transaction)
                                <div class="goal-transaction-item">
                                    <div class="goal-transaction-left">
                                        <div class="goal-transaction-icon {{ $transaction->isDeposit() ? 'deposit' : 'withdrawal' }}">
                                            <i class="fas {{ $transaction->isDeposit() ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
                                        </div>
                                        <div class="goal-transaction-details">
                                            <div class="goal-transaction-type">
                                                @if($transaction->transaction_type === 'manual_deposit')
                                                    Manual Deposit
                                                @elseif($transaction->transaction_type === 'manual_withdrawal')
                                                    Manual Withdrawal
                                                @elseif($transaction->transaction_type === 'auto_allocation')
                                                    Auto-Allocation
                                                @elseif($transaction->transaction_type === 'redemption')
                                                    Redemption
                                                @endif
                                            </div>
                                            <div class="goal-transaction-date">
                                                {{ $transaction->created_at->format('M j, Y g:i A') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="goal-transaction-amount {{ $transaction->isDeposit() ? 'positive' : 'negative' }}">
                                        {{ $transaction->isDeposit() ? '+' : '' }}${{ number_format(abs($transaction->amount), 2) }}
                                    </div>
                                </div>
                            @empty
                                <div class="goal-transaction-empty">
                                    No transactions yet
                                </div>
                            @endforelse
                        </div>
                    </div>

                        <!-- Edit Button and See Less Link -->
                        <div class="goal-edit-button-container">
                            @if(!in_array($goal->status, ['pending_redemption', 'redeemed']))
                                <button @click.stop="openEditModal({{ $goal->id }})" class="btn-edit-goal">
                                    <i class="fas fa-edit"></i> Edit Goal
                                </button>
                            @endif
                            <button onclick="hideDetails{{ $goal->id }}()"
                                    class="goal-see-less" style="color: {{ $kid->color }};">
                                See Less
                            </button>
                        </div>
                    </div>
                    <!-- End Expanded Details Section -->
                </div>
            @endforeach
        </div>
    @else
        <div class="goals-empty-state">
            <i class="fas fa-bullseye"></i>
            @if($completedGoals->count() > 0)
                <p>No active goals right now!</p>
                <button onclick="openCreateModal()" class="btn-add-goal">Create New Goal</button>
            @else
                <p>You haven't created any goals yet!</p>
                <button onclick="openCreateModal()" class="btn-add-goal">Create Your First Goal</button>
            @endif
        </div>
    @endif
        </div>
        <!-- End Active Tab Content -->

        <!-- Completed Tab Content -->
        <div x-show="activeTab === 'completed'" x-cloak>
            @if($completedGoals->count() > 0)
                <div class="goals-grid" style="margin-top: 24px;">
                    @foreach($completedGoals as $goal)
                        <div class="goal-card goal-card-redeemed">
                            <div class="goal-card-header">
                                <div class="goal-card-header-left">
                                    @if($goal->photo_path)
                                        <img src="{{ asset('storage/' . $goal->photo_path) }}" alt="{{ $goal->title }}" class="goal-icon">
                                    @else
                                        <div class="goal-icon goal-icon-placeholder">
                                            <i class="fas fa-trophy"></i>
                                        </div>
                                    @endif
                                    <div class="goal-card-title-section">
                                        <h3 class="goal-card-title">{{ $goal->title }}</h3>
                                        @if($goal->description)
                                            <p class="goal-card-description">{{ $goal->description }}</p>
                                        @endif
                                        <div class="goal-redeemed-badge">
                                            <i class="fas fa-check-circle"></i> Redeemed on {{ $goal->redeemed_at->format('M j, Y') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="goal-redeemed-amount">
                                Goal: ${{ number_format($goal->target_amount, 2) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="goals-empty-state">
                    <i class="fas fa-trophy"></i>
                    <p>You haven't completed any goals yet!</p>
                    <p style="font-size: 14px; color: #6b7280; margin-top: 8px;">Keep saving to reach your first goal!</p>
                </div>
            @endif
        </div>
        <!-- End Completed Tab Content -->
    </div>
    <!-- End Tab Navigation and Content -->

    <!-- Create/Edit Modal -->
    <div x-data="goalModal()" x-show="showModal" x-cloak class="modal-overlay" @click.self="closeModal()">
        <div class="modal-container" @click.stop>
            <div class="modal-header">
                <h2 x-text="isEditMode ? 'Edit Goal' : 'Create New Goal'"></h2>
                <button @click="closeModal()" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Add/Remove Funds buttons and forms (only show in edit mode, outside main form to avoid nesting) -->
            <div x-show="isEditMode">
                <div class="modal-fund-actions">
                    <div class="modal-fund-section">
                        <button type="button"
                                @click="showModalAddFunds = !showModalAddFunds; showModalRemoveFunds = false; $nextTick(() => { if(showModalAddFunds) $refs.modalAddInput.focus(); });"
                                class="btn-modal-add-funds">
                            <i class="fas fa-plus"></i> Add Funds
                        </button>
                    </div>

                    <div class="modal-fund-section">
                        <button type="button"
                                @click="showModalRemoveFunds = !showModalRemoveFunds; showModalAddFunds = false; $nextTick(() => { if(showModalRemoveFunds) $refs.modalRemoveInput.focus(); });"
                                class="btn-modal-remove-funds">
                            <i class="fas fa-minus"></i> Remove Funds
                        </button>
                    </div>
                </div>

                <!-- Inline Forms Below Buttons (full width) -->
                <div x-show="showModalAddFunds || showModalRemoveFunds" class="modal-inline-forms-container">
                    <div x-show="showModalAddFunds" x-collapse class="modal-inline-form-wrapper" style="width: 100% !important;">
                        <form @submit.prevent="submitModalAddFunds()" class="modal-inline-form" style="display: flex !important; width: 100% !important; gap: 8px !important;">
                            <input type="text"
                                   @input="handleModalCurrencyInput($event, 'add')"
                                   placeholder="$0.00"
                                   class="modal-inline-input"
                                   x-ref="modalAddInput"
                                   style="flex: 1 !important;"
                                   required>
                            <button type="submit" class="modal-inline-submit modal-inline-submit-add" :disabled="modalAddLoading">
                                <span x-show="!modalAddLoading && !modalAddSuccess">+ Add</span>
                                <span x-show="modalAddLoading" x-cloak>
                                    <i class="fas fa-spinner fa-spin"></i>
                                </span>
                                <span x-show="modalAddSuccess" x-cloak>
                                    <i class="fas fa-check"></i>
                                </span>
                            </button>
                        </form>
                        <div x-show="modalAddError" x-text="modalAddError" class="goal-inline-error" x-cloak></div>
                        <div x-show="modalAddAmount && !modalAddError"
                             class="goal-inline-preview"
                             :class="{ 'insufficient': !modalHasSufficientFunds }"
                             x-cloak>
                            <div x-show="modalHasSufficientFunds">
                                <div x-text="'Adding $' + (parseInt(modalAddAmount) / 100).toFixed(2) + ' gets you to ' + modalNewProgressAfterAdd + '% completed!'"></div>
                                <div class="goal-inline-preview-remaining" x-text="'$' + (balance - (parseInt(modalAddAmount) / 100)).toFixed(2) + ' will remain available'"></div>
                            </div>
                            <span x-show="!modalHasSufficientFunds">Insufficient funds available</span>
                        </div>
                    </div>

                    <div x-show="showModalRemoveFunds" x-collapse class="modal-inline-form-wrapper" style="width: 100% !important;">
                        <form @submit.prevent="submitModalRemoveFunds()" class="modal-inline-form" style="display: flex !important; width: 100% !important; gap: 8px !important;">
                            <input type="text"
                                   @input="handleModalCurrencyInput($event, 'remove')"
                                   placeholder="$0.00"
                                   class="modal-inline-input"
                                   x-ref="modalRemoveInput"
                                   style="flex: 1 !important;"
                                   required>
                            <button type="submit" class="modal-inline-submit modal-inline-submit-remove" :disabled="modalRemoveLoading">
                                <span x-show="!modalRemoveLoading && !modalRemoveSuccess">- Remove</span>
                                <span x-show="modalRemoveLoading" x-cloak>
                                    <i class="fas fa-spinner fa-spin"></i>
                                </span>
                                <span x-show="modalRemoveSuccess" x-cloak>
                                    <i class="fas fa-check"></i>
                                </span>
                            </button>
                        </form>
                        <div x-show="modalRemoveError" x-text="modalRemoveError" class="goal-inline-error" x-cloak></div>
                        <div x-show="modalRemoveAmount && !modalRemoveError"
                             class="goal-inline-preview"
                             :class="{ 'insufficient': !modalHasSufficientGoalFunds }"
                             x-cloak>
                            <div x-show="modalHasSufficientGoalFunds">
                                <div x-text="'Removing $' + (parseInt(modalRemoveAmount) / 100).toFixed(2) + ' returns you to ' + modalNewProgressAfterRemove + '% completed'"></div>
                                <div class="goal-inline-preview-remaining" x-text="'$' + (balance + (parseInt(modalRemoveAmount) / 100)).toFixed(2) + ' will be available'"></div>
                            </div>
                            <span x-show="!modalHasSufficientGoalFunds">Insufficient goal funds to remove</span>
                        </div>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submitForm()" class="modal-form">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="form-group">
                    <label for="title">Goal Title *</label>
                    <input type="text" id="title" x-model="formData.title" required maxlength="255" class="form-input">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" x-model="formData.description" maxlength="1000" rows="3" class="form-input"></textarea>
                </div>

                <div class="form-group">
                    <label for="product_url">Product Link</label>
                    <input type="url" id="product_url" x-model="formData.product_url" maxlength="500" class="form-input" placeholder="https://...">
                </div>

                <div class="form-group">
                    <label for="target_amount">Target Amount * ($)</label>
                    <input
                        type="text"
                        id="target_amount"
                        x-model="formData.target_amount"
                        required
                        class="form-input"
                        @input="formData.target_amount = formatCurrency($event.target.value)"
                        placeholder="$0.00"
                    >
                </div>

                <div class="form-group">
                    <label for="auto_allocation_percentage">Auto-Allocation</label>
                    <small class="form-help" style="margin-bottom: 16px; display: block;">How much of your weekly allowance should automatically go to this goal?</small>

                    <!-- Slider and Circle Visualization Container -->
                    <div class="auto-allocation-visual">
                        <!-- Left: Circle Visualization -->
                        <div class="auto-allocation-circle">
                            <svg viewBox="0 0 100 100" class="allocation-circle-svg">
                                <!-- Background circle -->
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#e5e7eb" stroke-width="8"/>
                                <!-- Allocated portion circle -->
                                <circle
                                    cx="50"
                                    cy="50"
                                    r="40"
                                    fill="none"
                                    stroke="{{ $kid->color }}"
                                    stroke-width="8"
                                    stroke-dasharray="{{ 2 * 3.14159 * 40 }}"
                                    :stroke-dashoffset="(2 * 3.14159 * 40) * (1 - (formData.auto_allocation_percentage || 0) / 100)"
                                    transform="rotate(-90 50 50)"
                                    class="allocation-progress"
                                />
                                <!-- Center text showing percentage -->
                                <text x="50" y="50" text-anchor="middle" dominant-baseline="middle" class="allocation-circle-percentage" x-text="(parseInt(formData.auto_allocation_percentage) || 0) + '%'">0%</text>
                            </svg>
                            <div class="allocation-circle-label">of $<span x-text="weeklyAllowance.toFixed(2)"></span><br><small style="font-size: 0.75em; opacity: 0.7;">(weekly allowance)</small></div>
                        </div>

                        <!-- Right: Slider and Info -->
                        <div class="auto-allocation-controls">
                            <div class="slider-container">
                                <input type="range"
                                       id="auto_allocation_percentage"
                                       x-model="formData.auto_allocation_percentage"
                                       min="0"
                                       :max="maxAllowedAllocation"
                                       step="5"
                                       class="allocation-slider"
                                       style="--slider-color: {{ $kid->color }};">
                                <div class="slider-labels">
                                    <span>0%</span>
                                    <span x-text="Math.round(maxAllowedAllocation / 2) + '%'"></span>
                                    <span x-text="maxAllowedAllocation + '%'"></span>
                                </div>
                            </div>

                                                        <!-- Allocation Warning/Info -->
                            <div x-show="maxAllowedAllocation < 100" class="allocation-info" x-cloak style="margin: 12px 0; padding: 10px 14px; background: #eff6ff; border-left: 3px solid #3b82f6; border-radius: 6px; font-size: 13px; color: #1e40af;">
                                <div x-show="!allocationExceedsLimit" style="display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-info-circle" style="color: #3b82f6;"></i>
                                    <span>Only <strong x-text="maxAllowedAllocation"></strong>% allocation remains available</span>
                                </div>
                                <div x-show="allocationExceedsLimit" style="display: flex; align-items: center; gap: 8px; color: #dc2626;">
                                    <i class="fas fa-exclamation-circle" style="color: #dc2626;"></i>
                                    <span>Exceeds limit by <strong x-text="Math.abs(remainingAllocation)"></strong>% — Max allowed: <strong x-text="maxAllowedAllocation"></strong>%</span>
                                </div>
                            </div>


                            <!-- Preview Info -->
                            <div x-show="autoAllocationAmount > 0" class="auto-allocation-preview" x-cloak>
                                <div x-text="'$' + autoAllocationAmount.toFixed(2) + ' per week goes to this goal'"></div>
                                <div class="auto-allocation-preview-completion" x-show="weeksToComplete > 0" x-text="'Goal completes in ~' + timeToCompleteText + ' (' + estimatedCompletionDate + ')'" x-cloak></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="photo">Goal Photo/Icon</label>
                    <div class="photo-upload-area" @drop.prevent="handleFileDrop($event)" @dragover.prevent @dragenter.prevent>
                        <input type="file" id="photo" @change="handleFileSelect($event)" accept="image/jpeg,image/png,image/jpg,image/gif" class="photo-input">
                        <div class="photo-upload-content">
                            <template x-if="photoPreview">
                                <img :src="photoPreview" alt="Preview" class="photo-preview">
                            </template>
                            <template x-if="!photoPreview">
                                <div class="photo-upload-placeholder">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p>Drag and drop an image here, or click to select</p>
                                </div>
                            </template>
                        </div>
                    </div>
                    <template x-if="photoPreview">
                        <button type="button" @click="removePhoto()" class="btn-remove-photo">Remove Photo</button>
                    </template>
                </div>

                <div class="modal-footer">
                    <button type="button" @click="closeModal()" class="btn-secondary">Cancel</button>
                    <template x-if="isEditMode">
                        <button type="button" @click="deleteGoal()" class="btn-danger">Delete Goal</button>
                    </template>
                    <button type="submit" class="btn-primary" :disabled="submitting || allocationExceedsLimit">
                        <span x-show="!submitting && !allocationExceedsLimit" x-text="isEditMode ? 'Update Goal' : 'Create Goal'"></span>
                        <span x-show="submitting">Saving...</span>
                        <span x-show="allocationExceedsLimit && !submitting" style="opacity: 0.7;">Allocation Exceeds Limit</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-data="{ showDeleteModal: false, deleteCallback: null }" 
         x-show="showDeleteModal" 
         x-cloak 
         class="modal-overlay" 
         @click.self="showDeleteModal = false"
         @open-delete-modal.window="showDeleteModal = true; deleteCallback = $event.detail.callback">
        <div class="modal-container" @click.stop style="max-width: 500px;">
            <div class="modal-header">
                <h2>Delete Goal</h2>
                <button @click="showDeleteModal = false" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <div style="display: flex; align-items: flex-start; gap: 16px; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle" style="color: #f59e0b; font-size: 32px; flex-shrink: 0; margin-top: 4px;"></i>
                    <div>
                        <p style="margin: 0 0 12px 0; font-size: 16px; font-weight: 600; color: #1a1a1a;">Are you sure you want to delete this goal?</p>
                        <p style="margin: 0; font-size: 14px; color: #666;">Any funds in this goal will be returned to your main account. This action cannot be undone.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 12px; padding: 16px 24px; border-top: 1px solid #e5e7eb;">
                <button type="button" @click="showDeleteModal = false" class="btn-secondary">Cancel</button>
                <button type="button" @click="deleteCallback && deleteCallback(); showDeleteModal = false" class="btn-danger">Delete Goal</button>
            </div>
        </div>
    </div>


<!-- Vanilla JS functions for goal card collapse -->
<script>
    @foreach($activeGoals as $goal)
    function showDetails{{ $goal->id }}() {
        const seeDetailsDiv = document.querySelector('.goal-see-details-{{ $goal->id }}');
        const expandedDiv = document.querySelector('.goal-expanded-{{ $goal->id }}');

        if (seeDetailsDiv) seeDetailsDiv.classList.add('hidden');
        if (expandedDiv) expandedDiv.classList.remove('hidden');
    }

    function hideDetails{{ $goal->id }}() {
        const seeDetailsDiv = document.querySelector('.goal-see-details-{{ $goal->id }}');
        const expandedDiv = document.querySelector('.goal-expanded-{{ $goal->id }}');

        if (seeDetailsDiv) seeDetailsDiv.classList.remove('hidden');
        if (expandedDiv) expandedDiv.classList.add('hidden');
    }

    function submitAddFunds{{ $goal->id }}() {
        const card = document.querySelector('[data-goal-id="{{ $goal->id }}"]');
        const alpineData = Alpine.$data(card);

        const amountInCents = parseInt(alpineData.addFundsAmount);
        if (isNaN(amountInCents) || amountInCents <= 0) {
            alpineData.addFundsError = 'Please enter a valid amount';
            return;
        }

        const amountInDollars = amountInCents / 100;

        // Check if user has sufficient funds
        if (amountInDollars > alpineData.balance) {
            alpineData.addFundsError = 'Insufficient funds available';
            return;
        }

        const amountNeeded = alpineData.targetAmount - alpineData.currentAmount;

        // Check if amount exceeds what's needed to complete the goal
        // Add small tolerance (0.001) to account for floating point precision
        if (amountInDollars > (amountNeeded + 0.001)) {
            alpineData.addFundsError = `You only need $${amountNeeded.toFixed(2)} to complete this goal!`;
            return;
        }

        // Show spinner
        alpineData.addFundsLoading = true;

        fetch('/kid/goals/{{ $goal->id }}/add-funds', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ amount: amountInDollars })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show spinner for 1.75 seconds
                setTimeout(() => {
                    alpineData.addFundsLoading = false;
                    alpineData.addFundsSuccess = true;

                    // Show checkmark for 1.5 seconds, then collapse and reload
                    setTimeout(() => {
                        alpineData.addFundsSuccess = false;
                        alpineData.showAddFunds = false;
                        alpineData.addFundsAmount = '';

                        // Clear the input field
                        const input = card.querySelector('.goal-inline-input');
                        if (input) input.value = '';

                        // Wait for collapse animation (300ms) then reload
                        setTimeout(() => {
                            location.reload();
                        }, 300);
                    }, 1500);
                }, 1750);
            } else {
                alert(data.message || 'Failed to add funds');
                alpineData.addFundsLoading = false;
            }
        })
        .catch(error => {
            alert('An error occurred');
            alpineData.addFundsLoading = false;
        });
    }

    function requestRedemption{{ $goal->id }}() {
        const button = event.target.closest('button');
        const alpineData = Alpine.$data(button);

        alpineData.requesting{{ $goal->id }} = true;

        fetch('/kid/goals/{{ $goal->id }}/request-redemption', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                alert(data.message || 'Failed to request redemption');
                alpineData.requesting{{ $goal->id }} = false;
            }
        })
        .catch(error => {
            alert('An error occurred');
            alpineData.requesting{{ $goal->id }} = false;
        });
    }
    @endforeach
</script>

<!-- Alpine.js with Collapse Plugin -->
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
    // Global functions for add/remove funds
    function addFunds(goalId) {
        const amount = prompt('Enter amount to add to this goal:');
        if (amount === null || amount === '') return;

        const numAmount = parseFloat(amount);
        if (isNaN(numAmount) || numAmount <= 0) {
            alert('Please enter a valid amount');
            return;
        }

        fetch(`/kid/goals/${goalId}/add-funds`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ amount: numAmount })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            alert('An error occurred. Please try again.');
            console.error('Error:', error);
        });
    }

    function removeFunds(goalId) {
        const amount = prompt('Enter amount to remove from this goal:');
        if (amount === null || amount === '') return;

        const numAmount = parseFloat(amount);
        if (isNaN(numAmount) || numAmount <= 0) {
            alert('Please enter a valid amount');
            return;
        }

        fetch(`/kid/goals/${goalId}/remove-funds`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ amount: numAmount })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            alert('An error occurred. Please try again.');
            console.error('Error:', error);
        });
    }

    function openCreateModal() {
        window.dispatchEvent(new CustomEvent('open-goal-modal', { detail: { mode: 'create' } }));
    }

    function openEditModal(goalId) {
        window.dispatchEvent(new CustomEvent('open-goal-modal', { detail: { mode: 'edit', goalId: goalId } }));
    }

    // Goal notification dismissal (7 days using sessionStorage)
    function goalNotification() {
        return {
            isDismissed: false,

            init() {
                // Check if notification was dismissed
                const dismissedUntil = sessionStorage.getItem('goalNotificationDismissedUntil');
                if (dismissedUntil) {
                    const dismissedDate = new Date(parseInt(dismissedUntil));
                    const now = new Date();
                    if (now < dismissedDate) {
                        this.isDismissed = true;
                    } else {
                        // Expired, remove from storage
                        sessionStorage.removeItem('goalNotificationDismissedUntil');
                    }
                }
            },

            dismissNotification() {
                // Set dismissal for 7 days
                const dismissUntil = new Date();
                dismissUntil.setDate(dismissUntil.getDate() + 7);
                sessionStorage.setItem('goalNotificationDismissedUntil', dismissUntil.getTime().toString());
                this.isDismissed = true;
            }
        }
    }

    function goalModal() {
        return {
            showModal: false,
            isEditMode: false,
            editGoalId: null,
            currentGoalAmount: 0,
            currentGoalAllocation: 0, // Track current goal's allocation when editing
            balance: {{ $kid->balance }},
            totalAllocated: {{ $totalAllocated }}, // Total from all active goals
            submitting: false,
            photoPreview: null,
            photoFile: null,
            showModalAddFunds: false,
            showModalRemoveFunds: false,
            modalAddAmount: '',
            modalRemoveAmount: '',
            modalAddLoading: false,
            modalAddSuccess: false,
            modalAddError: '',
            modalRemoveLoading: false,
            modalRemoveSuccess: false,
            modalRemoveError: '',
            formData: {
                title: '',
                description: '',
                product_url: '',
                target_amount: '',
                auto_allocation_percentage: '',
            },

            get weeklyAllowance() {
                return {{ $kid->allowance_amount }};
            },

            get remainingAllocation() {
                // Calculate remaining allocation based on mode
                if (this.isEditMode) {
                    // When editing, subtract current goal's allocation from total
                    const otherGoalsAllocation = this.totalAllocated - this.currentGoalAllocation;
                    return 100 - otherGoalsAllocation - (parseFloat(this.formData.auto_allocation_percentage) || 0);
                } else {
                    // When creating, subtract new allocation from total
                    return 100 - this.totalAllocated - (parseFloat(this.formData.auto_allocation_percentage) || 0);
                }
            },

            get allocationExceedsLimit() {
                return this.remainingAllocation < 0;
            },

            get maxAllowedAllocation() {
                if (this.isEditMode) {
                    const otherGoalsAllocation = this.totalAllocated - this.currentGoalAllocation;
                    return 100 - otherGoalsAllocation;
                } else {
                    return 100 - this.totalAllocated;
                }
            },

            get autoAllocationAmount() {
                if (!this.formData.auto_allocation_percentage || this.formData.auto_allocation_percentage <= 0) return 0;
                return (this.weeklyAllowance * parseFloat(this.formData.auto_allocation_percentage)) / 100;
            },

            get weeksToComplete() {
                if (!this.formData.target_amount || !this.autoAllocationAmount || this.autoAllocationAmount <= 0) return 0;
                const targetAmount = parseFloat(this.formData.target_amount) || 0;
                const currentAmount = this.isEditMode ? this.currentGoalAmount : 0;
                const remaining = targetAmount - currentAmount;
                if (remaining <= 0) return 0;
                return Math.ceil(remaining / this.autoAllocationAmount);
            },

            get timeToCompleteText() {
                if (!this.weeksToComplete) return '';

                if (this.weeksToComplete > 51) {
                    const months = Math.round(this.weeksToComplete / 4.33);
                    return months + ' month' + (months !== 1 ? 's' : '');
                } else {
                    return this.weeksToComplete + ' week' + (this.weeksToComplete !== 1 ? 's' : '');
                }
            },

            get estimatedCompletionDate() {
                if (!this.weeksToComplete) return null;
                const today = new Date();
                const completionDate = new Date(today.getTime() + (this.weeksToComplete * 7 * 24 * 60 * 60 * 1000));
                return completionDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            },

            // Modal Add Funds validations
            get modalHasSufficientFunds() {
                if (!this.modalAddAmount) return false;
                const amountInDollars = parseInt(this.modalAddAmount) / 100;
                return this.balance >= amountInDollars;
            },

            get modalNewProgressAfterAdd() {
                if (!this.modalAddAmount || !this.formData.target_amount) return 0;
                const amountInDollars = parseInt(this.modalAddAmount) / 100;
                const newCurrent = this.currentGoalAmount + amountInDollars;
                const targetAmount = parseFloat(this.formData.target_amount) || 0;
                if (targetAmount <= 0) return 0;
                return Math.min(100, Math.round((newCurrent / targetAmount) * 100));
            },

            // Modal Remove Funds validations
            get modalHasSufficientGoalFunds() {
                if (!this.modalRemoveAmount) return false;
                const amountInDollars = parseInt(this.modalRemoveAmount) / 100;
                return this.currentGoalAmount >= amountInDollars;
            },

            get modalNewProgressAfterRemove() {
                if (!this.modalRemoveAmount || !this.formData.target_amount) return 0;
                const amountInDollars = parseInt(this.modalRemoveAmount) / 100;
                const newCurrent = Math.max(0, this.currentGoalAmount - amountInDollars);
                const targetAmount = parseFloat(this.formData.target_amount) || 0;
                if (targetAmount <= 0) return 0;
                return Math.max(0, Math.round((newCurrent / targetAmount) * 100));
            },

            init() {
                window.addEventListener('open-goal-modal', (event) => {
                    if (event.detail.mode === 'create') {
                        this.openCreate();
                    } else if (event.detail.mode === 'edit') {
                        this.openEdit(event.detail.goalId);
                    }
                });
            },

            openCreate() {
                this.isEditMode = false;
                this.editGoalId = null;
                this.resetForm();
                this.showModal = true;
            },

            openEdit(goalId) {
                this.isEditMode = true;
                this.editGoalId = goalId;
                this.loadGoalData(goalId);
                this.showModal = true;
            },

            loadGoalData(goalId) {
                fetch(`/kid/goals/${goalId}/edit-data`, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    this.formData.title = data.title || '';
                    this.formData.description = data.description || '';
                    this.formData.product_url = data.product_url || '';
                    this.formData.target_amount = data.target_amount || '';
                    this.formData.auto_allocation_percentage = data.auto_allocation_percentage || '';
                    this.currentGoalAmount = parseFloat(data.current_amount) || 0;
                    this.currentGoalAllocation = parseFloat(data.auto_allocation_percentage) || 0;

                    if (data.photo_path) {
                        this.photoPreview = `/storage/${data.photo_path}`;
                    }
                })
                .catch(error => {
                    alert('Error loading goal data');
                    console.error('Error:', error);
                });
            },

            closeModal() {
                this.showModal = false;
                this.resetForm();
            },

            handleModalCurrencyInput(event, type) {
                let input = event.target.value.replace(/[^0-9]/g, '');
                if (type === 'add') {
                    this.modalAddAmount = input;
                } else {
                    this.modalRemoveAmount = input;
                }
                let numValue = input === '' ? '' : input;
                if (numValue === '') {
                    event.target.value = '';
                } else {
                    let cents = parseInt(numValue);
                    let dollars = (cents / 100).toFixed(2);
                    event.target.value = '$' + dollars;
                }
            },

            submitModalAddFunds() {
                const amountInCents = parseInt(this.modalAddAmount);
                if (isNaN(amountInCents) || amountInCents <= 0) {
                    alert('Please enter a valid amount');
                    return;
                }

                const amountInDollars = amountInCents / 100;

                // Get goal data to check amount needed
                const goalCard = document.querySelector(`[data-goal-id="${this.editGoalId}"]`);
                if (goalCard) {
                    const alpineData = Alpine.$data(goalCard);
                    const amountNeeded = alpineData.targetAmount - alpineData.currentAmount;

                    // Check if amount exceeds what's needed to complete the goal
                    if (amountInDollars > amountNeeded) {
                        alert(`You only need $${amountNeeded.toFixed(2)} to complete this goal!`);
                        return;
                    }
                }

                this.modalAddLoading = true;

                fetch(`/kid/goals/${this.editGoalId}/add-funds`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ amount: amountInDollars })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update balance and current amount immediately
                        this.balance -= amountInDollars;
                        this.currentGoalAmount += amountInDollars;

                        setTimeout(() => {
                            this.modalAddLoading = false;
                            this.modalAddSuccess = true;

                            setTimeout(() => {
                                this.modalAddSuccess = false;
                                this.showModalAddFunds = false;
                                this.modalAddAmount = '';
                                location.reload();
                            }, 1500);
                        }, 1750);
                    } else {
                        alert(data.message || 'Failed to add funds');
                        this.modalAddLoading = false;
                    }
                })
                .catch(error => {
                    alert('An error occurred');
                    this.modalAddLoading = false;
                });
            },

            submitModalRemoveFunds() {
                const amountInCents = parseInt(this.modalRemoveAmount);
                if (isNaN(amountInCents) || amountInCents <= 0) {
                    alert('Please enter a valid amount');
                    return;
                }

                const amountInDollars = amountInCents / 100;
                this.modalRemoveLoading = true;

                fetch(`/kid/goals/${this.editGoalId}/remove-funds`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ amount: amountInDollars })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update balance and current amount immediately
                        this.balance += amountInDollars;
                        this.currentGoalAmount -= amountInDollars;

                        setTimeout(() => {
                            this.modalRemoveLoading = false;
                            this.modalRemoveSuccess = true;

                            setTimeout(() => {
                                this.modalRemoveSuccess = false;
                                this.showModalRemoveFunds = false;
                                this.modalRemoveAmount = '';
                                location.reload();
                            }, 1500);
                        }, 1750);
                    } else {
                        alert(data.message || 'Failed to remove funds');
                        this.modalRemoveLoading = false;
                    }
                })
                .catch(error => {
                    alert('An error occurred');
                    this.modalRemoveLoading = false;
                });
            },

            resetForm() {
                this.formData = {
                    title: '',
                    description: '',
                    product_url: '',
                    target_amount: '',
                    auto_allocation_percentage: '',
                };
                this.currentGoalAllocation = 0;
                this.photoPreview = null;
                this.photoFile = null;
            },

            handleFileSelect(event) {
                const file = event.target.files[0];
                if (file) {
                    this.processFile(file);
                }
            },

            handleFileDrop(event) {
                const file = event.dataTransfer.files[0];
                if (file && file.type.startsWith('image/')) {
                    this.processFile(file);
                }
            },

            processFile(file) {
                this.photoFile = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.photoPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            },

            removePhoto() {
                this.photoPreview = null;
                this.photoFile = null;
                document.getElementById('photo').value = '';
            },

            submitForm() {
                if (this.submitting) return;
                this.submitting = true;

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('title', this.formData.title);
                formData.append('description', this.formData.description || '');
                formData.append('product_url', this.formData.product_url || '');
                // Convert formatted currency ($11.00) back to decimal (11.00)
                const targetAmount = (this.formData.target_amount || '').replace(/[^0-9.]/g, '');
                formData.append('target_amount', targetAmount);
                formData.append('auto_allocation_percentage', this.formData.auto_allocation_percentage || '0');

                if (this.photoFile) {
                    formData.append('photo', this.photoFile);
                }

                const url = this.isEditMode
                    ? `/kid/goals/${this.editGoalId}`
                    : '/kid/goals';

                if (this.isEditMode) {
                    formData.append('_method', 'PUT');
                }

                fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (response.ok) {
                        return response.json();
                    } else {
                        return response.text().then(text => {
                            try {
                                const data = JSON.parse(text);
                                throw new Error(data.message || 'An error occurred');
                            } catch (e) {
                                throw new Error('Server error: ' + response.status);
                            }
                        });
                    }
                })
                .then(data => {
                    window.location.reload();
                })
                .catch(error => {
                    alert(error.message);
                    this.submitting = false;
                });
            },

            deleteGoal() {
                // Dispatch event to open delete confirmation modal
                window.dispatchEvent(new CustomEvent('open-delete-modal', {
                    detail: {
                        callback: () => this.performDelete()
                    }
                }));
            },

            performDelete() {
                fetch(`/kid/goals/${this.editGoalId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ _method: 'DELETE' })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.error || data.message || 'Error deleting goal');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    }
                })
                .catch(error => {
                    alert(error.message || 'An unexpected error occurred. Please try again.');
                });
            },

            formatCurrency(value) {
                let numValue = value.replace(/[^0-9]/g, '');
                if (numValue === '') return '';
                let cents = parseInt(numValue);
                let dollars = (cents / 100).toFixed(2);
                return '$' + dollars;
            }
        }
    }
</script>

@endsection

<style>
    [x-cloak] { display: none !important; }
    .hidden { display: none !important; }

    /* Goals Header with Inline Funds */
    .goals-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 12px;
        border-bottom: 3px solid #e5e7eb;
        position: relative;
    }

    .goals-header::after {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 0;
        width: 80px;
        height: 3px;
    }

    .goals-header-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .goals-section-title {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
    }

    .goals-header-funds {
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: rgba(255, 255, 255, 0.8);
        border-radius: 8px;
        border: 1px solid currentColor;
        opacity: 0.85;
    }

    .goals-header-funds i {
        font-size: 13px;
    }

    .btn-add-goal {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        color: white;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        font-size: 14px;
    }

    .goals-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
        align-items: start; /* Prevent cards from stretching to match heights */
    }

    .goal-card {
        position: relative;
        background: white;
        border-radius: 12px;
        padding: 24px;
        transition: all 0.3s;
    }

    /* Card Header */
    .goal-card-header {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 24px;
    }

    .goal-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        object-fit: cover;
        flex-shrink: 0;
        background: #f3f4f6;
    }

    .goal-icon-placeholder {
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 28px;
    }

    .goal-title-section {
        flex: 1;
        min-width: 0;
    }

    .goal-title {
        font-size: 20px;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 6px 0;
    }

    .goal-link {
        font-size: 13px;
        color: #ec4899;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .goal-link:hover {
        text-decoration: underline;
    }

    .goal-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }

    .btn-add-funds {
        background: #10b981;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-add-funds:hover {
        background: #059669;
    }

    .btn-goal-completed {
        background: #10b981;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        font-size: 14px;
        cursor: default;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Inline Add Funds Form */
    .goal-inline-add-funds {
        margin: 0 0 16px 0;
        overflow: hidden; /* Helps with collapse animation */
    }

    .goal-inline-form {
        display: flex;
        gap: 8px;
        align-items: center;
        padding: 0; /* Remove any internal padding that might interfere */
    }

    .goal-inline-input {
        flex: 1;
        padding: 10px 12px;
        border: 2px solid #d1d5db;
        border-radius: 6px;
        font-size: 16px;
        font-weight: 600;
        transition: border-color 0.2s;
    }

    .goal-inline-input:focus {
        outline: none;
        border-color: #10b981;
    }

    .goal-inline-submit {
        padding: 10px 20px;
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: opacity 0.2s;
        white-space: nowrap;
    }

    .goal-inline-error {
        margin-top: 8px;
        padding: 8px 12px;
        background: #fee2e2;
        color: #991b1b;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
    }

    .goal-inline-preview {
        margin-top: 8px;
        padding: 8px 12px;
        background: #f0fdf4;
        color: #166534;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        text-align: left;
    }

    .goal-inline-preview.insufficient {
        background: #fee2e2;
        color: #991b1b;
    }

    .goal-inline-preview-remaining {
        margin-top: 4px;
        font-size: 12px;
        opacity: 0.8;
    }

    .goal-inline-submit:hover:not(:disabled) {
        opacity: 0.9;
    }

    .goal-inline-submit:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .btn-remove-funds {
        background: #ef4444;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-remove-funds:hover {
        background: #dc2626;
    }

    /* Progress Section */
    .goal-progress-section {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        gap: 24px;
        margin-bottom: 16px;
        padding: 20px 0;
    }

    .goal-progress-message {
        font-size: 16px;
        font-weight: 500;
        color: #374151;
        text-align: left;
        line-height: 1.5;
        padding-left: 6px;
    }

    .goal-beaker-circle {
        width: 120px;
        height: 120px;
        flex-shrink: 0;
    }

    .beaker-svg {
        width: 100%;
        height: 100%;
    }

    .goal-progress-amount {
        text-align: right;
        padding-right: 6px;
    }

    /* See Details Link */
    .goal-see-details-container {
        text-align: right;
        margin-top: 16px;
    }

    .goal-see-details {
        background: none;
        border: none;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        padding: 0;
        transition: opacity 0.2s;
    }

    .goal-see-details:hover {
        opacity: 0.7;
    }

    .goal-current-amount {
        font-size: 28px;
        font-weight: 700;
        color: #1f2937;
        line-height: 1;
    }

    .goal-target-amount {
        font-size: 16px;
        font-weight: 500;
        color: #6b7280;
        margin-top: 4px;
    }

    .goal-amount-remaining {
        font-size: 14px;
        font-weight: 700;
        margin-top: 6px;
    }

    /* Mobile: 2 column layout */
    @media (max-width: 768px) {
        .goal-progress-section {
            grid-template-columns: 1fr auto;
            gap: 16px;
        }

        .goal-progress-message {
            grid-column: 1 / -1;
            text-align: center;
        }

        .goal-beaker-circle {
            justify-self: center;
        }

        .goal-progress-amount {
            text-align: left;
        }
    }

    /* Auto-allocation boxes */
    .goal-auto-allocation-box {
        background: #dbeafe;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 16px;
        font-size: 14px;
        color: #1e40af;
        text-align: center;
    }

    .goal-expected-date {
        margin-top: 4px;
        font-size: 13px;
    }

    .goal-no-allocation-box {
        background: #fef3c7;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 16px;
        font-size: 14px;
        color: #92400e;
        text-align: center;
    }

    .goal-expected-unavailable {
        margin-top: 4px;
        font-size: 13px;
    }

    .goal-description-container {
        margin-bottom: 16px;
        min-height: 62px; /* Reserve space for 2 lines + read more button */
    }

    .goal-description {
        font-size: 14px;
        color: #6b7280;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 42px; /* 2 lines at line-height 1.5 (14px * 1.5 * 2) */
    }

    .goal-description.expanded {
        display: block;
        -webkit-line-clamp: unset;
        min-height: auto;
    }

    .goal-description-empty {
        min-height: 42px;
        margin-bottom: 0;
    }

    .goal-description-toggle {
        background: none;
        border: none;
        font-size: 13px;
        cursor: pointer;
        padding: 0;
        margin-top: 4px;
        font-weight: 500;
        height: 20px; /* Fixed height for the button */
    }

    .goal-description-toggle:hover {
        text-decoration: underline;
    }

    .goal-transaction-section {
        border-top: 1px solid #e5e7eb;
        padding: 4px 16px;
        margin-top: 16px;
        background: #f9fafb;
        border-radius: 8px;
    }

    .goal-transaction-toggle {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        background: none;
        border: none;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
    }

    .goal-transaction-toggle:hover {
        color: #1f2937;
    }

    .goal-transaction-list {
        margin-top: 12px;
    }

    .goal-transaction-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .goal-transaction-item:last-child {
        border-bottom: none;
    }

    .goal-transaction-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .goal-transaction-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    .goal-transaction-icon.deposit {
        background: #d1fae5;
        color: #10b981;
    }

    .goal-transaction-icon.withdrawal {
        background: #fee2e2;
        color: #ef4444;
    }

    .goal-transaction-details {
        flex: 1;
    }

    .goal-transaction-type {
        font-size: 14px;
        font-weight: 600;
        color: #1f2937;
    }

    .goal-transaction-date {
        font-size: 12px;
        color: #6b7280;
    }

    .goal-transaction-amount {
        font-size: 16px;
        font-weight: 700;
    }

    .goal-transaction-amount.positive {
        color: #10b981;
    }

    .goal-transaction-amount.negative {
        color: #ef4444;
    }

    .goal-transaction-empty {
        padding: 20px;
        text-align: center;
        color: #9ca3af;
        font-size: 14px;
    }

    .goal-edit-button-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 16px;
        padding-top: 12px;
    }

    .goal-see-less {
        background: none;
        border: none;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        padding: 0;
        transition: opacity 0.2s;
    }

    .goal-see-less:hover {
        opacity: 0.7;
    }

    .btn-edit-goal {
        padding: 10px 20px;
        background: none;
        border: none;
        font-weight: 600;
        color: #B0ACAC;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-edit-goal:hover {
        color: #8a8686;
    }

    .goals-empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .goals-empty-state i {
        font-size: 64px;
        color: #d1d5db;
        margin-bottom: 16px;
    }

    .goals-empty-state p {
        font-size: 18px;
        color: #6b7280;
        margin-bottom: 24px;
    }

    .goal-card-redeemed {
        opacity: 0.8;
        border-left-color: #10b981 !important;
    }

    .goal-redeemed-badge {
        font-size: 13px;
        color: #10b981;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .goal-redeemed-amount {
        font-size: 16px;
        font-weight: 600;
        color: #6b7280;
        margin-top: 12px;
    }

    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 20px;
    }

    .modal-container {
        background: white;
        border-radius: 16px;
        max-width: 600px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        padding: 0 22px 10px 22px;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 24px 0;
        border-bottom: 1px solid #e5e7eb;
    }

    .modal-header h2 {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        color: #6b7280;
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .modal-close:hover {
        background: #f3f4f6;
        color: #1f2937;
    }

    .modal-form {
        padding: 24px 0 0 0;
    }

    .modal-fund-actions {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
        padding-bottom: 24px;
        border-bottom: 1px solid #e5e7eb;
    }

    .modal-fund-section {
        flex: 1;
    }

    .btn-modal-add-funds,
    .btn-modal-remove-funds {
        width: 100%;
        padding: 12px 20px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 14px;
    }

    .btn-modal-add-funds {
        background: #10b981;
        color: white;
    }

    .btn-modal-add-funds:hover {
        background: #059669;
    }

    .btn-modal-remove-funds {
        background: #ef4444;
        color: white;
    }

    .btn-modal-remove-funds:hover {
        background: #dc2626;
    }

    .modal-inline-forms-container {
        width: 100%;
    }

    .modal-inline-form-wrapper {
        width: 100%;
    }

    .modal-inline-form {
        display: flex;
        gap: 8px;
        width: 100%;
    }

    .modal-inline-input {
        flex: 1;
        padding: 12px 16px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 18px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
        font-weight: 600;
        background: white;
    }

    .modal-inline-input:focus {
        outline: none;
        border-color: #10b981;
    }

    .modal-inline-submit {
        padding: 12px 24px;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: opacity 0.2s;
        white-space: nowrap;
        flex-shrink: 0;
        min-width: 100px;
    }

    .modal-inline-submit-add {
        background: #10b981;
    }

    .modal-inline-submit-add:hover:not(:disabled) {
        opacity: 0.9;
    }

    .modal-inline-submit-remove {
        background: #ef4444;
    }

    .modal-inline-submit-remove:hover:not(:disabled) {
        opacity: 0.9;
    }

    .modal-inline-submit:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-input {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 18px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
        transition: border-color 0.2s;
    }

    .form-input:focus {
        outline: none;
        border-color: #10b981;
    }

    .form-help {
        display: block;
        font-size: 12px;
        color: #6b7280;
        margin-top: 4px;
    }

    .photo-upload-area {
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
    }

    .photo-upload-area:hover {
        border-color: #3b82f6;
        background: #f9fafb;
    }

    .photo-input {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
    }

    .photo-upload-content {
        pointer-events: none;
    }

    .photo-preview {
        max-width: 100%;
        max-height: 200px;
        border-radius: 8px;
    }

    .photo-upload-placeholder {
        color: #6b7280;
    }

    .photo-upload-placeholder i {
        font-size: 48px;
        margin-bottom: 12px;
        color: #d1d5db;
    }

    .photo-upload-placeholder p {
        margin: 0;
        font-size: 14px;
    }

    .btn-remove-photo {
        margin-top: 8px;
        padding: 6px 12px;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-remove-photo:hover {
        background: #dc2626;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }

    .btn-secondary {
        padding: 10px 20px;
        border-radius: 8px;
        background: white;
        border: 2px solid #e5e7eb;
        color: #374151;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-secondary:hover {
        background: #f3f4f6;
    }

    .btn-primary {
        padding: 10px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-danger {
        padding: 10px 20px;
        border-radius: 8px;
        background: #ef4444;
        color: white;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-danger:hover {
        background: #dc2626;
    }

    .alert {
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 24px;
        font-weight: 600;
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

    .auto-allocation-preview {
        margin-top: 8px;
        padding: 8px 12px;
        background: #f0fdf4;
        color: #166534;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        text-align: left;
    }

    .auto-allocation-preview-completion {
        margin-top: 4px;
        font-size: 12px;
        opacity: 0.8;
    }

    /* Auto-Allocation Visual Components */
    .auto-allocation-visual {
        display: flex;
        gap: 32px;
        align-items: center;
        padding: 20px;
        background: #f9fafb;
        border-radius: 12px;
        margin-top: 8px;
    }

    .auto-allocation-circle {
        flex-shrink: 0;
        text-align: center;
    }

    .allocation-circle-svg {
        width: 120px;
        height: 120px;
    }

    .allocation-circle-percentage {
        font-size: 24px;
        font-weight: 700;
        fill: #1f2937;
    }

    .allocation-circle-label {
        margin-top: 8px;
        font-size: 14px;
        font-weight: 600;
        color: #6b7280;
    }

    .auto-allocation-controls {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .slider-container {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .allocation-slider {
        width: 100%;
        height: 8px;
        border-radius: 4px;
        background: linear-gradient(to right, #e5e7eb 0%, var(--slider-color, #10b981) 100%);
        outline: none;
        -webkit-appearance: none;
        appearance: none;
        cursor: pointer;
    }

    .allocation-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--slider-color, #10b981);
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s;
    }

    .allocation-slider::-webkit-slider-thumb:hover {
        transform: scale(1.1);
    }

    .allocation-slider::-moz-range-thumb {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--slider-color, #10b981);
        cursor: pointer;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s;
    }

    .allocation-slider::-moz-range-thumb:hover {
        transform: scale(1.1);
    }

    .slider-labels {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        color: #6b7280;
        font-weight: 500;
        padding: 0 4px;
    }

    @media (max-width: 768px) {
        .auto-allocation-visual {
            flex-direction: column;
            gap: 20px;
        }

        .allocation-circle-svg {
            width: 100px;
            height: 100px;
        }

        .allocation-circle-percentage {
            font-size: 20px;
        }
    }

    @media (max-width: 768px) {
        .goals-grid {
            grid-template-columns: 1fr;
        }

        .goals-header {
            flex-direction: column;
            gap: 12px;
            align-items: flex-start;
        }

        .btn-add-goal {
            width: 100%;
            text-align: center;
        }

        .goal-card-header {
            flex-wrap: wrap;
        }

        .goal-actions {
            width: 100%;
        }

        .btn-add-funds,
        .btn-remove-funds {
            flex: 1;
        }
    }

    /* Goal Notifications */
    .goal-notification {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        margin-bottom: 24px;
        border-radius: 12px;
        background: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        animation: slideDown 0.3s ease-out;
    }

    .goal-notification-ready {
        border-left: 4px solid #10b981;
        background: linear-gradient(to right, rgba(16, 185, 129, 0.05), white);
    }

    .goal-notification-pending {
        border-left: 4px solid #f59e0b;
        background: linear-gradient(to right, rgba(245, 158, 11, 0.05), white);
    }

    .goal-notification-content {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
    }

    .goal-notification-icon {
        font-size: 24px;
        line-height: 1;
    }

    .goal-notification-text {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .goal-notification-text strong {
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
    }

    .goal-notification-text span {
        font-size: 14px;
        color: #6b7280;
    }

    .goal-notification-dismiss {
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        padding: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s;
    }

    .goal-notification-dismiss:hover {
        background: #f3f4f6;
        color: #374151;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Ask Parent to Redeem Button */
    .btn-ask-redeem {
        width: 100%;
        padding: 14px 20px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .btn-ask-redeem:hover:not(:disabled) {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
    }

    .btn-ask-redeem:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    /* Pending Redemption Button */
    .btn-pending-redemption {
        width: 100%;
        padding: 14px 20px;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: not-allowed;
        opacity: 0.9;
    }

    /* Goal Complete Overlay */
    .goal-complete-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 10;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        padding: 20px 24px;
        background: white;
        border-radius: 12px;
        border: 3px solid #10b981;
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
        min-width: 280px;
    }

    .goal-complete-badge {
        font-size: 20px;
        font-weight: 700;
        color: #10b981;
        text-align: center;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .goal-complete-badge i {
        font-size: 24px;
    }

    .btn-ask-redeem-subtle {
        padding: 10px 20px;
        background: #10b981;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-ask-redeem-subtle:hover:not(:disabled) {
        background: #059669;
        transform: translateY(-1px);
    }

    .btn-ask-redeem-subtle:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .goal-dont-redeem-link {
        font-size: 13px;
        color: #6b7280;
        text-decoration: none;
        transition: color 0.2s;
    }

    .goal-dont-redeem-link:hover {
        color: #374151;
        text-decoration: underline;
    }

    /* Add opacity to progress section when complete */
    .goal-card [x-data] .goal-progress-section {
        transition: opacity 0.3s;
    }

    .goal-card [x-data][x-bind] .goal-progress-section {
        opacity: 1;
    }
</style>
