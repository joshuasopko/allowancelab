<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head')
    <title>{{ $kid->name }} - @yield('tab-title', 'Overview') - AllowanceLab</title>

    @vite(['resources/css/dashboard.css', 'resources/js/dashboard.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    @include('partials.header')

    <!-- Dashboard Container -->
    <div class="dashboard-container">
        @include('partials.sidebar')

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-wrapper" style="max-width: 1200px;">

                <!-- Kid Header -->
                <div class="kid-focused-header">
                    <a href="{{ route('dashboard') }}" class="back-link-mobile">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <div class="kid-info">
                        <h1>{{ $kid->name }}</h1>
                        <div class="kid-balance" style="color: {{ $kid->color }};">
                            <i class="fas fa-wallet"></i>
                            ${{ number_format($kid->balance, 2) }} available
                        </div>
                    </div>
                    @if(request()->routeIs('kids.goals'))
                        <button type="button" onclick="openCreateGoalModal()" class="btn-new-goal" style="--kid-color: {{ $kid->color }};">
                            <i class="fas fa-plus"></i> New Goal
                        </button>
                    @endif
                </div>

                <!-- Desktop Tabs -->
                <div style="display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #e5e7eb;">
                    <nav class="kid-focused-tabs desktop-tabs" style="border-bottom: none; margin-bottom: 0;">
                        <a href="{{ route('kids.overview', $kid) }}"
                           class="tab {{ request()->routeIs('kids.overview') ? 'active' : '' }}">
                            <i class="fas fa-chart-pie"></i>
                            <span>Overview</span>
                        </a>
                        <a href="{{ route('kids.allowance', $kid) }}"
                           class="tab {{ request()->routeIs('kids.allowance') ? 'active' : '' }}">
                            <i class="fas fa-coins"></i>
                            <span>Allowance</span>
                        </a>
                        <a href="{{ route('kids.goals', $kid) }}"
                           class="tab {{ request()->routeIs('kids.goals') ? 'active' : '' }}">
                            <i class="fas fa-bullseye"></i>
                            <span>Goals</span>
                        </a>
                        <a href="{{ route('kids.wishes', $kid) }}"
                           class="tab {{ request()->routeIs('kids.wishes') ? 'active' : '' }}">
                            <i class="fas fa-gift"></i>
                            <span>Wishes</span>
                        </a>
                    </nav>
                    <a href="{{ route('dashboard') }}" class="back-to-dashboard">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <!-- Tab Content -->
                <div class="kid-focused-content">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Bottom Tabs -->
    <nav class="kid-focused-tabs mobile-tabs">
        <a href="{{ route('kids.overview', $kid) }}"
           class="tab {{ request()->routeIs('kids.overview') ? 'active' : '' }}">
            <i class="fas fa-chart-pie"></i>
            @if(request()->routeIs('kids.overview'))
                <span>Overview</span>
            @endif
        </a>
        <a href="{{ route('kids.allowance', $kid) }}"
           class="tab {{ request()->routeIs('kids.allowance') ? 'active' : '' }}">
            <i class="fas fa-coins"></i>
            @if(request()->routeIs('kids.allowance'))
                <span>Allowance</span>
            @endif
        </a>
        <a href="{{ route('kids.goals', $kid) }}"
           class="tab {{ request()->routeIs('kids.goals') ? 'active' : '' }}">
            <i class="fas fa-bullseye"></i>
            @if(request()->routeIs('kids.goals'))
                <span>Goals</span>
            @endif
        </a>
        <a href="{{ route('kids.wishes', $kid) }}"
           class="tab {{ request()->routeIs('kids.wishes') ? 'active' : '' }}">
            <i class="fas fa-gift"></i>
            @if(request()->routeIs('kids.wishes'))
                <span>Wishes</span>
            @endif
        </a>
    </nav>

    @yield('modals')

    @include('partials.version')

    @vite('resources/css/kid-focused.css')
</body>

</html>
