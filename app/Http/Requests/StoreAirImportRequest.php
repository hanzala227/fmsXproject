<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\IataMawbRule;

class StoreAirImportRequest extends FormRequest
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
        return [
            // Required fields
            'file_no' => 'required|string|unique:air_imports,file_no',
            'mawb_no' => ['required', 'string', 'max:255', 'unique:air_imports,mawb_no', new IataMawbRule()],
            'office_id' => 'required|exists:offices,id',
            'eta' => 'nullable|date|after_or_equal:etd',
            
            // Optional fields with validation
            'post_date' => 'nullable|date',
            'op_id' => 'nullable|exists:users,id',
            'forwarding_agent_id' => 'nullable|exists:trade_partners,id',
            'oversea_agent_id' => 'nullable|exists:trade_partners,id',
            'carrier_id' => 'nullable|exists:trade_partners,id',
            'acct_carrier_id' => 'nullable|exists:trade_partners,id',
            'coloader_id' => 'nullable|exists:trade_partners,id',
            'business_referred_by_id' => 'nullable|exists:trade_partners,id',
            'referred_by_id' => 'nullable|exists:trade_partners,id',
            
            'awb_type' => 'nullable|string|in:NORMAL,DIRECT',
            'flight_no' => 'nullable|string|max:50',
            'dep_port_id' => 'nullable|exists:ports,id',
            'dst_port_id' => 'nullable|exists:ports,id',
            'freight_location_id' => 'nullable|exists:ports,id',
            'etd' => 'nullable|date',
            'atd' => 'nullable|date',
            'ata' => 'nullable|date|after_or_equal:atd',
            
            'pkg_qty' => 'nullable|numeric|min:0',
            'pkg_unit_id' => 'nullable|exists:package_units,id',
            'gross_weight_kg' => 'nullable|numeric|min:0',
            'gross_weight_lb' => 'nullable|numeric|min:0',
            'chargeable_weight_kg' => 'nullable|numeric|min:0',
            'chargeable_weight_lb' => 'nullable|numeric|min:0',
            'volume_weight_kg' => 'nullable|numeric|min:0',
            'volume_cbm' => 'nullable|numeric|min:0',
            
            'freight_term' => 'nullable|string|in:PREPAID,COLLECT',
            'freight' => 'nullable|string|max:255',
            'transportation' => 'nullable|string|max:255',
            'incoterm_id' => 'nullable|exists:incoterms,id',
            'svc_term_from_id' => 'nullable|exists:service_terms,id',
            'svc_term_to_id' => 'nullable|exists:service_terms,id',
            'cargo_type' => 'nullable|string',
            'stackable' => 'nullable|boolean',
            'is_ecommerce' => 'nullable|boolean',
            'display_unit' => 'nullable|string',
            'storage_start_date' => 'nullable|date',
            'available_date' => 'nullable|date',
            'internal_remark' => 'nullable|string',
            
            // Direct Master fields
            'is_direct_master' => 'nullable|boolean',
            'dm_customer_id' => 'nullable|exists:trade_partners,id',
            'dm_shipper_id' => 'nullable|exists:trade_partners,id',
            'dm_consignee_id' => 'nullable|exists:trade_partners,id',
            'dm_notify_id' => 'nullable|exists:trade_partners,id',
            'dm_bill_to_id' => 'nullable|exists:trade_partners,id',
            'dm_sales_person_id' => 'nullable|exists:users,id',
            'dm_sales_type' => 'nullable|string',
            
            // HBLs
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
            'hbls.*.hsn_code' => 'nullable|string|max:255',
            'hbls.*.hsn' => 'nullable|string|max:255',
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
            'hbls.*.commodity' => 'nullable|string|max:255',
            'hbls.*.incoterms_id' => 'nullable|exists:incoterms,id',
            'hbls.*.incoterm_id' => 'nullable|exists:incoterms,id',
            'hbls.*.freight_term' => 'nullable|string|max:255',
            'hbls.*.is_an_sent' => 'nullable|boolean',
            'hbls.*.an_sent_date' => 'nullable|date',
            'hbls.*.is_do_sent' => 'nullable|boolean',
            'hbls.*.do_sent_date' => 'nullable|date',
            'hbls.*.hbl_remark' => 'nullable|string',
            'hbls.*.mark_text' => 'nullable|string',
            'hbls.*.description_text' => 'nullable|string',
            'hbls.*.sub_hawbs' => 'nullable',
            'hbls.*.commodities' => 'nullable',
            'hbls.*.po_numbers' => 'nullable',
            'hbls.*.warehouse_receipts' => 'nullable',
            'hbls.*.hbl_is_ecommerce' => 'nullable|boolean',
            'hbls.*.is_ecommerce' => 'nullable|boolean',
            'hbls.*.hawb_memos' => 'nullable',
            'hbls.*.is_blocked' => 'nullable|boolean',
            'hbls.*.op_id' => 'nullable|exists:users,id',
            
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
            'cy_cfs_loc' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'ams_no' => 'nullable|string',
            'isf_no' => 'nullable|string',
            'isf_matched_date' => 'nullable|date',
            'isf_3rd_party' => 'nullable|boolean',
            'sales_type' => 'nullable|string',
            'c_released_date' => 'nullable|date',
            'entry_no' => 'nullable|string',
            'entry_doc_sent_date' => 'nullable|date',
            'service_term_from' => 'nullable|string',
            'service_term_to' => 'nullable|string',
            'ror' => 'nullable|boolean',
            'released_by_id' => 'nullable|exists:users,id',
            'do_sent' => 'nullable|boolean',
            'do_sent_date' => 'nullable|date',
            'hold' => 'nullable|boolean',
            'door_delivered_date' => 'nullable|date',
            'class_of_entry' => 'nullable|string',
            'cargo_released_to' => 'nullable|string',
            'ship_type' => 'nullable|string',
            
            // Containers
            'containers' => 'nullable|array',
            'containers.*.container_no' => 'nullable|string',
            'containers.*.pp_ctf' => 'nullable|string',
            'containers.*.container_type' => 'nullable|string',
            'containers.*.seal_no' => 'nullable|string',
            'containers.*.lfd' => 'nullable|date',
            'containers.*.pkg_qty' => 'nullable|numeric',
            'containers.*.weight_kg' => 'nullable|numeric',

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
            'mawb_no.required' => 'MAWB Number is required',
            'mawb_no.unique' => 'This MAWB Number already exists. Please use a unique MAWB number.',
            'office_id.required' => 'Office is required',
            'office_id.exists' => 'Selected office is invalid',

            'eta.date' => 'ETA must be a valid date',
            'carrier_id.exists' => 'Selected carrier is invalid',
            'oversea_agent_id.exists' => 'Selected oversea agent is invalid',
            'dep_port_id.exists' => 'Selected departure port is invalid',
            'dst_port_id.exists' => 'Selected destination port is invalid',
        ];
    }
}
