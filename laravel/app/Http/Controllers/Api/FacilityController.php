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
        $user = $request->user();

        $query = Facility::with('category');

        // If the requester is a district admin, force district filter to their district
        if ($user && $user->role === 'district_admin') {
            $query->where('district', $user->district);
        } elseif ($request->has('district') && $request->district) {
            // Allow district filtering via query param for master/state admins or public listing
            $query->where('district', $request->district);
        }

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
            'price_per_day' => 'nullable|numeric|min:0',
            'image' => 'nullable|string',
            'is_available' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        // If the authenticated user is a district admin, always assign their district
        $user = $request->user();
        if ($user && $user->role === 'district_admin') {
            $data['district'] = $user->district;
        }

        $facility = Facility::create($data);

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
            'price_per_day' => 'nullable|numeric|min:0',
            'image' => 'nullable|string',
            'is_available' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // If district admin, ensure they can only update facilities in their district
        if ($user && $user->role === 'district_admin' && $facility->district !== $user->district) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: cannot modify facility outside your district'
            ], 403);
        }

        $data = $request->all();
        // Prevent district admin from changing the district field
        if ($user && $user->role === 'district_admin') {
            unset($data['district']);
        }

        $facility->update($data);

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

        $user = request()->user();
        if ($user && $user->role === 'district_admin' && $facility->district !== $user->district) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: cannot delete facility outside your district'
            ], 403);
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

        $query = $facility->bookings()->with(['user']);

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
                  ->orderBy('start_time', 'asc')
                  ->get();

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }
}
