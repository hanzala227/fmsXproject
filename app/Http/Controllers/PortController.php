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

        $query = Port::query()->with('country')->whereNull('deleted_at');

        // Filter by AIR or SEA if specified
        if (in_array($type, ['AIR', 'SEA'], true)) {
            $query->where('type', $type);
        }

        // Search by Code, City, Name, or Country
        if ($q !== '') {
            if (mb_strlen($q) < 2) {
                return response()->json([]);
            }

            $query->where(function ($sub) use ($q) {
                $sub->where('ports.code', 'like', $q . '%')
                    ->orWhere('ports.city', 'like', $q . '%')
                    ->orWhere('ports.name', 'like', '%' . $q . '%')
                    ->orWhereHas('country', fn($cq) => $cq->where('name', 'like', $q . '%'));
            });

            // Priority: Exact Code Match > Code Prefix > City Prefix > Name Prefix > Substring
            $query->orderByRaw("CASE 
                WHEN ports.code = ? THEN 1 
                WHEN ports.code LIKE ? THEN 2 
                WHEN ports.city LIKE ? THEN 3 
                WHEN ports.name LIKE ? THEN 4 
                ELSE 5 
            END", [$q, "{$q}%", "{$q}%", "{$q}%"])
            ->orderBy('ports.name');
        } else {
            $query->orderByDesc('ports.id');
        }

        // Return max 30 records with standard rich label: [CODE] Name (City, Country)
        $ports = $query->limit(30)->get()->map(function ($p) {
            $country = $p->country?->name ?? '';
            $city = $p->city ?? '';
            $locationParts = array_filter([$city, $country]);
            $locStr = !empty($locationParts) ? ' (' . implode(', ', $locationParts) . ')' : '';
            $label = ($p->code ? "[{$p->code}] " : '') . $p->name . $locStr;

            return [
                'id'   => $p->id,
                'code' => $p->code,
                'name' => $label,
            ];
        });

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
