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

        // For the new per_hour/per_day model we detect overlaps between per_hour bookings
        // and defined time slots. A slot is considered booked if any per_hour booking
        // on the same date overlaps the slot's time range.
        $availableSlots = $timeSlots->map(function ($slot) use ($request) {
            $isBooked = Booking::where('facility_id', $request->facility_id)
                ->where('booking_date', $request->date)
                ->where('booking_type', 'per_hour')
                ->whereIn('status', ['pending', 'confirmed'])
                ->whereRaw('start_time < ? AND end_time > ?', [$slot->end_time, $slot->start_time])
                ->exists();

            $slot->is_booked = $isBooked;
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
