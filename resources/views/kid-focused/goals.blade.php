@extends('layouts.kid-focused')

@section('title', $kid->name . '\'s Goals - AllowanceLab')

@section('header-right')
    <a href="{{ route('dashboard') }}" class="add-kid-btn" style="text-decoration: none;">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
@endsection

@section('content')
    <!-- Alpine.js for tab switching and collapse plugin -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @php
        // Convert hex to RGB for theming
        $hex = ltrim($kid->color, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        // Light background
        $lightR = round($r * 0.15 + 255 * 0.85);
        $lightG = round($g * 0.15 + 255 * 0.85);
        $lightB = round($b * 0.15 + 255 * 0.85);
        $lightShade = "rgb($lightR, $lightG, $lightB)";
    @endphp

    <style>
        /* Hide elements with x-cloak until Alpine.js loads */
        [x-cloak] {
            display: none !important;
        }

        /* Override content wrapper width to match kid's view */
        .content-wrapper {
            max-width: 1200px !important;
        }

        /* Hide desktop New Goal button on mobile */
        @media (max-width: 768px) {
            .desktop-only-new-goal {
                display: none !important;
            }
        }

        .goal-card {
            box-shadow: 0 4px 16px rgba({{ $r }}, {{ $g }}, {{ $b }}, 0.50) !important;
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

        .goal-status-badge.ready {
            background: {{ $lightShade }} !important;
            color: {{ $kid->color }} !important;
            border: 1px solid {{ $kid->color }} !important;
        }

        .goal-btn-view {
            background: {{ $kid->color }} !important;
        }

        .goal-btn-view:hover {
            background: color-mix(in srgb, {{ $kid->color }} 85%, black) !important;
        }

        /* Tab Navigation Styles */
        .goals-tabs {
            display: flex;
            gap: 8px;
        }

        .goals-tab {
            padding: 10px 18px;
            font-size: 15px;
            font-weight: 600;
            color: #6b7280;
            background: #f9fafb;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .goals-tab:hover {
            color: #374151;
            background: #f3f4f6;
        }

        .goals-tab-active {
            color: white !important;
            background: {{ $kid->color }} !important;
        }

        .goals-tab-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 22px;
            padding: 0 6px;
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            background-color: white;
            border-radius: 11px;
            transition: all 0.2s ease;
        }

        .goals-tab-active .goals-tab-count {
            color: {{ $kid->color }};
            background-color: white;
        }
    </style>

    <!-- Success/Error Messages -->
    <div x-data="{ showSuccess: {{ session('success') ? 'true' : 'false' }}, showError: {{ session('error') ? 'true' : 'false' }} }">
        <div x-show="showSuccess" x-transition class="alert alert-success" style="display: none;">
            {{ session('success') }}
        </div>
        <div x-show="showError" x-transition class="alert alert-error" style="display: none;">
            {{ session('error') }}
        </div>
    </div>

    <!-- Tab Navigation and Content -->
    <div x-data="{ activeTab: 'active' }" style="margin-top: 32px;">
        <!-- Sub-Tab Headers with Actions -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <div class="goals-tabs">
                <button
                    @click="activeTab = 'active'"
                    :class="{ 'goals-tab-active': activeTab === 'active' }"
                    class="goals-tab">
                    Active Goals
                    @if($activeGoals->count() > 0)
                        <span class="goals-tab-count">{{ $activeGoals->count() }}</span>
                    @endif
                </button>
                <button
                    @click="activeTab = 'completed'"
                    :class="{ 'goals-tab-active': activeTab === 'completed' }"
                    class="goals-tab">
                    Completed Goals
                    @if($completedGoals->count() > 0)
                        <span class="goals-tab-count">{{ $completedGoals->count() }}</span>
                    @endif
                </button>
            </div>

            <div style="display: flex; align-items: center; gap: 16px;" class="desktop-only-new-goal">
                <button class="btn-add-goal" onclick="openCreateGoalModal()" x-show="activeTab === 'active'">+ New Goal</button>
            </div>
        </div>

        <!-- Active Tab Content -->
        <div x-show="activeTab === 'active'" x-cloak>

            <!-- Active Goals Grid -->
    @if($activeGoals->count() > 0)
        <div class="goals-grid">
            @foreach($activeGoals as $goal)
                @php
                    $progress = $goal->target_amount > 0 ? ($goal->current_amount / $goal->target_amount) * 100 : 0;
                    $progress = min($progress, 100);
                    $isComplete = $progress >= 100;
                    $circumference = 2 * pi() * 54;
                    $offset = $circumference - ($progress / 100) * $circumference;
                @endphp

                <div class="goal-card {{ $isComplete ? 'ready-to-redeem' : '' }}"
                     x-data="{
                         showAddFunds: false,
                         addFundsAmount: '',
                         addFundsError: '',
                         addFundsLoading: false,
                         addFundsSuccess: false,
                         showRemoveFunds: false,
                         removeFundsAmount: '',
                         removeFundsError: '',
                         removeFundsLoading: false,
                         removeFundsSuccess: false,
                         balance: {{ $kid->balance }},
                         currentAmount: {{ $goal->current_amount }},
                         targetAmount: {{ $goal->target_amount }}
                     }"
                     data-goal-id="{{ $goal->id }}">
                    <!-- Card Header: Icon, Title, Actions -->
                    <div class="goal-card-header">
                        @if($goal->photo_path)
                            <img src="{{ asset('storage/' . $goal->photo_path) }}" alt="{{ $goal->title }}" class="goal-icon">
                        @else
                            <div class="goal-icon goal-icon-placeholder">
                                <i class="fas fa-bullseye"></i>
                            </div>
                        @endif

                        <div class="goal-title-section">
                            <h3 class="goal-title">{{ strlen($goal->title) > 60 ? substr($goal->title, 0, 60) . '...' : $goal->title }}</h3>
                            @if($goal->product_url)
                                <a href="{{ $goal->product_url }}" target="_blank" class="goal-link">
                                    <i class="fas fa-external-link-alt"></i> View on {{ parse_url($goal->product_url, PHP_URL_HOST) }}
                                </a>
                            @endif
                        </div>

                        <!-- Goal Complete Overlay for Parent -->
                        @if($isComplete)
                            <div class="goal-complete-overlay">
                                <div class="goal-complete-badge">
                                    <i class="fas fa-check-circle"></i> GOAL COMPLETE!
                                </div>
                                <form id="redeem-form-{{ $goal->id }}" action="{{ route('parent.goals.redeem', $goal) }}" method="POST">
                                    @csrf
                                    <button type="button" onclick="showRedeemConfirmation('{{ $goal->id }}', '{{ $kid->name }}', '{{ $goal->title }}', '{{ number_format($goal->current_amount, 2) }}')" class="btn-ask-redeem-subtle">
                                        <i class="fas fa-gift"></i> Redeem Goal
                                    </button>
                                </form>
                            </div>
                        @endif

                        <div class="goal-actions" @if($isComplete) style="display: none;" @endif>
                            @if($goal->status === 'pending_redemption')
                                <div style="display: flex; flex-direction: column; gap: 8px; width: 100%;">
                                    <div class="btn-pending-redemption" style="text-align: center; padding: 8px; background: #fff3cd; color: #856404; border-radius: 8px; font-size: 12px; font-weight: 600;">
                                        <i class="fas fa-clock"></i> {{ $kid->name }} Requested Redemption
                                    </div>
                                    <div style="display: flex; gap: 8px;">
                                        <form id="approve-form-{{ $goal->id }}" action="{{ route('parent.goals.approve-redemption', $goal) }}" method="POST" style="flex: 1;">
                                            @csrf
                                            <button type="button" onclick="showApproveConfirmation('{{ $goal->id }}', '{{ $kid->name }}', '{{ $goal->title }}', '{{ number_format($goal->current_amount, 2) }}')" class="goal-btn-approve" style="width: 100%; padding: 8px; background: #10b981; color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <form action="{{ route('parent.goals.deny-redemption', $goal) }}" method="POST" style="flex: 1;">
                                            @csrf
                                            <button type="submit" class="goal-btn-deny" onclick="return confirm('Deny redemption? Goal will remain active for {{ $kid->name }}.');" style="width: 100%; padding: 8px; background: #ef4444; color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">
                                                <i class="fas fa-times"></i> Deny
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @else
                                <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-end;">
                                    <button @click="showAddFunds = !showAddFunds; showRemoveFunds = false; $nextTick(() => { if(showAddFunds) $refs.addFundsInput.focus(); })"
                                            class="btn-add-funds"
                                            style="background-color: {{ $kid->color }};">
                                        + Add Funds
                                    </button>
                                    <button @click="showRemoveFunds = !showRemoveFunds; showAddFunds = false; $nextTick(() => { if(showRemoveFunds) $refs.removeFundsInput.focus(); })"
                                            class="btn-remove-funds-link">
                                        Remove Funds
                                    </button>
                                </div>
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
                                   x-model="addFundsAmount"
                                   autocomplete="off"
                                   inputmode="decimal">

                            <button type="submit"
                                    class="goal-inline-btn-submit"
                                    style="background-color: {{ $kid->color }};"
                                    :disabled="addFundsLoading || addFundsSuccess">
                                <span x-show="!addFundsLoading && !addFundsSuccess">+ Add</span>
                                <span x-show="addFundsLoading && !addFundsSuccess">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </span>
                                <span x-show="addFundsSuccess">
                                    <i class="fas fa-check"></i>
                                </span>
                            </button>
                        </form>

                        <div x-show="addFundsError" class="goal-inline-error" x-text="addFundsError"></div>
                    </div>

                    <!-- Inline Remove Funds Form -->
                    <div x-show="showRemoveFunds" x-collapse class="goal-inline-add-funds">
                        <form @submit.prevent="submitRemoveFunds{{ $goal->id }}()" class="goal-inline-form">
                            <input type="text"
                                   @input="handleRemoveFundsInput($event)"
                                   placeholder="$0.00"
                                   class="goal-inline-input"
                                   x-ref="removeFundsInput"
                                   x-model="removeFundsAmount"
                                   autocomplete="off"
                                   inputmode="decimal">

                            <button type="submit"
                                    class="goal-inline-btn-submit"
                                    style="background-color: #ef4444;"
                                    :disabled="removeFundsLoading || removeFundsSuccess">
                                <span x-show="!removeFundsLoading && !removeFundsSuccess">Remove</span>
                                <span x-show="removeFundsLoading && !removeFundsSuccess">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </span>
                                <span x-show="removeFundsSuccess">
                                    <i class="fas fa-check"></i>
                                </span>
                            </button>
                        </form>

                        <div x-show="removeFundsError" class="goal-inline-error" x-text="removeFundsError"></div>
                    </div>

                    <!-- Progress Section with Flask Beaker -->
                    <div class="goal-progress-section" style="@if($isComplete) opacity: 0.3; @endif">
                        <!-- Left Column: Completion Message -->
                        <div class="goal-progress-message">
                            <div>{{ $kid->name }} is</div>
                            <div style="font-weight: 700; color: {{ $isComplete ? '#10b981' : $kid->color }};">{{ number_format($progress, 0) }}% completed</div>
                            <div>to their goal!</div>
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
                                    stroke="{{ $isComplete ? '#10b981' : $kid->color }}"
                                    stroke-width="8"
                                    stroke-dasharray="{{ 2 * 3.14159 * 45 }}"
                                    stroke-dashoffset="{{ 2 * 3.14159 * 45 * (1 - $progress / 100) }}"
                                    transform="rotate(-90 50 50)"
                                    class="beaker-progress"
                                />
                                <!-- Flask SVG in center (20% larger) -->
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
                                            y="{{ 13 - (29 * $progress / 100) }}"
                                            width="24"
                                            height="29"
                                            fill="{{ $isComplete ? '#10b981' : $kid->color }}"
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
                            <div class="goal-current-amount">${{ number_format($goal->current_amount, 2) }}</div>
                            <div class="goal-target-amount">of ${{ number_format($goal->target_amount, 2) }}</div>
                            @if(!$isComplete)
                                <div class="goal-amount-remaining" style="color: {{ $kid->color }};">${{ number_format($goal->target_amount - $goal->current_amount, 2) }} to complete</div>
                            @endif
                        </div>
                    </div>

                    <!-- See Details Link -->
                    <div class="goal-see-details-container">
                        <button onclick="window.location='{{ route('parent.goals.show', $goal) }}'" class="goal-see-details" style="color: {{ $isComplete ? '#10b981' : $kid->color }};">
                            See Details
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="goals-empty-state">
            <i class="fas fa-bullseye"></i>
            @if($completedGoals->count() > 0)
                <p>No active goals right now!</p>
                <button class="btn-add-goal" onclick="openCreateGoalModal()">Create New Goal</button>
            @else
                <p>{{ $kid->name }} hasn't created any goals yet!</p>
                <button class="btn-add-goal" onclick="openCreateGoalModal()">Create First Goal</button>
            @endif
        </div>
    @endif

    <script>
        // Handle currency input formatting
        function handleAddFundsInput(event) {
            const input = event.target;
            let value = input.value.replace(/[^0-9]/g, '');

            if (value === '') {
                input.value = '';
                return;
            }

            const numValue = parseInt(value);
            const dollars = Math.floor(numValue / 100);
            const cents = numValue % 100;
            input.value = `$${dollars}.${cents.toString().padStart(2, '0')}`;
        }

        @foreach($activeGoals as $goal)
        function submitAddFunds{{ $goal->id }}() {
            const card = document.querySelector('[data-goal-id="{{ $goal->id }}"]');
            const alpineData = Alpine.$data(card);

            const amountInCents = parseInt(alpineData.addFundsAmount.replace(/[^0-9]/g, ''));
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
            if (amountInDollars > (amountNeeded + 0.001)) {
                alpineData.addFundsError = `You only need $${amountNeeded.toFixed(2)} to complete this goal!`;
                return;
            }

            // Clear error and show spinner
            alpineData.addFundsError = '';
            alpineData.addFundsLoading = true;

            fetch('{{ route('parent.goals.add-funds', $goal) }}', {
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

                        // Show checkmark for 1.5 seconds, then reload
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }, 1750);
                } else {
                    alpineData.addFundsLoading = false;
                    alpineData.addFundsError = data.message || 'Failed to add funds';
                }
            })
            .catch(error => {
                alpineData.addFundsLoading = false;
                alpineData.addFundsError = 'An error occurred. Please try again.';
                console.error('Error:', error);
            });
        }
        @endforeach

        // Handle remove funds input formatting
        function handleRemoveFundsInput(event) {
            const input = event.target;
            let value = input.value.replace(/[^0-9]/g, '');

            if (value === '') {
                input.value = '';
                return;
            }

            const numValue = parseInt(value);
            const dollars = Math.floor(numValue / 100);
            const cents = numValue % 100;
            input.value = `$${dollars}.${cents.toString().padStart(2, '0')}`;
        }

        @foreach($activeGoals as $goal)
        function submitRemoveFunds{{ $goal->id }}() {
            const card = document.querySelector('[data-goal-id="{{ $goal->id }}"]');
            const alpineData = Alpine.$data(card);

            const amountInCents = parseInt(alpineData.removeFundsAmount.replace(/[^0-9]/g, ''));
            if (isNaN(amountInCents) || amountInCents <= 0) {
                alpineData.removeFundsError = 'Please enter a valid amount';
                return;
            }

            const amountInDollars = amountInCents / 100;

            // Check if goal has sufficient funds
            if (amountInDollars > alpineData.currentAmount) {
                alpineData.removeFundsError = `Only $${alpineData.currentAmount.toFixed(2)} available in this goal`;
                return;
            }

            // Clear error and show spinner
            alpineData.removeFundsError = '';
            alpineData.removeFundsLoading = true;

            fetch('{{ route('parent.goals.remove-funds', $goal) }}', {
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
                        alpineData.removeFundsLoading = false;
                        alpineData.removeFundsSuccess = true;

                        // Show checkmark for 1.5 seconds, then reload
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }, 1750);
                } else {
                    alpineData.removeFundsLoading = false;
                    alpineData.removeFundsError = data.message || 'Failed to remove funds';
                }
            })
            .catch(error => {
                alpineData.removeFundsLoading = false;
                alpineData.removeFundsError = 'An error occurred. Please try again.';
                console.error('Error:', error);
            });
        }
        @endforeach
    </script>

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
                                        <h3 class="goal-card-title">{{ strlen($goal->title) > 60 ? substr($goal->title, 0, 60) . '...' : $goal->title }}</h3>
                                        @if($goal->description)
                                            <p class="goal-card-description">{{ $goal->description }}</p>
                                        @endif
                                        <div class="goal-redeemed-badge">
                                            <i class="fas fa-check-circle"></i> Redeemed on {{ $goal->redeemed_at->format('M j, Y') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div class="goal-redeemed-amount">
                                    Goal: ${{ number_format($goal->target_amount, 2) }}
                                </div>
                                <button onclick="showTransactionHistory({{ $goal->id }})" class="btn-transaction-history" style="background: transparent; color: #9ca3af; padding: 6px 12px; border: none; font-size: 12px; font-weight: 500; cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='#6b7280'" onmouseout="this.style.color='#9ca3af'">
                                    <i class="fas fa-history"></i> Transaction History
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="goals-empty-state">
                    <i class="fas fa-trophy"></i>
                    <p>No completed goals yet!</p>
                    <p style="font-size: 14px; color: #6b7280; margin-top: 8px;">{{ $kid->name }} hasn't completed any goals yet!</p>
                </div>
            @endif
        </div>
        <!-- End Completed Tab Content -->
    </div>
    <!-- End Tab Navigation and Content -->

    <!-- Create/Edit Goal Modal -->
    <div id="parentGoalModal" class="modal-overlay" onclick="if(event.target === this) closeParentGoalModal()">
        <div class="modal-container">
            <div class="modal-header">
                <h2 id="goalModalTitle">Create Goal for {{ $kid->name }}</h2>
                <button onclick="closeParentGoalModal()" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="parentGoalForm" onsubmit="submitParentGoalForm(event); return false;" class="modal-form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="goalFormMethod" name="_method" value="">
                <input type="hidden" id="goalFormId" value="">

                <div class="form-group">
                    <label for="goalProductUrl">Product Link (optional)</label>
                    <div class="input-with-button">
                        <input type="url" id="goalProductUrl" name="product_url" maxlength="500" class="form-input" placeholder="https://www.amazon.com/...">
                        <button type="button" onclick="scrapeGoalUrl()" id="scrapeGoalBtn" class="btn-scrape">
                            <i class="fas fa-magic"></i> Auto-fill
                        </button>
                    </div>
                    <p class="input-hint">Paste a link and we'll try to auto-fill the details! Works best with Target, Walmart, and most online stores.</p>
                    <div id="scrapeGoalError" class="input-error" style="display: none;"></div>
                    <div id="scrapeGoalPartialSuccess" class="input-hint" style="display: none; color: #f59e0b; margin-top: 8px;">
                        <i class="fas fa-info-circle"></i> <span id="scrapeGoalPartialMessage"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="goalTitle">Goal Title *</label>
                    <input type="text" id="goalTitle" name="title" required maxlength="255" class="form-input">
                </div>

                <div class="form-group">
                    <label for="goalDescription">Description</label>
                    <textarea id="goalDescription" name="description" maxlength="1000" rows="3" class="form-input"></textarea>
                </div>

                <div class="form-group">
                    <label for="goalTargetAmount">Target Amount * ($)</label>
                    <input
                        type="text"
                        id="goalTargetAmount"
                        name="target_amount"
                        required
                        class="form-input"
                        oninput="this.value = formatCurrencyInput(this.value)"
                        placeholder="$0.00"
                    >
                </div>

                <div class="form-group">
                    <label for="goalAutoAllocation">Auto-Allocation</label>
                    <small class="form-help" style="margin-bottom: 16px; display: block;">How much of {{ $kid->name }}'s weekly allowance should automatically go to this goal?</small>

                    <!-- Slider and Circle Visualization Container -->
                    <div class="auto-allocation-visual">
                        <!-- Left: Circle Visualization -->
                        <div class="auto-allocation-circle">
                            <svg viewBox="0 0 100 100" class="allocation-circle-svg">
                                <!-- Background circle -->
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#e5e7eb" stroke-width="8"/>
                                <!-- Allocated portion circle -->
                                <circle
                                    id="allocationCircle"
                                    cx="50"
                                    cy="50"
                                    r="40"
                                    fill="none"
                                    stroke="{{ $kid->color }}"
                                    stroke-width="8"
                                    stroke-dasharray="{{ 2 * 3.14159 * 40 }}"
                                    stroke-dashoffset="{{ 2 * 3.14159 * 40 }}"
                                    transform="rotate(-90 50 50)"
                                    class="allocation-progress"
                                />
                                <!-- Center text showing percentage -->
                                <text id="allocationPercentText" x="50" y="50" text-anchor="middle" dominant-baseline="middle" class="allocation-circle-percentage">0%</text>
                            </svg>
                            <div class="allocation-circle-label">of $<span id="weeklyAllowanceDisplay">{{ number_format($kid->allowance_amount, 2) }}</span><br><small style="font-size: 0.75em; opacity: 0.7;">(weekly allowance)</small></div>
                        </div>

                        <!-- Right: Slider and Info -->
                        <div class="auto-allocation-controls">
                            <div class="slider-container">
                                <input type="range"
                                       id="goalAutoAllocation"
                                       name="auto_allocation_percentage"
                                       value="0"
                                       min="0"
                                       max="100"
                                       step="5"
                                       class="allocation-slider"
                                       style="--slider-color: {{ $kid->color }};"
                                       oninput="updateAllocationDisplay()">
                                <div class="slider-labels">
                                    <span id="sliderMin">0%</span>
                                    <span id="sliderMid">50%</span>
                                    <span id="sliderMax">100%</span>
                                </div>
                            </div>

                            <!-- Allocation Warning/Info -->
                            <div id="allocationInfo" class="allocation-info" style="display: none; margin: 12px 0; padding: 10px 14px; background: #eff6ff; border-left: 3px solid #3b82f6; border-radius: 6px; font-size: 13px; color: #1e40af;">
                                <div id="allocationInfoContent" style="display: flex; align-items: center; gap: 8px;">
                                    <i id="allocationIcon" class="fas fa-info-circle" style="color: #3b82f6;"></i>
                                    <span id="allocationMessage"></span>
                                </div>
                            </div>

                            <!-- Preview Info -->
                            <div id="allocationPreview" class="auto-allocation-preview" style="display: none;">
                                <div id="allocationAmountText"></div>
                                <div id="allocationCompletionText" class="auto-allocation-preview-completion" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="goalPhoto">Goal Photo/Icon</label>
                    <div class="photo-upload-area" id="photoUploadArea">
                        <input type="file" id="goalPhoto" name="photo" accept="image/jpeg,image/png,image/jpg,image/gif" class="photo-input" onchange="handlePhotoSelect(event)">
                        <div class="photo-upload-content" id="photoUploadContent">
                            <div class="photo-upload-placeholder">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Drag and drop an image here, or click to select</p>
                            </div>
                        </div>
                    </div>
                    <div id="photoError" style="display: none; margin-top: 8px; padding: 8px 12px; background: #fee2e2; color: #dc2626; border-radius: 6px; font-size: 13px; font-weight: 500;"></div>
                    <button type="button" id="removePhotoBtn" onclick="removePhoto()" class="btn-remove-photo" style="display: none;">Remove Photo</button>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="closeParentGoalModal()" class="btn-secondary">Cancel</button>
                    <button type="button" id="deleteGoalBtn" onclick="deleteParentGoal()" class="btn-danger" style="display: none;">Delete Goal</button>
                    <button type="submit" id="submitGoalBtn" class="btn-primary">Create Goal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="modal-overlay" onclick="if(event.target === this) closeDeleteConfirmModal()" style="z-index: 10001;">
        <div class="modal-container" onclick="event.stopPropagation()" style="max-width: 500px;">
            <div class="modal-header">
                <h2>Delete Goal</h2>
                <button onclick="closeDeleteConfirmModal()" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <div style="display: flex; align-items: flex-start; gap: 16px; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle" style="color: #f59e0b; font-size: 32px; flex-shrink: 0; margin-top: 4px;"></i>
                    <div>
                        <p style="margin: 0 0 12px 0; font-size: 16px; font-weight: 600; color: #1a1a1a;">Are you sure you want to delete this goal?</p>
                        <p style="margin: 0; font-size: 14px; color: #666;">Any funds in this goal will be returned to the main account. This action cannot be undone.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 12px; padding: 16px 24px; border-top: 1px solid #e5e7eb;">
                <button type="button" onclick="closeDeleteConfirmModal()" class="btn-secondary">Cancel</button>
                <button type="button" onclick="confirmDeleteGoal()" class="btn-danger">Delete Goal</button>
            </div>
        </div>
    </div>


    <script>
        // Global variables for parent goal modal
        const WEEKLY_ALLOWANCE = {{ $kid->allowance_amount }};
        const TOTAL_ALLOCATED = {{ $totalAllocated }};
        let isEditMode = false;
        let currentGoalId = null;
        let currentGoalAllocation = 0; // Track current goal's allocation when editing
        let photoFile = null;

        // Calculate max allowed allocation based on mode
        function getMaxAllowedAllocation() {
            if (isEditMode) {
                const otherGoalsAllocation = TOTAL_ALLOCATED - currentGoalAllocation;
                return 100 - otherGoalsAllocation;
            } else {
                return 100 - TOTAL_ALLOCATED;
            }
        }

        // Check if current allocation exceeds limit
        function allocationExceedsLimit() {
            const currentPercentage = parseInt(document.getElementById('goalAutoAllocation').value) || 0;
            const maxAllowed = getMaxAllowedAllocation();
            return currentPercentage > maxAllowed;
        }

        // Currency formatting function (converts 1100 -> $11.00)
        function formatCurrencyInput(value) {
            let numValue = value.replace(/[^0-9]/g, '');
            if (numValue === '') return '';
            let cents = parseInt(numValue);
            let dollars = (cents / 100).toFixed(2);
            return '$' + dollars;
        }

        function openCreateGoalModal() {
            isEditMode = false;
            currentGoalId = null;
            resetGoalForm();
            document.getElementById('goalModalTitle').textContent = 'Create Goal for {{ $kid->name }}';
            document.getElementById('submitGoalBtn').textContent = 'Create Goal';
            document.getElementById('deleteGoalBtn').style.display = 'none';
            document.getElementById('goalFormMethod').value = '';
            document.getElementById('parentGoalModal').classList.add('active');
        }

        function openEditGoalModal(goalId) {
            isEditMode = true;
            currentGoalId = goalId;
            resetGoalForm();
            document.getElementById('goalModalTitle').textContent = 'Edit Goal for {{ $kid->name }}';
            document.getElementById('submitGoalBtn').textContent = 'Update Goal';
            document.getElementById('deleteGoalBtn').style.display = 'inline-block';
            document.getElementById('goalFormMethod').value = 'PUT';
            document.getElementById('goalFormId').value = goalId;
            loadGoalData(goalId);
            document.getElementById('parentGoalModal').classList.add('active');
        }

        function closeParentGoalModal() {
            document.getElementById('parentGoalModal').classList.remove('active');
            resetGoalForm();
        }

        function resetGoalForm() {
            document.getElementById('parentGoalForm').reset();
            document.getElementById('goalTitle').value = '';
            document.getElementById('goalDescription').value = '';
            document.getElementById('goalProductUrl').value = '';
            document.getElementById('goalTargetAmount').value = '';
            document.getElementById('goalAutoAllocation').value = 0;
            currentGoalAllocation = 0;
            photoFile = null;
            document.getElementById('photoUploadContent').innerHTML = `
                <div class="photo-upload-placeholder">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Drag and drop an image here, or click to select</p>
                </div>
            `;
            document.getElementById('removePhotoBtn').style.display = 'none';
            updateAllocationDisplay();
        }

        function loadGoalData(goalId) {
            fetch(`/goals/${goalId}/edit`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('goalTitle').value = data.title || '';
                document.getElementById('goalDescription').value = data.description || '';
                document.getElementById('goalProductUrl').value = data.product_url || '';
                document.getElementById('goalTargetAmount').value = data.target_amount || '';
                document.getElementById('goalAutoAllocation').value = data.auto_allocation_percentage || 0;
                currentGoalAllocation = parseFloat(data.auto_allocation_percentage) || 0;

                if (data.photo_path) {
                    document.getElementById('photoUploadContent').innerHTML = `
                        <img src="/storage/${data.photo_path}" alt="Preview" class="photo-preview">
                    `;
                    document.getElementById('removePhotoBtn').style.display = 'block';
                }

                updateAllocationDisplay();
            })
            .catch(error => {
                alert('Error loading goal data');
                console.error('Error:', error);
            });
        }

        function updateAllocationDisplay() {
            const percentage = parseInt(document.getElementById('goalAutoAllocation').value) || 0;
            const targetAmount = parseFloat(document.getElementById('goalTargetAmount').value) || 0;
            const maxAllowed = getMaxAllowedAllocation();
            const exceedsLimit = allocationExceedsLimit();

            // Update slider max attribute and labels
            const slider = document.getElementById('goalAutoAllocation');
            slider.setAttribute('max', maxAllowed);
            document.getElementById('sliderMid').textContent = Math.round(maxAllowed / 2) + '%';
            document.getElementById('sliderMax').textContent = maxAllowed + '%';

            // Show/hide allocation warning
            const allocationInfo = document.getElementById('allocationInfo');
            const allocationIcon = document.getElementById('allocationIcon');
            const allocationMessage = document.getElementById('allocationMessage');
            const allocationInfoContent = document.getElementById('allocationInfoContent');

            if (maxAllowed < 100) {
                allocationInfo.style.display = 'block';
                if (exceedsLimit) {
                    const excessAmount = percentage - maxAllowed;
                    allocationIcon.className = 'fas fa-exclamation-circle';
                    allocationIcon.style.color = '#dc2626';
                    allocationInfoContent.style.color = '#dc2626';
                    allocationMessage.innerHTML = `Exceeds limit by <strong>${excessAmount}</strong>% — Max allowed: <strong>${maxAllowed}</strong>%`;
                } else {
                    allocationIcon.className = 'fas fa-info-circle';
                    allocationIcon.style.color = '#3b82f6';
                    allocationInfoContent.style.color = '#1e40af';
                    allocationMessage.innerHTML = `Only <strong>${maxAllowed}</strong>% allocation remains available`;
                }
            } else {
                allocationInfo.style.display = 'none';
            }

            // Update submit button state
            const submitBtn = document.getElementById('submitGoalBtn');
            if (exceedsLimit) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Allocation Exceeds Limit';
                submitBtn.style.opacity = '0.6';
            } else {
                submitBtn.disabled = false;
                submitBtn.textContent = isEditMode ? 'Update Goal' : 'Create Goal';
                submitBtn.style.opacity = '1';
            }

            // Update percentage text
            document.getElementById('allocationPercentText').textContent = percentage + '%';

            // Update circle progress
            const circumference = 2 * 3.14159 * 40;
            const offset = circumference * (1 - percentage / 100);
            document.getElementById('allocationCircle').setAttribute('stroke-dashoffset', offset);

            // Calculate and show allocation amount
            const allocationAmount = (WEEKLY_ALLOWANCE * percentage) / 100;

            if (allocationAmount > 0) {
                document.getElementById('allocationPreview').style.display = 'block';
                document.getElementById('allocationAmountText').textContent =
                    '$' + allocationAmount.toFixed(2) + ' per week goes to this goal';

                // Calculate weeks to complete
                if (targetAmount > 0) {
                    const weeksToComplete = Math.ceil(targetAmount / allocationAmount);
                    let timeText = '';

                    if (weeksToComplete > 51) {
                        const months = Math.round(weeksToComplete / 4.33);
                        timeText = months + ' month' + (months !== 1 ? 's' : '');
                    } else {
                        timeText = weeksToComplete + ' week' + (weeksToComplete !== 1 ? 's' : '');
                    }

                    const today = new Date();
                    const completionDate = new Date(today.getTime() + (weeksToComplete * 7 * 24 * 60 * 60 * 1000));
                    const dateString = completionDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

                    document.getElementById('allocationCompletionText').textContent =
                        'Goal completes in ~' + timeText + ' (' + dateString + ')';
                    document.getElementById('allocationCompletionText').style.display = 'block';
                } else {
                    document.getElementById('allocationCompletionText').style.display = 'none';
                }
            } else {
                document.getElementById('allocationPreview').style.display = 'none';
            }
        }

        function showPhotoError(message) {
            const errorDiv = document.getElementById('photoError');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }

        function hidePhotoError() {
            const errorDiv = document.getElementById('photoError');
            errorDiv.style.display = 'none';
        }

        function handlePhotoSelect(event) {
            hidePhotoError();
            const file = event.target.files[0];
            if (file) {
                // Check file size (2MB limit)
                const maxSize = 2 * 1024 * 1024; // 2MB in bytes
                if (file.size > maxSize) {
                    showPhotoError('Image is too large. Please choose an image smaller than 2MB.');
                    event.target.value = ''; // Clear the input
                    return;
                }

                photoFile = file;
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('photoUploadContent').innerHTML = `
                        <img src="${e.target.result}" alt="Preview" class="photo-preview">
                    `;
                    document.getElementById('removePhotoBtn').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        function removePhoto() {
            photoFile = null;
            document.getElementById('goalPhoto').value = '';
            document.getElementById('photoUploadContent').innerHTML = `
                <div class="photo-upload-placeholder">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Drag and drop an image here, or click to select</p>
                </div>
            `;
            document.getElementById('removePhotoBtn').style.display = 'none';
            hidePhotoError();
        }

        // URL Scraping for Goals
        let isScrapingGoal = false;

        function scrapeGoalUrl() {
            const url = document.getElementById('goalProductUrl').value.trim();
            const scrapeBtn = document.getElementById('scrapeGoalBtn');
            const scrapeError = document.getElementById('scrapeGoalError');
            const scrapePartialSuccess = document.getElementById('scrapeGoalPartialSuccess');

            scrapeError.style.display = 'none';
            scrapePartialSuccess.style.display = 'none';

            if (!url) {
                scrapeError.textContent = 'Please enter a URL first';
                scrapeError.style.display = 'block';
                return;
            }

            if (isScrapingGoal) return;

            isScrapingGoal = true;
            scrapeBtn.disabled = true;
            scrapeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

            fetch('{{ route("parent.goals.scrape-url", $kid) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ url: url })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Server returned error: ' + response.status);
                }
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server did not return JSON. This may be a routing or authentication issue.');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (data.data.title) {
                        document.getElementById('goalTitle').value = data.data.title;
                    }
                    if (data.data.price) {
                        const formattedPrice = '$' + parseFloat(data.data.price).toFixed(2);
                        document.getElementById('goalTargetAmount').value = formattedPrice;
                    }
                    if (data.data.image_url) {
                        // Load scraped image into photo preview
                        document.getElementById('photoUploadContent').innerHTML = `
                            <img src="${data.data.image_url}" alt="Preview" class="photo-preview">
                        `;
                        document.getElementById('removePhotoBtn').style.display = 'block';

                        // Convert image URL to blob and store as photo file
                        fetch(data.data.image_url)
                            .then(res => res.blob())
                            .then(blob => {
                                const fileName = 'scraped-image.jpg';
                                photoFile = new File([blob], fileName, { type: blob.type });
                            })
                            .catch(err => console.log('Could not convert image to file:', err));
                    }

                    if (data.partial) {
                        document.getElementById('scrapeGoalPartialMessage').textContent = data.message;
                        scrapePartialSuccess.style.display = 'block';
                    }

                    // Trigger allocation display update in case price changed
                    updateAllocationDisplay();
                } else {
                    scrapeError.textContent = data.message || 'Failed to auto-fill from URL';
                    scrapeError.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Scrape error:', error);
                scrapeError.textContent = 'Failed to auto-fill. Please enter details manually.';
                scrapeError.style.display = 'block';
            })
            .finally(() => {
                isScrapingGoal = false;
                scrapeBtn.disabled = false;
                scrapeBtn.innerHTML = '<i class="fas fa-magic"></i> Auto-fill';
            });
        }

        function submitParentGoalForm(event) {
            event.preventDefault();

            const submitBtn = document.getElementById('submitGoalBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';

            const formData = new FormData(document.getElementById('parentGoalForm'));

            // Convert formatted currency ($11.00) back to decimal (11.00)
            const targetAmountInput = document.getElementById('goalTargetAmount');
            const targetAmount = targetAmountInput.value.replace(/[^0-9.]/g, '');
            formData.set('target_amount', targetAmount);

            if (photoFile) {
                formData.set('photo', photoFile);
            }

            let url, method;
            if (isEditMode && currentGoalId) {
                url = `/goals/${currentGoalId}`;
                method = 'POST';
            } else {
                url = '{{ route('parent.goals.store', $kid) }}';
                method = 'POST';
            }

            fetch(url, {
                method: method,
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                }
            })
            .then(response => {
                if (response.status === 413) {
                    throw new Error('Image file is too large. Please choose an image smaller than 2MB.');
                }
                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }
                if (!response.ok) {
                    throw new Error('Server error occurred');
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    window.location.reload();
                } else if (data && data.error) {
                    showPhotoError(data.error);
                    submitBtn.disabled = false;
                    submitBtn.textContent = isEditMode ? 'Update Goal' : 'Create Goal';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showPhotoError(error.message || 'An error occurred. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = isEditMode ? 'Update Goal' : 'Create Goal';
            });
        }

        function deleteParentGoal() {
            const modal = document.getElementById('deleteConfirmModal');
            if (modal) {
                modal.style.display = 'flex';
                modal.style.visibility = 'visible';
                modal.style.opacity = '1';
            }
        }

        function closeDeleteConfirmModal() {
            document.getElementById('deleteConfirmModal').style.display = 'none';
        }

        function confirmDeleteGoal() {
            closeDeleteConfirmModal();

            fetch(`/goals/${currentGoalId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
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
                } else {
                    alert(data.error || 'Error deleting goal');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || 'An unexpected error occurred. Please try again.');
            });
        }

        // Check for edit query parameter on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Hide delete confirmation modal initially
            const deleteModal = document.getElementById('deleteConfirmModal');
            if (deleteModal) {
                deleteModal.style.display = 'none';
            }

            const urlParams = new URLSearchParams(window.location.search);
            const editGoalId = urlParams.get('edit');
            if (editGoalId) {
                openEditGoalModal(parseInt(editGoalId));
                // Clean up URL without reload
                window.history.replaceState({}, '', '{{ route('kids.goals', $kid) }}');
            }

            // Add drag and drop handlers
            const uploadArea = document.getElementById('photoUploadArea');
            uploadArea.addEventListener('dragover', (e) => e.preventDefault());
            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                hidePhotoError();
                const file = e.dataTransfer.files[0];
                if (file && file.type.startsWith('image/')) {
                    // Check file size (2MB limit)
                    const maxSize = 2 * 1024 * 1024; // 2MB in bytes
                    if (file.size > maxSize) {
                        showPhotoError('Image is too large. Please choose an image smaller than 2MB.');
                        return;
                    }

                    photoFile = file;
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        document.getElementById('photoUploadContent').innerHTML = `
                            <img src="${event.target.result}" alt="Preview" class="photo-preview">
                        `;
                        document.getElementById('removePhotoBtn').style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>

@endsection

<style>
    .kid-info-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px;
        background: white;
        border-radius: 12px;
        border-left: 4px solid;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 32px;
    }

    .kid-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 28px;
        font-weight: 700;
    }

    .kid-details {
        flex: 1;
    }

    .kid-name {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 4px;
    }

    .kid-balance {
        font-size: 16px;
        color: #6b7280;
    }

    .btn-back-dashboard {
        padding: 10px 20px;
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        color: #6b7280;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-back-dashboard:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
    }

    .goals-header-funds {
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        background: white;
        border-radius: 8px;
        border: 2px solid currentColor;
        white-space: nowrap;
    }

    .goals-header-funds i {
        font-size: 12px;
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
        align-items: start;
    }

    .goal-card {
        position: relative;
        background: white;
        border-radius: 12px;
        padding: 24px;
        transition: all 0.3s;
        min-height: 420px;
        display: flex;
        flex-direction: column;
    }

    .goal-card.ready-to-redeem {
        box-shadow: 0 4px 24px rgba(16, 185, 129, 0.4) !important;
    }

    /* Card Header */
    .goal-card-header {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 24px;
    }

    .goal-card-header-left {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        flex: 1;
    }

    .goal-card-title-section {
        flex: 1;
        min-width: 0;
    }

    .goal-card-title {
        font-size: 20px;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 6px 0;
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
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-add-funds:hover {
        background: #059669;
    }

    .btn-remove-funds-link {
        background: none;
        border: none;
        color: #ef4444;
        font-size: 12px;
        font-weight: 400;
        cursor: pointer;
        padding: 3px 12px;
        text-decoration: none;
        transition: opacity 0.2s;
    }

    .btn-remove-funds-link:hover {
        opacity: 0.7;
        text-decoration: underline;
    }

    .goal-card-description {
        font-size: 14px;
        color: #6b7280;
        margin-top: 4px;
        line-height: 1.4;
        margin-bottom: 20px;
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
        background: #10b981;
        color: white;
        padding: 12px 24px;
        border-radius: 12px;
        font-size: 18px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-ask-redeem-subtle {
        background: #10b981;
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-ask-redeem-subtle:hover {
        background: #059669;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
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
        text-decoration: underline;
    }

    /* Inline Add Funds Form */
    .goal-inline-add-funds {
        margin: 16px 0 0 0;
        overflow: hidden;
    }

    .goal-inline-form {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .goal-inline-input {
        flex: 1;
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        transition: border-color 0.2s;
    }

    .goal-inline-input:focus {
        outline: none;
        border-color: #10b981;
    }

    .goal-inline-btn-submit {
        padding: 12px 24px;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.2s;
        min-width: 90px;
    }

    .goal-inline-btn-submit:hover:not(:disabled) {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    .goal-inline-btn-submit:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .goal-inline-error {
        margin-top: 8px;
        padding: 8px 12px;
        background: #fee2e2;
        color: #dc2626;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
    }

    .goal-btn-view-past {
        display: block;
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        text-align: center;
        color: #6b7280;
        font-weight: 600;
        text-decoration: none;
        margin-top: 16px;
        border: 2px solid #e5e7eb;
        background: white;
        transition: all 0.2s;
    }

    .goal-btn-view-past:hover {
        background: #f3f4f6;
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
        background: linear-gradient(135deg, rgba(209, 250, 229, 0.3) 0%, rgba(255, 255, 255, 0.95) 100%);
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

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal-backdrop {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        position: relative;
        background: white;
        border-radius: 12px;
        padding: 32px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .modal-header h3 {
        font-size: 20px;
        font-weight: 700;
        color: #1f2937;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 32px;
        color: #9ca3af;
        cursor: pointer;
        line-height: 1;
    }

    .modal-close:hover {
        color: #6b7280;
    }

    .btn-modal-close {
        width: 100%;
        padding: 12px;
        background: #6b7280;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 16px;
    }

    .btn-modal-close:hover {
        background: #4b5563;
    }

    /* Modal Styles */
    [x-cloak] { display: none !important; }

    .modal-overlay {
        display: flex;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        padding: 20px;
        align-items: center;
        justify-content: center;
    }

    .modal-container {
        background: white;
        border-radius: 16px;
        max-width: 600px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 24px 24px 16px 24px;
        border-bottom: 2px solid #e5e7eb;
    }

    .modal-header h2 {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        color: #9ca3af;
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: all 0.2s;
    }

    .modal-close:hover {
        background: #f3f4f6;
        color: #6b7280;
    }

    .modal-form {
        padding: 24px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }

    .form-help {
        display: block;
        font-size: 13px;
        color: #6b7280;
        font-weight: 400;
    }

    .form-input {
        width: 100%;
        padding: 10px 12px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 15px;
        transition: border-color 0.2s;
    }

    .form-input:focus {
        outline: none;
        border-color: {{ $kid->color }};
    }

    /* URL Scraping Styles */
    .input-with-button {
        display: flex;
        gap: 8px;
    }

    .input-with-button .form-input {
        flex: 1;
    }

    .btn-scrape {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: background 0.2s;
    }

    .btn-scrape:hover {
        background: #2563eb;
    }

    .btn-scrape:disabled {
        background: #9ca3af;
        cursor: not-allowed;
    }

    .input-hint {
        font-size: 13px;
        color: #6b7280;
        margin-top: 6px;
        margin-bottom: 0;
    }

    .input-error {
        font-size: 13px;
        color: #ef4444;
        margin-top: 6px;
        background: #fee2e2;
        padding: 8px 12px;
        border-radius: 6px;
        border: 1px solid #fca5a5;
    }

    /* Auto-Allocation Styles */
    .auto-allocation-visual {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: 24px;
        align-items: center;
        padding: 16px;
        background: #f9fafb;
        border-radius: 8px;
    }

    .auto-allocation-circle {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }

    .allocation-circle-svg {
        width: 100px;
        height: 100px;
    }

    .allocation-circle-percentage {
        font-size: 18px;
        font-weight: 700;
        fill: #1f2937;
    }

    .allocation-circle-label {
        font-size: 12px;
        color: #6b7280;
        text-align: center;
        font-weight: 600;
    }

    .auto-allocation-controls {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .slider-container {
        flex: 1;
    }

    .allocation-slider {
        width: 100%;
        height: 8px;
        -webkit-appearance: none;
        appearance: none;
        background: #e5e7eb;
        border-radius: 4px;
        outline: none;
    }

    .allocation-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--slider-color, {{ $kid->color }});
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .allocation-slider::-moz-range-thumb {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--slider-color, {{ $kid->color }});
        cursor: pointer;
        border: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .slider-labels {
        display: flex;
        justify-content: space-between;
        margin-top: 4px;
        font-size: 11px;
        color: #9ca3af;
    }

    .auto-allocation-preview {
        padding: 12px;
        background: white;
        border-radius: 8px;
        font-size: 13px;
        color: #059669;
        font-weight: 600;
        border: 1px solid #d1fae5;
    }

    .auto-allocation-preview-completion {
        margin-top: 4px;
        font-size: 12px;
        color: #6b7280;
    }

    /* Photo Upload Styles */
    .photo-upload-area {
        position: relative;
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
    }

    .photo-upload-area:hover {
        border-color: {{ $kid->color }};
        background: #f9fafb;
    }

    .photo-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .photo-upload-content {
        position: relative;
        z-index: 1;
        pointer-events: none;
    }

    .photo-preview {
        max-width: 200px;
        max-height: 200px;
        border-radius: 8px;
        object-fit: cover;
    }

    .photo-upload-placeholder {
        color: #9ca3af;
    }

    .photo-upload-placeholder i {
        font-size: 48px;
        margin-bottom: 12px;
        display: block;
    }

    .photo-upload-placeholder p {
        font-size: 14px;
        margin: 0;
    }

    .btn-remove-photo {
        margin-top: 8px;
        padding: 6px 12px;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-remove-photo:hover {
        background: #dc2626;
    }

    /* Modal Footer */
    .modal-footer {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        padding-top: 20px;
        border-top: 2px solid #e5e7eb;
    }

    .btn-secondary {
        padding: 10px 20px;
        background: #f3f4f6;
        color: #6b7280;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
    }

    .btn-primary {
        padding: 10px 20px;
        background: {{ $kid->color }};
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary:hover {
        background: color-mix(in srgb, {{ $kid->color }} 85%, black);
    }

    .btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-danger {
        padding: 10px 20px;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-danger:hover {
        background: #dc2626;
    }

    @media (max-width: 768px) {
        .goals-tabs {
            flex: 1;
        }

        .goals-tab {
            flex: 1;
            justify-content: center;
            font-size: 13px;
            padding: 8px 12px;
        }

        .btn-add-goal {
            font-size: 13px;
            padding: 8px 14px;
        }

        .goals-header-funds {
            display: none !important;
        }

        .kid-info-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .btn-back-dashboard {
            width: 100%;
            text-align: center;
        }

        .goals-grid {
            grid-template-columns: 1fr;
        }

        .goal-progress-section {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .goal-beaker-circle {
            margin: 16px auto;
        }

        .auto-allocation-visual {
            grid-template-columns: 1fr;
        }

        .modal-footer {
            flex-direction: column;
        }

        .modal-footer button {
            width: 100%;
        }
    }
</style>

<!-- Redeem/Approve Confirmation Modal -->
<div id="redeemConfirmModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 24px; max-width: 440px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-gift" style="color: white; font-size: 24px;"></i>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 18px; font-weight: 600; color: #111827;">Redeem Goal</h3>
                <p id="redeemGoalTitle" style="margin: 4px 0 0 0; font-size: 14px; color: #6b7280;"></p>
            </div>
        </div>
        <p id="redeemConfirmMessage" style="margin: 16px 0; color: #374151; font-size: 14px; line-height: 1.5;"></p>
        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button onclick="closeRedeemConfirmation()" style="flex: 1; padding: 10px 16px; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                Cancel
            </button>
            <button onclick="confirmRedeem()" style="flex: 1; padding: 10px 16px; background: #10b981; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                <i class="fas fa-check"></i> Confirm Redemption
            </button>
        </div>
    </div>
</div>

<!-- Transaction History Modal -->
<div id="transactionHistoryModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 24px; max-width: 700px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
            <h3 style="font-size: 20px; font-weight: 700; color: #1f2937; margin: 0;">
                <i class="fas fa-history" style="color: #6b7280; margin-right: 8px;"></i>
                Transaction History
            </h3>
            <button onclick="closeTransactionHistory()" style="width: 32px; height: 32px; border-radius: 50%; border: none; background: #f3f4f6; color: #6b7280; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                        <th style="padding: 12px; text-align: left; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase;">Date</th>
                        <th style="padding: 12px; text-align: left; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase;">Time</th>
                        <th style="padding: 12px; text-align: left; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase;">Type</th>
                        <th style="padding: 12px; text-align: left; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase;">Amount</th>
                        <th style="padding: 12px; text-align: left; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase;">By</th>
                    </tr>
                </thead>
                <tbody id="transactionHistoryTableBody">
                    <!-- Rows will be populated by JavaScript -->
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px; text-align: right;">
            <button onclick="closeTransactionHistory()" style="padding: 10px 20px; background: #6b7280; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                Close
            </button>
        </div>
    </div>
</div>

<script>
    let currentRedeemFormId = null;

    function showRedeemConfirmation(goalId, kidName, goalTitle, amount) {
        currentRedeemFormId = 'redeem-form-' + goalId;
        document.getElementById('redeemGoalTitle').textContent = goalTitle;
        document.getElementById('redeemConfirmMessage').textContent =
            `This confirms that ${kidName} has received their item. The funds ($${amount}) will remain locked in the goal as a permanent record of the purchase.`;
        document.getElementById('redeemConfirmModal').style.display = 'flex';
    }

    function showApproveConfirmation(goalId, kidName, goalTitle, amount) {
        currentRedeemFormId = 'approve-form-' + goalId;
        document.getElementById('redeemGoalTitle').textContent = goalTitle;
        document.getElementById('redeemConfirmMessage').textContent =
            `This confirms that ${kidName} has received their item. The funds ($${amount}) will remain locked in the goal as a permanent record of the purchase.`;
        document.getElementById('redeemConfirmModal').style.display = 'flex';
    }

    function closeRedeemConfirmation() {
        document.getElementById('redeemConfirmModal').style.display = 'none';
        currentRedeemFormId = null;
    }

    function confirmRedeem() {
        if (currentRedeemFormId) {
            document.getElementById(currentRedeemFormId).submit();
        }
    }

    // Close modal on backdrop click
    document.getElementById('redeemConfirmModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeRedeemConfirmation();
        }
    });

    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('redeemConfirmModal').style.display === 'flex') {
            closeRedeemConfirmation();
        }
    });

    // Transaction History Modal Functions
    const goalTransactions = {
        @foreach($completedGoals as $goal)
        {{ $goal->id }}: [
            @foreach($goal->goalTransactions()->orderBy('created_at', 'desc')->get() as $transaction)
            {
                date: '{{ $transaction->created_at->format("M j, Y") }}',
                time: '{{ $transaction->created_at->format("g:i A") }}',
                type: '{{ ucfirst(str_replace("_", " ", $transaction->transaction_type)) }}',
                amount: '{{ $transaction->isDeposit() ? "+" : "-" }}${{ number_format(abs($transaction->amount), 2) }}',
                performedBy: '{{ $transaction->performedBy ? $transaction->performedBy->name : ($transaction->transaction_type === "auto_allocation" ? "System" : $transaction->kid->name) }}',
                description: '{{ $transaction->description }}',
                isDeposit: {{ $transaction->isDeposit() ? 'true' : 'false' }}
            }{{ $loop->last ? '' : ',' }}
            @endforeach
        ]{{ $loop->last ? '' : ',' }}
        @endforeach
    };

    function showTransactionHistory(goalId) {
        const transactions = goalTransactions[goalId] || [];
        const modal = document.getElementById('transactionHistoryModal');
        const tbody = document.getElementById('transactionHistoryTableBody');

        // Clear existing rows
        tbody.innerHTML = '';

        // Populate table
        if (transactions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #6b7280;">No transactions found</td></tr>';
        } else {
            transactions.forEach(transaction => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;">${transaction.date}</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;">${transaction.time}</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;">${transaction.type}</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; color: ${transaction.isDeposit ? '#10b981' : '#ef4444'}; font-weight: 600;">${transaction.amount}</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;">${transaction.performedBy}</td>
                `;
                tbody.appendChild(row);
            });
        }

        modal.style.display = 'flex';
    }

    function closeTransactionHistory() {
        document.getElementById('transactionHistoryModal').style.display = 'none';
    }

    // Close transaction history modal on backdrop click
    document.getElementById('transactionHistoryModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeTransactionHistory();
        }
    });

    // Close transaction history modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('transactionHistoryModal')?.style.display === 'flex') {
            closeTransactionHistory();
        }
    });
</script>
