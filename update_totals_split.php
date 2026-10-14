<?php
$content = file_get_contents('app/Http/Controllers/InvoiceController.php');

$search = <<<EOD
        // Apply filters to totals query
        \$totalsQuery = clone \$query;
        
        // Sum totals grouped by currency_id
        \$totalsByCurrencyId = \$totalsQuery->where('status', '!=', 'VOID')
            ->select('currency_id', DB::raw('SUM(total_amount) as total_invoice'), DB::raw('SUM(paid_amount) as total_paid'), DB::raw('SUM(balance_amount) as total_balance'))
            ->groupBy('currency_id')
            ->get();

        // Fetch all currencies used in these totals
        \$currencies = Currency::whereIn('id', \$totalsByCurrencyId->pluck('currency_id'))->get()->keyBy('id');
        
        \$totalsByCurrency = [];
        \$grandTotalInvoice = 0;
        \$grandTotalPaid = 0;
        \$grandTotalBalance = 0;

        foreach (\$totalsByCurrencyId as \$item) {
            \$currency = \$currencies->get(\$item->currency_id);
            \$code = \$currency ? \$currency->code : 'USD'; // Fallback
            
            if (!isset(\$totalsByCurrency[\$code])) {
                \$totalsByCurrency[\$code] = ['invoice' => 0, 'paid' => 0, 'balance' => 0];
            }
            \$totalsByCurrency[\$code]['invoice'] += \$item->total_invoice;
            \$totalsByCurrency[\$code]['paid'] += \$item->total_paid;
            \$totalsByCurrency[\$code]['balance'] += \$item->total_balance;

            // Conversion to Base Currency
            \$rate = 1;
            if (\$code !== \$baseCurrencyCode) {
                \$rateRecord = DB::table('currency_rates')
                    ->where('from_currency', \$code)
                    ->where('to_currency', \$baseCurrencyCode)
                    ->orderByDesc('as_of_date')
                    ->first();
                if (\$rateRecord) {
                    \$rate = \$rateRecord->rate_internal ?: 1;
                } else {
                    // Try reverse rate if direct not found
                    \$reverseRateRecord = DB::table('currency_rates')
                        ->where('from_currency', \$baseCurrencyCode)
                        ->where('to_currency', \$code)
                        ->orderByDesc('as_of_date')
                        ->first();
                    if (\$reverseRateRecord && \$reverseRateRecord->rate_internal > 0) {
                        \$rate = 1 / \$reverseRateRecord->rate_internal;
                    }
                }
            }
            
            \$grandTotalInvoice += \$item->total_invoice * \$rate;
            \$grandTotalPaid += \$item->total_paid * \$rate;
            \$grandTotalBalance += \$item->total_balance * \$rate;
        }

        \$availableCurrencies = Currency::orderBy('code')->get();

        return view('accounting.invoice-list', compact(
            'invoices', 'docType', 
            'totalsByCurrency', 'grandTotalInvoice', 'grandTotalPaid', 'grandTotalBalance', 
            'baseCurrencyCode', 'availableCurrencies'
        ));
EOD;

$replace = <<<EOD
        // Apply filters to totals query
        \$totalsQuery = clone \$query;
        
        // Sum totals grouped by currency_id and type (AR vs AP), handling CN as negative
        \$totalsGrouped = \$totalsQuery->where('status', '!=', 'VOID')
            ->select(
                'currency_id', 
                'type',
                DB::raw("SUM(CASE WHEN document_type = 'CN' THEN -total_amount ELSE total_amount END) as total_invoice"),
                DB::raw("SUM(CASE WHEN document_type = 'CN' THEN -paid_amount ELSE paid_amount END) as total_paid"),
                DB::raw("SUM(CASE WHEN document_type = 'CN' THEN -balance_amount ELSE balance_amount END) as total_balance")
            )
            ->groupBy('currency_id', 'type')
            ->get();

        // Fetch all currencies used in these totals
        \$currencies = Currency::whereIn('id', \$totalsGrouped->pluck('currency_id'))->get()->keyBy('id');
        
        \$totalsByCurrencyAR = [];
        \$totalsByCurrencyAP = [];
        \$grandTotalInvoiceAR = 0; \$grandTotalPaidAR = 0; \$grandTotalBalanceAR = 0;
        \$grandTotalInvoiceAP = 0; \$grandTotalPaidAP = 0; \$grandTotalBalanceAP = 0;

        foreach (\$totalsGrouped as \$item) {
            \$currency = \$currencies->get(\$item->currency_id);
            \$code = \$currency ? \$currency->code : 'USD'; // Fallback
            \$tType = \$item->type === 'AP' ? 'AP' : 'AR';
            
            if (\$tType === 'AR') {
                if (!isset(\$totalsByCurrencyAR[\$code])) \$totalsByCurrencyAR[\$code] = ['invoice' => 0, 'paid' => 0, 'balance' => 0];
                \$totalsByCurrencyAR[\$code]['invoice'] += \$item->total_invoice;
                \$totalsByCurrencyAR[\$code]['paid'] += \$item->total_paid;
                \$totalsByCurrencyAR[\$code]['balance'] += \$item->total_balance;
            } else {
                if (!isset(\$totalsByCurrencyAP[\$code])) \$totalsByCurrencyAP[\$code] = ['invoice' => 0, 'paid' => 0, 'balance' => 0];
                \$totalsByCurrencyAP[\$code]['invoice'] += \$item->total_invoice;
                \$totalsByCurrencyAP[\$code]['paid'] += \$item->total_paid;
                \$totalsByCurrencyAP[\$code]['balance'] += \$item->total_balance;
            }

            // Conversion to Base Currency
            \$rate = 1;
            if (\$code !== \$baseCurrencyCode) {
                \$rateRecord = DB::table('currency_rates')
                    ->where('from_currency', \$code)
                    ->where('to_currency', \$baseCurrencyCode)
                    ->orderByDesc('as_of_date')
                    ->first();
                if (\$rateRecord) {
                    \$rate = \$rateRecord->rate_internal ?: 1;
                } else {
                    // Try reverse rate if direct not found
                    \$reverseRateRecord = DB::table('currency_rates')
                        ->where('from_currency', \$baseCurrencyCode)
                        ->where('to_currency', \$code)
                        ->orderByDesc('as_of_date')
                        ->first();
                    if (\$reverseRateRecord && \$reverseRateRecord->rate_internal > 0) {
                        \$rate = 1 / \$reverseRateRecord->rate_internal;
                    }
                }
            }
            
            if (\$tType === 'AR') {
                \$grandTotalInvoiceAR += \$item->total_invoice * \$rate;
                \$grandTotalPaidAR += \$item->total_paid * \$rate;
                \$grandTotalBalanceAR += \$item->total_balance * \$rate;
            } else {
                \$grandTotalInvoiceAP += \$item->total_invoice * \$rate;
                \$grandTotalPaidAP += \$item->total_paid * \$rate;
                \$grandTotalBalanceAP += \$item->total_balance * \$rate;
            }
        }

        \$availableCurrencies = Currency::orderBy('code')->get();

        return view('accounting.invoice-list', compact(
            'invoices', 'docType', 
            'totalsByCurrencyAR', 'grandTotalInvoiceAR', 'grandTotalPaidAR', 'grandTotalBalanceAR', 
            'totalsByCurrencyAP', 'grandTotalInvoiceAP', 'grandTotalPaidAP', 'grandTotalBalanceAP', 
            'baseCurrencyCode', 'availableCurrencies'
        ));
EOD;

$newContent = str_replace($search, $replace, $content);
file_put_contents('app/Http/Controllers/InvoiceController.php', $newContent);
echo "Replaced in InvoiceController.php\n";
