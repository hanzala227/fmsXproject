<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ShipmentTracking extends Model
{
    use HasFactory;

    protected $table = 'shipment_trackings';

    protected $fillable = [
        'company_id',
        'trackable_type',
        'trackable_id',
        'carrier_code',
        'tracking_type',
        'tracking_number',
        'current_status',
        'status_description',
        'progress_percentage',
        'origin_locode',
        'origin_name',
        'destination_locode',
        'destination_name',
        'vessel_name',
        'voyage_no',
        'etd',
        'atd',
        'eta',
        'ata',
        'last_polled_at',
        'raw_payload',
    ];

    protected $casts = [
        'progress_percentage' => 'integer',
        'etd' => 'datetime',
        'atd' => 'datetime',
        'eta' => 'datetime',
        'ata' => 'datetime',
        'last_polled_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    protected $appends = [
        'status_badge',
        'map_data',
        'display_origin',
        'display_destination',
        'container_type_label',
        'carrier_badge',
        'journey_steps',
        'eta_display',
        'route_display',
    ];

    /**
     * Polymorphic relation to shipment (OceanImport, OceanExport, etc.)
     */
    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Chronological tracking events.
     */
    public function events(): HasMany
    {
        return $this->hasMany(TrackingEvent::class, 'shipment_tracking_id')
            ->orderBy('event_date_time', 'desc');
    }

    /**
     * Chronological tracking events ascending (for journey playback).
     */
    public function timelineEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class, 'shipment_tracking_id')
            ->orderBy('event_date_time', 'asc');
    }

    /**
     * Badge CSS class helper based on status.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match (strtolower((string) $this->current_status)) {
            'delivered', 'completed' => ['bg' => '#dcfce7', 'color' => '#15803d', 'label' => 'DELIVERED', 'step' => 5],
            'gate_out' => ['bg' => '#fef3c7', 'color' => '#b45309', 'label' => 'GATE OUT', 'step' => 4],
            'discharged', 'arrived' => ['bg' => '#e0e7ff', 'color' => '#4338ca', 'label' => 'DISCHARGED', 'step' => 4],
            'in_transit', 'loaded' => ['bg' => '#dbeafe', 'color' => '#1d4ed8', 'label' => 'IN TRANSIT', 'step' => 3],
            'gate_in' => ['bg' => '#f3e8ff', 'color' => '#7e22ce', 'label' => 'GATE IN', 'step' => 2],
            default => ['bg' => '#f1f5f9', 'color' => '#475569', 'label' => 'BOOKED', 'step' => 1],
        };
    }

    /**
     * Map visualization payload for live vessel and route plotting.
     */
    public function getMapDataAttribute(): array
    {
        $originLoc = $this->origin_locode ?: ($this->trackable?->pol?->code ?? null);
        $originName = $this->origin_name ?: ($this->trackable?->pol?->name ?? null);
        $destLoc = $this->destination_locode ?: ($this->trackable?->pod?->code ?? null);
        $destName = $this->destination_name ?: ($this->trackable?->pod?->name ?? null);

        $origin = \App\Services\Tracking\PortCoordinatesResolver::resolve($originLoc, $originName);
        $dest = \App\Services\Tracking\PortCoordinatesResolver::resolve($destLoc, $destName);

        $etdDate = $this->etd ?: ($this->trackable?->etd ?? null);
        if ($etdDate && !($etdDate instanceof \Carbon\CarbonInterface)) {
            $etdDate = \Carbon\Carbon::parse($etdDate);
        }
        $etaDate = $this->eta ?: ($this->trackable?->eta ?? null);
        if ($etaDate && !($etaDate instanceof \Carbon\CarbonInterface)) {
            $etaDate = \Carbon\Carbon::parse($etaDate);
        }
        $vesselName = $this->vessel_name ?: ($this->trackable?->vessel?->name ?? ($this->trackable?->vessel_name ?? null));
        $voyageNo = $this->voyage_no ?: ($this->trackable?->voyage ?? ($this->trackable?->voyage_no ?? null));

        if (!$origin || !$dest) {
            return [
                'has_route' => false,
                'is_pending_ports' => true,
                'origin' => [
                    'code' => $originLoc ?: '',
                    'label' => $originName ?: 'Awaiting Port Scans',
                    'date' => $etdDate ? $etdDate->format('M d, Y') : 'ETD TBA',
                ],
                'destination' => [
                    'code' => $destLoc ?: '',
                    'label' => $destName ?: 'Awaiting Port Scans',
                    'date' => $etaDate ? $etaDate->format('M d, Y') : 'ETA TBA',
                ],
                'vessel' => [
                    'name' => $vesselName ?: ($this->carrier_code ? "{$this->carrier_code} Vessel" : 'Ocean Carrier'),
                    'voyage' => $voyageNo ?: '',
                    'lat' => null,
                    'lng' => null,
                    'heading' => 0,
                    'status' => $this->current_status ?: 'booked',
                    'progress' => 0,
                ],
                'waypoints' => [],
            ];
        }

        $progress = (float) ($this->progress_percentage ?? 0);
        if ($this->current_status === 'booked' || empty($this->current_status)) {
            $progress = 0.0;
        }

        $route = \App\Services\Tracking\PortCoordinatesResolver::buildMaritimeRoute($origin, $dest, $progress);

        $carrierName = !empty($this->raw_payload['carrierName']) ? $this->raw_payload['carrierName'] : ($this->carrier_code ?: 'Ocean Carrier');
        $resolvedVessel = $vesselName ?: ($carrierName . ' Vessel');

        // Extract terminal info for POD and POL from raw_payload
        $podTerminal = '';
        $polTerminal = '';
        if (!empty($this->raw_payload['containers'][0]['locations']) && is_array($this->raw_payload['containers'][0]['locations'])) {
            foreach ($this->raw_payload['containers'][0]['locations'] as $loc) {
                if (($loc['locationType'] ?? '') === 'lpod') {
                    $term = $loc['terminalName'] ?? '';
                    $code = $loc['terminalCode'] ?? '';
                    foreach ($loc['events'] ?? [] as $ev) {
                        if (!empty($ev['terminalName']) && empty($term)) {
                            $term = $ev['terminalName'];
                            $code = $ev['terminalCode'] ?? $code;
                        }
                    }
                    $city = ucfirst(strtolower($loc['city'] ?? ''));
                    $podTerminal = $term . ($code ? " ({$code})" : '') . ($city ? ", {$city}" : '');
                } elseif (($loc['locationType'] ?? '') === 'fpol') {
                    $term = $loc['terminalName'] ?? '';
                    $code = $loc['terminalCode'] ?? '';
                    foreach ($loc['events'] ?? [] as $ev) {
                        if (!empty($ev['terminalName']) && empty($term)) {
                            $term = $ev['terminalName'];
                            $code = $ev['terminalCode'] ?? $code;
                        }
                    }
                    $city = ucfirst(strtolower($loc['city'] ?? ''));
                    $polTerminal = $term . ($code ? " ({$code})" : '') . ($city ? ", {$city}" : '');
                }
            }
        }
        if (empty($podTerminal)) {
            $podTerminal = $destName ?: ($dest['name'] ?? 'Destination Port');
        }
        if (empty($polTerminal)) {
            $polTerminal = $originName ?: ($origin['name'] ?? 'Origin Port');
        }

        return [
            'has_route' => true,
            'is_pending_ports' => false,
            'origin' => array_merge($origin, [
                'code' => $originLoc ?: ($origin['code'] ?? ''),
                'label' => $originName ?: $origin['name'],
                'date' => $etdDate ? $etdDate->format('M d, Y') : 'ETD TBA',
                'terminal' => $polTerminal,
            ]),
            'destination' => array_merge($dest, [
                'code' => $destLoc ?: ($dest['code'] ?? ''),
                'label' => $destName ?: $dest['name'],
                'date' => $etaDate ? $etaDate->format('M d, Y') : 'ETA TBA',
                'terminal' => $podTerminal,
            ]),
            'vessel' => [
                'name' => $resolvedVessel,
                'voyage' => $voyageNo ?: 'Voyage Active',
                'lat' => $route['vessel']['lat'],
                'lng' => $route['vessel']['lng'],
                'heading' => $route['heading'],
                'status' => $this->current_status ?: 'booked',
                'progress' => (int) $progress,
            ],
            'waypoints' => $route['waypoints'],
        ];
    }

    /**
     * Helper to convert 2-letter ISO country code or 5-letter UN/LOCODE into Unicode flag emoji.
     */
    public static function getCountryFlagEmoji(?string $code): string
    {
        if (!$code) return '🌐';
        $clean = strtoupper(trim($code));
        $country = strlen($clean) >= 2 ? substr($clean, 0, 2) : $clean;
        if (strlen($country) !== 2 || !ctype_alpha($country)) {
            return '🌐';
        }
        return mb_chr(127397 + ord($country[0]), 'UTF-8') . mb_chr(127397 + ord($country[1]), 'UTF-8');
    }

    /**
     * Container type and size formatted string (e.g. 40'HQ, 40' General Purpose).
     */
    public function getContainerTypeLabelAttribute(): string
    {
        $cnt = $this->raw_payload['containers'][0] ?? [];
        $size = strtolower((string) ($cnt['containerSize'] ?? ''));
        $type = strtolower((string) ($cnt['containerType'] ?? ''));

        if (str_contains($size, '40') && (str_contains($size, 'hc') || str_contains($size, 'hq') || str_contains($type, 'high'))) {
            return "40'HQ";
        }
        if (str_contains($size, '40') && (str_contains($size, 'gp') || str_contains($type, 'dry') || $type === 'standard')) {
            return "40' General Purpose";
        }
        if (str_contains($size, '20') && (str_contains($size, 'gp') || str_contains($type, 'dry'))) {
            return "20' General Purpose";
        }
        if (str_contains($size, '45')) {
            return "45' High Cube";
        }
        if (str_contains($size, 'rf') || str_contains($type, 'reefer')) {
            return "40' Reefer";
        }

        // Check linked shipment container records
        if ($this->trackable && method_exists($this->trackable, 'containers') && $this->trackable->containers) {
            $matched = $this->trackable->containers->firstWhere('container_no', $this->tracking_number)
                ?: $this->trackable->containers->first();
            if ($matched && $matched->containerType) {
                $code = strtoupper((string) ($matched->containerType->code ?? ''));
                $desc = strtoupper((string) ($matched->containerType->description ?? ''));
                if (str_contains($code, '40HC') || str_contains($code, '40HQ') || str_contains($desc, 'HIGH CUBE')) {
                    return "40'HQ";
                }
                if (str_contains($code, '40GP') || str_contains($code, '40DC')) {
                    return "40' General Purpose";
                }
                if (str_contains($code, '20GP') || str_contains($code, '20DC')) {
                    return "20' General Purpose";
                }
                if (!empty($matched->containerType->name)) {
                    return $matched->containerType->name;
                }
            }
        }

        if (!empty($size) || !empty($type)) {
            return trim(strtoupper($size) . ' ' . ucfirst($type));
        }
        return "40'HQ";
    }

    /**
     * Carrier badge branding with exact colors and logos.
     */
    public function getCarrierBadgeAttribute(): array
    {
        $code = strtoupper((string) ($this->carrier_code ?: 'MSCU'));
        $map = [
            'MSCU' => ['name' => 'MSC', 'short' => 'msc', 'bg' => '#ffcc00', 'color' => '#000000', 'border' => '#eab308'],
            'MAEU' => ['name' => 'Maersk', 'short' => 'maersk', 'bg' => '#002444', 'color' => '#ffffff', 'border' => '#002444'],
            'CMDU' => ['name' => 'CMA CGM', 'short' => 'cma', 'bg' => '#00256c', 'color' => '#ffffff', 'border' => '#00256c'],
            'COSU' => ['name' => 'COSCO', 'short' => 'cosco', 'bg' => '#0284c7', 'color' => '#ffffff', 'border' => '#0284c7'],
            'HLCU' => ['name' => 'Hapag-Lloyd', 'short' => 'hl', 'bg' => '#ff6600', 'color' => '#ffffff', 'border' => '#ea580c'],
            'ONEY' => ['name' => 'ONE', 'short' => 'one', 'bg' => '#ff1493', 'color' => '#ffffff', 'border' => '#db2777'],
            'EGLV' => ['name' => 'Evergreen', 'short' => 'evergreen', 'bg' => '#008040', 'color' => '#ffffff', 'border' => '#008040'],
            'HDMU' => ['name' => 'HMM', 'short' => 'hmm', 'bg' => '#003399', 'color' => '#ffffff', 'border' => '#003399'],
            'YMLU' => ['name' => 'Yang Ming', 'short' => 'yml', 'bg' => '#dc2626', 'color' => '#ffffff', 'border' => '#b91c1c'],
            'ZIMU' => ['name' => 'ZIM', 'short' => 'zim', 'bg' => '#1e3a8a', 'color' => '#ffffff', 'border' => '#1e3a8a'],
            'WHLC' => ['name' => 'Wan Hai', 'short' => 'whl', 'bg' => '#0284c7', 'color' => '#ffffff', 'border' => '#0284c7'],
        ];

        return $map[$code] ?? [
            'name' => $this->raw_payload['carrierName'] ?? ($this->carrier_code ?: 'CARRIER'),
            'short' => strtolower(substr($code, 0, 4)),
            'bg' => '#2563eb',
            'color' => '#ffffff',
            'border' => '#1d4ed8',
        ];
    }

    /**
     * Vertical Journey Stepper parsed dynamically from ShipmentBeat telemetry.
     */
    public function getJourneyStepsAttribute(): array
    {
        $steps = [];
        $cnt = $this->raw_payload['containers'][0] ?? null;
        $locations = $cnt['locations'] ?? [];

        $origLoc = $this->origin_locode ?: ($this->trackable?->pol?->code ?? null);
        $origName = $this->origin_name ?: ($this->trackable?->pol?->name ?? 'Awaiting Port Scans');
        $destLoc = $this->destination_locode ?: ($this->trackable?->pod?->code ?? null);
        $destName = $this->destination_name ?: ($this->trackable?->pod?->name ?? 'Awaiting Port Scans');
        $vesselName = $this->vessel_name ?: ($this->trackable?->vessel?->name ?? ($this->trackable?->vessel_name ?? null));
        $origFlag = self::getCountryFlagEmoji($origLoc);
        $destFlag = self::getCountryFlagEmoji($destLoc);

        // Clean port city labels
        $origCity = $origName;
        $destCity = $destName;
        if (!empty($this->trackable?->pol?->city)) {
            $origCity = $this->trackable->pol->city . ', ' . ($this->trackable->pol->country?->name ?? 'Bangladesh');
        } elseif (str_contains($origName, '(')) {
            if (preg_match('/\(([^)]+)\)/', $origName, $m)) {
                $origCity = trim($m[1]) . ', Bangladesh';
            }
        }
        if (!empty($this->trackable?->pod?->city)) {
            $destCity = $this->trackable->pod->city . ', ' . ($this->trackable->pod->country?->name ?? 'Singapore');
        }

        $hasPol = false;
        $hasVesselStep = false;
        $hasPod = false;
        $hasDel = false;

        if (!empty($locations) && is_array($locations)) {
            foreach ($locations as $loc) {
                $type = strtolower((string) ($loc['locationType'] ?? ''));
                $locPort = strtoupper(trim((string) ($loc['portCode'] ?? '')));
                $city = ucwords(strtolower($loc['city'] ?? ($loc['locationName'] ?? '')));
                $cc = $loc['countryCode'] ?? (strlen($locPort) >= 2 ? substr($locPort, 0, 2) : '');
                $flag = self::getCountryFlagEmoji($cc);

                $term = $loc['terminalName'] ?? '';
                $events = $loc['events'] ?? [];
                foreach ($events as $ev) {
                    if (!empty($ev['terminalName']) && empty($term)) $term = $ev['terminalName'];
                }

                // If type is empty, infer from port or events
                if (empty($type)) {
                    if ($locPort && $origLoc && $locPort === strtoupper($origLoc)) {
                        $type = 'fpol';
                    } elseif ($locPort && $destLoc && $locPort === strtoupper($destLoc)) {
                        $type = 'lpod';
                    } else {
                        foreach ($events as $ev) {
                            $st = strtoupper((string) ($ev['status'] ?? ''));
                            if (in_array($st, ['LOADED', 'GATE_IN', 'DEPARTED'])) {
                                $type = 'fpol';
                                break;
                            } elseif (in_array($st, ['DISCHARGED', 'ARRIVED', 'GATE_OUT'])) {
                                $type = 'lpod';
                                break;
                            }
                        }
                    }
                }

                if ($type === 'por') {
                    $steps[] = [
                        'type' => 'por',
                        'label' => 'Place of Receipt',
                        'city' => $city ?: $origCity,
                        'flag' => $flag ?: $origFlag,
                        'terminal' => $term ?: $origName,
                        'status' => 'completed',
                    ];
                } elseif ($type === 'fpol') {
                    $hasPol = true;
                    $steps[] = [
                        'type' => 'fpol',
                        'label' => 'POL',
                        'city' => $city ?: $origCity,
                        'flag' => $flag ?: $origFlag,
                        'terminal' => $term ?: $origName,
                        'status' => 'completed',
                    ];

                    if (!empty($vesselName)) {
                        $hasVesselStep = true;
                        $steps[] = [
                            'type' => 'vessel',
                            'label' => 'At sea on ' . $vesselName,
                            'city' => null,
                            'flag' => null,
                            'terminal' => null,
                            'status' => in_array($this->current_status, ['in_transit', 'loaded']) ? 'active' : 'upcoming',
                        ];
                    }
                } elseif ($type === 'lpod') {
                    $hasPod = true;
                    if (!$hasVesselStep && !empty($vesselName)) {
                        $hasVesselStep = true;
                        $steps[] = [
                            'type' => 'vessel',
                            'label' => 'At sea on ' . $vesselName,
                            'city' => null,
                            'flag' => null,
                            'terminal' => null,
                            'status' => in_array($this->current_status, ['in_transit', 'loaded']) ? 'active' : 'upcoming',
                        ];
                    }
                    $steps[] = [
                        'type' => 'lpod',
                        'label' => 'POD',
                        'city' => $city ?: $destCity,
                        'flag' => $flag ?: $destFlag,
                        'terminal' => $term ?: $destName,
                        'status' => in_array($this->current_status, ['discharged', 'delivered']) ? 'completed' : 'upcoming',
                    ];
                } elseif ($type === 'del' || $type === 'fdel') {
                    $hasDel = true;
                    $steps[] = [
                        'type' => 'del',
                        'label' => 'Place of Delivery',
                        'city' => $city ?: $destCity,
                        'flag' => $flag ?: $destFlag,
                        'terminal' => $term ?: $destName,
                        'status' => $this->current_status === 'delivered' ? 'completed' : 'upcoming',
                    ];
                }
            }
        }

        // Fill any missing stages from shipment booking
        if (!$hasPol) {
            array_unshift($steps, [
                'type' => 'fpol',
                'label' => 'POL',
                'city' => $origCity,
                'flag' => $origFlag,
                'terminal' => $origName,
                'status' => in_array($this->current_status, ['loaded', 'in_transit', 'discharged', 'delivered']) ? 'completed' : 'upcoming',
            ]);
        }

        if (!$hasVesselStep && !empty($vesselName)) {
            // Insert vessel step immediately after POL
            $polIndex = 0;
            foreach ($steps as $k => $s) {
                if ($s['type'] === 'fpol') { $polIndex = $k; break; }
            }
            array_splice($steps, $polIndex + 1, 0, [[
                'type' => 'vessel',
                'label' => 'At sea on ' . $vesselName,
                'city' => null,
                'flag' => null,
                'terminal' => null,
                'status' => in_array($this->current_status, ['in_transit', 'loaded']) ? 'active' : 'upcoming',
            ]]);
        }

        if (!$hasPod) {
            $steps[] = [
                'type' => 'lpod',
                'label' => 'POD',
                'city' => $destCity,
                'flag' => $destFlag,
                'terminal' => $destName,
                'status' => in_array($this->current_status, ['discharged', 'delivered']) ? 'completed' : 'upcoming',
            ];
        }

        if (!$hasDel) {
            $steps[] = [
                'type' => 'del',
                'label' => 'Place of Delivery',
                'city' => $destCity,
                'flag' => $destFlag,
                'terminal' => $destName,
                'status' => $this->current_status === 'delivered' ? 'completed' : 'upcoming',
            ];
        }

        return $steps;
    }

    /**
     * ETA presentation formatted with city and local time label (e.g. ETA • Tema | Nov 30, 2026 LT).
     */
    public function getEtaDisplayAttribute(): array
    {
        $destCity = 'Destination';
        if (!empty($this->raw_payload['containers'][0]['locations'])) {
            foreach ($this->raw_payload['containers'][0]['locations'] as $loc) {
                if (in_array($loc['locationType'] ?? '', ['lpod', 'del', 'fdel']) && !empty($loc['city'])) {
                    $destCity = ucfirst(strtolower($loc['city']));
                    break;
                }
            }
        }
        if ($destCity === 'Destination') {
            if (!empty($this->trackable?->pod?->city)) {
                $destCity = $this->trackable->pod->city;
            } elseif (!empty($this->destination_name)) {
                $parts = explode(',', $this->destination_name);
                $destCity = trim(str_ireplace('port', '', $parts[0]));
            }
        }

        $etaDate = $this->eta ?: ($this->trackable?->eta ?? null);
        $formattedDate = 'ETA TBA';
        if ($etaDate) {
            $formattedDate = ($etaDate instanceof \Carbon\CarbonInterface ? $etaDate : \Carbon\Carbon::parse($etaDate))->format('M d, Y') . ' LT';
        }

        return [
            'city' => $destCity,
            'label' => "ETA • {$destCity}",
            'date' => $formattedDate,
        ];
    }

    /**
     * Header Route Display (Origin Flag + City ➔ Destination Flag + City).
     */
    public function getRouteDisplayAttribute(): array
    {
        $origLoc = $this->origin_locode ?: ($this->trackable?->pol?->code ?? null);
        $destLoc = $this->destination_locode ?: ($this->trackable?->pod?->code ?? null);
        $originFlag = self::getCountryFlagEmoji($origLoc);
        $destFlag = self::getCountryFlagEmoji($destLoc);

        $originCity = 'Origin';
        $destCity = 'Destination';

        if (!empty($this->raw_payload['containers'][0]['locations'])) {
            foreach ($this->raw_payload['containers'][0]['locations'] as $loc) {
                if (($loc['locationType'] ?? '') === 'fpol') {
                    $originCity = ucfirst(strtolower($loc['city'] ?? ''));
                    if (!empty($loc['countryCode'])) {
                        $originFlag = self::getCountryFlagEmoji($loc['countryCode']);
                    }
                } elseif (($loc['locationType'] ?? '') === 'lpod') {
                    $destCity = ucfirst(strtolower($loc['city'] ?? ''));
                    if (!empty($loc['countryCode'])) {
                        $destFlag = self::getCountryFlagEmoji($loc['countryCode']);
                    }
                }
            }
        }

        if ($originCity === 'Origin') {
            if (!empty($this->trackable?->pol?->city)) {
                $originCity = $this->trackable->pol->city;
            } elseif (!empty($this->origin_name)) {
                $parts = explode(',', $this->origin_name);
                $originCity = trim(str_ireplace('port', '', $parts[0]));
            }
        }
        if ($destCity === 'Destination') {
            if (!empty($this->trackable?->pod?->city)) {
                $destCity = $this->trackable->pod->city;
            } elseif (!empty($this->destination_name)) {
                $parts = explode(',', $this->destination_name);
                $destCity = trim(str_ireplace('port', '', $parts[0]));
            }
        }

        return [
            'origin_city' => $originCity,
            'origin_flag' => $originFlag,
            'dest_city' => $destCity,
            'dest_flag' => $destFlag,
            'label' => "{$originFlag} {$originCity} ➔ {$destFlag} {$destCity}",
        ];
    }

    /**
     * Formatted display origin details with UN/LOCODE and fallback resolution.
     */
    public function getDisplayOriginAttribute(): array
    {
        $map = $this->map_data;
        $origLoc = $this->origin_locode ?: ($this->trackable?->pol?->code ?? ($map['origin']['code'] ?? ''));
        $name = $this->origin_name ?: ($this->trackable?->pol?->name ?? ($map['origin']['label'] ?? 'Awaiting Origin Port'));
        $etdDate = $this->etd ?: ($this->trackable?->etd ?? null);
        if ($etdDate && !($etdDate instanceof \Carbon\CarbonInterface)) {
            $etdDate = \Carbon\Carbon::parse($etdDate);
        }
        return [
            'name' => $name,
            'code' => $origLoc,
            'label' => $origLoc ? "{$name} ({$origLoc})" : $name,
            'date' => $etdDate ? $etdDate->format('M d, Y') : ($map['origin']['date'] ?? 'ETD TBA'),
        ];
    }

    /**
     * Formatted display destination details with UN/LOCODE and fallback resolution.
     */
    public function getDisplayDestinationAttribute(): array
    {
        $map = $this->map_data;
        $destLoc = $this->destination_locode ?: ($this->trackable?->pod?->code ?? ($map['destination']['code'] ?? ''));
        $name = $this->destination_name ?: ($this->trackable?->pod?->name ?? ($map['destination']['label'] ?? 'Awaiting Destination Port'));
        $etaDate = $this->eta ?: ($this->trackable?->eta ?? null);
        if ($etaDate && !($etaDate instanceof \Carbon\CarbonInterface)) {
            $etaDate = \Carbon\Carbon::parse($etaDate);
        }
        return [
            'name' => $name,
            'code' => $destLoc,
            'label' => $destLoc ? "{$name} ({$destLoc})" : $name,
            'date' => $etaDate ? $etaDate->format('M d, Y') : ($map['destination']['date'] ?? 'ETA TBA'),
        ];
    }
}

