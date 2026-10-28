<?php

namespace App\Http\Controllers;

use App\Models\OceanImportHbl;
use Illuminate\Http\Request;

class HouseBillOfLadingController extends Controller
{
    public function index(Request $request)
    {
        $query = OceanImportHbl::with(['oceanImport', 'customer', 'shipper', 'consignee'])->latest();

        if ($request->filled('ocean_import_id')) {
            $query->where('ocean_import_id', $request->ocean_import_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('hbl_no', 'like', "%{$s}%")
                  ->orWhere('po_no', 'like', "%{$s}%");
            });
        }

        $hbls = $request->has('limit') ? $query->paginate($request->input('limit', 25)) : $query->get();
        return response()->json($hbls);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ocean_import_id' => 'required|exists:ocean_imports,id',
            'hbl_no' => 'required|string|max:255',
            'customer_id' => 'nullable|exists:trade_partners,id',
            'shipper_id' => 'nullable|exists:trade_partners,id',
            'consignee_id' => 'nullable|exists:trade_partners,id',
            'quotation_no' => 'nullable|string|max:255',
        ]);

        $hbl = OceanImportHbl::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'House Bill of Lading created successfully.',
            'data' => $hbl->load(['oceanImport', 'customer'])
        ], 201);
    }

    public function show($id)
    {
        $hbl = OceanImportHbl::with(['oceanImport', 'customer', 'shipper', 'consignee', 'notifyParty'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $hbl
        ]);
    }

    public function update(Request $request, $id)
    {
        $hbl = OceanImportHbl::findOrFail($id);

        $validated = $request->validate([
            'hbl_no' => 'nullable|string|max:255',
            'customer_id' => 'nullable|exists:trade_partners,id',
            'shipper_id' => 'nullable|exists:trade_partners,id',
            'consignee_id' => 'nullable|exists:trade_partners,id',
            'quotation_no' => 'nullable|string|max:255',
        ]);

        $hbl->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'House Bill of Lading updated successfully.',
            'data' => $hbl->load(['oceanImport', 'customer'])
        ]);
    }

    public function destroy($id)
    {
        $hbl = OceanImportHbl::findOrFail($id);
        $hbl->delete();

        return response()->json([
            'success' => true,
            'message' => 'House Bill of Lading deleted successfully.'
        ]);
    }
}
