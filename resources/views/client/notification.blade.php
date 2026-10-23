@extends('layouts.userapp')
@section('title', 'Notifications & Activity Stream')

@push('styles')
<style>
    /* ==========================================================================
       INTERNATIONAL FREIGHT STANDARD CLIENT PORTAL — NOTIFICATIONS STREAM
       Theme: Enterprise Freight Blue / Slate / Clean Neutral (Zero tacky emojis)
       ========================================================================== */

    :root {
        --fms-navy: #0f172a;
        --fms-blue: #1d4ed8;
        --fms-blue-hover: #1e40af;
        --fms-blue-light: #eff6ff;
        --fms-slate-50: #f8fafc;
        --fms-slate-100: #f1f5f9;
        --fms-slate-200: #e2e8f0;
        --fms-slate-300: #cbd5e1;
        --fms-slate-400: #94a3b8;
        --fms-slate-600: #475569;
        --fms-slate-700: #334155;
        --fms-slate-900: #0f172a;
        --fms-emerald: #059669;
        --fms-emerald-bg: #ecfdf5;
        --fms-amber: #d97706;
        --fms-amber-bg: #fffbeb;
    }

    .fms-notif-page {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        color: var(--fms-slate-900);
        background: transparent;
        padding-bottom: 40px;
    }

    /* Page Header Bar */
    .fms-header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--fms-slate-200);
    }
    .fms-title-group h1 {
        font-size: 20px;
        font-weight: 700;
        color: var(--fms-navy);
        margin: 0 0 4px 0;
        letter-spacing: -0.01em;
    }
    .fms-title-group p {
        font-size: 13px;
        color: var(--fms-slate-600);
        margin: 0;
    }
    .fms-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .fms-btn-mark-read {
        background: #ffffff;
        border: 1px solid var(--fms-slate-200);
        border-radius: 6px;
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 600;
        color: var(--fms-slate-700);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s ease;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    }
    .fms-btn-mark-read:hover {
        background: var(--fms-slate-50);
        border-color: var(--fms-blue);
        color: var(--fms-blue);
    }

    /* Top KPI Stats Grid */
    .fms-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }
    @media (max-width: 1024px) {
        .fms-kpi-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .fms-kpi-grid { grid-template-columns: 1fr; }
    }
    .fms-kpi-card {
        background: #ffffff;
        border: 1px solid var(--fms-slate-200);
        border-radius: 8px;
        padding: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .fms-kpi-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.06);
    }
    .fms-kpi-info h3 {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--fms-slate-600);
        margin: 0 0 6px 0;
    }
    .fms-kpi-info .fms-kpi-val {
        font-size: 24px;
        font-weight: 700;
        color: var(--fms-navy);
        line-height: 1;
        margin: 0;
    }
    .fms-kpi-icon {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        background: var(--fms-slate-100);
        color: var(--fms-slate-700);
    }
    .fms-kpi-icon.ocean { background: #eff6ff; color: #1d4ed8; }
    .fms-kpi-icon.green { background: #ecfdf5; color: #059669; }
    .fms-kpi-icon.amber { background: #fffbeb; color: #b45309; }

    /* Control Panel: Tabs & Filters */
    .fms-controls-card {
        background: #ffffff;
        border: 1px solid var(--fms-slate-200);
        border-radius: 8px;
        margin-bottom: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }
    .fms-tabs-container {
        display: flex;
        align-items: center;
        border-bottom: 1px solid var(--fms-slate-200);
        padding: 0 16px;
        overflow-x: auto;
        gap: 8px;
        scrollbar-width: thin;
    }
    .fms-tab-btn {
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        padding: 12px 14px;
        font-size: 13px;
        font-weight: 600;
        color: var(--fms-slate-600);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
        transition: all 0.15s ease;
    }
    .fms-tab-btn:hover {
        color: var(--fms-blue);
    }
    .fms-tab-btn.active {
        color: var(--fms-blue);
        border-bottom-color: var(--fms-blue);
    }
    .fms-tab-count {
        background: var(--fms-slate-100);
        color: var(--fms-slate-700);
        font-size: 11px;
        font-weight: 600;
        padding: 2px 7px;
        border-radius: 10px;
    }
    .fms-tab-btn.active .fms-tab-count {
        background: var(--fms-blue-light);
        color: var(--fms-blue);
    }

    /* Filter & Search Bar */
    .fms-search-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding: 12px 16px;
        background: var(--fms-slate-50);
        border-radius: 0 0 8px 8px;
    }
    .fms-search-box {
        position: relative;
        flex: 1;
        min-width: 240px;
        max-width: 480px;
    }
    .fms-search-box i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--fms-slate-400);
        font-size: 14px;
    }
    .fms-search-input {
        width: 100%;
        height: 36px;
        padding: 6px 12px 6px 36px;
        font-size: 13px;
        border: 1px solid var(--fms-slate-200);
        border-radius: 6px;
        background: #ffffff;
        color: var(--fms-slate-900);
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .fms-search-input:focus {
        border-color: var(--fms-blue);
        box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1);
    }

    /* Notifications Stream Cards */
    .fms-stream-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .fms-notif-item {
        background: #ffffff;
        border: 1px solid var(--fms-slate-200);
        border-radius: 8px;
        padding: 16px 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        display: flex;
        align-items: flex-start;
        gap: 16px;
        transition: all 0.15s ease;
        position: relative;
    }
    .fms-notif-item:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .fms-notif-item.unread {
        border-left: 3px solid var(--fms-blue);
        background: #fcfdfe;
    }
    .fms-notif-icon-box {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .fms-notif-content {
        flex: 1;
        min-width: 0;
    }
    .fms-notif-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 4px;
        flex-wrap: wrap;
    }
    .fms-notif-badges {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .fms-badge-tag {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 2px 8px;
        border-radius: 4px;
        border: 1px solid transparent;
    }
    .fms-badge-tag.shipment { background: #eff6ff; color: #1d4ed8; border-color: #dbeafe; }
    .fms-badge-tag.milestone { background: #ecfdf5; color: #059669; border-color: #d1fae5; }
    .fms-badge-tag.invoice   { background: #fffbeb; color: #b45309; border-color: #fef3c7; }
    .fms-badge-tag.quote     { background: #eef2ff; color: #4338ca; border-color: #e0e7ff; }

    .fms-notif-ref {
        font-size: 11px;
        font-family: ui-monospace, monospace;
        font-weight: 600;
        background: var(--fms-slate-100);
        color: var(--fms-slate-700);
        padding: 2px 6px;
        border-radius: 4px;
    }
    .fms-notif-time {
        font-size: 12px;
        color: var(--fms-slate-400);
        display: flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
    }
    .fms-notif-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--fms-navy);
        margin: 0 0 4px 0;
        line-height: 1.3;
    }
    .fms-notif-msg {
        font-size: 13px;
        color: var(--fms-slate-600);
        line-height: 1.45;
        margin: 0 0 10px 0;
    }
    .fms-notif-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .fms-notif-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 600;
        color: var(--fms-blue);
        text-decoration: none;
        padding: 4px 10px;
        border-radius: 4px;
        background: var(--fms-blue-light);
        transition: all 0.15s ease;
    }
    .fms-notif-action-btn:hover {
        background: #dbeafe;
        color: var(--fms-blue-hover);
    }
    .fms-unread-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--fms-blue);
        display: inline-block;
        flex-shrink: 0;
    }

    /* Empty state */
    .fms-empty-state {
        background: #ffffff;
        border: 1px solid var(--fms-slate-200);
        border-radius: 8px;
        text-align: center;
        padding: 56px 20px;
        color: var(--fms-slate-600);
    }
    .fms-empty-state i {
        font-size: 40px;
        color: var(--fms-slate-400);
        margin-bottom: 10px;
    }
</style>
@endpush

@section('content')

<div class="fms-notif-page">

    <!-- Header Bar -->
    <div class="fms-header-bar">
        <div class="fms-title-group">
            <h1>Notifications & Activity Stream</h1>
            <p>Real-time updates on active freight shipments, milestones, issued invoices, and quotations.</p>
        </div>
        <div class="fms-header-actions">
            <button type="button" class="fms-btn-mark-read" onclick="markAllNotificationsAsRead()">
                <i class="bi bi-check2-all"></i> Mark all as read
            </button>
            <div class="fms-meta-tag" style="background:#fff; border:1px solid #e2e8f0; border-radius:6px; padding:6px 12px; font-size:12px;">
                <i class="bi bi-building"></i>
                <span>Account: <strong>{{ auth()->user()->company->name ?? 'Customer Account' }}</strong></span>
            </div>
        </div>
    </div>

    <!-- Top KPI Stats Grid -->
    <div class="fms-kpi-grid">
        <div class="fms-kpi-card">
            <div class="fms-kpi-info">
                <h3>Total Stream Events</h3>
                <div class="fms-kpi-val" id="kpi-total-val">{{ $totalNotifications }}</div>
            </div>
            <div class="fms-kpi-icon">
                <i class="bi bi-bell"></i>
            </div>
        </div>
        <div class="fms-kpi-card">
            <div class="fms-kpi-info">
                <h3>Shipment Bookings</h3>
                <div class="fms-kpi-val">{{ $shipmentCount }}</div>
            </div>
            <div class="fms-kpi-icon ocean">
                <i class="bi bi-box-seam"></i>
            </div>
        </div>
        <div class="fms-kpi-card">
            <div class="fms-kpi-info">
                <h3>Milestone Alerts</h3>
                <div class="fms-kpi-val">{{ $milestoneCount }}</div>
            </div>
            <div class="fms-kpi-icon green">
                <i class="bi bi-geo-alt"></i>
            </div>
        </div>
        <div class="fms-kpi-card">
            <div class="fms-kpi-info">
                <h3>Financial & Quotes</h3>
                <div class="fms-kpi-val">{{ $invoiceCount + $quoteCount }}</div>
            </div>
            <div class="fms-kpi-icon amber">
                <i class="bi bi-receipt-cutoff"></i>
            </div>
        </div>
    </div>

    <!-- Control Panel: Tabs & Filters -->
    <div class="fms-controls-card">
        <div class="fms-tabs-container">
            <button type="button" class="fms-tab-btn active" onclick="switchNotifTab('all', this)">
                <i class="bi bi-grid"></i> All Notifications <span class="fms-tab-count">{{ $totalNotifications }}</span>
            </button>
            <button type="button" class="fms-tab-btn" onclick="switchNotifTab('shipment', this)">
                <i class="bi bi-box-seam"></i> Shipment Bookings <span class="fms-tab-count">{{ $shipmentCount }}</span>
            </button>
            <button type="button" class="fms-tab-btn" onclick="switchNotifTab('milestone', this)">
                <i class="bi bi-geo-alt"></i> Transit Milestones <span class="fms-tab-count">{{ $milestoneCount }}</span>
            </button>
            <button type="button" class="fms-tab-btn" onclick="switchNotifTab('invoice', this)">
                <i class="bi bi-receipt-cutoff"></i> Invoices & Billing <span class="fms-tab-count">{{ $invoiceCount }}</span>
            </button>
            <button type="button" class="fms-tab-btn" onclick="switchNotifTab('quote', this)">
                <i class="bi bi-file-earmark-check"></i> Quotations <span class="fms-tab-count">{{ $quoteCount }}</span>
            </button>
        </div>

        <div class="fms-search-bar">
            <div class="fms-search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="notif-search-input" class="fms-search-input" placeholder="Search notifications by Booking No, Port, MBL, Invoice #..." oninput="filterNotifications()">
            </div>
            <div style="font-size:12px; color:var(--fms-slate-600); display:flex; align-items:center; gap:6px;">
                <i class="bi bi-lightning-charge" style="color:var(--fms-blue);"></i> Real-time event sync active
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="fms-stream-list" id="notif-stream-container">
        @forelse($sortedNotifications as $n)
            <div class="fms-notif-item unread"
                 id="notif-card-{{ $n['id'] }}"
                 data-id="{{ $n['id'] }}"
                 data-category="{{ $n['category'] }}"
                 data-search="{{ strtolower($n['title'].' '.$n['message'].' '.$n['ref_no'].' '.$n['type']) }}"
                 onclick="toggleNotifRead('{{ $n['id'] }}')">
                
                <div class="fms-notif-icon-box" style="background: {{ $n['icon_bg'] }}; color: {{ $n['icon_color'] }};">
                    <i class="bi {{ $n['icon_icon'] ?? $n['icon'] }}"></i>
                </div>

                <div class="fms-notif-content">
                    <div class="fms-notif-header">
                        <div class="fms-notif-badges">
                            <span class="fms-badge-tag {{ $n['category'] }}">{{ $n['badge'] }}</span>
                            <span class="fms-notif-ref">{{ $n['ref_no'] }}</span>
                            <span class="fms-unread-dot" id="dot-{{ $n['id'] }}" title="Unread event"></span>
                        </div>
                        <div class="fms-notif-time">
                            <i class="bi bi-clock"></i>
                            <span>{{ $n['time'] instanceof \Carbon\Carbon ? $n['time']->diffForHumans() : \Carbon\Carbon::parse($n['time'])->diffForHumans() }}</span>
                        </div>
                    </div>

                    <h3 class="fms-notif-title">{{ $n['title'] }}</h3>
                    <p class="fms-notif-msg">{!! $n['message'] !!}</p>

                    <div class="fms-notif-footer" onclick="event.stopPropagation()">
                        <a href="{{ $n['link'] }}" class="fms-notif-action-btn">
                            {{ $n['link_text'] }} <i class="bi bi-arrow-right"></i>
                        </a>
                        <span style="font-size: 11px; color: var(--fms-slate-400);">
                            {{ $n['time'] instanceof \Carbon\Carbon ? $n['time']->format('d M Y, H:i') : \Carbon\Carbon::parse($n['time'])->format('d M Y, H:i') }}
                        </span>
                    </div>
                </div>

            </div>
        @empty
            <div class="fms-empty-state">
                <i class="bi bi-bell-slash"></i>
                <h3 style="font-size: 16px; font-weight: 700; color: var(--fms-navy); margin: 0 0 6px 0;">No Notifications Yet</h3>
                <p style="font-size: 13px; margin: 0; color: var(--fms-slate-600);">
                    Events regarding shipment bookings, vessel milestones, invoices, and quotations will appear here automatically.
                </p>
            </div>
        @endforelse

        <div id="fms-no-results-notif" class="fms-empty-state" style="display: none;">
            <i class="bi bi-search"></i>
            <h3 style="font-size: 16px; font-weight: 700; color: var(--fms-navy); margin: 0 0 6px 0;">No matching notifications</h3>
            <p style="font-size: 13px; margin: 0; color: var(--fms-slate-600);">
                Try clearing your search terms or selecting another category tab.
            </p>
        </div>
    </div>

</div>

@push('scripts')
<script>
    let activeNotifTab = 'all';
    const READ_NOTIFS_STORAGE_KEY = 'fms_client_read_notifs_{{ auth()->id() }}';

    function getReadNotifIds() {
        try {
            return JSON.parse(localStorage.getItem(READ_NOTIFS_STORAGE_KEY) || '[]');
        } catch (e) {
            return [];
        }
    }

    function saveReadNotifIds(ids) {
        localStorage.setItem(READ_NOTIFS_STORAGE_KEY, JSON.stringify(ids));
    }

    function initReadStates() {
        const readIds = getReadNotifIds();
        readIds.forEach(id => {
            const card = document.getElementById('notif-card-' + id);
            const dot = document.getElementById('dot-' + id);
            if (card) card.classList.remove('unread');
            if (dot) dot.style.display = 'none';
        });
    }

    function toggleNotifRead(id) {
        let readIds = getReadNotifIds();
        const card = document.getElementById('notif-card-' + id);
        const dot = document.getElementById('dot-' + id);

        if (!readIds.includes(id)) {
            readIds.push(id);
            saveReadNotifIds(readIds);
            if (card) card.classList.remove('unread');
            if (dot) dot.style.display = 'none';
        }
    }

    function markAllNotificationsAsRead() {
        const cards = document.querySelectorAll('.fms-notif-item');
        let allIds = [];
        cards.forEach(card => {
            const id = card.getAttribute('data-id');
            if (id) allIds.push(id);
            card.classList.remove('unread');
            const dot = document.getElementById('dot-' + id);
            if (dot) dot.style.display = 'none';
        });
        saveReadNotifIds(allIds);
    }

    function switchNotifTab(category, btn) {
        activeNotifTab = category;
        document.querySelectorAll('.fms-tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        filterNotifications();
    }

    function filterNotifications() {
        const query = (document.getElementById('notif-search-input').value || '').trim().toLowerCase();
        const items = document.querySelectorAll('.fms-notif-item');
        let visibleCount = 0;

        items.forEach(item => {
            const cat = item.getAttribute('data-category');
            const searchTarget = item.getAttribute('data-search') || '';

            const matchesCategory = (activeNotifTab === 'all' || cat === activeNotifTab);
            const matchesQuery = (!query || searchTarget.includes(query));

            if (matchesCategory && matchesQuery) {
                item.style.display = 'flex';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        const noResults = document.getElementById('fms-no-results-notif');
        if (noResults) {
            noResults.style.display = (visibleCount === 0 && items.length > 0) ? 'block' : 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        initReadStates();
    });
</script>
@endpush

@endsection
