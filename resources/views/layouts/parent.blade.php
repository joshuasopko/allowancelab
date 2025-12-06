<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head', ['title' => '@yield('title', 'AllowanceLab')'])

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
            <div class="content-wrapper">
                @yield('content')
            </div>
        </div>
    </div>

    @yield('modals')

    @include('partials.version')
</body>
</body>

</body>

</html>