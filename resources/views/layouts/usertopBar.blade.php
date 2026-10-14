{{-- =========================================================
     TOPBAR  (search + notifications + user menu)
     Route names jo chahiye:  client.shipments  ,  client.shipments.search  ,  logout
========================================================= --}}
<style>
    .topbar.tb {
        display: flex; align-items: center; gap: 14px;
        padding: 10px 20px; background: #fff; border-bottom: 1px solid #e2e8f0;
        position: relative; z-index: 50;
    }

    /* ---- Burger ---- */
    .topbar.tb .burger {
        border: 0; background: transparent; width: 38px; height: 38px; border-radius: 8px;
        font-size: 22px; color: #475569; cursor: pointer; flex-shrink: 0;
    }
    .topbar.tb .burger:hover { background: #f1f5f9; }

    /* ---- Search ---- */
    .tb-search { position: relative; flex: 1; max-width: 560px; }
    .tb-search-field {
        display: flex; align-items: center; gap: 10px; height: 40px; padding: 0 12px;
        background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
        transition: border-color .15s, box-shadow .15s, background .15s;
    }
    .tb-search-field:focus-within {
        background: #fff; border-color: #1d6bf3; box-shadow: 0 0 0 3px rgba(29, 107, 243, .12);
    }
    .tb-search-field > i { color: #94a3b8; font-size: 14px; }
    .tb-search-field input {
        flex: 1; min-width: 0; border: 0; outline: 0; background: transparent;
        font-size: 13px; color: #0f172a;
    }
    .tb-search-field input::placeholder { color: #94a3b8; }
    .tb-spinner {
        width: 14px; height: 14px; border-radius: 50%; display: none;
        border: 2px solid #cbd5e1; border-top-color: #1d6bf3; animation: tbSpin .7s linear infinite;
    }
    .tb-search.loading .tb-spinner { display: block; }
    @keyframes tbSpin { to { transform: rotate(360deg); } }
    @media (prefers-reduced-motion: reduce) { .tb-spinner { animation-duration: 1.6s; } }

    .tb-results {
        position: absolute; top: calc(100% + 6px); left: 0; right: 0; display: none;
        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .14); max-height: 380px; overflow-y: auto; padding: 6px;
        z-index: 1000;
    }
    .tb-search.open .tb-results { display: block; }

    .tb-item {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        padding: 10px 12px; border-radius: 8px; text-decoration: none; cursor: pointer;
    }
    .tb-item:hover, .tb-item.active { background: #eff6ff; }
    .tb-file { font-size: 13px; font-weight: 600; color: #0f172a; word-break: break-all; }
    .tb-tabs { display: flex; flex-wrap: wrap; gap: 6px; justify-content: flex-end; }
    .tb-tab {
        display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 999px;
        font-size: 11px; font-weight: 600; background: #e0ecff; color: #1d4ed8; white-space: nowrap;
    }
    .tb-tab.ocean { background: #dcfce7; color: #166534; }
    .tb-msg { padding: 14px 12px; font-size: 13px; color: #64748b; }

    /* ---- Right side ---- */
    .tb-right { margin-left: auto; display: flex; align-items: center; gap: 8px; }
    .tb-bell {
        width: 38px; height: 38px; border-radius: 8px; display: inline-flex; align-items: center;
        justify-content: center; color: #475569; font-size: 18px; text-decoration: none;
    }
    .tb-bell:hover { background: #f1f5f9; }

    /* ---- User menu ---- */
    .tb-user { position: relative; }
    .tb-user-btn {
        display: flex; align-items: center; gap: 10px; padding: 5px 8px; border: 0;
        background: transparent; border-radius: 10px; cursor: pointer; text-align: left;
    }
    .tb-user-btn:hover, .tb-user.open .tb-user-btn { background: #f1f5f9; }
    .tb-avatar {
        width: 34px; height: 34px; border-radius: 50%; background: #dbeafe; color: #1d6bf3;
        display: inline-flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;
    }
    .tb-user-info { display: flex; flex-direction: column; line-height: 1.25; }
    .tb-user-info strong { font-size: 13px; color: #0f172a; }
    .tb-user-info small { font-size: 11px; color: #64748b; }
    .tb-chev { font-size: 11px; color: #94a3b8; transition: transform .15s; }
    .tb-user.open .tb-chev { transform: rotate(180deg); }

    .tb-menu {
        position: absolute; right: 0; top: calc(100% + 6px); min-width: 220px; display: none;
        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 6px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .14); z-index: 1000;
    }
    .tb-user.open .tb-menu { display: block; }
    .tb-menu-head { padding: 8px 10px 10px; border-bottom: 1px solid #f1f5f9; margin-bottom: 6px; }
    .tb-menu-head strong { display: block; font-size: 13px; color: #0f172a; }
    .tb-menu-head small { font-size: 11px; color: #64748b; word-break: break-all; }
    .tb-menu button {
        width: 100%; display: flex; align-items: center; gap: 10px; padding: 9px 10px; border: 0;
        background: transparent; border-radius: 8px; font-size: 13px; color: #b91c1c; cursor: pointer; text-align: left;
    }
    .tb-menu button:hover { background: #fef2f2; }

    .tb-bell:focus-visible, .tb-user-btn:focus-visible, .tb-menu button:focus-visible,
    .topbar.tb .burger:focus-visible { outline: 2px solid #1d6bf3; outline-offset: 2px; }

    @media (max-width: 768px) {
        .topbar.tb { padding: 8px 12px; gap: 8px; }
        .tb-user-info, .tb-chev { display: none; }
    }
</style>

<header class="topbar tb">

    <button class="burger" id="menuToggle" type="button" aria-label="Toggle menu">
        <i class="bi bi-list"></i>
    </button>

    {{-- ===== Search ===== --}}
    <div class="tb-search" id="tbSearch"
         data-search-url="{{ route('client.shipments.search') }}"
         data-shipments-url="{{ route('client.shipments') }}">

        <div class="tb-search-field">
            <i class="bi bi-search"></i>
            <input type="text" id="tbSearchInput" autocomplete="off"
                   placeholder="Search by Booking No, MAWB, MBL, Booking No..."
                   aria-label="Search shipments">
            <span class="tb-spinner" aria-hidden="true"></span>
        </div>

        <div class="tb-results" id="tbResults" role="listbox"></div>
    </div>

    {{-- ===== Right side ===== --}}
    <div class="tb-right">

        <a href="#" class="tb-bell" aria-label="Notifications"><i class="bi bi-bell"></i></a>

        <div class="tb-user" id="tbUser">
            <button type="button" class="tb-user-btn" id="tbUserBtn" aria-haspopup="true" aria-expanded="false">
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
    /* ---------- User menu ---------- */
    var user = document.getElementById('tbUser');
    var userBtn = document.getElementById('tbUserBtn');

    userBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        var open = user.classList.toggle('open');
        userBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    /* ---------- Shipment search ---------- */
    var box = document.getElementById('tbSearch');
    var input = document.getElementById('tbSearchInput');
    var list = document.getElementById('tbResults');
    var searchUrl = box.dataset.searchUrl;
    var shipmentsUrl = box.dataset.shipmentsUrl;

    var timer = null, controller = null, items = [], activeIdx = -1;

    function clearList() { while (list.firstChild) list.removeChild(list.firstChild); items = []; activeIdx = -1; }
    function showList() { box.classList.add('open'); }
    function hideList() { box.classList.remove('open'); }

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
            // Pehle tab par le jao, aur file number bhej do taake wahan popup khul jaye
            a.href = shipmentsUrl + '?file=' + encodeURIComponent(r.file_no) + '#' + r.tabs[0].id;

            var f = document.createElement('span');
            f.className = 'tb-file';
            f.textContent = r.file_no;

            var tabs = document.createElement('span');
            tabs.className = 'tb-tabs';
            r.tabs.forEach(function (t) {
                var s = document.createElement('span');
                s.className = 'tb-tab ' + (t.mode === 'air' ? '' : 'ocean');
                var ic = document.createElement('i');
                ic.className = 'bi ' + (t.mode === 'air' ? 'bi-airplane-fill' : 'bi-water');
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
        items.forEach(function (el) { el.classList.remove('active'); });
        activeIdx = (i + items.length) % items.length;
        items[activeIdx].classList.add('active');
        items[activeIdx].scrollIntoView({ block: 'nearest' });
    }

    function search(q) {
        if (controller) controller.abort();
        controller = new AbortController();
        box.classList.add('loading');

        fetch(searchUrl + '?q=' + encodeURIComponent(q), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            signal: controller.signal,
            credentials: 'same-origin'
        })
        .then(function (res) { if (!res.ok) throw new Error('bad'); return res.json(); })
        .then(function (data) {
            box.classList.remove('loading');
            if (!data.results || !data.results.length) { message('No shipment found for "' + q + '".'); return; }
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
        if (q.length < 2) { if (controller) controller.abort(); box.classList.remove('loading'); hideList(); return; }
        timer = setTimeout(function () { search(q); }, 250);
    });

    input.addEventListener('focus', function () { if (list.firstChild) showList(); });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowDown') { e.preventDefault(); setActive(activeIdx + 1); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(activeIdx - 1); }
        else if (e.key === 'Enter' && activeIdx > -1) { e.preventDefault(); items[activeIdx].click(); }
        else if (e.key === 'Escape') { hideList(); input.blur(); }
    });

    /* ---------- Bahar click par dono dropdown band ---------- */
    document.addEventListener('click', function (e) {
        if (!box.contains(e.target)) hideList();
        if (!user.contains(e.target)) { user.classList.remove('open'); userBtn.setAttribute('aria-expanded', 'false'); }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { user.classList.remove('open'); userBtn.setAttribute('aria-expanded', 'false'); }
    });
})();
</script>