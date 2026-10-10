<?php

namespace App\Http\Controllers;

use App\Models\TradePartner;
use Illuminate\Support\Facades\DB;

class ClientComapnyController extends Controller
{
    public function index()
    {
        $user       = auth()->user();
        $companyId  = $user->company_id;
        $customerId = $user->trade_partner_id;

        // Customer ke bina portal nahi chalega
        abort_if(!$companyId || !$customerId, 403);

        /*
        |----------------------------------------------------------------------
        | 1) Logged-in customer ki apni company (trade_partners table se)
        |----------------------------------------------------------------------
        */
        $myCompany = TradePartner::where('company_id', $companyId)
            ->where('id', $customerId)
            ->firstOrFail();

        /*
        |----------------------------------------------------------------------
        | 2) Shippers jin se is customer ka kaam hua hai
        |    Customer do jagah milta hai:
        |      a) Master table ke dm_* columns (direct master)
        |      b) HBL tables ke customer_id / bill_to columns
        |    (Pehle wale code mein kuch columns master tables mein hain hi nahi
        |     the, is liye SQL error aata tha. Ab sahi tables use ho rahi hain.)
        |----------------------------------------------------------------------
        */
        $ids = collect();

        // ---- Direct master wale shipments ----
        $ids = $ids->merge(
            DB::table('air_imports')
                ->where('company_id', $companyId)->whereNull('deleted_at')
                ->whereNotNull('dm_shipper_id')
                ->where(fn($q) => $q->where('dm_customer_id', $customerId)
                                    ->orWhere('dm_bill_to_id', $customerId)
                                    ->orWhere('bill_to_id', $customerId)
                                    ->orWhere('consignee_id', $customerId))
                ->pluck('dm_shipper_id')
        );

        $ids = $ids->merge(
            DB::table('air_exports')
                ->where('company_id', $companyId)->whereNull('deleted_at')
                ->whereNotNull('dm_shipper_id')
                ->where(fn($q) => $q->where('dm_customer_id', $customerId)
                                    ->orWhere('dm_bill_to_id', $customerId))
                ->pluck('dm_shipper_id')
        );

        foreach (['ocean_imports', 'ocean_exports'] as $table) {
            $ids = $ids->merge(
                DB::table($table)
                    ->where('company_id', $companyId)->whereNull('deleted_at')
                    ->whereNotNull('dm_shipper_id')
                    ->where(fn($q) => $q->where('dm_customer_id', $customerId)
                                        ->orWhere('dm_bill_to_id', $customerId))
                    ->pluck('dm_shipper_id')
            );
        }

        // ---- HBL wale shipments ----
        $hbls = [
            'air_import_hbls'   => ['customer_id', 'bill_to_id'],
            'air_export_hbls'   => ['customer_id', 'bill_to'],
            'ocean_import_hbls' => ['customer_id'],
            'ocean_export_hbls' => ['customer_id'],
        ];
        foreach ($hbls as $table => $customerCols) {
            $ids = $ids->merge(
                DB::table($table)
                    ->where('company_id', $companyId)->whereNull('deleted_at')
                    ->whereNotNull('shipper_id')
                    ->where(function ($q) use ($customerCols, $customerId) {
                        foreach ($customerCols as $col) {
                            $q->orWhere($col, $customerId);
                        }
                    })
                    ->pluck('shipper_id')
            );
        }

        $shipperIds = $ids->filter()->unique()->values();

        $shippers = TradePartner::where('company_id', $companyId)
            ->whereIn('id', $shipperIds)
            ->orderBy('name')
            ->get();

        return view('client.company', compact('myCompany', 'shippers'));
    }
}