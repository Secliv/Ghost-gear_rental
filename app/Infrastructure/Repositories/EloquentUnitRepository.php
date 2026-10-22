<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\UnitRepositoryInterface;
use App\Enums\LoanStatus;
use App\Models\Unit;

class EloquentUnitRepository implements UnitRepositoryInterface
{
    public function findById(int $id, bool $lock = false): ?Unit
    {
        $query = Unit::query()->where('id', $id);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function findByCode(string $code): ?Unit
    {
        return Unit::query()->where('unit_code', $code)->first();
    }

    public function create(array $data): Unit
    {
        return Unit::query()->create($data);
    }

    public function update(Unit $unit, array $data): Unit
    {
        $unit->update($data);

        return $unit->fresh();
    }

    public function delete(Unit $unit): bool
    {
        return (bool) $unit->delete();
    }

    public function hasActiveOrPendingLoans(Unit $unit): bool
    {
        return $unit->loans()
            ->whereIn('status', [LoanStatus::Menunggu->value, LoanStatus::Aktif->value])
            ->exists();
    }
}
