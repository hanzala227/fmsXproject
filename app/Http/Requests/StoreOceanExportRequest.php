<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreOceanExportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
        // Ensure old input is flashed to session
        $this->flash();
        
        throw (new ValidationException($validator))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
    }

    protected function prepareForValidation()
    {
        $booleanFields = [
            'is_direct_master', 'is_ecommerce', 'is_isf_3rd_party',
            'is_ror', 'is_hold', 'is_blocked',
        ];
        $mergeData = [];
        foreach ($booleanFields as $field) {
            if ($this->has($field)) {
                $mergeData[$field] = filter_var($this->input($field), FILTER_VALIDATE_BOOLEAN);
            }
        }
        
        if (empty($this->bl_type)) $mergeData['bl_type'] = 'NORMAL';
        if (empty($this->cargo_type)) $mergeData['cargo_type'] = 'GENERAL CARGO';
        if (empty($this->ship_mode)) $mergeData['ship_mode'] = 'FCL';
        
        if (!empty($mergeData)) {
            $this->merge($mergeData);
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

        if ($this->has('hbls') && is_array($this->hbls)) {
            $hbls = $this->hbls;
            foreach ($hbls as $index => $hbl) {
                foreach (['is_rail', 'is_express_bl', 'is_door_move', 'is_customs_clear', 'is_customs_hold', 'is_obl_received', 'is_fr_released', 'is_an_sent', 'is_do_sent', 'is_ecommerce', 'is_customs_doc'] as $boolField) {
                    if (isset($hbl[$boolField])) {
                        $hbls[$index][$boolField] = filter_var($hbl[$boolField], FILTER_VALIDATE_BOOLEAN);
                    }
                }
                foreach (['date_of_issue', 'obl_received_date', 'fr_released_date', 'an_sent_date', 'do_sent_date'] as $dateField) {
                    if (isset($hbl[$dateField]) && $hbl[$dateField] === '') {
                        $hbls[$index][$dateField] = null;
                    }
                }
            }
            $this->merge(['hbls' => $hbls]);
        }
    }

    public function rules()
    {
        return [
            'file_no' => 'required|string|unique:ocean_exports,file_no',
            'mbl_no' => 'nullable|string|unique:ocean_exports,mbl_no',
            'booking_no' => 'nullable|string',
            'post_date' => 'nullable|date',
            'office_id' => 'nullable|exists:offices,id',
            'op_id' => 'nullable|exists:users,id',
            'forwarding_agent_id' => 'nullable|exists:trade_partners,id',
            'oversea_agent_id' => 'nullable|exists:trade_partners,id',
            'co_loader_id' => 'nullable|exists:trade_partners,id',
            'carrier_id' => 'nullable|exists:trade_partners,id',
            'acct_carrier_id' => 'nullable|exists:trade_partners,id',
            'business_referred_by_id' => 'nullable|exists:trade_partners,id',
            
            'is_direct_master' => 'boolean',
            'dm_customer_id' => 'nullable|exists:trade_partners,id',
            'dm_shipper_id' => 'nullable|exists:trade_partners,id',
            'dm_consignee_id' => 'nullable|exists:trade_partners,id',
            'dm_notify_id' => 'nullable|exists:trade_partners,id',
            'dm_bill_to_id' => 'nullable|exists:trade_partners,id',
            'dm_sales_person_id' => 'nullable|exists:users,id',
            
            'agent_ref_no' => 'nullable|string',
            'contract_no' => 'nullable|string',
            'sub_bl_no' => 'nullable|string',
            'bl_type' => 'nullable|string',
            'cargo_type' => 'nullable|string',
            'ship_mode' => 'nullable|string',
            
            'vessel_id' => 'nullable|exists:vessels,id',
            'voyage' => 'nullable|string',
            'pol_id' => 'nullable|exists:ports,id',
            'pod_id' => 'nullable|exists:ports,id',
            'del_id' => 'nullable|exists:ports,id',
            'fdest_id' => 'nullable|exists:ports,id',
            'receipt_id' => 'nullable|exists:ports,id',
            
            'etd' => 'nullable|date',
            'eta' => 'nullable|date',
            'atd' => 'nullable|date',
            'ata' => 'nullable|date',
            'etb' => 'nullable|date',
            'final_eta' => 'nullable|date',
            'receipt_etd' => 'nullable|date',
            
            'cy_location_id' => 'nullable|exists:trade_partners,id',
            'cfs_location_id' => 'nullable|exists:trade_partners,id',
            'return_location_id' => 'nullable|exists:trade_partners,id',
            
            'service_term_from_id' => 'nullable|exists:service_terms,id',
            'service_term_to_id' => 'nullable|exists:service_terms,id',
            'freight_term' => 'nullable|string',
            'freight' => 'nullable|string',
            'transportation' => 'nullable|string',
            'obl_type' => 'nullable|string',
            'obl_received_date' => 'nullable|date',
            'released_date' => 'nullable|date',
            'latest_gate_in' => 'nullable|date',
            
            'is_ecommerce' => 'boolean',
            'is_released' => 'boolean',
            'internal_remark' => 'nullable|string',
            
            'sales_type' => 'nullable|string',
            'incoterm_id' => 'nullable|string|exists:incoterms,code',
            'ams_no' => 'nullable|string',
            'isf_no' => 'nullable|string',
            'isf_matched_date' => 'nullable|date',
            'is_isf_3rd_party' => 'boolean',
            'entry_no' => 'nullable|string',
            'entry_doc_sent_date' => 'nullable|date',
            'go_date' => 'nullable|date',
            'available_date' => 'nullable|date',
            'c_released_date' => 'nullable|date',
            'released_by_id' => 'nullable|exists:users,id',
            'is_ror' => 'boolean',
            'is_hold' => 'boolean',
            'door_delivery_date' => 'nullable|date',
            'trucker_id' => 'nullable|exists:trade_partners,id',
            'expiry_date' => 'nullable|date',
            'is_blocked' => 'boolean',
            
            'containers' => 'nullable|array',
            'containers.*.container_no' => 'nullable|string',
            'containers.*.container_type_id' => 'nullable|exists:container_types,id',
            'containers.*.pp_ctf' => 'nullable|string',
            'containers.*.seal_no' => 'nullable|string',
            'containers.*.seal_no2' => 'nullable|string',
            'containers.*.pkg_qty' => 'nullable|numeric',
            'containers.*.pkg_unit_id' => 'nullable|exists:package_units,id',
            'containers.*.weight_kg' => 'nullable|numeric',
            'containers.*.weight_lb' => 'nullable|numeric',
            'containers.*.measure_cbm' => 'nullable|numeric',
            'containers.*.measure_cft' => 'nullable|numeric',
            'containers.*.pickup_no' => 'nullable|string',
            'containers.*.cprs_no' => 'nullable|string',
            'containers.*.cnru_no' => 'nullable|string',
            'containers.*.it_no' => 'nullable|string',
            'containers.*.yard_location' => 'nullable|string',
            'containers.*.chassis_days' => 'nullable|numeric',
            'containers.*.tare_weight' => 'nullable|numeric',
            'containers.*.vgm' => 'nullable|numeric',
            'containers.*.net_weight' => 'nullable|numeric',
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
            'containers.*.is_dg' => 'nullable|boolean',
            'containers.*.is_carrier_release' => 'nullable|boolean',
            'containers.*.is_avail_pickup' => 'nullable|boolean',
            'containers.*.is_complete' => 'nullable|boolean',
            'containers.*.is_customs_hold' => 'nullable|boolean',
            'containers.*.is_an_sent' => 'nullable|boolean',
            'containers.*.is_do_sent' => 'nullable|boolean',
            'containers.*.trucker_id' => 'nullable|exists:trade_partners,id',
            'containers.*.remarks' => 'nullable|string',
            'containers.*.internal_remarks' => 'nullable|string',
            
            'hbls' => 'nullable|array',
            'hbls.*.hbl_no' => 'required_with:hbls|string|unique:ocean_export_hbls,hbl_no',
            'hbls.*.quotation_no' => 'nullable|string',
            'hbls.*.customer_id' => 'nullable|exists:trade_partners,id',
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
            'hbls.*.pol_id' => 'nullable|exists:ports,id',
            'hbls.*.sales_person_id' => 'nullable|exists:users,id',
            'hbls.*.vessel_name' => 'nullable|string',
            'hbls.*.voyage_no' => 'nullable|string',
            'hbls.*.pre_carriage_by' => 'nullable|string',
            'hbls.*.service_term' => 'nullable|string',
            'hbls.*.ship_mode' => 'nullable|string',
            'hbls.*.ship_type' => 'nullable|string',
            'hbls.*.cargo_type' => 'nullable|string',
            'hbls.*.incoterms_id' => 'nullable|string',
            'hbls.*.sc_no' => 'nullable|string',
            'hbls.*.lc_no' => 'nullable|string',
            'hbls.*.freight_payable_at' => 'nullable|string',
            'hbls.*.is_rail' => 'nullable|boolean',
            'hbls.*.is_express_bl' => 'nullable|boolean',
            'hbls.*.is_door_move' => 'nullable|boolean',
            'hbls.*.is_customs_clear' => 'nullable|boolean',
            'hbls.*.is_customs_hold' => 'nullable|boolean',
            'hbls.*.is_obl_received' => 'nullable|boolean',
            'hbls.*.is_fr_released' => 'nullable|boolean',
            'hbls.*.is_an_sent' => 'nullable|boolean',
            'hbls.*.is_do_sent' => 'nullable|boolean',
            'hbls.*.is_ecommerce' => 'nullable|boolean',
            'hbls.*.is_customs_doc' => 'nullable|boolean',
            'hbls.*.date_of_issue' => 'nullable|date',
            'hbls.*.obl_received_date' => 'nullable|date',
            'hbls.*.fr_released_date' => 'nullable|date',
            'hbls.*.an_sent_date' => 'nullable|date',
            'hbls.*.do_sent_date' => 'nullable|date',
            'hbls.*.hbl_remark' => 'nullable|string',
            'hbls.*.po_no' => 'nullable|string',
            'hbls.*.name_account' => 'nullable|string',
            'hbls.*.group_comm' => 'nullable|string',
            'hbls.*.line_code' => 'nullable|string',
            
            'charges' => 'nullable|array',
            'charges.*.chrg_code' => 'nullable|string',
            'charges.*.charge_name' => 'nullable|string',
            'charges.*.party' => 'nullable|string',
            'charges.*.party_name_id' => 'nullable|integer',
            'charges.*.pr' => 'nullable|string',
            'charges.*.ppc' => 'nullable|string',
            'charges.*.sal' => 'nullable|string',
            'charges.*.currency' => 'nullable|string',
            'charges.*.rate' => 'nullable|numeric',
            'charges.*.qty' => 'nullable|numeric',
            'charges.*.qty_type' => 'nullable|string',
            'charges.*.vat' => 'nullable|numeric',
            'charges.*.inv_no' => 'nullable|string',
            'charges.*.financial_date' => 'nullable|date',
            'charges.*.eq_bl_no' => 'nullable|string',
            'charges.*.remark_text' => 'nullable|string',
            
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
            
            // HBL validation
            'hbls.*.hbl_no.required_with' => 'HBL No is required for each HBL.',
            'hbls.*.hbl_no.unique' => 'One or more HBL numbers are already used. Each HBL No must be unique.',
            
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
            
            // HBL related records
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
            'booking_no' => 'Booking No',
            'office_id' => 'Office',
            'op_id' => 'Operator',
            'carrier_id' => 'Carrier',
            'vessel_id' => 'Vessel',
            'pol_id' => 'Port of Loading',
            'pod_id' => 'Port of Discharge',
            'del_id' => 'Place of Delivery',
            'fdest_id' => 'Final Destination',
            'dm_customer_id' => 'client',
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
