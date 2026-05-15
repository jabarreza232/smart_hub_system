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
        ]);

        // ---------------------------------------------------
        // 3. SEEDER EQUIPMENTS (INVENTARIS)
        // ---------------------------------------------------
        $workspace = Equipment::create([
            'name' => 'Meeting Room A (Kapasitas 6 Orang)',
            'type' => 'workspace',
            'stock' => 1,
            'status' => 'available',
        ]);

        $camera = Equipment::create([
            'name' => 'Kamera Sony A7III + Lensa 24-70mm',
            'type' => 'studio_gear',
            'stock' => 2,
            'status' => 'available',
        ]);

        $mic = Equipment::create([
            'name' => 'Microphone Shure SM7B',
            'type' => 'studio_gear',
            'stock' => 1,
            'status' => 'maintenance', // Contoh status sedang diperbaiki
        ]);

        // ---------------------------------------------------
        // 4. SEEDER BOOKINGS (JADWAL PEMINJAMAN)
        // ---------------------------------------------------

        // Skenario 1: Member 1 sudah booking dan belum check-in (Status: Pending)
        Booking::create([
            'user_id' => $member1->id,
            'equipment_id' => $workspace->id,
            'start_time' => Carbon::now()->addHours(2),
            'end_time' => Carbon::now()->addHours(4),
            'status' => 'pending',
        ]);

        // Skenario 2: Member 2 sedang menggunakan kamera (Status: Checked_in)
        $activeBooking = Booking::create([
            'user_id' => $member2->id,
            'equipment_id' => $camera->id,
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHours(3),
            'status' => 'checked_in',
        ]);

        // Karena kamera sedang dipakai 1 unit, kita update stock alatnya
        $camera->decrement('stock');

        // (Opsional) Jika stock habis, set status menjadi in_use
        if ($camera->stock === 0) {
            $camera->update(['status' => 'in_use']);
        }
    }
}
