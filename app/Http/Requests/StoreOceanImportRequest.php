<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOceanImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $mergeData = [
            'is_direct_master' => $this->boolean('is_direct_master'),
            'is_ecommerce' => $this->boolean('is_ecommerce'),
            'is_obl_received' => $this->boolean('is_obl_received'),
            'is_released' => $this->boolean('is_released'),
            'is_isf_3rd_party' => $this->boolean('is_isf_3rd_party'),
            'is_ror' => $this->boolean('is_ror'),
            'is_hold' => $this->boolean('is_hold'),
        ];

        if (empty($this->bl_type)) $mergeData['bl_type'] = 'NORMAL';
        if (empty($this->cargo_type)) $mergeData['cargo_type'] = 'GENERAL CARGO';
        if (empty($this->ship_mode)) $mergeData['ship_mode'] = 'FCL';

        $this->merge($mergeData);

        if ($this->has('hbls') && is_array($this->hbls)) {
            $hbls = $this->hbls;
            foreach ($hbls as $index => $hbl) {
                $hbls[$index]['is_express_bl'] = isset($hbl['is_express_bl']) && ($hbl['is_express_bl'] == '1' || $hbl['is_express_bl'] == 'on' || $hbl['is_express_bl'] === true);
                $hbls[$index]['is_door_move'] = isset($hbl['is_door_move']) && ($hbl['is_door_move'] == '1' || $hbl['is_door_move'] == 'on' || $hbl['is_door_move'] === true);
                $hbls[$index]['is_customs_clear'] = isset($hbl['is_customs_clear']) && ($hbl['is_customs_clear'] == '1' || $hbl['is_customs_clear'] == 'on' || $hbl['is_customs_clear'] === true);
                $hbls[$index]['is_customs_hold'] = isset($hbl['is_customs_hold']) && ($hbl['is_customs_hold'] == '1' || $hbl['is_customs_hold'] == 'on' || $hbl['is_customs_hold'] === true);
                $hbls[$index]['is_obl_received'] = isset($hbl['is_obl_received']) && ($hbl['is_obl_received'] == '1' || $hbl['is_obl_received'] == 'on' || $hbl['is_obl_received'] === true);
                $hbls[$index]['is_fr_released'] = isset($hbl['is_fr_released']) && ($hbl['is_fr_released'] == '1' || $hbl['is_fr_released'] == 'on' || $hbl['is_fr_released'] === true);
                $hbls[$index]['is_an_sent'] = isset($hbl['is_an_sent']) && ($hbl['is_an_sent'] == '1' || $hbl['is_an_sent'] == 'on' || $hbl['is_an_sent'] === true);
                $hbls[$index]['is_do_sent'] = isset($hbl['is_do_sent']) && ($hbl['is_do_sent'] == '1' || $hbl['is_do_sent'] == 'on' || $hbl['is_do_sent'] === true);
                $hbls[$index]['is_ecommerce'] = isset($hbl['is_ecommerce']) && ($hbl['is_ecommerce'] == '1' || $hbl['is_ecommerce'] == 'on' || $hbl['is_ecommerce'] === true);
                $hbls[$index]['is_customs_doc'] = isset($hbl['is_customs_doc']) && ($hbl['is_customs_doc'] == '1' || $hbl['is_customs_doc'] == 'on' || $hbl['is_customs_doc'] === true);
                $hbls[$index]['is_rail'] = isset($hbl['is_rail']) && ($hbl['is_rail'] == '1' || $hbl['is_rail'] == 'on' || $hbl['is_rail'] === true);
            }
            $this->merge(['hbls' => $hbls]);
        }
        
        if ($this->has('containers') && is_array($this->containers)) {
            $containers = $this->containers;
            foreach ($containers as $index => $container) {
                $containers[$index]['is_dg'] = isset($container['is_dg']) && ($container['is_dg'] == '1' || $container['is_dg'] == 'on' || $container['is_dg'] === true);
                $containers[$index]['is_carrier_release'] = isset($container['is_carrier_release']) && ($container['is_carrier_release'] == '1' || $container['is_carrier_release'] == 'on' || $container['is_carrier_release'] === true);
                $containers[$index]['is_avail_pickup'] = isset($container['is_avail_pickup']) && ($container['is_avail_pickup'] == '1' || $container['is_avail_pickup'] == 'on' || $container['is_avail_pickup'] === true);
                $containers[$index]['is_complete'] = isset($container['is_complete']) && ($container['is_complete'] == '1' || $container['is_complete'] == 'on' || $container['is_complete'] === true);
                $containers[$index]['is_customs_hold'] = isset($container['is_customs_hold']) && ($container['is_customs_hold'] == '1' || $container['is_customs_hold'] == 'on' || $container['is_customs_hold'] === true);
                $containers[$index]['is_an_sent'] = isset($container['is_an_sent']) && ($container['is_an_sent'] == '1' || $container['is_an_sent'] == 'on' || $container['is_an_sent'] === true);
                $containers[$index]['is_do_sent'] = isset($container['is_do_sent']) && ($container['is_do_sent'] == '1' || $container['is_do_sent'] == 'on' || $container['is_do_sent'] === true);

                foreach (['lfd','fdd','storage_start_date','storage_end_date','unload_vessel_date','gate_in_date','rail_start_date','pod_eta','appointment_date','pickup_date','gate_out_date','fdest_eta','eta_door','ata_door','empty_conf_date','empty_ret_date','an_sent_date','do_sent_date'] as $dateField) {
                    if (isset($container[$dateField]) && $container[$dateField] === '') $containers[$index][$dateField] = null;
                }
                foreach (['pkg_qty','weight_kg','weight_lb','measure_cbm','measure_cft','chassis_days','tare_weight','vgm','net_weight'] as $numField) {
                    if (isset($container[$numField]) && $container[$numField] === '') $containers[$index][$numField] = null;
                }
            }
            $this->merge(['containers' => $containers]);
        }
    }

    public function rules(): array
    {
        return [
            // Core
            'file_no' => 'required|string|max:255|unique:ocean_imports,file_no',
            'mbl_no' => 'nullable|string|max:255|unique:ocean_imports,mbl_no',
            'post_date' => 'nullable|date',
            'office_id' => 'nullable|exists:offices,id',
            
            // Parties
            'op_id' => 'nullable|exists:users,id',
            'forwarding_agent_id' => 'nullable|exists:trade_partners,id',
            'oversea_agent_id' => 'nullable|exists:trade_partners,id',
            'co_loader_id' => 'nullable|exists:trade_partners,id',
            'carrier_id' => 'nullable|exists:trade_partners,id',
            'acct_carrier_id' => 'nullable|exists:trade_partners,id',
            'business_referred_by_id' => 'nullable|exists:trade_partners,id',
            'is_direct_master' => 'sometimes|boolean',
            'dm_customer_id' => 'nullable|exists:trade_partners,id',
            'dm_shipper_id' => 'nullable|exists:trade_partners,id',
            'dm_consignee_id' => 'nullable|exists:trade_partners,id',
            'dm_notify_id' => 'nullable|exists:trade_partners,id',
            'dm_bill_to_id' => 'nullable|exists:trade_partners,id',
            'dm_sales_person_id' => 'nullable|exists:users,id',
            
            // Details
            'agent_ref_no' => 'nullable|string|max:255',
            'contract_no' => 'nullable|string|max:255',
            'sub_bl_no' => 'nullable|string|max:255',
            'bl_type' => 'nullable|string|max:255',
            'cargo_type' => 'nullable|string|max:255',
            'ship_mode' => 'nullable|string|max:255',
            
            // Logistics
            'vessel_id' => 'nullable|exists:vessels,id',
            'voyage' => 'nullable|string|max:255',
            'pol_id' => 'nullable|exists:ports,id',
            'pod_id' => 'nullable|exists:ports,id',
            'del_id' => 'nullable|exists:ports,id',
            'trans_shipment_id' => 'nullable|exists:ports,id',
            'trans_shipments' => 'nullable|array',
            'trans_shipments.*.port_id' => 'nullable|exists:ports,id',
            'trans_shipments.*.etd' => 'nullable|date',
            'trans_shipments.*.eta' => 'nullable|date',
            'trans_shipments.*.vessel_voyage' => 'nullable|string',
            'fdest_id' => 'nullable|exists:ports,id',
            'receipt_id' => 'nullable|exists:ports,id',
            'etd' => 'nullable|date',
            'eta' => 'nullable|date',
            'atd' => 'nullable|date',
            'ata' => 'nullable|date',
            'etb' => 'nullable|date',
            'final_eta' => 'nullable|date',
            'receipt_etd' => 'nullable|date',
            
            // Locations
            'cy_location_id' => 'nullable|exists:trade_partners,id',
            'cfs_location_id' => 'nullable|exists:trade_partners,id',
            'return_location_id' => 'nullable|exists:trade_partners,id',
            
            // Terms
            'service_term_from_id' => 'nullable|exists:service_terms,id',
            'service_term_to_id' => 'nullable|exists:service_terms,id',
            'freight_term' => 'nullable|string|max:255',
            'transportation' => 'nullable|string|max:255',
            'obl_type' => 'nullable|string|max:255',
            'obl_received_date' => 'nullable|date',
            'released_date' => 'nullable|date',
            'latest_gate_in' => 'nullable|date',
            'is_ecommerce' => 'sometimes|boolean',
            'is_obl_received' => 'sometimes|boolean',
            'is_released' => 'sometimes|boolean',
            'internal_remark' => 'nullable|string',
            'mark' => 'nullable|string',
            'description' => 'nullable|string',

            // Filing fields
            'ams_no' => 'nullable|string|max:255',
            'isf_no' => 'nullable|string|max:255',
            'isf_matched_date' => 'nullable|date',
            'is_isf_3rd_party' => 'sometimes|boolean',
            'entry_no' => 'nullable|string|max:255',
            'entry_doc_sent_date' => 'nullable|date',
            'go_date' => 'nullable|date',
            'available_date' => 'nullable|date',
            'c_released_date' => 'nullable|date',
            'released_by_id' => 'nullable|exists:users,id',
            'is_ror' => 'sometimes|boolean',
            'is_hold' => 'sometimes|boolean',
            'door_delivery_date' => 'nullable|date',
            'trucker_id' => 'nullable|exists:trade_partners,id',
            'expiry_date' => 'nullable|date',
            'sales_type' => 'nullable|string|max:255',
            'incoterm_id' => 'nullable|string|max:255',
            
            // Nested validation
            'hbls' => 'sometimes|array',
            'hbls.*.hbl_no' => 'required|string',
            'hbls.*.quotation_no' => 'nullable|string',
            'hbls.*.customer_id' => 'nullable|exists:trade_partners,id',
            'hbls.*.sales_person_id' => 'nullable|exists:users,id',
            'hbls.*.shipper_id' => 'nullable|exists:trade_partners,id',
            'hbls.*.consignee_id' => 'nullable|exists:trade_partners,id',
            'hbls.*.notify_party_id' => 'nullable|exists:trade_partners,id',
            'hbls.*.customs_broker_id' => 'nullable|exists:trade_partners,id',
            'hbls.*.delivery_location_id' => 'nullable|exists:trade_partners,id',
            'hbls.*.cfs_location_id' => 'nullable|exists:trade_partners,id',
            'hbls.*.referred_by_id' => 'nullable|exists:trade_partners,id',
            'hbls.*.freight_released_by_id' => 'nullable|exists:users,id',
            'hbls.*.pod_id' => 'nullable|exists:ports,id',
            'hbls.*.del_id' => 'nullable|exists:ports,id',
            'hbls.*.fdest_id' => 'nullable|exists:ports,id',
            'hbls.*.receipt_id' => 'nullable|exists:ports,id',
            'hbls.*.vessel_name' => 'nullable|string',
            'hbls.*.voyage_no' => 'nullable|string',
            'hbls.*.pre_carriage_by' => 'nullable|string',
            'hbls.*.service_term' => 'nullable|string',
            'hbls.*.ship_mode' => 'nullable|string',
            'hbls.*.ship_type' => 'nullable|string',
            'hbls.*.cargo_type' => 'nullable|string',
            'hbls.*.incoterms_id' => 'nullable|string',
            'hbls.*.date_of_issue' => 'nullable|date',
            'hbls.*.lc_no' => 'nullable|string',
            'hbls.*.sc_no' => 'nullable|string',
            'hbls.*.freight_payable_at' => 'nullable|string',
            'hbls.*.hbl_freight' => 'nullable|string',
            'hbls.*.is_express_bl' => 'sometimes|boolean',
            'hbls.*.is_door_move' => 'sometimes|boolean',
            'hbls.*.is_customs_clear' => 'sometimes|boolean',
            'hbls.*.is_customs_hold' => 'sometimes|boolean',
            'hbls.*.is_obl_received' => 'sometimes|boolean',
            'hbls.*.obl_received_date' => 'nullable|date',
            'hbls.*.is_fr_released' => 'sometimes|boolean',
            'hbls.*.fr_released_date' => 'nullable|date',
            'hbls.*.is_an_sent' => 'sometimes|boolean',
            'hbls.*.an_sent_date' => 'nullable|date',
            'hbls.*.is_do_sent' => 'sometimes|boolean',
            'hbls.*.do_sent_date' => 'nullable|date',
            'hbls.*.name_account' => 'nullable|string',
            'hbls.*.group_comm' => 'nullable|string',
            'hbls.*.line_code' => 'nullable|string',
            'hbls.*.is_ecommerce' => 'sometimes|boolean',
            'hbls.*.is_customs_doc' => 'sometimes|boolean',
            'hbls.*.is_rail' => 'sometimes|boolean',
            'hbls.*.hbl_remark' => 'nullable|string',
            'hbls.*.po_no' => 'nullable|string',
            'hbls.*.po_mapping_type' => 'nullable|string|in:container,item',
            'hbls.*.hbl_mark' => 'nullable|string',
            'hbls.*.hbl_description' => 'nullable|string',
            'hbls.*.arrival_notice_remark' => 'nullable|string',
            'hbls.*.delivery_order_remark' => 'nullable|string',
            
            // Nested HBL Containers
            'hbls.*.containers' => 'sometimes|array',
            'hbls.*.containers.*.container_no' => 'nullable|string',
            'hbls.*.containers.*.pkg_qty' => 'nullable|numeric',
            'hbls.*.containers.*.pkg_unit' => 'nullable|string',
            'hbls.*.containers.*.weight_kg' => 'nullable|numeric',
            'hbls.*.containers.*.weight_unit' => 'nullable|string',
            'hbls.*.containers.*.measure_cbm' => 'nullable|numeric',
            'hbls.*.containers.*.measure_unit' => 'nullable|string',
            'hbls.*.containers.*.po_no' => 'nullable|string',

            // Nested HBL Commodities
            'hbls.*.commodities' => 'sometimes|array',
            'hbls.*.commodities.*.commodity_desc' => 'nullable|string',
            'hbls.*.commodities.*.hts_code' => 'nullable|string',
            'hbls.*.commodities.*.container_no' => 'nullable|string',
            'hbls.*.commodities.*.po_no' => 'nullable|string',

            // Nested HBL Receipts
            'hbls.*.receipts' => 'sometimes|array',
            'hbls.*.receipts.*.receipt_no' => 'nullable|string',
            'hbls.*.receipts.*.vin_no' => 'nullable|string',
            'hbls.*.receipts.*.total_pcs' => 'nullable|integer',
            'hbls.*.receipts.*.available_pcs' => 'nullable|integer',
            'hbls.*.receipts.*.allocated_pcs' => 'nullable|integer',
            'hbls.*.receipts.*.unit' => 'nullable|string',
            'hbls.*.receipts.*.actual_weight' => 'nullable|numeric',
            'hbls.*.receipts.*.measurement' => 'nullable|numeric',
            'hbls.*.receipts.*.remarks' => 'nullable|string',

            'containers' => 'sometimes|array',
            'containers.*.container_no' => 'nullable|string',
            'containers.*.pp_ctf' => 'nullable|string',
            'containers.*.container_type_id' => 'nullable|exists:container_types,id',
            'containers.*.seal_no' => 'nullable|string',
            'containers.*.seal_no2' => 'nullable|string',
            'containers.*.lfd' => 'nullable|date',
            'containers.*.fdd' => 'nullable|date',
            'containers.*.storage_start_date' => 'nullable|date',
            'containers.*.storage_end_date' => 'nullable|date',
            'containers.*.unload_vessel_date' => 'nullable|date',
            'containers.*.gate_in_date' => 'nullable|date',
            'containers.*.rail_start_date' => 'nullable|date',
            'containers.*.pod_eta' => 'nullable|date',
            'containers.*.appointment_date' => 'nullable|date',
            'containers.*.pickup_date' => 'nullable|date',
            'containers.*.gate_out_date' => 'nullable|date',
            'containers.*.fdest_eta' => 'nullable|date',
            'containers.*.eta_door' => 'nullable|date',
            'containers.*.ata_door' => 'nullable|date',
            'containers.*.empty_conf_date' => 'nullable|date',
            'containers.*.empty_ret_date' => 'nullable|date',
            'containers.*.an_sent_date' => 'nullable|date',
            'containers.*.do_sent_date' => 'nullable|date',
            'containers.*.pkg_qty' => 'nullable|numeric',
            'containers.*.pkg_unit_id' => 'nullable|exists:package_units,id',
            'containers.*.weight_kg' => 'nullable|numeric',
            'containers.*.weight_lb' => 'nullable|numeric',
            'containers.*.measure_cbm' => 'nullable|numeric',
            'containers.*.measure_cft' => 'nullable|numeric',
            'containers.*.chassis_days' => 'nullable|numeric',
            'containers.*.tare_weight' => 'nullable|numeric',
            'containers.*.vgm' => 'nullable|numeric',
            'containers.*.net_weight' => 'nullable|numeric',
            'containers.*.pickup_no' => 'nullable|string',
            'containers.*.cprs_no' => 'nullable|string',
            'containers.*.cnru_no' => 'nullable|string',
            'containers.*.it_no' => 'nullable|string',
            'containers.*.is_dg' => 'sometimes|boolean',
            'containers.*.is_carrier_release' => 'sometimes|boolean',
            'containers.*.is_customs_hold' => 'sometimes|boolean',
            'containers.*.is_an_sent' => 'sometimes|boolean',
            'containers.*.is_do_sent' => 'sometimes|boolean',
            'containers.*.yard_location' => 'nullable|string',
            'containers.*.is_avail_pickup' => 'sometimes|boolean',
            'containers.*.trucker_id' => 'nullable|exists:trade_partners,id',
            'containers.*.is_complete' => 'sometimes|boolean',
            'containers.*.remarks' => 'nullable|string',
            'containers.*.internal_remarks' => 'nullable|string',

            // Memos
            'memos' => 'sometimes|array',
            'memos.*.id' => 'nullable|integer',
            'memos.*.subject' => 'required|string|max:255',
            'memos.*.content' => 'nullable|string',

            // Charges
            'charges' => 'sometimes|array',
            'charges.*.id' => 'nullable|integer',
            'charges.*.party' => 'nullable|string|max:255',
            'charges.*.party_name' => 'nullable|string|max:255',
            'charges.*.party_name_id' => 'nullable|integer',
            'charges.*.sal' => 'nullable|string|max:255',
            'charges.*.pr' => 'nullable|string|max:255',
            'charges.*.ppc' => 'nullable|string|max:255',
            'charges.*.chrg_code' => 'nullable|string|max:255',
            'charges.*.currency' => 'nullable|string|max:255',
            'charges.*.rate' => 'nullable|numeric',
            'charges.*.qty' => 'nullable|numeric',
            'charges.*.qty_type' => 'nullable|string|max:255',
            'charges.*.roe' => 'nullable|numeric',
            'charges.*.vat' => 'nullable|numeric',
            'charges.*.inv_no' => 'nullable|string|max:255',
            'charges.*.financial_date' => 'nullable|date',
            'charges.*.eq_bl_no' => 'nullable|string|max:255',
            'charges.*.remark' => 'nullable|string|max:255',
            'charges.*.remark_text' => 'nullable|string|max:255',
            'charges.*.mbl_no' => 'nullable|string|max:255',
            
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

    public function messages(): array
    {
        return [
            'file_no.required' => 'File No is required.',
            'file_no.unique' => 'This File No is already used. Please enter a unique File No.',
            'mbl_no.unique' => 'This MBL No is already used. Please enter a unique MBL No.',
            
            // Related records validation
            'office_id.exists' => 'The selected office does not exist.',
            'op_id.exists' => 'The selected operator does not exist.',
            'carrier_id.exists' => 'The selected carrier does not exist.',
            'vessel_id.exists' => 'The selected vessel does not exist.',
            'pol_id.exists' => 'The selected Port of Loading does not exist.',
            'pod_id.exists' => 'The selected Port of Discharge does not exist.',
            'dm_customer_id.exists' => 'The selected customer does not exist.',
            'dm_shipper_id.exists' => 'The selected shipper does not exist.',
            'dm_consignee_id.exists' => 'The selected consignee does not exist.',
            'dm_notify_id.exists' => 'The selected notify party does not exist.',
            'trucker_id.exists' => 'The selected trucker does not exist.',
            
            // Container validation
            'containers.*.container_type_id.exists' => 'One or more selected container types do not exist.',
            'containers.*.pkg_unit_id.exists' => 'One or more selected package units do not exist.',
            'containers.*.trucker_id.exists' => 'One or more selected truckers do not exist.',
            
            // HBL validation
            'hbls.*.hbl_no.required' => 'HBL No is required for each HBL.',
            'hbls.*.customer_id.exists' => 'One or more selected customers do not exist.',
            'hbls.*.shipper_id.exists' => 'One or more selected shippers do not exist.',
            'hbls.*.consignee_id.exists' => 'One or more selected consignees do not exist.',
            'hbls.*.notify_party_id.exists' => 'One or more selected notify parties do not exist.',
            
            // Date validation
            'etd.date' => 'ETD must be a valid date.',
            'eta.date' => 'ETA must be a valid date.',
            'post_date.date' => 'Post Date must be a valid date.',
        ];
    }

    public function attributes(): array
    {
        return [
            'file_no' => 'File No',
            'mbl_no' => 'MBL No',
            'office_id' => 'Office',
            'op_id' => 'Operator',
            'carrier_id' => 'Carrier',
            'vessel_id' => 'Vessel',
            'pol_id' => 'Port of Loading',
            'pod_id' => 'Port of Discharge',
            'del_id' => 'Place of Delivery',
            'fdest_id' => 'Final Destination',
            'dm_customer_id' => 'Customer',
            'dm_shipper_id' => 'Shipper',
            'dm_consignee_id' => 'Consignee',
            'dm_notify_id' => 'Notify Party',
            'etd' => 'ETD',
            'eta' => 'ETA',
            'atd' => 'ATD',
            'ata' => 'ATA',
        ];
    }
}
