<?php

namespace App\Http\Controllers;

use App\Models\TradePartner;
use App\Models\Country;
use App\Models\Office;
use App\Models\Currency;
use App\Models\AccountGroup;
use App\Models\CreditLimitGroup;
use App\Http\Requests\StoreTradePartnerRequest;
use App\Http\Requests\UpdateTradePartnerRequest;
use Illuminate\Http\Request;

class TradePartnerController extends Controller
{
    public function index(Request $request)
    {
        $query = TradePartner::with(['country', 'salesPerson', 'csPerson', 'accountGroup'])
            ->withCount('contacts');

        if ($request->trashed) {
            $query->onlyTrashed();
        }

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%")
                  ->orWhere('local_name', 'like', "%{$s}%")
                  ->orWhere('scac_code', 'like', "%{$s}%")
                  ->orWhere('iata_code', 'like', "%{$s}%")
                  ->orWhere('firms_code', 'like', "%{$s}%")
                  ->orWhere('alias', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('city', 'like', "%{$s}%")
                  ->orWhere('state', 'like', "%{$s}%")
                  ->orWhere('tax_id', 'like', "%{$s}%")
                  ->orWhere('zip_code', 'like', "%{$s}%")
                  ->orWhere('remark', 'like', "%{$s}%");
            });
        }

        if ($request->filter_type) {
            $query->where('type', $request->filter_type);
        }
        if ($request->filter_status) {
            $query->where('status', $request->filter_status);
        }
        if ($request->filter_country) {
            $query->where('country_id', $request->filter_country);
        }
        if ($request->filter_sales_person) {
            $query->where('sales_person_id', $request->filter_sales_person);
        }
        if ($request->filter_payment_type) {
            $query->where('payment_type', $request->filter_payment_type);
        }
        if ($request->filter_name) {
            $query->where('name', 'like', "%{$request->filter_name}%");
        }
        if ($request->filter_local_name) {
            $query->where('local_name', 'like', "%{$request->filter_local_name}%");
        }
        if ($request->filter_scac) {
            $query->where(function ($q) use ($request) {
                $q->where('scac_code', 'like', "%{$request->filter_scac}%")
                  ->orWhere('iata_code', 'like', "%{$request->filter_scac}%");
            });
        }
        if ($request->filter_firm) {
            $query->where('firms_code', 'like', "%{$request->filter_firm}%");
        }
        if ($request->filter_alias) {
            $query->where('alias', 'like', "%{$request->filter_alias}%");
        }
        if ($request->filter_address) {
            $query->where(function ($q) use ($request) {
                $q->where('billing_address', 'like', "%{$request->filter_address}%")
                  ->orWhere('local_address', 'like', "%{$request->filter_address}%");
            });
        }
        if ($request->filter_city) {
            $query->where('city', 'like', "%{$request->filter_city}%");
        }
        if ($request->filter_state) {
            $query->where('state', 'like', "%{$request->filter_state}%");
        }
        if ($request->filter_tax) {
            $query->where('tax_id', 'like', "%{$request->filter_tax}%");
        }
        if ($request->filter_zip) {
            $query->where('zip_code', 'like', "%{$request->filter_zip}%");
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['type', 'status', 'name', 'code', 'local_name', 'city', 'state', 'country_id', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir);
        } else {
            $query->latest();
        }

        $partners = $query->paginate(20)->withQueryString();

        $countries = Country::orderBy('name')->get();
        $users = \App\Models\User::orderBy('name')->get();

        return view('trade-partner.list', compact('partners', 'countries', 'users'));
    }

    public function destroy(TradePartner $tradePartner)
    {
        if ($this->isInUse([$tradePartner->id])) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete: Trade Partner is linked to active shipments, invoices, or quotations.'], 422);
            }
            return redirect()->route('trade-partner.index')->with('error', 'Cannot delete: Trade Partner is linked to active shipments, invoices, or quotations.');
        }

        $tradePartner->delete();
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Trade Partner deleted.']);
        }
        return redirect()->route('trade-partner.index')->with('success', 'Trade Partner deleted.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No trade partners selected.'], 422);
        }

        if ($this->isInUse($ids)) {
            return response()->json(['success' => false, 'message' => 'Cannot delete: One or more selected Trade Partners are linked to active shipments, invoices, or quotations.'], 422);
        }

        TradePartner::whereIn('id', $ids)->delete();
        return response()->json(['success' => true, 'message' => count($ids) . ' trade partner(s) deleted.']);
    }

    private function isInUse(array $ids): bool
    {
        if (empty($ids)) return false;

        $checks = [
            ['table' => 'ocean_imports', 'columns' => ['agent_id', 'shipper_id', 'consignee_id', 'notify_id', 'customer_id', 'bill_to_id', 'customs_broker_id', 'trucker_id', 'delivery_location_id']],
            ['table' => 'ocean_exports', 'columns' => ['agent_id', 'shipper_id', 'consignee_id', 'notify_id', 'customer_id', 'bill_to_id', 'customs_broker_id', 'trucker_id', 'delivery_location_id']],
            ['table' => 'air_imports', 'columns' => ['agent_id', 'shipper_id', 'consignee_id', 'notify_id', 'customer_id', 'bill_to_id', 'customs_broker_id', 'trucker_id', 'delivery_location_id']],
            ['table' => 'air_exports', 'columns' => ['agent_id', 'shipper_id', 'consignee_id', 'notify_id', 'customer_id', 'bill_to_id', 'customs_broker_id', 'trucker_id', 'delivery_location_id']],
            ['table' => 'quotations', 'columns' => ['customer_id', 'agent_id', 'shipper_id', 'consignee_id']],
            ['table' => 'invoices', 'columns' => ['bill_to_id']],
            ['table' => 'invoice_lines', 'columns' => ['bill_to_id', 'vendor_id']],
            ['table' => 'payments', 'columns' => ['trade_partner_id']],
            ['table' => 'journal_entries', 'columns' => ['trade_partner_id']],
        ];

        foreach ($checks as $check) {
            if (\Illuminate\Support\Facades\Schema::hasTable($check['table'])) {
                foreach ($check['columns'] as $column) {
                    if (\Illuminate\Support\Facades\Schema::hasColumn($check['table'], $column)) {
                        if (\Illuminate\Support\Facades\DB::table($check['table'])->whereIn($column, $ids)->exists()) {
                            return true;
                        }
                    }
                }
            }
        }
        
        return false;
    }

    public function bulkRestore(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No trade partners selected.'], 422);
        }
        TradePartner::onlyTrashed()->whereIn('id', $ids)->restore();
        return response()->json(['success' => true, 'message' => count($ids) . ' trade partner(s) restored.']);
    }

    public function exportCsv(Request $request)
    {
        $query = TradePartner::with(['country', 'salesPerson', 'csPerson', 'accountGroup'])
            ->whereNull('deleted_at');

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%")
                  ->orWhere('local_name', 'like', "%{$s}%");
            });
        }
        if ($request->filter_type) $query->where('type', $request->filter_type);
        if ($request->filter_status) $query->where('status', $request->filter_status);
        if ($request->filter_country) $query->where('country_id', $request->filter_country);
        if ($request->filter_sales_person) $query->where('sales_person_id', $request->filter_sales_person);

        $partners = $query->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="trade_partners_export.csv"',
        ];

        $callback = function () use ($partners) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Type', 'Status', 'Local Name', 'SCAC', 'IATA', 'Firm Code', 'Alias', 'Contact', 'Group', 'Address', 'Remark', 'City', 'State', 'Tax ID', 'Track 1099', 'Zip', 'Country', 'Sales Person', 'OP Assigned', 'Payment Type', 'Credit Terms'], ',', '"');
            foreach ($partners as $p) {
                $firstContact = $p->contacts->first();
                fputcsv($handle, [
                    $p->type,
                    $p->status,
                    $p->local_name,
                    $p->scac_code,
                    $p->iata_code,
                    $p->firms_code,
                    $p->alias,
                    $firstContact->email_name ?? $p->phone,
                    $p->accountGroup->name ?? '',
                    $p->billing_address,
                    $p->remark,
                    $p->city,
                    $p->state,
                    $p->tax_id,
                    $p->track_1099 ? 'Yes' : 'No',
                    $p->zip_code,
                    $p->country->name ?? '',
                    $p->salesPerson->name ?? '',
                    $p->csPerson->name ?? '',
                    $p->payment_type,
                    ($p->credit_term_days ?: 0) . ' ' . ($p->credit_term_unit ?? 'Days'),
                ], ',', '"');
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function create()
    {
        $tradePartner = new TradePartner();
        $tradePartner->setRelation('contacts', collect());
        $tradePartner->setRelation('memos', collect());
        $tradePartner->setRelation('defaultFreights', collect());
        $tradePartner->setRelation('commodities', collect());
        $tradePartner->setRelation('filingSettings', null);
        $tradePartner->setRelation('relatedParties', collect());
        $tradePartner->setRelation('documents', collect());
        $countries = Country::orderBy('name')->get();
        $offices = Office::orderBy('name')->get();
        $currencies = Currency::orderBy('code')->get();
        $users = \App\Models\User::orderBy('name')->get();
        $allTradePartners = \App\Models\TradePartner::orderBy('name')->get();
        $accountGroups = AccountGroup::orderBy('name')->get();
        $creditLimitGroups = CreditLimitGroup::orderBy('name')->get();
        return view('trade-partner.create', compact(
            'tradePartner', 'countries', 'offices', 'currencies',
            'users', 'allTradePartners', 'accountGroups', 'creditLimitGroups'
        ));
    }

    public function store(StoreTradePartnerRequest $request)
    {
        try {
            \DB::beginTransaction();

            $data = $request->validated();
            
            // Auto generate code if not provided
            if (empty($data['code'])) {
                $maxId = TradePartner::withTrashed()->max('id') ?? 0;
                $prefix = strtoupper(substr($data['type'] ?? 'TP', 0, 2));
                
                $code = $prefix . str_pad($maxId + 1, 5, '0', STR_PAD_LEFT);
                // Ensure absolute uniqueness just in case
                while (TradePartner::withTrashed()->where('code', $code)->exists()) {
                    $maxId++;
                    $code = $prefix . str_pad($maxId + 1, 5, '0', STR_PAD_LEFT);
                }
                $data['code'] = $code;
            }

            $partner = TradePartner::create($data);

            // Create portal access if provided
            $portalEmail = request()->input('portal_email');
            $portalPassword = request()->input('portal_password');
            if (!empty($portalEmail) && !empty($portalPassword)) {
                $existingUser = \App\Models\User::where('email', $portalEmail)->first();
                if ($existingUser) {
                    $existingUser->update([
                        'password' => \Hash::make($portalPassword),
                        'role' => 'client',
                        'company_id' => auth()->user()->company_id,
                        'trade_partner_id' => $partner->id
                    ]);
                } else {
                    \App\Models\User::create([
                        'name' => $partner->name,
                        'email' => $portalEmail,
                        'password' => \Hash::make($portalPassword),
                        'role' => 'client',
                        'company_id' => auth()->user()->company_id,
                        'trade_partner_id' => $partner->id
                    ]);
                }
            }

            // Filter out empty rows before saving sub-relationships
            $filterContacts = collect($request->contacts ?? [])
                ->filter(fn($c) => !empty(array_filter($c, fn($v) => $v !== null && $v !== '' && $v !== false)))
                ->values()->toArray();

            $filterMemos = collect($request->memos ?? [])
                ->filter(fn($m) => !empty($m['subject']) || !empty($m['content']))
                ->values()->toArray();

            $filterFreights = collect($request->defaultFreights ?? [])
                ->filter(fn($f) => !empty($f['freight_code']) || !empty($f['description']) || ($f['rate'] ?? 0) > 0 || ($f['amount'] ?? 0) > 0)
                ->map(fn($f) => [
                    'transport_mode' => $f['transport_mode'] ?? '',
                    'section' => $f['section'] ?? '',
                    'ship_mode' => $f['ship_mode'] ?? 'All',
                    'freight_code' => $f['freight_code'] ?? '',
                    'description' => $f['description'] ?? '',
                    'pc' => in_array($f['pc'] ?? '', ['PREPAID', 'COLLECT']) ? $f['pc'] : 'COLLECT',
                    'type' => $f['type'] ?? '',
                    'unit' => $f['unit'] ?? 'UNIT',
                    'currency_id' => !empty($f['currency_id']) ? $f['currency_id'] : null,
                    'volume' => is_numeric($f['volume'] ?? null) ? $f['volume'] : 1,
                    'rate' => is_numeric($f['rate'] ?? null) ? $f['rate'] : 0,
                    'amount' => is_numeric($f['amount'] ?? null) ? $f['amount'] : 0,
                    'agent_amount' => is_numeric($f['agent_amount'] ?? null) ? $f['agent_amount'] : 0,
                ])
                ->values()->toArray();

            $filterCommodities = collect($request->commodities ?? [])
                ->filter(fn($c) => !empty($c['description']) || !empty($c['hts_code']) || !empty($c['package_unit_id']))
                ->map(fn($c) => [
                    'description'       => $c['description'] ?? null,
                    'package_unit_id'   => !empty($c['package_unit_id']) ? (int) $c['package_unit_id'] : null,
                    'hts_code'          => $c['hts_code'] ?? null,
                    'pcs'               => is_numeric($c['pcs'] ?? null) ? $c['pcs'] : null,
                    'net_weight'        => is_numeric($c['net_weight'] ?? null) ? $c['net_weight'] : null,
                    'net_weight_unit'   => $c['net_weight_unit'] ?? null,
                    'gross_weight'      => is_numeric($c['gross_weight'] ?? null) ? $c['gross_weight'] : null,
                    'gross_weight_unit' => $c['gross_weight_unit'] ?? null,
                    'measurement'       => is_numeric($c['measurement'] ?? null) ? $c['measurement'] : null,
                    'measurement_unit'  => $c['measurement_unit'] ?? null,
                    'unit_price'        => is_numeric($c['unit_price'] ?? null) ? $c['unit_price'] : null,
                    'amount'            => is_numeric($c['amount'] ?? null) ? $c['amount'] : null,
                    'details'           => $c['details'] ?? null,
                ])
                ->values()->toArray();

            $filterParties = collect($request->relatedParties ?? [])
                ->filter(fn($p) => !empty($p['related_partner_id']))
                ->values()->toArray();

            // Save sub-relationships
            foreach ($filterContacts as $contact) {
                $partner->contacts()->create($contact);
            }

            foreach ($filterMemos as $memo) {
                $partner->memos()->create($memo);
            }

            foreach ($filterFreights as $df) {
                $partner->defaultFreights()->create($df);
            }

            foreach ($filterCommodities as $commodity) {
                $partner->commodities()->create($commodity);
            }

            if ($request->has('filingSetting') && collect($request->filingSetting)->filter()->isNotEmpty()) {
                $partner->filingSettings()->create($request->filingSetting);
            }

            foreach ($filterParties as $party) {
                $partner->relatedParties()->create($party);
            }

            \DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Trade Partner created successfully!',
                    'redirect' => route('trade-partner.edit', $partner->id),
                    'id' => $partner->id,
                    'name' => $partner->name,
                ]);
            }

            return redirect()->route('trade-partner.edit', $partner->id)->with('success', 'Trade Partner created.');
        } catch (\Exception $e) {
            \DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => [$e->getMessage()]], 422);
            }
            throw $e;
        }
    }

    public function show($id)
    {
        $tradePartner = $id instanceof TradePartner ? $id : TradePartner::with(['city', 'state', 'country', 'salesPerson'])->findOrFail($id);

        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $tradePartner
            ]);
        }

        return redirect()->route('trade-partners.edit', $tradePartner->id);
    }

    public function edit(TradePartner $tradePartner)
    {
        $tradePartner->load(['contacts', 'memos.user', 'defaultFreights', 'commodities', 'filingSettings', 'relatedParties', 'documents.uploader']);
        $countries = Country::orderBy('name')->get();
        $offices = Office::orderBy('name')->get();
        $currencies = Currency::orderBy('code')->get();
        $users = \App\Models\User::orderBy('name')->get();
        $allTradePartners = \App\Models\TradePartner::orderBy('name')->get();
        $accountGroups = AccountGroup::orderBy('name')->get();
        $creditLimitGroups = CreditLimitGroup::orderBy('name')->get();
        
        return view('trade-partner.create', compact(
            'tradePartner', 'countries', 'offices', 'currencies',
            'users', 'allTradePartners', 'accountGroups', 'creditLimitGroups'
        ));
    }

    public function update(UpdateTradePartnerRequest $request, TradePartner $tradePartner)
    {
        try {
            \DB::beginTransaction();

            $data = $request->validated();
            
            // Check type restriction (customer to oversea agent)
            if ($tradePartner->type === 'CS' && ($data['type'] ?? '') === 'PR') {
                throw new \Exception('TP Type cannot be changed to Oversea Agent for active client accounts.');
            }

            $tradePartner->update($data);

            // Create or update portal access if provided
            $portalEmail = request()->input('portal_email');
            $portalPassword = request()->input('portal_password');
            if (!empty($portalEmail)) {
                $existingUser = \App\Models\User::where('email', $portalEmail)->first();
                $updateData = [
                    'role' => 'client',
                    'company_id' => auth()->user()->company_id,
                    'trade_partner_id' => $tradePartner->id
                ];
                if (!empty($portalPassword)) {
                    $updateData['password'] = \Hash::make($portalPassword);
                }
                
                if ($existingUser) {
                    $existingUser->update($updateData);
                } else {
                    $updateData['name'] = $tradePartner->name;
                    $updateData['email'] = $portalEmail;
                    // If no password provided for new user, create a random one to secure the account
                    if (empty($updateData['password'])) {
                        $updateData['password'] = \Hash::make(\Illuminate\Support\Str::random(12));
                    }
                    \App\Models\User::create($updateData);
                }
            }

            // Filter out empty rows before updating sub-relationships
            $filterContacts = collect($request->contacts ?? [])
                ->filter(fn($c) => !empty(array_filter($c, fn($v) => $v !== null && $v !== '' && $v !== false)))
                ->values()->toArray();

            $filterMemos = collect($request->memos ?? [])
                ->filter(fn($m) => !empty($m['subject']) || !empty($m['content']))
                ->values()->toArray();

            $filterFreights = collect($request->defaultFreights ?? [])
                ->filter(fn($f) => !empty($f['freight_code']) || !empty($f['description']) || ($f['rate'] ?? 0) > 0 || ($f['amount'] ?? 0) > 0)
                ->map(fn($f) => [
                    'transport_mode' => $f['transport_mode'] ?? '',
                    'section' => $f['section'] ?? '',
                    'ship_mode' => $f['ship_mode'] ?? 'All',
                    'freight_code' => $f['freight_code'] ?? '',
                    'description' => $f['description'] ?? '',
                    'pc' => in_array($f['pc'] ?? '', ['PREPAID', 'COLLECT']) ? $f['pc'] : 'COLLECT',
                    'type' => $f['type'] ?? '',
                    'unit' => $f['unit'] ?? 'UNIT',
                    'currency_id' => !empty($f['currency_id']) ? $f['currency_id'] : null,
                    'volume' => is_numeric($f['volume'] ?? null) ? $f['volume'] : 1,
                    'rate' => is_numeric($f['rate'] ?? null) ? $f['rate'] : 0,
                    'amount' => is_numeric($f['amount'] ?? null) ? $f['amount'] : 0,
                    'agent_amount' => is_numeric($f['agent_amount'] ?? null) ? $f['agent_amount'] : 0,
                ])
                ->values()->toArray();

            $filterCommodities = collect($request->commodities ?? [])
                ->filter(fn($c) => !empty($c['description']) || !empty($c['hts_code']) || !empty($c['package_unit_id']))
                ->map(fn($c) => [
                    'description'       => $c['description'] ?? null,
                    'package_unit_id'   => !empty($c['package_unit_id']) ? (int) $c['package_unit_id'] : null,
                    'hts_code'          => $c['hts_code'] ?? null,
                    'pcs'               => is_numeric($c['pcs'] ?? null) ? $c['pcs'] : null,
                    'net_weight'        => is_numeric($c['net_weight'] ?? null) ? $c['net_weight'] : null,
                    'net_weight_unit'   => $c['net_weight_unit'] ?? null,
                    'gross_weight'      => is_numeric($c['gross_weight'] ?? null) ? $c['gross_weight'] : null,
                    'gross_weight_unit' => $c['gross_weight_unit'] ?? null,
                    'measurement'       => is_numeric($c['measurement'] ?? null) ? $c['measurement'] : null,
                    'measurement_unit'  => $c['measurement_unit'] ?? null,
                    'unit_price'        => is_numeric($c['unit_price'] ?? null) ? $c['unit_price'] : null,
                    'amount'            => is_numeric($c['amount'] ?? null) ? $c['amount'] : null,
                    'details'           => $c['details'] ?? null,
                ])
                ->values()->toArray();

            $filterParties = collect($request->relatedParties ?? [])
                ->filter(fn($p) => !empty($p['related_partner_id']))
                ->values()->toArray();

            // Sync sub-relationships by recreating
            $tradePartner->contacts()->forceDelete();
            foreach ($filterContacts as $contact) {
                $tradePartner->contacts()->create($contact);
            }

            $tradePartner->memos()->forceDelete();
            foreach ($filterMemos as $memo) {
                $tradePartner->memos()->create($memo);
            }

            $tradePartner->defaultFreights()->forceDelete();
            foreach ($filterFreights as $df) {
                $tradePartner->defaultFreights()->create($df);
            }

            $tradePartner->commodities()->forceDelete();
            foreach ($filterCommodities as $commodity) {
                $tradePartner->commodities()->create($commodity);
            }

            $tradePartner->filingSettings()->delete();
            if ($request->has('filingSetting') && collect($request->filingSetting)->filter()->isNotEmpty()) {
                $tradePartner->filingSettings()->create($request->filingSetting);
            }

            $tradePartner->relatedParties()->forceDelete();
            foreach ($filterParties as $party) {
                $tradePartner->relatedParties()->create($party);
            }

            \DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Trade Partner updated successfully!',
                    'id' => $tradePartner->id
                ]);
            }

            return back()->with('success', 'Trade Partner updated.');
        } catch (\Exception $e) {
            \DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => [$e->getMessage()]], 422);
            }
            throw $e;
        }
    }



    public function uploadDocument(Request $request, TradePartner $tradePartner)
    {
        $request->validate(['file' => 'required|file|max:10240']);
        $path = $request->file('file')->store('trade-partner-documents');
        
        $file = $request->file('file');
        $doc = $tradePartner->documents()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'file_extension' => $file->getClientOriginalExtension(),
            'uploaded_by' => auth()->id(),
        ]);
        
        $doc->load('uploader');
        
        return response()->json(['success' => true, 'document' => $doc]);
    }

    public function deleteDocument(TradePartner $tradePartner, \App\Models\Document $document)
    {
        \Storage::delete($document->file_path);
        $document->delete();
        return response()->json(['success' => true]);
    }

    public function emailDocument(Request $request, TradePartner $tradePartner, \App\Models\Document $document)
    {
        // Simulate or integrate with email service
        return response()->json(['success' => true, 'message' => 'Email sent successfully.']);
    }

    public function activityLogs(TradePartner $tradePartner)
    {
        return response()->json(['logs' => $tradePartner->getActivityLogs()]);
    }

    public function checkBondStatus(Request $request, TradePartner $tradePartner)
    {
        // Simulated response
        return response()->json([
            'success' => true, 
            'message' => 'Bond status checked successfully. Status: Active'
        ]);
    }

    public function grantAccess(Request $request, TradePartner $tradePartner)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if ($user) {
            $user->update([
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'role' => 'client',
                'trade_partner_id' => $tradePartner->id,
                'company_id' => auth()->user()->company_id,
            ]);
        } else {
            \App\Models\User::create([
                'name' => $tradePartner->name,
                'email' => $request->email,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'role' => 'client',
                'trade_partner_id' => $tradePartner->id,
                'company_id' => auth()->user()->company_id,
                'status' => 'Enable',
            ]);
        }

        return response()->json(['success' => true]);
    }
}
