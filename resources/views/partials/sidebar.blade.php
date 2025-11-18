<!-- Sidebar -->
<div class="sidebar">
    @php
        $nameParts = explode(' ', Auth::user()->name);
        $firstName = ucfirst(strtolower($nameParts[0]));
        $lastName = isset($nameParts[1]) ? ucfirst(strtolower($nameParts[1])) : $firstName;
    @endphp

    <div class="sidebar-welcome">Welcome, {{ $firstName }}!</div>

    <div class="sidebar-menu">
        <div class="menu-item has-subtext">
            Account Info
            <div class="menu-subtext">{{ $lastName }} Family</div>
        </div>
        <a href="{{ route('dashboard') }}"
            class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
        @hasSection('sidebar-active')
            @yield('sidebar-active')
        @endif
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