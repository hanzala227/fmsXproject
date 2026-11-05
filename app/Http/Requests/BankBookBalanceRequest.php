<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BankBookBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period_from'     => 'nullable|date',
            'period_to'       => 'nullable|date|after_or_equal:period_from',
            'bank_account_id' => 'nullable|exists:banks,id',
            'status'          => 'nullable|in:all,active,inactive',
            'type'            => 'nullable|in:all,Bank,Book',
            'report_type'     => 'nullable|in:Summary,Detail',
            'hide_subtotal'   => 'nullable',
            'currency'        => 'nullable|in:bank_currency,main_currency',
        ];
    }

    public function messages(): array
    {
        return [
            'period_to.after_or_equal' => 'End date must be after or equal to start date.',
            'bank_account_id.exists'   => 'Selected bank account does not exist in Settings.',
        ];
    }
}
