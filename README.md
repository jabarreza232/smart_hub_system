# 🏢 Smart-Hub Management System (Backend API)

Smart-Hub Management System adalah sebuah platform *backend* berbasis REST API yang dirancang khusus untuk komunitas kreatif lokal. Sistem ini mengelola inventaris peralatan studio dan ruang kerja (*workspace*), serta memfasilitasi proses peminjaman secara mandiri.

Tantangan utama yang diselesaikan oleh sistem ini adalah melayani dua titik akses pengguna secara bersamaan dengan tingkat otorisasi yang berbeda:
1. **Web Dashboard (Admin):** Digunakan oleh pengelola untuk melakukan CRUD inventaris, memantau jadwal peminjaman, dan menganalisis metrik penggunaan fasilitas.
2. **Aplikasi Tablet (Anggota):** Digunakan oleh anggota komunitas di lokasi (*on-site*) untuk menelusuri ketersediaan alat, membuat reservasi, dan melakukan proses *check-in*/*check-out* menggunakan sistem pemindaian SKU/Barcode secara *real-time*.

## ✨ Fitur Utama

*   **Role-Based Access Control (RBAC):** Pemisahan hak akses menggunakan token abilities (Laravel Sanctum) antara Admin dan Member.
*   **Manajemen Inventaris Terpusat:** Pelacakan stok alat dan ruangan beserta lokasinya menggunakan SKU (*Stock Keeping Unit*).
*   **Sistem Reservasi Presisi:** Memisahkan antara waktu reservasi yang direncanakan (`start_time`, `end_time`) dengan waktu penggunaan aktual di lapangan (`actual_check_in`, `actual_check_out`) untuk keperluan audit.
*   **Real-time Check-In API:** Transaksi *database* yang aman (menggunakan *DB Transactions*) untuk mencegah selisih stok saat proses *check-in/out*.
*   **Custom API Resources:** Respon JSON yang telah di-*flatten* dan dioptimalkan untuk integrasi *frontend* yang mudah.

## 🛠️ Teknologi yang Digunakan

*   **Framework:** Laravel 10 / 11
*   **Language:** PHP 8.1+
*   **Database:** MySQL / MariaDB
*   **Authentication:** Laravel Sanctum (Token-based Auth)

---

## 🚀 Panduan Instalasi (Lokal)

Ikuti langkah-langkah di bawah ini untuk menjalankan proyek ini di mesin lokal Anda.

### Prasyarat
Pastikan sistem Anda sudah terinstal:
*   [PHP](https://www.php.net/) (Minimal v8.1)
*   [Composer](https://getcomposer.org/)
*   [MySQL](https://www.mysql.com/) atau MariaDB (Bisa menggunakan ServBay, XAMPP, atau setara)

### Langkah-langkah Instalasi

**1. Clone Repositori**
```bash
git clone <url-repositori-anda>
cd smart-hub-management-system
```

**2. Instalasi Dependensi**
```bash
composer install
```

** 3. Konfigurasi Environment Salin file .env.example menjadi .env.**
```bash
cp .env.example .env
```
Buka file `.env` yang baru saja disalin menggunakan teks editor, lalu sesuaikan konfigurasi *database* dan zona waktu (*timezone*) Anda. Pastikan Anda telah membuat *database* kosong (misalnya bernama `db_smarthub`) di MySQL atau MariaDB Anda:

```bash
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_smarthub
DB_USERNAME=root
DB_PASSWORD=
```

4. Generate Application Key

Laravel membutuhkan kunci aplikasi yang unik untuk mengamankan sesi dan enkripsi data.

```Bash
php artisan key:generate
```
5. Migrasi dan Seed Database

Perintah ini akan mengeksekusi semua file migration untuk membangun struktur tabel di database, sekaligus menjalankan seeder untuk mengisi data awal (akun Admin, Member, beserta contoh inventaris dan jadwal).

```Bash
php artisan migrate:fresh --seed
```
6. Bersihkan Cache Konfigurasi

Langkah ini penting untuk memastikan sistem membaca perubahan terbaru pada file .env (seperti zona waktu) dan registrasi middleware aplikasi.

```Bash
php artisan config:clear
php artisan route:clear
```
7. Jalankan Server Lokal

Aktifkan server pengembangan bawaan dari Laravel.

```Bash
php artisan serve
```
## 🔑 Kredensial Pengujian (Seeder)

Gunakan kredensial berikut untuk melakukan pengujian integrasi API via Postman atau Insomnia. 

> **Catatan:** Sistem ini menerapkan pelacakan sesi *device*. Pastikan untuk selalu mengirimkan *key* `device_name` di dalam *Body* (JSON) saat memanggil *endpoint* login.

**1. Akses Admin (Web Dashboard)**
Digunakan untuk mengelola inventaris dan memantau seluruh jadwal.
*   **Email:** `admin@smarthub.local`
*   **Password:** `password123`
*   **Abilities yang didapat:** `equipment:manage`, `booking:manage`

**2. Akses Member (Aplikasi Tablet / Mobile)**
Digunakan oleh anggota komunitas untuk reservasi dan *check-in* di lokasi.
*   **Email:** `member1@smarthub.local` *(Tersedia juga member2@smarthub.local)*
*   **Password:** `password123`
*   **Abilities yang didapat:** `equipment:read`, `booking:create`, `booking:check-in`

---

## 📡 Struktur Endpoint API

Semua *request* ke *endpoint* terproteksi wajib menyertakan:
*   **Header:** `Accept: application/json`
*   **Header:** `Authorization: Bearer <access_token>`

### Endpoint Publik (Autentikasi)
| Method | Endpoint | Fungsi |
| :--- | :--- | :--- |
| `POST` | `/api/login` | Mendapatkan Bearer Token berdasarkan *role* pengguna. |

### Endpoint Admin (Wajib Token + Ability: `manage`)
| Method | Endpoint | Fungsi |
| :--- | :--- | :--- |
| `GET` | `/api/admin/equipments` | Menampilkan semua alat (termasuk yang rusak/maintenance). |
| `POST` | `/api/admin/equipments` | Menambahkan master data alat baru. |
| `PUT/PATCH` | `/api/admin/equipments/{id}` | Mengubah data/status alat. |
| `DELETE` | `/api/admin/equipments/{id}` | Menghapus alat dari sistem. |
| `GET` | `/api/admin/bookings` | Melihat seluruh rekam jejak jadwal & detail pengguna. |

### Endpoint Member (Wajib Token + Ability: `read`/`create`/`check-in`)
| Method | Endpoint | Fungsi |
| :--- | :--- | :--- |
| `GET` | `/api/member/equipments` | Melihat alat yang bersatus `available` saja. |
| `GET` | `/api/member/my-bookings` | Melihat riwayat jadwal milik anggota tersebut. |
| `POST` | `/api/member/bookings` | Membuat reservasi jadwal baru (`start_time` & `end_time`). |
| `POST` | `/api/member/check-in` | Memulai pemakaian alat (*scan* SKU + catat `actual_check_in`). |
| `POST` | `/api/member/check-out` | Mengembalikan alat (kembalikan stok + catat `actual_check_out`). |

---

## ⚙️ Panduan Konfigurasi Lanjutan (Troubleshooting)

Jika Anda menemui kendala saat masa pengembangan (terutama terkait waktu dan otorisasi), pastikan konfigurasi berikut telah diterapkan di *source code*:

### 1. Sinkronisasi Zona Waktu & Output JSON
Untuk memastikan waktu `actual_check_in` dan output JSON sinkron dengan waktu lokal (WIB) tanpa terkonversi kembali ke UTC (berakhiran huruf `Z`):
*   Pastikan `APP_TIMEZONE=Asia/Jakarta` sudah ada di file `.env`.
*   Pada Model yang memiliki data waktu (seperti `Booking.php`), tambahkan fungsi ini agar format API tetap terbaca dengan jelas:
    ```php
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }