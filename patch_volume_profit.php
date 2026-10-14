<?php
$content = file_get_contents('app/Http/Controllers/ReportController.php');

// 1. Add items.currency to with() in volumeProfitChartData
$old_query = <<<'QUERY'
    $query = Quotation::query()
        ->with([
            'items',
            'customer',
            'office',
            'salesPerson',
            'pol',
            'pod'
        ])
QUERY;
$new_query = <<<'QUERY'
    $query = Quotation::query()
        ->with([
            'items.currency',
            'customer',
            'office',
            'salesPerson',
            'pol',
            'pod'
        ])
QUERY;
$content = str_replace($old_query, $new_query, $content);

// 2. Fix TOTALS
$old_totals = <<<'TOTALS'
    foreach ($quotations as $q) {

        foreach ($q->items as $item) {

            $amount = (float) ($item->total_amount ?? 0);

            if ($item->type === 'AR') {
                $totalAr += $amount;
            }

            if ($item->type === 'AP') {
                $totalAp += $amount;
            }
        }
TOTALS;
$new_totals = <<<'TOTALS'
    foreach ($quotations as $q) {

        foreach ($q->items as $item) {
            $rate = ($item->currency && $item->currency->exchange_rate > 0) ? (float) $item->currency->exchange_rate : 1;
            $amount = (float) ($item->total_amount ?? 0) / $rate;

            if ($item->type === 'AR') {
                $totalAr += $amount;
            }

            if ($item->type === 'AP') {
                $totalAp += $amount;
            }
        }
TOTALS;
$content = str_replace($old_totals, $new_totals, $content);

// 3. Fix MONTHLY DATA
$old_monthly = <<<'MONTHLY'
        foreach ($monthQ as $q) {

            foreach ($q->items as $item) {

                $amount = (float) ($item->total_amount ?? 0);

                if ($item->type === 'AR') {
                    $monthAr += $amount;
                }

                if ($item->type === 'AP') {
                    $monthAp += $amount;
                }
            }
MONTHLY;
$new_monthly = <<<'MONTHLY'
        foreach ($monthQ as $q) {

            foreach ($q->items as $item) {
                $rate = ($item->currency && $item->currency->exchange_rate > 0) ? (float) $item->currency->exchange_rate : 1;
                $amount = (float) ($item->total_amount ?? 0) / $rate;

                if ($item->type === 'AR') {
                    $monthAr += $amount;
                }

                if ($item->type === 'AP') {
                    $monthAp += $amount;
                }
            }
MONTHLY;
$content = str_replace($old_monthly, $new_monthly, $content);

// 4. Fix BY SHIPPING TYPE
$old_shipping = <<<'SHIPPING'
            foreach ($qs as $q) {

                foreach ($q->items as $item) {

                    $amount = (float) (
                        $item->total_amount ?? 0
                    );

                    if ($item->type === 'AR') {
                        $ar += $amount;
                    }

                    if ($item->type === 'AP') {
                        $ap += $amount;
                    }
                }
SHIPPING;
$new_shipping = <<<'SHIPPING'
            foreach ($qs as $q) {

                foreach ($q->items as $item) {
                    $rate = ($item->currency && $item->currency->exchange_rate > 0) ? (float) $item->currency->exchange_rate : 1;
                    $amount = (float) ($item->total_amount ?? 0) / $rate;

                    if ($item->type === 'AR') {
                        $ar += $amount;
                    }

                    if ($item->type === 'AP') {
                        $ap += $amount;
                    }
                }
SHIPPING;
// We need to replace this twice, since BY OFFICE also has the EXACT SAME LOOP in volumeProfitChartData, and BY ROUTE does too!
$content = str_replace($old_shipping, $new_shipping, $content);

file_put_contents('app/Http/Controllers/ReportController.php', $content);
echo "Patched volumeProfitChartData\n";
