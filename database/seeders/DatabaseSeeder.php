<?php

namespace Database\Seeders;

use App\Enums\DeviceType;
use App\Enums\UnitStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Profile;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\User;
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
        Setting::set('fine_per_day', '10000000');

        // 2. Users & Profiles
        $admin = User::query()->create([
            'username' => 'admin',
            'password' => Hash::make('ganti-saya'),
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        Profile::query()->create([
            'user_id' => $admin->id,
            'full_name' => 'Administrator GhostGear',
            'phone' => '081234567890',
            'address' => 'Markas Pusat Investigasi GhostGear, Lantai 4',
        ]);

        $member1 = User::query()->create([
            'username' => 'investigator1',
            'password' => Hash::make('password123'),
            'role' => UserRole::Anggota,
            'is_active' => true,
        ]);

        Profile::query()->create([
            'user_id' => $member1->id,
            'full_name' => 'Hafizh Paranormal',
            'phone' => '081298765432',
            'address' => 'Jl. Misteri No. 13, Jakarta Selatan',
        ]);

        $member2 = User::query()->create([
            'username' => 'investigator2',
            'password' => Hash::make('password123'),
            'role' => UserRole::Anggota,
            'is_active' => true,
        ]);

        Profile::query()->create([
            'user_id' => $member2->id,
            'full_name' => 'Rian GhostHunter',
            'phone' => '081345678901',
            'address' => 'Jl. Senja Lembah No. 42, Bandung',
        ]);

        // 3. Categories
        $catEmf = Category::query()->create([
            'name' => 'Detektor Medan Elektromagnetik',
            'description' => 'Peralatan pendeteksi fluktuasi medan elektromagnetik (EMF) untuk melacak entitas metafisika.',
        ]);

        $catCamera = Category::query()->create([
            'name' => 'Kamera Pencitraan',
            'description' => 'Kamera inframerah termal dan night vision untuk visualisasi anomali tak kasat mata.',
        ]);

        $catAudio = Category::query()->create([
            'name' => 'Perekam Suara',
            'description' => 'Peralatan penangkap audio frekuensi ultra-sensitif untuk perekaman fenomena suara elektronik (EVP).',
        ]);

        $catMotion = Category::query()->create([
            'name' => 'Sensor dan Pelacak Gerak',
            'description' => 'Detektor gerak gelombang mikro dan sensor getaran untuk memetakan pergerakan gaib.',
        ]);

        $catSupport = Category::query()->create([
            'name' => 'Perlengkapan Pendukung',
            'description' => 'Perangkat komunikasi, daya cadangan, dan proteksi penyelidikan lapangan.',
        ]);

        // 4. Equipments & Units
        // 4a. EMF Meter
        $emf = Equipment::query()->create([
            'name' => 'K2 Deluxe EMF Meter',
            'device_type' => DeviceType::EmfMeter,
            'description' => 'Detektor medan elektromagnetik legendaris dengan respon cepat dan 5 indikator LED multi-warna.',
            'emf_range' => '50-60 Hz (1.5 to 20+ mG)',
            'battery_hours' => 12.0,
        ]);
        $emf->categories()->attach([$catEmf->id]);

        Unit::query()->create(['equipment_id' => $emf->id, 'unit_code' => 'EMF-001', 'status' => UnitStatus::Tersedia]);
        Unit::query()->create(['equipment_id' => $emf->id, 'unit_code' => 'EMF-002', 'status' => UnitStatus::Tersedia]);
        Unit::query()->create(['equipment_id' => $emf->id, 'unit_code' => 'EMF-003', 'status' => UnitStatus::Tersedia]);

        // 4b. Thermal Camera
        $thermal = Equipment::query()->create([
            'name' => 'FLIR E4 Thermal Imager Pro',
            'device_type' => DeviceType::ThermalCamera,
            'description' => 'Kamera pencitraan termal beresolusi tinggi untuk melacak cold spot dan fluktuasi temperatur mendadak.',
            'thermal_temp_range_c' => '-20°C sampai 250°C',
            'battery_hours' => 4.5,
        ]);
        $thermal->categories()->attach([$catCamera->id]);

        Unit::query()->create(['equipment_id' => $thermal->id, 'unit_code' => 'THR-001', 'status' => UnitStatus::Tersedia]);
        Unit::query()->create(['equipment_id' => $thermal->id, 'unit_code' => 'THR-002', 'status' => UnitStatus::Tersedia]);

        // 4c. Night Vision Camera
        $nv = Equipment::query()->create([
            'name' => 'Sionyx Aurora Full-Color Night Vision',
            'device_type' => DeviceType::NightVisionCamera,
            'description' => 'Kamera malam ultra-sensitif full-color bersertifikasi militer untuk eksplorasi ruangan gelap total.',
            'night_vision_resolution' => '1080p 60fps Full Spectrum',
            'battery_hours' => 2.5,
        ]);
        $nv->categories()->attach([$catCamera->id]);

        Unit::query()->create(['equipment_id' => $nv->id, 'unit_code' => 'NVC-001', 'status' => UnitStatus::Tersedia]);
        Unit::query()->create(['equipment_id' => $nv->id, 'unit_code' => 'NVC-002', 'status' => UnitStatus::Tersedia]);

        // 4d. EVP Recorder
        $evp = Equipment::query()->create([
            'name' => 'Zoom H4n Pro EVP Audio Recorder',
            'device_type' => DeviceType::EvpRecorder,
            'description' => 'Perekam suara stereo presisi tinggi dengan low noise floor untuk merekam komunikasi Electronic Voice Phenomena.',
            'audio_record_hours' => 18.5,
            'battery_hours' => 10.0,
        ]);
        $evp->categories()->attach([$catAudio->id]);

        Unit::query()->create(['equipment_id' => $evp->id, 'unit_code' => 'EVP-001', 'status' => UnitStatus::Tersedia]);
        Unit::query()->create(['equipment_id' => $evp->id, 'unit_code' => 'EVP-002', 'status' => UnitStatus::Tersedia]);
        Unit::query()->create(['equipment_id' => $evp->id, 'unit_code' => 'EVP-003', 'status' => UnitStatus::Tersedia]);

        // 4e. Motion Sensor
        $motion = Equipment::query()->create([
            'name' => 'EDI+ Paranormal Multi-Sensor',
            'device_type' => DeviceType::MotionSensor,
            'description' => 'Alat deteksi gerak inframerah pasif terintegrasi dengan sensor getaran seismik 3-axis.',
            'motion_sensor_type' => 'PIR Inframerah Pasif & Getaran 3-Axis',
            'battery_hours' => 20.0,
        ]);
        $motion->categories()->attach([$catMotion->id, $catSupport->id]);

        Unit::query()->create(['equipment_id' => $motion->id, 'unit_code' => 'MOT-001', 'status' => UnitStatus::Tersedia]);
        Unit::query()->create(['equipment_id' => $motion->id, 'unit_code' => 'MOT-002', 'status' => UnitStatus::Tersedia]);
    }
}
