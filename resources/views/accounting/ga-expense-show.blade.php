<x-layout>
    @push('styles')
    <style>
        .page-content { padding: 15px; background: #eef1f5; min-height: calc(100vh - 50px); font-family: 'Open Sans', sans-serif !important; }
        .page-bar { background-color: #fff; padding: 8px 20px; margin-bottom: 15px; border: 1px solid #e9ebec; border-radius: 4px; }
        .page-breadcrumb { list-style: none; padding: 0; margin: 0; display: flex; align-items: center; }
        .page-breadcrumb li { font-size: 12px; color: #888; display: flex; align-items: center; }
        .page-breadcrumb li a { color: #337ab7; text-decoration: none; }
        .page-breadcrumb li i { margin: 0 8px; font-size: 10px; opacity: 0.5; }

        .detail-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 4px; margin-bottom: 15px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .detail-card-header { padding: 10px 15px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border-radius: 4px 4px 0 0; }
        .detail-card-header h4 { margin: 0; font-size: 12px; font-weight: 700; color: #1e293b; text-transform: uppercase; }
        .detail-card-body { padding: 15px; }
        .detail-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px 20px; }
        .detail-field { margin-bottom: 4px; }
        .detail-label { font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 600; }
        .detail-value { font-size: 12px; color: #1e293b; font-weight: 500; margin-top: 2px; }

        .grid-table { width: 100%; border-collapse: collapse; font-size: 11px; }
        .grid-table th { background: #f1f5f9; color: #475569; font-weight: 600; padding: 6px 8px; text-align: left; border: 1px solid #e2e8f0; }
        .grid-table td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; }
        .grid-table tr:hover td { background-color: #f8fafc; }
        .grid-table .text-right { text-align: right; }
        .grid-table .text-center { text-align: center; }

        .badge-status { padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: 600; }
        .bg-green { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .bg-blue  { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
        .bg-yellow { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
        .bg-red   { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .bg-gray  { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
        .bg-purple { background: #faf5ff; color: #9333ea; border: 1px solid #e9d5ff; }

        .btn-tool { background: #fff; border: 1px solid #cbd5e1; padding: 2px 8px; font-size: 10px; color: #334155; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; height: 22px; border-radius: 2px; }
        .btn-tool:hover { background: #f1f5f9; border-color: #94a3b8; }
        .btn-tool.green { background: #3b82f6 !important; color: #fff !important; border-color: #2563eb !important; }

        @media print {
            .page-bar, .btn-tool, .detail-card-header .actions { display: none; }
            .page-content { padding: 0; background: #fff; }
            .detail-card { border: none; box-shadow: none; }
            .detail-card-header { background: #f8fafc; }
        }
    </style>
    @endpush

    <div class="page-content">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a></li>
                <li><i class="fa fa-angle-right"></i><a href="{{ route('accounting.ga-expense.index') }}">G&A Expenses</a></li>
                <li><i class="fa fa-angle-right"></i><span style="color: #333; font-weight: 700;">{{ $ga_expense->invoice_no }}</span></li>
            </ul>
        </div>

        <div style="margin-bottom: 15px; display: flex; gap: 4px; justify-content: flex-end;">
            <a class="btn-tool" href="{{ route('accounting.ga-expense.edit', $ga_expense->id) }}" target="_blank"><i class="fa fa-pencil"></i> Edit</a>
            <button class="btn-tool" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
            <a class="btn-tool" href="{{ route('accounting.ga-expense.index') }}" target="_blank"><i class="fa fa-arrow-left"></i> Back to List</a>
        </div>

        {{-- Main Details --}}
        <div class="detail-card">
            <div class="detail-card-header">
                <h4><i class="fa fa-file-text-o" style="color:#3b82f6;margin-right:6px;"></i> G&A Expense – {{ $ga_expense->invoice_no }}</h4>
                <div class="actions" style="display:flex;align-items:center;gap:8px;">
                    <span class="badge-status 
                        @switch($ga_expense->status)
                            @case('DRAFT') bg-gray @break
                            @case('POSTED') bg-blue @break
                            @case('PAID') bg-green @break
                            @case('PARTIAL') bg-yellow @break
                            @case('VOID') bg-red @break
                            @default bg-gray
                        @endswitch
                    ">{{ $ga_expense->status }}</span>
                    @if($ga_expense->color)
                        <span class="color-mark" style="background:{{ $ga_expense->color }};width:14px;height:14px;border-radius:3px;display:inline-block;border:1px solid rgba(0,0,0,0.1);"></span>
                    @endif
                </div>
            </div>
            <div class="detail-card-body">
                <div class="detail-grid">
                    <div class="detail-field">
                        <div class="detail-label">Payable To</div>
                        <div class="detail-value">
                            <span style="font-weight:600;color:#2563eb;">{{ $ga_expense->payable_to ?? '--' }}</span>
                        </div>
                    </div>
                    <div class="detail-field">
                        <div class="detail-label">Party</div>
                        <div class="detail-value">{{ $ga_expense->billTo->name ?? '--' }}</div>
                    </div>
                    <div class="detail-field">
                        <div class="detail-label">Reference No.</div>
                        <div class="detail-value">{{ $ga_expense->invoice_no }}</div>
                    </div>
                    <div class="detail-field">
                        <div class="detail-label">Type</div>
                        <div class="detail-value">{{ $ga_expense->type }}</div>
                    </div>
                    <div class="detail-field">
                        <div class="detail-label">Post Date</div>
                        <div class="detail-value">{{ $ga_expense->invoice_date ? $ga_expense->invoice_date->format('M d, Y') : '--' }}</div>
                    </div>
                    <div class="detail-field">
                        <div class="detail-label">Due Date</div>
                        <div class="detail-value">{{ $ga_expense->due_date ? $ga_expense->due_date->format('M d, Y') : '--' }}</div>
                    </div>
                    <div class="detail-field">
                        <div class="detail-label">Office</div>
                        <div class="detail-value">{{ $ga_expense->office->code ?? '--' }}</div>
                    </div>
                    <div class="detail-field">
                        <div class="detail-label">Currency</div>
                        <div class="detail-value">{{ $ga_expense->currency->code ?? 'USD' }}</div>
                    </div>
                    <div class="detail-field">
                        <div class="detail-label">Issued By</div>
                        <div class="detail-value">{{ $ga_expense->issuer->name ?? '--' }}</div>
                    </div>
                    <div class="detail-field">
                        <div class="detail-label">Billing Address</div>
                        <div class="detail-value">{{ $ga_expense->billing_address ?? '--' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Amount Details --}}
        <div class="detail-card">
            <div class="detail-card-header">
                <h4><i class="fa fa-calculator" style="color:#3b82f6;margin-right:6px;"></i> Amount Details</h4>
            </div>
            <div class="detail-card-body">
                <div class="detail-grid">
                    <div class="detail-field">
                        <div class="detail-label">Amount Before Tax</div>
                        <div class="detail-value" style="font-size:14px;font-weight:700;">{{ number_format($ga_expense->subtotal, 2) }}</div>
                    </div>
                    <div class="detail-field">
                        <div class="detail-label">Tax Amount</div>
                        <div class="detail-value" style="font-size:14px;font-weight:700;">{{ number_format($ga_expense->tax_total, 2) }}</div>
                    </div>
                    <div class="detail-field">
                        <div class="detail-label">Total Amount</div>
                        <div class="detail-value" style="font-size:14px;font-weight:700;color:#1e293b;">{{ number_format($ga_expense->total_amount, 2) }}</div>
                    </div>
                    <div class="detail-field">
                        <div class="detail-label">Paid Amount</div>
                        <div class="detail-value" style="font-size:14px;font-weight:700;color:#16a34a;">{{ number_format($ga_expense->paid_amount, 2) }}</div>
                    </div>
                    <div class="detail-field">
                        <div class="detail-label">Balance</div>
                        <div class="detail-value" style="font-size:14px;font-weight:700;color:{{ $ga_expense->balance_amount > 0 ? '#dc2626' : '#1e293b' }};">{{ number_format($ga_expense->balance_amount, 2) }}</div>
                    </div>
                    @if($ga_expense->discount_pct)
                    <div class="detail-field">
                        <div class="detail-label">Discount</div>
                        <div class="detail-value">{{ $ga_expense->discount_pct }}%</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Invoice Lines --}}
        @if($ga_expense->lines->count() > 0)
        <div class="detail-card">
            <div class="detail-card-header">
                <h4><i class="fa fa-list" style="color:#3b82f6;margin-right:6px;"></i> Expense Lines</h4>
            </div>
            <div class="detail-card-body" style="padding:0;">
                <table class="grid-table">
                    <thead>
                        <tr>
                            <th style="width:30px;text-align:center;">#</th>
                            <th>Billing Code</th>
                            <th>Description</th>
                            <th style="width:60px;text-align:right;">Qty</th>
                            <th style="width:80px;text-align:right;">Rate</th>
                            <th style="width:110px;text-align:right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ga_expense->lines as $i => $line)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $line->charge_code ?? '--' }}</td>
                            <td>{{ $line->description }}</td>
                            <td class="text-right">{{ $line->qty }}</td>
                            <td class="text-right">{{ number_format($line->rate, 2) }}</td>
                            <td class="text-right">{{ number_format($line->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Payment History --}}
        @if($ga_expense->payments->count() > 0)
        <div class="detail-card">
            <div class="detail-card-header">
                <h4><i class="fa fa-credit-card" style="color:#3b82f6;margin-right:6px;"></i> Payment History</h4>
            </div>
            <div class="detail-card-body" style="padding:0;">
                <table class="grid-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th style="text-align:right;">Amount</th>
                            <th>Method</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ga_expense->payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date ? $payment->payment_date->format('m-d-Y') : '--' }}</td>
                            <td>{{ $payment->reference_no ?? '--' }}</td>
                            <td class="text-right">{{ number_format($payment->amount ?? 0, 2) }}</td>
                            <td>{{ $payment->payment_method ?? '--' }}</td>
                            <td>{{ $payment->notes ?? '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Internal Remark --}}
        @if($ga_expense->internal_remark)
        <div class="detail-card">
            <div class="detail-card-header">
                <h4><i class="fa fa-sticky-note-o" style="color:#3b82f6;margin-right:6px;"></i> Internal Remark</h4>
            </div>
            <div class="detail-card-body">
                <p style="font-size:12px;color:#475569;margin:0;">{{ $ga_expense->internal_remark }}</p>
            </div>
        </div>
        @endif
    </div>
</x-layout>
