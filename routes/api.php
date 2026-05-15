<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// Import Admin Controllers
use App\Http\Controllers\Api\Admin\EquipmentController as AdminEquipmentController;
use App\Http\Controllers\Api\Admin\BookingController as AdminBookingController;

// Import Member Controllers
use App\Http\Controllers\Api\Member\EquipmentController as MemberEquipmentController;
use App\Http\Controllers\Api\Member\BookingController as MemberBookingController;

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {

    Route::middleware('ability:equipment:manage')->prefix('admin')->group(function () {
        Route::apiResource('equipments', AdminEquipmentController::class);
        Route::get('bookings', [AdminBookingController::class, 'index']);
    });

    Route::prefix('member')->group(function () {

        // Izin melihat alat
        Route::middleware('ability:equipment:read')->group(function () {
            Route::get('equipments', [MemberEquipmentController::class, 'index']);
        });

        // Izin mengatur peminjaman dan check-in
        Route::middleware('ability:booking:create,booking:check-in')->group(function () {
            Route::get('my-bookings', [MemberBookingController::class, 'myBookings']);
            Route::post('bookings', [MemberBookingController::class, 'store']);
            Route::post('check-in', [MemberBookingController::class, 'checkIn']);
        });
    });
});
