<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage {{ $kid->name }} - AllowanceLab</title>
    @vite('resources/css/dashboard.css')
</head>

<body>
    <!-- Header -->
    <div class="top-header">
        <div class="header-left">
            <div class="logo-section">
                <div class="logo">🧪</div>
                <div class="brand-name">AllowanceLab</div>
            </div>
            <div class="header-nav">
                <a href="#">Chore List</a>
                <a href="#">Goals</a>
                <a href="#">Loans</a>
                <a href="#">Jobs</a>
            </div>
        </div>
        <div class="header-right">
            <a href="{{ route('dashboard') }}" class="back-btn">← Back to Dashboard</a>
        </div>
    </div>

    <!-- Dashboard Container -->
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-welcome">Welcome, {{ Auth::user()->name }}!</div>

            <div class="sidebar-menu">
                <div class="menu-item has-subtext">
                    Account Info
                    <div class="menu-subtext">{{ Auth::user()->name }} Family</div>
                </div>
                <a href="{{ route('dashboard') }}" class="menu-item">Dashboard</a>
                <div class="menu-item active">Manage {{ $kid->name }}</div>
                <div class="menu-item">Settings</div>
                <div class="menu-item">Billing</div>
                <div class="menu-item">Help</div>
                <div class="menu-divider"></div>
                <div class="menu-item">Family Settings</div>
                <div class="menu-item">Preferences</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="menu-item sign-out"
                        style="width: 100%; text-align: left; background: none; border: none; cursor: pointer; font-size: 15px; font-weight: 500;">Sign
                        Out</button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-wrapper">
                <!-- Page Header -->
                <div class="manage-header">
                    <div class="manage-kid-info">
                        <div class="avatar" style="background: {{ $kid->color }};">
                            {{ strtoupper(substr($kid->name, 0, 1)) }}
                        </div>
                        <div>
                            <h1 class="manage-title">Manage {{ $kid->name }}</h1>
                            <div class="manage-subtitle">Edit profile, settings, and more</div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="manage-tabs">
                    <button class="manage-tab active" onclick="switchTab('profile')">Profile</button>
                    <button class="manage-tab" onclick="switchTab('chores')" disabled>Chores</button>
                    <button class="manage-tab" onclick="switchTab('goals')" disabled>Goals</button>
                    <button class="manage-tab" onclick="switchTab('loans')" disabled>Loans</button>
                </div>

                <!-- Profile Tab Content -->
                <div class="tab-content" id="profileTab">
                    <form action="{{ route('kids.update', $kid) }}" method="POST" class="manage-form">
                        @csrf
                        @method('PATCH')

                        <!-- Basic Info Section -->
                        <div class="form-section">
                            <h3 class="section-title">Basic Information</h3>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-input" name="name" value="{{ $kid->name }}" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Birthday</label>
                                    <input type="date" class="form-input" name="birthday"
                                        value="{{ $kid->birthday->format('Y-m-d') }}" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-input" value="{{ $kid->username }}" readonly
                                        disabled>
                                    <small style="color: #666;">Username cannot be changed</small>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Password</label>
                                    <input type="password" class="form-input" value="********" readonly disabled>
                                    <small style="color: #666;">Password hidden for security</small>
                                </div>
                            </div>
                        </div>

                        <!-- Appearance Section -->
                        <div class="form-section">
                            <h3 class="section-title">Appearance</h3>

                            <div class="form-group">
                                <label class="form-label">Avatar Color</label>
                                <div class="color-grid">
                                    <div class="color-option {{ $kid->color == '#ff9999' ? 'selected' : '' }}"
                                        style="background: #ff9999;" onclick="selectColorManage('#ff9999')"></div>
                                    <div class="color-option {{ $kid->color == '#ffcc99' ? 'selected' : '' }}"
                                        style="background: #ffcc99;" onclick="selectColorManage('#ffcc99')"></div>
                                    <div class="color-option {{ $kid->color == '#ffff99' ? 'selected' : '' }}"
                                        style="background: #ffff99;" onclick="selectColorManage('#ffff99')"></div>
                                    <div class="color-option {{ $kid->color == '#99ff99' ? 'selected' : '' }}"
                                        style="background: #99ff99;" onclick="selectColorManage('#99ff99')"></div>
                                    <div class="color-option {{ $kid->color == '#99ccff' ? 'selected' : '' }}"
                                        style="background: #99ccff;" onclick="selectColorManage('#99ccff')"></div>
                                    <div class="color-option {{ $kid->color == '#cc99ff' ? 'selected' : '' }}"
                                        style="background: #cc99ff;" onclick="selectColorManage('#cc99ff')"></div>
                                </div>
                                <input type="hidden" name="color" id="colorInputManage" value="{{ $kid->color }}">
                            </div>
                        </div>

                        <!-- Allowance Section -->
                        <div class="form-section">
                            <h3 class="section-title">Allowance Settings</h3>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Weekly Allowance</label>
                                    <input type="text" inputmode="decimal" class="form-input" name="allowance_amount"
                                        value="{{ number_format($kid->allowance_amount, 2) }}" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Allowance Day</label>
                                    <select class="form-input" name="allowance_day" required>
                                        <option value="monday" {{ $kid->allowance_day == 'monday' ? 'selected' : '' }}>
                                            Monday</option>
                                        <option value="tuesday" {{ $kid->allowance_day == 'tuesday' ? 'selected' : '' }}>
                                            Tuesday</option>
                                        <option value="wednesday" {{ $kid->allowance_day == 'wednesday' ? 'selected' : '' }}>Wednesday</option>
                                        <option value="thursday" {{ $kid->allowance_day == 'thursday' ? 'selected' : '' }}>Thursday</option>
                                        <option value="friday" {{ $kid->allowance_day == 'friday' ? 'selected' : '' }}>
                                            Friday</option>
                                        <option value="saturday" {{ $kid->allowance_day == 'saturday' ? 'selected' : '' }}>Saturday</option>
                                        <option value="sunday" {{ $kid->allowance_day == 'sunday' ? 'selected' : '' }}>
                                            Sunday</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Points Section -->
                        <div class="form-section">
                            <h3 class="section-title">Points System</h3>

                            <div class="checkbox-group">
                                <input type="checkbox" name="points_enabled" id="usePointsManage" class="checkbox-input"
                                    value="1" {{ $kid->points_enabled ? 'checked' : '' }}
                                    onchange="toggleMaxPointsManage()">
                                <label class="checkbox-label" for="usePointsManage">
                                    Use point system?
                                </label>
                            </div>

                            <div class="form-group max-points-group" id="maxPointsGroupManage"
                                style="{{ $kid->points_enabled ? '' : 'display: none;' }}">
                                <label class="form-label">Max Points (per week)</label>
                                <input type="number" class="form-input" name="max_points" value="{{ $kid->max_points }}"
                                    min="1" max="100">
                                <small style="color: #666;">Recommended: 10 points</small>
                            </div>
                        </div>

                        <!-- Save Button -->
                        <div class="form-actions">
                            <button type="submit" class="save-btn">Save Changes</button>
                        </div>
                    </form>

                    <!-- Danger Zone -->
                    <div class="form-section danger-zone">
                        <h3 class="section-title" style="color: #f44336;">Danger Zone</h3>
                        <p style="color: #666; margin-bottom: 16px;">Permanently remove this kid and all their data.
                            This action cannot be undone.</p>
                        <button type="button" class="delete-btn" onclick="confirmDeleteKid()">Delete
                            {{ $kid->name }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal-container" style="max-width: 400px;">
            <div class="modal-header">
                <h2 class="modal-title">Delete {{ $kid->name }}?</h2>
                <button class="close-btn" onclick="closeDeleteModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p style="color: #666;">This will permanently delete {{ $kid->name }} and all their transaction history,
                    point adjustments, and data.</p>
                <p style="color: #f44336; font-weight: 600; margin-top: 16px;">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <form action="{{ route('kids.destroy', $kid) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="modal-btn" style="background: #f44336; color: white;">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Tab switching
        function switchTab(tabName) {
            // For now, only profile tab is active
            // Future tabs will be enabled as features are built
        }

        // Color selection for manage page
        function selectColorManage(color) {
            document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('selected'));
            event.target.classList.add('selected');
            document.getElementById('colorInputManage').value = color;
        }

        // Toggle max points visibility
        function toggleMaxPointsManage() {
            const checkbox = document.getElementById('usePointsManage');
            const maxPointsGroup = document.getElementById('maxPointsGroupManage');

            if (checkbox.checked) {
                maxPointsGroup.style.display = 'block';
            } else {
                maxPointsGroup.style.display = 'none';
            }
        }

        // Delete confirmation
        function confirmDeleteKid() {
            document.getElementById('deleteModal').classList.add('active');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        // Close modal on backdrop click
        document.getElementById('deleteModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
    </script>
</body>

</html>