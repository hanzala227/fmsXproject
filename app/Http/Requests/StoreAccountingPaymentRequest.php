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
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) {
                    $invoiceId = $this->input('invoice_id');
                    if ($invoiceId) {
                        $invoice = \App\Models\Invoice::find($invoiceId);
                        if ($invoice && (float) $invoice->total_amount > 0) {
                            $remaining = max(0, round((float) $invoice->balance_amount, 2));
                            $tds = (float) $this->input('tds_tax', 0);
                            $discount = (float) $this->input('discount', 0);
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
            'invoice_id' => 'nullable|exists:invoices,id',
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

