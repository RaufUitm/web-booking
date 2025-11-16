<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\TimeSlot;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        // Get bookings for authenticated user
        $bookings = Booking::with(['facility', 'timeSlot'])
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
            'service_id' => 'required|exists:services,id',
            'time_slot_id' => 'required|exists:time_slots,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string|max:20',
            'notes' => 'nullable|string'
        ]);

        // Check if time slot is available
        $timeSlot = TimeSlot::find($validated['time_slot_id']);
        if (!$timeSlot->is_available) {
            return response()->json(['message' => 'Time slot not available'], 422);
        }

        $booking = Booking::create($validated);

        // Mark time slot as unavailable
        $timeSlot->update(['is_available' => false]);

        return response()->json($booking->load(['service', 'timeSlot']), 201);
    }

    public function show(Booking $booking)
    {
        return $booking->load(['service', 'timeSlot']);
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:pending,confirmed,cancelled,completed',
            'notes' => 'sometimes|nullable|string'
        ]);

        $booking->update($validated);

        // If cancelled, make time slot available again
        if (isset($validated['status']) && $validated['status'] === 'cancelled') {
            $booking->timeSlot->update(['is_available' => true]);
        }

        return response()->json($booking->load(['service', 'timeSlot']));
    }

    public function cancel($id)
    {
        $booking = Booking::with(['facility', 'timeSlot'])->findOrFail($id);

        // Update booking status to cancelled
        $booking->update(['status' => 'cancelled']);

        // Make time slot available again
        if ($booking->timeSlot) {
            $booking->timeSlot->update(['is_available' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully',
            'data' => $booking->fresh(['facility', 'timeSlot'])
        ]);
    }

    public function destroy(Booking $booking)
    {
        $booking->timeSlot->update(['is_available' => true]);
        $booking->delete();
        return response()->json(null, 204);
    }
}
