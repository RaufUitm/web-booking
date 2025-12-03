<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function dashboard(Request $request)
    {
        try {
            $user = $request->user();
            $district = $request->query('district');

            // Apply district filter based on role
            $districtFilter = null;
            if ($user->role === 'district_admin') {
                // District admin can only see their district
                $districtFilter = $user->district;
            } elseif (in_array($user->role, ['state_admin', 'master_admin']) && $district) {
                // State/Master admin can filter by selected district
                $districtFilter = $district;
            }

            // Build queries with district filter
            $userQuery = User::query();
            $bookingQuery = Booking::query();
            // facility query should use the Facility model
            $facilityQuery = Facility::query();

            if ($districtFilter) {
                $userQuery->where('district', $districtFilter);
                $facilityQuery->where('district', $districtFilter);
                // Filter bookings by facilities in the district
                $facilityIds = Facility::where('district', $districtFilter)->pluck('id');
                $bookingQuery->whereIn('facility_id', $facilityIds);
            }

            // Get booking IDs for revenue calculation
            $bookingIds = (clone $bookingQuery)->pluck('id');
            $confirmedBookingIds = (clone $bookingQuery)->whereIn('status', ['confirmed', 'completed'])->pluck('id');
            
            // Calculate revenue from payments table
            $totalRevenue = 0;
            $monthlyRevenue = 0;
            
            if ($confirmedBookingIds->isNotEmpty()) {
                $totalRevenue = DB::table('payments')
                    ->whereIn('booking_id', $confirmedBookingIds)
                    ->where('payment_status', 'completed')
                    ->sum('amount') ?? 0;
                    
                $monthlyRevenue = DB::table('payments')
                    ->whereIn('booking_id', $confirmedBookingIds)
                    ->where('payment_status', 'completed')
                    ->whereMonth('created_at', now()->month)
                    ->sum('amount') ?? 0;
            }
            
            $stats = [
                'total_users' => $userQuery->count(),
                'total_bookings' => (clone $bookingQuery)->count(),
                'total_facilities' => $facilityQuery->count(),
                'pending_bookings' => (clone $bookingQuery)->where('status', 'pending')->count(),
                'confirmed_bookings' => (clone $bookingQuery)->where('status', 'confirmed')->count(),
                'total_revenue' => $totalRevenue,
                'monthly_revenue' => $monthlyRevenue,
            ];

            $recent_bookings = (clone $bookingQuery)
                ->with(['user', 'facility'])
                ->latest()
                ->take(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'recent_bookings' => $recent_bookings,
                    'filtered_district' => $districtFilter
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all users
     */
    public function getUsers(Request $request)
    {
        try {
            $query = User::query();

            // Filter by role
            if ($request->has('role') && $request->role !== '') {
                $query->where('role', $request->role);
            }

            // Search
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('ic_number', 'like', "%{$search}%");
                });
            }

            $users = $query->latest()->get();

            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new user
     */
    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'ic_number' => 'nullable|string|max:20',
            'role' => 'required|in:user,district_admin,state_admin,master_admin',
            'district' => 'required_if:role,district_admin|nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'ic_number' => $request->ic_number,
                'role' => $request->role,
                'district' => $request->role === 'district_admin' ? $request->district : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'ic_number' => 'nullable|string|max:20',
            'role' => 'sometimes|required|in:user,district_admin,state_admin,master_admin',
            'district' => 'required_if:role,district_admin|nullable|string|max:255',
            'password' => 'nullable|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->only(['name', 'email', 'phone', 'ic_number', 'role']);

            // Handle district field
            if ($request->role === 'district_admin') {
                $data['district'] = $request->district;
            } else {
                $data['district'] = null;
            }

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user
     */
    public function deleteUser($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Prevent deleting self
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete your own account'
                ], 403);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all bookings for admin
     */
    public function getBookings(Request $request)
    {
        try {
            $user = $request->user();

            $query = Booking::with(['user', 'facility']);

            // If requester is a district admin, restrict bookings to facilities in their district
            if ($user && $user->role === 'district_admin') {
                $facilityIds = Facility::where('district', $user->district)->pluck('id');
                $query->whereIn('facility_id', $facilityIds);
            } elseif ($request->has('district') && $request->district) {
                // Allow filtering by district for state/master admins
                $facilityIds = Facility::where('district', $request->district)->pluck('id');
                $query->whereIn('facility_id', $facilityIds);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by date range
            if ($request->has('from_date')) {
                $query->whereDate('booking_date', '>=', $request->from_date);
            }

            if ($request->has('to_date')) {
                $query->whereDate('booking_date', '<=', $request->to_date);
            }

            // Search
            if ($request->has('search')) {
                $search = $request->search;
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('facility', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            $bookings = $query->with(['payment'])->latest()->paginate($request->get('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => $bookings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch bookings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update booking status
     */
    public function updateBookingStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,cancelled,completed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $booking = Booking::with('facility')->find($id);

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            // If district admin, ensure the booking belongs to a facility in their district
            $user = $request->user();
            if ($user && $user->role === 'district_admin') {
                if (!$booking->facility || $booking->facility->district !== $user->district) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Forbidden: cannot modify booking outside your district'
                    ], 403);
                }
            }

            $booking->update([
                'status' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking status updated successfully',
                'data' => $booking->load(['user', 'facility'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update booking status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reports
     */
    public function getReports(Request $request)
    {
        try {
            $type = $request->get('type', 'monthly');
            $year = $request->get('year', now()->year);
            $month = $request->get('month', now()->month);
            $district = $request->get('district');

            $data = [];

            switch ($type) {
                case 'monthly':
                    $data = $this->getMonthlyReport($year, $month, $district);
                    break;
                case 'yearly':
                    $data = $this->getYearlyReport($year, $district);
                    break;
                case 'facility':
                    $data = $this->getFacilityReport($year, $month, $district);
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getMonthlyReport($year, $month, $district = null)
    {
        $query = Booking::whereYear('booking_date', $year)
            ->whereMonth('booking_date', $month);

        // Apply district filter
        if ($district) {
            $query->whereHas('facility', function($q) use ($district) {
                $q->where('district', $district);
            });
        }

        $bookings = $query->selectRaw('DATE(booking_date) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Calculate revenue per date
        foreach ($bookings as $booking) {
            $dateQuery = Booking::whereDate('booking_date', $booking->date);
            
            if ($district) {
                $dateQuery->whereHas('facility', function($q) use ($district) {
                    $q->where('district', $district);
                });
            }
            
            $dateBookingIds = $dateQuery->pluck('id');
            $booking->revenue = DB::table('payments')
                ->whereIn('booking_id', $dateBookingIds)
                ->where('payment_status', 'completed')
                ->sum('amount');
        }

        $totalRevenue = $bookings->sum('revenue');

        return [
            'bookings' => $bookings,
            'total_bookings' => $bookings->sum('total'),
            'total_revenue' => $totalRevenue
        ];
    }

    private function getYearlyReport($year, $district = null)
    {
        $query = Booking::whereYear('booking_date', $year);

        // Apply district filter
        if ($district) {
            $query->whereHas('facility', function($q) use ($district) {
                $q->where('district', $district);
            });
        }

        $bookings = $query->selectRaw('MONTH(booking_date) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Calculate revenue per month
        foreach ($bookings as $booking) {
            $monthQuery = Booking::whereYear('booking_date', $year)
                ->whereMonth('booking_date', $booking->month);
            
            if ($district) {
                $monthQuery->whereHas('facility', function($q) use ($district) {
                    $q->where('district', $district);
                });
            }
            
            $monthBookingIds = $monthQuery->pluck('id');
            
            $booking->revenue = DB::table('payments')
                ->whereIn('booking_id', $monthBookingIds)
                ->where('payment_status', 'completed')
                ->sum('amount');
        }

        $totalRevenue = $bookings->sum('revenue');

        return [
            'bookings' => $bookings,
            'total_bookings' => $bookings->sum('total'),
            'total_revenue' => $totalRevenue
        ];
    }

    private function getFacilityReport($year, $month, $district = null)
    {
        $query = Booking::with('facility')
            ->whereYear('booking_date', $year)
            ->whereMonth('booking_date', $month);

        // Apply district filter
        if ($district) {
            $query->whereHas('facility', function($q) use ($district) {
                $q->where('district', $district);
            });
        }

        $bookings = $query->selectRaw('facility_id, COUNT(*) as total')
            ->groupBy('facility_id')
            ->orderByDesc('total')
            ->get();

        // Calculate revenue from payments for each facility
        foreach ($bookings as $booking) {
            $facilityQuery = Booking::where('facility_id', $booking->facility_id)
                ->whereYear('booking_date', $year)
                ->whereMonth('booking_date', $month);
            
            if ($district) {
                $facilityQuery->whereHas('facility', function($q) use ($district) {
                    $q->where('district', $district);
                });
            }
            
            $facilityBookingIds = $facilityQuery->pluck('id');
            
            $booking->revenue = DB::table('payments')
                ->whereIn('booking_id', $facilityBookingIds)
                ->where('payment_status', 'completed')
                ->sum('amount');
        }

        return [
            'bookings' => $bookings,
            'total_bookings' => $bookings->sum('total'),
            'total_revenue' => $bookings->sum('revenue')
        ];
    }

    /**
     * Get all categories
     */
    public function getCategories(Request $request)
    {
        try {
            $categories = Category::withCount('facilities')
                ->orderBy('name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new category
     */
    public function createCategory(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:categories,name',
                'description' => 'nullable|string',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $category = Category::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a category
     */
    public function updateCategory(Request $request, $id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:categories,name,' . $id,
                'description' => 'nullable|string',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $category->update([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a category
     */
    public function deleteCategory($id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            // Check if category has facilities
            $facilitiesCount = $category->facilities()->count();
            if ($facilitiesCount > 0) {
                // Optional: Decide whether to prevent deletion or just warn
                // For now, we'll allow deletion but set facilities' category_id to null
                $category->facilities()->update(['category_id' => null]);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
