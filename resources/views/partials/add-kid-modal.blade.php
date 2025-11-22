<!-- Add Kid Modal -->
<div class="modal-overlay" id="addKidModal">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Add a New Kid</h2>
            <button class="close-btn" onclick="closeAddKidModal()">&times;</button>
        </div>
        <form action="{{ route('kids.store') }}" method="POST" id="addKidForm">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-input" name="name" id="kidName" placeholder="First name"
                            required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Birthday</label>
                        <input type="date" class="form-input" name="birthday" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Weekly Allowance</label>
                        <input type="text" inputmode="decimal" class="form-input" name="allowance_amount"
                            placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Allowance Day
                            <span class="tooltip-icon">?
                                <span class="tooltip-text">Select the day of the week to post allowance.</span>
                            </span>
                        </label>
                        <select class="form-input" name="allowance_day" required>
                            <option value="monday">Monday</option>
                            <option value="tuesday">Tuesday</option>
                            <option value="wednesday">Wednesday</option>
                            <option value="thursday">Thursday</option>
                            <option value="friday" selected>Friday</option>
                            <option value="saturday">Saturday</option>
                            <option value="sunday">Sunday</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="avatar-label">Choose Avatar Color</label>
                    <div class="avatar-preview-container">
                        <div id="avatarPreview" class="avatar-preview" style="background: #80d4b0;">?</div>
                        <span id="colorLabel" style="font-size: 15px; color: #666;">Mint Green</span>
                    </div>
                    <input type="hidden" name="color" id="selectedColor" value="#80d4b0">
                    <input type="hidden" name="avatar" value="avatar1">
                    <div class="color-grid">
                        <div class="color-option selected" style="background: #80d4b0;" data-color="#80d4b0"
                            data-name="Mint Green" onclick="selectColor(this)"></div>
                        <div class="color-option" style="background: #ff9999;" data-color="#ff9999" data-name="Coral"
                            onclick="selectColor(this)"></div>
                        <div class="color-option" style="background: #b19cd9;" data-color="#b19cd9" data-name="Lavender"
                            onclick="selectColor(this)"></div>
                        <div class="color-option" style="background: #87ceeb;" data-color="#87ceeb" data-name="Sky Blue"
                            onclick="selectColor(this)"></div>
                        <div class="color-option" style="background: #ffb380;" data-color="#ffb380" data-name="Peach"
                            onclick="selectColor(this)"></div>
                        <div class="color-option" style="background: #e066a6;" data-color="#e066a6" data-name="Magenta"
                            onclick="selectColor(this)"></div>
                        <div class="color-option" style="background: #ffd966;" data-color="#ffd966"
                            data-name="Butter Yellow" onclick="selectColor(this)"></div>
                        <div class="color-option" style="background: #a8c686;" data-color="#a8c686"
                            data-name="Sage Green" onclick="selectColor(this)"></div>
                        <div class="color-option" style="background: #5ab9b3;" data-color="#5ab9b3" data-name="Teal"
                            onclick="selectColor(this)"></div>
                        <div class="color-option" style="background: #9bb7d4;" data-color="#9bb7d4"
                            data-name="Periwinkle" onclick="selectColor(this)"></div>
                        <div class="color-option" style="background: #ff9966;" data-color="#ff9966"
                            data-name="Tangerine" onclick="selectColor(this)"></div>
                        <div class="color-option" style="background: #d4a5d4;" data-color="#d4a5d4" data-name="Lilac"
                            onclick="selectColor(this)"></div>
                    </div>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" name="points_enabled" id="usePoints" class="checkbox-input" value="1" checked
                        onchange="toggleMaxPoints()">
                    <label class="checkbox-label" for="usePoints">
                        Use point system?
                        <span class="tooltip-icon">
                            ?
                            <span class="tooltip-text">Points encourage accountability. Kids start each week with full
                                points.</span>
                        </span>
                    </label>
                </div>

                <div class="form-group max-points-group" id="maxPointsGroup">
                    <label class="form-label">Starting Points (per week)</label>
                    <input type="number" class="form-input" name="max_points" value="10" min="1" max="100">
                    <small style="color: #666; font-size: 12px;">Recommended: 10 points</small>
                </div>

                <!-- Info text about next step -->
                <div
                    style="margin-top: 20px; padding: 15px; background: #f0f9ff; border-radius: 8px; border-left: 4px solid #3b82f6;">
                    <p style="margin: 0; color: #1e40af; font-size: 14px;">
                        <strong>Next Step:</strong> After creating this kid, you'll have the option to send them an
                        invite to create their own account.
                    </p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeAddKidModal()">Cancel</button>
                <button type="submit" class="modal-btn modal-btn-submit">Add Kid</button>
            </div>
        </form>
    </div>
</div>