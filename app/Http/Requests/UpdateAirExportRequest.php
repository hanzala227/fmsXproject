<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\IataMawbRule;

class UpdateAirExportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $gross = floatval($this->input('gross_weight', 0));
        $cbm = floatval($this->input('volume') ?? $this->input('volume_cbm', 0));
        $minVolumetric = round($cbm * 166.67, 2);
        $minRequired = max($gross, $minVolumetric);
        $chargeable = floatval($this->input('chargeable_weight', 0));

        $merge = [
            'is_ecommerce' => filter_var($this->is_ecommerce, FILTER_VALIDATE_BOOLEAN),
            'is_blocked' => filter_var($this->is_blocked, FILTER_VALIDATE_BOOLEAN),
            'is_direct_master' => filter_var($this->is_direct_master, FILTER_VALIDATE_BOOLEAN),
            'internal_remark' => $this->internal_remark ?? $this->memo,
        ];

        // IATA TACT Rule: Automatically guarantee chargeable weight >= max(gross, volumetric)
        if ($minRequired > 0 && ($chargeable < $minRequired || $chargeable == 0)) {
            $merge['chargeable_weight'] = $minRequired;
        }

        $this->merge($merge);
    }

    public function rules()
    {
        $id = $this->route('air_export') ? $this->route('air_export')->id : null;

        return [
            'file_no' => 'required|string|unique:air_exports,file_no,' . $id,
            'mawb_no' => ['nullable', 'string', new IataMawbRule()],
            'booking_no' => 'nullable|string',
            'post_date' => 'nullable|date',
            'office_id' => 'nullable|exists:offices,id',
            'op_id' => 'nullable|exists:users,id',
            'forwarding_agent_id' => 'nullable|exists:trade_partners,id',
            'oversea_agent_id' => 'nullable|exists:trade_partners,id',
            'carrier_id' => 'nullable|exists:trade_partners,id',
            'acct_carrier_id' => 'nullable|exists:trade_partners,id',

            'flight_no' => 'nullable|string',
            'dep_port_id' => 'nullable|exists:ports,id',
            'dst_port_id' => 'nullable|exists:ports,id',
            'etd' => 'nullable|date',
            'eta' => 'nullable|date|after_or_equal:etd',
            'atd' => 'nullable|date',
            'ata' => 'nullable|date|after_or_equal:atd',

            'pkg_qty' => 'nullable|numeric',
            'pkg_unit_id' => 'nullable|exists:package_units,id',
            'gross_weight' => 'nullable|numeric',
            'chargeable_weight' => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) {
                    $gross = floatval($this->input('gross_weight', 0));
                    $cbm = floatval($this->input('volume') ?? $this->input('volume_cbm', 0));
                    $minVolumetric = round($cbm * 166.67, 2);
                    $minRequired = max($gross, $minVolumetric);
                    if ($minRequired > 0 && floatval($value) < ($minRequired - 0.05)) {
                        $fail("Chargeable weight cannot be less than {$minRequired} kg (Gross: {$gross} kg, IATA 1:6 Volumetric: {$minVolumetric} kg).");
                    }
                }
            ],
            'volume' => 'nullable|numeric',
            'buying_rate' => 'nullable|numeric',
            'selling_rate' => 'nullable|numeric',

            'freight_term' => 'nullable|string',
            'is_ecommerce' => 'boolean',
            'sales_type' => 'nullable|string',
            'is_blocked' => 'boolean',
            'is_direct_master' => 'boolean',
            'cargo_type' => 'nullable|string|max:100',
            'display_unit' => 'nullable|string|max:20',
            'internal_remark' => 'nullable|string',

            // Direct Master fields
            'agent_ref_no' => 'nullable|string',
            'dm_customer_id' => 'nullable|exists:trade_partners,id',
            'dm_shipper_id' => 'nullable|exists:trade_partners,id',
            'dm_bill_to_id' => 'nullable|exists:trade_partners,id',
            'dm_consignee_id' => 'nullable|exists:trade_partners,id',
            'dm_notify_id' => 'nullable|exists:trade_partners,id',
            'dm_sales_person_id' => 'nullable|exists:users,id',

            // MAWB party fields
            'shipper_id' => 'nullable|exists:trade_partners,id',
            'consignee_id' => 'nullable|exists:trade_partners,id',
            'notify_id' => 'nullable|exists:trade_partners,id',
            'actual_shipper_id' => 'nullable|exists:trade_partners,id',

            // Additional fields
            'incoterm_id' => 'nullable|max:255',
            'mark_number' => 'nullable|string',
            'service_term_from' => 'nullable|string',
            'service_term_to' => 'nullable|string',
            'agent_id' => 'nullable|exists:trade_partners,id',
            'co_loader_id' => 'nullable|exists:trade_partners,id',
            'trans_port_id' => 'nullable|exists:ports,id',
            'trans_port1_id' => 'nullable|exists:ports,id',
            'trans_port2_id' => 'nullable|exists:ports,id',
            'trans_port3_id' => 'nullable|exists:ports,id',
            'delivery_port_id' => 'nullable|exists:ports,id',
            'route_data' => 'nullable|array',
            'other_charges' => 'nullable|array',
            'accounting_info' => 'nullable|array',
            'commodities' => 'nullable|array',
            'itn_no' => 'nullable|string|max:255',
            'cers_no' => 'nullable|string|max:255',
            'reference_no' => 'nullable|string|max:255',
            'awb_date' => 'nullable|date',
            'cargo_ready_date' => 'nullable|date',
            'issuing_carrier' => 'nullable|string|max:255',
            'awb_type' => 'nullable|string|max:50',
            'dv_carriage' => 'nullable|string|max:255',
            'dv_customs' => 'nullable|string|max:255',
            'insurance' => 'nullable|string|max:255',
            'wt_val' => 'nullable|string|max:255',
            'other_term' => 'nullable|string|max:255',

            'hbls' => 'nullable|array',
            'hbls.*.id' => 'nullable|integer',
            'hbls.*.hawb_no' => 'nullable|string',
            'hbls.*.customer_id' => 'nullable|exists:trade_partners,id',
            'hbls.*.shipper_id' => 'nullable|exists:trade_partners,id',
            'hbls.*.consignee_id' => 'nullable|exists:trade_partners,id',
            'hbls.*.notify_party_id' => 'nullable|exists:trade_partners,id',
            'hbls.*.sales_person_id' => 'nullable|exists:users,id',
            'hbls.*.pkg_qty' => 'nullable|numeric',
            'hbls.*.pkg_unit_id' => 'nullable|exists:package_units,id',
            'hbls.*.gross_weight' => 'nullable|numeric',
            'hbls.*.chargeable_weight' => 'nullable|numeric',
            'hbls.*.volume' => 'nullable|numeric',
            'hbls.*.buying_rate' => 'nullable|numeric',
            'hbls.*.selling_rate' => 'nullable|numeric',
            'hbls.*.commodity' => 'nullable|string',
            'hbls.*.incoterms_id' => 'nullable|string',
            'hbls.*.freight_term' => 'nullable|string',
            'hbls.*.sales_type' => 'nullable|string',
            'hbls.*.hbl_remark' => 'nullable|string',
            'hbls.*.booking_date' => 'nullable|date',
            'hbls.*.itn_no' => 'nullable|string',
            'hbls.*.departure' => 'nullable|string',
            'hbls.*.destination' => 'nullable|string',
            'hbls.*.feta' => 'nullable|date',
            'hbls.*.cargo_type' => 'nullable|string',
            'hbls.*.ship_type' => 'nullable|string',
            'hbls.*.display_unit' => 'nullable|string',
            'hbls.*.bill_to' => 'nullable|string',
            'hbls.*.cargo_pickup' => 'nullable|string',
            'hbls.*.delivery_to' => 'nullable|string',
            'hbls.*.oversea_agent_id' => 'nullable|exists:trade_partners,id',
            'hbls.*.quotation_no' => 'nullable|string',
            'hbls.*.issuing_carrier' => 'nullable|string',
            'hbls.*.booking_no' => 'nullable|string',
            'hbls.*.cargo_ready_date' => 'nullable|date',
            'hbls.*.dv_carriage' => 'nullable|numeric',
            'hbls.*.dv_customs' => 'nullable|numeric',
            'hbls.*.insurance' => 'nullable|numeric',
            'hbls.*.other_charge_term' => 'nullable|string',
            'hbls.*.mark' => 'nullable|string',
            'hbls.*.description' => 'nullable|string',
            'hbls.*.remark' => 'nullable|string',

            'charges' => 'nullable|array',
            
            'routes' => 'nullable|array',
            'routes.*.airport_id' => 'nullable|exists:ports,id',
            'routes.*.eta' => 'nullable|date',
            'routes.*.ata' => 'nullable|date',
            'routes.*.etd' => 'nullable|date',
            'routes.*.atd' => 'nullable|date',
            'routes.*.flight_no' => 'nullable|string|max:255',
            'routes.*.carrier_id' => 'nullable|exists:trade_partners,id',
        ];
    }
}
