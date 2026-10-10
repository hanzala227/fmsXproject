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
        ['Notifications', 'bi-bell', 'notifications', 5],
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
                    @if (!empty($m[4]))<i class="bi bi-chevron-down caret fx-caret"></i>@endif
                </a>
            @endif
        @endforeach
    </nav>

    <!-- Help box -->
    <div class="help fx-help">
        <div class="help-row fx-help-row">
            <i class="bi bi-headset"></i>
            <div>
                <strong>Need Help?</strong>
                <small>Contact our support team</small>
            </div>
        </div>
        <a href="#" class="help-btn fx-help-btn">Contact Support</a>
    </div>
</aside>

<style>
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

/* Logo area */
#sidebar .fx-header {
    display: flex;
    align-items: center;
    height: 50px;
    padding: 0 16px;
    background-color: #405189 !important;
    border-bottom: 1px solid rgba(255,255,255,0.10);
    flex-shrink: 0;
}
#sidebar .fx-brand {
    display: flex;
    flex-direction: column;
    justify-content: center;
    line-height: 1.1;
    text-decoration: none !important;
    color: #ffffff !important;
}
#sidebar .fx-brand-name {
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.1em;
    color: #ffffff !important;
}
#sidebar .fx-brand-name b { color: #ffffff !important; }
#sidebar .fx-brand small {
    font-size: 7px;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: #c3cbe4 !important;
}

/* Nav */
#sidebar .fx-nav {
    flex: 1;
    overflow-y: auto;
    padding: 12px 0 40px;
    font-size: 10px;
    font-weight: 500;
    background-color: #405189 !important;
    scrollbar-width: none;
    -ms-overflow-style: none;
}
#sidebar .fx-nav::-webkit-scrollbar { display: none; }

#sidebar .fx-item {
    display: flex !important;
    align-items: center;
    width: 100%;
    padding: 10px 20px !important;
    border-left: 2px solid transparent;
    text-decoration: none !important;
    color: #c3cbe4 !important;
    background: transparent !important;
    transition: all 0.2s;
}
#sidebar .fx-item:hover {
    background-color: rgba(255,255,255,0.10) !important;
    color: #ffffff !important;
}
#sidebar .fx-item.active {
    background-color: rgba(255,255,255,0.15) !important;
    color: #ffffff !important;
    border-left: 2px solid #ffffff !important;
}

#sidebar .fx-icon {
    width: 20px;
    margin-right: 12px;
    text-align: center;
    font-size: 12px;
    opacity: 0.7;
}
#sidebar .fx-item.active .fx-icon,
#sidebar .fx-item:hover .fx-icon { opacity: 1; }

#sidebar .fx-label {
    flex: 1;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Badge */
#sidebar .fx-badge {
    font-style: normal;
    min-width: 16px;
    padding: 1px 5px;
    border-radius: 999px;
    background-color: rgba(255,255,255,0.20) !important;
    color: #ffffff !important;
    font-size: 8px;
    font-weight: 700;
    line-height: 1.4;
    text-align: center;
}

/* Caret */
#sidebar .fx-caret {
    margin-left: 6px;
    font-size: 8px;
    opacity: 0.6;
    transition: transform 0.2s;
}
#sidebar .fx-caret-open { transform: rotate(180deg); }

/* Submenu */
#sidebar .fx-sub {
    background-color: #3b4b7a !important;
    border-top: 1px solid rgba(255,255,255,0.05);
}
#sidebar .fx-sub-link {
    display: block;
    padding: 10px 20px 10px 52px !important;
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    text-decoration: none !important;
    color: #a0a8c1 !important;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: all 0.2s;
}
#sidebar .fx-sub-link:last-child { border-bottom: none; }
#sidebar .fx-sub-link:hover {
    color: #ffffff !important;
    background-color: rgba(255,255,255,0.05) !important;
}

/* Help box */
#sidebar .fx-help {
    flex-shrink: 0;
    margin: 0 12px 12px;
    padding: 12px;
    border-radius: 4px;
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
#sidebar .fx-help-row > i { font-size: 16px; color: #c3cbe4; }
#sidebar .fx-help-row strong {
    display: block;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #ffffff;
}
#sidebar .fx-help-row small {
    display: block;
    font-size: 8px;
    color: #c3cbe4;
}
#sidebar .fx-help-btn {
    display: block;
    padding: 7px 0;
    border-radius: 3px;
    background-color: rgba(255,255,255,0.15) !important;
    color: #ffffff !important;
    font-size: 8px;
    font-weight: 700;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    text-decoration: none !important;
    transition: background-color 0.2s;
}
#sidebar .fx-help-btn:hover { background-color: rgba(255,255,255,0.25) !important; }

[x-cloak] { display: none !important; }
</style>