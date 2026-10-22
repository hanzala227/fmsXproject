<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FreightX</title>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    
    <!-- Professional Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; }
        html, body { 
            margin: 0; 
            padding: 0; 
            width: 100%; 
            max-width: 100%; 
            overflow-x: auto; 
            height: 100%; 
            position: relative;
        }
        body { font-family: 'Inter', sans-serif; background: #eef1f5; }
        .font-oswald { font-family: 'Oswald', sans-serif; }
        
        /* CONTENT CONTAINER REINFORCEMENT */
        .app-wrapper { 
            display: flex; 
            width: 100%; 
            max-width: 100%; 
            height: 100vh; 
            overflow: hidden; 
        }
        .main-content-wrapper { 
            display: flex; 
            flex-direction: column; 
            flex: 1; 
            min-width: 0; 
            max-width: 100%; 
            overflow: hidden; 
        }
        main { 
            flex: 1; 
            overflow-y: auto; 
            overflow-x: visible; 
            padding: 0; 
            width: 100%; 
            max-width: 100%; 
        }

        /* Sidebar Visibility Fix */
        .app-wrapper { display: flex; width: 100%; height: 100vh; overflow: hidden; }
        aside { width: 200px !important; flex-shrink: 0 !important; display: flex !important; transition: width 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important; }
        .main-content-wrapper { flex: 1; min-width: 0; display: flex; flex-direction: column; overflow: hidden; }
        
        /* Smooth Transitions */
        aside, aside > div {
            transition: width 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        /* Sidebar Collapsed State (Desktop) */
        body.sidebar-collapsed aside {
            width: 60px !important;
            overflow: visible !important; /* Allow popover menus to float outside! */
        }
        body.sidebar-collapsed aside .flex.flex-col.w-\[200px\],
        body.sidebar-collapsed aside nav,
        body.sidebar-collapsed aside .custom-sidebar-scrollbar {
            width: 60px !important;
            overflow: visible !important;
            overflow-x: visible !important;
            overflow-y: visible !important;
        }

        /* 1. HIDE ALL non-icon elements on the collapsed 60px rail */
        body.sidebar-collapsed aside nav span,
        body.sidebar-collapsed aside nav div.pt-6,
        body.sidebar-collapsed aside nav i.fa-angle-right,
        body.sidebar-collapsed aside nav i.fa-angle-down,
        body.sidebar-collapsed aside .px-3.py-4 {
            display: none !important;
        }

        /* 2. Format ALL rail links and buttons as 60px centered flex boxes */
        body.sidebar-collapsed aside nav a.sidebar-nav-item,
        body.sidebar-collapsed aside nav button.sidebar-nav-item,
        body.sidebar-collapsed aside nav .sidebar-nav-item {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            padding: 0 !important;
            margin: 0 !important;
            height: 42px !important;
            width: 60px !important;
            min-width: 60px !important;
            max-width: 60px !important;
            border-left: none !important;
            position: relative !important;
            overflow: hidden !important;
        }

        /* 3. Center the inner flex container inside button & link tags */
        body.sidebar-collapsed aside nav button.sidebar-nav-item .flex,
        body.sidebar-collapsed aside nav a.sidebar-nav-item .flex,
        body.sidebar-collapsed aside nav div > button .flex,
        body.sidebar-collapsed aside nav div > a .flex {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            width: 100% !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* 4. Force ALL rail icons to be perfectly centered, remove all margins (mr-3, etc) */
        body.sidebar-collapsed aside nav a i,
        body.sidebar-collapsed aside nav button i,
        body.sidebar-collapsed aside nav .sidebar-nav-item i {
            margin: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding: 0 !important;
            font-size: 14px !important;
            width: 20px !important;
            height: 20px !important;
            min-width: 20px !important;
            min-height: 20px !important;
            max-width: 20px !important;
            max-height: 20px !important;
            line-height: 1 !important;
            text-align: center !important;
            display: inline-flex !important;
            justify-content: center !important;
            align-items: center !important;
            flex-shrink: 0 !important;
            flex-grow: 0 !important;
            position: relative !important;
            left: 0 !important;
            right: 0 !important;
        }

        /* 5. Center logo area */
        body.sidebar-collapsed aside .flex.items-center.h-\[50px\].px-4 {
            justify-content: center !important;
            padding: 0 !important;
        }

        /* 6. PREMIUM COLLAPSED FLOATING SUBMENUS OVERRIDES */
        body.sidebar-collapsed aside nav > div {
            position: relative !important;
        }

        body.sidebar-collapsed .collapsed-floating-submenu {
            position: absolute !important;
            left: 60px !important;
            top: 0 !important;
            width: 230px !important;
            min-width: 230px !important;
            max-width: 260px !important;
            z-index: 99999 !important;
            background-color: #3b4b7a !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.4) !important;
            border-radius: 0 6px 6px 0 !important;
            border-left: 3px solid #0ab39c !important;
            padding: 6px 0 !important;
            height: auto !important;
            max-height: calc(100vh - 40px) !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }

        body.sidebar-collapsed .collapsed-floating-submenu.collapsed-floating-submenu-bottom {
            top: auto !important;
            bottom: 0 !important;
        }

        body.sidebar-collapsed .collapsed-floating-submenu a,
        body.sidebar-collapsed .collapsed-floating-submenu button {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            padding-left: 16px !important;
            padding-right: 14px !important;
            padding-top: 9px !important;
            padding-bottom: 9px !important;
            text-align: left !important;
            color: #c3cbe4 !important;
            text-decoration: none !important;
            font-size: 9px !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            width: 100% !important;
            height: auto !important;
            min-height: 32px !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
            white-space: normal !important;
            word-break: break-word !important;
            position: relative !important;
            overflow: visible !important;
            margin: 0 !important;
        }

        body.sidebar-collapsed .collapsed-floating-submenu a:hover,
        body.sidebar-collapsed .collapsed-floating-submenu button:hover {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.08) !important;
        }

        body.sidebar-collapsed .collapsed-floating-submenu button .flex.items-center {
            justify-content: flex-start !important;
            width: 100% !important;
        }

        body.sidebar-collapsed .collapsed-floating-submenu span {
            display: inline-block !important; /* Re-enable link & header text */
            color: inherit !important;
            font-size: 9px !important;
            font-weight: bold !important;
        }

        body.sidebar-collapsed .collapsed-floating-submenu i.fa-angle-right,
        body.sidebar-collapsed .collapsed-floating-submenu i.fa-angle-down {
            display: inline-block !important; /* Re-enable nested sub-arrows inside popups */
            margin-left: auto !important;
            width: auto !important;
            height: auto !important;
            font-size: 8px !important;
            opacity: 0.6 !important;
        }

        body.sidebar-collapsed .collapsed-floating-submenu div[x-show="subOpen"] a {
            padding-left: 28px !important;
            font-size: 8.5px !important;
            color: #a0a8c1 !important;
            background-color: rgba(0, 0, 0, 0.15) !important;
        }

        body.sidebar-collapsed .collapsed-floating-submenu div[x-show="subOpen"] a:hover {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.1) !important;
        }

        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        @media (max-width: 768px) {
            body:not(.sidebar-mobile-open) aside { display: none !important; }
            .sidebar-mobile-open aside { display: flex !important; position: fixed; z-index: 2000; width: 200px; }
            /* Prevent desktop collapsed styles from clashing on mobile */
            body.sidebar-collapsed aside { width: 200px !important; }
        }
    </style>
    @stack('styles')
</head>
<body class="h-full font-sans antialiased text-[#333] bg-[#eef1f5]">
    <script>window.__turbo_tracking_listeners = true;</script>
    @include('components.global-preloader')
    <div class="app-wrapper">
        <!-- Sidebar -->
        <x-sidebar :type="$type ?? 'admin'" />

        <div class="main-content-wrapper">
            <!-- Mobile Overlay -->
            <div onclick="document.body.classList.remove('sidebar-mobile-open')" class="fixed inset-0 bg-black/50 z-[1999] md:hidden transition-opacity cursor-pointer hidden [.sidebar-mobile-open_&]:block"></div>
            
            <!-- Top Navbar -->
            <x-navbar :type="$type ?? 'admin'" />

            <!-- Content Area -->
            <main class="custom-scrollbar focus:outline-none">
                {{ $slot }}
            </main>
        </div>
    </div>
    
    <x-add-new-modal />
    <x-track-trace />
    
    {{-- 
    <script>
        window.FrexConfig = {
            userName: "{{ auth()->user()->name ?? 'Boss' }}"
        };
    </script>
    <script src="{{ asset('js/frex-voice.js') }}"></script>
    --}}
    
    <x-searchable-select-global />
    
    @stack('scripts')
</body>
</html>

