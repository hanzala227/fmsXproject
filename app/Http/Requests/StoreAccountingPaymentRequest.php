<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
                            if ((float) $value > $remaining + 0.001) {
                                $fail("Payment amount (\${$value}) exceeds remaining invoice balance (\${$remaining}). Overpayments must be logged as Customer Advance.");
                            }
                        }
                    }
                }
            ],
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
            'bank_name' => 'nullable|string|max:200',
            'bank_currency_id' => 'nullable|exists:currencies,id',
        ];
    }
}
