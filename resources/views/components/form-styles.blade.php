<style>
        /* ========== ULTRA-COMPACT PROFESSIONAL UI/UX STYLES FOR OCEAN IMPORT FORM ========== */
        .page-content {
            padding: 8px 12px;
            background: #eef1f5;
            min-height: calc(100vh - 50px);
            font-family: 'Inter', 'Open Sans', sans-serif !important;
        }

        /* Breadcrumbs */
        .page-bar { background-color: #fff; padding: 8px 20px; margin-bottom: 15px; border: 1px solid #e9ebec; border-radius: 4px; }
        .page-breadcrumb { list-style: none; padding: 0; margin: 0; display: flex; align-items: center; }
        .page-breadcrumb li { font-size: 12px; color: #888; display: flex; align-items: center; }
        .page-breadcrumb li a { color: #337ab7; text-decoration: none; transition: color 0.15s; }
        .page-breadcrumb li a:hover { color: #1d4ed8; }
        .page-breadcrumb li i { margin: 0 8px; font-size: 10px; opacity: 0.5; }

        .portlet.light {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            margin-bottom: 10px !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }

        .portlet-title {
            padding: 4px 10px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 26px;
            background-color: #f8fafc;
        }

        .portlet-body {
            padding: 8px 10px;
        }

        .caption-subject {
            color: #3b82f6;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Buttons */
        .btn-freightx, .btn-freightx {
            background: #3b82f6;
            color: #fff !important;
            border: none;
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(59, 130, 246, 0.2);
        }
        .btn-freightx:hover, .btn-freightx:hover {
            background: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(59, 130, 246, 0.25);
        }
        .btn-default-gf {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #334155;
            padding: 3px 8px;
            font-size: 10px;
            border-radius: 3px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .btn-default-gf:hover {
            background: #f8fafc;
            border-color: #94a3b8;
            color: #0f172a;
        }
        .btn-default-gf.dark {
            background: #1e293b;
            border-color: #334155;
            color: #f8fafc;
        }
        .btn-default-gf.dark:hover {
            background: #334155;
            border-color: #475569;
        }

        /* Form Inputs */
        .form-control-gf {
            width: 100%;
            height: 26px;
            min-height: 26px;
            border: 1px solid #cbd5e1;
            padding: 2px 6px;
            font-size: 11px;
            line-height: 20px;
            border-radius: 3px;
            background: #ffffff;
            color: #1e293b;
            font-family: inherit;
            box-sizing: border-box;
            transition: all 0.2s ease;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.01);
        }
        select.form-control-gf {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 6px center;
            background-size: 9px;
            padding: 2px 20px 2px 6px;
            line-height: 20px;
        }
        textarea.form-control-gf {
            height: auto;
            min-height: 50px;
            line-height: 1.4;
            padding: 6px 8px;
        }
        .form-control-gf:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }
        .form-control-gf:disabled, .form-control-gf[readonly] {
            background-color: #f1f5f9 !important;
            color: #64748b;
            cursor: not-allowed;
        }

        .form-label-gf {
            font-size: 11px;
            font-weight: 600;
            color: #475569;
            display: inline-block;
            width: 105px;
            max-width: 105px;
            text-align: right;
            margin-right: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex-shrink: 0;
            line-height: 26px;
            height: 26px;
        }

        .form-group-gf {
            display: flex;
            align-items: center;
            margin-bottom: 4px;
            width: 100%;
            min-height: 26px;
            min-width: 0;
        }
        .form-input-container {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 3px;
            position: relative;
            min-width: 0;
            width: 100%;
        }

        /* Checkboxes & Radios */
        input[type="checkbox"], input[type="radio"] {
            width: 12px !important;
            height: 12px !important;
            margin: 0;
            cursor: pointer;
            accent-color: #3b82f6;
        }

        /* Tabs */
        ul.gf-tabs {
            display: flex !important;
            list-style: none !important;
            padding: 0 !important;
            margin: 0 0 10px 0 !important;
            background: #ffffff !important;
            border-bottom: 1px solid #e2e8f0 !important;
            border-radius: 4px 4px 0 0 !important;
            overflow-x: auto !important;
            white-space: nowrap !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02) !important;
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
        ul.gf-tabs::-webkit-scrollbar {
            display: none !important;
        }
        ul.gf-tabs li {
            margin-bottom: -1px !important;
        }
        ul.gf-tabs li a {
            padding: 8px 16px !important;
            display: block !important;
            color: #64748b !important;
            text-decoration: none !important;
            border: 1px solid transparent !important;
            cursor: pointer !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            transition: all 0.2s ease !important;
            line-height: 1.2 !important;
        }
        ul.gf-tabs li a:hover {
            color: #3b82f6 !important;
            background: #f8fafc !important;
        }
        ul.gf-tabs li.disabled-tab a {
            cursor: not-allowed !important;
            opacity: 0.45 !important;
            pointer-events: none !important;
            color: #94a3b8 !important;
            background: #f8fafc !important;
            border-color: transparent !important;
        }
        ul.gf-tabs li.active a {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-bottom-color: #ffffff !important;
            border-top: 3px solid #3b82f6 !important;
            color: #0f172a !important;
            border-radius: 4px 4px 0 0 !important;
        }

        /* Tables */
        .table-custom {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 10px;
            background: #ffffff;
        }
        .table-custom thead th {
            text-align: left;
            padding: 6px 8px;
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
            border-top: 1px solid #e2e8f0;
            letter-spacing: 0.3px;
        }
        .table-custom tbody td {
            padding: 4px 8px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
            color: #334155;
        }
        .table-custom tbody tr:hover {
            background-color: #f1f5f9;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }

        .table-gf { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 10px; }
        .table-gf th { background: #f8f9fa; border: 1px solid #ddd; padding: 6px; font-weight: 600; color: #555; text-align: left; }
        .table-gf td { border: 1px solid #ddd; padding: 4px; vertical-align: middle; }

        /* Grids */
        .main-grid { display: flex; flex-direction: column; gap: 4px; width: 100%; }
        .form-grid-4 { 
            display: grid; 
            grid-template-columns: repeat(4, minmax(0, 1fr)); 
            gap: 4px 10px; 
            width: 100%;
            box-sizing: border-box;
        }
        .form-grid-4 > div {
            min-width: 0;
            width: 100%;
            box-sizing: border-box;
        }

        @media (max-width: 1366px) {
            .form-grid-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 768px) {
            .form-grid-4 { grid-template-columns: 1fr; }
            .form-row { display: flex !important; flex-direction: column !important; gap: 6px !important; }
            .form-row > .form-group { grid-column: span 12 !important; width: 100% !important; }
        }
        @media (max-width: 576px) {
            .form-group-gf { flex-direction: column; align-items: flex-start; height: auto; min-height: unset; margin-bottom: 8px; }
            .form-label-gf { width: 100% !important; text-align: left !important; margin-bottom: 2px; }
            .form-input-container { width: 100%; }
            .container-toolbar { flex-direction: column; align-items: flex-start; gap: 8px; }
            .container-toolbar > div { width: 100%; display: flex; flex-wrap: wrap; gap: 4px; margin-left: 0 !important; }
            .modal-container { width: 95% !important; margin: 10px; }
            .radio-group { flex-wrap: wrap; }
            .gallery-toolbar { flex-direction: column; align-items: stretch; }
            .page-bar { padding: 8px 10px; }
            .portlet-title { flex-direction: column; align-items: flex-start; gap: 4px; padding: 6px 10px; }
            .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .expanded-container { flex-direction: column; }
            .expanded-col { width: 100% !important; border-right: none; border-bottom: 1px dashed #cbd5e1; }
            .expanded-col:last-child { border-bottom: none; }
        }

        /* Memos */
        .memo-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: 10px;
            margin-bottom: 8px;
            border-radius: 3px;
        }
        .memo-header {
            padding: 4px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            color: #3b82f6;
            font-weight: 700;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
        }
        .memo-body {
            padding: 6px;
            background: #f8fafc;
        }
        .btn-memo-doc {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 2px 6px;
            font-size: 9px;
            border-radius: 2px;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 4px;
            font-weight: 600;
        }
        .memo-table { width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0; background: #fff; }
        .memo-table th { background: #f1f5f9; color: #475569; padding: 4px 6px; font-weight: 600; border: 1px solid #e2e8f0; }
        .memo-table td { padding: 4px 6px; border: 1px solid #e2e8f0; color: #334155; }
        .memo-content-area {
            border: 1px solid #cbd5e1;
            width: 100%;
            height: 60px;
            resize: vertical;
            font-size: 10px;
            padding: 4px;
            background: #ffffff;
            border-radius: 3px;
        }

        /* Container Toolbar & Tables */
        .container-toolbar {
            display: flex;
            gap: 4px;
            margin-bottom: 6px;
            align-items: center;
            flex-wrap: wrap;
            background: #f8fafc;
            padding: 4px 8px;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
        }
        .btn-tool {
            background: #0ea5e9;
            color: #fff;
            border: none;
            padding: 3px 8px;
            font-size: 10px;
            border-radius: 2px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-tool:hover { background: #0284c7; box-shadow: 0 1px 2px rgba(14, 165, 233, 0.2); }
        .btn-tool-outline {
            background: #fff;
            color: #0ea5e9;
            border: 1px solid #0ea5e9;
            padding: 2px 8px;
            font-size: 10px;
            border-radius: 2px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-tool-outline:hover { background: #f0f9ff; }
        .btn-tool-secondary { background: #64748b; color: #fff; border: none; padding: 3px 8px; font-size: 10px; border-radius: 2px; cursor: pointer; font-weight: 600;}
        .btn-tool-secondary:hover { background: #475569; }

        .container-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            border: 1px solid #e2e8f0;
            background: #fff;
        }
        .container-table th {
            background: #f8fafc;
            color: #475569;
            padding: 0;
            border: 1px solid #e2e8f0;
            text-align: center;
            font-weight: 700;
            height: 24px;
            vertical-align: top;
        }
        .container-table td {
            padding: 2px 4px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            height: 22px;
        }
        .container-table tr.row-main { background: #ffffff; }
        .container-table tr:hover { background-color: #f1f5f9; }

        .container-table .header-split { display: flex; flex-direction: column; height: 100%; }
        .container-table .header-top {
            padding: 2px;
            border-bottom: 1px solid #e2e8f0;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
        }
        .container-table .header-bottom {
            padding: 1px;
            background: #f8fafc;
            height: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            font-weight: 600;
            color: #64748b;
        }

        .expanded-row { display: none !important; }
        .expanded-row td { padding: 0 !important; background: #fafafa !important; border-top: none !important; border-bottom: 2px solid #e2e8f0 !important;}
        .expanded-container { display: flex; width: 100%; border-top: 1px dashed #cbd5e1; }
        .expanded-col { border-right: 1px dashed #cbd5e1; padding: 6px 10px; }
        .expanded-col:last-child { border-right: none; }

        textarea.form-control-gf { min-height: 50px; resize: vertical; padding: 6px; }
        .hbl-header { font-weight: 700; color: #3b82f6; font-size: 11px; margin-bottom: 4px; border-bottom: 2px solid #e2e8f0; padding-bottom: 2px; }

        .total-row { background: #f8fafc; font-weight: 700; font-size: 11px; color: #0f172a; }
        .total-label-cell { text-align: right; padding-right: 10px !important; color: #3b82f6; border: 1px solid #e2e8f0 !important; }
        .total-val-cell { background: #fff; text-align: right; padding-right: 4px !important; border: 1px solid #e2e8f0 !important; font-size: 11px; color: #1e293b; }

        .btn-tool-icon {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            cursor: pointer;
            color: #475569;
            border-radius: 2px;
            transition: all 0.2s;
        }
        .btn-tool-icon:hover { background: #f1f5f9; border-color: #94a3b8; color: #0f172a; }
        .btn-tool-icon-blue { background: #3b82f6; color: #fff; border-color: #3b82f6; }
        .btn-tool-icon-blue:hover { background: #2563eb; color: #fff;}

        /* Modals */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); display: flex; align-items: center; justify-content: center; z-index: 10000; backdrop-filter: blur(2px); }
        .modal-container { background: #ffffff; width: 90%; max-width: 1000px; border-radius: 6px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); overflow: hidden; border-top: 3px solid #3b82f6; }
        .modal-header { padding: 10px 15px; background: #ffffff; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: 700; color: #0f172a; }
        .modal-body { padding: 15px; max-height: 75vh; overflow-y: auto; }
        .modal-footer { padding: 10px 15px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: right; }
        
        /* HBL Container & Items styles */
        .tab-btn {
            background: none;
            border: none;
            padding: 6px 15px;
            font-size: 11px;
            font-weight: bold;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
            border-bottom: 2px solid transparent;
        }
        .tab-btn:hover {
            color: #0f172a;
            background: #e2e8f0;
        }
        .tab-btn.active-tab {
            color: #f2bc00;
            border-bottom: 2px solid #f2bc00;
            background: #ffffff;
        }
        
        /* Ultra-compact styles for Charges Table */
        .charges-table-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 15px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
        }
        .charges-table-container table {
            width: 100%;
            border-collapse: collapse;
        }
        .charges-table-container th {
            padding: 4px 6px !important;
            font-size: 10px !important;
            font-weight: 600;
            background: #f1f3f6;
        }
        .charges-table-container td {
            padding: 3px 5px !important;
            font-size: 10px !important;
            vertical-align: middle;
        }
        .charges-table-container .form-control-gf {
            height: 18px !important;
            font-size: 9px !important;
            padding: 0 3px !important;
            border-radius: 2px;
        }
        .charges-table-container select.form-control-gf {
            padding-right: 10px !important;
            background-position: right 2px center !important;
            background-size: 6px !important;
        }

        /* Section Card */
        .section-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .text-danger { color: #ef4444; }

        /* Radio Group */
        .radio-group { display: flex; align-items: center; gap: 10px; }
        .radio-label { display: flex; align-items: center; gap: 4px; font-size: 10px; color: #334155; cursor: pointer; }

        /* Gallery */
        .gallery-header { background: #475569; color: #fff; padding: 8px 12px; font-size: 12px; font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
        .gallery-toolbar { padding: 8px; border-bottom: 1px solid #e2e8f0; display: flex; gap: 8px; align-items: center; }
        .gallery-row { padding: 15px; border-bottom: 1px solid #e2e8f0; font-size: 12px; font-weight: 600; color: #0f172a; }
        .gallery-filters { display: flex; margin-left: auto; }
        .gallery-filter-btn { background: #fff; border: 1px solid #cbd5e1; padding: 4px 12px; font-size: 11px; color: #475569; cursor: pointer; border-right: none; }
        .gallery-filter-btn:last-child { border-right: 1px solid #cbd5e1; }
        .gallery-filter-btn:hover { background: #f1f5f9; }

        /* Toast */
        .toast-container { position: fixed; top: 56px; right: 16px; z-index: 99999; display: flex; flex-direction: column; gap: 6px; pointer-events: none; }
        .toast { background: #1e293b; color: #fff; padding: 8px 14px; border-radius: 4px; font-size: 11px; box-shadow: 0 4px 16px rgba(0,0,0,0.25); display: flex; align-items: center; gap: 8px; animation: toastIn 0.25s ease; pointer-events: all; }
        .toast.success { border-left: 3px solid #22c55e; }
        .toast.error   { border-left: 3px solid #ef4444; }
        .toast.warning { border-left: 3px solid #f59e0b; }
        .toast.info    { border-left: 3px solid #3b82f6; }
        @keyframes toastIn { from { transform: translateX(40px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        /* AI Extraction Global UI Styles (Advanced Split Pane) */
        .ai-dropzone { border: 2px dashed #cbd5e1; padding: 40px 20px; text-align: center; border-radius: 8px; background: #f8fafc; cursor: pointer; display: block; transition: all 0.3s ease; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; }
        .ai-dropzone:hover { border-color: #337ab7; background: #f0f7ff; }
        .ai-dropzone-icon { font-size: 48px; color: #337ab7; margin-bottom: 15px; }
        .ai-dropzone-title { font-size: 16px; color: #334155; font-weight: 600; margin-bottom: 8px; }
        .ai-dropzone-desc { font-size: 13px; color: #64748b; }
        
        .ai-split-layout { display: flex; width: 100%; height: 500px; gap: 20px; }
        .ai-pane-left { flex: 1; background: #f1f5f9; border-radius: 8px; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; }
        .ai-pane-right { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        
        /* Advanced Scanner Animation */
        @keyframes scanVertical {
            0% { top: 0%; }
            50% { top: 100%; }
            100% { top: 0%; }
        }
        @keyframes pulseGlow {
            0% { box-shadow: 0 0 10px rgba(16, 185, 129, 0.4); }
            50% { box-shadow: 0 0 20px rgba(16, 185, 129, 0.8); }
            100% { box-shadow: 0 0 10px rgba(16, 185, 129, 0.4); }
        }
        .ai-doc-preview-wrapper { width: 90%; height: 90%; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); position: relative; display: flex; align-items: center; justify-content: center; color: #cbd5e1; font-size: 64px; }
        .ai-scanner-line-large { position: absolute; left: 0; width: 100%; height: 3px; background: #10b981; box-shadow: 0 0 15px #10b981, 0 0 30px #10b981; animation: scanVertical 3s linear infinite; z-index: 10; }
        .ai-scanner-badge { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border: 2px solid #10b981; padding: 12px 24px; border-radius: 8px; font-weight: bold; color: #10b981; font-size: 18px; z-index: 20; animation: pulseGlow 2s infinite; display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .ai-scanner-badge i { color: #10b981; }

        /* Simple Loading State Animation */
        @keyframes scanLaser {
            0% { top: 0%; opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { top: 100%; opacity: 0; }
        }
        .ai-scanner-wrapper { display: flex; flex-direction: column; align-items: center; margin-top: 15px; }
        .ai-scanner-doc { position: relative; width: 48px; height: 64px; border: 2px solid #cbd5e1; border-radius: 4px; background: #fff; overflow: hidden; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; }
        .ai-scanner-doc i { color: #94a3b8; font-size: 24px; opacity: 0.5; }
        .ai-scanner-line { position: absolute; left: 0; width: 100%; height: 2px; background: #337ab7; box-shadow: 0 0 10px rgba(51, 122, 183, 0.8); animation: scanLaser 1.5s ease-in-out infinite alternate; }
        .ai-loading-text { color: #337ab7; font-weight: 600; font-size: 13px; }

        /* Extracted Data Tables */
        .ai-table-header { font-size: 15px; font-weight: 600; color: #334155; margin: 0 0 10px 0; display: flex; align-items: center; justify-content: space-between; }
        .ai-data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; background: white; border-radius: 6px; overflow: hidden; border: 1px solid #e2e8f0; }
        .ai-data-table th { background: #64748b; color: white; text-align: left; padding: 10px 12px; font-size: 12px; font-weight: 600; }
        .ai-data-table td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #334155; }
        .ai-data-table tr:last-child td { border-bottom: none; }
        .ai-extracted-value { color: #059669; font-weight: 600; background: #f0fdf4; padding: 2px 6px; border-radius: 4px; border: 1px solid #bbf7d0; display: inline-block; }
        .ai-checkbox { width: 16px; height: 16px; accent-color: #337ab7; cursor: pointer; }
        .ai-table-container { flex: 1; overflow-y: auto; padding-right: 5px; }
        .ai-table-container::-webkit-scrollbar { width: 6px; }
        .ai-table-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        
        /* Hide global browser-top loading lines (Turbo/NProgress/Pace) that might conflict with AI UI */
        .turbo-progress-bar, #nprogress, .pace { display: none !important; opacity: 0 !important; visibility: hidden !important; }
    </style>