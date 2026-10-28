<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = Schedule::with(['vessel', 'office']);
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($query->latest()->paginate($request->get('per_page', 25)));
        }
        return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'schedule_no' => 'nullable|string|max:100',
            'vessel_name' => 'nullable|string',
            'voyage' => 'nullable|string',
            'pol_name' => 'nullable|string',
            'pod_name' => 'nullable|string',
            'etd' => 'nullable|date',
            'eta' => 'nullable|date',
        ]);

        $data = $request->all();
        if (empty($data['schedule_no'])) {
            $data['schedule_no'] = 'SCH-' . strtoupper(uniqid());
        }

        $schedule = Schedule::create($data);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Schedule created successfully',
                'data' => $schedule->load(['vessel', 'office'])
            ], 201);
        }

        return redirect()->back()->with('success', 'Schedule created successfully');
    }

    public function show($id)
    {
        $schedule = Schedule::with(['vessel', 'office', 'charges', 'documents'])->findOrFail($id);

        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $schedule
            ]);
        }

        return response()->json(['success' => true, 'data' => $schedule]);
    }

    public function update(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->update($request->all());

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Schedule updated successfully',
                'data' => $schedule->load(['vessel', 'office'])
            ]);
        }

        return redirect()->back()->with('success', 'Schedule updated successfully');
    }

    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Schedule deleted successfully'
            ]);
        }

        return redirect()->back()->with('success', 'Schedule deleted successfully');
    }
}
