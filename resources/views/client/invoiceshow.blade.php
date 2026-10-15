@extends('layouts.userapp')
@section('title', 'Invoice ' . $invoice->invoice_no)

@section('content')

@php
    $bal = (float) $invoice->total_amount - (float) $invoice->paid_amount;
@endphp

<div class="page-content">

    <div class="page-bar">
        <ul class="page-breadcrumb">
            <li><i class="fa fa-home"></i> <a href="{{ route('client.index') }}">Home</a> <i class="fa fa-angle-right"></i></li>
            <li><a href="{{ route('cp.invoices.index') }}">My Invoices</a> <i class="fa fa-angle-right"></i></li>
            <li><span style="color:#333;font-weight:700;">{{ $invoice->invoice_no }}</span></li>
        </ul>
    </div>

    <div class="portlet">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-file-text-o" style="color:#3b82f6;"></i>
                <span class="caption-subject">INVOICE {{ $invoice->invoice_no }}</span>
            </div>
        </div>

        {{-- Index page sirf yeh div uthati hai --}}
        <div id="invoice-detail-content">

            <div class="sub-title">
                Invoice Information
                <span class="status-pill" style="margin-left:8px;">{{ $invoice->status }}</span>
            </div>

            <div class="details-grid">
                <div class="detail-item"><span class="detail-label">Invoice Number</span><span class="detail-value">{{ $invoice->invoice_no }}</span></div>
                <div class="detail-item"><span class="detail-label">Invoice Date</span><span class="detail-value">{{ $invoice->invoice_date ?? '-' }}</span></div>
                <div class="detail-item"><span class="detail-label">Due Date</span><span class="detail-value">{{ $invoice->due_date ?? '-' }}</span></div>
                <div class="detail-item"><span class="detail-label">Currency</span><span class="detail-value">{{ $invoice->currency ?? '-' }}</span></div>
                <div class="detail-item full"><span class="detail-label">Bill To</span><span class="detail-value">{{ $invoice->billing_address ?? '-' }}</span></div>
            </div>

            <div class="sub-title">Invoice Items</div>
            <div class="grid-wrapper">
                <table class="grid-table">
                    <thead>
                        <tr>
                            <th style="width:30px;text-align:center;">#</th>
                            <th>Description</th>
                            <th class="num">Qty</th>
                            <th class="num">Rate</th>
                            <th class="num">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lines as $l)
                            <tr>
                                <td style="text-align:center;color:#94a3b8;">{{ $loop->iteration }}</td>
                                <td>{{ $l->description ?? '-' }}</td>
                                <td class="num">{{ $l->qty ?? '-' }}</td>
                                <td class="num">{{ number_format((float) ($l->rate ?? 0), 2) }}</td>
                                <td class="num">{{ number_format((float) ($l->amount ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr class="empty-row">
                                <td colspan="5">
                                    <div class="empty-title">No Invoice Items</div>
                                    No line items were found for this invoice.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="total-box">
                <div class="total-row"><span>Subtotal</span><strong>{{ number_format((float) $invoice->subtotal, 2) }}</strong></div>
                <div class="total-row"><span>Tax</span><strong>{{ number_format((float) $invoice->tax_total, 2) }}</strong></div>
                <div class="total-row main"><span>Total</span><strong>{{ $invoice->currency ?? '' }} {{ number_format((float) $invoice->total_amount, 2) }}</strong></div>
                <div class="total-row"><span>Paid</span><strong>{{ number_format((float) $invoice->paid_amount, 2) }}</strong></div>
                <div class="total-row balance"><span>Balance Due</span><strong>{{ number_format($bal, 2) }}</strong></div>
            </div>

            <div class="sub-title">Payments Received</div>
            <div class="grid-wrapper">
                <table class="grid-table">
                    <thead>
                        <tr>
                            <th style="width:30px;text-align:center;">#</th>
                            <th>Payment #</th>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th class="num">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $p)
                            <tr>
                                <td style="text-align:center;color:#94a3b8;">{{ $loop->iteration }}</td>
                                <td>{{ $p->payment_no ?? '-' }}</td>
                                <td>{{ $p->payment_date ?? '-' }}</td>
                                <td>{{ $p->payment_method ?? '-' }}</td>
                                <td>{{ $p->reference_no ?? '-' }}</td>
                                <td class="num">{{ number_format((float) ($p->amount ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr class="empty-row">
                                <td colspan="6">
                                    <div class="empty-title">No Payments Yet</div>
                                    No payments have been recorded against this invoice.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

<link rel="stylesheet" href="{{ asset('css/client-portal.css') }}">

@endsection