<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FacilityController extends Controller
{
    public function index(Request $request)
    {
        $query = Facility::with('category');

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by availability
        if ($request->has('available')) {
            $query->where('is_available', $request->available);
        }

        $facilities = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $facilities
        ]);
    }

    public function show($id)
    {
        $facility = Facility::with(['category', 'timeSlots'])->find($id);

        if (!$facility) {
            return response()->json([
                'success' => false,
                'message' => 'Facility not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $facility
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'price_per_hour' => 'required|numeric|min:0',
            'image' => 'nullable|string',
            'is_available' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $facility = Facility::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Facility created successfully',
            'data' => $facility
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $facility = Facility::find($id);

        if (!$facility) {
            return response()->json([
                'success' => false,
                'message' => 'Facility not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'location' => 'sometimes|string|max:255',
            'capacity' => 'sometimes|integer|min:1',
            'price_per_hour' => 'sometimes|numeric|min:0',
            'image' => 'nullable|string',
            'is_available' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $facility->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Facility updated successfully',
            'data' => $facility
        ]);
    }

    public function destroy($id)
    {
        $facility = Facility::find($id);

        if (!$facility) {
            return response()->json([
                'success' => false,
                'message' => 'Facility not found'
            ], 404);
        }

        $facility->delete();

        return response()->json([
            'success' => true,
            'message' => 'Facility deleted successfully'
        ]);
    }

    public function bookings(Request $request, $id)
    {
        $facility = Facility::find($id);

        if (!$facility) {
            return response()->json([
                'success' => false,
                'message' => 'Facility not found'
            ], 404);
        }

        $query = $facility->bookings()->with(['timeSlot', 'user']);

        // Filter by year and month
        if ($request->has('year') && $request->has('month')) {
            $year = $request->year;
            $month = str_pad($request->month, 2, '0', STR_PAD_LEFT);
            $query->whereYear('booking_date', $year)
                  ->whereMonth('booking_date', $month);
        }

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('booking_date', $request->date);
        }

        // Only show confirmed and pending bookings (not cancelled)
        $query->whereIn('status', ['pending', 'confirmed', 'completed']);

        $bookings = $query->orderBy('booking_date', 'asc')
                          ->orderBy('time_slot_id', 'asc')
                          ->get();

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }
}
