<?php

namespace Database\Factories;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Loan>
 */
class LoanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'member_id' => User::factory()->anggota(),
            'unit_id' => Unit::factory(),
            'requested_at' => now(),
            'status' => LoanStatus::Menunggu,
        ];
    }

    public function aktif(): static
    {
        return $this->state(fn () => [
            'status' => LoanStatus::Aktif,
            'approved_at' => now(),
            'borrow_date' => now()->startOfDay(),
            'due_date' => now()->startOfDay()->addDays(5),
        ]);
    }
}
