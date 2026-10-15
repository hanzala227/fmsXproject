<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - FreitX</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/userdashboard.css') }}" rel="stylesheet">
    @stack('styles')
    <style>
        /* Base Layout (Desktop Mode - Sidebar Visible) */
.main {
    width: calc(100% - 200px);
    margin-left: 200px;
    min-height: 100vh;
    transition: margin-left 0.25s ease, width 0.25s ease;
    padding: 10px;
}

/* Tablet & Mobile Mode (≤900px - Sidebar becomes Overlay/Drawer) */
@media (max-width: 900px) {
    .main {
        width: 100% !important;
        margin-left: 0 !important;
    }
}
    </style>
</head>
<body>
    @include('layouts.userSidebar')
    <div class="main">
        @include('layouts.usertopBar')
        <main class="maincontext">
            @yield('content')
        </main>
    </div>
<script src="{{ asset('js/userdashboard.js') }}"></script>
@stack('scripts')
</body>
</html>