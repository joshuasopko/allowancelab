@extends('layouts.parent')

@section('title', 'Manage Family - AllowanceLab')

@section('header-right')
    <a href="{{ route('dashboard') }}" class="back-to-dashboard-btn">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
@endsection

@section('content')
    <!-- Mobile Back Button (Top) -->
    <a href="{{ route('dashboard') }}" class="mobile-back-link mobile-back-top">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
    <div class="manage-family-container">
        <div class="manage-family-header">
            <h1>Manage Family</h1>
        </div>

        <!-- Tab Navigation -->
        @if($isOwner)
            <div class="manage-family-tabs">
                <button class="tab-btn active" onclick="switchTab('invite-users')">Invite Users</button>
                <button class="tab-btn" onclick="switchTab('user-accounts')">User Accounts</button>
                <button class="tab-btn" onclick="switchTab('kids-accounts')">Kids Accounts</button>
            </div>
        @else
            <!-- Co-Parent View: Only Kids Accounts Tab -->
            <div class="manage-family-tabs">
                <button class="tab-btn active" onclick="switchTab('kids-accounts')">Kids Accounts</button>
            </div>
        @endif

        <!-- Tab Content -->
        <div class="tab-content-container">
            @if($isOwner)
                <!-- Invite Users Tab -->
                <div class="tab-content active" id="invite-users-tab">
                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            @foreach($errors->all() as $error)
                                {{ $error }}
                            @endforeach
                        </div>
                    @endif

                    <!-- Co-Parent Invite Section -->
                    <div class="invite-section">
                        <div class="invite-section-header">
                            <h2>Parent Invite</h2>
                            <p class="invite-description">Invite another parent with full access to all kids and features.
                                Parent accounts can manage everything except inviting or removing other family members.</p>
                        </div>

                        <div class="invite-form-container" id="coParentFormContainer">
                            <button class="invite-toggle-btn" onclick="toggleCoParentForm()">
                                <i class="fas fa-user-plus"></i> Invite Parent
                            </button>

                            <form class="invite-form" id="coParentForm" style="display: none;" method="POST"
                                action="{{ route('family.invite') }}">
                                @csrf
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="coParentName">Full Name</label>
                                        <input type="text" id="coParentName" name="name" placeholder="Enter full name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="coParentEmail">Email Address</label>
                                        <input type="email" id="coParentEmail" name="email" placeholder="Enter email address"
                                            required>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button type="button" class="btn-cancel" onclick="toggleCoParentForm()">Cancel</button>
                                    <button type="submit" class="btn-submit">
                                        <i class="fas fa-paper-plane"></i> Send Invitation
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Custom Access Section (Coming Soon) -->
                    <div class="invite-section invite-section-disabled">
                        <div class="invite-section-header">
                            <h2>Managed Access Invite</h2>
                            <p class="invite-description">Invite users with granular permissions to specific kids and features.
                                Perfect for extended family or caregivers who need limited access.</p>
                        </div>

                        <div class="invite-form-container">
                            <button class="invite-toggle-btn" disabled>
                                <i class="fas fa-user-lock"></i> Invite Managed Access User
                            </button>
                            <span class="coming-soon-label">Feature Coming Soon</span>
                        </div>
                    </div>

                </div>

                <!-- User Accounts Tab -->
                <div class="tab-content" id="user-accounts-tab">

                    <!-- Active Members Section -->
                    <div class="accounts-section">
                        <h2 class="section-title">Active Family Members</h2>

                        @if($familyMembers->count() > 0)
                            <div class="accounts-ledger">
                                @foreach($familyMembers as $member)
                                    <div class="account-row">
                                        <div class="account-info">
                                            <div class="account-avatar">
                                                {{ strtoupper(substr($member['name'], 0, 1)) }}
                                            </div>
                                            <div class="account-details">
                                                <div class="account-name">{{ $member['name'] }}</div>
                                                <div class="account-email">{{ $member['email'] }}</div>
                                            </div>
                                        </div>
                                        <div class="account-meta">
                                            <span
                                                class="account-role {{ $member['role'] === 'owner' ? 'role-owner' : 'role-coparent' }}">
                                                {{ $member['role'] === 'owner' ? 'SuperAdmin' : 'Parent Account' }}
                                            </span>
                                            <span class="account-date">Joined
                                                {{ \Carbon\Carbon::parse($member['joined_at'])->format('M j, Y') }}</span>
                                        </div>
                                        <div class="account-actions">
                                            @if($member['role'] !== 'owner' && $member['id'] !== $user->id)
                                                <button class="family-action-btn btn-danger"
                                                    onclick="confirmRemoveMember({{ $member['id'] }}, '{{ $member['name'] }}')">
                                                    <i class="fas fa-user-times"></i> Revoke Access
                                                </button>
                                            @elseif($member['id'] === $user->id)
                                                <span class="you-badge">You</span>
                                            @else
                                                <span class="owner-badge">Owner</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="empty-state">No active members yet.</p>
                        @endif
                    </div>

                    <!-- Pending Invites Section -->
                    @if($pendingInvites->count() > 0)
                        <div class="accounts-section">
                            <h2 class="section-title">Pending Invitations</h2>

                            <div class="accounts-ledger">
                                @foreach($pendingInvites as $invite)
                                    <div class="account-row pending">
                                        <div class="account-info">
                                            <div class="account-avatar pending-avatar">
                                                <i class="fas fa-envelope"></i>
                                            </div>
                                            <div class="account-details">
                                                <div class="account-name">{{ $invite->email }}</div>
                                                <div class="account-email">Invited {{ $invite->created_at->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                        <div class="account-meta">
                                            <span class="account-status status-pending">
                                                <i class="fas fa-clock"></i> Pending
                                            </span>
                                            <span class="account-date">Expires {{ $invite->expires_at->format('M j, Y') }}</span>
                                        </div>
                                        <div class="account-actions">
                                            <button class="family-action-btn btn-secondary" onclick="resendInvite({{ $invite->id }})">
                                                <i class="fas fa-paper-plane"></i> Resend
                                            </button>
                                            <button class="family-action-btn btn-danger-outline"
                                                onclick="cancelInvite({{ $invite->id }})">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            @endif

            <!-- Kids Accounts Tab -->
            <div class="tab-content {{ !$isOwner ? 'active' : '' }}" id="kids-accounts-tab">

                <div class="accounts-section">
                    <h2 class="section-title">Kids in Your Family</h2>

                    @if($kids->count() > 0)
                        <div class="accounts-ledger">
                            @foreach($kids as $kid)
                                <div class="account-row kid-row">
                                    <div class="account-info">
                                        <div class="kid-account-avatar" style="background: {{ $kid->color }};">
                                            {{ strtoupper(substr($kid->name, 0, 1)) }}
                                        </div>
                                        <div class="account-details">
                                            <div class="account-name">{{ $kid->name }}</div>
                                            <div class="kid-meta-row">
                                                <span class="kid-meta-item">Age
                                                    {{ \Carbon\Carbon::parse($kid->birthday)->age }}</span>
                                                <span class="kid-meta-separator">•</span>
                                                <span class="kid-meta-item">Balance: ${{ number_format($kid->balance, 2) }}</span>
                                                @if($kid->points_enabled)
                                                    <span class="kid-meta-separator">•</span>
                                                    <span class="kid-meta-item">{{ $kid->points }}/{{ $kid->max_points }} points</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="account-meta">
                                        <span class="kid-allowance-badge">
                                            ${{ number_format($kid->allowance_amount, 2) }}/{{ ucfirst($kid->allowance_day) }}
                                        </span>
                                    </div>
                                    <div class="account-actions">
                                        <a href="{{ route('kids.manage', $kid) }}" class="family-action-btn btn-primary">
                                            <i class="fas fa-cog"></i> Manage Kid
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="empty-state">No kids added yet. Add your first kid from the dashboard!</p>
                    @endif
                </div>

            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="confirm-modal-overlay" style="display: none;">
        <div class="confirm-modal">
            <div class="confirm-modal-icon" id="confirmIcon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="confirm-modal-title" id="confirmTitle">Confirm Action</h3>
            <p class="confirm-modal-message" id="confirmMessage">Are you sure?</p>
            <div class="confirm-modal-actions">
                <button class="confirm-btn-cancel" onclick="closeConfirmModal()">Cancel</button>
                <button class="confirm-btn-confirm" id="confirmButton">Confirm</button>
            </div>
        </div>

    </div>
    <!-- Mobile Back Button (Top) -->
    <a href="{{ route('dashboard') }}" class="mobile-back-link mobile-back-top">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
    <script src="{{ asset('js/manage-family.js') }}"></script>
@endsection