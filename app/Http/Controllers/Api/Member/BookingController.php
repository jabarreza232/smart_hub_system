<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Equipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
 // Anggota melihat riwayat peminjamannya sendiri
    public function myBookings(Request $request): JsonResponse
    {
        $bookings = Booking::with('equipment')
                           ->where('user_id', $request->user()->id)
                           ->latest()
                           ->get();

        return response()->json(['success' => true, 'data' => $bookings]);
    }

    // Anggota membuat reservasi/jadwal baru
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:equipments,id',
            'start_time' => 'required|date|after_or_equal:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        // Cek ketersediaan alat
        $equipment = Equipment::findOrFail($validated['equipment_id']);
        if ($equipment->status !== 'available' || $equipment->stock < 1) {
            return response()->json(['success' => false, 'message' => 'Alat tidak tersedia'], 400);
        }

        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'equipment_id' => $equipment->id,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => 'pending' // Status awal
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reservasi berhasil dibuat',
            'data' => $booking
        ], 201);
    }

    // Anggota melakukan Check-In di Tablet (Menggunakan Database Transaction untuk keamanan data)
    public function checkIn(Request $request): JsonResponse
    {
        $request->validate(['booking_id' => 'required|exists:bookings,id']);

        $booking = Booking::where('id', $request->booking_id)
                          ->where('user_id', $request->user()->id)
                          ->firstOrFail();

        if ($booking->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Check-in gagal. Status jadwal tidak valid.'], 400);
        }

        // Gunakan DB Transaction agar tidak ada data yang asinkron jika terjadi error
        DB::beginTransaction();
        try {
            $booking->update(['status' => 'checked_in']);
            
            // Kurangi stok dan ubah status jika stok habis
            $equipment = $booking->equipment;
            $newStock = $equipment->stock - 1;
            
            $equipment->update([
                'stock' => $newStock,
                'status' => $newStock === 0 ? 'in_use' : 'available'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Check-in berhasil. Selamat menggunakan fasilitas!',
                'data' => $booking->load('equipment')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem'], 500);
        }
    }
}
