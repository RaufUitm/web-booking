<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Mail\BookingConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'facility']);

        // Always filter by user_id - users (including admin) only see their own bookings
        // Admins can see all bookings through the admin/bookings endpoint
        $query->where('user_id', $request->user()->id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    public function show($id)
    {
        $booking = Booking::with(['user', 'facility', 'payment'])->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        // Check if user has permission to view this booking
        if ($booking->user_id !== request()->user()->id && request()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $booking
        ]);
    }

    public function store(Request $request)
    {
        // Booking can be either per_day or per_hour (start_time/end_time)
        $validator = Validator::make($request->all(), [
            'facility_id' => 'required|exists:facilities,id',
            'booking_type' => 'required|in:per_day,per_hour',
            'booking_date' => 'required|date|after_or_equal:today',
            'number_of_people' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Additional validation based on booking type
        $bookingType = $request->booking_type;
        if ($bookingType === 'per_hour') {
            $hourValidator = Validator::make($request->all(), [
                'start_time' => ['required', 'date_format:H:i'],
                'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            ]);
            if ($hourValidator->fails()) {
                return response()->json(['success' => false, 'errors' => $hourValidator->errors()], 422);
            }

            // Check overlapping per_hour bookings for same facility/date
            $start = $request->start_time;
            $end = $request->end_time;

            $conflict = Booking::where('facility_id', $request->facility_id)
                ->where('booking_date', $request->booking_date)
                ->where('booking_type', 'per_hour')
                ->whereIn('status', ['pending', 'confirmed'])
                ->where(function ($q) use ($start, $end) {
                    // overlap if existing.start < new.end AND existing.end > new.start
                    $q->whereRaw("start_time < ? AND end_time > ?", [$end, $start]);
                })
                ->first();

            if ($conflict) {
                return response()->json(['success' => false, 'message' => 'The selected time range overlaps with another booking'], 409);
            }

            $bookingData = [
                'user_id' => $request->user()->id,
                'facility_id' => $request->facility_id,
                'booking_type' => 'per_hour',
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'booking_date' => $request->booking_date,
                'number_of_people' => $request->number_of_people,
                'notes' => $request->notes,
                'status' => 'pending',
            ];

        } else { // per_day
            // ensure no bookings already exist for this facility/date
            $existing = Booking::where('facility_id', $request->facility_id)
                ->where('booking_date', $request->booking_date)
                ->whereIn('status', ['pending', 'confirmed'])
                ->first();

            if ($existing) {
                return response()->json(['success' => false, 'message' => 'Facility already has a booking on this date'], 409);
            }

            $bookingData = [
                'user_id' => $request->user()->id,
                'facility_id' => $request->facility_id,
                'booking_type' => 'per_day',
                'booking_date' => $request->booking_date,
                'number_of_people' => $request->number_of_people,
                'notes' => $request->notes,
                'status' => 'pending',
            ];
        }

        $booking = Booking::create($bookingData);

        // Send confirmation email
        try {
            Mail::to($request->user()->email)->send(new BookingConfirmation($booking));
        } catch (\Exception $e) {
            // Log error but don't fail the booking
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully',
            'data' => $booking->load(['facility'])
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        // Check permissions
        if ($booking->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:pending,confirmed,cancelled,completed',
            'booking_date' => 'sometimes|date|after_or_equal:today',
            'number_of_people' => 'sometimes|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $booking->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully',
            'data' => $booking->load(['facility'])
        ]);
    }

    public function cancel($id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        // Check permissions
        if ($booking->user_id !== request()->user()->id && request()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully',
            'data' => $booking
        ]);
    }

    public function destroy($id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        // Only admins can delete bookings
        if (request()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $booking->delete();

        return response()->json([
            'success' => true,
            'message' => 'Booking deleted successfully'
        ]);
    }
}
