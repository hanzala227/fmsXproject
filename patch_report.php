<?php
$content = file_get_contents('app/Http/Controllers/ReportController.php');

// 1. Add items.currency to with()
$content = str_replace(
    "->with(['items', 'salesPerson', 'office', 'customer', 'pol', 'pod'])",
    "->with(['items.currency', 'salesPerson', 'office', 'customer', 'pol', 'pod'])",
    $content
);

// 2. Fix the loop in $grouped
$old_loop = <<<'LOOP'
            foreach ($qs as $q) {
                foreach ($q->items as $item) {
                    if ($item->type === 'AR') $ar += (float) $item->total_amount;
                    elseif ($item->type === 'AP') $ap += (float) $item->total_amount;
                }
                $vol += (float) ($q->volume_cbm ?? 0);
                $status = $q->status ?: 'Unknown';
                $statuses[$status] = ($statuses[$status] ?? 0) + 1;
                $st = $q->shipping_type ?: 'Unspecified';
                if (!isset($shippingBreakdown[$st])) {
                    $shippingBreakdown[$st] = ['count' => 0, 'profit' => 0];
                }
                $shippingBreakdown[$st]['count']++;
                $shippingBreakdown[$st]['profit'] += $ar - $ap;
            }
LOOP;

$new_loop = <<<'LOOP'
            foreach ($qs as $q) {
                $qAr = 0;
                $qAp = 0;
                
                // Only count revenue/cost if Quote is Verified/Accepted/Won
                $isValid = in_array(strtoupper($q->status), ['ACCEPTED', 'WON']);

                foreach ($q->items as $item) {
                    $rate = ($item->currency && $item->currency->exchange_rate > 0) ? (float) $item->currency->exchange_rate : 1;
                    $amount = (float) $item->total_amount / $rate;
                    
                    if ($isValid) {
                        if ($item->type === 'AR') {
                            $qAr += $amount;
                            $ar += $amount;
                        } elseif ($item->type === 'AP') {
                            $qAp += $amount;
                            $ap += $amount;
                        }
                    }
                }
                
                if ($isValid) {
                    $vol += (float) ($q->volume_cbm ?? 0);
                }

                $status = $q->status ?: 'Unknown';
                $statuses[$status] = ($statuses[$status] ?? 0) + 1;
                $st = $q->shipping_type ?: 'Unspecified';
                
                if (!isset($shippingBreakdown[$st])) {
                    $shippingBreakdown[$st] = ['count' => 0, 'profit' => 0];
                }
                $shippingBreakdown[$st]['count']++;
                $shippingBreakdown[$st]['profit'] += ($qAr - $qAp);
            }
LOOP;

$content = str_replace($old_loop, $new_loop, $content);

// 3. Fix the margin calculation
$old_margin = <<<'MARGIN'
            $profit = $ar - $ap;
            $margin = $ar > 0 ? round(($profit / $ar) * 100, 1) : 0;

            $wonCount = $statuses['Won'] ?? 0;
MARGIN;

$new_margin = <<<'MARGIN'
            $profit = $ar - $ap;
            $margin = $ar != 0 ? round(($profit / abs($ar)) * 100, 1) : 0;

            $wonCount = ($statuses['Won'] ?? 0) + ($statuses['ACCEPTED'] ?? 0) + ($statuses['Accepted'] ?? 0);
MARGIN;

$content = str_replace($old_margin, $new_margin, $content);

// 4. Fix allAr, allAp, allVol
$old_totals = <<<'TOTALS'
        $allAr = $quotations->sum(fn($q) => $q->items->where('type', 'AR')->sum('total_amount'));
        $allAp = $quotations->sum(fn($q) => $q->items->where('type', 'AP')->sum('total_amount'));
        $allVol = $quotations->sum(fn($q) => (float) ($q->volume_cbm ?? 0));
TOTALS;

$new_totals = <<<'TOTALS'
        $allAr = 0; $allAp = 0; $allVol = 0;
        foreach ($quotations as $q) {
            $isValid = in_array(strtoupper($q->status), ['ACCEPTED', 'WON']);
            if ($isValid) {
                foreach ($q->items as $item) {
                    $rate = ($item->currency && $item->currency->exchange_rate > 0) ? (float) $item->currency->exchange_rate : 1;
                    $amount = (float) $item->total_amount / $rate;
                    if ($item->type === 'AR') $allAr += $amount;
                    elseif ($item->type === 'AP') $allAp += $amount;
                }
                $allVol += (float) ($q->volume_cbm ?? 0);
            }
        }
TOTALS;

$content = str_replace($old_totals, $new_totals, $content);

// 5. Fix byShippingType
$old_shipping = <<<'SHIPPING'
        $byShippingType = $quotations->groupBy(fn($q) => $q->shipping_type ?: 'Unspecified')->map(function ($qs, $key) {
            $ar = 0; $ap = 0;
            foreach ($qs as $q) {
                foreach ($q->items as $item) {
                    if ($item->type === 'AR') $ar += (float) $item->total_amount;
                    elseif ($item->type === 'AP') $ap += (float) $item->total_amount;
                }
            }
            return ['label' => $key, 'revenue' => round($ar, 2), 'cost' => round($ap, 2), 'profit' => round($ar - $ap, 2), 'count' => $qs->count()];
        })->values();
SHIPPING;

$new_shipping = <<<'SHIPPING'
        $byShippingType = $quotations->groupBy(fn($q) => $q->shipping_type ?: 'Unspecified')->map(function ($qs, $key) {
            $ar = 0; $ap = 0;
            foreach ($qs as $q) {
                $isValid = in_array(strtoupper($q->status), ['ACCEPTED', 'WON']);
                if ($isValid) {
                    foreach ($q->items as $item) {
                        $rate = ($item->currency && $item->currency->exchange_rate > 0) ? (float) $item->currency->exchange_rate : 1;
                        $amount = (float) $item->total_amount / $rate;
                        if ($item->type === 'AR') $ar += $amount;
                        elseif ($item->type === 'AP') $ap += $amount;
                    }
                }
            }
            return ['label' => $key, 'revenue' => round($ar, 2), 'cost' => round($ap, 2), 'profit' => round($ar - $ap, 2), 'count' => $qs->count()];
        })->values();
SHIPPING;

$content = str_replace($old_shipping, $new_shipping, $content);

file_put_contents('app/Http/Controllers/ReportController.php', $content);
echo "Patched ReportController.php\n";
