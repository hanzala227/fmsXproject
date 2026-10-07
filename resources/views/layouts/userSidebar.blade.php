@php
    // label, icon, route name, optional badge
    $menu = [
        ['Dashboard', 'bi-box-seam', 'client'],
        ['My Shipments', 'bi-boxes', 'shipments'],
        ['Track Shipment', 'bi-geo-alt', 'tracking'],
        // ['Bookings', 'bi-calendar-check', 'bookings'],
        ['Quotes', 'bi-file-earmark-text', 'quotes'],
        // ['Documents', 'bi-file-earmark', 'documents'],
        ['Invoices & Payments', 'bi-cash-stack', 'invoices'],
        // ['Requests', 'bi-download', 'requests'],
        ['Messages', 'bi-envelope', 'messages', 3],
        ['Notifications', 'bi-bell', 'notifications', 5],
        ['Reports', 'bi-file-earmark-bar-graph', 'reports'],
        ['Company', 'bi-building', 'company', null, true, [
        ['Profile', 'company'],
        ['Users',   'company'],
]],
        ['Settings', 'bi-gear', 'settings'],
    ];
@endphp
<aside class="sidebar" id="sidebar">
    <a href="{{ url('client') }}" class="brand">
        <span class="brand-name">Freit<b>X</b></span>
        <small>FREIGHT FORWARDER</small>
    </a>
    <nav class="menu">
        @foreach ($menu as $m)
            @php
                $active = Route::has($m[2]) && request()->routeIs($m[2] . '*');
                $href =$m[2]?: '#';
            @endphp
            <a href="{{ $href }}" class="menu-item {{ $active ? 'active' : '' }}">
                <i class="bi {{ $m[1] }}"></i><span>{{ $m[0] }}</span>
                @if (!empty($m[3]))<em class="badge-dot">{{ $m[3] }}</em>@endif
                @if (!empty($m[4]))<i class="bi bi-chevron-down caret"></i>@endif
            </a>
        @endforeach
    </nav>
    <div class="help">
        <div class="help-row"><i class="bi bi-headset"></i>
            <div><strong>Need Help?</strong><small>Contact our support team</small></div>
        </div>
        <a href="#" class="help-btn">Contact Support</a>
    </div>
</aside>