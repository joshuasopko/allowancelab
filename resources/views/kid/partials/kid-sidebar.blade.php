<!-- Kid Sidebar -->
<aside class="kid-sidebar mobile-hidden" id="kidSidebar">

    <!-- Welcome Message -->
    <div class="kid-sidebar-welcome">
        <div class="kid-sidebar-welcome-title">
            Step into the lab,<br><span class="kid-name-colored"
                style="color: {{ $kid->color }};">{{ $kid->name }}.</span>
        </div>
        <div class="kid-sidebar-welcome-subtitle">
            Let's grow that allowance beaker!
        </div>
    </div>

    <!-- Birthday Countdown -->
    @php
        $birthday = \Carbon\Carbon::parse($kid->birthday);
        $nextBirthday = $birthday->copy()->year(now()->year);
        if ($nextBirthday->isPast()) {
            $nextBirthday->addYear();
        }
        $daysUntilBirthday = (int) now()->diffInDays($nextBirthday);
    @endphp
    <div class="kid-birthday-countdown">
        <div class="kid-birthday-icon">🎉</div>
        <div class="kid-birthday-text">
            Your birthday is in<br>
            <strong>{{ $daysUntilBirthday }} days!</strong>
        </div>
    </div>

    <!-- Menu -->
    <nav class="kid-sidebar-menu">
        <a href="{{ route('kid.dashboard') }}"
            class="kid-menu-item {{ request()->routeIs('kid.dashboard') ? 'active' : '' }}">
            Dashboard
        </a>

        <div class="kid-menu-divider"></div>

        <!-- Lab Tools Header -->
        <div class="kid-lab-tools-header"
            style="color: {{ $kid->color }}; border-bottom: 1px solid {{ $kid->color }}80;">
            <span>Lab Tools</span>
            <i class="fas fa-flask"></i>
        </div>

        <!-- Goals Feature -->
        <a href="{{ route('kid.goals.index') }}"
            class="kid-menu-item {{ request()->routeIs('kid.goals.*') ? 'active' : '' }}"
            onclick="localStorage.setItem('goalsLaunchToastDismissed', 'true');">
            <span>My Goals</span>
            @if($kid->getActiveGoalsCount() > 0)
                <span class="kid-goals-counter" style="background-color: {{ $kid->color }};">
                    {{ $kid->getActiveGoalsCount() }}
                </span>
            @endif
        </a>

        <!-- Goals Launch Toast (Persistent until dismissed or goals clicked) -->
        <div class="kid-goals-sidebar-toast"
             style="--kid-color: {{ $kid->color }};"
             x-data="{
                 show: true,
                 init() {
                     const dismissed = localStorage.getItem('goalsLaunchToastDismissed');
                     if (dismissed === 'true') {
                         this.show = false;
                     }
                 },
                 dismiss() {
                     localStorage.setItem('goalsLaunchToastDismissed', 'true');
                     this.show = false;
                 },
                 exploreGoals() {
                     localStorage.setItem('goalsLaunchToastDismissed', 'true');
                     window.location.href = '{{ route('kid.goals.index') }}';
                 }
             }"
             x-show="show"
             x-transition>
            <div class="kid-goals-sidebar-toast-content">
                <div class="kid-goals-sidebar-toast-icon">🎯</div>
                <div class="kid-goals-sidebar-toast-text">
                    <strong>Goals Are Here!</strong>
                    <p>Save for something special</p>
                </div>
            </div>
            <div class="kid-goals-sidebar-toast-actions">
                <button class="kid-goals-sidebar-toast-btn" @click="exploreGoals()">
                    Explore →
                </button>
                <button class="kid-goals-sidebar-toast-close" @click="dismiss()" aria-label="Dismiss">
                    ✕
                </button>
            </div>
        </div>

        <!-- Goal Status Indicators (show on all pages) -->
        @php
            $readyCount = $kid->getReadyToRedeemGoalsCount();
            $pendingCount = $kid->getPendingRedemptionGoalsCount();
        @endphp

        @if($readyCount > 0)
            <div class="kid-goal-status-indicator kid-goal-ready">
                <i class="fas fa-gift"></i>
                <span>{{ $readyCount }} Goal{{ $readyCount > 1 ? 's' : '' }} Ready!</span>
            </div>
        @endif

        @if($pendingCount > 0)
            <div class="kid-goal-status-indicator kid-goal-pending">
                <i class="fas fa-clock"></i>
                <span>{{ $pendingCount }} Goal{{ $pendingCount > 1 ? 's' : '' }} Pending</span>
            </div>
        @endif

        <!-- Available Funds Submenu (only show on goals page) -->
        @if(request()->routeIs('kid.goals.*'))
            <div class="kid-available-funds-submenu" style="border-left: 3px solid {{ $kid->color }};">
                <div class="kid-available-funds-submenu-content">
                    <i class="fas fa-wallet kid-available-funds-submenu-icon" style="color: {{ $kid->color }};"></i>
                    <span class="kid-available-funds-submenu-amount" style="color: {{ $kid->color }};">${{ number_format($kid->balance, 2) }}</span>
                    <span class="kid-available-funds-submenu-label">available</span>
                </div>
            </div>
        @endif

        <!-- Wishes Feature -->
        <a href="{{ route('kid.wishes.index') }}"
            class="kid-menu-item {{ request()->routeIs('kid.wishes.*') ? 'active' : '' }}">
            <span>My Wishes</span>
            @if($kid->getPendingWishRequestsCount() > 0)
                <span class="kid-goals-counter" style="background-color: {{ $kid->color }};">
                    {{ $kid->getPendingWishRequestsCount() }}
                </span>
            @endif
        </a>

        <!-- Coming Soon Features -->

        <a href="#" class="kid-menu-item kid-coming-soon-item">
            My Chores
            <span class="kid-coming-soon-badge">Coming Soon</span>
        </a>

        <a href="#" class="kid-menu-item kid-coming-soon-item">
            Jobs Board
            <span class="kid-coming-soon-badge">Coming Soon</span>
        </a>

        <a href="#" class="kid-menu-item kid-coming-soon-item">
            Request Funds
            <span class="kid-coming-soon-badge">Coming Soon</span>
        </a>

        <a href="#" class="kid-menu-item kid-coming-soon-item">
            Loans
            <span class="kid-coming-soon-badge">Coming Soon</span>
        </a>

        <a href="#" class="kid-menu-item kid-coming-soon-item">
            Learn & Grow
            <span class="kid-coming-soon-badge">Coming Soon</span>
        </a>

        <div class="kid-menu-divider"></div>

        <a href="{{ route('kid.profile') }}"
            class="kid-menu-item {{ request()->routeIs('kid.profile') ? 'active' : '' }}">
            <span>Profile Settings</span>
        </a>

        <form action="{{ route('kid.logout') }}" method="POST" style="margin-top: auto;">
            @csrf
            <button type="submit" class="kid-menu-item sign-out"
                style="width: 100%; text-align: left; background: none; border: none; cursor: pointer;">
                Sign Out
            </button>
        </form>
    </nav>
</aside>