<!-- Sidebar -->
<div class="sidebar" id="parentSidebar">
    @php
        $nameParts = explode(' ', Auth::user()->name);
        $firstName = ucfirst(strtolower($nameParts[0]));
        $lastName = isset($nameParts[1]) ? ucfirst(strtolower($nameParts[1])) : $firstName;
    @endphp

    <div class="sidebar-welcome">
        @php
            $hour = now()->hour;
            if ($hour < 12) {
                $greeting = 'Good morning';
            } elseif ($hour < 18) {
                $greeting = 'Good afternoon';
            } else {
                $greeting = 'Good evening';
            }
        @endphp
        <div class="sidebar-welcome-greeting">
            {{ $greeting }}, {{ Auth::user()->first_name ?? explode(' ', Auth::user()->name)[0] }}
        </div>
        <div class="sidebar-welcome-subtitle">
            You're managing a family of {{ Auth::user()->accessibleKids()->count() }} kids.
        </div>
    </div>

    <div class="sidebar-menu">
        <a href="{{ route('parent.account') }}" class="menu-item {{ request()->routeIs('parent.account') ? 'active' : '' }}">
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

        <a href="{{ route('manage-family') }}"
            class="menu-item {{ request()->routeIs('manage-family') ? 'active' : '' }}">Manage Family</a>
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