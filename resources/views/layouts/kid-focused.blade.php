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
                    <div class="kid-info">
                        <h1>{{ $kid->name }}</h1>
                        <div class="kid-balance" style="color: {{ $kid->color }};">
                            <i class="fas fa-wallet"></i>
                            ${{ number_format($kid->balance, 2) }} available
                        </div>
                    </div>
                    <a href="{{ route('kids.manage', $kid) }}" class="btn-manage">
                        <i class="fas fa-cog"></i> Manage
                    </a>
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

    <style>
    /* Kid-Focused Header */
    .kid-focused-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #e5e7eb;
    }

    .kid-info h1 {
        font-size: 28px;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 8px 0;
    }

    .kid-balance {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        background: white;
        border-radius: 8px;
        border: 2px solid currentColor;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }

    .kid-balance i {
        font-size: 12px;
    }

    .btn-manage {
        background: #6b7280;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        font-size: 14px;
        transition: background 0.2s;
    }

    .btn-manage:hover {
        background: #4b5563;
    }

    /* Back to Dashboard Link */
    .back-to-dashboard {
        color: #6b7280;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        padding: 12px 16px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: color 0.2s;
        white-space: nowrap;
    }

    .back-to-dashboard:hover {
        color: #3b82f6;
    }

    .back-to-dashboard i {
        font-size: 12px;
    }

    /* Desktop Tabs */
    .kid-focused-tabs.desktop-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 32px;
        padding-bottom: 0;
    }

    .kid-focused-tabs.desktop-tabs .tab {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        color: #6b7280;
        text-decoration: none;
        font-weight: 600;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        transition: all 0.2s;
    }

    .kid-focused-tabs.desktop-tabs .tab:hover {
        color: #3b82f6;
        background: #f9fafb;
    }

    .kid-focused-tabs.desktop-tabs .tab.active {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
    }

    .kid-focused-tabs.desktop-tabs .tab i {
        font-size: 18px;
    }

    /* Mobile Bottom Tabs */
    .kid-focused-tabs.mobile-tabs {
        display: none;
    }

    /* Content Area */
    .kid-focused-content {
        min-height: 400px;
    }

    /* Mobile Styles */
    @media (max-width: 768px) {
        .kid-focused-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .back-to-dashboard {
            display: none;
        }

        .kid-focused-tabs.desktop-tabs {
            display: none;
        }

        .kid-focused-tabs.mobile-tabs {
            display: flex;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #e5e7eb;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            padding: 8px 0;
        }

        .kid-focused-tabs.mobile-tabs .tab {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 8px 4px;
            color: #9ca3af;
            text-decoration: none;
            transition: all 0.2s;
        }

        .kid-focused-tabs.mobile-tabs .tab i {
            font-size: 24px;
        }

        .kid-focused-tabs.mobile-tabs .tab span {
            font-size: 11px;
            font-weight: 600;
        }

        .kid-focused-tabs.mobile-tabs .tab.active {
            color: #3b82f6;
        }

        .kid-focused-tabs.mobile-tabs .tab.active i {
            font-size: 26px;
        }

        /* Add bottom padding to content to account for fixed tabs */
        .kid-focused-content {
            padding-bottom: 80px;
        }
    }
    </style>
</body>

</html>
