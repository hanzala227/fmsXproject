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
        if (empty($name) || !is_string($name)) return null;

        $name = trim($name);
        if ($name === '') return null;

        // 1. Try exact code or exact name match first
        $exactPort = \App\Models\Port::where('code', $name)
            ->orWhere('name', $name)
            ->first();
        if ($exactPort) return $exactPort->id;

        // 2. Extract potential code tokens (e.g., 3 to 6 uppercase letters/digits)
        // From strings like "BDDAC = Dhaka, Bangladesh", "DAC / DHAKA", "Dhaka (DAC)", "USLAX - Los Angeles"
        preg_match_all('/\b[A-Za-z0-9]{3,6}\b/', $name, $matches);
        $candidateTokens = !empty($matches[0]) ? array_unique($matches[0]) : [];

        foreach ($candidateTokens as $token) {
            $upperToken = strtoupper($token);
            $portByCode = \App\Models\Port::where('code', $upperToken)->first();
            if ($portByCode) {
                return $portByCode->id;
            }
        }

        // 3. Split by delimiters (=, -, /, ,, (, ), ;, |) into phrases
        $segments = preg_split('/[=\-\/,\(\);|:]+/', $name);
        $cleanSegments = [];
        foreach ($segments as $seg) {
            $seg = trim($seg);
            if (strlen($seg) >= 2) {
                $cleanSegments[] = $seg;
            }
        }

        // Try matching each segment as exact code or exact name
        foreach ($cleanSegments as $seg) {
            $upperSeg = strtoupper($seg);
            $port = \App\Models\Port::where('code', $upperSeg)
                ->orWhere('name', $seg)
                ->first();
            if ($port) {
                return $port->id;
            }
        }

        // Try fuzzy LIKE matching on clean segments
        foreach ($cleanSegments as $seg) {
            if (strlen($seg) >= 3) {
                $port = \App\Models\Port::where('name', 'like', '%' . $seg . '%')
                    ->orWhere('city', 'like', '%' . $seg . '%')
                    ->first();
                if ($port) {
                    return $port->id;
                }
            }
        }

        // Also check if any existing port's code is contained within the string
        // E.g. $name has "BDDAC = Dhaka", and a port has code "BDDAC"
        try {
            $portContained = \App\Models\Port::whereRaw('? LIKE CONCAT("%", code, "%")', [$name])->first();
            if ($portContained) {
                return $portContained->id;
            }
        } catch (\Throwable $e) {
            // ignore raw query fallback if unsupported
        }

        // 4. Port does not exist - create safely without duplicate key violation
        // Pick best candidate code or derive one
        $derivedCode = null;
        foreach ($candidateTokens as $token) {
            $t = strtoupper($token);
            if (strlen($t) >= 3 && strlen($t) <= 5 && ctype_alnum($t)) {
                $derivedCode = $t;
                break;
            }
        }

        if (!$derivedCode) {
            $cleanLetters = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name));
            $derivedCode = substr($cleanLetters, 0, 5);
        }
        if (strlen($derivedCode) < 3) {
            $derivedCode = 'PRT' . rand(10, 99);
        }

        // Check if $derivedCode ALREADY exists in DB (even if soft-deleted)
        $existingByCode = \App\Models\Port::withTrashed()->where('code', $derivedCode)->first();
        if ($existingByCode) {
            if ($existingByCode->trashed()) {
                $existingByCode->restore();
            }
            return $existingByCode->id;
        }

        // Guarantee uniqueness for the code
        $finalCode = substr($derivedCode, 0, 5);
        $attempts = 1;
        while (\App\Models\Port::withTrashed()->where('code', $finalCode)->exists() && $attempts < 100) {
            $finalCode = substr($derivedCode, 0, 3) . str_pad($attempts, 2, '0', STR_PAD_LEFT);
            $attempts++;
        }

        $defaultCountry = \App\Models\Country::first();
        $countryId = $defaultCountry ? $defaultCountry->id : 1;

        // Clean up port name for display
        $cleanName = !empty($cleanSegments[1]) ? $cleanSegments[1] : (!empty($cleanSegments[0]) ? $cleanSegments[0] : $name);

        try {
            $newPort = \App\Models\Port::create([
                'name' => $cleanName,
                'code' => $finalCode,
                'country_id' => $countryId,
                'type' => 'AIR'
            ]);
            return $newPort->id;
        } catch (\Throwable $e) {
            // Safety fallback: if creation fails, find by code or return first port
            $fallback = \App\Models\Port::where('code', $finalCode)->first() ?? \App\Models\Port::first();
            return $fallback ? $fallback->id : null;
        }
    }

    // Helper to get or create a TradePartner
    private function getOrCreateTradePartner($name)
    {
        if (empty($name) || !is_string($name)) return null;
        $name = trim($name);
        if ($name === '') return null;

        // 1. Exact match on name or alias
        $tp = \App\Models\TradePartner::where('name', $name)
            ->orWhere('alias', $name)
            ->orWhere('print_name', $name)
            ->first();
        if ($tp) return $tp->id;

        // 2. Try match on leading company name token (e.g. "Emirates" from "Emirates Airline Cargo")
        $firstWord = trim(explode(' ', $name)[0]);
        if (strlen($firstWord) >= 3) {
            $tp = \App\Models\TradePartner::where('name', 'like', '%' . $firstWord . '%')->first();
            if ($tp) return $tp->id;
        }

        // 3. Create new with unique code
        $cleanLetters = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name));
        $prefix = substr($cleanLetters, 0, 3);
        if (strlen($prefix) < 2) $prefix = 'TP';

        $code = $prefix . rand(1000, 9999);
        $attempts = 0;
        while (\App\Models\TradePartner::withTrashed()->where('code', $code)->exists() && $attempts < 50) {
            $code = $prefix . rand(1000, 9999);
            $attempts++;
        }

        try {
            $tp = \App\Models\TradePartner::create([
                'name' => $name,
                'code' => $code,
                'type' => 'AGENT'
            ]);
            return $tp->id;
        } catch (\Throwable $e) {
            $fallback = \App\Models\TradePartner::where('code', $code)->first() ?? \App\Models\TradePartner::first();
            return $fallback ? $fallback->id : null;
        }
    }

    // Helper to find a User
    private function getUser($name)
    {
        if (empty($name) || !is_string($name)) return null;
        $name = trim($name);
        if ($name === '') return null;

        $user = \App\Models\User::where('name', $name)->first();
        if ($user) return $user->id;

        $search = trim(explode(' ', $name)[0]);
        if (strlen($search) >= 2) {
            $user = \App\Models\User::where('name', 'like', '%' . $search . '%')->first();
            if ($user) return $user->id;
        }
        return null;
    }

    // Helper to find ContainerType
    private function getContainerType($name)
    {
        if (empty($name)) return null;
        $search = trim($name);
        $ct = \App\Models\ContainerType::where('name', 'like', '%' . $search . '%')->first();
        return $ct ? $ct->id : null;
    }

    // Helper to find Incoterm
    private function getIncotermId($name)
    {
        if (empty($name)) return null;
        $search = strtoupper(trim($name));
        $inco = \App\Models\Incoterm::where('code', $search)->orWhere('name', 'like', '%' . $search . '%')->first();
        return $inco ? $inco->id : null;
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

            if (isset($data['routes']) && is_array($data['routes'])) {
                foreach ($data['routes'] as &$route) {
                    if (!empty($route['pol_name'])) $route['pol_id'] = $this->getOrCreatePort($route['pol_name']);
                    if (!empty($route['pod_name'])) $route['pod_id'] = $this->getOrCreatePort($route['pod_name']);
                }
            }

            if (isset($data['containers']) && is_array($data['containers'])) {
                foreach ($data['containers'] as &$container) {
                    if (!empty($container['container_type'])) $container['container_type_id'] = $this->getContainerType($container['container_type']);
                }
            }
            
            \Log::info("AI Extracted Data with IDs:", $data);

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Document processed successfully.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 200);
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
                    
                    if (!empty($hbl['pol_name'])) $hbl['pol_id'] = $this->getOrCreatePort($hbl['pol_name']);
                    if (!empty($hbl['pod_name'])) $hbl['pod_id'] = $this->getOrCreatePort($hbl['pod_name']);
                    if (!empty($hbl['receipt_name'])) $hbl['receipt_id'] = $this->getOrCreatePort($hbl['receipt_name']);
                    if (!empty($hbl['del_name'])) $hbl['del_id'] = $this->getOrCreatePort($hbl['del_name']);
                    if (!empty($hbl['fdest_name'])) $hbl['fdest_id'] = $this->getOrCreatePort($hbl['fdest_name']);
                }
            }

            if (isset($data['routes']) && is_array($data['routes'])) {
                foreach ($data['routes'] as &$route) {
                    if (!empty($route['pol_name'])) $route['pol_id'] = $this->getOrCreatePort($route['pol_name']);
                    if (!empty($route['pod_name'])) $route['pod_id'] = $this->getOrCreatePort($route['pod_name']);
                }
            }

            if (isset($data['containers']) && is_array($data['containers'])) {
                foreach ($data['containers'] as &$container) {
                    if (!empty($container['container_type'])) $container['container_type_id'] = $this->getContainerType($container['container_type']);
                }
            }
            
            \Log::info("AI Extracted Ocean Export Data with IDs:", $data);

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Document processed successfully.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 200);
        }
    }

    public function extractAirImport(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,txt,csv,jpg,jpeg,png,webp|max:10240', // max 10MB
        ]);

        try {
            $data = $this->aiService->extractData($request->file('document'), 'air_import');

            if (isset($data['mbl'])) {
                // Normalize MAWB / MBL number
                if (empty($data['mbl']['mawb_no']) && !empty($data['mbl']['mbl_no'])) {
                    $data['mbl']['mawb_no'] = $data['mbl']['mbl_no'];
                } elseif (empty($data['mbl']['mbl_no']) && !empty($data['mbl']['mawb_no'])) {
                    $data['mbl']['mbl_no'] = $data['mbl']['mawb_no'];
                }

                // Departure Port
                $polName = $data['mbl']['departure_name'] ?? $data['mbl']['pol_name'] ?? null;
                if (!empty($polName)) {
                    $polId = $this->getOrCreatePort($polName);
                    $data['mbl']['pol_id'] = $polId;
                    $data['mbl']['departure_id'] = $polId;
                    $data['mbl']['dep_port_id'] = $polId;
                    $data['mbl']['pol_name'] = $polName;
                    $data['mbl']['departure_name'] = $polName;
                }

                // Destination Port
                $podName = $data['mbl']['destination_name'] ?? $data['mbl']['pod_name'] ?? null;
                if (!empty($podName)) {
                    $podId = $this->getOrCreatePort($podName);
                    $data['mbl']['pod_id'] = $podId;
                    $data['mbl']['destination_id'] = $podId;
                    $data['mbl']['dst_port_id'] = $podId;
                    $data['mbl']['freight_location_id'] = $podId;
                    $data['mbl']['pod_name'] = $podName;
                    $data['mbl']['destination_name'] = $podName;
                }

                // Carrier
                if (!empty($data['mbl']['carrier_name'])) {
                    $carrierId = $this->getOrCreateTradePartner($data['mbl']['carrier_name']);
                    $data['mbl']['carrier_id'] = $carrierId;
                }

                // Forwarding Agent / Co-loader
                $fwdName = $data['mbl']['forwarding_agent_name'] ?? $data['mbl']['co_loader_name'] ?? null;
                if (!empty($fwdName)) {
                    $fwdId = $this->getOrCreateTradePartner($fwdName);
                    $data['mbl']['forwarding_agent_id'] = $fwdId;
                    $data['mbl']['co_loader_id'] = $fwdId;
                    $data['mbl']['forwarding_agent_name'] = $fwdName;
                    $data['mbl']['co_loader_name'] = $fwdName;
                }

                // Oversea Agent
                if (!empty($data['mbl']['oversea_agent_name'])) {
                    $data['mbl']['oversea_agent_id'] = $this->getOrCreateTradePartner($data['mbl']['oversea_agent_name']);
                }

                // Master Shipper, Consignee, Notify
                if (!empty($data['mbl']['shipper_name'])) {
                    $data['mbl']['shipper_id'] = $this->getOrCreateTradePartner($data['mbl']['shipper_name']);
                }
                if (!empty($data['mbl']['consignee_name'])) {
                    $data['mbl']['consignee_id'] = $this->getOrCreateTradePartner($data['mbl']['consignee_name']);
                }
                if (!empty($data['mbl']['notify_party_name'])) {
                    $data['mbl']['notify_id'] = $this->getOrCreateTradePartner($data['mbl']['notify_party_name']);
                }

                // OP & Sales
                if (!empty($data['mbl']['op_name'])) {
                    $data['mbl']['op_id'] = $this->getUser($data['mbl']['op_name']);
                }
                if (!empty($data['mbl']['dm_sales_person_name'])) {
                    $data['mbl']['dm_sales_person_id'] = $this->getUser($data['mbl']['dm_sales_person_name']);
                }

                // Incoterms
                $incoRaw = $data['mbl']['incoterms'] ?? $data['mbl']['incoterm'] ?? null;
                if (!empty($incoRaw)) {
                    $incoId = $this->getIncotermId($incoRaw);
                    if ($incoId) {
                        $data['mbl']['incoterm_id'] = $incoId;
                    }
                }

                // Normalize weights & packages on MBL
                if (empty($data['mbl']['gross_weight']) && !empty($data['mbl']['weight'])) {
                    $data['mbl']['gross_weight'] = $data['mbl']['weight'];
                } elseif (empty($data['mbl']['weight']) && !empty($data['mbl']['gross_weight'])) {
                    $data['mbl']['weight'] = $data['mbl']['gross_weight'];
                }
                if (empty($data['mbl']['volume']) && !empty($data['mbl']['volume_weight'])) {
                    $data['mbl']['volume'] = $data['mbl']['volume_weight'];
                } elseif (empty($data['mbl']['volume_weight']) && !empty($data['mbl']['volume'])) {
                    $data['mbl']['volume_weight'] = $data['mbl']['volume'];
                }
                if (empty($data['mbl']['pieces']) && !empty($data['mbl']['pkg_qty'])) {
                    $data['mbl']['pieces'] = $data['mbl']['pkg_qty'];
                } elseif (empty($data['mbl']['pkg_qty']) && !empty($data['mbl']['pieces'])) {
                    $data['mbl']['pkg_qty'] = $data['mbl']['pieces'];
                }
            }

            // Sync Flights & Routes
            $flightList = $data['flights'] ?? $data['routes'] ?? [];
            if (is_array($flightList) && count($flightList) > 0) {
                foreach ($flightList as &$flight) {
                    $pol = $flight['departure_name'] ?? $flight['pol_name'] ?? null;
                    if (!empty($pol)) {
                        $pId = $this->getOrCreatePort($pol);
                        $flight['pol_id'] = $pId;
                        $flight['departure_id'] = $pId;
                        $flight['pol_name'] = $pol;
                        $flight['departure_name'] = $pol;
                    }
                    $pod = $flight['destination_name'] ?? $flight['pod_name'] ?? null;
                    if (!empty($pod)) {
                        $pId = $this->getOrCreatePort($pod);
                        $flight['pod_id'] = $pId;
                        $flight['destination_id'] = $pId;
                        $flight['pod_name'] = $pod;
                        $flight['destination_name'] = $pod;
                    }
                    if (!empty($flight['carrier_name'])) {
                        $flight['carrier_id'] = $this->getOrCreateTradePartner($flight['carrier_name']);
                    }
                }
                unset($flight);
                $data['flights'] = $flightList;
                $data['routes'] = $flightList;
            }

            // HBLs
            if (isset($data['hbls']) && is_array($data['hbls'])) {
                foreach ($data['hbls'] as &$hbl) {
                    // Normalize HAWB / HBL number
                    if (empty($hbl['hawb_no']) && !empty($hbl['hbl_no'])) {
                        $hbl['hawb_no'] = $hbl['hbl_no'];
                    } elseif (empty($hbl['hbl_no']) && !empty($hbl['hawb_no'])) {
                        $hbl['hbl_no'] = $hbl['hawb_no'];
                    }

                    // Normalize weights
                    if (empty($hbl['gross_weight']) && !empty($hbl['weight'])) {
                        $hbl['gross_weight'] = $hbl['weight'];
                    } elseif (empty($hbl['weight']) && !empty($hbl['gross_weight'])) {
                        $hbl['weight'] = $hbl['gross_weight'];
                    }
                    if (empty($hbl['volume_weight']) && !empty($hbl['volume'])) {
                        $hbl['volume_weight'] = $hbl['volume'];
                    } elseif (empty($hbl['volume']) && !empty($hbl['volume_weight'])) {
                        $hbl['volume'] = $hbl['volume_weight'];
                    }

                    if (!empty($hbl['shipper_name'])) $hbl['shipper_id'] = $this->getOrCreateTradePartner($hbl['shipper_name']);
                    if (!empty($hbl['consignee_name'])) $hbl['consignee_id'] = $this->getOrCreateTradePartner($hbl['consignee_name']);
                    if (!empty($hbl['notify_party_name'])) $hbl['notify_id'] = $this->getOrCreateTradePartner($hbl['notify_party_name']);

                    $hDep = $hbl['departure_name'] ?? $hbl['pol_name'] ?? null;
                    if (!empty($hDep)) {
                        $hbl['departure_id'] = $this->getOrCreatePort($hDep);
                        $hbl['departure_name'] = $hDep;
                    }
                    $hDst = $hbl['destination_name'] ?? $hbl['pod_name'] ?? null;
                    if (!empty($hDst)) {
                        $hbl['destination_id'] = $this->getOrCreatePort($hDst);
                        $hbl['destination_name'] = $hDst;
                    }
                }
                unset($hbl);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Document processed successfully.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 200);
        }
    }

    public function extractAirExport(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,txt,csv,jpg,jpeg,png,webp|max:10240', // max 10MB
        ]);

        try {
            $data = $this->aiService->extractData($request->file('document'), 'air_export');

            if (isset($data['mbl'])) {
                // Normalize MAWB / MBL number
                if (empty($data['mbl']['mawb_no']) && !empty($data['mbl']['mbl_no'])) {
                    $data['mbl']['mawb_no'] = $data['mbl']['mbl_no'];
                } elseif (empty($data['mbl']['mbl_no']) && !empty($data['mbl']['mawb_no'])) {
                    $data['mbl']['mbl_no'] = $data['mbl']['mawb_no'];
                }

                // Departure Port
                $polName = $data['mbl']['departure_name'] ?? $data['mbl']['pol_name'] ?? null;
                if (!empty($polName)) {
                    $polId = $this->getOrCreatePort($polName);
                    $data['mbl']['pol_id'] = $polId;
                    $data['mbl']['departure_id'] = $polId;
                    $data['mbl']['dep_port_id'] = $polId;
                    $data['mbl']['pol_name'] = $polName;
                    $data['mbl']['departure_name'] = $polName;
                }

                // Destination Port
                $podName = $data['mbl']['destination_name'] ?? $data['mbl']['pod_name'] ?? null;
                if (!empty($podName)) {
                    $podId = $this->getOrCreatePort($podName);
                    $data['mbl']['pod_id'] = $podId;
                    $data['mbl']['destination_id'] = $podId;
                    $data['mbl']['dst_port_id'] = $podId;
                    $data['mbl']['pod_name'] = $podName;
                    $data['mbl']['destination_name'] = $podName;
                }

                // Carrier
                if (!empty($data['mbl']['carrier_name'])) {
                    $carrierId = $this->getOrCreateTradePartner($data['mbl']['carrier_name']);
                    $data['mbl']['carrier_id'] = $carrierId;
                }

                // Forwarding Agent / Co-loader
                $fwdName = $data['mbl']['forwarding_agent_name'] ?? $data['mbl']['co_loader_name'] ?? null;
                if (!empty($fwdName)) {
                    $fwdId = $this->getOrCreateTradePartner($fwdName);
                    $data['mbl']['forwarding_agent_id'] = $fwdId;
                    $data['mbl']['co_loader_id'] = $fwdId;
                    $data['mbl']['forwarding_agent_name'] = $fwdName;
                    $data['mbl']['co_loader_name'] = $fwdName;
                }

                // Oversea Agent
                if (!empty($data['mbl']['oversea_agent_name'])) {
                    $data['mbl']['oversea_agent_id'] = $this->getOrCreateTradePartner($data['mbl']['oversea_agent_name']);
                }

                // Master Shipper, Consignee, Notify
                if (!empty($data['mbl']['shipper_name'])) {
                    $data['mbl']['shipper_id'] = $this->getOrCreateTradePartner($data['mbl']['shipper_name']);
                }
                if (!empty($data['mbl']['consignee_name'])) {
                    $data['mbl']['consignee_id'] = $this->getOrCreateTradePartner($data['mbl']['consignee_name']);
                }
                if (!empty($data['mbl']['notify_party_name'])) {
                    $data['mbl']['notify_id'] = $this->getOrCreateTradePartner($data['mbl']['notify_party_name']);
                }
                if (!empty($data['mbl']['actual_shipper_name'])) {
                    $data['mbl']['actual_shipper_id'] = $this->getOrCreateTradePartner($data['mbl']['actual_shipper_name']);
                }

                // Operator & Sales
                if (!empty($data['mbl']['op_name'])) {
                    $data['mbl']['op_id'] = $this->getUser($data['mbl']['op_name']);
                }
                if (!empty($data['mbl']['dm_sales_person_name'])) {
                    $data['mbl']['sales_person_id'] = $this->getUser($data['mbl']['dm_sales_person_name']);
                }

                // Incoterms
                $incoRaw = $data['mbl']['incoterms'] ?? $data['mbl']['incoterm'] ?? null;
                if (!empty($incoRaw)) {
                    $incoId = $this->getIncotermId($incoRaw);
                    if ($incoId) {
                        $data['mbl']['incoterm_id'] = $incoId;
                    }
                }

                // Normalize weights & packages on MBL
                if (empty($data['mbl']['gross_weight']) && !empty($data['mbl']['weight'])) {
                    $data['mbl']['gross_weight'] = $data['mbl']['weight'];
                } elseif (empty($data['mbl']['weight']) && !empty($data['mbl']['gross_weight'])) {
                    $data['mbl']['weight'] = $data['mbl']['gross_weight'];
                }
                if (empty($data['mbl']['volume']) && !empty($data['mbl']['volume_weight'])) {
                    $data['mbl']['volume'] = $data['mbl']['volume_weight'];
                } elseif (empty($data['mbl']['volume_weight']) && !empty($data['mbl']['volume'])) {
                    $data['mbl']['volume_weight'] = $data['mbl']['volume'];
                }
                if (empty($data['mbl']['pieces']) && !empty($data['mbl']['pkg_qty'])) {
                    $data['mbl']['pieces'] = $data['mbl']['pkg_qty'];
                } elseif (empty($data['mbl']['pkg_qty']) && !empty($data['mbl']['pieces'])) {
                    $data['mbl']['pkg_qty'] = $data['mbl']['pieces'];
                }
            }

            // Sync Flights & Routes
            $flightList = $data['flights'] ?? $data['routes'] ?? [];
            if (is_array($flightList) && count($flightList) > 0) {
                foreach ($flightList as &$flight) {
                    $pol = $flight['departure_name'] ?? $flight['pol_name'] ?? null;
                    if (!empty($pol)) {
                        $pId = $this->getOrCreatePort($pol);
                        $flight['pol_id'] = $pId;
                        $flight['departure_id'] = $pId;
                        $flight['pol_name'] = $pol;
                        $flight['departure_name'] = $pol;
                    }
                    $pod = $flight['destination_name'] ?? $flight['pod_name'] ?? null;
                    if (!empty($pod)) {
                        $pId = $this->getOrCreatePort($pod);
                        $flight['pod_id'] = $pId;
                        $flight['destination_id'] = $pId;
                        $flight['pod_name'] = $pod;
                        $flight['destination_name'] = $pod;
                    }
                    if (!empty($flight['carrier_name'])) {
                        $flight['carrier_id'] = $this->getOrCreateTradePartner($flight['carrier_name']);
                    }
                }
                unset($flight);
                $data['flights'] = $flightList;
                $data['routes'] = $flightList;
            }

            // HBLs
            if (isset($data['hbls']) && is_array($data['hbls'])) {
                foreach ($data['hbls'] as &$hbl) {
                    // Normalize HAWB / HBL number
                    if (empty($hbl['hawb_no']) && !empty($hbl['hbl_no'])) {
                        $hbl['hawb_no'] = $hbl['hbl_no'];
                    } elseif (empty($hbl['hbl_no']) && !empty($hbl['hawb_no'])) {
                        $hbl['hbl_no'] = $hbl['hawb_no'];
                    }

                    // Normalize weights
                    if (empty($hbl['gross_weight']) && !empty($hbl['weight'])) {
                        $hbl['gross_weight'] = $hbl['weight'];
                    } elseif (empty($hbl['weight']) && !empty($hbl['gross_weight'])) {
                        $hbl['weight'] = $hbl['gross_weight'];
                    }
                    if (empty($hbl['volume_weight']) && !empty($hbl['volume'])) {
                        $hbl['volume_weight'] = $hbl['volume'];
                    } elseif (empty($hbl['volume']) && !empty($hbl['volume_weight'])) {
                        $hbl['volume'] = $hbl['volume_weight'];
                    }

                    if (!empty($hbl['shipper_name'])) $hbl['shipper_id'] = $this->getOrCreateTradePartner($hbl['shipper_name']);
                    if (!empty($hbl['consignee_name'])) $hbl['consignee_id'] = $this->getOrCreateTradePartner($hbl['consignee_name']);
                    if (!empty($hbl['notify_party_name'])) $hbl['notify_id'] = $this->getOrCreateTradePartner($hbl['notify_party_name']);

                    $hDep = $hbl['departure_name'] ?? $hbl['pol_name'] ?? null;
                    if (!empty($hDep)) {
                        $hbl['departure_id'] = $this->getOrCreatePort($hDep);
                        $hbl['departure_name'] = $hDep;
                    }
                    $hDst = $hbl['destination_name'] ?? $hbl['pod_name'] ?? null;
                    if (!empty($hDst)) {
                        $hbl['destination_id'] = $this->getOrCreatePort($hDst);
                        $hbl['destination_name'] = $hDst;
                    }
                }
                unset($hbl);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Document processed successfully.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 200);
        }
    }
}
