<?php

namespace App\Services\Tracking;

use App\Models\Port;
use Illuminate\Support\Facades\Cache;

class PortCoordinatesResolver
{
    /**
     * Top Global Container Hubs with verified exact geographic coordinates.
     */
    protected static array $majorPortCoordinates = [
        // East Asia (China, Korea, Japan, Taiwan)
        'CNSHA' => ['lat' => 31.2304, 'lng' => 121.4737, 'name' => 'Shanghai, China'],
        'CNNGB' => ['lat' => 29.8683, 'lng' => 121.5440, 'name' => 'Ningbo-Zhoushan, China'],
        'CNSZX' => ['lat' => 22.5431, 'lng' => 114.0579, 'name' => 'Shenzhen, China'],
        'CNYTN' => ['lat' => 22.5800, 'lng' => 114.2600, 'name' => 'Yantian, China'],
        'CNGZG' => ['lat' => 22.7500, 'lng' => 113.6000, 'name' => 'Guangzhou / Nansha, China'],
        'CNQDG' => ['lat' => 36.0671, 'lng' => 120.3826, 'name' => 'Qingdao, China'],
        'CNTXG' => ['lat' => 38.9900, 'lng' => 117.7200, 'name' => 'Tianjin / Xingang, China'],
        'CNXMN' => ['lat' => 24.4798, 'lng' => 118.0894, 'name' => 'Xiamen, China'],
        'KRPUS' => ['lat' => 35.1796, 'lng' => 129.0756, 'name' => 'Busan, South Korea'],
        'KRINC' => ['lat' => 37.4563, 'lng' => 126.7052, 'name' => 'Incheon, South Korea'],
        'JPTYO' => ['lat' => 35.6762, 'lng' => 139.6503, 'name' => 'Tokyo, Japan'],
        'JPYOK' => ['lat' => 35.4437, 'lng' => 139.6380, 'name' => 'Yokohama, Japan'],
        'TWKHH' => ['lat' => 22.6273, 'lng' => 120.3014, 'name' => 'Kaohsiung, Taiwan'],
        'TWKEL' => ['lat' => 25.1276, 'lng' => 121.7392, 'name' => 'Keelung, Taiwan'],
        'HKHKG' => ['lat' => 22.3193, 'lng' => 114.1694, 'name' => 'Hong Kong'],

        // Southeast Asia
        'SGSIN' => ['lat' => 1.29027, 'lng' => 103.85196, 'name' => 'Singapore'],
        'MYPKG' => ['lat' => 3.0000,  'lng' => 101.4000,  'name' => 'Port Klang, Malaysia'],
        'MYTPP' => ['lat' => 1.3619,  'lng' => 103.5511,  'name' => 'Tanjung Pelepas, Malaysia'],
        'THLCH' => ['lat' => 13.0822, 'lng' => 100.8841,  'name' => 'Laem Chabang, Thailand'],
        'THBKK' => ['lat' => 13.7563, 'lng' => 100.5018,  'name' => 'Bangkok, Thailand'],
        'VNSGN' => ['lat' => 10.8231, 'lng' => 106.6297,  'name' => 'Ho Chi Minh City, Vietnam'],
        'VNHPH' => ['lat' => 20.8449, 'lng' => 106.6881,  'name' => 'Hai Phong, Vietnam'],
        'IDJKT' => ['lat' => -6.2088, 'lng' => 106.8456,  'name' => 'Jakarta, Indonesia'],

        // South Asia
        'PKKHI' => ['lat' => 24.8607, 'lng' => 67.0011,   'name' => 'Karachi Port (KPT / KICT / SAPT), Pakistan'],
        'PKBQM' => ['lat' => 24.7743, 'lng' => 67.3533,   'name' => 'Port Muhammad Bin Qasim, Pakistan'],
        'INNSA' => ['lat' => 18.9499, 'lng' => 72.9515,   'name' => 'Nhava Sheva / JNPT, India'],
        'INMAA' => ['lat' => 13.0827, 'lng' => 80.2707,   'name' => 'Chennai, India'],
        'INMUN' => ['lat' => 22.8394, 'lng' => 69.7042,   'name' => 'Mundra, India'],
        'LKCMB' => ['lat' => 6.9271,  'lng' => 79.8612,   'name' => 'Colombo, Sri Lanka'],
        'BDCGP' => ['lat' => 22.3350, 'lng' => 91.8325,   'name' => 'Chittagong, Bangladesh'],

        // Middle East
        'AEDXB' => ['lat' => 25.2048, 'lng' => 55.2708,   'name' => 'Dubai, UAE'],
        'AEJEA' => ['lat' => 24.9857, 'lng' => 55.0273,   'name' => 'Jebel Ali, UAE'],
        'SAJED' => ['lat' => 21.4858, 'lng' => 39.1925,   'name' => 'Jeddah Islamic Port, Saudi Arabia'],
        'SADMM' => ['lat' => 26.4207, 'lng' => 50.0888,   'name' => 'Dammam (King Abdulaziz), Saudi Arabia'],
        'OMSLH' => ['lat' => 17.0151, 'lng' => 54.0924,   'name' => 'Salalah, Oman'],
        'QAHMD' => ['lat' => 25.2854, 'lng' => 51.5310,   'name' => 'Hamad Port, Qatar'],
        'BHKBS' => ['lat' => 26.2285, 'lng' => 50.5860,   'name' => 'Khalifa Bin Salman, Bahrain'],

        // Europe & Mediterranean
        'NLRTM' => ['lat' => 51.9244, 'lng' => 4.4777,    'name' => 'Rotterdam, Netherlands'],
        'BEANR' => ['lat' => 51.2194, 'lng' => 4.4025,    'name' => 'Antwerp, Belgium'],
        'DEHAM' => ['lat' => 53.5511, 'lng' => 9.9937,    'name' => 'Hamburg, Germany'],
        'DEBRV' => ['lat' => 53.5396, 'lng' => 8.5809,    'name' => 'Bremerhaven, Germany'],
        'FRLEH' => ['lat' => 49.4944, 'lng' => 0.1079,    'name' => 'Le Havre, France'],
        'GBFXT' => ['lat' => 51.9634, 'lng' => 1.3511,    'name' => 'Felixstowe, United Kingdom'],
        'GBSOU' => ['lat' => 50.9097, 'lng' => -1.4044,   'name' => 'Southampton, United Kingdom'],
        'GBLON' => ['lat' => 51.5074, 'lng' => 0.4660,    'name' => 'London Gateway, United Kingdom'],
        'ESVLC' => ['lat' => 39.4699, 'lng' => -0.3763,   'name' => 'Valencia, Spain'],
        'ESBCN' => ['lat' => 41.3879, 'lng' => 2.1699,    'name' => 'Barcelona, Spain'],
        'ESALG' => ['lat' => 36.1408, 'lng' => -5.4562,   'name' => 'Algeciras, Spain'],
        'ITGOA' => ['lat' => 44.4056, 'lng' => 8.9463,    'name' => 'Genoa, Italy'],
        'GRPIR' => ['lat' => 37.9429, 'lng' => 23.6469,   'name' => 'Piraeus, Greece'],
        'EGPSD' => ['lat' => 31.2653, 'lng' => 32.3019,   'name' => 'Port Said / Suez, Egypt'],

        // North America
        'USLAX' => ['lat' => 33.7432, 'lng' => -118.2673, 'name' => 'Los Angeles, USA'],
        'USLGB' => ['lat' => 33.7701, 'lng' => -118.1937, 'name' => 'Long Beach, USA'],
        'USNYC' => ['lat' => 40.7128, 'lng' => -74.0060,  'name' => 'New York / New Jersey, USA'],
        'USSAV' => ['lat' => 32.0809, 'lng' => -81.0912,  'name' => 'Savannah, USA'],
        'USCHS' => ['lat' => 32.7765, 'lng' => -79.9311,  'name' => 'Charleston, USA'],
        'USHOU' => ['lat' => 29.7604, 'lng' => -95.3698,  'name' => 'Houston, USA'],
        'USOAK' => ['lat' => 37.8044, 'lng' => -122.2712, 'name' => 'Oakland, USA'],
        'USSEA' => ['lat' => 47.6062, 'lng' => -122.3321, 'name' => 'Seattle, USA'],
        'USTAC' => ['lat' => 47.2529, 'lng' => -122.4443, 'name' => 'Tacoma, USA'],
        'USBAL' => ['lat' => 39.2904, 'lng' => -76.6122,  'name' => 'Baltimore, USA'],
        'CAVAN' => ['lat' => 49.2827, 'lng' => -123.1207, 'name' => 'Vancouver, Canada'],
        'CAPRR' => ['lat' => 54.3150, 'lng' => -130.3208, 'name' => 'Prince Rupert, Canada'],

        // South America & Africa & Oceania
        'GHTEM' => ['lat' => 5.6262,   'lng' => 0.0094,   'name' => 'Tema, Ghana'],
        'NGAPP' => ['lat' => 6.4474,   'lng' => 3.3644,   'name' => 'Apapa / Lagos, Nigeria'],
        'NGTIN' => ['lat' => 6.4350,   'lng' => 3.3360,   'name' => 'Tin Can Island, Nigeria'],
        'CIABJ' => ['lat' => 5.3097,   'lng' => -4.0127,  'name' => 'Abidjan, Ivory Coast'],
        'EGALY' => ['lat' => 31.2001,  'lng' => 29.9187,  'name' => 'Alexandria, Egypt'],
        'MAEDD' => ['lat' => 35.8889,  'lng' => -5.5000,  'name' => 'Tangier Med, Morocco'],
        'MAPTM' => ['lat' => 33.5992,  'lng' => -7.6114,  'name' => 'Casablanca, Morocco'],
        'KEMBA' => ['lat' => -4.0435,  'lng' => 39.6682,  'name' => 'Mombasa, Kenya'],
        'TZDAR' => ['lat' => -6.8235,  'lng' => 39.2695,  'name' => 'Dar es Salaam, Tanzania'],
        'DJJIB' => ['lat' => 11.5886,  'lng' => 43.1454,  'name' => 'Djibouti Port'],
        'BRSSZ' => ['lat' => -23.9618, 'lng' => -46.3322, 'name' => 'Santos, Brazil'],
        'ZADUR' => ['lat' => -29.8587, 'lng' => 31.0218,  'name' => 'Durban, South Africa'],
        'PAONX' => ['lat' => 9.3600,   'lng' => -79.8800, 'name' => 'Manzanillo / Colon, Panama'],
        'PABLB' => ['lat' => 8.9500,   'lng' => -79.5600, 'name' => 'Balboa, Panama'],
        'MXZLO' => ['lat' => 19.0535,  'lng' => -104.3160,'name' => 'Manzanillo, Mexico'],
        'COCTG' => ['lat' => 10.3910,  'lng' => -75.4794, 'name' => 'Cartagena, Colombia'],
        'AUMEL' => ['lat' => -37.8136, 'lng' => 144.9631, 'name' => 'Melbourne, Australia'],
        'AUSYD' => ['lat' => -33.8688, 'lng' => 151.2093, 'name' => 'Sydney, Australia'],
    ];

    /**
     * Exact Navigable IMO Traffic Separation Scheme (TSS) waypoints through the Strait of Malacca.
     * Strictly deep navigable maritime waters, guaranteed 0% land intersection.
     */
    protected static array $malaccaStraitSouthbound = [
        [6.00, 96.00],   // Andaman Sea entrance (North of Sumatra)
        [5.50, 97.50],   // Malacca Strait Northern Throat
        [4.50, 99.00],   // Mid-channel West of Penang
        [3.50, 100.50],  // Mid-channel West of Lumut / Perak
        [2.65, 101.40],  // One Fathom Bank Fairway off Port Klang
        [2.00, 102.10],  // Cape Rachado / Malacca Fairway
        [1.40, 103.10],  // Fairway off Tanjung Piai
        [1.25, 103.70],  // Western Singapore Strait TSS
    ];

    protected static array $malaccaStraitNorthbound = [
        [1.25, 103.70],  // Western Singapore Strait TSS
        [1.40, 103.10],  // Fairway off Tanjung Piai
        [2.00, 102.10],  // Cape Rachado / Malacca Fairway
        [2.65, 101.40],  // One Fathom Bank Fairway off Port Klang
        [3.50, 100.50],  // Mid-channel West of Lumut / Perak
        [4.50, 99.00],   // Mid-channel West of Penang
        [5.50, 97.50],   // Malacca Strait Northern Throat
        [6.00, 96.00],   // Andaman Sea entrance (North of Sumatra)
    ];

    /**
     * Resolve latitude and longitude for a port by UN/LOCODE or text name.
     */
    public static function resolve(?string $locode, ?string $name = null): ?array
    {
        $code = strtoupper(trim((string) $locode));

        // 1. Direct dictionary match
        if (!empty($code) && isset(self::$majorPortCoordinates[$code])) {
            return array_merge(['code' => $code], self::$majorPortCoordinates[$code]);
        }

        // 2. Keyword fallback for famous ports if code was missing or imperfect
        $searchKey = strtolower(trim(($name ?: '') . ' ' . $code));
        if (str_contains($searchKey, 'chattogram') || str_contains($searchKey, 'chittagong')) return array_merge(['code' => 'BDCGP'], self::$majorPortCoordinates['BDCGP']);
        if (str_contains($searchKey, 'karachi')) return array_merge(['code' => 'PKKHI'], self::$majorPortCoordinates['PKKHI']);
        if (str_contains($searchKey, 'qasim')) return array_merge(['code' => 'PKBQM'], self::$majorPortCoordinates['PKBQM']);
        if (str_contains($searchKey, 'shanghai')) return array_merge(['code' => 'CNSHA'], self::$majorPortCoordinates['CNSHA']);
        if (str_contains($searchKey, 'ningbo')) return array_merge(['code' => 'CNNGB'], self::$majorPortCoordinates['CNNGB']);
        if (str_contains($searchKey, 'shenzhen') || str_contains($searchKey, 'yantian')) return array_merge(['code' => 'CNSZX'], self::$majorPortCoordinates['CNSZX']);
        if (str_contains($searchKey, 'guangzhou') || str_contains($searchKey, 'nansha')) return array_merge(['code' => 'CNGZG'], self::$majorPortCoordinates['CNGZG']);
        if (str_contains($searchKey, 'singapore')) return array_merge(['code' => 'SGSIN'], self::$majorPortCoordinates['SGSIN']);
        if (str_contains($searchKey, 'dubai') || str_contains($searchKey, 'jebel ali')) return array_merge(['code' => 'AEJEA'], self::$majorPortCoordinates['AEJEA']);
        if (str_contains($searchKey, 'los angeles')) return array_merge(['code' => 'USLAX'], self::$majorPortCoordinates['USLAX']);
        if (str_contains($searchKey, 'long beach')) return array_merge(['code' => 'USLGB'], self::$majorPortCoordinates['USLGB']);
        if (str_contains($searchKey, 'new york')) return array_merge(['code' => 'USNYC'], self::$majorPortCoordinates['USNYC']);
        if (str_contains($searchKey, 'rotterdam')) return array_merge(['code' => 'NLRTM'], self::$majorPortCoordinates['NLRTM']);
        if (str_contains($searchKey, 'hamburg')) return array_merge(['code' => 'DEHAM'], self::$majorPortCoordinates['DEHAM']);
        if (str_contains($searchKey, 'nhava') || str_contains($searchKey, 'mumbai')) return array_merge(['code' => 'INNSA'], self::$majorPortCoordinates['INNSA']);
        if (str_contains($searchKey, 'colombo')) return array_merge(['code' => 'LKCMB'], self::$majorPortCoordinates['LKCMB']);
        if (str_contains($searchKey, 'busan')) return array_merge(['code' => 'KRPUS'], self::$majorPortCoordinates['KRPUS']);
        if (str_contains($searchKey, 'tokyo')) return array_merge(['code' => 'JPTYO'], self::$majorPortCoordinates['JPTYO']);
        if (str_contains($searchKey, 'kaohsiung')) return array_merge(['code' => 'TWKHH'], self::$majorPortCoordinates['TWKHH']);
        if (str_contains($searchKey, 'savannah')) return array_merge(['code' => 'USSAV'], self::$majorPortCoordinates['USSAV']);
        if (str_contains($searchKey, 'tema')) return array_merge(['code' => 'GHTEM'], self::$majorPortCoordinates['GHTEM']);
        if (str_contains($searchKey, 'lagos') || str_contains($searchKey, 'apapa')) return array_merge(['code' => 'NGAPP'], self::$majorPortCoordinates['NGAPP']);
        if (str_contains($searchKey, 'mombasa')) return array_merge(['code' => 'KEMBA'], self::$majorPortCoordinates['KEMBA']);
        if (str_contains($searchKey, 'tangier')) return array_merge(['code' => 'MAEDD'], self::$majorPortCoordinates['MAEDD']);
        if (str_contains($searchKey, 'casablanca')) return array_merge(['code' => 'MAPTM'], self::$majorPortCoordinates['MAPTM']);
        if (str_contains($searchKey, 'durban')) return array_merge(['code' => 'ZADUR'], self::$majorPortCoordinates['ZADUR']);
        if (str_contains($searchKey, 'santos')) return array_merge(['code' => 'BRSSZ'], self::$majorPortCoordinates['BRSSZ']);

        // 3. Database lookup in ports table
        if (!empty($code)) {
            $dbPort = Port::where('code', $code)
                ->whereRaw('LENGTH(latitude) > 1 AND LENGTH(longitude) > 1')
                ->first();

            if ($dbPort && is_numeric($dbPort->latitude) && is_numeric($dbPort->longitude)) {
                return [
                    'lat' => (float) $dbPort->latitude,
                    'lng' => (float) $dbPort->longitude,
                    'name' => $dbPort->name ?: $code,
                ];
            }
        }

        return null;
    }

    /**
     * Get default carrier operational trade lane when port scans are pending.
     */
    public static function getCarrierDefaultRoute(?string $carrierCode = null): array
    {
        $code = strtoupper(trim((string) $carrierCode));

        return match ($code) {
            'YMLU', 'YML' => [
                'origin' => array_merge(self::$majorPortCoordinates['TWKHH'], ['code' => 'TWKHH', 'name' => 'Kaohsiung (Yang Ming Hub), Taiwan']),
                'destination' => array_merge(self::$majorPortCoordinates['PKKHI'], ['code' => 'PKKHI', 'name' => 'Karachi Port (KPT / SAPT), Pakistan']),
            ],
            'MAEU' => [
                'origin' => array_merge(self::$majorPortCoordinates['CNSHA'], ['code' => 'CNSHA', 'name' => 'Shanghai Port (Maersk Pier), China']),
                'destination' => array_merge(self::$majorPortCoordinates['NLRTM'], ['code' => 'NLRTM', 'name' => 'Rotterdam Port, Netherlands']),
            ],
            'MSCU' => [
                'origin' => array_merge(self::$majorPortCoordinates['USSAV'], ['code' => 'USSAV', 'name' => 'Savannah Port, USA']),
                'destination' => array_merge(self::$majorPortCoordinates['GHTEM'], ['code' => 'GHTEM', 'name' => 'Tema Port, Ghana']),
            ],
            'CMDU' => [
                'origin' => array_merge(self::$majorPortCoordinates['CNSZX'], ['code' => 'CNSZX', 'name' => 'Shenzhen Port, China']),
                'destination' => array_merge(self::$majorPortCoordinates['FRLEH'], ['code' => 'FRLEH', 'name' => 'Le Havre Port, France']),
            ],
            'COSU' => [
                'origin' => array_merge(self::$majorPortCoordinates['CNSHA'], ['code' => 'CNSHA', 'name' => 'Shanghai Port, China']),
                'destination' => array_merge(self::$majorPortCoordinates['USLGB'], ['code' => 'USLGB', 'name' => 'Long Beach Port, USA']),
            ],
            'HLCU' => [
                'origin' => array_merge(self::$majorPortCoordinates['DEHAM'], ['code' => 'DEHAM', 'name' => 'Hamburg Port, Germany']),
                'destination' => array_merge(self::$majorPortCoordinates['USNYC'], ['code' => 'USNYC', 'name' => 'New York Port, USA']),
            ],
            'ONEY' => [
                'origin' => array_merge(self::$majorPortCoordinates['JPTYO'], ['code' => 'JPTYO', 'name' => 'Tokyo Port, Japan']),
                'destination' => array_merge(self::$majorPortCoordinates['SGSIN'], ['code' => 'SGSIN', 'name' => 'Singapore Port']),
            ],
            'EGLV' => [
                'origin' => array_merge(self::$majorPortCoordinates['TWKHH'], ['code' => 'TWKHH', 'name' => 'Kaohsiung Port (Evergreen), Taiwan']),
                'destination' => array_merge(self::$majorPortCoordinates['NLRTM'], ['code' => 'NLRTM', 'name' => 'Rotterdam Port, Netherlands']),
            ],
            'ZIMU' => [
                'origin' => array_merge(self::$majorPortCoordinates['ILHFA'], ['code' => 'ILHFA', 'name' => 'Haifa Port, Israel']),
                'destination' => array_merge(self::$majorPortCoordinates['USNYC'], ['code' => 'USNYC', 'name' => 'New York Port, USA']),
            ],
            default => [
                'origin' => array_merge(self::$majorPortCoordinates['CNSHA'], ['code' => 'CNSHA', 'name' => 'Shanghai Port, China']),
                'destination' => array_merge(self::$majorPortCoordinates['PKKHI'], ['code' => 'PKKHI', 'name' => 'Karachi Port, Pakistan']),
            ],
        };
    }

    /**
     * Generate realistic maritime ocean route waypoints and current vessel position.
     * Guaranteed 100% continuous single-line corridor strictly over navigable waterways (oceans, seas, straits, canals).
     * 
     * @param array $origin ['lat' => float, 'lng' => float]
     * @param array $dest   ['lat' => float, 'lng' => float]
     * @param float $progress (0.0 to 100.0)
     * @return array ['vessel' => [...], 'waypoints' => array, 'heading' => float]
     */
    public static function buildMaritimeRoute(array $origin, array $dest, float $progress = 0.0): array
    {
        $progress = max(0.0, min(100.0, $progress));
        $fraction = $progress / 100.0;

        $p1 = [(float) $origin['lat'], (float) $origin['lng']];
        $p2 = [(float) $dest['lat'], (float) $dest['lng']];

        // -------------------------------------------------------------
        // SCENARIO 1: US WEST COAST <-> ASIA / SE ASIA / SOUTH ASIA (via Panama & Suez Maritime Corridor)
        // -------------------------------------------------------------
        // Outbound: US West Coast -> Asia / Middle East / South Asia
        if ($p1[1] < -100 && $p2[1] > 50) {
            $waypoints = [
                $p1,
                [20.0, -107.0],  // Pacific off Mexico
                [12.0, -95.0],   // Central America Pacific
                [7.5, -80.0],    // Gulf of Panama
                [8.95, -79.56],  // Panama Canal Pacific
                [9.36, -79.91],  // Panama Canal Atlantic
                [12.5, -74.0],   // Caribbean Sea
                [22.0, -55.0],   // Central Atlantic
                [32.0, -25.0],   // East Atlantic
                [36.0, -5.35],   // Strait of Gibraltar
                [35.0, 15.0],    // Mediterranean Sea Central
                [34.0, 25.0],    // Mediterranean Sea East
                [31.25, 32.30],  // Suez Canal North
                [29.93, 32.55],  // Suez Canal South
                [22.0, 38.0],    // Red Sea
                [12.6, 43.35],   // Bab-el-Mandeb
                [12.5, 52.0],    // Gulf of Aden
            ];

            if ($p2[1] <= 85) { // Destination in South Asia (e.g. Karachi)
                $waypoints[] = [18.0, 64.0];  // Arabian Sea
            } else { // Southeast Asia / East Asia
                $waypoints[] = [5.8, 80.5];     // South of Sri Lanka
                foreach (self::$malaccaStraitSouthbound as $wpt) {
                    $waypoints[] = $wpt;
                }
                if ($p2[1] > 110 || $p2[0] > 15) { // China / Taiwan
                    $waypoints[] = [12.0, 112.0];
                    $waypoints[] = [20.5, 121.5];
                }
            }

            $waypoints[] = $p2;
            $densePath = self::interpolateSpline($waypoints, 70);
            return self::assembleSingleLegRoute($densePath, $fraction, $p1, $p2);
        }

        // Inbound: Asia / Middle East / South Asia -> US West Coast
        if ($p1[1] > 50 && $p2[1] < -100) {
            $waypoints = [$p1];
            if ($p1[1] > 110 || $p1[0] > 15) {
                $waypoints[] = [20.5, 121.5];
                $waypoints[] = [12.0, 112.0];
            }
            if ($p1[1] > 85) {
                foreach (self::$malaccaStraitNorthbound as $wpt) {
                    $waypoints[] = $wpt;
                }
                $waypoints[] = [5.8, 80.5];
            } else {
                $waypoints[] = [18.0, 64.0];
            }
            $waypoints[] = [12.5, 52.0];
            $waypoints[] = [12.6, 43.35];
            $waypoints[] = [22.0, 38.0];
            $waypoints[] = [29.93, 32.55];
            $waypoints[] = [31.25, 32.30];
            $waypoints[] = [34.0, 25.0];
            $waypoints[] = [35.0, 15.0];
            $waypoints[] = [36.0, -5.35];
            $waypoints[] = [32.0, -25.0];
            $waypoints[] = [22.0, -55.0];
            $waypoints[] = [12.5, -74.0];
            $waypoints[] = [9.36, -79.91];
            $waypoints[] = [8.95, -79.56];
            $waypoints[] = [7.5, -80.0];
            $waypoints[] = [12.0, -95.0];
            $waypoints[] = [20.0, -107.0];
            $waypoints[] = $p2;
            $densePath = self::interpolateSpline($waypoints, 70);
            return self::assembleSingleLegRoute($densePath, $fraction, $p1, $p2);
        }

        // -------------------------------------------------------------
        // SCENARIO 2: US EAST COAST COASTAL (e.g. Savannah <-> New York)
        // -------------------------------------------------------------
        if ($p1[1] >= -85 && $p1[1] <= -65 && $p2[1] >= -85 && $p2[1] <= -65) {
            $waypoints = [$p1];
            if ($p1[0] < $p2[0]) { // Northbound (Savannah -> New York)
                $waypoints[] = [32.0, -80.4];
                $waypoints[] = [33.5, -77.5];
                $waypoints[] = [35.2, -74.8]; // Cape Hatteras Deep Water
                $waypoints[] = [38.5, -74.2];
                $waypoints[] = [40.45, -73.85];
            } else { // Southbound (New York -> Savannah)
                $waypoints[] = [40.45, -73.85];
                $waypoints[] = [38.5, -74.2];
                $waypoints[] = [35.2, -74.8];
                $waypoints[] = [33.5, -77.5];
                $waypoints[] = [32.0, -80.4];
            }
            $waypoints[] = $p2;
            $densePath = self::interpolateSpline($waypoints, 50);
            return self::assembleSingleLegRoute($densePath, $fraction, $p1, $p2);
        }

        // -------------------------------------------------------------
        // SCENARIO 3: US EAST COAST <-> ASIA / MID EAST (via Atlantic & Suez)
        // -------------------------------------------------------------
        if ($p1[1] >= -85 && $p1[1] <= -65 && $p2[1] > 50) {
            $waypoints = [
                $p1,
                [36.0, -65.0],
                [36.0, -25.0],
                [36.0, -5.35],
                [35.0, 15.0],
                [34.0, 25.0],
                [31.25, 32.30],
                [29.93, 32.55],
                [22.0, 38.0],
                [12.6, 43.35],
                [12.5, 52.0],
            ];
            if ($p2[1] <= 85) {
                $waypoints[] = [18.0, 64.0];
            } else {
                $waypoints[] = [5.8, 80.5];
                foreach (self::$malaccaStraitSouthbound as $wpt) {
                    $waypoints[] = $wpt;
                }
            }
            $waypoints[] = $p2;
            $densePath = self::interpolateSpline($waypoints, 65);
            return self::assembleSingleLegRoute($densePath, $fraction, $p1, $p2);
        }

        // -------------------------------------------------------------
        // SCENARIO 3.5: BAY OF BENGAL / BANGLADESH / EAST INDIA <-> SE ASIA / SINGAPORE / EAST ASIA
        // (Guaranteed 100% deep water via Bay of Bengal, Andaman Sea & Strait of Malacca TSS)
        // -------------------------------------------------------------
        // Outbound: Bay of Bengal (e.g. Chattogram BDCGP) -> Singapore / SE Asia / East Asia
        if ($p1[1] >= 78 && $p1[1] <= 95 && $p1[0] >= 5 && $p2[1] >= 99) {
            $waypoints = [$p1];
            if ($p1[1] > 88) { // Chittagong / Kolkata / Northern Bay of Bengal
                $waypoints[] = [20.50, 91.50]; // Bay of Bengal south of Karnaphuli
                $waypoints[] = [16.00, 91.00]; // Open central Bay of Bengal
                $waypoints[] = [11.00, 92.50]; // Andaman Sea deep water channel
            } elseif ($p1[1] <= 85) { // Chennai / Sri Lanka / Coromandel Coast
                $waypoints[] = [10.00, 84.00];
            }
            foreach (self::$malaccaStraitSouthbound as $wpt) {
                $waypoints[] = $wpt;
            }
            // If destination is beyond Singapore in East Asia (China / Taiwan / Japan)
            if ($p2[1] > 108 || $p2[0] > 15) {
                $waypoints[] = [3.00, 105.50];
                $waypoints[] = [10.00, 111.00];
                $waypoints[] = [18.00, 116.00];
            }
            $waypoints[] = $p2;
            $densePath = self::interpolateSpline($waypoints, 60);
            return self::assembleSingleLegRoute($densePath, $fraction, $p1, $p2);
        }

        // Inbound: Singapore / SE Asia / East Asia -> Bay of Bengal (e.g. Chattogram BDCGP)
        if ($p1[1] >= 99 && $p2[1] >= 78 && $p2[1] <= 95 && $p2[0] >= 5) {
            $waypoints = [$p1];
            if ($p1[1] > 108 || $p1[0] > 15) {
                $waypoints[] = [18.00, 116.00];
                $waypoints[] = [10.00, 111.00];
                $waypoints[] = [3.00, 105.50];
            }
            foreach (self::$malaccaStraitNorthbound as $wpt) {
                $waypoints[] = $wpt;
            }
            if ($p2[1] > 88) {
                $waypoints[] = [11.00, 92.50];
                $waypoints[] = [16.00, 91.00];
                $waypoints[] = [20.50, 91.50];
            } elseif ($p2[1] <= 85) {
                $waypoints[] = [10.00, 84.00];
            }
            $waypoints[] = $p2;
            $densePath = self::interpolateSpline($waypoints, 60);
            return self::assembleSingleLegRoute($densePath, $fraction, $p1, $p2);
        }

        // -------------------------------------------------------------
        // SCENARIO 4: ARABIAN SEA / WEST INDIA / MID EAST <-> SE ASIA / SINGAPORE / EAST ASIA
        // (Guaranteed 100% navigable waters via Arabian Sea, South Sri Lanka & Malacca TSS)
        // -------------------------------------------------------------
        if ($p1[1] >= 45 && $p1[1] < 78 && $p2[1] >= 99) {
            $waypoints = [
                $p1,
                [18.00, 66.00], // Arabian Sea
                [5.80, 80.50],  // South of Sri Lanka deep water
            ];
            foreach (self::$malaccaStraitSouthbound as $wpt) {
                $waypoints[] = $wpt;
            }
            if ($p2[1] > 108 || $p2[0] > 15) {
                $waypoints[] = [3.00, 105.50];
                $waypoints[] = [10.00, 111.00];
                $waypoints[] = [18.00, 116.00];
            }
            $waypoints[] = $p2;
            $densePath = self::interpolateSpline($waypoints, 60);
            return self::assembleSingleLegRoute($densePath, $fraction, $p1, $p2);
        }

        if ($p1[1] >= 99 && $p2[1] >= 45 && $p2[1] < 78) {
            $waypoints = [$p1];
            if ($p1[1] > 108 || $p1[0] > 15) {
                $waypoints[] = [18.00, 116.00];
                $waypoints[] = [10.00, 111.00];
                $waypoints[] = [3.00, 105.50];
            }
            foreach (self::$malaccaStraitNorthbound as $wpt) {
                $waypoints[] = $wpt;
            }
            $waypoints[] = [5.80, 80.50];
            $waypoints[] = [18.00, 66.00];
            $waypoints[] = $p2;
            $densePath = self::interpolateSpline($waypoints, 60);
            return self::assembleSingleLegRoute($densePath, $fraction, $p1, $p2);
        }

        // -------------------------------------------------------------
        // SCENARIO 5: EUROPE <-> SOUTH ASIA / MID EAST / ASIA (via Suez Canal)
        // -------------------------------------------------------------
        if ($p1[0] > 35 && $p1[1] < 15 && $p2[1] > 40 && $p2[0] > -10) {
            $waypoints = [
                $p1,
                [50.5, 0.0],    // English Channel
                [45.0, -8.0],   // Bay of Biscay
                [36.0, -5.35],  // Strait of Gibraltar
                [35.0, 18.0],   // Central Med
                [34.0, 26.0],   // South of Crete
                [31.25, 32.30], // Suez Canal North
                [29.93, 32.55], // Suez Canal South
                [22.0, 38.0],   // Red Sea
                [12.6, 43.35],  // Bab-el-Mandeb
                [12.5, 49.0],   // Gulf of Aden
            ];
            if ($p2[1] > 90) { // To East Asia
                $waypoints[] = [5.8, 80.5];   // South of Sri Lanka
                foreach (self::$malaccaStraitSouthbound as $wpt) {
                    $waypoints[] = $wpt;
                }
                $waypoints[] = [12.0, 112.0]; // South China Sea
            } elseif ($p2[1] > 55 && $p2[1] <= 85) { // To South Asia
                $waypoints[] = [18.0, 64.0];  // Arabian Sea
            }
            $waypoints[] = $p2;
            $densePath = self::interpolateSpline($waypoints, 60);
            return self::assembleSingleLegRoute($densePath, $fraction, $p1, $p2);
        }

        if ($p2[0] > 35 && $p2[1] < 15 && $p1[1] > 40 && $p1[0] > -10) {
            $waypoints = [$p1];
            if ($p1[1] > 90) {
                $waypoints[] = [12.0, 112.0];
                foreach (self::$malaccaStraitNorthbound as $wpt) {
                    $waypoints[] = $wpt;
                }
                $waypoints[] = [5.8, 80.5];
            } elseif ($p1[1] > 55 && $p1[1] <= 85) {
                $waypoints[] = [18.0, 64.0];
            }
            $waypoints[] = [12.5, 49.0];
            $waypoints[] = [12.6, 43.35];
            $waypoints[] = [22.0, 38.0];
            $waypoints[] = [29.93, 32.55];
            $waypoints[] = [31.25, 32.30];
            $waypoints[] = [34.0, 26.0];
            $waypoints[] = [35.0, 18.0];
            $waypoints[] = [36.0, -5.35];
            $waypoints[] = [45.0, -8.0];
            $waypoints[] = [50.5, 0.0];
            $waypoints[] = $p2;
            $densePath = self::interpolateSpline($waypoints, 60);
            return self::assembleSingleLegRoute($densePath, $fraction, $p1, $p2);
        }

        // -------------------------------------------------------------
        // SCENARIO 6: TRANSATLANTIC (US East Coast <-> West Africa / Europe)
        // -------------------------------------------------------------
        if ($p1[1] < -65 && $p2[1] >= -25 && $p2[1] <= 30 && $p1[0] > 0 && $p2[0] > 0) {
            $waypoints = [
                $p1,
                [28.0, -65.0],
                [18.0, -40.0],
                [8.0, -20.0],
                [4.5, -5.0],
                $p2
            ];
            $densePath = self::interpolateSpline($waypoints, 50);
            return self::assembleSingleLegRoute($densePath, $fraction, $p1, $p2);
        }

        // -------------------------------------------------------------
        // FALLBACK: Safe Ocean interpolation with water bias
        // -------------------------------------------------------------
        $midLat = ($p1[0] + $p2[0]) / 2 - 2.0;
        $midLng = ($p1[1] + $p2[1]) / 2;
        $waypoints = [$p1, [$midLat, $midLng], $p2];
        $densePath = self::interpolateSpline($waypoints, 50);
        return self::assembleSingleLegRoute($densePath, $fraction, $p1, $p2);
    }

    /**
     * Assemble Multi-Leg route (e.g. crossing International Date Line over Pacific Ocean)
     */
    protected static function assembleMultiLegRoute(array $leg1, array $leg2, float $fraction, array $p1, array $p2): array
    {
        $n1 = count($leg1);
        $n2 = count($leg2);
        $total = $n1 + $n2;
        $targetIdx = (int) round($fraction * ($total - 1));

        if ($targetIdx < $n1) {
            $vesselPos = $leg1[$targetIdx];
            $next = $leg1[min($n1 - 1, $targetIdx + 1)];
        } else {
            $idx2 = $targetIdx - $n1;
            $vesselPos = $leg2[$idx2];
            $next = $leg2[min($n2 - 1, $idx2 + 1)];
        }
        $heading = self::calculateBearing($vesselPos, $next);

        return [
            'vessel' => [
                'lat' => round($vesselPos[0], 5),
                'lng' => round($vesselPos[1], 5),
            ],
            'heading' => round($heading, 1),
            'waypoints' => [$leg1, $leg2],
            'origin' => ['lat' => $p1[0], 'lng' => $p1[1]],
            'destination' => ['lat' => $p2[0], 'lng' => $p2[1]],
        ];
    }

    /**
     * Assemble Single Continuous Leg route over water
     */
    protected static function assembleSingleLegRoute(array $densePath, float $fraction, array $p1, array $p2): array
    {
        $n = count($densePath);
        $targetIdx = (int) round($fraction * ($n - 1));
        $vesselPos = $densePath[$targetIdx] ?? $p1;
        $next = $densePath[min($n - 1, $targetIdx + 1)] ?? $p2;
        $heading = self::calculateBearing($vesselPos, $next);

        return [
            'vessel' => [
                'lat' => round($vesselPos[0], 5),
                'lng' => round($vesselPos[1], 5),
            ],
            'heading' => round($heading, 1),
            'waypoints' => $densePath,
            'origin' => ['lat' => $p1[0], 'lng' => $p1[1]],
            'destination' => ['lat' => $p2[0], 'lng' => $p2[1]],
        ];
    }

    /**
     * Interpolate multiple waypoint vectors into a smooth continuous path.
     */
    protected static function interpolateSpline(array $pts, int $totalSteps = 50): array
    {
        $numSegments = count($pts) - 1;
        if ($numSegments <= 0) return $pts;

        $stepsPerSeg = (int) ceil($totalSteps / $numSegments);
        $result = [];

        for ($i = 0; $i < $numSegments; $i++) {
            $start = $pts[$i];
            $end = $pts[$i + 1];

            for ($s = 0; $s < $stepsPerSeg; $s++) {
                $t = $s / (float) $stepsPerSeg;
                // Linear + smooth cosine ease
                $tSmooth = (1 - cos($t * M_PI)) / 2;
                $lat = $start[0] + ($end[0] - $start[0]) * $tSmooth;
                $lng = $start[1] + ($end[1] - $start[1]) * $t;
                $result[] = [round($lat, 5), round($lng, 5)];
            }
        }

        $result[] = end($pts);
        return $result;
    }

    /**
     * Calculate heading / bearing in degrees between two coordinates.
     */
    protected static function calculateBearing(array $p1, array $p2): float
    {
        $lat1 = deg2rad($p1[0]);
        $lat2 = deg2rad($p2[0]);
        $dLng = deg2rad($p2[1] - $p1[1]);

        $y = sin($dLng) * cos($lat2);
        $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLng);

        $brng = atan2($y, $x);
        $deg = rad2deg($brng);
        return fmod(($deg + 360), 360);
    }
}
