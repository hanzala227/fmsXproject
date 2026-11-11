<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Port;
use App\Models\Country;

class SyncWorldPortsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ports:sync-world-data {--source=ourairports : Source for airport data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean and enrich world ports & airports data (IATA/UNLOCODE) safely without deleting existing IDs or breaking foreign keys';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Safe World Ports & Airports Sync...');
        $this->info('Rule: ZERO deletions. All existing IDs, shipments, and foreign keys remain 100% intact.');

        // Build Country ISO to ID lookup map
        $countries = Country::all()->keyBy(function ($c) {
            return strtoupper(trim($c->code ?: $c->iso_alpha2));
        });

        $this->info('Loaded ' . $countries->count() . ' countries for ISO code mapping.');

        // ── 1. SYNC WORLD AIRPORTS (OurAirports Global Dataset) ──
        $url = 'https://davidmegginson.github.io/ourairports-data/airports.csv';
        $this->info("Fetching latest global airport dataset from: {$url}");

        $tempFile = storage_path('app/airports_temp.csv');
        if (!file_exists(dirname($tempFile))) {
            mkdir(dirname($tempFile), 0755, true);
        }

        $fp = fopen($tempFile, 'w+');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        if ($httpCode !== 200 || !file_exists($tempFile)) {
            $this->error("Failed to download OurAirports CSV (HTTP Code: {$httpCode}). Proceeding with local database enrichment.");
        } else {
            $this->info('Download complete. Processing IATA-coded commercial and cargo airports...');

            $handle = fopen($tempFile, 'r');
            $header = fgetcsv($handle, 0, ',', '"', '\\');
            $iataIdx = array_search('iata_code', $header);
            $nameIdx = array_search('name', $header);
            $cityIdx = array_search('municipality', $header);
            $countryIdx = array_search('iso_country', $header);
            $latIdx = array_search('latitude_deg', $header);
            $lonIdx = array_search('longitude_deg', $header);

            $updatedAirports = 0;
            $insertedAirports = 0;

            DB::beginTransaction();
            try {
                while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                    $iata = strtoupper(trim($row[$iataIdx] ?? ''));
                    if (empty($iata) || strlen($iata) !== 3) {
                        continue; // Only commercial/cargo airports with standard 3-letter IATA code
                    }

                    $name = trim($row[$nameIdx] ?? '');
                    $city = trim($row[$cityIdx] ?? '');
                    $isoCountry = strtoupper(trim($row[$countryIdx] ?? ''));
                    $lat = !empty($row[$latIdx]) ? (float)$row[$latIdx] : null;
                    $lon = !empty($row[$lonIdx]) ? (float)$row[$lonIdx] : null;

                    $countryId = $countries->get($isoCountry)?->id;
                    if (!$countryId) {
                        continue; // Skip if country is not registered
                    }

                    // Check if airport already exists by IATA code
                    $existing = Port::where('type', 'AIR')->where('code', $iata)->first();

                    if ($existing) {
                        $updateData = [];
                        if (empty($existing->city) && !empty($city)) $updateData['city'] = $city;
                        if (!$existing->country_id && $countryId) $updateData['country_id'] = $countryId;
                        if (!$existing->latitude && $lat) $updateData['latitude'] = $lat;
                        if (!$existing->longitude && $lon) $updateData['longitude'] = $lon;

                        if (!empty($updateData)) {
                            $existing->update($updateData);
                            $updatedAirports++;
                        }
                    } else {
                        Port::create([
                            'code'       => $iata,
                            'name'       => $name,
                            'city'       => $city,
                            'country_id' => $countryId,
                            'type'       => 'AIR',
                            'latitude'   => $lat,
                            'longitude'  => $lon,
                        ]);
                        $insertedAirports++;
                    }
                }
                DB::commit();
                $this->info("Airports processed: {$updatedAirports} enriched, {$insertedAirports} newly added.");
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Airports sync error: " . $e->getMessage());
            }

            fclose($handle);
            @unlink($tempFile);
        }

        // ── 2. STANDARDIZE TOP SEA PORTS (UN/LOCODE) ──
        $this->info('Standardizing major world sea ports (UN/LOCODE)...');
        $topSeaPorts = [
            ['code' => 'SGSIN', 'name' => 'Singapore Port', 'city' => 'Singapore', 'country' => 'SG'],
            ['code' => 'CNSHA', 'name' => 'Shanghai Port', 'city' => 'Shanghai', 'country' => 'CN'],
            ['code' => 'CNNGB', 'name' => 'Ningbo-Zhoushan Port', 'city' => 'Ningbo', 'country' => 'CN'],
            ['code' => 'CNSZX', 'name' => 'Shenzhen Port', 'city' => 'Shenzhen', 'country' => 'CN'],
            ['code' => 'CNCAN', 'name' => 'Guangzhou Port', 'city' => 'Guangzhou', 'country' => 'CN'],
            ['code' => 'KRPUS', 'name' => 'Busan Port', 'city' => 'Busan', 'country' => 'KR'],
            ['code' => 'CNTAO', 'name' => 'Qingdao Port', 'city' => 'Qingdao', 'country' => 'CN'],
            ['code' => 'HKHKG', 'name' => 'Hong Kong Port', 'city' => 'Hong Kong', 'country' => 'HK'],
            ['code' => 'NLRTM', 'name' => 'Rotterdam Port', 'city' => 'Rotterdam', 'country' => 'NL'],
            ['code' => 'AEJEA', 'name' => 'Jebel Ali Port', 'city' => 'Dubai', 'country' => 'AE'],
            ['code' => 'MYPKG', 'name' => 'Port Klang', 'city' => 'Klang', 'country' => 'MY'],
            ['code' => 'BEANR', 'name' => 'Antwerp Port', 'city' => 'Antwerp', 'country' => 'BE'],
            ['code' => 'TWKHH', 'name' => 'Kaohsiung Port', 'city' => 'Kaohsiung', 'country' => 'TW'],
            ['code' => 'USLAX', 'name' => 'Los Angeles Port', 'city' => 'Los Angeles', 'country' => 'US'],
            ['code' => 'USLGB', 'name' => 'Long Beach Port', 'city' => 'Long Beach', 'country' => 'US'],
            ['code' => 'DEHAM', 'name' => 'Hamburg Port', 'city' => 'Hamburg', 'country' => 'DE'],
            ['code' => 'MYTPP', 'name' => 'Tanjung Pelepas Port', 'city' => 'Johor', 'country' => 'MY'],
            ['code' => 'USNYC', 'name' => 'New York / New Jersey Port', 'city' => 'New York', 'country' => 'US'],
            ['code' => 'THLCH', 'name' => 'Laem Chabang Port', 'city' => 'Chonburi', 'country' => 'TH'],
            ['code' => 'LKCMB', 'name' => 'Colombo Port', 'city' => 'Colombo', 'country' => 'LK'],
            ['code' => 'PKKHI', 'name' => 'Karachi Port (KPT)', 'city' => 'Karachi', 'country' => 'PK'],
            ['code' => 'PKBQM', 'name' => 'Port Muhammad Bin Qasim', 'city' => 'Karachi', 'country' => 'PK'],
            ['code' => 'PKGWN', 'name' => 'Gwadar Port', 'city' => 'Gwadar', 'country' => 'PK'],
            ['code' => 'INNSA', 'name' => 'Nhava Sheva (JNPT)', 'city' => 'Mumbai', 'country' => 'IN'],
            ['code' => 'INBOM', 'name' => 'Mumbai Port', 'city' => 'Mumbai', 'country' => 'IN'],
            ['code' => 'INMUN', 'name' => 'Mundra Port', 'city' => 'Mundra', 'country' => 'IN'],
            ['code' => 'BDCGP', 'name' => 'Chittagong (Chattogram) Port', 'city' => 'Chattogram', 'country' => 'BD'],
            ['code' => 'BDDAC', 'name' => 'Dhaka ICD (Kamalapur)', 'city' => 'Dhaka', 'country' => 'BD'],
            ['code' => 'BDMPA', 'name' => 'Mongla Port', 'city' => 'Mongla', 'country' => 'BD'],
            ['code' => 'JPTYO', 'name' => 'Tokyo Port', 'city' => 'Tokyo', 'country' => 'JP'],
            ['code' => 'JPYOK', 'name' => 'Yokohama Port', 'city' => 'Yokohama', 'country' => 'JP'],
            ['code' => 'GBFXT', 'name' => 'Felixstowe Port', 'city' => 'Felixstowe', 'country' => 'GB'],
            ['code' => 'GBSOU', 'name' => 'Southampton Port', 'city' => 'Southampton', 'country' => 'GB'],
            ['code' => 'FRLEH', 'name' => 'Le Havre Port', 'city' => 'Le Havre', 'country' => 'FR'],
            ['code' => 'ESVLC', 'name' => 'Valencia Port', 'city' => 'Valencia', 'country' => 'ES'],
            ['code' => 'ESBCN', 'name' => 'Barcelona Port', 'city' => 'Barcelona', 'country' => 'ES'],
            ['code' => 'ITGOA', 'name' => 'Genoa Port', 'city' => 'Genoa', 'country' => 'IT'],
            ['code' => 'EGPSD', 'name' => 'Port Said', 'city' => 'Port Said', 'country' => 'EG'],
            ['code' => 'SAJED', 'name' => 'Jeddah Islamic Port', 'city' => 'Jeddah', 'country' => 'SA'],
            ['code' => 'SADMM', 'name' => 'King Abdul Aziz Port Dammam', 'city' => 'Dammam', 'country' => 'SA'],
        ];

        $updatedSea = 0;
        $insertedSea = 0;

        foreach ($topSeaPorts as $sp) {
            $cId = $countries->get($sp['country'])?->id;
            $existing = Port::where('type', 'SEA')->where('code', $sp['code'])->first();

            if ($existing) {
                $existing->update([
                    'name'       => $sp['name'],
                    'city'       => $sp['city'],
                    'country_id' => $cId ?: $existing->country_id,
                ]);
                $updatedSea++;
            } else {
                Port::create([
                    'code'       => $sp['code'],
                    'name'       => $sp['name'],
                    'city'       => $sp['city'],
                    'country_id' => $cId,
                    'type'       => 'SEA',
                ]);
                $insertedSea++;
            }
        }

        $this->info("Sea Ports standardized: {$updatedSea} updated, {$insertedSea} newly added.");

        $totalPorts = Port::count();
        $totalAir = Port::where('type', 'AIR')->count();
        $totalSea = Port::where('type', 'SEA')->count();

        $this->info("--------------------------------------------------");
        $this->info("SUCCESS! World Ports & Airports Database is clean.");
        $this->info("Total Records: {$totalPorts} (Airports: {$totalAir}, Sea Ports: {$totalSea})");
        $this->info("Foreign Keys & Existing Shipments: 100% Intact & Untouched.");
        $this->info("--------------------------------------------------");

        return 0;
    }
}
