<?php

namespace Database\Factories;

use App\Enums\UnitStatus;
use App\Models\Equipment;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'equipment_id' => Equipment::factory(),
            'unit_code' => strtoupper(fake()->bothify('UNIT-###??')),
            'status' => UnitStatus::Tersedia,
        ];
    }

    public function ditahan(): static
    {
        return $this->state(fn () => [
            'status' => UnitStatus::Ditahan,
        ]);
    }

    public function dipinjam(): static
    {
        return $this->state(fn () => [
            'status' => UnitStatus::Dipinjam,
        ]);
    }
}
