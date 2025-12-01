<!-- Sidebar -->
<div class="sidebar" id="parentSidebar">
    @php
        $nameParts = explode(' ', Auth::user()->name);
        $firstName = ucfirst(strtolower($nameParts[0]));
        $lastName = isset($nameParts[1]) ? ucfirst(strtolower($nameParts[1])) : $firstName;
    @endphp

    <div class="sidebar-welcome">Welcome, {{ $firstName }}!</div>

    <div class="sidebar-menu">
        <a href="#" class="menu-item">
            <div class="menu-item-main">My Account</div>
            <div class="menu-subtext">{{ $lastName }} Family</div>
        </a>

        <a href="{{ route('dashboard') }}"
            class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>

        @hasSection('sidebar-active')
            @yield('sidebar-active')
        @endif

        <div class="menu-item menu-dropdown" onclick="toggleLabTools(event)">
            <span>Lab Tools</span>
            <i class="fas fa-chevron-down dropdown-icon"></i>
        </div>
        <div class="dropdown-content" id="labToolsDropdown">
            <a href="#" class="dropdown-item">
                Chore List
                <span class="coming-soon-badge">Coming Soon</span>
            </a>
            <a href="#" class="dropdown-item">
                Goals
                <span class="coming-soon-badge">Coming Soon</span>
            </a>
            <a href="#" class="dropdown-item">
                Loans
                <span class="coming-soon-badge">Coming Soon</span>
            </a>
            <a href="#" class="dropdown-item">
                Jobs
                <span class="coming-soon-badge">Coming Soon</span>
            </a>
        </div>

        <div class="menu-divider"></div>

        <a href="#" class="menu-item">Manage Family</a>
        <a href="#" class="menu-item">Help</a>

        <div class="menu-divider mobile-only"></div>

        <!-- Mobile Add Kid Button -->
        <div class="mobile-add-kid-wrapper">
            <button class="mobile-add-kid-btn" onclick="openAddKidModal(); toggleMobileMenu();">+ Add Kid</button>
        </div>


        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="menu-item sign-out">Sign Out</button>
        </form>
    </div>
</div>