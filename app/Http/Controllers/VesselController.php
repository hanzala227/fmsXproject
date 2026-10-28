<?php

namespace App\Http\Controllers;

use App\Models\Vessel;
use Illuminate\Http\Request;

class VesselController extends Controller
{
    public function index(Request $request)
    {
        $query = Vessel::query();
        if ($request->has('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }
        return response()->json($query->limit($request->get('limit', 25))->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'call_sign' => 'nullable',
            'mmsi' => 'nullable',
            'imo' => 'nullable',
        ]);
        return response()->json(Vessel::create($validated), 201);
    }

    public function show($id)
    {
        $vessel = Vessel::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $vessel
        ]);
    }

    public function update(Request $request, $id)
    {
        $vessel = Vessel::findOrFail($id);
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'call_sign' => 'nullable|string|max:50',
            'mmsi' => 'nullable|string|max:50',
            'imo' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        $vessel->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vessel updated successfully.',
            'data' => $vessel
        ]);
    }

    public function destroy($id)
    {
        $vessel = Vessel::findOrFail($id);
        $vessel->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vessel deleted successfully.'
        ]);
    }
}
