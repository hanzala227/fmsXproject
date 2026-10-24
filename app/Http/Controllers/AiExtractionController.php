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

    // Helper to get or create a TradePartner with Multi-Tenant Isolation
    private function getOrCreateTradePartner($name, $preferredType = 'AGENT')
    {
        if (empty($name) || !is_string($name)) return null;
        $name = trim($name);
        if ($name === '') return null;

        $companyId = auth()->user()->company_id ?? 1;

        // 1. Exact match within company
        $tp = \App\Models\TradePartner::where('company_id', $companyId)
            ->where(function($q) use ($name) {
                $q->where('name', $name)
                  ->orWhere('alias', $name)
                  ->orWhere('print_name', $name);
            })
            ->first();
        if ($tp) return $tp->id;

        // 2. Fuzzy match on significant token (>= 4 chars) within company
        $firstWord = trim(explode(' ', $name)[0]);
        if (strlen($firstWord) >= 4) {
            $tp = \App\Models\TradePartner::where('company_id', $companyId)
                ->where('name', 'like', '%' . $firstWord . '%')
                ->first();
            if ($tp) return $tp->id;
        }

        // 3. Fallback check across all partners if not found in company (shared global carriers/airlines)
        $globalTp = \App\Models\TradePartner::where(function($q) use ($name) {
                $q->where('name', $name)
                  ->orWhere('alias', $name);
            })
            ->first();
        if ($globalTp) return $globalTp->id;

        // 4. Create new partner safely under the logged in company
        $cleanLetters = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name));
        $prefix = substr($cleanLetters, 0, 3);
        if (strlen($prefix) < 2) $prefix = 'TP';

        $code = $prefix . rand(1000, 9999);
        $attempts = 0;
        while (\App\Models\TradePartner::withTrashed()->where('company_id', $companyId)->where('code', $code)->exists() && $attempts < 50) {
            $code = $prefix . rand(1000, 9999);
            $attempts++;
        }

        try {
            $tp = \App\Models\TradePartner::create([
                'company_id' => $companyId,
                'name' => $name,
                'code' => $code,
                'type' => $preferredType
            ]);
            return $tp->id;
        } catch (\Throwable $e) {
            $fallback = \App\Models\TradePartner::where('company_id', $companyId)->first() ?? \App\Models\TradePartner::first();
            return $fallback ? $fallback->id : null;
        }
    }

    // Helper to find a User within current company
    private function getUser($name)
    {
        if (empty($name) || !is_string($name)) return null;
        $name = trim($name);
        if ($name === '') return null;

        $companyId = auth()->user()->company_id ?? 1;

        $user = \App\Models\User::where('company_id', $companyId)->where('name', $name)->first();
        if ($user) return $user->id;

        $search = trim(explode(' ', $name)[0]);
        if (strlen($search) >= 2) {
            $user = \App\Models\User::where('company_id', $companyId)->where('name', 'like', '%' . $search . '%')->first();
            if ($user) return $user->id;
        }
        return null;
    }

    // Helper to find ContainerType with ISO Equipment Code Normalizer
    private function getContainerType($name)
    {
        if (empty($name)) return null;
        $raw = strtoupper(trim($name));
        
        // 1. Direct ISO dictionary lookup
        $isoMap = [
            '20GP' => '20GP', '20DC' => '20DC', '20DV' => '20GP', '20FT' => '20GP', '20 STANDARD' => '20GP', '22G1' => '20GP',
            '40GP' => '40GP', '40DC' => '40DC', '40DV' => '40GP', '40FT' => '40GP', '40 STANDARD' => '40GP', '42G1' => '40GP',
            '40HC' => '40HC', '40HQ' => '40HQ', '40 HIGH CUBE' => '40HQ', '40HIGHCUBE' => '40HQ', '45G1' => '40HC',
            '45HC' => '45HC', '45HQ' => '45HQ', '45 HIGH CUBE' => '45HQ',
            '20RF' => '20RF', '20 REEFER' => '20RF', '40RF' => '40RF', '40 REEFER' => '40RF',
            '20OT' => '20OT', '20 OPEN TOP' => '20OT', '40OT' => '40OT', '40 OPEN TOP' => '40OT',
            '20FR' => '20FR', '20 FLAT RACK' => '20FR', '40FR' => '40FR', '40 FLAT RACK' => '40FR',
            '20TK' => '20TK', '20 TANK' => '20TK', '40TK' => '40TK',
        ];

        // Clean input of quotes, dashes, spaces
        $normalized = str_replace(["'", '"', '-', '_', ' '], '', $raw);
        if (isset($isoMap[$normalized])) {
            $mappedCode = $isoMap[$normalized];
            $ct = \App\Models\ContainerType::where('code', $mappedCode)->orWhere('name', $mappedCode)->first();
            if ($ct) return $ct->id;
        }

        // Try exact code or name
        $ct = \App\Models\ContainerType::where('code', $raw)->orWhere('name', $raw)->first();
        if ($ct) return $ct->id;

        // Try contains like
        $ct = \App\Models\ContainerType::where('name', 'like', '%' . $raw . '%')
            ->orWhere('description', 'like', '%' . $raw . '%')
            ->first();
        return $ct ? $ct->id : null;
    }

    // Helper to find PackageUnit (UNECE Rec 21 Standard)
    private function getPackageUnitId($name)
    {
        if (empty($name)) return null;
        $raw = strtoupper(trim($name));

        $unitMap = [
            'CTN' => 'CTN', 'CARTON' => 'CTN', 'CARTONS' => 'CTN', 'BOX' => 'CTN', 'BOXES' => 'CTN',
            'PLT' => 'PLT', 'PALLET' => 'PLT', 'PALLETS' => 'PLT', 'SKID' => 'PLT', 'SKIDS' => 'PLT',
            'PCS' => 'PCS', 'PIECE' => 'PCS', 'PIECES' => 'PCS', 'UNIT' => 'PCS', 'UNITS' => 'PCS',
            'PKG' => 'PKG', 'PACKAGE' => 'PKG', 'PACKAGES' => 'PKG', 'PKGS' => 'PKG',
            'DRUM' => 'DRUM', 'DRUMS' => 'DRUM', 'BAG' => 'BAG', 'BAGS' => 'BAG',
        ];

        $target = $unitMap[$raw] ?? $raw;
        $unit = \App\Models\PackageUnit::where('code', $target)->orWhere('name', 'like', '%' . $target . '%')->first();
        if ($unit) return $unit->id;

        $first = \App\Models\PackageUnit::first();
        return $first ? $first->id : null;
    }

    // Helper to find Incoterm
    private function getIncoterm($name)
    {
        if (empty($name)) return null;
        $search = strtoupper(trim($name));
        return \App\Models\Incoterm::where('code', $search)->orWhere('name', 'like', '%' . $search . '%')->first();
    }

    private function getIncotermId($name)
    {
        $inco = $this->getIncoterm($name);
        return $inco ? $inco->id : null;
    }

    // Helper to sanitize & format IATA MAWB numbers (e.g. 176-12345675)
    private function sanitizeMawbNumber($raw)
    {
        if (empty($raw)) return null;
        $clean = preg_replace('/[^0-9]/', '', (string)$raw);
        if (strlen($clean) === 11) {
            return substr($clean, 0, 3) . '-' . substr($clean, 3);
        }
        return trim((string)$raw);
    }

    // Helper to resolve multiple or single uploaded files
    private function resolveUploadedFiles(Request $request): array
    {
        $request->validate([
            'documents'   => 'nullable|array|max:10',
            'documents.*' => 'file|mimes:pdf,txt,csv,jpg,jpeg,png,webp|max:15360',
            'document'    => 'nullable|file|mimes:pdf,txt,csv,jpg,jpeg,png,webp|max:15360',
        ]);

        $files = [];
        if ($request->hasFile('documents')) {
            $files = $request->file('documents');
        } elseif ($request->hasFile('document')) {
            $files = [$request->file('document')];
        }

        if (empty($files)) {
            throw new Exception("Please upload at least one valid document (PDF, JPG, PNG).");
        }

        return $files;
    }

    public function extractOceanImport(Request $request)
    {
        try {
            $files = $this->resolveUploadedFiles($request);

            if (config('frexo_ai.enabled', false)) {
                $pipeline = app(\App\Services\FrexoAi\Pipeline\StagedExtractionPipeline::class);
                $result = $pipeline->execute($files, 'ocean_import');
                return response()->json([
                    'success' => true,
                    'data' => $result->toArray(),
                    'message' => 'Document processed successfully via Staged Pipeline.'
                ]);
            }

            $data = $this->aiService->extractData($files, 'ocean_import');

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
                
                // Incoterm
                if (!empty($data['mbl']['incoterm'])) {
                    $inco = $this->getIncoterm($data['mbl']['incoterm']);
                    if ($inco) {
                        $data['mbl']['incoterm_id'] = $inco->id;
                        $data['mbl']['incoterm_code'] = $inco->code;
                        $data['mbl']['incoterm_name'] = $inco->code . ' - ' . $inco->name;
                    }
                }
                
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
                    if (!empty($hbl['incoterm'])) {
                        $inco = $this->getIncoterm($hbl['incoterm']);
                        if ($inco) {
                            $hbl['incoterm_id'] = $inco->id;
                            $hbl['incoterm_code'] = $inco->code;
                            $hbl['incoterm_name'] = $inco->code . ' - ' . $inco->name;
                            $hbl['incoterms_id'] = $inco->code;
                        }
                    }
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
                    if (!empty($container['package_unit'])) $container['package_unit_id'] = $this->getPackageUnitId($container['package_unit']);
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
        try {
            $files = $this->resolveUploadedFiles($request);

            if (config('frexo_ai.enabled', false)) {
                $pipeline = app(\App\Services\FrexoAi\Pipeline\StagedExtractionPipeline::class);
                $result = $pipeline->execute($files, 'ocean_export');
                return response()->json([
                    'success' => true,
                    'data' => $result->toArray(),
                    'message' => 'Document processed successfully via Staged Pipeline.'
                ]);
            }

            $data = $this->aiService->extractData($files, 'ocean_export');

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

                // Incoterm
                if (!empty($data['mbl']['incoterm'])) {
                    $inco = $this->getIncoterm($data['mbl']['incoterm']);
                    if ($inco) {
                        $data['mbl']['incoterm_id'] = $inco->id;
                        $data['mbl']['incoterm_code'] = $inco->code;
                        $data['mbl']['incoterm_name'] = $inco->code . ' - ' . $inco->name;
                    }
                }

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
                    if (!empty($hbl['incoterm'])) {
                        $inco = $this->getIncoterm($hbl['incoterm']);
                        if ($inco) {
                            $hbl['incoterm_id'] = $inco->id;
                            $hbl['incoterm_code'] = $inco->code;
                            $hbl['incoterm_name'] = $inco->code . ' - ' . $inco->name;
                            $hbl['incoterms_id'] = $inco->code;
                        }
                    }
                    
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
                    if (!empty($container['package_unit'])) $container['package_unit_id'] = $this->getPackageUnitId($container['package_unit']);
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
        try {
            $files = $this->resolveUploadedFiles($request);

            if (config('frexo_ai.enabled', false)) {
                $pipeline = app(\App\Services\FrexoAi\Pipeline\StagedExtractionPipeline::class);
                $result = $pipeline->execute($files, 'air_import');
                return response()->json([
                    'success' => true,
                    'data' => $result->toArray(),
                    'message' => 'Document processed successfully via Staged Pipeline.'
                ]);
            }

            $data = $this->aiService->extractData($files, 'air_import');

            if (isset($data['mbl'])) {
                // Normalize MAWB / MBL number & IATA 11-digit format (xxx-xxxxxxxx)
                if (empty($data['mbl']['mawb_no']) && !empty($data['mbl']['mbl_no'])) {
                    $data['mbl']['mawb_no'] = $data['mbl']['mbl_no'];
                } elseif (empty($data['mbl']['mbl_no']) && !empty($data['mbl']['mawb_no'])) {
                    $data['mbl']['mbl_no'] = $data['mbl']['mawb_no'];
                }
                if (!empty($data['mbl']['mawb_no'])) {
                    $data['mbl']['mawb_no'] = $this->sanitizeMawbNumber($data['mbl']['mawb_no']);
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
                    $inco = $this->getIncoterm($incoRaw);
                    if ($inco) {
                        $data['mbl']['incoterm_id'] = $inco->id;
                        $data['mbl']['incoterm_code'] = $inco->code;
                        $data['mbl']['incoterm_name'] = $inco->code . ' - ' . $inco->name;
                    }
                }

                // Normalize IATA Air Freight weights & packages on MBL
                if (empty($data['mbl']['gross_weight']) && !empty($data['mbl']['weight'])) {
                    $data['mbl']['gross_weight'] = $data['mbl']['weight'];
                } elseif (empty($data['mbl']['weight']) && !empty($data['mbl']['gross_weight'])) {
                    $data['mbl']['weight'] = $data['mbl']['gross_weight'];
                }

                if (!empty($data['mbl']['volume'])) {
                    $cbm = (float)$data['mbl']['volume'];
                    // IATA Standard: 1 CBM = 166.67 KG
                    $calcVolW = round($cbm * 166.67, 2);
                    if (empty($data['mbl']['volume_weight']) || (float)$data['mbl']['volume_weight'] == $cbm) {
                        $data['mbl']['volume_weight'] = $calcVolW;
                    }
                }

                $gross = (float)($data['mbl']['gross_weight'] ?? ($data['mbl']['weight'] ?? 0));
                $volW = (float)($data['mbl']['volume_weight'] ?? 0);
                if (empty($data['mbl']['chargeable_weight'])) {
                    $data['mbl']['chargeable_weight'] = max($gross, $volW);
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

                    // Normalize IATA Air Freight weights on HBL
                    if (empty($hbl['gross_weight']) && !empty($hbl['weight'])) {
                        $hbl['gross_weight'] = $hbl['weight'];
                    } elseif (empty($hbl['weight']) && !empty($hbl['gross_weight'])) {
                        $hbl['weight'] = $hbl['gross_weight'];
                    }

                    if (!empty($hbl['volume'])) {
                        $hCbm = (float)$hbl['volume'];
                        $hCalcVolW = round($hCbm * 166.67, 2);
                        if (empty($hbl['volume_weight']) || (float)$hbl['volume_weight'] == $hCbm) {
                            $hbl['volume_weight'] = $hCalcVolW;
                        }
                    }

                    $hGross = (float)($hbl['gross_weight'] ?? ($hbl['weight'] ?? 0));
                    $hVolW = (float)($hbl['volume_weight'] ?? 0);
                    if (empty($hbl['chargeable_weight'])) {
                        $hbl['chargeable_weight'] = max($hGross, $hVolW);
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
        try {
            $files = $this->resolveUploadedFiles($request);

            if (config('frexo_ai.enabled', false)) {
                $pipeline = app(\App\Services\FrexoAi\Pipeline\StagedExtractionPipeline::class);
                $result = $pipeline->execute($files, 'air_export');
                return response()->json([
                    'success' => true,
                    'data' => $result->toArray(),
                    'message' => 'Document processed successfully via Staged Pipeline.'
                ]);
            }

            $data = $this->aiService->extractData($files, 'air_export');

            if (isset($data['mbl'])) {
                // Normalize MAWB / MBL number & IATA 11-digit format (xxx-xxxxxxxx)
                if (empty($data['mbl']['mawb_no']) && !empty($data['mbl']['mbl_no'])) {
                    $data['mbl']['mawb_no'] = $data['mbl']['mbl_no'];
                } elseif (empty($data['mbl']['mbl_no']) && !empty($data['mbl']['mawb_no'])) {
                    $data['mbl']['mbl_no'] = $data['mbl']['mawb_no'];
                }
                if (!empty($data['mbl']['mawb_no'])) {
                    $data['mbl']['mawb_no'] = $this->sanitizeMawbNumber($data['mbl']['mawb_no']);
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
                    $inco = $this->getIncoterm($incoRaw);
                    if ($inco) {
                        $data['mbl']['incoterm_id'] = $inco->id;
                        $data['mbl']['incoterm_code'] = $inco->code;
                        $data['mbl']['incoterm_name'] = $inco->code . ' - ' . $inco->name;
                    }
                }

                // Normalize IATA Air Freight weights & packages on MBL
                if (empty($data['mbl']['gross_weight']) && !empty($data['mbl']['weight'])) {
                    $data['mbl']['gross_weight'] = $data['mbl']['weight'];
                } elseif (empty($data['mbl']['weight']) && !empty($data['mbl']['gross_weight'])) {
                    $data['mbl']['weight'] = $data['mbl']['gross_weight'];
                }

                if (!empty($data['mbl']['volume'])) {
                    $cbm = (float)$data['mbl']['volume'];
                    // IATA Standard: 1 CBM = 166.67 KG
                    $calcVolW = round($cbm * 166.67, 2);
                    if (empty($data['mbl']['volume_weight']) || (float)$data['mbl']['volume_weight'] == $cbm) {
                        $data['mbl']['volume_weight'] = $calcVolW;
                    }
                }

                $gross = (float)($data['mbl']['gross_weight'] ?? ($data['mbl']['weight'] ?? 0));
                $volW = (float)($data['mbl']['volume_weight'] ?? 0);
                if (empty($data['mbl']['chargeable_weight'])) {
                    $data['mbl']['chargeable_weight'] = max($gross, $volW);
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

                    // Normalize IATA Air Freight weights on HBL
                    if (empty($hbl['gross_weight']) && !empty($hbl['weight'])) {
                        $hbl['gross_weight'] = $hbl['weight'];
                    } elseif (empty($hbl['weight']) && !empty($hbl['gross_weight'])) {
                        $hbl['weight'] = $hbl['gross_weight'];
                    }

                    if (!empty($hbl['volume'])) {
                        $hCbm = (float)$hbl['volume'];
                        $hCalcVolW = round($hCbm * 166.67, 2);
                        if (empty($hbl['volume_weight']) || (float)$hbl['volume_weight'] == $hCbm) {
                            $hbl['volume_weight'] = $hCalcVolW;
                        }
                    }

                    $hGross = (float)($hbl['gross_weight'] ?? ($hbl['weight'] ?? 0));
                    $hVolW = (float)($hbl['volume_weight'] ?? 0);
                    if (empty($hbl['chargeable_weight'])) {
                        $hbl['chargeable_weight'] = max($hGross, $hVolW);
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

    /**
     * Apply extraction fields with tenant isolation, revalidation, and audit trail.
     */
    public function apply(Request $request, \App\Services\FrexoAi\Apply\ExtractionApplyService $applyService)
    {
        try {
            $payload = $request->validate([
                'run_uuid' => 'required|string',
                'idempotency_token' => 'required|string',
                'module' => 'required|string',
                'expected_form_version' => 'nullable|integer',
                'current_form_version' => 'nullable|integer',
                'existing_values' => 'nullable|array',
                'accepted_fields' => 'nullable|array',
                'rejected_fields' => 'nullable|array',
            ]);

            $result = $applyService->apply($payload);

            return response()->json($result);
        } catch (\Throwable $e) {
            $statusCode = str_contains($e->getMessage(), 'STALE_FORM_STATE') ? 409 : 422;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Search and match entity with candidate list; never auto-creates records.
     */
    public function matchEntity(Request $request, \App\Services\FrexoAi\Matching\TenantEntityMatcher $matcher)
    {
        try {
            $entityType = $request->input('entity_type', 'trade_partner');
            $query = $request->input('query', '');

            if ($entityType === 'port') {
                $result = $matcher->matchPort($query);
            } else {
                $type = $request->input('partner_type');
                $result = $matcher->matchTradePartner($query, null, $type);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
