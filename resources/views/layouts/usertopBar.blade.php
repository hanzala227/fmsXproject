{{-- =========================================================
     TOPBAR: Module Dropdown + Shipment Search + Notifications
     + User Menu + Responsive Mobile Search
========================================================= --}}

@php
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
        ['Notifications', 'bi-bell', 'notifications'],
        ['Reports', 'bi-file-earmark-bar-graph', 'reports'],
        ['Company', 'bi-building', 'company'],
        // ['Settings', 'bi-gear', 'settings'],
    ];

    $moduleLinks = [];

    foreach ($menu as $m) {
        $routeKey = $m[2] ?? '';

        // Dashboard ke liye client.index; baqi client.{key}
        $routeName = $routeKey === 'client'
            ? 'client.index'
            : 'client.' . $routeKey;

        if (\Illuminate\Support\Facades\Route::has($routeName)) {
            $moduleLinks[] = [
                'label' => $m[0],
                'icon' => $m[1],
                'url' => route($routeName),
                'active' => request()->routeIs($routeName)
                    || request()->routeIs($routeName . '.*'),
            ];
        }
    }
@endphp

<style>
    .topbar.tb {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 10px 20px;
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
        position: relative;
        z-index: 1998;
        flex-shrink: 0;
        margin-bottom: 8px;
    }

    /* Burger */
    .topbar.tb .burger {
        display: none;
        align-items: center;
        justify-content: center;
        border: 0;
        background: transparent;
        width: 40px;
        height: 40px;
        border-radius: 8px;
        font-size: 22px;
        color: #475569;
        cursor: pointer;
        flex-shrink: 0;
    }

    .topbar.tb .burger:hover {
        background: #f1f5f9;
    }

    /* Search */
    .tb-search {
        position: relative;
        flex: 1;
        min-width: 0;
        max-width: 560px;
    }

    .tb-search-field {
        display: flex;
        align-items: center;
        gap: 10px;
        height: 40px;
        padding: 0 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        transition: border-color .15s, box-shadow .15s, background .15s;
    }

    .tb-search-field:focus-within {
        background: #fff;
        border-color: #1d6bf3;
        box-shadow: 0 0 0 3px rgba(29,107,243,.12);
    }

    .tb-search-field > i {
        color: #94a3b8;
        font-size: 14px;
        flex-shrink: 0;
    }

    .tb-search-field input {
        flex: 1;
        min-width: 0;
        width: 100%;
        border: 0;
        outline: 0;
        background: transparent;
        font-size: 13px;
        color: #0f172a;
    }

    .tb-search-field input::placeholder {
        color: #94a3b8;
    }

    .tb-spinner {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        display: none;
        flex-shrink: 0;
        border: 2px solid #cbd5e1;
        border-top-color: #1d6bf3;
        animation: tbSpin .7s linear infinite;
    }

    .tb-search.loading .tb-spinner {
        display: block;
    }

    @keyframes tbSpin {
        to { transform: rotate(360deg); }
    }

    .tb-search-back {
        display: none;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 28px;
        height: 28px;
        border: 0;
        border-radius: 6px;
        background: transparent;
        color: #475569;
        font-size: 16px;
        cursor: pointer;
    }

    .tb-search-back:hover {
        background: #e2e8f0;
    }

    /* Shipment search results */
    .tb-results {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        display: none;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 12px 30px rgba(15,23,42,.14);
        max-height: 380px;
        overflow-y: auto;
        padding: 6px;
        z-index: 1002;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
    }

    .tb-search.open .tb-results {
        display: block;
    }

    .tb-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 8px;
        text-decoration: none;
        cursor: pointer;
    }

    .tb-item:hover,
    .tb-item.active {
        background: #eff6ff;
    }

    .tb-file {
        font-size: 13px;
        font-weight: 600;
        color: #0f172a;
        word-break: break-all;
    }

    .tb-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        justify-content: flex-end;
    }

    .tb-tab {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        background: #e0ecff;
        color: #1d4ed8;
        white-space: nowrap;
    }

    .tb-tab.ocean {
        background: #dcfce7;
        color: #166534;
    }

    .tb-msg {
        padding: 14px 12px;
        font-size: 13px;
        color: #64748b;
    }

    /* Module dropdown */
    .tb-modules {
        position: relative;
        flex-shrink: 0;
    }

    .tb-modules-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 40px;
        padding: 0 12px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        color: #334155;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        transition: background .15s, border-color .15s;
    }

    .tb-modules-btn:hover,
    .tb-modules.open .tb-modules-btn {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #1d4ed8;
    }

    .tb-modules-btn .tb-module-chevron {
        font-size: 11px;
        transition: transform .15s;
    }

    .tb-modules.open .tb-module-chevron {
        transform: rotate(180deg);
    }

    .tb-modules-menu {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        width: 260px;
        max-width: calc(100vw - 24px);
        display: none;
        padding: 7px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 12px 30px rgba(15,23,42,.14);
        z-index: 1003;
        max-height: min(65vh, 430px);
        overflow-y: auto;
    }

    .tb-modules.open .tb-modules-menu {
        display: block;
    }

    .tb-modules-heading {
        padding: 9px 11px;
        margin-bottom: 5px;
        border-bottom: 1px solid #f1f5f9;
        color: #94a3b8;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .tb-module-link {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 10px 11px;
        border-radius: 8px;
        text-decoration: none;
        color: #475569;
        font-size: 13px;
        font-weight: 500;
        transition: background .15s, color .15s;
    }

    .tb-module-link > i:first-child {
        width: 19px;
        text-align: center;
        font-size: 16px;
        color: #64748b;
    }

    .tb-module-link:hover,
    .tb-module-link.active {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .tb-module-link:hover > i:first-child,
    .tb-module-link.active > i:first-child {
        color: #1d6bf3;
    }

    .tb-module-current {
        margin-left: auto;
        font-size: 12px;
        color: #1d6bf3;
    }

    /* Right side */
    .tb-right {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .tb-bell,
    .tb-search-toggle {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #475569;
        font-size: 18px;
        text-decoration: none;
        border: 0;
        background: transparent;
        cursor: pointer;
    }

    .tb-bell:hover,
    .tb-search-toggle:hover {
        background: #f1f5f9;
    }

    .tb-search-toggle {
        display: none;
    }

    /* User menu */
    .tb-user {
        position: relative;
    }

    .tb-user-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 5px 8px;
        border: 0;
        background: transparent;
        border-radius: 10px;
        cursor: pointer;
        text-align: left;
    }

    .tb-user-btn:hover,
    .tb-user.open .tb-user-btn {
        background: #f1f5f9;
    }

    .tb-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #dbeafe;
        color: #1d6bf3;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .tb-user-info {
        display: flex;
        flex-direction: column;
        line-height: 1.25;
        min-width: 0;
    }

    .tb-user-info strong {
        font-size: 13px;
        color: #0f172a;
    }

    .tb-user-info small {
        font-size: 11px;
        color: #64748b;
    }

    .tb-chev {
        font-size: 11px;
        color: #94a3b8;
        transition: transform .15s;
    }

    .tb-user.open .tb-chev {
        transform: rotate(180deg);
    }

    .tb-menu {
        position: absolute;
        right: 0;
        top: calc(100% + 6px);
        min-width: 220px;
        max-width: calc(100vw - 24px);
        display: none;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 6px;
        box-shadow: 0 12px 30px rgba(15,23,42,.14);
        z-index: 1003;
    }

    .tb-user.open .tb-menu {
        display: block;
    }

    .tb-menu-head {
        padding: 8px 10px 10px;
        border-bottom: 1px solid #f1f5f9;
        margin-bottom: 6px;
    }

    .tb-menu-head strong {
        display: block;
        font-size: 13px;
        color: #0f172a;
    }

    .tb-menu-head small {
        font-size: 11px;
        color: #64748b;
        word-break: break-all;
    }

    .tb-menu button {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 10px;
        border: 0;
        background: transparent;
        border-radius: 8px;
        font-size: 13px;
        color: #b91c1c;
        cursor: pointer;
        text-align: left;
    }

    .tb-menu button:hover {
        background: #fef2f2;
    }

    .tb-bell:focus-visible,
    .tb-search-toggle:focus-visible,
    .tb-search-back:focus-visible,
    .tb-user-btn:focus-visible,
    .tb-menu button:focus-visible,
    .tb-modules-btn:focus-visible,
    .tb-module-link:focus-visible,
    .topbar.tb .burger:focus-visible {
        outline: 2px solid #1d6bf3;
        outline-offset: 2px;
    }

    /* Responsive */
    @media (max-width: 900px) {
        .topbar.tb {
            padding: 8px 14px;
            gap: 10px;
        }

        .topbar.tb .burger {
            display: inline-flex;
            z-index: 2001;
        }

        .tb-search {
            max-width: none;
        }
    }

    @media (max-width: 768px) {
        .topbar.tb {
            padding: 8px 12px;
            gap: 8px;
        }

        .tb-user-info,
        .tb-chev {
            display: none;
        }

        .tb-user-btn {
            padding: 3px;
        }

        .tb-search-toggle {
            display: inline-flex;
        }

        .topbar.tb .tb-search {
            display: none;
        }

        .topbar.tb.searching .tb-search {
            display: block;
            flex: 1;
        }

        .topbar.tb.searching .burger,
        .topbar.tb.searching .tb-right,
        .topbar.tb.searching .tb-modules {
            display: none;
        }

        .tb-search-back {
            display: inline-flex;
        }

        .tb-search-field {
            height: 42px;
        }

        .tb-search-field input {
            font-size: 16px;
        }

        .tb-results {
            max-height: min(60vh, 380px);
        }

        .tb-modules-menu {
            position: fixed;
            top: 65px;
            left: 12px;
            right: 12px;
            width: auto;
            max-width: none;
            max-height: calc(100vh - 85px);
        }
    }

    @media (max-width: 480px) {
        .tb-right {
            gap: 2px;
        }

        .tb-item {
            flex-wrap: wrap;
            gap: 6px;
        }

        .tb-tabs {
            justify-content: flex-start;
        }

        .tb-modules-btn {
            padding: 0 9px;
            gap: 6px;
            font-size: 12px;
        }
    }
</style>

<header class="topbar tb">

    {{-- Burger --}}
    <button class="burger" id="menuToggle" type="button" aria-label="Toggle menu">
        <i class="bi bi-list"></i>
    </button>

    {{-- Shipment Search --}}
    <div class="tb-search" id="tbSearch"
         data-search-url="{{ route('client.shipments.search') }}"
         data-shipments-url="{{ route('client.shipments') }}">

        <div class="tb-search-field">
            <button type="button" class="tb-search-back" id="tbSearchBack" aria-label="Close search">
                <i class="bi bi-arrow-left"></i>
            </button>

            <i class="bi bi-search"></i>

            <input type="text" id="tbSearchInput" autocomplete="off"
                   placeholder="Search by Booking No, MAWB, MBL..."
                   aria-label="Search shipments">

            <span class="tb-spinner" aria-hidden="true"></span>
        </div>

        <div class="tb-results" id="tbResults" role="listbox"></div>
    </div>

    {{-- Module Dropdown --}}
    <div class="tb-modules" id="tbModules">

        <button type="button"
                class="tb-modules-btn"
                id="tbModulesBtn"
                aria-haspopup="true"
                aria-expanded="false">

            <i class="bi bi-grid"></i>
            <span>Modules</span>
            <i class="bi bi-chevron-down tb-module-chevron"></i>
        </button>

        <div class="tb-modules-menu" id="tbModulesMenu" role="menu">
            <div class="tb-modules-heading">Client Portal Modules</div>

            @forelse($moduleLinks as $module)
                <a href="{{ $module['url'] }}"
                   class="tb-module-link {{ $module['active'] ? 'active' : '' }}"
                   role="menuitem">

                    <i class="bi {{ $module['icon'] }}"></i>
                    <span>{{ $module['label'] }}</span>

                    @if($module['active'])
                        <i class="bi bi-check2 tb-module-current"></i>
                    @endif
                </a>
            @empty
                <div class="tb-msg">
                    No module routes were found. Please check the route names.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Right side --}}
    <div class="tb-right">

        <button type="button" class="tb-search-toggle" id="tbSearchToggle"
                aria-label="Search shipments" aria-expanded="false">
            <i class="bi bi-search"></i>
        </button>

        <a href="{{ \Illuminate\Support\Facades\Route::has('client.notifications') ? route('client.notifications') : '#' }}"
           class="tb-bell" aria-label="Notifications">
            <i class="bi bi-bell"></i>
        </a>

        <div class="tb-user" id="tbUser">
            <button type="button" class="tb-user-btn" id="tbUserBtn"
                    aria-haspopup="true" aria-expanded="false" aria-label="Account menu">

                <span class="tb-avatar"><i class="bi bi-person-fill"></i></span>

                <span class="tb-user-info">
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>{{ auth()->user()->email }}</small>
                </span>

                <i class="bi bi-chevron-down tb-chev"></i>
            </button>

            <div class="tb-menu" role="menu">
                <div class="tb-menu-head">
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>{{ auth()->user()->email }}</small>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" role="menuitem">
                        <i class="bi bi-box-arrow-right"></i> Log out
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<script>
(function () {
    var header = document.querySelector('.topbar.tb');
    if (!header) return;

    /* ---------- Elements ---------- */
    var user = document.getElementById('tbUser');
    var userBtn = document.getElementById('tbUserBtn');
    var modules = document.getElementById('tbModules');
    var modulesBtn = document.getElementById('tbModulesBtn');

    var box = document.getElementById('tbSearch');
    var input = document.getElementById('tbSearchInput');
    var list = document.getElementById('tbResults');
    var searchUrl = box.dataset.searchUrl;
    var shipmentsUrl = box.dataset.shipmentsUrl;

    var sToggle = document.getElementById('tbSearchToggle');
    var sBack = document.getElementById('tbSearchBack');
    var mqSmall = window.matchMedia('(max-width: 768px)');

    var timer = null;
    var controller = null;
    var items = [];
    var activeIdx = -1;

    /* ---------- Dropdowns ---------- */
    function closeUser() {
        user.classList.remove('open');
        userBtn.setAttribute('aria-expanded', 'false');
    }

    function closeModules() {
        modules.classList.remove('open');
        modulesBtn.setAttribute('aria-expanded', 'false');
    }

    userBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        closeModules();

        var isOpen = user.classList.toggle('open');
        userBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    modulesBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        closeUser();

        var isOpen = modules.classList.toggle('open');
        modulesBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    /* ---------- Mobile search ---------- */
    function openSearch() {
        header.classList.add('searching');
        sToggle.setAttribute('aria-expanded', 'true');
        input.focus();
    }

    function closeSearch() {
        header.classList.remove('searching');
        sToggle.setAttribute('aria-expanded', 'false');
        hideList();
    }

    sToggle.addEventListener('click', function (e) {
        e.stopPropagation();
        openSearch();
    });

    sBack.addEventListener('click', function (e) {
        e.stopPropagation();
        closeSearch();
    });

    var onSizeChange = function (e) {
        if (!e.matches) closeSearch();
    };

    if (mqSmall.addEventListener) {
        mqSmall.addEventListener('change', onSizeChange);
    } else {
        mqSmall.addListener(onSizeChange);
    }

    /* ---------- Shipment search results ---------- */
    function clearList() {
        while (list.firstChild) list.removeChild(list.firstChild);
        items = [];
        activeIdx = -1;
    }

    function showList() {
        box.classList.add('open');
    }

    function hideList() {
        box.classList.remove('open');
    }

    function message(text) {
        clearList();

        var d = document.createElement('div');
        d.className = 'tb-msg';
        d.textContent = text;

        list.appendChild(d);
        showList();
    }

    function render(results) {
        clearList();

        results.forEach(function (r) {
            var a = document.createElement('a');
            a.className = 'tb-item';
            a.setAttribute('role', 'option');

            var firstTab = r.tabs && r.tabs.length ? r.tabs[0].id : '';
            a.href = shipmentsUrl
                + '?file=' + encodeURIComponent(r.file_no)
                + (firstTab ? '#' + encodeURIComponent(firstTab) : '');

            var f = document.createElement('span');
            f.className = 'tb-file';
            f.textContent = r.file_no;

            var tabs = document.createElement('span');
            tabs.className = 'tb-tabs';

            (r.tabs || []).forEach(function (t) {
                var s = document.createElement('span');
                s.className = 'tb-tab ' + (t.mode === 'air' ? '' : 'ocean');

                var ic = document.createElement('i');
                ic.className = 'bi ' + (
                    t.mode === 'air' ? 'bi-airplane-fill' : 'bi-water'
                );

                s.appendChild(ic);
                s.appendChild(document.createTextNode(t.label));
                tabs.appendChild(s);
            });

            a.appendChild(f);
            a.appendChild(tabs);
            list.appendChild(a);
            items.push(a);
        });

        showList();
    }

    function setActive(i) {
        if (!items.length) return;

        items.forEach(function (el) {
            el.classList.remove('active');
        });

        activeIdx = (i + items.length) % items.length;
        items[activeIdx].classList.add('active');
        items[activeIdx].scrollIntoView({ block: 'nearest' });
    }

    function search(q) {
        if (controller) controller.abort();

        controller = new AbortController();
        box.classList.add('loading');

        fetch(searchUrl + '?q=' + encodeURIComponent(q), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            signal: controller.signal,
            credentials: 'same-origin'
        })
        .then(function (res) {
            if (!res.ok) throw new Error('Search request failed');
            return res.json();
        })
        .then(function (data) {
            box.classList.remove('loading');

            if (!data.results || !data.results.length) {
                message('No shipment found for "' + q + '".');
                return;
            }

            render(data.results);
        })
        .catch(function (err) {
            if (err.name === 'AbortError') return;

            box.classList.remove('loading');
            message('Search is not available right now. Please try again.');
        });
    }

    input.addEventListener('input', function () {
        var q = input.value.trim();
        clearTimeout(timer);

        if (q.length < 2) {
            if (controller) controller.abort();
            box.classList.remove('loading');
            hideList();
            return;
        }

        timer = setTimeout(function () {
            search(q);
        }, 250);
    });

    input.addEventListener('focus', function () {
        if (list.firstChild) showList();
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            setActive(activeIdx + 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setActive(activeIdx - 1);
        } else if (e.key === 'Enter' && activeIdx > -1) {
            e.preventDefault();
            items[activeIdx].click();
        } else if (e.key === 'Escape') {
            hideList();
            input.blur();
            if (mqSmall.matches) closeSearch();
        }
    });

    /* ---------- Outside click / Escape ---------- */
    document.addEventListener('click', function (e) {
        if (box && !box.contains(e.target)) hideList();
        if (user && !user.contains(e.target)) closeUser();
        if (modules && !modules.contains(e.target)) closeModules();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeUser();
            closeModules();
        }
    });
})();
</script>
