<?php

namespace App\Http\Controllers;

use App\Models\OceanBooking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = OceanBooking::with(['customer', 'carrier', 'vessel', 'pol', 'pod']);
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($query->latest()->paginate($request->get('per_page', 25)));
        }
        return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_no' => 'nullable|string|max:100',
            'booking_date' => 'nullable|date',
            'customer_id' => 'nullable|integer',
            'carrier_id' => 'nullable|integer',
            'status' => 'nullable|string',
        ]);

        $data = $request->all();
        if (empty($data['booking_no'])) {
            $data['booking_no'] = 'BKG-' . strtoupper(uniqid());
        }

        $booking = OceanBooking::create($data);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => $booking->load(['customer', 'carrier', 'vessel', 'pol', 'pod'])
            ], 201);
        }

        return redirect()->back()->with('success', 'Booking created successfully');
    }

    public function show($id)
    {
        $booking = OceanBooking::with([
            'customer', 'carrier', 'vessel', 'pol', 'pod',
            'actualShipper', 'consignee', 'notify', 'billTo'
        ])->findOrFail($id);

        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $booking
            ]);
        }

        return response()->json(['success' => true, 'data' => $booking]);
    }

    public function update(Request $request, $id)
    {
        $booking = OceanBooking::findOrFail($id);
        $booking->update($request->all());

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Booking updated successfully',
                'data' => $booking->load(['customer', 'carrier', 'vessel', 'pol', 'pod'])
            ]);
        }

        return redirect()->back()->with('success', 'Booking updated successfully');
    }

    public function destroy($id)
    {
        $booking = OceanBooking::findOrFail($id);
        $booking->delete();

        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Booking deleted successfully'
            ]);
        }

        return redirect()->back()->with('success', 'Booking deleted successfully');
    }
}
