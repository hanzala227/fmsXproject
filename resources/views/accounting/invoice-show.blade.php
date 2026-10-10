<x-layout>
    @php
        $statusClasses = [
            'DRAFT' => 'badge-draft', 'POSTED' => 'badge-posted',
            'PAID' => 'badge-paid', 'PARTIAL' => 'badge-partial', 'VOID' => 'badge-void'
        ];
        $statusClass = $statusClasses[$invoice->status] ?? 'badge-draft';
        $lineSubtotal = $invoice->lines->sum('amount');
        $discount = ($lineSubtotal * ($invoice->discount_pct ?? 0)) / 100;
        $afterDiscount = $lineSubtotal - $discount;
        $shipping = $invoice->shipping_amount ?? 0;
        $tax = (($afterDiscount + $shipping) * ($invoice->tax_pct ?? 0)) / 100;
    @endphp
    @push('styles')
    <style>
        .page-content { padding: 8px 12px; background: #eef1f5; min-height: calc(100vh - 50px); font-family: 'Inter', 'Open Sans', sans-serif !important; }
        .portlet.light { background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 4px; margin-bottom: 10px !important; box-shadow: 0 1px 2px rgba(0,0,0,0.02); overflow: hidden; }
        .portlet-title { padding: 4px 10px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; min-height: 26px; background-color: #f8fafc; }
        .portlet-body { padding: 8px 10px; }
        .caption-subject { color: #3b82f6; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }

        .btn-freightx { background: #3b82f6; color: #fff !important; border: none; padding: 4px 10px; border-radius: 3px; font-size: 10px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s ease; box-shadow: 0 1px 2px rgba(59, 130, 246, 0.2); }
        .btn-freightx:hover { background: #2563eb; transform: translateY(-1px); box-shadow: 0 4px 6px rgba(59, 130, 246, 0.25); }
        .btn-default-gf { background: #ffffff; border: 1px solid #cbd5e1; color: #334155; padding: 3px 8px; font-size: 10px; border-radius: 3px; cursor: pointer; font-weight: 600; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 4px; text-decoration: none; }
        .btn-default-gf:hover { background: #f8fafc; border-color: #94a3b8; color: #0f172a; }

        .btn-tool { background: #fff; border: 1px solid #cbd5e1; padding: 2px 8px; font-size: 10px; color: #334155; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; height: 22px; border-radius: 2px; transition: all 0.15s; text-decoration: none; }
        .btn-tool:hover:not(:disabled) { background: #f1f5f9; border-color: #94a3b8; }
        .btn-tool:disabled { opacity: 0.4; cursor: not-allowed; }
        .btn-tool.green { background: #3b82f6 !important; color: #fff !important; border-color: #2563eb !important; font-weight: 600; }
        .btn-tool.green:hover { background: #2563eb !important; }
        .btn-tool.danger { background: #ef4444 !important; color: #fff !important; border-color: #dc2626 !important; }
        .btn-tool.danger:hover { background: #dc2626 !important; }

        .table-custom { width: 100%; border-collapse: collapse; font-size: 10px; background: #ffffff; }
        .table-custom thead th { text-align: left; padding: 5px 6px; background: #f8fafc; color: #475569; font-weight: 700; border: 1px solid #e2e8f0; letter-spacing: 0.3px; font-size: 9px; white-space: nowrap; }
        .table-custom tbody td { padding: 4px 6px; border: 1px solid #e2e8f0; vertical-align: middle; color: #334155; font-size: 10px; }
        .table-custom tbody tr:hover { background-color: #f1f5f9; }
        .table-custom tbody td:last-child { text-align: right; }

        .page-bar { background-color: #fff; padding: 8px 20px; margin-bottom: 10px; border: 1px solid #e9ebec; border-radius: 4px; }
        .page-breadcrumb { list-style: none; padding: 0; margin: 0; display: flex; align-items: center; }
        .page-breadcrumb li { font-size: 12px; color: #888; display: flex; align-items: center; }
        .page-breadcrumb li a { color: #337ab7; text-decoration: none; }
        .page-breadcrumb li i { margin: 0 8px; font-size: 10px; opacity: 0.5; }

        .action-bar { text-align: center; padding: 15px 0; margin-top: 15px; display: flex; gap: 6px; justify-content: center; flex-wrap: wrap; }

        /* Detail grid — matching Ocean Import design */
        .detail-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px 12px; }
        @media (max-width: 1200px) { .detail-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 900px) { .detail-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 600px) { .detail-grid { grid-template-columns: 1fr; } }
        .detail-item { margin-bottom: 2px; }
        .detail-label { display: block; font-size: 8px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 1px; }
        .detail-value { font-size: 12px; color: #1e293b; font-weight: 600; }

        /* Totals grid — matching invoice-create */
        .totals-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px 12px; }
        .total-item { padding: 4px 6px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 3px; }
        .total-item .label { font-size: 8px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
        .total-item .value { font-size: 13px; font-weight: 700; color: #1e293b; text-align: right; }
        .total-item .value.primary { color: #3b82f6; }
        .total-item .value.danger { color: #ef4444; }

        /* Status badges — matching Ocean Import */
        .badge-status { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
        .badge-draft { background: #e2e8f0; color: #475569; }
        .badge-posted { background: #dbeafe; color: #1e40af; }
        .badge-paid { background: #dcfce7; color: #166534; }
        .badge-partial { background: #fef9c3; color: #854d0e; }
        .badge-void { background: #fee2e2; color: #991b1b; }

        .type-badge { font-size: 9px; font-weight: 700; padding: 1px 6px; border-radius: 2px; text-transform: uppercase; }
        .type-ap { background: #fee2e2; color: #991b1b; }
        .type-ar { background: #dbeafe; color: #1e40af; }

        .overdue-text { font-size: 12px; color: #ef4444; font-weight: 700; margin-top: 2px; }

        /* Scrollable table */
        .table-scroll { overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 3px; }
    </style>
    @endpush

    <div class="page-content">
        {{-- Breadcrumb --}}
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a><i class="fa fa-angle-right"></i></li>
                <li><a href="{{ route('accounting.invoices.index') }}">Accounting</a><i class="fa fa-angle-right"></i></li>
                <li><a href="{{ route('accounting.invoices.index') }}">Invoices</a><i class="fa fa-angle-right"></i></li>
                <li><span style="color:#333;font-weight:700;">{{ $invoice->invoice_no }}</span></li>
            </ul>
        </div>

        {{-- Header with actions --}}
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:8px;">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <h1 class="caption-subject" style="font-size:16px;color:#1e293b;margin:0;">
                    <i class="fa fa-file-text-o"></i> Invoice <span style="color:#3b82f6;">#{{ $invoice->invoice_no }}</span>
                </h1>
                <span class="badge-status {{ $statusClass }}">{{ $invoice->status }}</span>
                <span class="type-badge {{ $invoice->type === 'AP' ? 'type-ap' : 'type-ar' }}">
                    {{ $invoice->type === 'AP' ? 'AP — Payable' : 'AR — Receivable' }}
                </span>
            </div>
            <div style="display:flex;gap:4px;flex-wrap:wrap;">
                <a href="{{ route('accounting.invoices.edit', $invoice) }}" class="btn-freightx" target="_blank"><i class="fa fa-pencil"></i> EDIT</a>
                <a href="javascript:window.print()" class="btn-default-gf"><i class="fa fa-print"></i> PRINT</a>
                <form action="{{ route('accounting.invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirmDelete()" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-default-gf" style="color:#ef4444;border-color:#fecaca;"><i class="fa fa-trash"></i> DELETE</button>
                </form>
                <a href="{{ route('accounting.invoices.index') }}" class="btn-default-gf" target="_blank"><i class="fa fa-arrow-left"></i> BACK</a>
            </div>
        </div>

        {{-- ═══════════════════ INVOICE DETAILS ═══════════════════ --}}
        @php
            $shipment = $invoice->invoiceable;
            $mblNo = $invoice->mbl_no ?? $shipment?->mbl_no ?? $shipment?->mawb_no ?? $shipment?->file_no ?? '--';
            $fileNo = $shipment?->file_no ?? $shipment?->bkg_no ?? '--';
            $vessel = $shipment?->vessel?->name ?? $shipment?->vessel_name ?? '--';
            if ($shipment?->voyage && $vessel !== '--') {
                $vessel .= ' / ' . $shipment->voyage;
            }
            $pol = $shipment?->portOfLoading?->name ?? $shipment?->depPort?->name ?? '--';
            $pod = $shipment?->portOfDischarge?->name ?? $shipment?->dstPort?->name ?? '--';
            $etd = $shipment?->etd?->format('M d, Y') ?? '--';
            $eta = $shipment?->eta?->format('M d, Y') ?? '--';
        @endphp
        <div class="portlet light">
            <div class="portlet-title">
                <span class="caption-subject"><i class="fa fa-info-circle"></i> Invoice Details</span>
            </div>
            <div class="portlet-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Invoice No.</span>
                        <span class="detail-value">{{ $invoice->invoice_no }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">MBL / MAWB No.</span>
                        <span class="detail-value" style="color:#2563eb;">{{ $mblNo }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">File / Job No.</span>
                        <span class="detail-value">{{ $fileNo }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Invoice Date</span>
                        <span class="detail-value">{{ $invoice->invoice_date?->format('M d, Y') ?? '--' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Due Date</span>
                        <span class="detail-value">{{ $invoice->due_date?->format('M d, Y') ?? '--' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Type</span>
                        <span class="detail-value" style="color:{{ $invoice->type === 'AP' ? '#ef4444' : '#3b82f6' }};">{{ $invoice->type ?? 'AR' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Bill To</span>
                        <span class="detail-value">{{ $invoice->billTo->name ?? '--' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Currency</span>
                        <span class="detail-value">{{ $invoice->currency->code ?? 'USD' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Vessel / Voyage</span>
                        <span class="detail-value">{{ $vessel }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">POL / POD</span>
                        <span class="detail-value">{{ $pol }} &rarr; {{ $pod }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">ETD / ETA</span>
                        <span class="detail-value">{{ $etd }} &rarr; {{ $eta }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Office</span>
                        <span class="detail-value">{{ $invoice->office->code ?? $invoice->office->name ?? '--' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Issued By</span>
                        <span class="detail-value">{{ $invoice->issuer->name ?? auth()->user()->name ?? '--' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Billing Address</span>
                        <span class="detail-value">{{ $invoice->billing_address ?? $invoice->billTo->address ?? '--' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Created</span>
                        <span class="detail-value">{{ $invoice->created_at?->format('M d, Y h:i A') ?? '--' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Internal Remark</span>
                        <span class="detail-value" style="font-weight:400;font-size:11px;">{{ $invoice->internal_remark ?? '--' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════ LINE ITEMS ═══════════════════ --}}
        <div class="portlet light">
            <div class="portlet-title">
                <span class="caption-subject"><i class="fa fa-list"></i> Line Items</span>
                <span style="font-size:10px;color:#64748b;font-weight:600;">{{ $invoice->lines->count() }} line(s)</span>
            </div>
            <div class="portlet-body" style="padding:8px;">
                @if($invoice->lines->count() > 0)
                <div class="table-scroll">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th style="width:28px;text-align:center;">#</th>
                                <th>Charge Code</th>
                                <th>Description</th>
                                <th style="width:50px;text-align:center;">Type</th>
                                <th style="width:55px;text-align:right;">Qty</th>
                                <th style="width:65px;text-align:right;">Rate</th>
                                <th style="width:70px;text-align:right;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->lines as $idx => $line)
                            <tr>
                                <td style="text-align:center;font-weight:600;color:#94a3b8;">{{ $idx + 1 }}</td>
                                <td>{{ $line->charge_code ?? '--' }}</td>
                                <td>{{ $line->description ?? '--' }}</td>
                                <td style="text-align:center;">
                                    <span class="type-badge {{ $line->type === 'AP' ? 'type-ap' : 'type-ar' }}">{{ $line->type }}</span>
                                </td>
                                <td style="text-align:right;">{{ number_format($line->qty, 2) }}</td>
                                <td style="text-align:right;">{{ number_format($line->rate, 2) }}</td>
                                <td style="text-align:right;font-weight:700;color:#3b82f6;">{{ number_format($line->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:#f8fafc;font-weight:700;">
                                <td colspan="4" style="text-align:right;color:#475569;padding:5px 6px;">Totals</td>
                                <td style="text-align:right;padding:5px 6px;">{{ number_format($invoice->lines->sum('qty'), 2) }}</td>
                                <td></td>
                                <td style="text-align:right;color:#3b82f6;padding:5px 6px;">{{ number_format($invoice->lines->sum('amount'), 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div style="text-align:center;padding:25px;color:#94a3b8;font-size:10px;">
                    <i class="fa fa-inbox" style="font-size:22px;display:block;margin-bottom:4px;"></i>
                    No line items on this invoice.
                </div>
                @endif
            </div>
        </div>

        {{-- ═══════════════════ CHARGES SUMMARY ═══════════════════ --}}
        @if(($invoice->discount_pct ?? 0) > 0 || ($invoice->tax_pct ?? 0) > 0 || ($invoice->shipping_amount ?? 0) > 0)
        <div class="portlet light">
            <div class="portlet-title">
                <span class="caption-subject"><i class="fa fa-calculator"></i> Charges Summary</span>
            </div>
            <div class="portlet-body">
                <div class="detail-grid">
                    @if(($invoice->discount_pct ?? 0) > 0)
                    <div class="detail-item">
                        <span class="detail-label">Discount ({{ $invoice->discount_pct }}%)</span>
                        <span class="detail-value" style="color:#ef4444;">- {{ number_format($discount, 2) }}</span>
                    </div>
                    @endif
                    @if(($invoice->tax_pct ?? 0) > 0)
                    <div class="detail-item">
                        <span class="detail-label">Tax ({{ $invoice->tax_pct }}%)</span>
                        <span class="detail-value" style="color:#ef4444;">{{ number_format($tax, 2) }}</span>
                    </div>
                    @endif
                    @if(($invoice->shipping_amount ?? 0) > 0)
                    <div class="detail-item">
                        <span class="detail-label">Shipping</span>
                        <span class="detail-value">{{ number_format($shipping, 2) }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- ═══════════════════ TOTALS ═══════════════════ --}}
        <div class="portlet light">
            <div class="portlet-title">
                <span class="caption-subject"><i class="fa fa-calculator"></i> Totals &amp; Balance</span>
            </div>
            <div class="portlet-body">
                <div class="totals-grid">
                    <div class="total-item">
                        <div class="label">Line Subtotal</div>
                        <div class="value">{{ number_format($lineSubtotal, 2) }}</div>
                    </div>
                    @if(($invoice->discount_pct ?? 0) > 0)
                    <div class="total-item">
                        <div class="label">Discount ({{ $invoice->discount_pct }}%)</div>
                        <div class="value danger">- {{ number_format($discount, 2) }}</div>
                    </div>
                    @endif
                    @if(($invoice->tax_pct ?? 0) > 0)
                    <div class="total-item">
                        <div class="label">Tax ({{ $invoice->tax_pct }}%)</div>
                        <div class="value danger">{{ number_format($tax, 2) }}</div>
                    </div>
                    @endif
                    @if(($invoice->shipping_amount ?? 0) > 0)
                    <div class="total-item">
                        <div class="label">Shipping</div>
                        <div class="value">{{ number_format($shipping, 2) }}</div>
                    </div>
                    @endif
                    <div class="total-item" @if(($invoice->discount_pct ?? 0) > 0 && ($invoice->tax_pct ?? 0) > 0) style="grid-column:span 2;" @endif>
                        <div class="label">Total Amount</div>
                        <div class="value primary">{{ number_format($invoice->total_amount, 2) }}</div>
                    </div>
                    <div class="total-item">
                        <div class="label">Paid</div>
                        <div class="value" style="color:#16a34a;">{{ number_format($invoice->paid_amount, 2) }}</div>
                    </div>
                    <div class="total-item">
                        <div class="label">Balance Due</div>
                        <div class="value primary">{{ number_format($invoice->balance_amount, 2) }}</div>
                    </div>
                </div>

                {{-- Overdue notice --}}
                @if($invoice->due_date && $invoice->due_date->isPast() && $invoice->status !== 'PAID' && $invoice->status !== 'VOID')
                <div style="margin-top:8px;padding:6px 10px;background:#fef2f2;border:1px solid #fecaca;border-radius:3px;display:flex;align-items:center;gap:6px;">
                    <i class="fa fa-exclamation-triangle" style="color:#ef4444;font-size:11px;"></i>
                    <span style="font-size:10px;color:#991b1b;font-weight:600;">
                        OVERDUE — Due {{ $invoice->due_date->format('M d, Y') }} ({{ $invoice->due_date->diffInDays(now()) }} days past)
                    </span>
                </div>
                @elseif($invoice->status === 'PAID')
                <div style="margin-top:8px;padding:6px 10px;background:#dcfce7;border:1px solid #86efac;border-radius:3px;display:flex;align-items:center;gap:6px;">
                    <i class="fa fa-check-circle" style="color:#16a34a;font-size:11px;"></i>
                    <span style="font-size:10px;color:#166534;font-weight:600;">Paid in full</span>
                </div>
                @elseif($invoice->due_date)
                <div style="margin-top:8px;padding:6px 10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:3px;display:flex;align-items:center;gap:6px;">
                    <i class="fa fa-clock-o" style="color:#64748b;font-size:11px;"></i>
                    <span style="font-size:10px;color:#475569;font-weight:600;">Due {{ $invoice->due_date->format('M d, Y') }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- ═══════════════════ DOCUMENTS ═══════════════════ --}}
        @if($invoice->documents->count() > 0)
        <div class="portlet light">
            <div class="portlet-title">
                <span class="caption-subject"><i class="fa fa-folder-open"></i> Documents ({{ $invoice->documents->count() }})</span>
            </div>
            <div class="portlet-body" style="padding:8px;">
                <div class="table-scroll">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th style="width:28px;">#</th>
                                <th>File Name</th>
                                <th style="width:60px;">Size</th>
                                <th style="width:60px;">Type</th>
                                <th style="width:80px;">Uploaded</th>
                                <th style="width:80px;">Uploader</th>
                                <th style="width:60px;text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->documents as $idx => $doc)
                            <tr>
                                <td style="text-align:center;font-weight:600;color:#94a3b8;">{{ $idx + 1 }}</td>
                                <td>
                                    <i class="fa fa-file-text-o" style="color:#64748b;margin-right:4px;"></i>
                                    {{ $doc->file_name }}
                                </td>
                                <td>{{ $doc->file_size ? number_format($doc->file_size / 1024, 1) . ' KB' : '-' }}</td>
                                <td>{{ $doc->file_extension ?? '-' }}</td>
                                <td>{{ $doc->created_at?->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $doc->uploader->name ?? 'N/A' }}</td>
                                <td style="text-align:center;">
                                    <a href="{{ route('accounting.invoices.documents.download', $doc) }}" class="btn-tool" style="height:18px;padding:0 6px;font-size:9px;text-decoration:none;" target="_blank">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ═══════════════════ ACTION BAR ═══════════════════ --}}
        <div class="action-bar">
            <a href="{{ route('accounting.invoices.edit', $invoice) }}" class="btn-tool green" target="_blank"><i class="fa fa-pencil"></i> Edit Invoice</a>
            <form action="{{ route('accounting.invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirmDelete()" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-tool danger"><i class="fa fa-trash"></i> Delete</button>
            </form>
            <a href="{{ route('accounting.invoices.index') }}" class="btn-tool" target="_blank"><i class="fa fa-arrow-left"></i> Back to List</a>
        </div>
    </div>

    @push('scripts')
    <script>
        function confirmDelete() {
            return confirm('Delete invoice #{{ $invoice->invoice_no }}? This action cannot be undone.');
        }
    </script>
    @endpush
</x-layout>
