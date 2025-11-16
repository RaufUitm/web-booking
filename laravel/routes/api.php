<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\TimeSlotController;

Route::apiResource('bookings', BookingController::class);
Route::apiResource('services', ServiceController::class)->only(['index', 'show']);
Route::get('time-slots', [TimeSlotController::class, 'index']);

