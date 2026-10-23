<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseAutomobileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vin_no' => 'required|string|max:50|unique:warehouse_automobiles,vin_no',
            'wh_receipt_no' => 'nullable|string|max:50',
            'received_by' => 'nullable|exists:users,id',
            'received_date' => 'nullable|date',
            'customer_id' => 'nullable|exists:trade_partners,id',
            'maker' => 'nullable|string|max:100',
            'year' => 'nullable|string|max:10',
            'model' => 'nullable|string|max:100',
            'engine_no' => 'nullable|string|max:100',
            'manufacture_date' => 'nullable|date',
            'title_received' => 'nullable|boolean',
            'office_id' => 'nullable|exists:offices,id',
            'color' => 'nullable|string|max:20',
            'is_blocked' => 'nullable|boolean',
            'internal_remark' => 'nullable|string',
            'tag_no' => 'nullable|string|max:50',
            'vehicle_state' => 'nullable|string|max:100',
            'condition' => 'nullable|string|max:255',
            'key_number' => 'nullable|string|max:50',
            'fuel' => 'nullable|string|max:50',
            'tire_size_front' => 'nullable|string|max:50',
            'tire_size_rear' => 'nullable|string|max:50',
            'mileage' => 'nullable|string|max:50',
            'w_sticker' => 'nullable|boolean',
            'remote_control' => 'nullable|boolean',
            'headphone' => 'nullable|boolean',
            'owners_manual' => 'nullable|boolean',
            'cd_player' => 'nullable|boolean',
            'cd_changer' => 'nullable|boolean',
            'first_aid_kit' => 'nullable|boolean',
            'floor_mat' => 'nullable|boolean',
            'cigarette_lighter' => 'nullable|boolean',
            'cargo_net' => 'nullable|boolean',
            'ashtray' => 'nullable|boolean',
            'Tools' => 'nullable|boolean',
            'spare_tire' => 'nullable|boolean',
            'sun_roof' => 'nullable|boolean',
        ];
    }
}
