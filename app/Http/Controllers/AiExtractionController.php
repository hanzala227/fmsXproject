<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AiExtractionService;
use Exception;

class AiExtractionController extends Controller
{
    protected $aiService;

    public function __construct(AiExtractionService $aiService)
    {
        $this->aiService = $aiService;
    }

    // Helper to get or create a Port
    private function getOrCreatePort($name)
    {
        if (empty($name)) return null;
        $search = trim(explode('(', $name)[0]);
        $port = \App\Models\Port::where('name', 'like', '%' . $search . '%')
                                ->orWhere('code', 'like', '%' . $search . '%')
                                ->first();
        if (!$port) {
            $defaultCountry = \App\Models\Country::first();
            $countryId = $defaultCountry ? $defaultCountry->id : 1;
            $port = \App\Models\Port::create([
                'name' => $name,
                'code' => strtoupper(substr($search, 0, 5)),
                'country_id' => $countryId
            ]);
        }
        return $port->id;
    }

    // Helper to get or create a TradePartner
    private function getOrCreateTradePartner($name)
    {
        if (empty($name)) return null;
        $search = trim(explode(' ', $name)[0]);
        $tp = \App\Models\TradePartner::where('name', 'like', '%' . $search . '%')->first();
        if (!$tp) {
            $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 3)) . rand(1000, 9999);
            $tp = \App\Models\TradePartner::create([
                'name' => $name,
                'code' => $code
            ]);
        }
        return $tp->id;
    }

    // Helper to find a User
    private function getUser($name)
    {
        if (empty($name)) return null;
        $search = trim(explode(' ', $name)[0]);
        $user = \App\Models\User::where('name', 'like', '%' . $search . '%')->first();
        return $user ? $user->id : null;
    }

    public function extractOceanImport(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,txt,csv|max:10240', // max 10MB
        ]);

        try {
            $data = $this->aiService->extractData($request->file('document'), 'ocean_import');

            // --- Database Mapping (Jugaar) ---
            if (isset($data['mbl'])) {
                
                // Ports
                if (!empty($data['mbl']['pol_name'])) $data['mbl']['pol_id'] = $this->getOrCreatePort($data['mbl']['pol_name']);
                if (!empty($data['mbl']['pod_name'])) $data['mbl']['pod_id'] = $this->getOrCreatePort($data['mbl']['pod_name']);
                if (!empty($data['mbl']['del_name'])) $data['mbl']['del_id'] = $this->getOrCreatePort($data['mbl']['del_name']);
                if (!empty($data['mbl']['fdest_name'])) $data['mbl']['fdest_id'] = $this->getOrCreatePort($data['mbl']['fdest_name']);
                
                // TradePartners & Users
                if (!empty($data['mbl']['forwarding_agent_name'])) $data['mbl']['forwarding_agent_id'] = $this->getOrCreateTradePartner($data['mbl']['forwarding_agent_name']);
                if (!empty($data['mbl']['op_name'])) $data['mbl']['op_id'] = $this->getUser($data['mbl']['op_name']);
                if (!empty($data['mbl']['dm_sales_person_name'])) $data['mbl']['dm_sales_person_id'] = $this->getUser($data['mbl']['dm_sales_person_name']);
                if (!empty($data['mbl']['oversea_agent_name'])) $data['mbl']['oversea_agent_id'] = $this->getOrCreateTradePartner($data['mbl']['oversea_agent_name']);
                if (!empty($data['mbl']['co_loader_name'])) $data['mbl']['co_loader_id'] = $this->getOrCreateTradePartner($data['mbl']['co_loader_name']);
                if (!empty($data['mbl']['carrier_name'])) $data['mbl']['carrier_id'] = $this->getOrCreateTradePartner($data['mbl']['carrier_name']);
                if (!empty($data['mbl']['acct_carrier_name'])) $data['mbl']['acct_carrier_id'] = $this->getOrCreateTradePartner($data['mbl']['acct_carrier_name']);
                if (!empty($data['mbl']['dm_notify_name'])) $data['mbl']['dm_notify_id'] = $this->getOrCreateTradePartner($data['mbl']['dm_notify_name']);
                if (!empty($data['mbl']['cy_location_name'])) $data['mbl']['cy_location_id'] = $this->getOrCreateTradePartner($data['mbl']['cy_location_name']);
                if (!empty($data['mbl']['cfs_location_name'])) $data['mbl']['cfs_location_id'] = $this->getOrCreateTradePartner($data['mbl']['cfs_location_name']);
                if (!empty($data['mbl']['business_referred_by_name'])) $data['mbl']['business_referred_by_id'] = $this->getOrCreateTradePartner($data['mbl']['business_referred_by_name']);
                
                // Vessel
                if (!empty($data['mbl']['vessel_name'])) {
                    $vName = trim(explode('/', $data['mbl']['vessel_name'])[0]);
                    $vessel = \App\Models\Vessel::where('name', 'like', '%' . $vName . '%')->first();
                    if (!$vessel) {
                        $vessel = \App\Models\Vessel::create(['name' => $vName]);
                    }
                    $data['mbl']['vessel_id'] = $vessel->id;
                }
            }

            if (isset($data['hbls']) && is_array($data['hbls'])) {
                foreach ($data['hbls'] as &$hbl) {
                    if (!empty($hbl['shipper_name'])) $hbl['shipper_id'] = $this->getOrCreateTradePartner($hbl['shipper_name']);
                    if (!empty($hbl['consignee_name'])) $hbl['consignee_id'] = $this->getOrCreateTradePartner($hbl['consignee_name']);
                    if (!empty($hbl['notify_party_name'])) $hbl['notify_id'] = $this->getOrCreateTradePartner($hbl['notify_party_name']);
                    if (!empty($hbl['customer_name'])) $hbl['customer_id'] = $this->getOrCreateTradePartner($hbl['customer_name']);
                    if (!empty($hbl['sales_person_name'])) $hbl['sales_person_id'] = $this->getOrCreateTradePartner($hbl['sales_person_name']);
                    if (!empty($hbl['forwarding_agent_name'])) $hbl['forwarding_agent_id'] = $this->getOrCreateTradePartner($hbl['forwarding_agent_name']);
                }
            }
            
            \Log::info("AI Extracted Data with IDs:", $data);

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Document processed successfully.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function extractOceanExport(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,txt,csv|max:10240', // max 10MB
        ]);

        try {
            $data = $this->aiService->extractData($request->file('document'), 'ocean_export');

            // --- Database Mapping (Jugaar) ---
            if (isset($data['mbl'])) {
                
                // Ports
                if (!empty($data['mbl']['pol_name'])) $data['mbl']['pol_id'] = $this->getOrCreatePort($data['mbl']['pol_name']);
                if (!empty($data['mbl']['pod_name'])) $data['mbl']['pod_id'] = $this->getOrCreatePort($data['mbl']['pod_name']);
                if (!empty($data['mbl']['del_name'])) $data['mbl']['del_id'] = $this->getOrCreatePort($data['mbl']['del_name']);
                if (!empty($data['mbl']['fdest_name'])) $data['mbl']['fdest_id'] = $this->getOrCreatePort($data['mbl']['fdest_name']);
                if (!empty($data['mbl']['receipt_name'])) $data['mbl']['receipt_id'] = $this->getOrCreatePort($data['mbl']['receipt_name']);
                
                // TradePartners & Users
                if (!empty($data['mbl']['forwarding_agent_name'])) $data['mbl']['forwarding_agent_id'] = $this->getOrCreateTradePartner($data['mbl']['forwarding_agent_name']);
                if (!empty($data['mbl']['op_name'])) $data['mbl']['op_id'] = $this->getUser($data['mbl']['op_name']);
                if (!empty($data['mbl']['dm_sales_person_name'])) $data['mbl']['dm_sales_person_id'] = $this->getUser($data['mbl']['dm_sales_person_name']);
                if (!empty($data['mbl']['oversea_agent_name'])) $data['mbl']['oversea_agent_id'] = $this->getOrCreateTradePartner($data['mbl']['oversea_agent_name']);
                if (!empty($data['mbl']['co_loader_name'])) $data['mbl']['co_loader_id'] = $this->getOrCreateTradePartner($data['mbl']['co_loader_name']);
                if (!empty($data['mbl']['carrier_name'])) $data['mbl']['carrier_id'] = $this->getOrCreateTradePartner($data['mbl']['carrier_name']);
                if (!empty($data['mbl']['acct_carrier_name'])) $data['mbl']['acct_carrier_id'] = $this->getOrCreateTradePartner($data['mbl']['acct_carrier_name']);
                if (!empty($data['mbl']['dm_notify_name'])) $data['mbl']['dm_notify_id'] = $this->getOrCreateTradePartner($data['mbl']['dm_notify_name']);
                if (!empty($data['mbl']['cy_location_name'])) $data['mbl']['cy_location_id'] = $this->getOrCreateTradePartner($data['mbl']['cy_location_name']);
                if (!empty($data['mbl']['cfs_location_name'])) $data['mbl']['cfs_location_id'] = $this->getOrCreateTradePartner($data['mbl']['cfs_location_name']);
                if (!empty($data['mbl']['business_referred_by_name'])) $data['mbl']['business_referred_by_id'] = $this->getOrCreateTradePartner($data['mbl']['business_referred_by_name']);
                
                // Export specific trade partners
                if (!empty($data['mbl']['return_location_name'])) $data['mbl']['return_location_id'] = $this->getOrCreateTradePartner($data['mbl']['return_location_name']);
                if (!empty($data['mbl']['empty_pickup_name'])) $data['mbl']['empty_pickup_id'] = $this->getOrCreateTradePartner($data['mbl']['empty_pickup_name']);
                if (!empty($data['mbl']['delivery_location_name'])) $data['mbl']['delivery_location_id'] = $this->getOrCreateTradePartner($data['mbl']['delivery_location_name']);

                // Vessel
                if (!empty($data['mbl']['vessel_name'])) {
                    $vName = trim(explode('/', $data['mbl']['vessel_name'])[0]);
                    $vessel = \App\Models\Vessel::where('name', 'like', '%' . $vName . '%')->first();
                    if (!$vessel) {
                        $vessel = \App\Models\Vessel::create(['name' => $vName]);
                    }
                    $data['mbl']['vessel_id'] = $vessel->id;
                }
            }

            if (isset($data['hbls']) && is_array($data['hbls'])) {
                foreach ($data['hbls'] as &$hbl) {
                    if (!empty($hbl['shipper_name'])) $hbl['shipper_id'] = $this->getOrCreateTradePartner($hbl['shipper_name']);
                    if (!empty($hbl['consignee_name'])) $hbl['consignee_id'] = $this->getOrCreateTradePartner($hbl['consignee_name']);
                    if (!empty($hbl['notify_party_name'])) $hbl['notify_id'] = $this->getOrCreateTradePartner($hbl['notify_party_name']);
                    if (!empty($hbl['customer_name'])) $hbl['customer_id'] = $this->getOrCreateTradePartner($hbl['customer_name']);
                    if (!empty($hbl['sales_person_name'])) $hbl['sales_person_id'] = $this->getOrCreateTradePartner($hbl['sales_person_name']);
                    if (!empty($hbl['forwarding_agent_name'])) $hbl['forwarding_agent_id'] = $this->getOrCreateTradePartner($hbl['forwarding_agent_name']);
                }
            }
            
            \Log::info("AI Extracted Ocean Export Data with IDs:", $data);

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Document processed successfully.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function extractAirImport(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,txt,csv,jpg,jpeg,png|max:10240', // max 10MB
        ]);

        try {
            $data = $this->aiService->extractData($request->file('document'), 'air_import');

            if (isset($data['mbl'])) {
                if (!empty($data['mbl']['pol_name'])) $data['mbl']['pol_id'] = $this->getOrCreatePort($data['mbl']['pol_name']);
                if (!empty($data['mbl']['pod_name'])) $data['mbl']['pod_id'] = $this->getOrCreatePort($data['mbl']['pod_name']);
                
                if (!empty($data['mbl']['forwarding_agent_name'])) $data['mbl']['forwarding_agent_id'] = $this->getOrCreateTradePartner($data['mbl']['forwarding_agent_name']);
                if (!empty($data['mbl']['op_name'])) $data['mbl']['op_id'] = $this->getUser($data['mbl']['op_name']);
                if (!empty($data['mbl']['dm_sales_person_name'])) $data['mbl']['dm_sales_person_id'] = $this->getUser($data['mbl']['dm_sales_person_name']);
                if (!empty($data['mbl']['oversea_agent_name'])) $data['mbl']['oversea_agent_id'] = $this->getOrCreateTradePartner($data['mbl']['oversea_agent_name']);
                if (!empty($data['mbl']['co_loader_name'])) $data['mbl']['co_loader_id'] = $this->getOrCreateTradePartner($data['mbl']['co_loader_name']);
                if (!empty($data['mbl']['carrier_name'])) $data['mbl']['carrier_id'] = $this->getOrCreateTradePartner($data['mbl']['carrier_name']);
            }

            if (isset($data['hbls']) && is_array($data['hbls'])) {
                foreach ($data['hbls'] as &$hbl) {
                    if (!empty($hbl['shipper_name'])) $hbl['shipper_id'] = $this->getOrCreateTradePartner($hbl['shipper_name']);
                    if (!empty($hbl['consignee_name'])) $hbl['consignee_id'] = $this->getOrCreateTradePartner($hbl['consignee_name']);
                    if (!empty($hbl['notify_party_name'])) $hbl['notify_id'] = $this->getOrCreateTradePartner($hbl['notify_party_name']);
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Document processed successfully.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function extractAirExport(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,txt,csv,jpg,jpeg,png|max:10240', // max 10MB
        ]);

        try {
            $data = $this->aiService->extractData($request->file('document'), 'air_export');

            if (isset($data['mbl'])) {
                if (!empty($data['mbl']['pol_name'])) $data['mbl']['pol_id'] = $this->getOrCreatePort($data['mbl']['pol_name']);
                if (!empty($data['mbl']['pod_name'])) $data['mbl']['pod_id'] = $this->getOrCreatePort($data['mbl']['pod_name']);
                
                if (!empty($data['mbl']['forwarding_agent_name'])) $data['mbl']['forwarding_agent_id'] = $this->getOrCreateTradePartner($data['mbl']['forwarding_agent_name']);
                if (!empty($data['mbl']['carrier_name'])) $data['mbl']['carrier_id'] = $this->getOrCreateTradePartner($data['mbl']['carrier_name']);
            }

            if (isset($data['hbls']) && is_array($data['hbls'])) {
                foreach ($data['hbls'] as &$hbl) {
                    if (!empty($hbl['shipper_name'])) $hbl['shipper_id'] = $this->getOrCreateTradePartner($hbl['shipper_name']);
                    if (!empty($hbl['consignee_name'])) $hbl['consignee_id'] = $this->getOrCreateTradePartner($hbl['consignee_name']);
                    if (!empty($hbl['notify_party_name'])) $hbl['notify_id'] = $this->getOrCreateTradePartner($hbl['notify_party_name']);
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Document processed successfully.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
