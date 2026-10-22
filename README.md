# GhostGear Rental 👻

Aplikasi web penyewaan peralatan investigasi paranormal berbasis **Laravel 12**, **Filament v3**, **Livewire 3**, dan **Tailwind CSS** dengan arsitektur **Clean Architecture**.

---

## 🏛️ Clean Architecture Implementation

Sistem ini menerapkan prinsip **Clean Architecture** sesuai referensi arsitektur modern Laravel yang memisahkan tanggung jawab ke dalam 4 layer utama:

```
[Validasi Input: Request] ──> [Menerima Request: Controller/Livewire] ──> [Logika Bisnis: Service]
                                                                                │
                                                                                ▼
[Format Output: Resource] <── [Entitas: Model/Entity] <── [Interaksi DB: Repository Interface / Eloquent]
```

### 1. Domain Layer (`app/Domain`)
*Bagian paling stabil dari sistem yang berisi aturan bisnis murni (pure business rules):*
- **Entities & Models** (`app/Domain/Entities/` & `app/Models/`):
  `Loan`, `Unit`, `Equipment`, `Category`, `User`, `Profile`, `Setting`.
- **Enums** (`app/Enums/`):
  `DeviceType`, `LoanStatus`, `UnitStatus`, `UserRole`.
- **Repository Interfaces** (`app/Domain/Repositories/`):
  - `LoanRepositoryInterface`: Kontrak operasi data peminjaman.
  - `UnitRepositoryInterface`: Kontrak operasi unit fisik (termasuk row locking).
  - `UserRepositoryInterface`: Kontrak operasi data pengguna dan anggota.
  - `EquipmentRepositoryInterface`: Kontrak operasi katalog alat.
  - `SettingRepositoryInterface`: Kontrak pengaturan sistem (denda per hari).

### 2. Application Layer (`app/Application`)
*Berisi use case logic yang menjalankan aturan proses bisnis:*
- **Services** (`app/Application/Services/`):
  - `LoanService`: Mengatur alur transaksi pengajuan (`request`), persetujuan (`approve`), penolakan (`reject`), pencatatan pengembalian dan kalkulasi denda (`returnLoan`), serta pelunasan denda (`markFinePaid`). Menggunakan constructor property promotion dan transaksi atomic `lockForUpdate`.
  - `EquipmentCatalogService`: Mengatur alur pencarian dan penyajian katalog alat.
- **Requests** (`app/Application/Requests/`):
  - `RequestLoanRequest`, `ReturnLoanRequest`, `UpdateProfileRequest` untuk validasi input.
- **Resources** (`app/Application/Resources/`):
  - `LoanResource`, `EquipmentResource`, `UnitResource` untuk memformat output data DTO/JSON.

### 3. Interface Layer (`app/Http`, `app/Filament`, `resources/views/livewire`)
*Menerima request dari user dan mengarahkan ke application service:*
- **Controllers**: `LoanPrintController` untuk cetak laporan riwayat.
- **Livewire Components**: Katalog alat, detail spesifikasi, pinjaman saya, profil.
- **Filament Admin Resources**: UserResource, CategoryResource, EquipmentResource, LoanResource.
- **Middleware**: `RoleMiddleware` (`role:admin`, `role:anggota`).

### 4. Infrastructure Layer (`app/Infrastructure`)
*Hal-hal teknis dan implementasi:*
- **Eloquent Repositories** (`app/Infrastructure/Repositories/`):
  `EloquentLoanRepository`, `EloquentUnitRepository`, `EloquentUserRepository`, `EloquentEquipmentRepository`, `EloquentSettingRepository`.
- **Service Provider** (`app/Infrastructure/Providers/RepositoryServiceProvider.php`):
  Mengikat repository interface ke implementasi Eloquent di IoC container Laravel.

---

## 🛠️ Tech Stack & Kebutuhan Sistem

- **PHP**: 8.3 atau lebih baru (PHP 8.4 didukung penuh)
- **Framework**: Laravel 12
- **Database**: MySQL 8 / 9 (utf8mb4, database: `ghostgear`)
- **Admin Panel**: Filament v3 (URL: `/admin`)
- **Sisi Anggota**: Blade + Livewire 3 + Tailwind CSS (Breeze Livewire stack)
- **Pengujian**: PHPUnit 12

---

## 📦 Cara Instalasi

1. **Clone repository dan masuk ke direktori:**
   ```bash
   cd GhostGrear
   ```

2. **Salin file environment dan atur kredensial database:**
   ```bash
   cp .env.example .env
   ```
   Pastikan konfigurasi database di file `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ghostgear
   DB_USERNAME=root
   DB_PASSWORD=
   ```

3. **Buat database MySQL:**
   ```sql
   CREATE DATABASE IF NOT EXISTS ghostgear CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

4. **Install dependensi PHP & Node:**
   ```bash
   composer install
   npm install
   ```

5. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

6. **Jalankan migrasi database dan seeder:**
   ```bash
   php artisan migrate:fresh --seed
   ```

7. **Kompilasi asset frontend:**
   ```bash
   npm run build
   ```

---

## 🚀 Perintah Menjalankan Aplikasi

Jalankan server aplikasi:
```bash
php artisan serve
```
Dan untuk mode pengembangan frontend (opsional):
```bash
npm run dev
```

Aplikasi dapat diakses melalui:
- **Halaman Anggota (Katalog & Peminjaman)**: `http://localhost:8000` (atau langsung login di `/login`)
- **Halaman Admin Filament**: `http://localhost:8000/admin`
- **Cetak Riwayat Peminjaman (Khusus Admin)**: `http://localhost:8000/admin/loans/print`

---

## 👥 Akun Bawaan (Hasil Seeder)

| Peran | Username | Password | Deskripsi |
|---|---|---|---|
| **Admin** | `admin` | `ganti-saya` | Akses penuh Panel Admin Filament (`/admin`) dan Cetak Riwayat |
| **Anggota 1** | `investigator1` | `password123` | Hafizh Paranormal (Katalog, Pinjaman Saya, Profil) |
| **Anggota 2** | `investigator2` | `password123` | Rian GhostHunter (Katalog, Pinjaman Saya, Profil) |

*Catatan: Registrasi mandiri dinonaktifkan (`/register` 404). Akun anggota didaftarkan secara manual oleh admin.*

---

## 🧪 Menjalankan Pengujian (Tests)

Seluruh 25 skenario pengujian bisnis GhostGear dan Clean Architecture lulus 100%:
```bash
php artisan test
```

### Rangkuman Skenario Test:
1. Anggota tidak bisa mengajukan pinjaman ketiga (maksimal 2 pinjaman aktif/menunggu tercapai).
2. Unit fisik berstatus ditahan atau dipinjam tidak bisa diajukan anggota lain.
3. Persetujuan admin (`approve`) otomatis menetapkan `due_date = borrow_date + 5 hari`.
4. Perhitungan denda keterlambatan (Rp10.000.000/hari kalender):
   - Dipinjam 1 Nov (due 6 Nov), kembali 6 Nov: denda Rp0.
   - Dikembalikan 8 Nov: terlambat 2 hari, total denda Rp20.000.000.
5. Anggota tidak diizinkan mengeksekusi aksi admin (`approve`, `reject`, `returnLoan`).
6. Anggota hanya dapat melihat data pinjaman miliknya sendiri di halaman Pinjaman Saya.
7. Kode unit duplikat ditolak oleh validasi basis data.
8. Admin tidak dapat menghapus anggota atau unit yang memiliki pinjaman berstatus menunggu atau aktif.
9. Constraint kolom MySQL `storedAs` mencegah unit memiliki 2 pinjaman berstatus aktif/menunggu.
10. **Clean Architecture Tests**: Binding DI domain interface ke infrastructure Eloquent, use cases orchestration pada Application Service, validasi input pada Form Requests, serta formatting data pada Json Resources.

---

## 📂 Struktur Folder Proyek

```
GhostGrear/
├── app/
│   ├── Application/                 # [Application Layer]
│   │   ├── Requests/                # Validasi Input (FormRequest)
│   │   │   ├── RequestLoanRequest.php
│   │   │   ├── ReturnLoanRequest.php
│   │   │   └── UpdateProfileRequest.php
│   │   ├── Resources/               # Format Response DTO/JSON
│   │   │   ├── EquipmentResource.php
│   │   │   ├── LoanResource.php
│   │   │   └── UnitResource.php
│   │   └── Services/                # Use Case & Logika Bisnis
│   │       ├── EquipmentCatalogService.php
│   │       └── LoanService.php
│   ├── Domain/                      # [Domain Layer]
│   │   ├── Entities/                # Entitas Bisnis
│   │   │   ├── Category.php, Equipment.php, Loan.php
│   │   │   ├── Profile.php, Setting.php, Unit.php, User.php
│   │   └── Repositories/            # Kontrak / Interface Repository
│   │       ├── EquipmentRepositoryInterface.php
│   │       ├── LoanRepositoryInterface.php
│   │       ├── SettingRepositoryInterface.php
│   │       ├── UnitRepositoryInterface.php
│   │       └── UserRepositoryInterface.php
│   ├── Infrastructure/              # [Infrastructure Layer]
│   │   ├── Providers/
│   │   │   └── RepositoryServiceProvider.php
│   │   └── Repositories/            # Implementasi Teknis ORM Eloquent
│   │       ├── EloquentEquipmentRepository.php
│   │       ├── EloquentLoanRepository.php
│   │       ├── EloquentSettingRepository.php
│   │       ├── EloquentUnitRepository.php
│   │       └── EloquentUserRepository.php
│   ├── Enums/                       # Value Objects / Enum PHP
│   │   ├── DeviceType.php, LoanStatus.php, UnitStatus.php, UserRole.php
│   ├── Filament/                    # [Interface Layer - Admin]
│   │   ├── Pages/Auth/Login.php
│   │   └── Resources/
│   │       ├── CategoryResource.php
│   │       ├── EquipmentResource.php
│   │       ├── LoanResource.php
│   │       └── UserResource.php
│   ├── Http/                        # [Interface Layer - HTTP]
│   │   ├── Controllers/Admin/LoanPrintController.php
│   │   └── Middleware/RoleMiddleware.php
│   ├── Models/                      # Eloquent Model Bindings
│   ├── Observers/                   # Event Observers (Penghapusan Guard)
│   └── Services/                    # Backward-compatible Service Bridge
├── config/
│   └── ghostgear.php                # max_active_loans = 2, max_loan_days = 5
├── database/
│   ├── migrations/                  # Skema database & MySQL virtual stored unique column
│   └── seeders/DatabaseSeeder.php   # Seeder admin, anggota, kategori, dan alat
├── resources/
│   └── views/
│       ├── admin/loans/print.blade.php # Cetak Riwayat (@media print)
│       └── livewire/pages/             # [Interface Layer - Anggota]
│           ├── catalog.blade.php
│           ├── equipment-detail.blade.php
│           ├── member-profile.blade.php
│           └── my-loans.blade.php
└── tests/
    └── Feature/
        ├── CleanArchitectureTest.php  # Pengujian 4 layer Clean Architecture
        └── GhostGearRentalTest.php    # Pengujian aturan bisnis wajib
```
