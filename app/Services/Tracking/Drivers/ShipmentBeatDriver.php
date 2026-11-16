<?php

namespace App\Services\Tracking\Drivers;

use App\Models\CarrierApiConfig;
use App\Services\Tracking\Contracts\CarrierTrackingInterface;
use App\Services\Tracking\DTO\TrackingEventDTO;
use App\Services\Tracking\DTO\TrackingResultDTO;
use Exception;
use Illuminate\Support\Facades\Http;
use Throwable;

class ShipmentBeatDriver extends AbstractCarrierDriver implements CarrierTrackingInterface
{
    protected string $carrierCode = 'shipmentbeat';

    public function __construct(?CarrierApiConfig $config = null)
    {
        $this->config = $config ?: CarrierApiConfig::getCarrier('shipmentbeat');
    }

    public function getCarrierCode(): string
    {
        return 'shipmentbeat';
    }

    /**
     * Map container prefix or carrier name to ShipmentBeat standard carrier code.
     */
    public static function resolveCarrierCode(string $trackingNumber, ?string $providedCarrier = null): ?string
    {
        // 1. Highest Precedence: User-selected or API-provided carrier
        if (!empty($providedCarrier) && strtolower($providedCarrier) !== 'shipmentbeat' && strtolower($providedCarrier) !== 'auto') {
            $upper = strtoupper(trim($providedCarrier));
            $validCodes = ['MAEU', 'MSCU', 'CMDU', 'COSU', 'HLCU', 'ONEY', 'EGLV', 'HDMU', 'ZIMU', 'YMLU', 'WHLC', 'PCIU', '12PD', 'ESPU', 'OOLU', 'MATS', '13DF', 'CULU'];
            if (in_array($upper, $validCodes)) {
                return $upper;
            }

            $c = strtolower(trim($providedCarrier));
            if (str_contains($c, 'maersk') || str_contains($c, 'safmarine') || str_contains($c, 'sealand')) return 'MAEU';
            if (str_contains($c, 'msc') || str_contains($c, 'mediterranean')) return 'MSCU';
            if (str_contains($c, 'hapag')) return 'HLCU';
            if (str_contains($c, 'cma') || str_contains($c, 'apl') || str_contains($c, 'anl')) return 'CMDU';
            if (str_contains($c, 'cosco')) return 'COSU';
            if (str_contains($c, 'one') || str_contains($c, 'ocean network')) return 'ONEY';
            if (str_contains($c, 'evergreen')) return 'EGLV';
            if (str_contains($c, 'hmm') || str_contains($c, 'hyundai')) return 'HDMU';
            if (str_contains($c, 'zim')) return 'ZIMU';
            if (str_contains($c, 'yang ming') || str_contains($c, 'yangming') || str_contains($c, 'yml')) return 'YMLU';
            if (str_contains($c, 'wan hai') || str_contains($c, 'wanhai')) return 'WHLC';
            if (str_contains($c, 'pil') || str_contains($c, 'pacific international')) return 'PCIU';
            if (str_contains($c, 'oocl') || str_contains($c, 'orient overseas')) return 'OOLU';
            if (str_contains($c, 'matson')) return 'MATS';
            if (str_contains($c, 'sitc')) return '12PD';
            if (str_contains($c, 'emirates') || str_contains($c, 'esl')) return 'ESPU';
            if (str_contains($c, 'ts lines') || str_contains($c, 'tsl') || str_contains($c, 'ts line')) return '13DF';
            if (str_contains($c, 'culines') || str_contains($c, 'china united') || str_contains($c, 'cul')) return 'CULU';
        }

        $clean = strtoupper(trim($trackingNumber));
        $prefix = substr($clean, 0, 4);

        // 2. Comprehensive BIC prefix mapping (All ocean carriers + Global leasing fleets)
        $bicMap = [
            // Maersk Line / Safmarine / Sealand (MAEU)
            'MSKU' => 'MAEU', 'MAEU' => 'MAEU', 'MRKU' => 'MAEU', 'MSFU' => 'MAEU',
            'MCAU' => 'MAEU', 'SEGU' => 'MAEU', 'SUDU' => 'MAEU', 'PONU' => 'MAEU',
            'SAMU' => 'MAEU', 'BSLU' => 'MAEU', 'MVIU' => 'MAEU', 'ALXU' => 'MAEU',

            // MSC (MSCU)
            'MEDU' => 'MSCU', 'MSCU' => 'MSCU', 'MSDU' => 'MSCU', 'MSPU' => 'MSCU',
            'MSEU' => 'MSCU', 'MSVU' => 'MSCU', 'MTNU' => 'MSCU', 'TCNU' => 'MSCU',
            'GLDU' => 'MSCU', 'SCXU' => 'MSCU', 'GTIU' => 'MSCU', 'MCHU' => 'MSCU',

            // Hapag-Lloyd (HLCU)
            'HLXU' => 'HLCU', 'HLCU' => 'HLCU', 'HLBU' => 'HLCU', 'CPSU' => 'HLCU',
            'UASC' => 'HLCU', 'UACU' => 'HLCU', 'HAMU' => 'HLCU', 'CLHU' => 'HLCU',
            'CPPU' => 'HLCU', 'LNXU' => 'HLCU', 'HIAU' => 'HLCU',

            // CMA CGM / APL / ANL (CMDU)
            'CMAU' => 'CMDU', 'CMDU' => 'CMDU', 'CGMU' => 'CMDU', 'APZU' => 'CMDU',
            'ANLU' => 'CMDU', 'CNCU' => 'CMDU', 'APLU' => 'CMDU', 'AMFU' => 'CMDU',
            'ECMU' => 'CMDU', 'CGTU' => 'CMDU',

            // COSCO Shipping / Florens (COSU)
            'COSU' => 'COSU', 'CHGO' => 'COSU', 'CBHU' => 'COSU', 'CCLU' => 'COSU',
            'CSNU' => 'COSU', 'CSXU' => 'COSU', 'DFSU' => 'COSU', 'FSCU' => 'COSU',
            'FBXU' => 'COSU', 'FCIU' => 'COSU', 'CKLU' => 'COSU', 'XINU' => 'COSU',

            // Ocean Network Express (ONEY)
            'ONEY' => 'ONEY', 'KKFU' => 'ONEY', 'MOLU' => 'ONEY', 'NYKU' => 'ONEY',
            'KLFU' => 'ONEY', 'TEMU' => 'ONEY', 'TGHU' => 'ONEY', 'TRHU' => 'ONEY',
            'MOFU' => 'ONEY', 'MOGU' => 'ONEY', 'JCGO' => 'ONEY',

            // Evergreen (EGLV)
            'EGLV' => 'EGLV', 'EGHU' => 'EGLV', 'EISU' => 'EGLV', 'EMCU' => 'EGLV',
            'EGSU' => 'EGLV', 'UGCO' => 'EGLV', 'UGCU' => 'EGLV', 'EGLE' => 'EGLV',

            // HMM (HDMU)
            'HDMU' => 'HDMU', 'HMMU' => 'HDMU',

            // ZIM (ZIMU)
            'ZIMU' => 'ZIMU', 'ZCSU' => 'ZIMU', 'ZIMZ' => 'ZIMU', 'ZAXU' => 'ZIMU',

            // Yang Ming (YMLU)
            'YMLU' => 'YMLU', 'YMMU' => 'YMLU', 'YMOU' => 'YMLU', 'YMPU' => 'YMLU',
            'YMCU' => 'YMLU',

            // Wan Hai Lines (WHLC)
            'WHLC' => 'WHLC', 'WHLU' => 'WHLC', 'WHCS' => 'WHLC', 'WHLE' => 'WHLC',

            // PIL (PCIU)
            'PCIU' => 'PCIU', 'PILU' => 'PCIU',

            // OOCL (OOLU)
            'OOLU' => 'OOLU', 'OOCU' => 'OOLU', 'OODU' => 'OOLU',

            // Matson (MATS)
            'MATS' => 'MATS', 'MATU' => 'MATS', 'MATF' => 'MATS',

            // SITC (12PD)
            'SITU' => '12PD', 'SITC' => '12PD', '12PD' => '12PD',

            // Emirates Shipping Line (ESPU)
            'ESPU' => 'ESPU', 'EMTU' => 'ESPU', 'ESLU' => 'ESPU',

            // TS Lines (13DF)
            'TSLU' => '13DF', '13DF' => '13DF', 'TSLN' => '13DF',

            // CU Lines (CULU)
            'CULU' => 'CULU', 'CULC' => 'CULU',

            // Major Global Leasing Container Fleets (Cronos, Seaco, Textainer, Triton, CAI, Beacon, UES, Interpool)
            'CRSU' => 'MSCU', 'CRXU' => 'MSCU', 'CRLU' => 'MSCU', 'CROU' => 'MSCU',
            'GESU' => 'MSCU', 'SEAU' => 'MSCU', 'SCZU' => 'MSCU', 'SGCU' => 'MSCU',
            'TEXU' => 'MSCU', 'TXGU' => 'MSCU', 'TGCU' => 'MSCU',
            'TTNU' => 'MSCU', 'TCLU' => 'MSCU', 'TRIU' => 'MSCU', 'TCKU' => 'MSCU', 'TLLU' => 'MSCU',
            'CAIU' => 'CMDU', 'CAXU' => 'CMDU', 'MAGU' => 'CMDU',
            'BEAU' => 'MSCU', 'BMOU' => 'MSCU', 'BSIU' => 'MSCU',
            'UESU' => 'MSCU', 'INKU' => 'MSCU', 'INBU' => 'MSCU', 'BOXU' => 'MSCU',
            'CARU' => 'MSCU', 'BHCU' => 'MSCU', 'SPNU' => 'MSCU', 'GATE' => 'MSCU',
        ];

        if (isset($bicMap[$prefix])) {
            return $bicMap[$prefix];
        }

        // 3. Check existing tracking record in local database
        try {
            $existingDb = \App\Models\ShipmentTracking::where('tracking_number', $clean)
                ->whereNotNull('carrier_code')
                ->whereNotIn('carrier_code', ['shipmentbeat', 'unknown'])
                ->first();
            if ($existingDb && !empty($existingDb->carrier_code)) {
                return strtoupper($existingDb->carrier_code);
            }
        } catch (\Throwable $e) {}

        // 4. Universal Fallback for any standard container: MSC operates universal leased & global fleet
        return 'MSCU';
    }

    /**
     * Validate ISO 6346 check digit for standard shipping container numbers.
     */
    public static function validateIso6346(string $containerNumber): array
    {
        $raw = strtoupper(trim($containerNumber));
        if (strlen($raw) !== 11 || !preg_match('/^[A-Z]{4}[0-9]{7}$/', $raw)) {
            return [
                'valid' => false,
                'message' => "Container number '{$raw}' must be exactly 4 letters followed by 7 digits (e.g., MEDU4387708)."
            ];
        }

        $letterValues = [
            'A' => 10, 'B' => 12, 'C' => 13, 'D' => 14, 'E' => 15, 'F' => 16, 'G' => 17, 'H' => 18, 'I' => 19, 'J' => 20,
            'K' => 21, 'L' => 23, 'M' => 24, 'N' => 25, 'O' => 26, 'P' => 27, 'Q' => 28, 'R' => 29, 'S' => 30, 'T' => 31,
            'U' => 32, 'V' => 34, 'W' => 35, 'X' => 36, 'Y' => 37, 'Z' => 38,
        ];

        $total = 0;
        for ($i = 0; $i < 10; $i++) {
            $char = $raw[$i];
            $val = $i < 4 ? ($letterValues[$char] ?? null) : (int) $char;
            if ($val === null) {
                return ['valid' => false, 'message' => "Invalid character in container number '{$raw}'."];
            }
            $total += $val * (2 ** $i);
        }

        $rem = $total % 11;
        if ($rem === 10) $rem = 0;

        $checkDigit = (int) $raw[10];
        if ($rem !== $checkDigit) {
            $suggested = substr($raw, 0, 10) . $rem;
            return [
                'valid' => false,
                'expected' => $rem,
                'actual' => $checkDigit,
                'suggested' => $suggested,
                'message' => "Invalid container check-digit for '{$raw}'. Expected digit is {$rem}, but '{$checkDigit}' was entered (Did you mean: {$suggested}?)."
            ];
        }

        return ['valid' => true, 'check_digit' => $rem];
    }

    /**
     * Test connection to ShipmentBeat API endpoint.
     */
    public function testConnection(): array
    {
        $config = $this->config ?: CarrierApiConfig::getCarrier('shipmentbeat');
        $apiKey = !empty($config?->api_key) ? $config->api_key : env('SHIPMENTBEAT_API_KEY');
        $baseUrl = rtrim(!empty($config?->base_url) ? $config->base_url : env('SHIPMENTBEAT_BASE_URL', 'https://api.shipmentbeat.com'), '/');

        if (empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'ShipmentBeat API Key is not configured.',
                'latency_ms' => 0,
                'details' => [],
            ];
        }

        $startTime = microtime(true);

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Accept' => 'application/json',
            ])->timeout(10)->get("{$baseUrl}/api/v1/carriers");

            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);
            $statusCode = $response->status();
            $data = $response->json();

            $this->logApiCall(
                endpoint: "{$baseUrl}/api/v1/carriers",
                method: 'GET',
                trackingNumber: null,
                requestPayload: null,
                statusCode: $statusCode,
                responsePayload: $data,
                durationMs: $latencyMs
            );

            if ($response->successful() && ($data['success'] ?? false)) {
                $carriersCount = count($data['data'] ?? []);
                return [
                    'success' => true,
                    'message' => "Successfully connected to ShipmentBeat ({$carriersCount} ocean carriers active).",
                    'latency_ms' => $latencyMs,
                    'details' => [
                        'status' => 'connected',
                        'supported_carriers_count' => $carriersCount,
                        'endpoint' => "{$baseUrl}/api/v1/carriers",
                    ],
                ];
            }

            return [
                'success' => false,
                'message' => $data['msg'] ?? "ShipmentBeat API returned status HTTP {$statusCode}.",
                'latency_ms' => $latencyMs,
                'details' => $data,
            ];
        } catch (Throwable $e) {
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);
            return [
                'success' => false,
                'message' => 'ShipmentBeat connection failed: ' . $e->getMessage(),
                'latency_ms' => $latencyMs,
                'details' => ['exception' => get_class($e)],
            ];
        }
    }

    /**
     * Track cargo by Container, B/L, or Booking number via ShipmentBeat.
     */
    public function track(string $trackingNumber, string $type = 'container', array $options = []): TrackingResultDTO
    {
        $cleanNumber = strtoupper(trim($trackingNumber));
        $config = $this->config ?: CarrierApiConfig::getCarrier('shipmentbeat');
        $apiKey = !empty($config?->api_key) ? $config->api_key : env('SHIPMENTBEAT_API_KEY');
        $baseUrl = rtrim(!empty($config?->base_url) ? $config->base_url : env('SHIPMENTBEAT_BASE_URL', 'https://api.shipmentbeat.com'), '/');

        if (empty($apiKey)) {
            throw new Exception("ShipmentBeat API key is not configured in settings or environment.");
        }

        // Validate container format when tracking by container
        if (strtolower($type) === 'container') {
            if (strlen($cleanNumber) < 8 || !preg_match('/^[A-Z0-9]+$/', $cleanNumber)) {
                throw new Exception("Container number '{$cleanNumber}' must be at least 8 alphanumeric characters.");
            }
        }

        $carrierCode = self::resolveCarrierCode($cleanNumber, $options['carrier_code'] ?? ($options['carrier_name'] ?? null));

        if (empty($carrierCode)) {
            throw new Exception("Carrier could not be identified for '{$cleanNumber}'. Please ensure it is a recognized container or specify the carrier.");
        }

        $startTime = microtime(true);

        $trackingNumberType = match (strtolower($type)) {
            'mbl', 'bl', 'bill_of_lading' => 'BillOfLading',
            'booking', 'booking_number' => 'BookingNumber',
            default => 'ContainerNumber',
        };

        try {
            // First check if shipment is already registered in ShipmentBeat
            $searchResponse = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Accept' => 'application/json',
            ])->timeout(15)->get("{$baseUrl}/api/v1/shipments", [
                'trackingNumber' => $cleanNumber,
                'page' => 1,
                'pageSize' => 10,
            ]);

            $existingShipment = null;
            if ($searchResponse->successful()) {
                $list = $searchResponse->json('data.items') ?? ($searchResponse->json('data') ?? []);
                if (is_array($list)) {
                    foreach ($list as $item) {
                        if (strtoupper($item['trackingNumber'] ?? '') === $cleanNumber) {
                            $existingShipment = $item;
                            if (!empty($item['carrierCode'])) {
                                $carrierCode = $item['carrierCode'];
                            }
                            break;
                        }
                    }
                }
            }

            // If not found in active list, register/create tracking on ShipmentBeat
            if (!$existingShipment) {
                // If user didn't explicitly choose a carrier, build priority candidate carriers
                $candidateCarriers = [$carrierCode];
                if (empty($options['carrier_code']) || strtolower($options['carrier_code']) === 'shipmentbeat') {
                    $topCarriers = ['MSCU', 'MAEU', 'CMDU', 'COSU', 'HLCU', 'ONEY', 'EGLV', 'HDMU', 'YMLU'];
                    $candidateCarriers = array_values(array_unique(array_merge([$carrierCode], $topCarriers)));
                }

                $createData = null;
                $lastStatusCode = 200;

                foreach ($candidateCarriers as $currCarrier) {
                    $typesToTry = [$trackingNumberType];
                    if ($trackingNumberType === 'ContainerNumber' && $currCarrier === 'YMLU') {
                        $typesToTry = ['BillOfLading', 'ContainerNumber'];
                    } elseif ($trackingNumberType === 'ContainerNumber') {
                        $typesToTry = ['ContainerNumber', 'BillOfLading'];
                    }

                    $successForCarrier = false;
                    foreach ($typesToTry as $candidateType) {
                        $createPayload = [
                            'carrierCode' => $currCarrier,
                            'trackingNumber' => $cleanNumber,
                            'trackingNumberType' => $candidateType,
                        ];

                        $createResponse = Http::withHeaders([
                            'Authorization' => "Bearer {$apiKey}",
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                        ])->timeout(15)->post("{$baseUrl}/api/v1/shipments", $createPayload);

                        $lastStatusCode = $createResponse->status();
                        $createData = $createResponse->json();

                        if ($createResponse->successful() && ($createData['success'] ?? false)) {
                            $existingShipment = $createData['data'] ?? $createData;
                            $carrierCode = $currCarrier;
                            $successForCarrier = true;
                            break;
                        }

                        // If carrier does not support ContainerNumber (code 1103), loop to fallback candidate type
                        if (($createData['code'] ?? null) == 1103 || str_contains(strtolower($createData['msg'] ?? ''), 'does not support tracking number type')) {
                            continue;
                        }

                        // If error message indicates duplicate or already registered, proceed to fetch
                        $errMsg = $createData['msg'] ?? '';
                        if (!empty($createData['data']['id']) || str_contains(strtolower($errMsg), 'already') || str_contains(strtolower($errMsg), 'duplicate') || ($createData['duplicate'] ?? false)) {
                            $existingShipment = $createData['data'] ?? null;
                            $carrierCode = $currCarrier;
                            $successForCarrier = true;
                            break;
                        }

                        break;
                    }

                    if ($successForCarrier) {
                        break;
                    }
                }

                $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

                $this->logApiCall(
                    endpoint: "{$baseUrl}/api/v1/shipments",
                    method: 'POST',
                    trackingNumber: $cleanNumber,
                    requestPayload: ['carrierCode' => $carrierCode, 'trackingNumber' => $cleanNumber],
                    statusCode: $lastStatusCode,
                    responsePayload: $createData,
                    durationMs: $latencyMs
                );

                if (!$existingShipment && !empty($createData['data']['id'])) {
                    $existingShipment = $createData['data'];
                }

                // If still not captured, attempt one more search lookup
                if (!$existingShipment) {
                    $reSearch = Http::withHeaders([
                        'Authorization' => "Bearer {$apiKey}",
                        'Accept' => 'application/json',
                    ])->timeout(10)->get("{$baseUrl}/api/v1/shipments", ['trackingNumber' => $cleanNumber]);
                    if ($reSearch->successful()) {
                        $items = $reSearch->json('data.items') ?? ($reSearch->json('data') ?? []);
                        if (is_array($items) && !empty($items[0])) {
                            $existingShipment = $items[0];
                            if (!empty($existingShipment['carrierCode'])) {
                                $carrierCode = $existingShipment['carrierCode'];
                            }
                        }
                    }
                }

                if (!$existingShipment) {
                    $errMsg = $createData['msg'] ?? 'Unable to create tracking on ShipmentBeat';
                    throw new Exception("ShipmentBeat tracking error: {$errMsg}");
                }
            }

            // Fetch detailed shipment info (POL, POD, Legs, Container Movement Events)
            if (!empty($existingShipment['id'])) {
                try {
                    $detailResponse = Http::withHeaders([
                        'Authorization' => "Bearer {$apiKey}",
                        'Accept' => 'application/json',
                    ])->timeout(15)->get("{$baseUrl}/api/v1/shipments/{$existingShipment['id']}");

                    if ($detailResponse->successful() && ($detailResponse->json('success') ?? false)) {
                        $existingShipment = $detailResponse->json('data') ?? $existingShipment;
                    }
                } catch (Throwable $e) {
                    // Fall back to existing summary data if detail endpoint fails
                }
            }

            // Map POL & POD directly from ShipmentBeat dynamic API response
            $originLocode = !empty($existingShipment['pol']) ? $existingShipment['pol'] : null;
            $originName = null;
            if (!empty($existingShipment['origin']['city'])) {
                $originName = $existingShipment['origin']['city'] . (!empty($existingShipment['origin']['countryName']) ? ', ' . $existingShipment['origin']['countryName'] : '');
            } elseif (!empty($existingShipment['pol'])) {
                $originName = $existingShipment['pol'];
            }

            $destinationLocode = !empty($existingShipment['pod']) ? $existingShipment['pod'] : null;
            $destinationName = null;
            if (!empty($existingShipment['destination']['city'])) {
                $destinationName = $existingShipment['destination']['city'] . (!empty($existingShipment['destination']['countryName']) ? ', ' . $existingShipment['destination']['countryName'] : '');
            } elseif (!empty($existingShipment['pod'])) {
                $destinationName = $existingShipment['pod'];
            }

            // Vessel & Voyage from Transport Legs or Port Calls directly from ShipmentBeat response
            $vesselName = null;
            $voyageNo = null;
            foreach ($existingShipment['transportLegs'] ?? [] as $leg) {
                if (!empty($leg['vesselName'])) {
                    $vesselName = $leg['vesselName'];
                    $voyageNo = $leg['voyageNumber'] ?? null;
                    break;
                }
            }
            if (empty($vesselName)) {
                foreach ($existingShipment['portCalls'] ?? [] as $pc) {
                    foreach ($pc['vessels'] ?? [] as $v) {
                        if (!empty($v['vesselName'])) {
                            $vesselName = $v['vesselName'];
                            $voyageNo = $v['voyageNumber'] ?? null;
                            break 2;
                        }
                    }
                }
            }
            if (empty($vesselName) && !empty($existingShipment['vessel_name'])) {
                $vesselName = $existingShipment['vessel_name'];
            }
            if (empty($voyageNo) && !empty($existingShipment['voyage_no'])) {
                $voyageNo = $existingShipment['voyage_no'];
            }

            $etd = !empty($existingShipment['etd']) ? date('Y-m-d H:i:s', strtotime($existingShipment['etd'])) : null;
            $eta = !empty($existingShipment['eta']) ? date('Y-m-d H:i:s', strtotime($existingShipment['eta'])) : null;
            $atd = !empty($existingShipment['atd']) ? date('Y-m-d H:i:s', strtotime($existingShipment['atd'])) : null;
            $ata = !empty($existingShipment['ata']) ? date('Y-m-d H:i:s', strtotime($existingShipment['ata'])) : null;

            // Map ShipmentBeat status to FreightX status
            $rawStatus = strtolower((string) ($existingShipment['trackingStatus'] ?? ($existingShipment['status'] ?? '')));
            $lifecycle = strtolower((string) ($existingShipment['lifecycleStatus'] ?? ''));

            if (empty($rawStatus) || $rawStatus === 'pending' || $rawStatus === 'not_started' || $lifecycle === 'pending') {
                $status = 'booked';
                $progress = 0; // Stage 1 of 6: Booked at Origin Terminal, awaiting gate-in and departure
            } else {
                $status = match ($rawStatus) {
                    'delivered', 'completed' => 'delivered',
                    'arrived', 'discharged' => 'discharged',
                    'loaded' => 'loaded',
                    'departed', 'in_transit' => 'in_transit',
                    'gate_in' => 'gate_in',
                    'delayed', 'exception' => 'delayed',
                    default => 'booked',
                };

                $progress = match ($status) {
                    'delivered' => 100,
                    'discharged' => 85,
                    'in_transit' => 50,
                    'loaded' => 35,
                    'gate_in' => 15,
                    'delayed' => 45,
                    default => 0,
                };
            }

            // Real-time maritime dead-reckoning calculation for in-transit ocean voyage:
            if ($status === 'in_transit' && $atd && $eta) {
                $depTs = strtotime($atd);
                $arrTs = strtotime($eta);
                $nowTs = time();
                if ($arrTs > $depTs) {
                    $elapsed = max(0, $nowTs - $depTs);
                    $duration = $arrTs - $depTs;
                    $fraction = min(0.95, max(0.05, $elapsed / $duration));
                    $progress = (int) round($fraction * 100);
                }
            }

            $events = [];
            // Parse real container location movement events from ShipmentBeat
            if (!empty($existingShipment['containers']) && is_array($existingShipment['containers'])) {
                foreach ($existingShipment['containers'] as $containerItem) {
                    foreach ($containerItem['locations'] ?? [] as $loc) {
                        $locName = $loc['locationName'] ?? ($loc['city'] ?? null);
                        $locCode = $loc['portCode'] ?? ($loc['locationCode'] ?? null);

                        foreach ($loc['events'] ?? [] as $ev) {
                            $isAct = ($ev['eventTimeType'] ?? '') === 'actual' || !empty($ev['actTime']);
                            $eventTime = $ev['actTime'] ?? ($ev['eventTime'] ?? ($ev['estTime'] ?? now()->toIso8601String()));
                            $desc = $ev['eventDescription'] ?? 'Milestone';
                            $terminalStr = $ev['terminalName'] ?? null;

                            $events[] = new TrackingEventDTO(
                                eventId: 'sb_' . ($containerItem['containerNumber'] ?? 'cnt') . '_' . md5($desc . $eventTime . $locName),
                                eventType: strtoupper($ev['status'] ?? 'MILESTONE'),
                                eventClassifier: $isAct ? 'ACT' : 'EST',
                                eventCode: strtoupper(substr($ev['status'] ?? 'INFO', 0, 10)),
                                eventDateTime: date('Y-m-d H:i:s', strtotime($eventTime)),
                                title: $desc,
                                description: $terminalStr ? "Terminal: {$terminalStr}" : "Location: {$locName}",
                                locationName: $locName,
                                locationLocode: $locCode,
                                vesselName: $ev['vesselName'] ?: $vesselName,
                                voyageNo: $ev['voyageNumber'] ?: $voyageNo
                            );
                        }
                    }
                }
            }

            // Fallback to top-level events if containers array is empty
            if (empty($events) && !empty($existingShipment['events']) && is_array($existingShipment['events'])) {
                foreach ($existingShipment['events'] as $evt) {
                    $events[] = new TrackingEventDTO(
                        eventId: $evt['id'] ?? ('sb_' . uniqid()),
                        eventType: $evt['type'] ?? 'MILESTONE',
                        eventClassifier: $evt['classifier'] ?? 'ACT',
                        eventCode: $evt['code'] ?? 'MILESTONE',
                        eventDateTime: $evt['date'] ?? now()->toIso8601String(),
                        title: $evt['title'] ?? ($evt['description'] ?? 'Carrier Milestone'),
                        description: $evt['description'] ?? null,
                        locationName: $evt['location'] ?? null,
                        locationLocode: $evt['locode'] ?? null,
                        vesselName: $evt['vessel'] ?? $vesselName,
                        voyageNo: $evt['voyage'] ?? $voyageNo
                    );
                }
            }

            // If still no events, generate initial registration milestone
            if (empty($events)) {
                $isPending = empty($rawStatus) || $rawStatus === 'not_started' || $lifecycle === 'pending';
                $carrierDisplay = !empty($existingShipment['carrierName']) ? $existingShipment['carrierName'] : $carrierCode;
                $createdDate = !empty($existingShipment['createdAt']) ? date('Y-m-d H:i:s', strtotime($existingShipment['createdAt'])) : now()->toDateTimeString();

                $events[] = new TrackingEventDTO(
                    eventId: 'sb_evt_init_' . md5($cleanNumber . $carrierCode),
                    eventType: $isPending ? 'TRACKING_REGISTERED' : 'TRACKING_ACTIVE',
                    eventClassifier: 'EST',
                    eventCode: 'BOOKED',
                    eventDateTime: $createdDate,
                    title: $isPending ? "Tracking Registered ({$carrierDisplay} Network)" : "Shipment Monitoring Active ({$carrierDisplay})",
                    description: $isPending 
                        ? "Container {$cleanNumber} registered with {$carrierDisplay}. Carrier telemetry synchronization in progress."
                        : "Container {$cleanNumber} linked to {$carrierDisplay} via ShipmentBeat live gateway.",
                    locationName: $originName ?: 'Carrier Terminal',
                    locationLocode: $originLocode,
                    vesselName: $vesselName,
                    voyageNo: $voyageNo
                );
            }

            // Sort events chronologically (latest first)
            usort($events, fn($a, $b) => strtotime($b->eventDateTime) <=> strtotime($a->eventDateTime));

            $carrierDisplay = !empty($existingShipment['carrierName']) ? $existingShipment['carrierName'] : $carrierCode;
            $statusDescription = empty($rawStatus) || $rawStatus === 'not_started' || $lifecycle === 'pending'
                ? "Carrier: {$carrierDisplay} ({$carrierCode}) | Status: Registered on Carrier Network (Awaiting Carrier Scans)"
                : "Carrier: {$carrierDisplay} ({$carrierCode}) | Status: " . ucfirst(str_replace('_', ' ', $rawStatus));

            return new TrackingResultDTO(
                carrierCode: $carrierCode ?: 'shipmentbeat',
                trackingNumber: $cleanNumber,
                trackingType: $type,
                currentStatus: $status,
                statusDescription: $statusDescription,
                progressPercentage: $progress,
                originLocode: $originLocode,
                originName: $originName,
                destinationLocode: $destinationLocode,
                destinationName: $destinationName,
                vesselName: $vesselName,
                voyageNo: $voyageNo,
                etd: $etd,
                eta: $eta,
                atd: $atd,
                ata: $ata,
                events: $events,
                rawPayload: $existingShipment ?: ['tracking_number' => $cleanNumber, 'carrier' => $carrierCode],
                isSimulation: false
            );
        } catch (Throwable $e) {
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);
            $this->logApiCall(
                endpoint: "{$baseUrl}/api/v1/shipments",
                method: 'POST',
                trackingNumber: $cleanNumber,
                requestPayload: ['trackingNumber' => $cleanNumber],
                statusCode: 500,
                responsePayload: ['error' => $e->getMessage()],
                durationMs: $latencyMs,
                errorMessage: $e->getMessage()
            );

            throw new Exception("ShipmentBeat: " . $e->getMessage(), 0, $e);
        }
    }
}
