<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HBL Print - {{ $data['hbl_number'] ?: 'New' }}</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { background:#e5e7eb; font-family:Arial,sans-serif; font-size:11px; }

/* Top Navigation Toolbar */
.toolbar {
    position:fixed; top:0; left:0; right:0; z-index:1000;
    background:#1e293b; color:#fff; padding:8px 20px;
    display:flex; align-items:center; gap:16px;
    box-shadow:0 2px 8px rgba(0,0,0,.3);
}
.toolbar select, .toolbar button {
    font-size:12px; padding:4px 10px; border-radius:4px; border:1px solid #475569;
}
.toolbar select { background:#334155; color:#fff; min-width:220px; }
.toolbar button { background:#3b82f6; color:#fff; border:none; cursor:pointer; }
.toolbar button:hover { background:#2563eb; }
.toolbar .title { font-size:14px; font-weight:600; }

.page-wrapper { margin:60px auto 30px; }

/* Global Input / Textarea overlay style */
.hbl-field {
    position: absolute;
    border: none;
    background: transparent;
    font-family: Arial, sans-serif;
    font-size: 9px;
    color: #000;
    padding: 1px 2px;
    outline: none;
    overflow: hidden;
    resize: none;
    line-height: 1.25;
}
.hbl-field:empty, .hbl-field:placeholder-shown {
    background: transparent;
}
.hbl-field:focus, .hbl-field:hover {
    background: transparent;
    border: none;
    box-shadow: none;
}
textarea.hbl-field { overflow-y: auto; }

/* Hide non-selected templates */
.template-container { display: none; }
.template-container.active { display: block; }

@media print {
    body { background:#fff; }
    .toolbar { display:none !important; }
    .page-wrapper { margin:0; }
    .template-container { display: none !important; }
    .template-container.active { display: block !important; }
    .hbl-field { border:none !important; outline:none !important; background:transparent !important; box-shadow:none !important; }
}
@page { margin:0; }
</style>
</head>
<body>

@if(!request('pdf'))
<div class="toolbar" id="toolbar">
  <span class="title">🖨 HBL Print System</span>
  <label style="color:#94a3b8;font-size:11px;">Select Template:</label>
  <select id="templateSelect" onchange="switchTemplate(this.value)">
    @foreach($templates as $t)
    <option value="{{ $t['key'] }}" data-size="{{ $t['size'] }}">{{ $t['name'] }}</option>
    @endforeach
  </select>
  <button onclick="window.print()">🖨 Print</button>
  <button onclick="window.print()">💾 Save PDF</button>
  
  <x-email-document-modal 
        documentType="ocean-export-hbl" 
        documentId="{{ $shipment->id }}" 
        documentUrl="{{ url()->full() }}" 
        documentName="HBL_{{ $shipment->file_no ?? 'Document' }}" 
    />

  <span style="color:#64748b;font-size:11px;margin-left:auto;">HBL: {{ $data['hbl_number'] ?: 'N/A' }} | File: {{ $shipment->file_no ?? '' }}</span>
</div>
@endif

<div class="page-wrapper" id="pageWrapper">
    @php
        // When generating PDF via Dompdf (no JS), only render the selected template as active.
        $selectedTpl = request('template', $templates[0]['key'] ?? '');
    @endphp

    <!-- Template 1: Silk Container Lines -->
    <div id="tpl-silk_container" class="template-container {{ $selectedTpl === 'silk_container' || request('pdf') && $selectedTpl === '' ? 'active' : '' }}">
        @include('ocean-export.hbl-templates.silk-container')
    </div>

    <!-- Template 2: NTG AIR -->
    <div id="tpl-ntg_air" class="template-container {{ $selectedTpl === 'ntg_air' ? 'active' : '' }}">
        @include('ocean-export.hbl-templates.ntg-air')
    </div>

    <!-- Template 3: United American Line -->
    <div id="tpl-united_american" class="template-container {{ $selectedTpl === 'united_american' ? 'active' : '' }}">
        @include('ocean-export.hbl-templates.united-american')
    </div>

    <!-- Template 4: Ocean Blue Express -->
    <div id="tpl-ocean_blue" class="template-container {{ $selectedTpl === 'ocean_blue' ? 'active' : '' }}">
        @include('ocean-export.hbl-templates.ocean-blue')
    </div>

    <!-- Template 5: Transamerica Logistics -->
    <div id="tpl-transamerica" class="template-container {{ $selectedTpl === 'transamerica' ? 'active' : '' }}">
        @include('ocean-export.hbl-templates.transamerica')
    </div>
</div>

<script>
function switchTemplate(key) {
    // Hide all template containers
    document.querySelectorAll('.template-container').forEach(el => el.classList.remove('active'));
    
    // Show active container
    const activeEl = document.getElementById('tpl-' + key);
    if (activeEl) {
        activeEl.classList.add('active');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    switchTemplate(document.getElementById('templateSelect').value);
});
</script>
</body>
</html>
