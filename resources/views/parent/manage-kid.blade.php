@extends('layouts.parent')

@section('title', 'Manage ' . $kid->name . ' - AllowanceLab')

@section('header-right')
    <a href="{{ route('dashboard') }}" class="back-btn">← Back to Dashboard</a>
@endsection

@section('sidebar-active')
    <div class="menu-item active">Manage {{ $kid->name }}</div>
@endsection

@section('content')
    <!-- Page Header -->
    <div class="manage-header">
        <div class="manage-kid-info">
            <div class="avatar" style="background: {{ $kid->color }};">{{ strtoupper(substr($kid->name, 0, 1)) }}</div>
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
                        <input type="date" class="form-input" name="birthday" value="{{ $kid->birthday->format('Y-m-d') }}"
                            required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-input" value="{{ $kid->username }}" readonly disabled>
                        <small style="color: #666; margin-left: 5px;">Username cannot be changed</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-input" value="********" readonly disabled>
                        <small style="color: #666; margin-left: 5px;">Password hidden for security</small>
                    </div>
                </div>
            </div>

            <!-- Appearance Section -->
            <div class="form-section">
                <h3 class="section-title">Appearance</h3>

                <div class="form-group">
                    <label class="form-label">Avatar Color</label>
                    <div class="color-grid">
                        <div class="color-option {{ $kid->color == '#80d4b0' ? 'selected' : '' }}"
                            style="background: #80d4b0;" data-color="#80d4b0" onclick="selectColorManage(this)"></div>
                        <div class="color-option {{ $kid->color == '#ff9999' ? 'selected' : '' }}"
                            style="background: #ff9999;" data-color="#ff9999" onclick="selectColorManage(this)"></div>
                        <div class="color-option {{ $kid->color == '#b19cd9' ? 'selected' : '' }}"
                            style="background: #b19cd9;" data-color="#b19cd9" onclick="selectColorManage(this)"></div>
                        <div class="color-option {{ $kid->color == '#87ceeb' ? 'selected' : '' }}"
                            style="background: #87ceeb;" data-color="#87ceeb" onclick="selectColorManage(this)"></div>
                        <div class="color-option {{ $kid->color == '#ffb380' ? 'selected' : '' }}"
                            style="background: #ffb380;" data-color="#ffb380" onclick="selectColorManage(this)"></div>
                        <div class="color-option {{ $kid->color == '#e066a6' ? 'selected' : '' }}"
                            style="background: #e066a6;" data-color="#e066a6" onclick="selectColorManage(this)"></div>
                        <div class="color-option {{ $kid->color == '#ffd966' ? 'selected' : '' }}"
                            style="background: #ffd966;" data-color="#ffd966" onclick="selectColorManage(this)"></div>
                        <div class="color-option {{ $kid->color == '#a8c686' ? 'selected' : '' }}"
                            style="background: #a8c686;" data-color="#a8c686" onclick="selectColorManage(this)"></div>
                        <div class="color-option {{ $kid->color == '#5ab9b3' ? 'selected' : '' }}"
                            style="background: #5ab9b3;" data-color="#5ab9b3" onclick="selectColorManage(this)"></div>
                        <div class="color-option {{ $kid->color == '#9bb7d4' ? 'selected' : '' }}"
                            style="background: #9bb7d4;" data-color="#9bb7d4" onclick="selectColorManage(this)"></div>
                        <div class="color-option {{ $kid->color == '#ff9966' ? 'selected' : '' }}"
                            style="background: #ff9966;" data-color="#ff9966" onclick="selectColorManage(this)"></div>
                        <div class="color-option {{ $kid->color == '#d4a5d4' ? 'selected' : '' }}"
                            style="background: #d4a5d4;" data-color="#d4a5d4" onclick="selectColorManage(this)"></div>
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
                            <option value="monday" {{ $kid->allowance_day == 'monday' ? 'selected' : '' }}>Monday</option>
                            <option value="tuesday" {{ $kid->allowance_day == 'tuesday' ? 'selected' : '' }}>Tuesday</option>
                            <option value="wednesday" {{ $kid->allowance_day == 'wednesday' ? 'selected' : '' }}>Wednesday
                            </option>
                            <option value="thursday" {{ $kid->allowance_day == 'thursday' ? 'selected' : '' }}>Thursday
                            </option>
                            <option value="friday" {{ $kid->allowance_day == 'friday' ? 'selected' : '' }}>Friday</option>
                            <option value="saturday" {{ $kid->allowance_day == 'saturday' ? 'selected' : '' }}>Saturday
                            </option>
                            <option value="sunday" {{ $kid->allowance_day == 'sunday' ? 'selected' : '' }}>Sunday</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Points Section -->
            <div class="form-section">
                <h3 class="section-title">Points System</h3>

                <div class="checkbox-group">
                    <input type="checkbox" name="points_enabled" id="usePointsManage" class="checkbox-input" value="1" {{ $kid->points_enabled ? 'checked' : '' }} onchange="toggleMaxPointsManage()">
                    <label class="checkbox-label" for="usePointsManage">
                        Use point system?
                    </label>
                </div>

                <div class="form-group max-points-group" id="maxPointsGroupManage"
                    style="{{ $kid->points_enabled ? '' : 'display: none;' }}">
                    <label class="form-label">Max Points (per week)</label>
                    <input type="number" class="form-input" name="max_points" value="{{ $kid->max_points }}" min="1"
                        max="100">
                    <small style="color: #666;">Recommended: 10 points</small>
                </div>
            </div>

            <!-- Save Button -->
            <div class="form-actions">
                <button type="submit" class="save-btn">Save Changes</button>
            </div>
        </form>

        <!-- Remove Account -->
        <div class="form-section danger-zone">
            <h3 class="section-title" style="color: #f44336;">Remove Account</h3>
            <p style="color: #666; margin-bottom: 16px;">Permanently remove this child's account and all their transaction
                history, point adjustments, and data.</p>
            <button type="button" class="delete-btn" onclick="confirmDeleteKid()">Remove Child Account</button>
        </div>
    </div>
@endsection

@section('modals')
    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal-container delete-confirm-modal">
            <div class="modal-header">
                <h2 class="modal-title" style="color: #f44336;">Remove {{ $kid->name }}'s Account?</h2>
                <button class="close-btn" onclick="closeDeleteModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p style="color: #666;">This will permanently remove {{ $kid->name }}'s account and all their transaction
                    history, point adjustments, and data.</p>
                <p style="color: #f44336; font-weight: 600; margin-top: 16px;">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <form action="{{ route('kids.destroy', $kid) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="modal-btn remove-confirm-btn">REMOVE ACCOUNT</button>
                </form>
            </div>
        </div>
    </div>
@endsection