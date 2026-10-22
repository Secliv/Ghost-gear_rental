<?php

namespace Database\Seeders;

use App\Enums\DeviceType;
use App\Enums\LoanStatus;
use App\Enums\UnitStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Loan;
use App\Models\Profile;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Settings
        Setting::set('fine_per_day', '100000');

        // 2. Users & Profiles
        $admin = User::query()->create([
            'username' => 'admin',
            'email' => 'nadia@ghostgear.id',
            'password' => Hash::make('ganti-saya'),
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        Profile::query()->create([
            'user_id' => $admin->id,
            'member_code' => 'ADM-001',
            'full_name' => 'Nadia Putri',
            'phone' => '081122223344',
            'address' => 'Markas Pusat Investigasi GhostGear, Lantai 4',
        ]);

        $memberDimas = User::query()->create([
            'username' => 'dimas.pratama',
            'email' => 'dimas.pratama@email.com',
            'password' => Hash::make('password123'),
            'role' => UserRole::Anggota,
            'is_active' => true,
        ]);

        Profile::query()->create([
            'user_id' => $memberDimas->id,
            'member_code' => 'AGT-001',
            'full_name' => 'Dimas Pratama',
            'phone' => '081234567890',
            'address' => 'Jl. Misteri No. 13, Jakarta Selatan',
        ]);

        $memberRaka = User::query()->create([
            'username' => 'raka.wijaya',
            'email' => 'raka.wijaya@email.com',
            'password' => Hash::make('password123'),
            'role' => UserRole::Anggota,
            'is_active' => true,
        ]);

        Profile::query()->create([
            'user_id' => $memberRaka->id,
            'member_code' => 'AGT-002',
            'full_name' => 'Raka Wijaya',
            'phone' => '081322008811',
            'address' => 'Jl. Senja Lembah No. 42, Bandung',
        ]);

        $memberBayu = User::query()->create([
            'username' => 'bayu.saputra',
            'email' => 'bayu.saputra@email.com',
            'password' => Hash::make('password123'),
            'role' => UserRole::Anggota,
            'is_active' => true,
        ]);

        Profile::query()->create([
            'user_id' => $memberBayu->id,
            'member_code' => 'AGT-003',
            'full_name' => 'Bayu Saputra',
            'phone' => '081277889021',
            'address' => 'Jl. Ciumbuleuit No. 15, Bandung',
        ]);

        $memberSari = User::query()->create([
            'username' => 'sari.lestari',
            'email' => 'sari.lestari@email.com',
            'password' => Hash::make('password123'),
            'role' => UserRole::Anggota,
            'is_active' => true,
        ]);

        Profile::query()->create([
            'user_id' => $memberSari->id,
            'member_code' => 'AGT-004',
            'full_name' => 'Sari Lestari',
            'phone' => '085744331020',
            'address' => 'Jl. Anggrek No. 8, Yogyakarta',
        ]);

        $memberIntan = User::query()->create([
            'username' => 'intan.maharani',
            'email' => 'intan.maharani@email.com',
            'password' => Hash::make('password123'),
            'role' => UserRole::Anggota,
            'is_active' => false,
        ]);

        Profile::query()->create([
            'user_id' => $memberIntan->id,
            'member_code' => 'AGT-005',
            'full_name' => 'Intan Maharani',
            'phone' => '081980016620',
            'address' => 'Jl. Kaliurang KM 5, Sleman',
        ]);

        // Keep backward-compatible credentials from original README
        $member1 = User::query()->create([
            'username' => 'investigator1',
            'email' => 'investigator1@ghostgear.id',
            'password' => Hash::make('password123'),
            'role' => UserRole::Anggota,
            'is_active' => true,
        ]);

        Profile::query()->create([
            'user_id' => $member1->id,
            'member_code' => 'AGT-006',
            'full_name' => 'Hafizh Paranormal',
            'phone' => '081298765432',
            'address' => 'Jl. Ekspedisi No. 9, Jakarta Barat',
        ]);

        $member2 = User::query()->create([
            'username' => 'investigator2',
            'email' => 'investigator2@ghostgear.id',
            'password' => Hash::make('password123'),
            'role' => UserRole::Anggota,
            'is_active' => true,
        ]);

        Profile::query()->create([
            'user_id' => $member2->id,
            'member_code' => 'AGT-007',
            'full_name' => 'Rian GhostHunter',
            'phone' => '081345678901',
            'address' => 'Jl. Peneliti No. 17, Semarang',
        ]);

        // 3. Categories matching mockup
        $catThermal = Category::query()->create([
            'name' => 'Kamera Termal',
            'description' => 'Imaging suhu dan deteksi anomali termal.',
        ]);

        $catNightVision = Category::query()->create([
            'name' => 'Night Vision',
            'description' => 'Pengamatan dan perekaman pada kondisi minim cahaya.',
        ]);

        $catEmf = Category::query()->create([
            'name' => 'Detektor EMF',
            'description' => 'Pengukuran medan elektromagnetik multi-field.',
        ]);

        $catAudio = Category::query()->create([
            'name' => 'Perekam Audio',
            'description' => 'Perekaman suara dan dokumentasi EVP.',
        ]);

        $catMotion = Category::query()->create([
            'name' => 'Sensor Gerak',
            'description' => 'Deteksi pergerakan berbasis inframerah pasif.',
        ]);

        $catSupport = Category::query()->create([
            'name' => 'Perlengkapan Pendukung',
            'description' => 'Perlengkapan tambahan untuk operasional lapangan.',
        ]);

        // 4. Equipments & Units
        // 4a. Kamera Termal FLIR High-Spec (Rp850.000 / hari)
        $thermal = Equipment::query()->create([
            'name' => 'Kamera Termal FLIR High-Spec',
            'device_type' => DeviceType::ThermalCamera,
            'rental_price_per_day' => 850000,
            'image' => 'images/kamera-termal.png',
            'deskripsi' => json_encode([
                ['title' => 'Rentang suhu', 'value' => '-20 °C hingga 650 °C'],
                ['title' => 'Resolusi termal', 'value' => '320 × 240 piksel'],
                ['title' => 'Sensitivitas (NETD)', 'value' => '< 0,05 °C'],
                ['title' => 'Spektrum inframerah', 'value' => '7,5–14 µm'],
                ['title' => 'Daya tahan baterai', 'value' => '4.5 jam'],
                ['title' => 'Kelengkapan unit', 'value' => 'Baterai, charger, hard case'],
            ], JSON_UNESCAPED_UNICODE),
        ]);
        $thermal->categories()->attach([$catThermal->id, $catSupport->id]);

        $uThm1 = Unit::query()->create(['equipment_id' => $thermal->id, 'unit_code' => 'GG-THM-001', 'status' => UnitStatus::Tersedia]);
        $uThm2 = Unit::query()->create(['equipment_id' => $thermal->id, 'unit_code' => 'GG-THM-002', 'status' => UnitStatus::Tersedia]);
        $uThm3 = Unit::query()->create(['equipment_id' => $thermal->id, 'unit_code' => 'GG-THM-003', 'status' => UnitStatus::Tersedia]);

        // 4b. Kamera Night Vision Ultra-HD 1080p (Rp600.000 / hari)
        $nv = Equipment::query()->create([
            'name' => 'Kamera Night Vision Ultra-HD 1080p',
            'device_type' => DeviceType::NightVisionCamera,
            'rental_price_per_day' => 600000,
            'image' => 'images/night-vision.png',
            'deskripsi' => json_encode([
                ['title' => 'Resolusi kamera', 'value' => '1920 x 1080 (Full HD)'],
                ['title' => 'Sensitivitas cahaya', 'value' => '0.0001 Lux Ultra Low Light'],
                ['title' => 'Daya tahan baterai', 'value' => '6.0 jam'],
                ['title' => 'Kelengkapan unit', 'value' => 'Baterai, charger, hard case'],
            ], JSON_UNESCAPED_UNICODE),
        ]);
        $nv->categories()->attach([$catNightVision->id]);

        $uNv1 = Unit::query()->create(['equipment_id' => $nv->id, 'unit_code' => 'GG-NV-001', 'status' => UnitStatus::Tersedia]);
        $uNv2 = Unit::query()->create(['equipment_id' => $nv->id, 'unit_code' => 'GG-NV-009', 'status' => UnitStatus::Ditahan]);

        // 4c. EMF Meter K-II Multi-Field (Rp350.000 / hari)
        $emf = Equipment::query()->create([
            'name' => 'EMF Meter K-II Multi-Field',
            'device_type' => DeviceType::EmfMeter,
            'rental_price_per_day' => 350000,
            'image' => 'images/emf-meter.png',
            'deskripsi' => json_encode([
                ['title' => 'Rentang pengukuran EMF', 'value' => '50-60 Hz (1.5 to 20+ mG)'],
                ['title' => 'Deteksi sumbu', 'value' => 'Single Axis ELF'],
                ['title' => 'Daya tahan baterai', 'value' => '12.0 jam'],
                ['title' => 'Kelengkapan unit', 'value' => 'Baterai 9V terpasang, hard case'],
            ], JSON_UNESCAPED_UNICODE),
        ]);
        $emf->categories()->attach([$catEmf->id]);

        $uEmf1 = Unit::query()->create(['equipment_id' => $emf->id, 'unit_code' => 'GG-EMF-001', 'status' => UnitStatus::Tersedia]);
        $uEmf2 = Unit::query()->create(['equipment_id' => $emf->id, 'unit_code' => 'GG-EMF-003', 'status' => UnitStatus::Tersedia]);
        $uEmf3 = Unit::query()->create(['equipment_id' => $emf->id, 'unit_code' => 'GG-EMF-006', 'status' => UnitStatus::Ditahan]);

        // 4d. EVP Audio Recorder (Rp250.000 / hari)
        $evp = Equipment::query()->create([
            'name' => 'EVP Audio Recorder',
            'device_type' => DeviceType::EvpRecorder,
            'rental_price_per_day' => 250000,
            'image' => 'images/evp-recorder.png',
            'deskripsi' => json_encode([
                ['title' => 'Durasi rekam audio', 'value' => '24 jam'],
                ['title' => 'Format audio', 'value' => 'WAV 96kHz/24-bit Lossless'],
                ['title' => 'Floor noise', 'value' => 'Ultra-low -120 dB'],
                ['title' => 'Daya tahan baterai', 'value' => '10.0 jam'],
                ['title' => 'Kelengkapan unit', 'value' => 'Baterai, earphone monitoring, hard case'],
            ], JSON_UNESCAPED_UNICODE),
        ]);
        $evp->categories()->attach([$catAudio->id]);

        $uEvp1 = Unit::query()->create(['equipment_id' => $evp->id, 'unit_code' => 'GG-EVP-001', 'status' => UnitStatus::Tersedia]);
        $uEvp2 = Unit::query()->create(['equipment_id' => $evp->id, 'unit_code' => 'GG-EVP-005', 'status' => UnitStatus::Tersedia]);
        $uEvp3 = Unit::query()->create(['equipment_id' => $evp->id, 'unit_code' => 'GG-EVP-008', 'status' => UnitStatus::Dipinjam]);

        // 4e. Sensor Gerak PIR Infrared (Rp200.000 / hari)
        $motion = Equipment::query()->create([
            'name' => 'Sensor Gerak PIR Infrared',
            'device_type' => DeviceType::MotionSensor,
            'rental_price_per_day' => 200000,
            'image' => 'images/sensor-gerak.png',
            'deskripsi' => json_encode([
                ['title' => 'Jenis sensor gerak', 'value' => 'PIR Inframerah Pasif'],
                ['title' => 'Radius cakupan', 'value' => '360° hingga 7 meter'],
                ['title' => 'Daya tahan baterai', 'value' => '20.0 jam'],
                ['title' => 'Kelengkapan unit', 'value' => 'Baterai, mounting bracket, hard case'],
            ], JSON_UNESCAPED_UNICODE),
        ]);
        $motion->categories()->attach([$catMotion->id, $catSupport->id]);

        $uMot1 = Unit::query()->create(['equipment_id' => $motion->id, 'unit_code' => 'GG-MOT-001', 'status' => UnitStatus::Tersedia]);
        $uMot2 = Unit::query()->create(['equipment_id' => $motion->id, 'unit_code' => 'GG-MOT-006', 'status' => UnitStatus::Tersedia]);
        $uMot3 = Unit::query()->create(['equipment_id' => $motion->id, 'unit_code' => 'GG-MOT-007', 'status' => UnitStatus::Tersedia]);

        // 5. Seed Loans matching mockups
        // Loan 1: Dimas Pratama (Dipinjam - EVP Recorder)
        Loan::query()->create([
            'member_id' => $memberDimas->id,
            'unit_id' => $uEvp3->id,
            'approved_by' => $admin->id,
            'requested_at' => Carbon::parse('2026-10-21 09:00:00'),
            'approved_at' => Carbon::parse('2026-10-21 10:00:00'),
            'borrow_date' => Carbon::parse('2026-10-21'),
            'due_date' => Carbon::parse('2026-10-24'),
            'status' => LoanStatus::Aktif,
            'rental_fee' => 750000,
            'fine_per_day' => 100000,
            'fine_amount' => 0,
            'fine_paid' => false,
        ]);

        // Loan 2: Dimas Pratama (Menunggu - Kamera Night Vision)
        Loan::query()->create([
            'member_id' => $memberDimas->id,
            'unit_id' => $uNv2->id,
            'requested_at' => Carbon::parse('2026-10-22 14:00:00'),
            'borrow_date' => Carbon::parse('2026-10-25'),
            'due_date' => Carbon::parse('2026-10-27'),
            'status' => LoanStatus::Menunggu,
            'rental_fee' => 1200000,
            'fine_per_day' => 100000,
            'fine_amount' => 0,
            'fine_paid' => false,
        ]);

        // Loan 3: Bayu Saputra (Menunggu - EMF Meter)
        Loan::query()->create([
            'member_id' => $memberBayu->id,
            'unit_id' => $uEmf3->id,
            'requested_at' => Carbon::parse('2026-10-22 16:30:00'),
            'borrow_date' => Carbon::parse('2026-10-23'),
            'due_date' => Carbon::parse('2026-10-26'),
            'status' => LoanStatus::Menunggu,
            'rental_fee' => 1050000,
            'fine_per_day' => 100000,
            'fine_amount' => 0,
            'fine_paid' => false,
        ]);

        // Loan 4: Dimas Pratama (Selesai - Sensor Gerak)
        Loan::query()->create([
            'member_id' => $memberDimas->id,
            'unit_id' => $uMot2->id,
            'approved_by' => $admin->id,
            'returned_by' => $admin->id,
            'requested_at' => Carbon::parse('2026-10-10 08:00:00'),
            'approved_at' => Carbon::parse('2026-10-10 09:00:00'),
            'borrow_date' => Carbon::parse('2026-10-10'),
            'due_date' => Carbon::parse('2026-10-13'),
            'return_date' => Carbon::parse('2026-10-13'),
            'late_days' => 0,
            'status' => LoanStatus::Dikembalikan,
            'rental_fee' => 600000,
            'fine_per_day' => 100000,
            'fine_amount' => 0,
            'fine_paid' => true,
        ]);

        // Loan 5: Sari Lestari (Selesai - Telat 1 Hari Belum Lunas)
        Loan::query()->create([
            'member_id' => $memberSari->id,
            'unit_id' => $uMot3->id,
            'approved_by' => $admin->id,
            'returned_by' => $admin->id,
            'requested_at' => Carbon::parse('2026-10-17 08:00:00'),
            'approved_at' => Carbon::parse('2026-10-17 09:00:00'),
            'borrow_date' => Carbon::parse('2026-10-17'),
            'due_date' => Carbon::parse('2026-10-20'),
            'return_date' => Carbon::parse('2026-10-21'),
            'late_days' => 1,
            'status' => LoanStatus::Dikembalikan,
            'rental_fee' => 600000,
            'fine_per_day' => 100000,
            'fine_amount' => 100000,
            'fine_paid' => false,
        ]);

        // Loan 6: Raka Wijaya (Selesai - Telat 1 Hari Sudah Lunas)
        Loan::query()->create([
            'member_id' => $memberRaka->id,
            'unit_id' => $uEmf2->id,
            'approved_by' => $admin->id,
            'returned_by' => $admin->id,
            'requested_at' => Carbon::parse('2026-10-10 08:00:00'),
            'approved_at' => Carbon::parse('2026-10-10 09:00:00'),
            'borrow_date' => Carbon::parse('2026-10-10'),
            'due_date' => Carbon::parse('2026-10-13'),
            'return_date' => Carbon::parse('2026-10-14'),
            'late_days' => 1,
            'status' => LoanStatus::Dikembalikan,
            'rental_fee' => 1050000,
            'fine_per_day' => 100000,
            'fine_amount' => 100000,
            'fine_paid' => true,
        ]);

        // Loan 7: Bayu Saputra (Selesai - Tepat Waktu Lunas)
        Loan::query()->create([
            'member_id' => $memberBayu->id,
            'unit_id' => $uEvp2->id,
            'approved_by' => $admin->id,
            'returned_by' => $admin->id,
            'requested_at' => Carbon::parse('2026-10-05 08:00:00'),
            'approved_at' => Carbon::parse('2026-10-05 09:00:00'),
            'borrow_date' => Carbon::parse('2026-10-05'),
            'due_date' => Carbon::parse('2026-10-07'),
            'return_date' => Carbon::parse('2026-10-07'),
            'late_days' => 0,
            'status' => LoanStatus::Dikembalikan,
            'rental_fee' => 500000,
            'fine_per_day' => 100000,
            'fine_amount' => 0,
            'fine_paid' => true,
        ]);
    }
}
