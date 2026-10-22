<?php

namespace App\Domain\Repositories;

use App\Models\Unit;

interface UnitRepositoryInterface
{
    /**
     * Find unit by ID with optional locking.
     */
    public function findById(int $id, bool $lock = false): ?Unit;

    /**
     * Find unit by unique unit code.
     */
    public function findByCode(string $code): ?Unit;

    /**
     * Create a new unit record.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Unit;

    /**
     * Update unit record.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Unit $unit, array $data): Unit;

    /**
     * Soft delete unit record.
     */
    public function delete(Unit $unit): bool;

    /**
     * Check if unit has active or pending loans.
     */
    public function hasActiveOrPendingLoans(Unit $unit): bool;
}
