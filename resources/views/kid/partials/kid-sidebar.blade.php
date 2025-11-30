<!-- Kid Sidebar -->
<aside class="kid-sidebar mobile-hidden" id="kidSidebar">
    <!-- Welcome Message -->
    <div class="kid-sidebar-welcome">
        Welcome back, {{ $kid->name }}!
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

        <!-- Coming Soon Features -->
        <a href="#" class="kid-menu-item kid-coming-soon-item">
            My Goals
            <span class="kid-coming-soon-badge">Coming Soon</span>
        </a>

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