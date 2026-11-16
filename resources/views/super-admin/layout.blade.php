<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FreightX — Super Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; width: 100%; max-width: 100%; overflow-x: hidden; height: 100%; }
        body { font-family: 'Inter', sans-serif; background: #eef1f5; }
        .font-oswald { font-family: 'Oswald', sans-serif; }
        .app-wrapper { display: flex; width: 100%; height: 100vh; overflow: hidden; }
        .main-content-wrapper { display: flex; flex-direction: column; flex: 1; min-width: 0; overflow: hidden; }
        main { flex: 1; overflow-y: auto; padding: 0; width: 100%; }
        aside { width: 220px; flex-shrink: 0; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 3px; }

        /* Toast */
        .toast-container { position: fixed; top: 56px; right: 16px; z-index: 9999; display: flex; flex-direction: column; gap: 6px; pointer-events: none; }
        .toast { background: #1e293b; color: #fff; padding: 8px 14px; border-radius: 4px; font-size: 11px; box-shadow: 0 4px 16px rgba(0,0,0,0.25); display: flex; align-items: center; gap: 8px; animation: toastIn 0.25s ease; pointer-events: all; }
        .toast.success { border-left: 3px solid #22c55e; }
        .toast.error { border-left: 3px solid #ef4444; }
        .toast.info { border-left: 3px solid #3b82f6; }
        @keyframes toastIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }

        @media (max-width: 768px) {
            aside { display: none; }
            body.sidebar-mobile-open aside { display: flex; position: fixed; z-index: 2000; width: 220px; }
        }
    </style>
    @stack('styles')
</head>
<body class="h-full font-sans antialiased text-[#333] bg-[#eef1f5]">
    <div class="app-wrapper">
        @include('super-admin.sidebar')

        <div class="main-content-wrapper">
            {{-- Mobile Overlay --}}
            <div onclick="document.body.classList.remove('sidebar-mobile-open')" class="fixed inset-0 bg-black/50 z-[1999] md:hidden hidden [.sidebar-mobile-open_&]:block cursor-pointer"></div>

            {{-- Top Navbar --}}
            <header style="height: 50px; background: #fff; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; padding: 0 20px; flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <button onclick="document.body.classList.toggle('sidebar-mobile-open')" class="md:hidden" style="background: none; border: none; font-size: 16px; color: #64748b; cursor: pointer;">
                        <i class="fa fa-bars"></i>
                    </button>
                    <span style="font-size: 13px; font-weight: 600; color: #405189; letter-spacing: 0.5px; text-transform: uppercase;">Super Admin Panel</span>
                </div>
                <div style="display: flex; align-items: center; gap: 16px;">
                    <a href="/" style="font-size: 10px; color: #64748b; text-decoration: none; display: flex; align-items: center; gap: 4px;">
                        <i class="fa fa-external-link"></i> Go to ERP
                    </a>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 28px; height: 28px; border-radius: 50%; background: #405189; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 10px; font-weight: 600;">
                            {{ strtoupper(substr(auth()->user()->name ?? 'SA', 0, 2)) }}
                        </div>
                        <span style="font-size: 11px; color: #475569;">{{ auth()->user()->name ?? 'Super Admin' }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                        @csrf
                        <button type="submit" style="background: none; border: none; color: #ef4444; font-size: 11px; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                            <i class="fa fa-sign-out"></i> Logout
                        </button>
                    </form>
                </div>
            </header>

            <main class="custom-scrollbar">
                {{-- Flash Messages --}}
                @if(session('success'))
                    <div style="margin: 12px 16px 0; padding: 10px 14px; background: #d1fae5; border: 1px solid #6ee7b7; border-radius: 4px; color: #065f46; font-size: 11px; display: flex; align-items: center; gap: 8px;">
                        <i class="fa fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div style="margin: 12px 16px 0; padding: 10px 14px; background: #fee2e2; border: 1px solid #fca5a5; border-radius: 4px; color: #991b1b; font-size: 11px; display: flex; align-items: center; gap: 8px;">
                        <i class="fa fa-times-circle"></i> {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <div id="toast-container" class="toast-container"></div>
    <script>
        function showToast(type, msg) {
            var icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle' };
            var container = document.getElementById('toast-container');
            var t = document.createElement('div');
            t.className = 'toast ' + type;
            t.innerHTML = '<i class="fa fa-' + (icons[type] || 'info-circle') + '"></i> ' + msg;
            container.appendChild(t);
            setTimeout(function() { t.remove(); }, 3000);
        }
    </script>
    @stack('scripts')
</body>
</html>
