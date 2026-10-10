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
</head>
<body>
<div class="app">
    @include('layouts.userSidebar')
    <div class="main" style="width : calc(100% - 200px); margin-left: 200px;">
        @include('layouts.usertopBar')
        <main class="content">
            @yield('content')
        </main>
    </div>
</div>
<script src="{{ asset('js/userdashboard.js') }}"></script>
@stack('scripts')
</body>
</html>