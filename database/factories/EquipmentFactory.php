<?php

namespace Database\Factories;

use App\Enums\DeviceType;
use App\Models\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true).' Meter',
            'device_type' => fake()->randomElement(DeviceType::cases()),
            'deskripsi' => json_encode([
                ['title' => 'Rentang Frekuensi', 'value' => '50-60 Hz'],
                ['title' => 'Daya Tahan Baterai', 'value' => '10.0 jam'],
                ['title' => 'Kelengkapan', 'value' => 'Unit utama, baterai, pouch pelindung'],
            ], JSON_UNESCAPED_UNICODE),
            'rental_price_per_day' => fake()->numberBetween(150000, 850000),
            'image' => 'images/emf-meter.png',
        ];
    }
}
