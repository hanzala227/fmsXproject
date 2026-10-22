<style>
    .page-content { padding: 4px; background: #eef1f5; min-height: calc(100vh - 50px); font-family: 'Open Sans', sans-serif !important; }
    .portlet.light { background-color: #fff; border: 1px solid #cbd5e1; border-radius: 2px; margin-bottom: 4px !important; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .portlet-title { padding: 4px 8px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; min-height: 28px; background: #fff; }
    .portlet-body { padding: 0; }
    .caption-subject { color: #1e293b; font-size: 12px; font-weight: 700; text-transform: uppercase; }

    /* Toolbar */
    .portlet-tool { background: #f8fafc; padding: 4px 8px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 4px; }
    .btn-group { display: flex; gap: 4px; }
    .btn-tool { background: #fff; border: 1px solid #cbd5e1; padding: 2px 8px; font-size: 10px; color: #334155; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 4px; height: 22px; border-radius: 2px; transition: all 0.15s; white-space: nowrap; box-sizing: border-box; }
    .btn-tool:hover:not(:disabled) { background: #f1f5f9; border-color: #94a3b8; }
    .btn-tool.active-filter { background: #3b82f6 !important; color: #fff !important; border-color: #2563eb !important; }
    .btn-tool.green { background: #3b82f6 !important; color: #fff !important; border-color: #2563eb !important; font-weight: 600; }
    .btn-tool.green:hover { background: #2563eb !important; }
    .btn-tool:disabled { opacity: 0.4; cursor: not-allowed; }
    .btn-tool.danger { background: #ef4444 !important; color: #fff !important; border-color: #dc2626 !important; }

    /* Table */
    .grid-container { width: 100%; overflow: hidden; background: #fff; border: 1px solid #e2e8f0; border-radius: 4px; }
    .grid-wrapper { width: 100%; overflow-x: auto; overflow-y: auto; height: calc(100vh - 225px); min-height: 300px; -webkit-overflow-scrolling: touch; }
    .grid-table { border-collapse: separate; border-spacing: 0; width: max-content; min-width: 100%; font-size: 11px; table-layout: auto; }
    .grid-table th { background: #f8fafc !important; color: #475569 !important; font-weight: 600; border-bottom: 2px solid #cbd5e1 !important; border-right: 1px solid #e2e8f0 !important; border-top: 1px solid #cbd5e1 !important; padding: 6px 10px; white-space: nowrap; height: 28px; position: static !important; top: auto !important; z-index: 1; text-align: left; user-select: none; }
    .grid-table td { padding: 4px 10px; border-bottom: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; white-space: nowrap; height: 28px; overflow: hidden; text-overflow: ellipsis; vertical-align: middle; color: #334155; }
    .sticky-col { position: static !important; left: auto !important; z-index: auto !important; background: #ffffff !important; border-right: 1px solid #e2e8f0 !important; }
    .sticky-col-header { position: static !important; top: auto !important; left: auto !important; z-index: auto !important; background: #f8fafc !important; color: #475569 !important; border-right: 1px solid #e2e8f0 !important; }
    .grid-table tr:hover td { background-color: #f1f5f9 !important; }
    .grid-table tr.row-selected td { background-color: #eff6ff !important; }
    .grid-table tr { cursor: pointer; }

    /* Filter Row */
    .filter-row td { background: #eff6ff !important; padding: 2px 3px; }
    .filter-row td.sticky-col { position: static !important; left: auto !important; background: #eff6ff !important; z-index: auto !important; }
    .filter-input { width: 100%; height: 18px; border: 1px solid #93c5fd; font-size: 9px; border-radius: 2px; padding: 0 3px; box-sizing: border-box; outline: none; background: #fff; }
    .filter-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 1px rgba(59,130,246,0.2); }

    /* Elements */
    .col-link { color: #3b82f6; font-weight: 600; text-decoration: none; }
    .col-link:hover { text-decoration: underline; }
    .badge-status { padding: 1px 4px; border-radius: 2px; font-size: 9px; font-weight: 600; }
    .bg-green { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
    .bg-blue  { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
    .bg-red   { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .bg-yellow { background: #fefce8; color: #ca8a04; border: 1px solid #fef08a; }
    .bg-gray  { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }
    .badge-overdue { background: #fef2f2; color: #dc2626; font-weight: 700; padding: 1px 4px; border-radius: 2px; font-size: 9px; }
    .summary-stats { display: inline-flex; gap: 12px; align-items: center; font-size: 10px; color: #64748b; margin-left: 8px; }
    .summary-stats .val { color: #1e293b; font-weight: 600; }
    .summary-stats .val.paid { color: #16a34a; }
    .val-pos  { color: #16a34a; font-weight: 600; }
    .val-neg  { color: #dc2626; font-weight: 600; }
    .color-mark { width: 10px; height: 10px; border-radius: 2px; display: inline-block; vertical-align: middle; border: 1px solid rgba(0,0,0,0.1); cursor: pointer; }
    
    /* Color Picker */
    .color-picker-overlay .modal-box { min-width: 220px; }
    .color-picker-grid { display: flex; flex-direction: column; gap: 4px; padding: 4px 0; }
    .color-picker-list { display: flex; flex-direction: column; gap: 4px; padding: 4px 0; }
    .color-picker-opt { display: flex; align-items: center; gap: 10px; padding: 6px 10px; border-radius: 4px; border: 2px solid transparent; cursor: pointer; transition: all 0.12s; font-size: 11px; color: #334155; }
    .color-picker-opt:hover { background: #f1f5f9; }
    .color-picker-opt.active { border-color: #1e293b; background: #f8fafc; }
    .color-picker-opt .swatch { width: 16px; height: 16px; border-radius: 3px; border: 1px solid rgba(0,0,0,0.1); flex-shrink: 0; }
    .color-picker-opt .fa-check { color: #22c55e; font-size: 11px; opacity: 0; margin-left: auto; }
    .color-picker-opt.active .fa-check { opacity: 1; }
    .color-clear-btn { font-size: 10px; color: #ef4444; cursor: pointer; text-align: center; padding: 6px 0 2px; border-top: 1px solid #f1f5f9; margin-top: 4px; }
    .color-clear-btn:hover { text-decoration: underline; }

    /* Pagination */
    .portlet-tool.bottom { background: #f8fafc !important; padding: 4px 8px !important; border-top: 1px solid #cbd5e1 !important; border-bottom: none !important; }
    .pagination { display: flex; list-style: none; padding: 0; margin: 0; gap: 2px; align-items: center; font-size: 10px; }
    .pagination li a, .pagination li span { padding: 2px 6px; border: 1px solid #cbd5e1; color: #334155; text-decoration: none; border-radius: 2px; background: #fff; }
    .pagination li.active span { background: #3b82f6; color: #fff; border-color: #2563eb; font-weight: 600; }
    .pagination li.disabled span { opacity: 0.5; cursor: not-allowed; }
    
    /* TP Pagination - Trade Partner Style */
    .tp-pagination { display: inline-flex; gap: 2px; align-items: center; }
    .tp-page-btn { display: inline-flex; align-items: center; justify-content: center; min-width: 22px; height: 22px; padding: 0 6px; border: 1px solid #cbd5e1; color: #334155; text-decoration: none; border-radius: 2px; background: #fff; font-size: 10px; font-weight: 500; transition: all 0.15s; cursor: pointer; }
    .tp-page-btn:hover:not(.disabled):not(.active) { background: #f1f5f9; border-color: #94a3b8; }
    .tp-page-btn.active { background: #3b82f6; color: #fff; border-color: #2563eb; font-weight: 600; }
    .tp-page-btn.disabled { opacity: 0.4; cursor: not-allowed; pointer-events: none; }
    
    /* Inputs */
    .input-inline { height: 20px; padding: 0 6px; border: 1px solid #cbd5e1; font-size: 10px; border-radius: 2px; outline: none; }
    .input-inline:focus { border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,0.1); }
    .select-tool { height: 22px; padding: 0 6px; border: 1px solid #cbd5e1; font-size: 10px; border-radius: 2px; color: #334155; background: #fff; cursor: pointer; }
    .select-tool:focus { outline: none; border-color: #3b82f6; }

    /* Action buttons in title */
    .btn-action-round { background: #64748b; color: #fff; border: 1px solid #475569; border-radius: 2px; padding: 0 8px; height: 20px; font-size: 10px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 4px; cursor: pointer; text-decoration: none; transition: all 0.15s; box-sizing: border-box; }
    .btn-action-round:hover { background: #475569; color: #fff; }
    .btn-action-round.white { background: #fff; color: #334155; border: 1px solid #cbd5e1; }
    .btn-action-round.white:hover { background: #f1f5f9; }
    .btn-action-round.active-filter { background: #3b82f6; border-color: #2563eb; }
    .btn-action-round.active { background: #3b82f6; border-color: #2563eb; }
    .btn-action-round.green-btn { background: #22c55e; border-color: #16a34a; color: #fff; }
    .btn-action-round.green-btn:hover { background: #16a34a; color: #fff; }

    /* Config Panel */
    .config-panel { position: absolute; right: 0; top: 24px; background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; box-shadow: 0 6px 20px rgba(0,0,0,0.12); z-index: 200; min-width: 200px; max-height: 320px; overflow-y: auto; padding: 8px; font-size: 10px; text-align: left; }
    .config-panel-title { font-weight: 700; color: #475569; margin-bottom: 6px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; padding-bottom: 4px; border-bottom: 1px solid #e2e8f0; }
    .config-panel label { display: flex; align-items: center; gap: 6px; padding: 3px 4px; cursor: pointer; border-radius: 2px; color: #334155; }
    .config-panel label:hover { background: #f1f5f9; }

    /* Toast */
    .toast-container { position: fixed; top: 56px; right: 16px; z-index: 9999; display: flex; flex-direction: column; gap: 6px; pointer-events: none; }
    .toast { background: #1e293b; color: #fff; padding: 8px 14px; border-radius: 4px; font-size: 11px; box-shadow: 0 4px 16px rgba(0,0,0,0.25); display: flex; align-items: center; gap: 8px; animation: toastIn 0.25s ease; pointer-events: all; }
    .toast.success { border-left: 3px solid #22c55e; }
    .toast.error   { border-left: 3px solid #ef4444; }
    .toast.info    { border-left: 3px solid #3b82f6; }
    @keyframes toastIn { from { transform: translateX(40px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

    /* Modal / Overlay */
    .overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.35); z-index: 9990; align-items: center; justify-content: center; animation: fadeIn 0.15s ease; }
    .overlay.open { display: flex; }
    @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
    .modal-box { background: #fff; border-radius: 6px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); overflow: hidden; }
    .modal-header { background: #f8fafc; padding: 8px 14px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; }
    .modal-header-title { font-size: 11px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 6px; }
    .modal-close { cursor: pointer; color: #94a3b8; font-size: 12px; background: none; border: none; padding: 0; }
    .modal-close:hover { color: #475569; }
    .modal-body { padding: 12px 14px; font-size: 10px; min-width: 340px; }
    .mbl-row { display: flex; padding: 4px 0; border-bottom: 1px solid #f1f5f9; }
    .mbl-row:last-child { border-bottom: none; }
    .mbl-row .lbl { color: #64748b; width: 130px; flex-shrink: 0; }
    .mbl-row .val { color: #0f172a; font-weight: 500; }

    /* Confirm Modal */
    .confirm-box { background: #fff; border-radius: 6px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); padding: 24px; text-align: center; min-width: 300px; max-width: 380px; }
    .confirm-icon { font-size: 28px; color: #ef4444; margin-bottom: 10px; }
    .confirm-box h4 { font-size: 13px; font-weight: 700; color: #1e293b; margin: 0 0 6px; }
    .confirm-box p  { font-size: 11px; color: #64748b; margin: 0 0 18px; }
    .confirm-actions { display: flex; gap: 8px; justify-content: center; }
    
    /* Breadcrumbs */
    .page-bar { background-color: #fff; padding: 8px 20px; margin-bottom: 15px; border: 1px solid #e9ebec; border-radius: 4px; }
    .page-breadcrumb { list-style: none; padding: 0; margin: 0; display: flex; align-items: center; }
    .page-breadcrumb li { font-size: 12px; color: #888; display: flex; align-items: center; }
    .page-breadcrumb li a { color: #337ab7; text-decoration: none; transition: color 0.15s; }
    .page-breadcrumb li a:hover { color: #1d4ed8; }
    .page-breadcrumb li i { margin: 0 8px; font-size: 10px; opacity: 0.5; }
    
    .page-content {
        padding: 8px 12px;
        background: #eef1f5;
        min-height: calc(100vh - 50px);
        font-family: 'Inter', 'Open Sans', sans-serif !important;
    }

    @media print {
        .page-sidebar-wrapper, .page-sidebar, .page-header, .header, .sidebar, 
        .page-bar, .nav-tabs-custom, .portlet-title .actions, .portlet-tool:not(.bottom), 
        .filter-row, #filter-row, .toast-container, .overlay, .modal-box, 
        .config-panel, #grid-loading, #pagination-container {
            display: none !important;
        }

        html, body {
            background: #ffffff !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }

        .page-content {
            padding: 0 !important;
            margin: 0 !important;
            background: #ffffff !important;
            min-height: auto !important;
        }

        .portlet.light {
            border: none !important;
            box-shadow: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .portlet-title {
            border-bottom: 2px solid #0f172a !important;
            padding: 4px 0 8px 0 !important;
            margin-bottom: 8px !important;
        }

        .caption-subject {
            font-size: 14pt !important;
            font-weight: 700 !important;
            color: #0f172a !important;
        }

        .grid-container {
            width: 100% !important;
            overflow: visible !important;
            background: #ffffff !important;
        }

        .grid-wrapper {
            width: 100% !important;
            height: auto !important;
            min-height: 0 !important;
            max-height: none !important;
            overflow: visible !important;
        }

        .grid-table {
            width: 100% !important;
            table-layout: auto !important;
            border-collapse: collapse !important;
            font-size: 9pt !important;
        }

        .grid-table th {
            background: #f1f5f9 !important;
            color: #0f172a !important;
            font-weight: 700 !important;
            border: 1px solid #94a3b8 !important;
            padding: 5px 6px !important;
            position: static !important;
            top: auto !important;
        }

        .grid-table td {
            border: 1px solid #cbd5e1 !important;
            padding: 4px 6px !important;
            font-size: 8.5pt !important;
            color: #1e293b !important;
            white-space: normal !important;
            word-break: break-word !important;
            height: auto !important;
        }

        .sticky-col, .sticky-col-header {
            position: static !important;
            left: auto !important;
            background: transparent !important;
            border-right: 1px solid #cbd5e1 !important;
        }

        .portlet-tool.bottom {
            display: flex !important;
            border-top: 1px solid #cbd5e1 !important;
            margin-top: 8px !important;
            padding: 6px 0 !important;
            background: transparent !important;
        }

        .col-link {
            color: #0f172a !important;
            text-decoration: none !important;
            font-weight: 600 !important;
        }
    }
</style>
