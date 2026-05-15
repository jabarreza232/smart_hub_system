<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(): JsonResponse
    {
        $bookings = Booking::with(['user', 'equipment'])
            ->latest()
            ->get();

        return response()->json(['success' => true, 'data' => $bookings]);
    }
}
