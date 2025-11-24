<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        // Get bookings for authenticated user
        $bookings = Booking::with(['facility'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'booking_type' => 'required|in:per_day,per_hour',
            'booking_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'number_of_people' => 'required|integer|min:1',
            'notes' => 'nullable|string'
        ]);

        $data = $validated;
        $data['user_id'] = $request->user()->id;
        $data['status'] = 'pending';

        $booking = Booking::create($data);

        return response()->json($booking->load(['facility']), 201);
    }

    public function show(Booking $booking)
    {
        return $booking->load(['service']);
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:pending,confirmed,cancelled,completed',
            'notes' => 'sometimes|nullable|string'
        ]);

        $booking->update($validated);

        return response()->json($booking->load(['service']));
    }

    public function cancel($id)
    {
        $booking = Booking::with(['facility'])->findOrFail($id);
        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully',
            'data' => $booking->fresh(['facility'])
        ]);
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();
        return response()->json(null, 204);
    }
}
