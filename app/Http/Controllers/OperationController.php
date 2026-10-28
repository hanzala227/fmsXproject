<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use Illuminate\Http\Request;

class OperationController extends Controller
{
    public function index(Request $request)
    {
        $query = Operation::query();
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($query->latest()->paginate($request->get('per_page', 25)));
        }
        return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'operation_number' => 'nullable|string|max:100',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string|max:50',
        ]);

        $data = $request->all();
        if (empty($data['operation_number'])) {
            $data['operation_number'] = 'OP-' . strtoupper(uniqid());
        }

        $operation = Operation::create($data);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Operation created successfully',
                'data' => $operation
            ], 201);
        }

        return redirect()->back()->with('success', 'Operation created successfully');
    }

    public function show($id)
    {
        $operation = Operation::findOrFail($id);

        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $operation
            ]);
        }

        return response()->json(['success' => true, 'data' => $operation]);
    }

    public function update(Request $request, $id)
    {
        $operation = Operation::findOrFail($id);
        $operation->update($request->all());

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Operation updated successfully',
                'data' => $operation
            ]);
        }

        return redirect()->back()->with('success', 'Operation updated successfully');
    }

    public function destroy($id)
    {
        $operation = Operation::findOrFail($id);
        $operation->delete();

        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Operation deleted successfully'
            ]);
        }

        return redirect()->back()->with('success', 'Operation deleted successfully');
    }
}
