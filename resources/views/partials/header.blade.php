<!-- Header -->
<div class="top-header">
    <div class="header-left">
        <div class="logo-section">
            <img src="{{ asset('/images/Allowance-Lab-logo.png') }}" alt="AllowanceLab" class="logo-img">
        </div>
        <div class="header-nav">
            <a href="#">Chore List</a>
            <a href="#">Goals</a>
            <a href="#">Loans</a>
            <a href="#">Jobs</a>
        </div>
    </div>
    <div class="header-right">
        @hasSection('header-right')
            @yield('header-right')
        @else
            <button class="add-kid-btn" onclick="openAddKidModal()">+ Add Kid</button>
        @endif
    </div>
    <div class="hamburger" onclick="toggleMobileMenu()">
        <span></span>
        <span></span>
        <span></span>
    </div>
</div>