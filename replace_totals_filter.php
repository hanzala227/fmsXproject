<?php
$content = file_get_contents('app/Http/Controllers/InvoiceController.php');

$search = <<<EOD
        // Sum totals grouped by currency_id
        \$totalsByCurrencyId = Invoice::where('status', '!=', 'VOID')
            ->select('currency_id', DB::raw('SUM(total_amount) as total_invoice'), DB::raw('SUM(paid_amount) as total_paid'), DB::raw('SUM(balance_amount) as total_balance'))
            ->groupBy('currency_id')
            ->get();
EOD;

$replace = <<<EOD
        // Apply filters to totals query
        \$totalsQuery = clone \$query;
        
        // Sum totals grouped by currency_id
        \$totalsByCurrencyId = \$totalsQuery->where('status', '!=', 'VOID')
            ->select('currency_id', DB::raw('SUM(total_amount) as total_invoice'), DB::raw('SUM(paid_amount) as total_paid'), DB::raw('SUM(balance_amount) as total_balance'))
            ->groupBy('currency_id')
            ->get();
EOD;

$newContent = str_replace($search, $replace, $content);
file_put_contents('app/Http/Controllers/InvoiceController.php', $newContent);
echo "Replaced in InvoiceController.php\n";
