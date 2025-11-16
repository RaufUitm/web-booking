<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TimeSlotController extends Controller
{
    public function index(Request $request)
    {
        $query = TimeSlot::query();

        // Filter by facility
        if ($request->has('facility_id')) {
            $query->where('facility_id', $request->facility_id);
        }

        $timeSlots = $query->orderBy('start_time')->get();

        return response()->json([
            'success' => true,
            'data' => $timeSlots
        ]);
    }

    public function available(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'facility_id' => 'required|exists:facilities,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $timeSlots = TimeSlot::where('facility_id', $request->facility_id)->get();

        // Get booked time slots for the date
        $bookedSlots = Booking::where('facility_id', $request->facility_id)
            ->where('booking_date', $request->date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('time_slot_id')
            ->toArray();

        // Mark slots as available or not
        $availableSlots = $timeSlots->map(function ($slot) use ($bookedSlots) {
            $slot->is_booked = in_array($slot->id, $bookedSlots);
            return $slot;
        });

        return response()->json([
            'success' => true,
            'data' => $availableSlots
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'facility_id' => 'required|exists:facilities,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $timeSlot = TimeSlot::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Time slot created successfully',
            'data' => $timeSlot
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $timeSlot = TimeSlot::find($id);

        if (!$timeSlot) {
            return response()->json([
                'success' => false,
                'message' => 'Time slot not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $timeSlot->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Time slot updated successfully',
            'data' => $timeSlot
        ]);
    }

    public function destroy($id)
    {
        $timeSlot = TimeSlot::find($id);

        if (!$timeSlot) {
            return response()->json([
                'success' => false,
                'message' => 'Time slot not found'
            ], 404);
        }

        $timeSlot->delete();

        return response()->json([
            'success' => true,
            'message' => 'Time slot deleted successfully'
        ]);
    }
}
