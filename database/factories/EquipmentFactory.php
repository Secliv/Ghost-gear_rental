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
            'description' => fake()->paragraph(),
            'emf_range' => '50-60 Hz',
            'battery_hours' => 10.0,
        ];
    }
}
