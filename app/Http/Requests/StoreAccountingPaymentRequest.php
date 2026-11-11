<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountingPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = auth()->user()?->company_id ?? 1;
        $cleanMethod = strtoupper(str_replace([' ', '_', '-'], '', (string)$this->input('payment_method')));
        $isCash = $cleanMethod === 'CASH';

        return [
            'payment_no' => 'required|string|max:255|unique:payments,payment_no',
            'payment_date' => 'required|date',
            'trade_partner_id' => 'required|exists:trade_partners,id',
            'currency_id' => 'required|exists:currencies,id',
            'exchange_rate' => 'nullable|numeric|min:0.000001',
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) use ($companyId) {
                    $paymentType = $this->input('type');
                    $allocations = collect($this->input('allocations', []))
                        ->filter(fn ($row) => !empty($row['invoice_id']));

                    if ($allocations->count() > 1) {
                        $sumAllocAmount = 0.0;
                        foreach ($allocations as $row) {
                            $allocAmt = (float) ($row['amount'] ?? 0);
                            $allocTds = (float) ($row['tds_tax'] ?? 0);
                            $allocDisc = (float) ($row['discount'] ?? 0);
                            $sumAllocAmount += $allocAmt;

                            if ($allocAmt + $allocTds + $allocDisc <= 0) {
                                $fail('Each selected invoice must have a positive payment, TDS, or discount allocation.');
                                return;
                            }

                            $inv = \App\Models\Invoice::where('company_id', $companyId)->find($row['invoice_id']);
                            if (!$inv) {
                                $fail('Selected invoice does not belong to your company.');
                                return;
                            }

                            if (!in_array(strtoupper($inv->status), ['POSTED', 'PARTIAL', 'PARTIALLY_PAID']) || $inv->verification_status !== 'Verified') {
                                $fail("Invoice {$inv->invoice_no} must be verified and posted before payments can be applied.");
                                return;
                            }

                            if ($paymentType === 'RECEIVED' && $inv->type === 'AP') {
                                $fail("Cannot record a RECEIVED payment against an Accounts Payable bill ({$inv->invoice_no}). Use Payment Make instead.");
                                return;
                            }
                            if ($paymentType === 'MADE' && $inv->type === 'AR') {
                                $fail("Cannot record a MADE payment against an Accounts Receivable invoice ({$inv->invoice_no}). Use Payment Receive instead.");
                                return;
                            }

                            if ($this->filled('currency_id') && $inv->currency_id && (int) $this->input('currency_id') !== (int) $inv->currency_id) {
                                $fail("Payment currency must match invoice currency ({$inv->currency?->code}).");
                                return;
                            }

                            if ((float) $inv->total_amount > 0) {
                                $remaining = max(0, round((float) $inv->balance_amount, 2));
                                $rowTotal = $allocAmt + $allocTds + $allocDisc;
                                if ($rowTotal > $remaining + 0.001) {
                                    $fail("Allocation for invoice {$inv->invoice_no} (\${$rowTotal}) exceeds remaining balance (\${$remaining}).");
                                    return;
                                }
                            }
                        }

                        if (abs($sumAllocAmount - (float) $value) > 0.02) {
                            $fail("Total payment amount (\${$value}) must equal the sum of invoice allocations (\${$sumAllocAmount}).");
                        }
                        return;
                    }

                    $firstAlloc = $allocations->first();
                    $invoiceId = $firstAlloc['invoice_id'] ?? $this->input('invoice_id');
                    if ($invoiceId) {
                        $invoice = \App\Models\Invoice::where('company_id', $companyId)->find($invoiceId);
                        if (!$invoice) {
                            $fail('Selected invoice does not belong to your company.');
                            return;
                        }

                        if (!in_array(strtoupper($invoice->status), ['POSTED', 'PARTIAL', 'PARTIALLY_PAID']) || $invoice->verification_status !== 'Verified') {
                            $fail("Invoice {$invoice->invoice_no} must be verified and posted before payments can be applied.");
                            return;
                        }

                        if ($paymentType === 'RECEIVED' && $invoice->type === 'AP') {
                            $fail("Cannot record a RECEIVED payment against an Accounts Payable bill ({$invoice->invoice_no}). Use Payment Make instead.");
                            return;
                        }
                        if ($paymentType === 'MADE' && $invoice->type === 'AR') {
                            $fail("Cannot record a MADE payment against an Accounts Receivable invoice ({$invoice->invoice_no}). Use Payment Receive instead.");
                            return;
                        }

                        if ($this->filled('currency_id') && $invoice->currency_id && (int) $this->input('currency_id') !== (int) $invoice->currency_id) {
                            $fail("Payment currency must match invoice currency ({$invoice->currency?->code}).");
                            return;
                        }

                        if ((float) $invoice->total_amount > 0) {
                            $remaining = max(0, round((float) $invoice->balance_amount, 2));
                            $tds = (float) ($firstAlloc['tds_tax'] ?? $this->input('tds_tax', 0));
                            $discount = (float) ($firstAlloc['discount'] ?? $this->input('discount', 0));
                            $totalSettlement = (float) $value + $tds + $discount;
                            if ($totalSettlement > $remaining + 0.001) {
                                $fail("Total settlement (Payment \${$value} + TDS \${$tds} + Discount \${$discount} = \${$totalSettlement}) exceeds remaining invoice balance (\${$remaining}). Overpayments must be logged as Customer Advance.");
                            }
                        }
                    }
                }
            ],
            'tds_tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string|max:50',
            'reference_no' => 'nullable|string|max:255',
            'remark' => 'nullable|string',
            'type' => 'required|in:RECEIVED,MADE',
            'invoice_id' => [
                'nullable',
                Rule::exists('invoices', 'id')->where(fn($q) => $q->where('company_id', $companyId)),
            ],
            'allocations' => 'nullable|array',
            'allocations.*.invoice_id' => [
                'required_with:allocations',
                Rule::exists('invoices', 'id')->where(fn($q) => $q->where('company_id', $companyId)),
            ],
            'allocations.*.amount' => 'required_with:allocations|numeric|min:0',
            'allocations.*.tds_tax' => 'nullable|numeric|min:0',
            'allocations.*.discount' => 'nullable|numeric|min:0',
            // Payment Make specific fields
            'payment_level' => 'nullable|string|max:20',
            'show_party_on_check' => 'nullable|boolean',
            'check_no' => 'nullable|string|max:100',
            'clear_date' => 'nullable|date',
            'void_date' => 'nullable|date',
            'office_id' => 'nullable|exists:offices,id',
            'bank_name' => [
                $isCash ? 'nullable' : 'required',
                'string',
                'max:200',
                Rule::exists('banks', 'bank_name')->where(function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                }),
            ],
            'bank_currency_id' => 'nullable|exists:currencies,id',
        ];
    }

    public function messages(): array
    {
        return [
            'bank_name.required' => 'Bank selection is mandatory from Settings for this payment method.',
            'bank_name.exists' => 'The selected Bank is invalid or not registered in Settings. Please select a valid bank.',
        ];
    }
}

