<?php 

namespace App\Http\Controllers;

use App\Models\Port;
use Illuminate\Http\Request;

class PortController extends Controller
{
    public function index(Request $request)
    {
        $query = Port::query();
        if ($request->has('q')) {
            $query->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('code', 'like', '%' . $request->q . '%');
        }
        return response()->json($query->limit(10)->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:10|unique:ports,code',
            'country_id' => 'nullable|integer',
            'country_code' => 'nullable',
        ]);

        if (empty($validated['country_id'])) {
            if (!empty($validated['country_code'])) {
                $country = \App\Models\Country::where('code', $validated['country_code'])->first();
                if ($country) {
                    $validated['country_id'] = $country->id;
                }
            }
            if (empty($validated['country_id'])) {
                $validated['country_id'] = \App\Models\Country::first()->id ?? 1;
            }
        }

        $port = Port::create($validated);

        return response()->json([
            'id' => $port->id,
            'name' => $port->name,
            'code' => $port->code,
        ], 201);
    }

public function search(Request $request)
{
    $q = trim($request->query('q', ''));
    $type = strtoupper(trim($request->query('type', '')));

    $query = Port::query();

    // Agar type AIR ya SEA specify ki ho to filter karo, warna fallback to SEA default ya all
    if (in_array($type, ['AIR', 'SEA'], true)) {
        $query->where('type', $type);
    }

    // Port name se search
    if ($q !== '') {
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $query->where('name', 'like', '%' . $q . '%');
    }

    // Latest 30 records
    $ports = $query
        ->orderByDesc('id')
        ->limit(30)
        ->get(['id', 'name']);

    return response()->json($ports);
}

    public function show($id)
    {
        $port = Port::with('country')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $port
        ]);
    }

    public function update(Request $request, $id)
    {
        $port = Port::findOrFail($id);
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:10|unique:ports,code,' . $port->id,
            'country_id' => 'nullable|integer',
            'type' => 'nullable|string|in:AIR,SEA,LAND',
        ]);

        $port->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Port updated successfully.',
            'data' => $port
        ]);
    }

    public function destroy($id)
    {
        $port = Port::findOrFail($id);
        $port->delete();

        return response()->json([
            'success' => true,
            'message' => 'Port deleted successfully.'
        ]);
    }
}
