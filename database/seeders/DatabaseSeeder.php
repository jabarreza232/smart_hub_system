<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Equipment;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ---------------------------------------------------
        // 1. SEEDER ROLES
        // ---------------------------------------------------
        $adminRole = Role::updateOrCreate(
            ['name' => 'admin'], // Cek berdasarkan kolom name
            ['description' => 'Administrator Sistem Smart-Hub'] // Data yang diupdate/tambah
        );

        $memberRole = Role::updateOrCreate(
            ['name' => 'member'],
            ['description' => 'Anggota Komunitas Kreatif']
        );
        // ---------------------------------------------------
        // 2. SEEDER USERS & PROFILES
        // ---------------------------------------------------

        // Akun Admin
        $admin = User::create([
            'role_id' => $adminRole->id,
            'email' => 'admin@smarthub.local',
            'password' => Hash::make('password123'), // Default password
            'is_active' => true,
        ]);

        Profile::create([
            'user_id' => $admin->id,
            'full_name' => 'Super Admin Smart-Hub',
            'phone_number' => '081100001111',
        ]);

        // Akun Anggota 1
        $member1 = User::create([
            'role_id' => $memberRole->id,
            'email' => 'member1@smarthub.local',
            'password' => Hash::make('password123'),
            'is_active' => true,
          
        ]);

        Profile::create([
            'user_id' => $member1->id,
            'full_name' => 'Budi Santoso (Desainer Grafis)',
            'phone_number' => '082200002222',
            'member_id_card' => 'MEM-2026-001',
              'company_or_institution' => 'Studio Kreatif Nusantara',
            'bio' => 'Desainer Grafis dan Illustrator 3D',
        ]);

        // Akun Anggota 2
        $member2 = User::create([
            'role_id' => $memberRole->id,
            'email' => 'member2@smarthub.local',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        Profile::create([
            'user_id' => $member2->id,
            'full_name' => 'Siti Aminah (Podcaster)',
            'phone_number' => '083300003333',
            'member_id_card' => 'MEM-2026-002',
            'company_or_institution' => 'Universitas Indonesia',
            'bio' => 'Podcaster & Audio Engineer',
        ]);

        // ---------------------------------------------------
        // 3. SEEDER EQUIPMENTS (INVENTARIS)
        // ---------------------------------------------------
        $workspace = Equipment::create([
            'sku' => 'WS-MTG-001',
            'name' => 'Meeting Room A (Kapasitas 6 Orang)',
            'type' => 'workspace',
            'stock' => 1,
            'status' => 'available',
            'location' => 'Lantai 2, Sayap Kanan',
            'condition_notes' => 'Proyektor berfungsi normal, AC dingin.',
        ]);

        $camera = Equipment::create([
            'sku' => 'CAM-SNY-A73-01',
            'name' => 'Kamera Sony A7III + Lensa 24-70mm',
            'type' => 'studio_gear',
            'stock' => 2,
            'location' => 'Loker Studio Utama, Rak 1',
            'condition_notes' => 'Kondisi mulus, sensor bersih.',
            'status' => 'in_use', // Status in_use karena sedang dipinjam
        ]);

        $mic = Equipment::create([
            'sku' => 'MIC-SHR-SM7B-01',
            'name' => 'Microphone Shure SM7B',
            'type' => 'studio_gear',
            'stock' => 1,
            'location' => 'Gudang Audio, Loker 3',
            'condition_notes' => 'Kabel XLR sedikit longgar, butuh pengecekan.',
            'status' => 'maintenance',
        ]);

        // ---------------------------------------------------
        // 4. SEEDER BOOKINGS (JADWAL PEMINJAMAN)
        // ---------------------------------------------------

        // Skenario 1: Member 1 sudah booking dan belum check-in (Status: Pending)
        Booking::create([
            'booking_code' => 'BK-' . now()->format('Ymd') . '-001',
            'user_id' => $member1->id,
            'equipment_id' => $workspace->id,
            'start_time' => Carbon::now()->addHours(2),
            'end_time' => Carbon::now()->addHours(4),
            'status' => 'pending',
            // actual_check_in dan actual_check_out masih null
        ]);

        // Skenario 2: Member 2 sedang menggunakan kamera sekarang (Status: Checked_in)
        Booking::create([
            'booking_code' => 'BK-' . now()->format('Ymd') . '-002',
            'user_id' => $member2->id,
            'equipment_id' => $camera->id,
            'start_time' => Carbon::now()->subMinutes(45),
            'end_time' => Carbon::now()->addHours(2),
            'actual_check_in' => Carbon::now()->subMinutes(40), // Cek-in 5 menit setelah waktu start
            'status' => 'checked_in',
        ]);

        Booking::create([
            'booking_code' => 'BK-' . now()->subDay()->format('Ymd') . '-003',
            'user_id' => $member1->id,
            'equipment_id' => $workspace->id,
            'start_time' => Carbon::now()->subDays(1)->setHour(10)->setMinute(0),
            'end_time' => Carbon::now()->subDays(1)->setHour(12)->setMinute(0),
            'actual_check_in' => Carbon::now()->subDays(1)->setHour(9)->setMinute(55),
            'actual_check_out' => Carbon::now()->subDays(1)->setHour(12)->setMinute(15), // Telat keluar 15 menit
            'status' => 'completed',
            'admin_notes' => 'Pengguna telat keluar ruangan 15 menit, sudah diberikan peringatan lisan.',
        ]);
    }
}
