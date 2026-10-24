<?php

namespace App\Services\FrexoAi\Matching;

use App\Models\Port;
use App\Models\TradePartner;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class TenantEntityMatcher
{
    /**
     * Resolves the server-side company ID strictly from authenticated context.
     * Never accepts an unverified company ID from AI or request input.
     */
    protected function resolveCompanyId(?int $explicitCompanyId = null): int
    {
        $authCompanyId = Auth::check() ? Auth::user()->company_id : null;

        if ($authCompanyId !== null) {
            // If explicit ID was passed, verify it matches the authenticated tenant
            if ($explicitCompanyId !== null && $explicitCompanyId !== $authCompanyId) {
                throw new InvalidArgumentException("Cross-tenant access violation: forbidden entity lookup for company {$explicitCompanyId}");
            }
            return (int) $authCompanyId;
        }

        if ($explicitCompanyId !== null) {
            return (int) $explicitCompanyId;
        }

        throw new InvalidArgumentException("Unauthenticated tenant context: company_id is required.");
    }

    /**
     * Match a trade partner (Shipper, Consignee, Notify, Carrier, etc.)
     * Returns structured result with candidates; NEVER creates records silently.
     */
    public function matchTradePartner(?string $query, ?int $companyId = null, ?string $type = null): array
    {
        $companyId = $this->resolveCompanyId($companyId);

        if (empty($query) || trim($query) === '') {
            return [
                'match_type' => 'NO_MATCH',
                'entity_id' => null,
                'confidence' => 'REVIEW_REQUIRED',
                'matched_entity' => null,
                'candidates' => [],
            ];
        }

        $rawQuery = trim($query);
        $normalizedQuery = $this->normalizeEntityName($rawQuery);

        $baseQuery = TradePartner::query()
            ->where('company_id', $companyId);

        if ($type) {
            $baseQuery->where(function ($q) use ($type) {
                $q->where('type', $type)->orWhereNull('type');
            });
        }

        // 1. Exact match by name, code, or alias
        $exactMatches = (clone $baseQuery)
            ->where(function ($q) use ($rawQuery) {
                $q->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($rawQuery)])
                  ->orWhereRaw('LOWER(TRIM(code)) = ?', [mb_strtolower($rawQuery)])
                  ->orWhereRaw('LOWER(TRIM(alias)) = ?', [mb_strtolower($rawQuery)]);
            })
            ->get();

        if ($exactMatches->count() === 1) {
            $partner = $exactMatches->first();
            return [
                'match_type' => 'EXACT',
                'entity_id' => $partner->id,
                'confidence' => 'CONFIDENT',
                'matched_entity' => [
                    'id' => $partner->id,
                    'name' => $partner->name,
                    'code' => $partner->code,
                ],
                'candidates' => [],
            ];
        }

        // 2. Normalized exact match (ignoring Inc, LLC, Ltd, punctuation)
        $candidates = (clone $baseQuery)
            ->where(function ($q) use ($normalizedQuery, $rawQuery) {
                $q->where('name', 'LIKE', '%' . substr($normalizedQuery, 0, 15) . '%')
                  ->orWhere('alias', 'LIKE', '%' . substr($normalizedQuery, 0, 15) . '%');
            })
            ->limit(10)
            ->get();

        $normalizedMatches = [];
        foreach ($candidates as $candidate) {
            if ($this->normalizeEntityName($candidate->name) === $normalizedQuery) {
                $normalizedMatches[] = $candidate;
            }
        }

        if (count($normalizedMatches) === 1) {
            $partner = $normalizedMatches[0];
            return [
                'match_type' => 'NORMALIZED_EXACT',
                'entity_id' => $partner->id,
                'confidence' => 'CONFIDENT',
                'matched_entity' => [
                    'id' => $partner->id,
                    'name' => $partner->name,
                    'code' => $partner->code,
                ],
                'candidates' => [],
            ];
        }

        // 3. Multiple matches or partial candidate list -> User review candidate list
        $candidateList = [];
        foreach ($candidates as $cand) {
            $candidateList[] = [
                'id' => $cand->id,
                'name' => $cand->name,
                'code' => $cand->code,
                'city' => $cand->city,
            ];
        }

        return [
            'match_type' => count($candidateList) > 0 ? 'CANDIDATE_LIST' : 'NO_MATCH',
            'entity_id' => null,
            'confidence' => 'REVIEW_REQUIRED',
            'matched_entity' => null,
            'candidates' => array_slice($candidateList, 0, 5),
        ];
    }

    /**
     * Match a port by UN/LOCODE or port name.
     * Ports are global reference tables, but zero or multiple matches produce candidate lists.
     */
    public function matchPort(?string $query): array
    {
        if (empty($query) || trim($query) === '') {
            return [
                'match_type' => 'NO_MATCH',
                'entity_id' => null,
                'confidence' => 'REVIEW_REQUIRED',
                'matched_entity' => null,
                'candidates' => [],
            ];
        }

        $rawQuery = trim($query);

        // 1. Exact match by port code (e.g. USLAX, CNSHA)
        $exactCode = Port::whereRaw('UPPER(TRIM(code)) = ?', [strtoupper($rawQuery)])->get();
        if ($exactCode->count() === 1) {
            $port = $exactCode->first();
            return [
                'match_type' => 'EXACT',
                'entity_id' => $port->id,
                'confidence' => 'CONFIDENT',
                'matched_entity' => [
                    'id' => $port->id,
                    'code' => $port->code,
                    'name' => $port->name,
                ],
                'candidates' => [],
            ];
        }

        // 2. Exact match by name
        $exactName = Port::whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($rawQuery)])->get();
        if ($exactName->count() === 1) {
            $port = $exactName->first();
            return [
                'match_type' => 'EXACT',
                'entity_id' => $port->id,
                'confidence' => 'CONFIDENT',
                'matched_entity' => [
                    'id' => $port->id,
                    'code' => $port->code,
                    'name' => $port->name,
                ],
                'candidates' => [],
            ];
        }

        // 3. Partial candidates
        $candidates = Port::where('name', 'LIKE', '%' . $rawQuery . '%')
            ->orWhere('code', 'LIKE', '%' . $rawQuery . '%')
            ->limit(5)
            ->get();

        $candidateList = [];
        foreach ($candidates as $cand) {
            $candidateList[] = [
                'id' => $cand->id,
                'code' => $cand->code,
                'name' => $cand->name,
                'city' => $cand->city,
            ];
        }

        return [
            'match_type' => count($candidateList) > 0 ? 'CANDIDATE_LIST' : 'NO_MATCH',
            'entity_id' => null,
            'confidence' => 'REVIEW_REQUIRED',
            'matched_entity' => null,
            'candidates' => $candidateList,
        ];
    }

    /**
     * Normalize company name by stripping common legal entity suffixes and punctuation.
     */
    public function normalizeEntityName(string $name): string
    {
        $cleaned = mb_strtolower(trim($name));
        // Remove punctuation except spaces and alphanumeric
        $cleaned = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $cleaned);
        // Common legal entity suffixes
        $suffixes = ['\binc\b', '\bllc\b', '\bltd\b', '\bcorp\b', '\bcorporation\b', '\bgmbh\b', '\bco\b', '\bcompany\b', '\bpte\b', '\bpty\b', '\bsa\b', '\bsp z o o\b'];
        
        // Loop to strip compound suffixes such as "(Pte.) Ltd." or "Co., Ltd."
        $prev = '';
        while ($prev !== $cleaned) {
            $prev = $cleaned;
            foreach ($suffixes as $suffix) {
                $cleaned = preg_replace('/' . $suffix . '$/i', '', trim($cleaned));
                $cleaned = preg_replace('/' . $suffix . '\s+/i', ' ', trim($cleaned));
            }
            $cleaned = trim($cleaned);
        }
        // Collapse multiple whitespace
        return trim(preg_replace('/\s+/', ' ', $cleaned));
    }
}
