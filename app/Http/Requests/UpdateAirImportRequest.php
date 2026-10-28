<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\IataMawbRule;

class UpdateAirImportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->has('referred_by_id') && !$this->has('business_referred_by_id')) {
            $this->merge(['business_referred_by_id' => $this->referred_by_id]);
        }
    }

    public function rules()
    {
        $id = $this->route('air_import') ? $this->route('air_import')->id : null;
        
        return [
            'file_no' => 'required|string|unique:air_imports,file_no,' . $id,
            'mawb_no' => 'nullable|string|unique:air_imports,mawb_no,' . $id,
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
            'gross_weight_kg' => 'nullable|numeric',
            'gross_weight_lb' => 'nullable|numeric',
            'weight_unit' => 'nullable|string',
            'chargeable_weight' => 'nullable|numeric',
            'chargeable_weight_kg' => 'nullable|numeric',
            'chargeable_weight_lb' => 'nullable|numeric',
            'volume' => 'nullable|numeric',
            'volume_weight_kg' => 'nullable|numeric',
            'volume_cbm' => 'nullable|numeric',
            
            'freight_term' => 'nullable|string',
            'freight' => 'nullable|string|max:255',
            'transportation' => 'nullable|string|max:255',
            'awb_type' => 'nullable|string',
            'cargo_type' => 'nullable|string',
            'stackable' => 'nullable|boolean',
            'incoterm_id' => 'nullable|exists:incoterms,id',
            'svc_term_from_id' => 'nullable|exists:service_terms,id',
            'svc_term_to_id' => 'nullable|exists:service_terms,id',
            'service_term_from' => 'nullable|string',
            'service_term_to' => 'nullable|string',
            'business_referred_by_id' => 'nullable|exists:trade_partners,id',
            'referred_by_id' => 'nullable|exists:trade_partners,id',
            'is_ecommerce' => 'boolean',
            'display_unit' => 'nullable|string',
            'internal_remark' => 'nullable|string',
            
            'hbls' => 'nullable|array',
            'hbls.*.id' => 'nullable|exists:air_import_hbls,id',
            'hbls.*.hawb_no' => 'nullable|string',
            'hbls.*.hbl_no' => 'nullable|string',
            'hbls.*.shipper_id' => 'nullable|exists:trade_partners,id',
            'hbls.*.consignee_id' => 'nullable|exists:trade_partners,id',
            'hbls.*.notify_party_id' => 'nullable|exists:trade_partners,id',
            'hbls.*.sales_person_id' => 'nullable|exists:users,id',
            'hbls.*.customer_id' => 'nullable|exists:trade_partners,id',
            'hbls.*.pkg_qty' => 'nullable|numeric',
            'hbls.*.pkg_unit_id' => 'nullable|exists:package_units,id',
            'hbls.*.gross_weight' => 'nullable|numeric',
            'hbls.*.gross_weight_kg' => 'nullable|numeric',
            'hbls.*.gross_weight_lb' => 'nullable|numeric',
            'hbls.*.chargeable_weight' => 'nullable|numeric',
            'hbls.*.chargeable_weight_kg' => 'nullable|numeric',
            'hbls.*.chargeable_weight_lb' => 'nullable|numeric',
            'hbls.*.volume' => 'nullable|numeric',
            'hbls.*.volume_weight_kg' => 'nullable|numeric',
            'hbls.*.volume_cbm' => 'nullable|numeric',
            'hbls.*.bill_to_id' => 'nullable|exists:trade_partners,id',
            'hbls.*.customs_broker_id' => 'nullable|exists:trade_partners,id',
            'hbls.*.freight_location_id' => 'nullable|exists:ports,id',
            'hbls.*.final_destination_id' => 'nullable|exists:ports,id',
            'hbls.*.final_eta' => 'nullable|date',
            'hbls.*.delivery_location_id' => 'nullable|exists:trade_partners,id',
            'hbls.*.trucker_id' => 'nullable|exists:trade_partners,id',
            'hbls.*.last_free_day' => 'nullable|date',
            'hbls.*.available_date' => 'nullable|date',
            'hbls.*.storage_start_date' => 'nullable|date',
            'hbls.*.sales_type' => 'nullable|string',
            'hbls.*.freight' => 'nullable|string|max:255',
            'hbls.*.transportation' => 'nullable|string|max:255',
            'hbls.*.quotation_id' => 'nullable|exists:quotations,id',
            'hbls.*.hsn_code' => 'nullable|string',
            'hbls.*.hsn' => 'nullable|string',
            'hbls.*.entry_no' => 'nullable|string',
            'hbls.*.class_of_entry' => 'nullable|string',
            'hbls.*.service_term_from_id' => 'nullable|exists:service_terms,id',
            'hbls.*.service_term_to_id' => 'nullable|exists:service_terms,id',
            'hbls.*.released_by_id' => 'nullable|exists:users,id',
            'hbls.*.cargo_released_to' => 'nullable|string',
            'hbls.*.c_released_date' => 'nullable|date',
            'hbls.*.door_delivered_date' => 'nullable|date',
            'hbls.*.ship_type' => 'nullable|string',
            'hbls.*.display_unit' => 'nullable|string',
            'hbls.*.is_frt_released' => 'nullable|boolean',
            'hbls.*.frt_released' => 'nullable|boolean',
            'hbls.*.frt_released_date' => 'nullable|date',
            'hbls.*.mark_text' => 'nullable|string',
            'hbls.*.description_text' => 'nullable|string',
            'hbls.*.hbl_remark' => 'nullable|string',
            'hbls.*.sub_hawbs' => 'nullable',
            'hbls.*.commodities' => 'nullable',
            'hbls.*.po_numbers' => 'nullable',
            'hbls.*.warehouse_receipts' => 'nullable',
            'hbls.*.hbl_is_ecommerce' => 'nullable|boolean',
            'hbls.*.is_ecommerce' => 'nullable|boolean',
            'hbls.*.hawb_memos' => 'nullable',
            'hbls.*.is_blocked' => 'nullable|boolean',
            'hbls.*.op_id' => 'nullable|exists:users,id',
            'hbls.*.commodity' => 'nullable|string',
            'hbls.*.incoterms_id' => 'nullable|exists:incoterms,id',
            'hbls.*.incoterm_id' => 'nullable|exists:incoterms,id',
            'hbls.*.freight_term' => 'nullable|string',
            'hbls.*.is_an_sent' => 'nullable|boolean',
            'hbls.*.an_sent_date' => 'nullable|date',
            'hbls.*.is_do_sent' => 'nullable|boolean',
            'hbls.*.do_sent_date' => 'nullable|date',

            // Filing fields
            'shipper_id' => 'nullable|exists:trade_partners,id',
            'consignee_id' => 'nullable|exists:trade_partners,id',
            'bill_to_id' => 'nullable|exists:trade_partners,id',
            'trucker_id' => 'nullable|exists:trade_partners,id',
            'pod_eta' => 'nullable|date',
            'ship_mode' => 'nullable|string',
            'go_date' => 'nullable|date',
            'sub_bl_no' => 'nullable|string',
            'final_destination_id' => 'nullable|exists:ports,id',
            'delivery_location_id' => 'nullable|exists:trade_partners,id',
            'final_eta' => 'nullable|date',
            'last_free_day' => 'nullable|date',
            'available_date' => 'nullable|date',
            'storage_start_date' => 'nullable|date',
            'cy_cfs_loc' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'ams_no' => 'nullable|string',
            'isf_no' => 'nullable|string',
            'isf_matched_date' => 'nullable|date',
            'isf_3rd_party' => 'nullable|boolean',
            'sales_type' => 'nullable|string',
            'c_released_date' => 'nullable|date',
            'entry_no' => 'nullable|string',
            'ror' => 'nullable|boolean',
            'released_by_id' => 'nullable|exists:users,id',
            'do_sent' => 'nullable|boolean',
            'do_sent_date' => 'nullable|date',
            'entry_doc_sent_date' => 'nullable|date',
            'hold' => 'nullable|boolean',
            'door_delivered_date' => 'nullable|date',
            'class_of_entry' => 'nullable|string',
            'cargo_released_to' => 'nullable|string',
            'ship_type' => 'nullable|string|in:NORMAL,S/W,T/S',
            'containers' => 'nullable|array',

            // Direct Master fields
            'is_direct_master' => 'nullable|boolean',
            'dm_customer_id' => 'nullable|exists:trade_partners,id',
            'dm_shipper_id' => 'nullable|exists:trade_partners,id',
            'dm_consignee_id' => 'nullable|exists:trade_partners,id',
            'dm_notify_id' => 'nullable|exists:trade_partners,id',
            'dm_bill_to_id' => 'nullable|exists:trade_partners,id',
            'dm_sales_person_id' => 'nullable|exists:users,id',
            'dm_sales_type' => 'nullable|string',

            // Charges
            'charges' => 'nullable|array',
            
            // Expanded Charge Fields
            
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

    public function messages()
    {
        return [
            'file_no.required' => 'File number is required',
            'file_no.unique' => 'This file number already exists. Please use a unique file number.',
            'mawb_no.unique' => 'This MAWB Number already exists. Please use a unique MAWB number.',
            'office_id.exists' => 'Selected office is invalid',
            'carrier_id.exists' => 'Selected carrier is invalid',
            'oversea_agent_id.exists' => 'Selected oversea agent is invalid',
            'dep_port_id.exists' => 'Selected departure port is invalid',
            'dst_port_id.exists' => 'Selected destination port is invalid',
        ];
    }
}
