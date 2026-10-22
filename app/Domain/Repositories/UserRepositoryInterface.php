<?php

namespace App\Domain\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    /**
     * Find user by ID.
     */
    public function findById(int $id): ?User;

    /**
     * Find user by username.
     */
    public function findByUsername(string $username): ?User;

    /**
     * Create a new user record.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User;

    /**
     * Update user record.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User;

    /**
     * Soft delete user record.
     */
    public function delete(User $user): bool;

    /**
     * Check if user has active or pending loans.
     */
    public function hasActiveOrPendingLoans(User $user): bool;

    /**
     * Get all active members.
     *
     * @return Collection<int, User>
     */
    public function getActiveMembers(): Collection;
}
