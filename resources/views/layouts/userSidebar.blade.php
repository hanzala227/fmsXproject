@php
    // label, icon, route name, optional badge, has-submenu flag, submenu items
    $menu = [
        ['Dashboard', 'bi-box-seam', 'client'],
        ['My Shipments', 'bi-boxes', 'shipments'],
        ['Track Shipment', 'bi-geo-alt', 'tracking'],
        // ['Bookings', 'bi-calendar-check', 'bookings'],
        ['Quotes', 'bi-file-earmark-text', 'quotes'],
        // ['Documents', 'bi-file-earmark', 'documents'],
        ['Invoices & Payments', 'bi-cash-stack', 'invoices'],
        // ['Requests', 'bi-download', 'requests'],
        // ['Messages', 'bi-envelope', 'messages', 3],
        ['Notifications', 'bi-bell', 'notifications', ''],
        ['Reports', 'bi-file-earmark-bar-graph', 'reports'],
        ['Company', 'bi-building', 'company', null, true, [
            // ['Profile', 'company'],
            // ['Users',   'company'],
        ]],
        // ['Settings', 'bi-gear', 'settings'],
    ];
@endphp

<aside class="sidebar fx-sidebar" id="sidebar" aria-label="Main navigation">

    <!-- Logo Area -->
    <div class="fx-header">
        <a href="{{ route('client.index') }}" class="fx-brand">
            <span class="fx-brand-name">Freit<b>X</b></span>
            <small>Freight Forwarder</small>
        </a>

        <!-- Close button (mobile only) -->
        <button type="button" class="fx-close" id="sidebarClose" aria-label="Close menu">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <nav class="fx-nav menu">
        @foreach ($menu as $m)
            @php
                $active = request()->is('client-portal/' . $m[2] . '*');
                $href   = $m[2] ? url('client-portal/' . $m[2]) : '#';
                $hasSub = !empty($m[4]) && !empty($m[5]);
            @endphp

            @if ($hasSub)
                <div x-data="{ open: {{ $active ? 'true' : 'false' }} }">
                    <a href="{{ $href }}" @click.prevent="open = !open"
                       class="menu-item fx-item {{ $active ? 'active' : '' }}">
                        <i class="bi {{ $m[1] }} fx-icon"></i>
                        <span class="fx-label">{{ $m[0] }}</span>
                        @if (!empty($m[3]))<em class="badge-dot fx-badge">{{ $m[3] }}</em>@endif
                        <i class="bi bi-chevron-down caret fx-caret" :class="open ? 'fx-caret-open' : ''"></i>
                    </a>
                    <div x-show="open" x-cloak class="fx-sub">
                        @foreach ($m[5] as $sub)
                            <a href="{{ $sub[1] ?: '#' }}" class="fx-sub-link">{{ $sub[0] }}</a>
                        @endforeach
                    </div>
                </div>
            @else
                <a href="{{ $href }}" class="menu-item fx-item {{ $active ? 'active' : '' }}">
                    <i class="bi {{ $m[1] }} fx-icon"></i>
                    <span class="fx-label">{{ $m[0] }}</span>
                    @if (!empty($m[3]))<em class="badge-dot fx-badge">{{ $m[3] }}</em>@endif
                </a>
            @endif
        @endforeach
    </nav>

    <!-- Help box -->
    <!-- <div class="help fx-help">
        <div class="help-row fx-help-row">
            <i class="bi bi-headset"></i>
            <div>
                <strong>Need Help?</strong>
                <small>Contact our support team</small>
            </div>
        </div>
        <a href="#" class="help-btn fx-help-btn">Contact Support</a>
    </div> -->
</aside>

<!-- Backdrop behind the mobile drawer -->
{{-- SIDEBAR OVERLAY --}}
<div class="fx-overlay" id="sidebarOverlay" aria-hidden="true">
    <div class="fx-overlay-panel">

        {{-- Header --}}
        <div class="fx-overlay-header">
            <div class="fx-overlay-brand">
                <span class="fx-brand-icon">
                    <i class="bi bi-grid-1x2-fill"></i>
                </span>

                <div class="fx-brand-text">
                    <strong>FreightX</strong>
                    <small>Client Portal</small>
                </div>
            </div>

            <button type="button"
                    class="fx-overlay-close"
                    id="sidebarClose"
                    aria-label="Close navigation">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

   
        <nav class="fx-overlay-nav" aria-label="Main navigation">

            <span class="fx-nav-label">MAIN MENU</span>

            @php
               
            @endphp
   @foreach ($menu as $m)
            @php
                $active = request()->is('client-portal/' . $m[2] . '*');
                $href   = $m[2] ? url('client-portal/' . $m[2]) : '#';
                $hasSub = !empty($m[4]) && !empty($m[5]);
            @endphp

            @if ($hasSub)
                <div x-data="{ open: {{ $active ? 'true' : 'false' }} }">
                    <a href="{{ $href }}" @click.prevent="open = !open"
                       class="menu-item fx-item {{ $active ? 'active' : '' }}">
                        <i class="bi {{ $m[1] }} fx-icon"></i>
                        <span class="fx-label">{{ $m[0] }}</span>
                        @if (!empty($m[3]))<em class="badge-dot fx-badge">{{ $m[3] }}</em>@endif
                        <i class="bi bi-chevron-down caret fx-caret" :class="open ? 'fx-caret-open' : ''"></i>
                    </a>
                    <div x-show="open" x-cloak class="fx-sub">
                        @foreach ($m[5] as $sub)
                            <a href="{{ $sub[1] ?: '#' }}" class="fx-sub-link">{{ $sub[0] }}</a>
                        @endforeach
                    </div>
                </div>
            @else
                <a href="{{ $href }}" class="menu-item fx-item {{ $active ? 'active' : '' }}">
                    <i class="bi {{ $m[1] }} fx-icon"></i>
                    <span class="fx-label">{{ $m[0] }}</span>
                    @if (!empty($m[3]))<em class="badge-dot fx-badge">{{ $m[3] }}</em>@endif
                </a>
            @endif
        @endforeach

        </nav>

        {{-- Footer --}}
        <div class="fx-overlay-footer">
            <span class="fx-footer-indicator"></span>
            <div>
                <strong>FreightX Portal</strong>
                <small>Shipment management</small>
            </div>
        </div>

    </div>
</div>

<style>
    /* Overlay background */
    .fx-overlay {
        position: fixed;
        inset: 0;
        z-index: 1999;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);

        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition:
            opacity 0.25s ease,
            visibility 0.25s ease;
    }

    .fx-overlay.open {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    /* Sidebar panel */
    .fx-overlay-panel {
        width: min(300px, 85vw);
        height: 100%;
        display: flex;
        flex-direction: column;
        background: #ffffff;
        box-shadow: 12px 0 40px rgba(0, 0, 0, 0.14);

        transform: translateX(-100%);
        transition: transform 0.28s ease;
        overflow: hidden;
    }

    .fx-overlay.open .fx-overlay-panel {
        transform: translateX(0);
    }

    /* Header */
    .fx-overlay-header {
        min-height: 76px;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border-bottom: 1px solid #edf0f2;
        background: #ffffff;
    }

    .fx-overlay-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .fx-brand-icon {
        width: 42px;
        height: 42px;
        flex-shrink: 0;
        display: grid;
        place-items: center;
        border-radius: 12px;
        background: #1d4533;
        color: #ffffff;
        font-size: 19px;
    }

    .fx-brand-text {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .fx-brand-text strong {
        color: #1d4533;
        font-size: 17px;
        font-weight: 750;
        letter-spacing: 0.2px;
    }

    .fx-brand-text small {
        color: #7b8580;
        font-size: 11px;
    }

    .fx-overlay-close {
        width: 36px;
        height: 36px;
        flex-shrink: 0;
        display: grid;
        place-items: center;
        border: 1px solid #e8ece9;
        border-radius: 10px;
        background: #ffffff;
        color: #47534d;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .fx-overlay-close:hover {
        background: #1d4533;
        border-color: #1d4533;
        color: #ffffff;
    }

    /* Navigation area */
    .fx-overlay-nav {
        flex: 1;
        min-height: 0;
        padding: 22px 13px;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .fx-nav-label {
        display: block;
        margin: 0 10px 13px;
        color: #929b95;
        font-size: 10px;
        font-weight: 750;
        letter-spacing: 1.5px;
    }

    /* Navigation links */
    .fx-nav-item {
        position: relative;
        display: flex;
        align-items: center;
        gap: 13px;
        min-height: 47px;
        margin-bottom: 5px;
        padding: 10px 12px;
        border: 1px solid transparent;
        border-radius: 10px;
        color: #4d5952;
        text-decoration: none;
        font-size: 13px;
        font-weight: 550;
        transition:
            color 0.2s ease,
            background 0.2s ease,
            border-color 0.2s ease;
    }

    .fx-nav-item:hover {
        background: #f4f7f5;
        color: #1d4533;
    }

    .fx-nav-item.active {
        background: #edf4ef;
        border-color: #e1ebe4;
        color: #1d4533;
        font-weight: 700;
    }

    .fx-nav-icon {
        width: 23px;
        flex-shrink: 0;
        display: inline-flex;
        justify-content: center;
        font-size: 17px;
    }

    .fx-nav-text {
        flex: 1;
        line-height: 1.4;
    }

    .fx-nav-active-dot {
        width: 6px;
        height: 6px;
        flex-shrink: 0;
        border-radius: 50%;
        background: #1d4533;
    }

    .fx-nav-badge {
        min-width: 21px;
        height: 21px;
        padding: 0 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 20px;
        background: #1d4533;
        color: #ffffff;
        font-size: 10px;
        font-weight: 700;
    }

    /* Optional company submenu */
    .fx-nav-submenu {
        margin: -1px 0 8px 35px;
        padding-left: 12px;
        border-left: 1px solid #e3e9e5;
    }

    .fx-nav-subitem {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 9px 8px;
        color: #68746c;
        text-decoration: none;
        font-size: 12px;
        border-radius: 7px;
    }

    .fx-nav-subitem:hover {
        background: #f4f7f5;
        color: #1d4533;
    }

    /* Footer */
    .fx-overlay-footer {
        min-height: 70px;
        padding: 14px 20px;
        display: flex;
        align-items: center;
        gap: 11px;
        border-top: 1px solid #edf0f2;
        background: #fbfcfb;
    }

    .fx-footer-indicator {
        width: 9px;
        height: 9px;
        flex-shrink: 0;
        border-radius: 50%;
        background: #2c8b57;
        box-shadow: 0 0 0 4px #e5f3e9;
    }

    .fx-overlay-footer div {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .fx-overlay-footer strong {
        color: #344239;
        font-size: 11px;
        font-weight: 700;
    }

    .fx-overlay-footer small {
        color: #89928c;
        font-size: 10px;
    }
    
/* Normal nav link */
.fx-item {
    color: #4d5952;
    background: transparent;
    transition: all 0.2s ease;
}

/* Link ka icon aur text */
.fx-item .fx-icon,
.fx-item .fx-label {
    color: inherit;
}

/* Mouse le jaane par */
.fx-item:hover {
    color: #1D4533;
    background: #F7EAE0;
}

/* Active/current page link */
.fx-item.active {
    color: #ffffff;
    background: #1D4533;
    border-radius: 8px;
}

/* Active link ka icon aur text */
.fx-item.active .fx-icon,
.fx-item.active .fx-label {
    color: #ffffff;
}

/* Dropdown arrow */
.fx-item .fx-caret {
    color: inherit;
}

/* Dropdown open ho to arrow */
.fx-caret-open {
    transform: rotate(180deg);
}

.fx-caret {
    transition: transform 0.2s ease;
}

    /* Lock background scrolling while open */
    body.fx-sidebar-open {
        overflow: hidden;
    }

    /* Small screens */
    @media (max-width: 480px) {
        .fx-overlay-panel {
            width: min(285px, 88vw);
        }

        .fx-overlay-header {
            min-height: 68px;
        }

        .fx-overlay-nav {
            padding: 17px 11px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .fx-overlay,
        .fx-overlay-panel,
        .fx-nav-item {
            transition: none;
        }
    }
</style>


<style>
/* ---------- Sidebar shell ---------- */
#sidebar.fx-sidebar {
    display: flex !important;
    flex-direction: column !important;
    flex-shrink: 0;
    width: 200px !important;
    min-width: 200px;
    height: 100vh !important;
    height: 100dvh !important;
    background-color: #405189 !important;
    color: #ffffff !important;
    border-right: 1px solid rgba(255,255,255,0.10) !important;
    overflow: hidden;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 2000;
}

/* ---------- Logo area ---------- */
#sidebar .fx-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 64px;
    padding: 0 20px;
    background-color: #405189 !important;
    border-bottom: 1px solid rgba(255,255,255,0.10);
    flex-shrink: 0;
}
#sidebar .fx-brand {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 2px;
    line-height: 1;
    text-decoration: none !important;
    color: #ffffff !important;
}
#sidebar .fx-brand-name {
    font-size: 24px;
    font-weight: 700;
    letter-spacing: 0.02em;
    color: #ffffff !important;
}
#sidebar .fx-brand-name b { color: #9db8ff !important; font-weight: 800; }
#sidebar .fx-brand small {
    font-size: 10.5px;
    font-weight: 500;
    letter-spacing: 0.06em;
    color: #c3cbe4 !important;
}

/* Close button: hidden on desktop */
#sidebar .fx-close {
    display: none;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border: 0;
    border-radius: 8px;
    background: transparent;
    color: #ffffff;
    font-size: 16px;
    cursor: pointer;
}
#sidebar .fx-close:hover { background-color: rgba(255,255,255,0.12); }
#sidebar .fx-close:focus-visible {
    outline: 2px solid #9db8ff;
    outline-offset: 2px;
}

/* ---------- Overlay: hidden on desktop ---------- */
.fx-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 2000;
    background: rgba(11, 26, 51, 0.55);
    opacity: 0;
    transition: opacity .25s ease;
}

/* ---------- Nav ---------- */
#sidebar .fx-nav {
    flex: 1;
    overflow-y: auto;
    padding: 14px 10px 40px;
    background-color: #405189 !important;
    scrollbar-width: none;
    -ms-overflow-style: none;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
}


#sidebar .fx-nav::-webkit-scrollbar { display: none; }

#sidebar .fx-item {
    display: flex !important;
    align-items: center;
    gap: 10px;
    width: 100%;
    margin-bottom: 2px;
    padding: 10px 12px !important;
    border-radius: 8px;
    border-left: 0 !important;
    text-decoration: none !important;
    color: #c3cbe4 !important;
    background: transparent !important;
    transition: background-color .18s, color .18s;
}
#sidebar .fx-item:hover {
    background-color: rgba(255,255,255,0.10) !important;
    color: #ffffff !important;
}
#sidebar .fx-item.active {
    background-color: rgba(255,255,255,0.18) !important;
    color: #ffffff !important;
    box-shadow: inset 3px 0 0 #9db8ff;
}
#sidebar .fx-item:focus-visible,
#sidebar .fx-sub-link:focus-visible {
    outline: 2px solid #9db8ff;
    outline-offset: -2px;
}

#sidebar .fx-icon {
    flex: 0 0 20px;
    width: 20px;
    margin: 0;
    text-align: center;
    font-size: 16px;
    line-height: 1;
    opacity: .75;
}
#sidebar .fx-item.active .fx-icon,
#sidebar .fx-item:hover .fx-icon { opacity: 1; }

#sidebar .fx-label {
    flex: 1;
    min-width: 0;
    font-size: 13px;
    font-weight: 500;
    line-height: 1.3;
    letter-spacing: 0;
    text-transform: none;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
#sidebar .fx-item.active .fx-label { font-weight: 600; }

/* ---------- Badge ---------- */
#sidebar .fx-badge {
    flex-shrink: 0;
    font-style: normal;
    min-width: 20px;
    padding: 1px 6px;
    border-radius: 999px;
    background-color: rgba(255,255,255,0.22) !important;
    color: #ffffff !important;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.5;
    text-align: center;
}

/* ---------- Caret ---------- */
#sidebar .fx-caret {
    flex-shrink: 0;
    margin: 0;
    font-size: 11px;
    opacity: .7;
    transition: transform .2s;
}
#sidebar .fx-caret-open { transform: rotate(180deg); }

/* ---------- Submenu ---------- */
#sidebar .fx-sub {
    margin: 2px 0 4px;
    padding: 4px 0;
    border-radius: 8px;
    background-color: rgba(0,0,0,0.12) !important;
}
#sidebar .fx-sub-link {
    display: block;
    padding: 8px 12px 8px 42px !important;
    font-size: 12.5px;
    font-weight: 500;
    text-transform: none;
    letter-spacing: 0;
    text-decoration: none !important;
    color: #b4bcd6 !important;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: color .18s, background-color .18s;
}
#sidebar .fx-sub-link:hover {
    color: #ffffff !important;
    background-color: rgba(255,255,255,0.06) !important;
}

/* ---------- Help box ---------- */
#sidebar .fx-help {
    flex-shrink: 0;
    margin: 0 12px 12px;
    padding: 12px;
    border-radius: 8px;
    background-color: rgba(255,255,255,0.08) !important;
    border: 1px solid rgba(255,255,255,0.10);
    color: #ffffff !important;
}
#sidebar .fx-help-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}
#sidebar .fx-help-row > i { font-size: 18px; color: #c3cbe4; }
#sidebar .fx-help-row strong {
    display: block;
    font-size: 12.5px;
    font-weight: 600;
    color: #ffffff;
}
#sidebar .fx-help-row small {
    display: block;
    font-size: 11px;
    color: #c3cbe4;
}
#sidebar .fx-help-btn {
    display: block;
    padding: 8px 0;
    border-radius: 6px;
    background-color: rgba(255,255,255,0.15) !important;
    color: #ffffff !important;
    font-size: 12px;
    font-weight: 600;
    text-align: center;
    text-decoration: none !important;
    transition: background-color .2s;
}
#sidebar .fx-help-btn:hover { background-color: rgba(255,255,255,0.25) !important; }

[x-cloak] { display: none !important; }

/* =========================================================
   RESPONSIVE
   ========================================================= */

/* Short screens (landscape phones, small laptops): tighten the header */
@media (max-height: 500px) {
    #sidebar .fx-header { height: 52px; }
    #sidebar .fx-nav { padding-top: 8px; }
    #sidebar .fx-item { padding: 8px 12px !important; }
}

/* ≤900px: sidebar becomes an off-canvas drawer */
@media (max-width: 900px) {
    #sidebar.fx-sidebar {
        width: min(270px, 85vw) !important;
        min-width: 0;
        transform: translateX(-100%);
        visibility: hidden;                 /* keeps hidden links out of tab order */
        transition: transform .25s ease, visibility 0s linear .25s;
        box-shadow: none;
        padding-bottom: env(safe-area-inset-bottom, 0px);
    }
    #sidebar.fx-sidebar.open {
        transform: translateX(0);
        visibility: visible;
        transition: transform .25s ease, visibility 0s;
        box-shadow: 0 0 30px rgba(0,0,0,.35);
    }

    #sidebar .fx-header {
        padding: 0 12px 0 20px;
        padding-top: env(safe-area-inset-top, 0px);
        height: calc(64px + env(safe-area-inset-top, 0px));
    }
    #sidebar .fx-close { display: inline-flex; }

    /* Larger touch targets */
    #sidebar .fx-item { padding: 12px 14px !important; }
    #sidebar .fx-label { font-size: 14px; }
    #sidebar .fx-icon { font-size: 18px; flex-basis: 22px; width: 22px; }
    #sidebar .fx-sub-link { padding: 11px 14px 11px 46px !important; font-size: 13.5px; }

    .fx-overlay { display: block; pointer-events: none; }
    .fx-overlay.open { opacity: 1; pointer-events: auto; }

    body.fx-sidebar-open { overflow: hidden; }
}

@media (prefers-reduced-motion: reduce) {
    #sidebar.fx-sidebar,
    #sidebar.fx-sidebar.open,
    .fx-overlay,
    #sidebar .fx-caret { transition: none; }
}
</style>

<script>
(function () {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var closeBtn = document.getElementById('sidebarClose');
    var mq = window.matchMedia('(max-width: 900px)');
    if (!sidebar || !overlay) return;

    function setOpen(open) {
        sidebar.classList.toggle('open', open);
        overlay.classList.toggle('open', open);
        document.body.classList.toggle('fx-sidebar-open', open);
        var burger = document.querySelector('.burger');
        if (burger) burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function toggle() { setOpen(!sidebar.classList.contains('open')); }

    // Expose for any button that wants to call it: onclick="toggleSidebar()"
    window.toggleSidebar = toggle;

    document.addEventListener('DOMContentLoaded', function () {
        var burger = document.querySelector('.burger');
        if (burger) {
            burger.setAttribute('aria-controls', 'sidebar');
            burger.setAttribute('aria-expanded', 'false');
            burger.addEventListener('click', toggle);
        }
    });

    overlay.addEventListener('click', function () { setOpen(false); });
    if (closeBtn) closeBtn.addEventListener('click', function () { setOpen(false); });

    // Close after tapping a real link on mobile
    sidebar.addEventListener('click', function (e) {
        var link = e.target.closest('a[href]');
        if (link && mq.matches && link.getAttribute('href') !== '#') setOpen(false);
    });

    // Esc closes the drawer
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar.classList.contains('open')) setOpen(false);
    });

    // Reset when resizing back to desktop
    var onChange = function (e) { if (!e.matches) setOpen(false); };
    if (mq.addEventListener) mq.addEventListener('change', onChange);
    else mq.addListener(onChange);
})();
</script>