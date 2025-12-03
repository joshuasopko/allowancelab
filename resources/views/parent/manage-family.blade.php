@extends('layouts.parent')

@section('title', 'Manage Family - AllowanceLab')

@section('content')
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
                    <h2>User Accounts Content</h2>
                    <p>List of family members will go here</p>
                </div>
            @endif

            <!-- Kids Accounts Tab -->
            <div class="tab-content {{ !$isOwner ? 'active' : '' }}" id="kids-accounts-tab">
                <h2>Kids Accounts Content</h2>
                <p>List of kids will go here</p>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Remove active class from all tabs and content
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

            // Add active class to clicked tab and corresponding content
            event.target.classList.add('active');
            document.getElementById(tabName + '-tab').classList.add('active');
        }

        function toggleCoParentForm() {
            const form = document.getElementById('coParentForm');
            const button = document.querySelector('#coParentFormContainer .invite-toggle-btn');

            if (form.style.display === 'none') {
                form.style.display = 'block';
                button.style.display = 'none';
            } else {
                form.style.display = 'none';
                button.style.display = 'block';
            }
        }
    </script>
@endsection