<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CurrencyRateController extends Controller
{
    public function index()
    {
        $rates = DB::table('currency_rates')
            ->orderBy('as_of_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($rate) {
                $rate->created_at = $rate->created_at ? date('m-d-Y', strtotime($rate->created_at)) : '';
                $rate->updated_at = $rate->updated_at ? date('m-d-Y', strtotime($rate->updated_at)) : '';
                $rate->selected = false;
                $rate->_unsaved = false;
                return $rate;
            });

        // Dynamic metrics calculation
        $latestRateRecord = DB::table('currency_rates')
            ->orderBy('as_of_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();
        
        $latestRate = $latestRateRecord ? number_format((float)$latestRateRecord->rate_external, 4) : '1.0000';
        
        $lastBlockRecord = DB::table('accounting_block_dates')
            ->orderBy('block_date', 'desc')
            ->first();
        $lastBlockDate = $lastBlockRecord ? date('m-d-Y', strtotime($lastBlockRecord->block_date)) : 'N/A';

        $fromCurrencies = DB::table('currency_rates')->distinct()->pluck('from_currency')->filter()->values()->toArray();
        $toCurrencies = ['USD'];

        return view('accounting.currency-table', compact(
            'rates', 'latestRate', 'lastBlockDate', 'fromCurrencies', 'toCurrencies'
        ));
    }

    public function getRates(Request $request)
    {
        $query = DB::table('currency_rates');

        if ($request->filled('from_currency') && $request->from_currency !== 'ALL') {
            $query->where('from_currency', $request->from_currency);
        }

        if ($request->filled('to_currency') && $request->to_currency !== 'ALL') {
            $query->where('to_currency', $request->to_currency);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('from_currency', 'like', "%{$search}%")
                  ->orWhere('to_currency', 'like', "%{$search}%")
                  ->orWhere('created_by', 'like', "%{$search}%")
                  ->orWhere('modified_by', 'like', "%{$search}%")
                  ->orWhere('generated_by', 'like', "%{$search}%")
                  ->orWhere('remark', 'like', "%{$search}%");
            });
        }

        $rates = $query->orderBy('as_of_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($rate) {
                $rate->created_at = $rate->created_at ? date('m-d-Y', strtotime($rate->created_at)) : '';
                $rate->updated_at = $rate->updated_at ? date('m-d-Y', strtotime($rate->updated_at)) : '';
                $rate->selected = false;
                $rate->_unsaved = false;
                return $rate;
            });

        // Dynamic metrics
        $latestRateRecord = DB::table('currency_rates')
            ->orderBy('as_of_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();
        $latestRate = $latestRateRecord ? number_format((float)$latestRateRecord->rate_external, 4) : '1.0000';

        $lastBlockRecord = DB::table('accounting_block_dates')
            ->orderBy('block_date', 'desc')
            ->first();
        $lastBlockDate = $lastBlockRecord ? date('m-d-Y', strtotime($lastBlockRecord->block_date)) : 'N/A';

        return response()->json([
            'rates' => $rates,
            'latest_rate' => $latestRate,
            'last_block_date' => $lastBlockDate,
        ]);
    }

    public function bulkSave(Request $request)
    {
        try {
            $rates = $request->rates;
            if (!$rates || !is_array($rates)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No rate data provided.'
                ], 400);
            }

            $savedRates = [];

            foreach ($rates as $rateData) {
                $dataToSave = [
                    'from_currency' => strtoupper(trim($rateData['from_currency'] ?? 'CAD')),
                    'to_currency'   => 'USD',
                    'as_of_date'    => $rateData['as_of_date'] ?? date('Y-m-d'),
                    'rate_internal' => (float)($rateData['rate_internal'] ?? 0),
                    'rate_external' => (float)($rateData['rate_external'] ?? 0),
                    'created_by'    => $rateData['created_by'] ?? 'System',
                    'modified_by'   => $rateData['modified_by'] ?? 'Admin',
                    'generated_by'  => $rateData['generated_by'] ?? 'Accounting',
                    'remark'        => $rateData['remark'] ?? '',
                    'updated_at'    => now(),
                ];

                if (isset($rateData['id']) && $rateData['id']) {
                    // Update existing rate
                    DB::table('currency_rates')
                        ->where('id', $rateData['id'])
                        ->update($dataToSave);
                    
                    $savedRates[] = array_merge($rateData, $dataToSave, [
                        'updated_at' => date('m-d-Y')
                    ]);
                } else {
                    // Create new rate
                    $dataToSave['created_at'] = now();
                    $id = DB::table('currency_rates')->insertGetId($dataToSave);

                    $savedRates[] = array_merge($rateData, $dataToSave, [
                        'id' => $id,
                        'created_at' => date('m-d-Y'),
                        'updated_at' => date('m-d-Y')
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($rates) . ' rate(s) saved successfully',
                'rates' => $savedRates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save rates: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::table('currency_rates')->where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rate deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete rate: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->ids;
            if (!$ids || !is_array($ids)) {
                return response()->json(['success' => false, 'message' => 'No records selected for deletion.'], 400);
            }

            DB::table('currency_rates')->whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => count($ids) . ' rate(s) deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete rates: ' . $e->getMessage()
            ], 500);
        }
    }

    public function print(Request $request)
    {
        $query = DB::table('currency_rates');

        if ($request->filled('from_currency') && $request->from_currency !== 'ALL') {
            $query->where('from_currency', $request->from_currency);
        }

        if ($request->filled('to_currency') && $request->to_currency !== 'ALL') {
            $query->where('to_currency', $request->to_currency);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('from_currency', 'like', "%{$search}%")
                  ->orWhere('to_currency', 'like', "%{$search}%")
                  ->orWhere('created_by', 'like', "%{$search}%")
                  ->orWhere('modified_by', 'like', "%{$search}%")
                  ->orWhere('generated_by', 'like', "%{$search}%")
                  ->orWhere('remark', 'like', "%{$search}%");
            });
        }

        $rates = $query->orderBy('as_of_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $latestRateRecord = DB::table('currency_rates')
            ->orderBy('as_of_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();
        $latestRate = $latestRateRecord ? number_format((float)$latestRateRecord->rate_external, 4) : '1.0000';

        $lastBlockRecord = DB::table('accounting_block_dates')
            ->orderBy('block_date', 'desc')
            ->first();
        $lastBlockDate = $lastBlockRecord ? date('m-d-Y', strtotime($lastBlockRecord->block_date)) : 'N/A';

        return view('accounting.currency-table-print', compact('rates', 'latestRate', 'lastBlockDate'));
    }

    public function export(Request $request)
    {
        $query = DB::table('currency_rates');

        if ($request->filled('from_currency') && $request->from_currency !== 'ALL') {
            $query->where('from_currency', $request->from_currency);
        }

        if ($request->filled('to_currency') && $request->to_currency !== 'ALL') {
            $query->where('to_currency', $request->to_currency);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('from_currency', 'like', "%{$search}%")
                  ->orWhere('to_currency', 'like', "%{$search}%")
                  ->orWhere('created_by', 'like', "%{$search}%")
                  ->orWhere('modified_by', 'like', "%{$search}%")
                  ->orWhere('generated_by', 'like', "%{$search}%")
                  ->orWhere('remark', 'like', "%{$search}%");
            });
        }

        $rates = $query->orderBy('as_of_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="currency-rates-' . date('Y-m-d') . '.csv"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ];

        $callback = function () use ($rates) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            // Header row
            fputcsv($handle, [
                'From', 'To', 'As of Date', 'Rate (Internal)', 'Rate (External)',
                'Created By', 'Created Date', 'Modified By', 'Last Modified',
                'Generated By', 'Remark'
            ]);

            // Data rows
            foreach ($rates as $rate) {
                fputcsv($handle, [
                    $rate->from_currency,
                    $rate->to_currency,
                    $rate->as_of_date ? date('m-d-Y', strtotime($rate->as_of_date)) : '',
                    $rate->rate_internal,
                    $rate->rate_external,
                    $rate->created_by ?? '',
                    $rate->created_at ? date('m-d-Y', strtotime($rate->created_at)) : '',
                    $rate->modified_by ?? '',
                    $rate->updated_at ? date('m-d-Y', strtotime($rate->updated_at)) : '',
                    $rate->generated_by ?? '',
                    $rate->remark ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, 'currency-rates-' . date('Y-m-d') . '.csv', $headers);
    }
}
