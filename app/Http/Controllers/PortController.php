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

    // Sirf AIR aur SEA types allow hain
    if (!in_array($type, ['AIR', 'SEA'], true)) {
        return response()->json([]);
    }

    $query = Port::query()
        ->where('type', $type);

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
   
}
