<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AllowanceLab')</title>
    @vite(['resources/css/dashboard.css', 'resources/js/dashboard.js'])
</head>

<body>
    @include('partials.header')

    <!-- Dashboard Container -->
    <div class="dashboard-container">
        @include('partials.sidebar')

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-wrapper">
                @yield('content')
            </div>
        </div>
    </div>

    @yield('modals')

</body>

</html>