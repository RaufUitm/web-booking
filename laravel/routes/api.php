<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FacilityController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TimeSlotController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

// Public facility and category browsing
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::get('/facilities', [FacilityController::class, 'index']);
Route::get('/facilities/{id}', [FacilityController::class, 'show']);
Route::get('/facilities/{id}/bookings', [FacilityController::class, 'bookings']);
// Allow public access to available time slots so guests can view slot availability
Route::get('/time-slots/available', [TimeSlotController::class, 'available']);

// Payment callback - Must be public for toyyibPay webhook
Route::post('/payment/callback', [PaymentController::class, 'handleCallback']);
Route::get('/payment/return', [PaymentController::class, 'handleReturn']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Time slots
    Route::get('/time-slots', [TimeSlotController::class, 'index']);

    // Bookings - User routes
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::put('/bookings/{id}', [BookingController::class, 'update']);
    Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel']);

    // Payment routes - Require authentication
    Route::post('/payment/create', [PaymentController::class, 'createPayment']);
    Route::get('/payment/verify', [PaymentController::class, 'verifyPayment']);
    Route::post('/payment/check-status', [PaymentController::class, 'checkPaymentStatus']);
    // Invoice download
    Route::get('/bookings/{id}/invoice', [PaymentController::class, 'downloadInvoice']);

    // Admin routes - All admin roles can access
    Route::middleware('admin.role')->prefix('admin')->group(function () {
        // Dashboard - All admins
        Route::get('/dashboard', [AdminController::class, 'dashboard']);

        // Booking management - All admins
        Route::get('/bookings', [AdminController::class, 'getBookings']);
        Route::put('/bookings/{id}/status', [AdminController::class, 'updateBookingStatus']);
        Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);

        // Reports - All admins
        Route::get('/reports', [AdminController::class, 'getReports']);

        // Category management - All admins
        Route::get('/categories', [AdminController::class, 'getCategories']);
        Route::post('/categories', [AdminController::class, 'createCategory']);
        Route::put('/categories/{id}', [AdminController::class, 'updateCategory']);
        Route::delete('/categories/{id}', [AdminController::class, 'deleteCategory']);

        // Facility management - All admins
        Route::post('/facilities', [FacilityController::class, 'store']);
        Route::put('/facilities/{id}', [FacilityController::class, 'update']);
        Route::post('/facilities/{id}', [FacilityController::class, 'update']); // Accept POST for multipart updates
        Route::delete('/facilities/{id}', [FacilityController::class, 'destroy']);

        // Time slot management - All admins
        Route::post('/time-slots', [TimeSlotController::class, 'store']);
        Route::put('/time-slots/{id}', [TimeSlotController::class, 'update']);
        Route::delete('/time-slots/{id}', [TimeSlotController::class, 'destroy']);

        // User management - Only Master/State admins
        Route::middleware('admin.role:master_admin,state_admin')->group(function () {
            Route::get('/users', [AdminController::class, 'getUsers']);
            Route::post('/users', [AdminController::class, 'createUser']);
            Route::put('/users/{id}', [AdminController::class, 'updateUser']);
            Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        });

        // System settings - Only Master admin
        Route::middleware('admin.role:master_admin')->group(function () {
            Route::get('/settings', [AdminController::class, 'getSettings']);
            Route::put('/settings', [AdminController::class, 'updateSettings']);
        });
    });
});

