<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
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
        $id = $this->route('invoice')?->id ?? $this->route('ga_expense')?->id;
        
        return [
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'bill_to_id' => 'required|exists:trade_partners,id',
            'billing_address' => 'nullable|string',
            'invoiceable_type' => 'nullable|string',
            'invoiceable_id' => 'nullable|integer',
            'currency_id' => 'required|exists:currencies,id',
            'subtotal' => 'nullable|numeric|min:0',
            'tax_total' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'balance_amount' => 'nullable|numeric',
            'status' => 'nullable|in:DRAFT,POSTED,PAID,PARTIAL,VOID',
            'type' => 'required|in:AR,AP',
            'office_id' => 'nullable|exists:offices,id',
            'issued_by' => 'nullable|exists:users,id',
            'discount_pct' => 'nullable|numeric|min:0|max:100',
            'tax_pct' => 'nullable|numeric|min:0|max:100',
            'shipping_amount' => 'nullable|numeric|min:0',
            'internal_remark' => 'nullable|string',
        ];
    }
}
