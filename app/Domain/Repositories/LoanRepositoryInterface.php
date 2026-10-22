<?php

namespace App\Domain\Repositories;

use App\Models\Loan;
use Illuminate\Database\Eloquent\Collection;

interface LoanRepositoryInterface
{
    /**
     * Find loan by ID.
     */
    public function findById(int $id): ?Loan;

    /**
     * Find loan with eager loaded relationships.
     */
    public function findWithRelations(int $id): ?Loan;

    /**
     * Count active or pending loans for a member.
     */
    public function countActiveOrPendingByMember(int $memberId, bool $lock = false): int;

    /**
     * Create a new loan record.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Loan;

    /**
     * Update an existing loan record.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Loan $loan, array $data): Loan;

    /**
     * Get loans belonging to a specific member.
     *
     * @return Collection<int, Loan>
     */
    public function getMemberLoans(int $memberId): Collection;

    /**
     * Get all loans for reporting with eager relations.
     *
     * @return Collection<int, Loan>
     */
    public function getAllForReport(): Collection;
}
