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

<aside class="sidebar fx-sidebar" id="sidebar">

    <!-- Logo Area -->
    <div class="fx-header">
        <a href="{{ route('client.index') }}" class="fx-brand">
            <span class="fx-brand-name">Freit<b>X</b></span>
            <small>Freight Forwarder</small>
        </a>
    </div>

    <nav class="fx-nav menu">
        @foreach ($menu as $m)
            @php
                $active = Route::has($m[2]) && request()->routeIs($m[2] . '*');
                $href   = $m[2] ?: '#';
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

<style>
/* ---------- Sidebar shell ---------- */
#sidebar.fx-sidebar {
    display: flex !important;
    flex-direction: column !important;
    flex-shrink: 0;
    width: 200px !important;
    min-width: 200px;
    height: 100vh !important;
    background-color: #405189 !important;
    color: #ffffff !important;
    border-right: 1px solid rgba(255,255,255,0.10) !important;
    overflow: hidden;
    position: fixed;
    z-index: 2000;
}

/* ---------- Logo area ---------- */
#sidebar .fx-header {
    display: flex;
    align-items: center;
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

/* ---------- Nav ---------- */
#sidebar .fx-nav {
    flex: 1;
    overflow-y: auto;
    padding: 14px 10px 40px;
    background-color: #405189 !important;
    scrollbar-width: none;
    -ms-overflow-style: none;
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
</style>