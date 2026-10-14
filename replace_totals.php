<?php
$content = file_get_contents('app/Http/Controllers/InvoiceController.php');

$search = <<<EOD
        \$totalInvoiceAmount = Invoice::where('status', '!=', 'VOID')->sum('total_amount');
        \$totalPaidAmount = Invoice::where('status', '!=', 'VOID')->sum('paid_amount');
        \$totalBalanceAmount = Invoice::where('status', '!=', 'VOID')->sum('balance_amount');

        return view('accounting.invoice-list', compact('invoices', 'totalInvoiceAmount', 'totalPaidAmount', 'totalBalanceAmount', 'docType'));
EOD;

$replace = <<<EOD
        \$baseCurrencyCode = \$request->input('base_currency', 'USD');
        
        // Sum totals grouped by currency_id
        \$totalsByCurrencyId = Invoice::where('status', '!=', 'VOID')
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

$newContent = str_replace($search, $replace, $content);
file_put_contents('app/Http/Controllers/InvoiceController.php', $newContent);
echo "Replaced in InvoiceController.php\n";
