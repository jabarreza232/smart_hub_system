<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Equipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        $equipment = Equipment::findOrFail($validated['equipment_id']);
        if ($equipment->status !== 'available' || $equipment->stock < 1) {
            return response()->json(['success' => false, 'message' => 'Alat tidak tersedia'], 400);
        }

        // --- LOGIKA OTOMATIS BOOKING CODE ---
        // Format: BK-20260515-5DIGITACAK
        $bookingCode = 'BK-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));

        $booking = Booking::create([
            'booking_code' => $bookingCode, // Masukkan ke sini
            'user_id' => $request->user()->id,
            'equipment_id' => $equipment->id,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reservasi berhasil dibuat dengan kode: ' . $bookingCode,
            'data' => $booking
        ], 201);
    }
    public function checkIn(Request $request): JsonResponse
    {
        // Tambahkan validasi SKU untuk memastikan anggota benar-benar memegang alat yang benar
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'sku'        => 'required|exists:equipments,sku'
        ]);

        $booking = Booking::with('equipment')
            ->where('id', $request->booking_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // 1. Validasi Status: Hanya bisa check-in jika status masih 'pending'
        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Check-in gagal. Anda sudah melakukan check-in atau jadwal telah dibatalkan.'
            ], 400);
        }

        // 2. Validasi SKU: Pastikan SKU yang di-scan di tablet cocok dengan alat yang dipesan
        if ($booking->equipment->sku !== $request->sku) {
            return response()->json([
                'success' => false,
                'message' => 'SKU tidak cocok. Silakan scan alat yang sesuai dengan pesanan Anda.'
            ], 422);
        }

        // Gunakan Database Transaction untuk menjamin integritas data
        return DB::transaction(function () use ($booking) {
            try {
                // 3. Update data Booking
                // Kita mengisi actual_check_in dengan waktu sekarang (real-time)
                $booking->update([
                    'status'          => 'checked_in',
                    'actual_check_in' => now(),
                ]);

                // 4. Update data Equipment
                $equipment = $booking->equipment;

                // Kurangi stok fisik
                $newStock = $equipment->stock - 1;

                $equipment->update([
                    'stock'  => $newStock,
                    // Jika stok habis, status menjadi 'in_use', jika masih ada tetap 'available'
                    'status' => ($newStock <= 0) ? 'in_use' : 'available'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Check-in berhasil pada ' . now()->format('H:i') . '. Selamat berkarya!',
                    'data'    => $booking->fresh(['equipment']) // Mengambil data terbaru setelah update
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan pada sistem: ' . $e->getMessage()
                ], 500);
            }
        });
    }

    public function checkOut(Request $request): JsonResponse
    {
        // Validasi input
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'sku'        => 'required|exists:equipments,sku'
        ]);

        $booking = Booking::with('equipment')
            ->where('id', $request->booking_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // 1. Validasi Status: Hanya bisa check-out jika status saat ini adalah 'checked_in'
        if ($booking->status !== 'checked_in') {
            return response()->json([
                'success' => false,
                'message' => 'Check-out gagal. Status jadwal tidak valid (mungkin sudah selesai atau belum check-in).'
            ], 400);
        }

        // 2. Validasi SKU: Memastikan alat yang dikembalikan sesuai dengan yang dipinjam
        if ($booking->equipment->sku !== $request->sku) {
            return response()->json([
                'success' => false,
                'message' => 'SKU tidak cocok. Pastikan Anda mengembalikan alat yang benar.'
            ], 422);
        }

        return DB::transaction(function () use ($booking) {
            try {
                // 3. Update data Booking
                // Mengisi actual_check_out dengan waktu sekarang
                $booking->update([
                    'status'           => 'completed',
                    'actual_check_out' => now(),
                ]);

                // 4. Update data Equipment
                $equipment = $booking->equipment;

                // Tambahkan kembali stok fisik
                $newStock = $equipment->stock + 1;

                $equipment->update([
                    'stock'  => $newStock,
                    // Setelah dikembalikan, status otomatis tersedia kembali
                    'status' => 'available'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Check-out berhasil. Terima kasih telah mengembalikan alat tepat waktu!',
                    'data'    => $booking->fresh(['equipment'])
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem saat proses pengembalian.'
                ], 500);
            }
        });
    }
}
