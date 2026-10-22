<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\LoanRepositoryInterface;
use App\Enums\LoanStatus;
use App\Models\Loan;
use Illuminate\Database\Eloquent\Collection;

class EloquentLoanRepository implements LoanRepositoryInterface
{
    public function findById(int $id): ?Loan
    {
        return Loan::query()->find($id);
    }

    public function findWithRelations(int $id): ?Loan
    {
        return Loan::query()
            ->with(['member.profile', 'unit.equipment', 'approvedBy', 'returnedBy'])
            ->find($id);
    }

    public function countActiveOrPendingByMember(int $memberId, bool $lock = false): int
    {
        $query = Loan::query()
            ->where('member_id', $memberId)
            ->whereIn('status', [LoanStatus::Menunggu->value, LoanStatus::Aktif->value]);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->count();
    }

    public function create(array $data): Loan
    {
        return Loan::query()->create($data);
    }

    public function update(Loan $loan, array $data): Loan
    {
        $loan->update($data);

        return $loan->fresh();
    }

    public function getMemberLoans(int $memberId): Collection
    {
        return Loan::query()
            ->with(['unit.equipment.categories'])
            ->where('member_id', $memberId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getAllForReport(): Collection
    {
        return Loan::query()
            ->with(['member.profile', 'unit.equipment', 'approvedBy', 'returnedBy'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
