<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <title>@yield('title', 'My Dashboard - AllowanceLab')</title>

    <script>
        // Set values immediately when script loads
        window.kidBalance = {{ $kid->balance }};
        window.kidPoints = {{ $kid->points ?? 0 }};
    </script>

    @vite(['resources/css/kid-dashboard.css', 'resources/js/kid-dashboard.js'])
</head>

<body class="kid-body">
    @include('kid.partials.kid-header')

    <!-- Dashboard Container -->
    <div class="kid-dashboard-container">
        @include('kid.partials.kid-sidebar')

        <!-- Main Content -->
        <div class="kid-main-content">
            <div class="kid-content-wrapper">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="kid-toast" id="kidToast">
        <div class="kid-toast-icon">ℹ️</div>
        <div class="kid-toast-message" id="kidToastMessage"></div>
    </div>
    @include('partials.version')
</body>
</body>

</html>