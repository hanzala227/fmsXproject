@extends('layouts.userapp')
@section('title', 'Shipments Directory')

@push('styles')
<style>
    /* ==========================================================================
       INTERNATIONAL FREIGHT STANDARD CLIENT PORTAL — SHIPMENTS DIRECTORY
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

    .fms-shipments-page {
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
    .fms-meta-tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #ffffff;
        border: 1px solid var(--fms-slate-200);
        border-radius: 6px;
        padding: 6px 12px;
        font-size: 12px;
        color: var(--fms-slate-700);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
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
    .fms-kpi-icon.air { background: #f0fdf4; color: #047857; }
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
    .fms-filter-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .fms-select-filter {
        height: 36px;
        padding: 4px 12px;
        font-size: 13px;
        border: 1px solid var(--fms-slate-200);
        border-radius: 6px;
        background: #ffffff;
        color: var(--fms-slate-700);
        cursor: pointer;
        outline: none;
    }

    /* Shipments Data Table */
    .fms-table-container {
        background: #ffffff;
        border: 1px solid var(--fms-slate-200);
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        overflow: hidden;
    }
    .fms-table-responsive {
        width: 100%;
        overflow-x: auto;
    }
    .fms-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 13px;
    }
    .fms-table thead th {
        background: #f8fafc;
        padding: 12px 14px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--fms-slate-600);
        border-bottom: 1px solid var(--fms-slate-200);
        white-space: nowrap;
    }
    .fms-table tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: var(--fms-slate-700);
    }
    .fms-table tbody tr.shipment-row {
        cursor: pointer;
        transition: background-color 0.12s ease;
    }
    .fms-table tbody tr.shipment-row:hover {
        background-color: #f8fafc;
    }
    .fms-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Cell Data Elements */
    .fms-ref-block {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .fms-booking-no {
        font-weight: 700;
        color: var(--fms-blue);
        text-decoration: none;
        cursor: pointer;
    }
    .fms-booking-no:hover {
        text-decoration: underline;
    }
    .fms-sub-ref {
        font-size: 11px;
        color: var(--fms-slate-400);
        font-family: ui-monospace, monospace;
    }

    .fms-badge-mode {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }
    .fms-badge-mode.ocean-import { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
    .fms-badge-mode.ocean-export { background: #f0fdfa; color: #0d9488; border: 1px solid #ccfbf1; }
    .fms-badge-mode.air-import   { background: #f0f9ff; color: #0284c7; border: 1px solid #e0f2fe; }
    .fms-badge-mode.air-export   { background: #eef2ff; color: #4f46e5; border: 1px solid #e0e7ff; }

    .fms-route-block {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .fms-route-ports {
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 700;
        color: var(--fms-slate-900);
    }
    .fms-route-ports i {
        color: var(--fms-slate-400);
        font-size: 11px;
    }
    .fms-route-locode {
        font-size: 11px;
        color: var(--fms-slate-400);
    }

    .fms-badge-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }
    .fms-badge-status.in-transit { background: var(--fms-blue-light); color: var(--fms-blue); border: 1px solid #bfdbfe; }
    .fms-badge-status.arrived    { background: var(--fms-emerald-bg); color: var(--fms-emerald); border: 1px solid #a7f3d0; }
    .fms-badge-status.delivered  { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
    .fms-badge-status.pending    { background: var(--fms-amber-bg); color: var(--fms-amber); border: 1px solid #fde68a; }

    .fms-action-btn {
        background: #ffffff;
        border: 1px solid var(--fms-slate-200);
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        color: var(--fms-slate-700);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.15s ease;
    }
    .fms-action-btn:hover {
        background: var(--fms-slate-50);
        border-color: var(--fms-blue);
        color: var(--fms-blue);
    }

    /* Empty state */
    .fms-empty-state {
        text-align: center;
        padding: 48px 16px;
        color: var(--fms-slate-600);
    }
    .fms-empty-state i {
        font-size: 36px;
        color: var(--fms-slate-400);
        margin-bottom: 8px;
    }

    /* ==========================================================================
       CENTER-ALIGNED POPUP MODAL (INTERNATIONAL FREIGHT STANDARD)
       ========================================================================== */
    .fms-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(4px);
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 16px;
        opacity: 0;
        transition: opacity 0.2s ease-in-out;
    }
    .fms-modal-backdrop.open {
        display: flex;
        opacity: 1;
    }
    .fms-modal-dialog {
        width: 100%;
        max-width: 860px;
        max-height: 90vh;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transform: scale(0.96) translateY(10px);
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .fms-modal-backdrop.open .fms-modal-dialog {
        transform: scale(1) translateY(0);
    }
    .fms-modal-header {
        padding: 18px 24px;
        background: #f8fafc;
        border-bottom: 1px solid var(--fms-slate-200);
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }
    .fms-modal-title {
        font-size: 19px;
        font-weight: 700;
        color: var(--fms-navy);
        margin: 0;
        letter-spacing: -0.01em;
    }
    .fms-modal-close {
        background: #ffffff;
        border: 1px solid var(--fms-slate-200);
        font-size: 15px;
        color: var(--fms-slate-400);
        cursor: pointer;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
    }
    .fms-modal-close:hover {
        color: var(--fms-slate-900);
        border-color: var(--fms-slate-400);
        background: var(--fms-slate-100);
    }
    .fms-modal-body {
        padding: 24px;
        overflow-y: auto;
        flex: 1;
    }
    .fms-modal-footer {
        padding: 14px 24px;
        background: #f8fafc;
        border-top: 1px solid var(--fms-slate-200);
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
    }

    /* Section Cards in Modal */
    .fms-detail-section {
        background: #ffffff;
        border: 1px solid var(--fms-slate-200);
        border-radius: 8px;
        padding: 16px 18px;
        margin-bottom: 16px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
    }
    .fms-detail-heading {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--fms-slate-600);
        letter-spacing: 0.05em;
        margin: 0 0 12px 0;
        display: flex;
        align-items: center;
        gap: 6px;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 8px;
    }
    .fms-info-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px 18px;
    }
    .fms-info-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px 18px;
    }
    @media (max-width: 768px) {
        .fms-info-grid-3, .fms-info-grid-2 { grid-template-columns: 1fr; }
    }
    .fms-info-item label {
        display: block;
        font-size: 11px;
        color: var(--fms-slate-400);
        margin-bottom: 2px;
        font-weight: 500;
    }
    .fms-info-item .val {
        font-size: 13px;
        font-weight: 600;
        color: var(--fms-slate-900);
        word-break: break-word;
    }
    .fms-info-item .val.highlight {
        color: var(--fms-blue);
        font-weight: 700;
    }
</style>
@endpush

@section('content')

@php
    // Merge all shipments into a unified, high-performing collection with rich non-confidential operational freight data
    $allMovements = collect();

    // 1. Ocean Import
    foreach($oceanImports as $s) {
        $pol = $s->portOfLoading->name ?? ($s->pol_id ? 'Port #'.$s->pol_id : 'TBD');
        $polCode = $s->portOfLoading->code ?? '';
        $pod = $s->portOfDischarge->name ?? ($s->pod_id ? 'Port #'.$s->pod_id : 'TBD');
        $podCode = $s->portOfDischarge->code ?? '';
        $del = $s->placeOfDelivery->name ?? ($s->placeOfDelivery->code ?? '-');
        $fdest = $s->finalDestination->name ?? ($s->finalDestination->code ?? '-');

        $containersList = $s->containers->map(function($c) {
            $cType = $c->containerType->name ?? ($c->container_size_type ?? '40HC');
            return [
                'no' => $c->container_no ?? 'TBA',
                'type' => $cType,
                'seal' => $c->seal_no ?? '-',
                'weight' => $c->weight_kg ? number_format($c->weight_kg, 0).' KG' : ($c->gross_weight_kg ? number_format($c->gross_weight_kg, 0).' KG' : '-'),
                'cbm' => $c->measure_cbm ? number_format($c->measure_cbm, 2).' CBM' : ($c->cbm ? number_format($c->cbm, 2).' CBM' : '-'),
                'pkgs' => $c->pkg_qty ? number_format($c->pkg_qty, 0).' Pkgs' : '-',
                'lfd' => $c->lfd ? \Carbon\Carbon::parse($c->lfd)->format('d M Y') : '-'
            ];
        })->toArray();

        $totalPkgs = $s->containers->sum('pkg_qty');
        $totalWeight = $s->containers->sum('weight_kg');
        $totalCbm = $s->containers->sum('measure_cbm');

        $firstHbl = $s->hbls->first();

        // Milestone step calculation
        $st = strtolower($s->status ?? 'in transit');
        $step = 3; // In Transit default
        if (str_contains($st, 'delivered') || str_contains($st, 'complete')) $step = 5;
        elseif (str_contains($st, 'arrived') || str_contains($st, 'customs') || str_contains($st, 'port')) $step = 4;
        elseif (str_contains($st, 'departed') || str_contains($st, 'transit')) $step = 3;
        elseif (str_contains($st, 'gate') || str_contains($st, 'loaded')) $step = 2;
        elseif (str_contains($st, 'booking') || str_contains($st, 'placed')) $step = 1;

        $allMovements->push([
            'id' => $s->id,
            'mode_key' => 'ocean-import',
            'mode_title' => 'Ocean Import',
            'mode_badge_class' => 'ocean-import',
            'mode_icon' => 'bi-water',
            'file_no' => $s->file_no ?? 'MOI-'.$s->id,
            'master_no' => $s->mbl_no ?? '-',
            'house_no' => $firstHbl->hbl_no ?? '-',
            'sub_bl_no' => $s->sub_bl_no ?? '-',
            'origin' => $pol,
            'origin_code' => $polCode,
            'destination' => $pod,
            'destination_code' => $podCode,
            'delivery_place' => $del,
            'final_destination' => $fdest,
            'carrier' => $s->carrier->name ?? ($s->vessel->name ?? 'Ocean Carrier'),
            'vessel_name' => $s->vessel->name ?? '-',
            'voyage' => $s->voyage ?? '-',
            'transport_id' => ($s->vessel->name ?? '-').($s->voyage ? ' / '.$s->voyage : ''),
            'etd' => $s->etd ? \Carbon\Carbon::parse($s->etd)->format('d M Y') : '-',
            'eta' => $s->eta ? \Carbon\Carbon::parse($s->eta)->format('d M Y') : '-',
            'atd' => $s->atd ? \Carbon\Carbon::parse($s->atd)->format('d M Y') : '-',
            'ata' => $s->ata ? \Carbon\Carbon::parse($s->ata)->format('d M Y') : '-',
            'status' => $s->status ?? 'In Transit',
            'milestone_step' => $step,
            'shipper' => $s->dmShipper->name ?? '-',
            'consignee' => $s->dmConsignee->name ?? (auth()->user()->company->name ?? '-'),
            'notify_party' => $s->dmNotify->name ?? 'Same as Consignee',
            'ship_mode' => $s->ship_mode ?: ($s->cargo_type ?: 'FCL'),
            'incoterm' => $s->incoterm->code ?? ($s->incoterm_id ?: 'FOB'),
            'freight_term' => $s->freight_term ?: 'Prepaid',
            'bl_type' => $s->bl_type ?: 'Original B/L',
            'commodity' => $s->description ?: 'General Merchandise Cargo',
            'total_packages' => $totalPkgs > 0 ? number_format($totalPkgs, 0).' Packages' : 'Standard Pack',
            'total_weight' => $totalWeight > 0 ? number_format($totalWeight, 0).' KG' : '-',
            'total_cbm' => $totalCbm > 0 ? number_format($totalCbm, 2).' CBM' : '-',
            'cargo_summary' => count($containersList) > 0 ? count($containersList).' Container(s) FCL' : 'Standard Cargo',
            'containers' => $containersList,
            'created_at' => $s->created_at ? $s->created_at->format('Y-m-d') : ''
        ]);
    }

    // 2. Ocean Export
    foreach($oceanExports as $s) {
        $pol = $s->portOfLoading->name ?? ($s->pol_id ? 'Port #'.$s->pol_id : 'TBD');
        $polCode = $s->portOfLoading->code ?? '';
        $pod = $s->portOfDischarge->name ?? ($s->pod_id ? 'Port #'.$s->pod_id : 'TBD');
        $podCode = $s->portOfDischarge->code ?? '';
        $del = $s->placeOfDelivery->name ?? ($s->placeOfDelivery->code ?? '-');
        $fdest = $s->finalDestination->name ?? ($s->finalDestination->code ?? '-');

        $containersList = $s->containers->map(function($c) {
            $cType = $c->containerType->name ?? ($c->container_size_type ?? '40HC');
            return [
                'no' => $c->container_no ?? 'TBA',
                'type' => $cType,
                'seal' => $c->seal_no ?? '-',
                'weight' => $c->weight_kg ? number_format($c->weight_kg, 0).' KG' : ($c->gross_weight_kg ? number_format($c->gross_weight_kg, 0).' KG' : '-'),
                'cbm' => $c->measure_cbm ? number_format($c->measure_cbm, 2).' CBM' : ($c->cbm ? number_format($c->cbm, 2).' CBM' : '-'),
                'pkgs' => $c->pkg_qty ? number_format($c->pkg_qty, 0).' Pkgs' : '-',
                'lfd' => $c->lfd ? \Carbon\Carbon::parse($c->lfd)->format('d M Y') : '-'
            ];
        })->toArray();

        $totalPkgs = $s->containers->sum('pkg_qty');
        $totalWeight = $s->containers->sum('weight_kg');
        $totalCbm = $s->containers->sum('measure_cbm');

        $firstHbl = $s->hbls->first();

        $st = strtolower($s->status ?? 'booking placed');
        $step = 1;
        if (str_contains($st, 'delivered') || str_contains($st, 'complete')) $step = 5;
        elseif (str_contains($st, 'arrived') || str_contains($st, 'customs') || str_contains($st, 'port')) $step = 4;
        elseif (str_contains($st, 'transit') || str_contains($st, 'departed')) $step = 3;
        elseif (str_contains($st, 'gate') || str_contains($st, 'loaded')) $step = 2;
        elseif (str_contains($st, 'booking') || str_contains($st, 'placed')) $step = 1;

        $allMovements->push([
            'id' => $s->id,
            'mode_key' => 'ocean-export',
            'mode_title' => 'Ocean Export',
            'mode_badge_class' => 'ocean-export',
            'mode_icon' => 'bi-ship',
            'file_no' => $s->file_no ?? 'MOE-'.$s->id,
            'master_no' => $s->mbl_no ?? '-',
            'house_no' => $firstHbl->hbl_no ?? '-',
            'sub_bl_no' => $s->sub_bl_no ?? '-',
            'origin' => $pol,
            'origin_code' => $polCode,
            'destination' => $pod,
            'destination_code' => $podCode,
            'delivery_place' => $del,
            'final_destination' => $fdest,
            'carrier' => $s->carrier->name ?? ($s->vessel->name ?? 'Ocean Carrier'),
            'vessel_name' => $s->vessel->name ?? '-',
            'voyage' => $s->voyage ?? '-',
            'transport_id' => ($s->vessel->name ?? '-').($s->voyage ? ' / '.$s->voyage : ''),
            'etd' => $s->etd ? \Carbon\Carbon::parse($s->etd)->format('d M Y') : '-',
            'eta' => $s->eta ? \Carbon\Carbon::parse($s->eta)->format('d M Y') : '-',
            'atd' => $s->atd ? \Carbon\Carbon::parse($s->atd)->format('d M Y') : '-',
            'ata' => $s->ata ? \Carbon\Carbon::parse($s->ata)->format('d M Y') : '-',
            'status' => $s->status ?? 'Booking Placed',
            'milestone_step' => $step,
            'shipper' => $s->dmShipper->name ?? (auth()->user()->company->name ?? '-'),
            'consignee' => $s->dmConsignee->name ?? '-',
            'notify_party' => $s->dmNotify->name ?? 'Same as Consignee',
            'ship_mode' => $s->ship_mode ?: ($s->cargo_type ?: 'FCL'),
            'incoterm' => $s->incoterm->code ?? ($s->incoterm_id ?: 'FOB'),
            'freight_term' => $s->freight_term ?: 'Prepaid',
            'bl_type' => $s->bl_type ?: 'Original B/L',
            'commodity' => $s->description ?: 'General Export Cargo',
            'total_packages' => $totalPkgs > 0 ? number_format($totalPkgs, 0).' Packages' : 'Standard Pack',
            'total_weight' => $totalWeight > 0 ? number_format($totalWeight, 0).' KG' : '-',
            'total_cbm' => $totalCbm > 0 ? number_format($totalCbm, 2).' CBM' : '-',
            'cargo_summary' => count($containersList) > 0 ? count($containersList).' Container(s) FCL' : 'Standard Cargo',
            'containers' => $containersList,
            'created_at' => $s->created_at ? $s->created_at->format('Y-m-d') : ''
        ]);
    }

    // 3. Air Import
    foreach($airImports as $s) {
        $dep = $s->depPort->name ?? ($s->dep_port_id ? 'Port #'.$s->dep_port_id : 'TBD');
        $depCode = $s->depPort->code ?? '';
        $dst = $s->dstPort->name ?? ($s->dst_port_id ? 'Port #'.$s->dst_port_id : 'TBD');
        $dstCode = $s->dstPort->code ?? '';
        $firstHawb = $s->hbls->first();

        $st = strtolower($s->status ?? 'in transit');
        $step = 3;
        if (str_contains($st, 'delivered') || str_contains($st, 'complete')) $step = 5;
        elseif (str_contains($st, 'arrived') || str_contains($st, 'customs') || str_contains($st, 'airport')) $step = 4;
        elseif (str_contains($st, 'transit') || str_contains($st, 'departed')) $step = 3;
        elseif (str_contains($st, 'flight') || str_contains($st, 'loaded')) $step = 2;
        elseif (str_contains($st, 'booking') || str_contains($st, 'placed')) $step = 1;

        $allMovements->push([
            'id' => $s->id,
            'mode_key' => 'air-import',
            'mode_title' => 'Air Import',
            'mode_badge_class' => 'air-import',
            'mode_icon' => 'bi-airplane',
            'file_no' => $s->file_no ?? 'MAI-'.$s->id,
            'master_no' => $s->mawb_no ?? '-',
            'house_no' => $firstHawb->hawb_no ?? '-',
            'sub_bl_no' => '-',
            'origin' => $dep,
            'origin_code' => $depCode,
            'destination' => $dst,
            'destination_code' => $dstCode,
            'delivery_place' => $dst,
            'final_destination' => $dst,
            'carrier' => $s->carrier->name ?? 'Air Cargo Carrier',
            'vessel_name' => 'Flight',
            'voyage' => $s->flight_no ?? '-',
            'transport_id' => $s->flight_no ? 'Flight '.$s->flight_no : 'Air Freight',
            'etd' => $s->etd ? \Carbon\Carbon::parse($s->etd)->format('d M Y') : '-',
            'eta' => $s->eta ? \Carbon\Carbon::parse($s->eta)->format('d M Y') : '-',
            'atd' => $s->atd ? \Carbon\Carbon::parse($s->atd)->format('d M Y') : '-',
            'ata' => $s->ata ? \Carbon\Carbon::parse($s->ata)->format('d M Y') : '-',
            'status' => $s->status ?? 'In Transit',
            'milestone_step' => $step,
            'shipper' => $s->dmShipper->name ?? '-',
            'consignee' => $s->dmConsignee->name ?? (auth()->user()->company->name ?? '-'),
            'notify_party' => $s->dmNotify->name ?? 'Same as Consignee',
            'ship_mode' => 'Air Freight Consolidation',
            'incoterm' => $s->incoterm_id ?: 'CPT / FCA',
            'freight_term' => $s->freight_term ?: 'Prepaid',
            'bl_type' => 'Master Air Waybill (MAWB)',
            'commodity' => $s->description ?? 'Air Freight Cargo',
            'total_packages' => $s->pkg_qty ? number_format($s->pkg_qty, 0).' Pkgs' : 'Air Pallet / Loose',
            'total_weight' => $s->gross_weight_kg ? number_format($s->gross_weight_kg, 0).' KG' : ($s->gross_weight ? number_format($s->gross_weight, 0).' KG' : '-'),
            'total_cbm' => $s->volume_cbm ? number_format($s->volume_cbm, 2).' CBM' : ($s->volume ? number_format($s->volume, 2).' CBM' : '-'),
            'cargo_summary' => 'Air Cargo Unit',
            'containers' => [],
            'created_at' => $s->created_at ? $s->created_at->format('Y-m-d') : ''
        ]);
    }

    // 4. Air Export
    foreach($airExports as $s) {
        $dep = $s->depPort->name ?? ($s->dep_port_id ? 'Port #'.$s->dep_port_id : 'TBD');
        $depCode = $s->depPort->code ?? '';
        $dst = $s->dstPort->name ?? ($s->dst_port_id ? 'Port #'.$s->dst_port_id : 'TBD');
        $dstCode = $s->dstPort->code ?? '';
        $firstHawb = $s->hbls->first();

        $st = strtolower($s->status ?? 'booking placed');
        $step = 1;
        if (str_contains($st, 'delivered') || str_contains($st, 'complete')) $step = 5;
        elseif (str_contains($st, 'arrived') || str_contains($st, 'customs') || str_contains($st, 'airport')) $step = 4;
        elseif (str_contains($st, 'transit') || str_contains($st, 'departed')) $step = 3;
        elseif (str_contains($st, 'flight') || str_contains($st, 'loaded')) $step = 2;
        elseif (str_contains($st, 'booking') || str_contains($st, 'placed')) $step = 1;

        $allMovements->push([
            'id' => $s->id,
            'mode_key' => 'air-export',
            'mode_title' => 'Air Export',
            'mode_badge_class' => 'air-export',
            'mode_icon' => 'bi-airplane',
            'file_no' => $s->file_no ?? 'MAE-'.$s->id,
            'master_no' => $s->mawb_no ?? '-',
            'house_no' => $firstHawb->hawb_no ?? '-',
            'sub_bl_no' => '-',
            'origin' => $dep,
            'origin_code' => $depCode,
            'destination' => $dst,
            'destination_code' => $dstCode,
            'delivery_place' => $dst,
            'final_destination' => $dst,
            'carrier' => $s->carrier->name ?? 'Air Cargo Carrier',
            'vessel_name' => 'Flight',
            'voyage' => $s->flight_no ?? '-',
            'transport_id' => $s->flight_no ? 'Flight '.$s->flight_no : 'Air Freight',
            'etd' => $s->etd ? \Carbon\Carbon::parse($s->etd)->format('d M Y') : '-',
            'eta' => $s->eta ? \Carbon\Carbon::parse($s->eta)->format('d M Y') : '-',
            'atd' => $s->atd ? \Carbon\Carbon::parse($s->atd)->format('d M Y') : '-',
            'ata' => $s->ata ? \Carbon\Carbon::parse($s->ata)->format('d M Y') : '-',
            'status' => $s->status ?? 'Booking Placed',
            'milestone_step' => $step,
            'shipper' => $s->dmShipper->name ?? (auth()->user()->company->name ?? '-'),
            'consignee' => $s->dmConsignee->name ?? '-',
            'notify_party' => $s->dmNotify->name ?? 'Same as Consignee',
            'ship_mode' => 'Air Freight Consolidation',
            'incoterm' => $s->incoterm_id ?: 'CPT / FCA',
            'freight_term' => $s->freight_term ?: 'Prepaid',
            'bl_type' => 'Master Air Waybill (MAWB)',
            'commodity' => $s->description ?? 'Air Freight Cargo',
            'total_packages' => $s->pkg_qty ? number_format($s->pkg_qty, 0).' Pkgs' : 'Air Pallet / Loose',
            'total_weight' => $s->gross_weight_kg ? number_format($s->gross_weight_kg, 0).' KG' : ($s->gross_weight ? number_format($s->gross_weight, 0).' KG' : '-'),
            'total_cbm' => $s->volume_cbm ? number_format($s->volume_cbm, 2).' CBM' : ($s->volume ? number_format($s->volume, 2).' CBM' : '-'),
            'cargo_summary' => 'Air Cargo Unit',
            'containers' => [],
            'created_at' => $s->created_at ? $s->created_at->format('Y-m-d') : ''
        ]);
    }

    // KPI Counts
    $totalCount = $allMovements->count();
    $inTransitCount = $allMovements->filter(fn($m) => stripos($m['status'], 'transit') !== false || stripos($m['status'], 'departed') !== false)->count();
    $arrivedCount = $allMovements->filter(fn($m) => stripos($m['status'], 'arrived') !== false || stripos($m['status'], 'customs') !== false)->count();
    $deliveredCount = $allMovements->filter(fn($m) => stripos($m['status'], 'delivered') !== false || stripos($m['status'], 'complete') !== false)->count();
    if($inTransitCount === 0 && $totalCount > 0) $inTransitCount = $totalCount;
@endphp

<div class="fms-shipments-page">

    <!-- Header Bar -->
    <div class="fms-header-bar">
        <div class="fms-title-group">
            <h1>Shipments Directory</h1>
            <p>Active and historical freight movements across ocean and air corridors.</p>
        </div>
        <div class="fms-meta-tag">
            <i class="bi bi-building"></i>
            <span>Account: <strong>{{ auth()->user()->company->name ?? 'Customer Account' }}</strong></span>
        </div>
    </div>

    <!-- Top KPI Metrics -->
    <div class="fms-kpi-grid">
        <div class="fms-kpi-card">
            <div class="fms-kpi-info">
                <h3>Total Tracked</h3>
                <div class="fms-kpi-val">{{ $totalCount }}</div>
            </div>
            <div class="fms-kpi-icon">
                <i class="bi bi-box-seam"></i>
            </div>
        </div>
        <div class="fms-kpi-card">
            <div class="fms-kpi-info">
                <h3>In Transit</h3>
                <div class="fms-kpi-val">{{ $inTransitCount }}</div>
            </div>
            <div class="fms-kpi-icon ocean">
                <i class="bi bi-arrow-repeat"></i>
            </div>
        </div>
        <div class="fms-kpi-card">
            <div class="fms-kpi-info">
                <h3>At Destination / Port</h3>
                <div class="fms-kpi-val">{{ $arrivedCount }}</div>
            </div>
            <div class="fms-kpi-icon air">
                <i class="bi bi-geo-alt"></i>
            </div>
        </div>
        <div class="fms-kpi-card">
            <div class="fms-kpi-info">
                <h3>Delivered / Complete</h3>
                <div class="fms-kpi-val">{{ $deliveredCount }}</div>
            </div>
            <div class="fms-kpi-icon amber">
                <i class="bi bi-check2-circle"></i>
            </div>
        </div>
    </div>

    <!-- Controls: Tabs, Filters, and Realtime Search -->
    <div class="fms-controls-card">
        <div class="fms-tabs-container">
            <button type="button" class="fms-tab-btn active" onclick="switchModeTab('all', this)">
                <i class="bi bi-grid"></i> All Movements <span class="fms-tab-count">{{ $totalCount }}</span>
            </button>
            <button type="button" class="fms-tab-btn" onclick="switchModeTab('ocean-import', this)">
                <i class="bi bi-water"></i> Ocean Import <span class="fms-tab-count">{{ count($oceanImports) }}</span>
            </button>
            <button type="button" class="fms-tab-btn" onclick="switchModeTab('ocean-export', this)">
                <i class="bi bi-ship"></i> Ocean Export <span class="fms-tab-count">{{ count($oceanExports) }}</span>
            </button>
            <button type="button" class="fms-tab-btn" onclick="switchModeTab('air-import', this)">
                <i class="bi bi-airplane"></i> Air Import <span class="fms-tab-count">{{ count($airImports) }}</span>
            </button>
            <button type="button" class="fms-tab-btn" onclick="switchModeTab('air-export', this)">
                <i class="bi bi-airplane-engines"></i> Air Export <span class="fms-tab-count">{{ count($airExports) }}</span>
            </button>
        </div>

        <div class="fms-search-bar">
            <div class="fms-search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="shipment-search-input" class="fms-search-input" placeholder="Search by Booking No, MBL/MAWB, Container, Port..." oninput="filterShipmentRows()">
            </div>
            <div class="fms-filter-group">
                <select id="status-filter-select" class="fms-select-filter" onchange="filterShipmentRows()">
                    <option value="">All Statuses</option>
                    <option value="In Transit">In Transit</option>
                    <option value="Booking">Booking Placed</option>
                    <option value="Arrived">Arrived at Port</option>
                    <option value="Delivered">Delivered</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Standard Freight Shipments Table -->
    <div class="fms-table-container">
        <div class="fms-table-responsive">
            <table class="fms-table" id="shipments-data-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">#</th>
                        <th style="width: 170px;">Shipment References</th>
                        <th style="width: 130px;">Mode</th>
                        <th style="min-width: 200px;">Route (POL &rarr; POD)</th>
                        <th style="min-width: 150px;">Carrier / Transport</th>
                        <th style="min-width: 140px;">Cargo Details</th>
                        <th style="width: 110px;">ETD</th>
                        <th style="width: 110px;">ETA</th>
                        <th style="width: 120px;">Status</th>
                        <th style="width: 90px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allMovements as $m)
                        <tr class="shipment-row"
                            data-mode="{{ $m['mode_key'] }}"
                            data-status="{{ strtolower($m['status']) }}"
                            data-search="{{ strtolower($m['file_no'].' '.$m['master_no'].' '.$m['house_no'].' '.$m['origin'].' '.$m['destination'].' '.$m['origin_code'].' '.$m['destination_code'].' '.$m['transport_id'].' '.$m['cargo_summary'].' '.$m['commodity']) }}"
                            onclick="openShipmentModal({{ json_encode($m) }})">
                            
                            <td style="text-align: center; color: var(--fms-slate-400);">{{ $loop->iteration }}</td>
                            
                            <td>
                                <div class="fms-ref-block">
                                    <span class="fms-booking-no">
                                        {{ $m['file_no'] }}
                                    </span>
                                    <span class="fms-sub-ref">
                                        MBL: {{ $m['master_no'] }}
                                    </span>
                                    @if($m['house_no'] && $m['house_no'] !== '-')
                                        <span class="fms-sub-ref">
                                            HBL: {{ $m['house_no'] }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td>
                                <span class="fms-badge-mode {{ $m['mode_badge_class'] }}">
                                    <i class="bi {{ $m['mode_icon'] }}"></i> {{ $m['mode_title'] }}
                                </span>
                            </td>

                            <td>
                                <div class="fms-route-block">
                                    <div class="fms-route-ports">
                                        <span>{{ $m['origin_code'] ?: Str::limit($m['origin'], 14) }}</span>
                                        <i class="bi bi-arrow-right"></i>
                                        <span>{{ $m['destination_code'] ?: Str::limit($m['destination'], 14) }}</span>
                                    </div>
                                    <div class="fms-route-locode">
                                        {{ Str::limit($m['origin'], 18) }} &rarr; {{ Str::limit($m['destination'], 18) }}
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div style="font-weight: 600; color: var(--fms-slate-900);">{{ $m['carrier'] }}</div>
                                <div style="font-size: 11px; color: var(--fms-slate-400);">{{ $m['transport_id'] }}</div>
                            </td>

                            <td>
                                <div style="font-weight: 500;">{{ $m['cargo_summary'] }}</div>
                                @if(!empty($m['containers']))
                                    <div style="font-size: 10px; color: var(--fms-slate-400); font-family: ui-monospace, monospace;">
                                        {{ $m['containers'][0]['no'] }} ({{ $m['containers'][0]['type'] }})
                                    </div>
                                @elseif($m['commodity'])
                                    <div style="font-size: 10px; color: var(--fms-slate-400);">
                                        {{ Str::limit($m['commodity'], 20) }}
                                    </div>
                                @endif
                            </td>

                            <td>{{ $m['etd'] }}</td>
                            <td><strong style="color: var(--fms-navy);">{{ $m['eta'] }}</strong></td>

                            <td>
                                @php
                                    $st = strtolower($m['status']);
                                    $badgeClass = 'in-transit';
                                    if(str_contains($st, 'arrived')) $badgeClass = 'arrived';
                                    elseif(str_contains($st, 'delivered')) $badgeClass = 'delivered';
                                    elseif(str_contains($st, 'pending') || str_contains($st, 'booking')) $badgeClass = 'pending';
                                @endphp
                                <span class="fms-badge-status {{ $badgeClass }}">
                                    <i class="bi bi-dot" style="font-size: 16px; margin: -4px;"></i> {{ $m['status'] }}
                                </span>
                            </td>

                            <td style="text-align: right;" onclick="event.stopPropagation()">
                                <button type="button" class="fms-action-btn" onclick="openShipmentModal({{ json_encode($m) }})">
                                    Details <i class="bi bi-chevron-right"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr class="fms-empty-placeholder">
                            <td colspan="10">
                                <div class="fms-empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p style="font-weight: 600; margin: 0 0 4px 0;">No shipments found</p>
                                    <p style="font-size: 12px; margin: 0;">There are currently no freight files linked to your account.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    
                    <tr id="fms-no-results-row" style="display: none;">
                        <td colspan="10">
                            <div class="fms-empty-state">
                                <i class="bi bi-search"></i>
                                <p style="font-weight: 600; margin: 0 0 4px 0;">No matching records</p>
                                <p style="font-size: 12px; margin: 0;">Try adjusting your search criteria or mode filter.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ==========================================================================
     CENTER-ALIGNED RICH SHIPMENT DETAILS MODAL POPUP (ZERO CONFIDENTIAL DATA)
     ========================================================================== -->
<div class="fms-modal-backdrop" id="fms-modal-backdrop" onclick="handleBackdropClick(event)">
    <div class="fms-modal-dialog" id="fms-modal-dialog">
        
        <!-- Modal Header -->
        <div class="fms-modal-header">
            <div>
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px; flex-wrap:wrap;">
                    <span id="m-mode-badge" class="fms-badge-mode ocean-import">Ocean Import</span>
                    <span id="m-status-badge" class="fms-badge-status in-transit">In Transit</span>
                    <span id="m-incoterm-badge" class="fms-meta-tag" style="padding:2px 8px; font-size:11px; font-weight:700;">FOB</span>
                </div>
                <h2 id="m-file-no" class="fms-modal-title"></h2>
                <div style="display:flex; align-items:center; gap:14px; font-size:12px; color:var(--fms-slate-600); margin-top:4px; flex-wrap:wrap;">
                    <span>Master Ref: <strong id="m-master-no" style="color: var(--fms-slate-900); font-family: ui-monospace, monospace;"></strong></span>
                    <span id="m-house-wrap">House Ref: <strong id="m-house-no" style="color: var(--fms-slate-900); font-family: ui-monospace, monospace;"></strong></span>
                </div>
            </div>
            <button type="button" class="fms-modal-close" onclick="closeShipmentModal()" title="Close dialog">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="fms-modal-body">

            <!-- Section 1: Route & Schedule Matrix -->
            <div class="fms-detail-section">
                <div class="fms-detail-heading">
                    <i class="bi bi-geo-alt"></i> Route & Transport Schedule
                </div>
                <div class="fms-info-grid-3">
                    <div class="fms-info-item">
                        <label>Port of Loading (POL)</label>
                        <div class="val highlight" id="m-pol">-</div>
                    </div>
                    <div class="fms-info-item">
                        <label>Port of Discharge (POD)</label>
                        <div class="val highlight" id="m-pod">-</div>
                    </div>
                    <div class="fms-info-item">
                        <label>Final Place of Delivery</label>
                        <div class="val" id="m-delivery">-</div>
                    </div>
                    <div class="fms-info-item">
                        <label>Carrier Line</label>
                        <div class="val" id="m-carrier">-</div>
                    </div>
                    <div class="fms-info-item">
                        <label>Vessel / Voyage (or Flight No)</label>
                        <div class="val" id="m-transport">-</div>
                    </div>
                    <div class="fms-info-item">
                        <label>Service Mode</label>
                        <div class="val" id="m-ship-mode">-</div>
                    </div>
                    <div class="fms-info-item">
                        <label>Estimated Departure (ETD)</label>
                        <div class="val" id="m-etd">-</div>
                    </div>
                    <div class="fms-info-item">
                        <label>Estimated Arrival (ETA)</label>
                        <div class="val highlight" id="m-eta">-</div>
                    </div>
                    <div class="fms-info-item">
                        <label>Actual Departure / Arrival (ATD / ATA)</label>
                        <div class="val" id="m-actual-dates">-</div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Consignment Stakeholders -->
            <div class="fms-detail-section">
                <div class="fms-detail-heading">
                    <i class="bi bi-people"></i> Associated Consignment Parties
                </div>
                <div class="fms-info-grid-3">
                    <div class="fms-info-item">
                        <label>Shipper (Origin Party)</label>
                        <div class="val" id="m-shipper">-</div>
                    </div>
                    <div class="fms-info-item">
                        <label>Consignee (Destination Party)</label>
                        <div class="val" id="m-consignee">-</div>
                    </div>
                    <div class="fms-info-item">
                        <label>Notify Party</label>
                        <div class="val" id="m-notify">-</div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Commercial & Billing Specifications -->
            <div class="fms-detail-section">
                <div class="fms-detail-heading">
                    <i class="bi bi-file-earmark-check"></i> Commercial & Freight Terms
                </div>
                <div class="fms-info-grid-3">
                    <div class="fms-info-item">
                        <label>Incoterm</label>
                        <div class="val" id="m-incoterm">-</div>
                    </div>
                    <div class="fms-info-item">
                        <label>Freight Payment Term</label>
                        <div class="val" id="m-freight-term">-</div>
                    </div>
                    <div class="fms-info-item">
                        <label>Bill of Lading Type</label>
                        <div class="val" id="m-bl-type">-</div>
                    </div>
                    <div class="fms-info-item">
                        <label>Nature of Goods / Description</label>
                        <div class="val" id="m-commodity">-</div>
                    </div>
                    <div class="fms-info-item">
                        <label>Total Packages</label>
                        <div class="val" id="m-total-pkgs">-</div>
                    </div>
                    <div class="fms-info-item">
                        <label>Total Weight & Volume</label>
                        <div class="val" id="m-weight-vol">-</div>
                    </div>
                </div>
            </div>

            <!-- Section 4: Equipment & Container Breakdown -->
            <div class="fms-detail-section" id="m-containers-card">
                <div class="fms-detail-heading">
                    <i class="bi bi-box-seam"></i> Equipment & Cargo Specifications
                </div>
                <div id="m-containers-content"></div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="fms-modal-footer">
            <button type="button" class="fms-action-btn" onclick="closeShipmentModal()" style="padding: 8px 16px;">
                Close
            </button>
            <a href="#" id="m-live-tracking-link" class="fms-action-btn" style="background: var(--fms-blue); color:#ffffff; border-color: var(--fms-blue); padding: 8px 18px; font-size: 13px;">
                <i class="bi bi-radar"></i> Open Live GPS Tracking
            </a>
        </div>

    </div>
</div>

@push('scripts')
<script>
    let activeModeTab = 'all';

    function switchModeTab(mode, btn) {
        activeModeTab = mode;
        document.querySelectorAll('.fms-tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        filterShipmentRows();
    }

    function filterShipmentRows() {
        const query = (document.getElementById('shipment-search-input').value || '').trim().toLowerCase();
        const statusFilter = (document.getElementById('status-filter-select').value || '').toLowerCase();
        const rows = document.querySelectorAll('.fms-table tbody tr.shipment-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowMode = row.getAttribute('data-mode');
            const rowStatus = row.getAttribute('data-status');
            const rowSearch = row.getAttribute('data-search');

            const matchesMode = (activeModeTab === 'all' || rowMode === activeModeTab);
            const matchesStatus = (!statusFilter || rowStatus.includes(statusFilter));
            const matchesQuery = (!query || rowSearch.includes(query));

            if (matchesMode && matchesStatus && matchesQuery) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const noResults = document.getElementById('fms-no-results-row');
        if (noResults) {
            noResults.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }
    }

    function openShipmentModal(m) {
        document.getElementById('m-file-no').textContent = m.file_no;
        document.getElementById('m-master-no').textContent = m.master_no;

        const houseWrap = document.getElementById('m-house-wrap');
        if (m.house_no && m.house_no !== '-') {
            houseWrap.style.display = 'inline';
            document.getElementById('m-house-no').textContent = m.house_no;
        } else {
            houseWrap.style.display = 'none';
        }
        
        const modeBadge = document.getElementById('m-mode-badge');
        modeBadge.className = 'fms-badge-mode ' + m.mode_badge_class;
        modeBadge.innerHTML = `<i class="bi ${m.mode_icon}"></i> ${m.mode_title}`;

        const statusBadge = document.getElementById('m-status-badge');
        let stClass = 'in-transit';
        const stLower = (m.status || '').toLowerCase();
        if (stLower.includes('arrived')) stClass = 'arrived';
        else if (stLower.includes('delivered')) stClass = 'delivered';
        else if (stLower.includes('booking') || stLower.includes('pending')) stClass = 'pending';
        statusBadge.className = 'fms-badge-status ' + stClass;
        statusBadge.textContent = m.status;

        document.getElementById('m-incoterm-badge').textContent = m.incoterm || 'FOB';

        // Route & Schedule
        document.getElementById('m-pol').textContent = (m.origin_code ? m.origin_code + ' - ' : '') + m.origin;
        document.getElementById('m-pod').textContent = (m.destination_code ? m.destination_code + ' - ' : '') + m.destination;
        document.getElementById('m-delivery').textContent = m.delivery_place || m.destination;
        document.getElementById('m-carrier').textContent = m.carrier || '-';
        document.getElementById('m-transport').textContent = m.transport_id || '-';
        document.getElementById('m-ship-mode').textContent = m.ship_mode || '-';
        document.getElementById('m-etd').textContent = m.etd || '-';
        document.getElementById('m-eta').textContent = m.eta || '-';
        
        let actualDates = '-';
        if (m.atd !== '-' && m.ata !== '-') actualDates = m.atd + ' (ATD) / ' + m.ata + ' (ATA)';
        else if (m.atd !== '-') actualDates = m.atd + ' (ATD)';
        else if (m.ata !== '-') actualDates = m.ata + ' (ATA)';
        document.getElementById('m-actual-dates').textContent = actualDates;

        // Parties
        document.getElementById('m-shipper').textContent = m.shipper || '-';
        document.getElementById('m-consignee').textContent = m.consignee || '-';
        document.getElementById('m-notify').textContent = m.notify_party || 'Same as Consignee';

        // Terms
        document.getElementById('m-incoterm').textContent = m.incoterm || '-';
        document.getElementById('m-freight-term').textContent = m.freight_term || '-';
        document.getElementById('m-bl-type').textContent = m.bl_type || '-';
        document.getElementById('m-commodity').textContent = m.commodity || '-';
        document.getElementById('m-total-pkgs').textContent = m.total_packages || '-';
        document.getElementById('m-weight-vol').textContent = (m.total_weight || '-') + ' / ' + (m.total_cbm || '-');

        // Equipment / Containers Specs Table
        const contContent = document.getElementById('m-containers-content');
        if (m.containers && m.containers.length > 0) {
            let html = `
                <div style="overflow-x: auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:12px; margin-top:4px;">
                        <thead>
                            <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0; color:#475569; text-transform:uppercase; font-size:11px;">
                                <th style="padding:8px 10px; text-align:left;">Container No</th>
                                <th style="padding:8px 10px; text-align:left;">Type / Size</th>
                                <th style="padding:8px 10px; text-align:left;">Seal No</th>
                                <th style="padding:8px 10px; text-align:right;">Packages</th>
                                <th style="padding:8px 10px; text-align:right;">Gross Weight</th>
                                <th style="padding:8px 10px; text-align:right;">Volume</th>
                                <th style="padding:8px 10px; text-align:center;">Last Free Day (LFD)</th>
                            </tr>
                        </thead>
                        <tbody>`;
            m.containers.forEach(c => {
                html += `
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:9px 10px; font-weight:700; color:#0f172a; font-family: ui-monospace, monospace;">${c.no}</td>
                        <td style="padding:9px 10px;"><span style="background:#f1f5f9; padding:2px 6px; border-radius:4px; font-size:11px; font-weight:600;">${c.type}</span></td>
                        <td style="padding:9px 10px; color:#64748b; font-family: ui-monospace, monospace;">${c.seal}</td>
                        <td style="padding:9px 10px; text-align:right;">${c.pkgs}</td>
                        <td style="padding:9px 10px; text-align:right; font-weight:600; color:#0f172a;">${c.weight}</td>
                        <td style="padding:9px 10px; text-align:right; font-weight:600; color:#0f172a;">${c.cbm}</td>
                        <td style="padding:9px 10px; text-align:center; color:#d97706; font-weight:600;">${c.lfd || '-'}</td>
                    </tr>`;
            });
            html += `</tbody></table></div>`;
            contContent.innerHTML = html;
        } else {
            contContent.innerHTML = `
                <div style="font-size:12px; color:#64748b; padding:8px 0; display:flex; align-items:center; gap:8px;">
                    <i class="bi bi-info-circle" style="color:var(--fms-blue);"></i>
                    <span>General Cargo Consignment (${m.cargo_summary}). No individual ocean container equipment assigned.</span>
                </div>`;
        }

        // Live tracking link
        const trackingNum = (m.containers && m.containers.length > 0 && m.containers[0].no !== 'TBA') 
            ? m.containers[0].no 
            : (m.master_no !== '-' ? m.master_no : m.file_no);
        document.getElementById('m-live-tracking-link').href = "{{ route('client.tracking') }}?number=" + encodeURIComponent(trackingNum);

        // Open Modal Dialog
        const backdrop = document.getElementById('fms-modal-backdrop');
        backdrop.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeShipmentModal() {
        const backdrop = document.getElementById('fms-modal-backdrop');
        backdrop.classList.remove('open');
        document.body.style.overflow = '';
    }

    function handleBackdropClick(e) {
        if (e.target.id === 'fms-modal-backdrop') {
            closeShipmentModal();
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeShipmentModal();
    });
</script>
@endpush

@endsection
