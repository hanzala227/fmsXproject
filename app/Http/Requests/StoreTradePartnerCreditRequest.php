<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTradePartnerCreditRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'entries' => 'required|array',
            'entries.*.id' => 'required|exists:trade_partners,id',
            'entries.*.account_group_id' => 'nullable|exists:account_groups,id',
            'entries.*.payment_type' => 'nullable|string|in:COD,CREDIT,PREPAID,COLLECT',
            'entries.*.credit_term_unit' => 'nullable|string|max:50',
            'entries.*.credit_term_days' => 'nullable|integer|min:0|max:9999',
            'entries.*.credit_limit' => 'nullable|numeric|min:0|max:9999999999.99',
            'entries.*.remark' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'entries.required' => 'No credit entries provided.',
            'entries.*.id.required' => 'Each entry must have a customer ID.',
            'entries.*.id.exists' => 'Invalid customer ID detected.',
            'entries.*.account_group_id.exists' => 'Invalid account group selected.',
            'entries.*.payment_type.in' => 'Payment type must be one of: COD, CREDIT, PREPAID, COLLECT.',
            'entries.*.credit_limit.numeric' => 'Credit limit must be a valid number.',
        ];
    }
}
