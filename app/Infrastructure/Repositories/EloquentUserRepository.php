<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\UserRepositoryInterface;
use App\Enums\LoanStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        return User::query()->find($id);
    }

    public function findByUsername(string $username): ?User
    {
        return User::query()->where('username', $username)->first();
    }

    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh();
    }

    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }

    public function hasActiveOrPendingLoans(User $user): bool
    {
        return $user->loans()
            ->whereIn('status', [LoanStatus::Menunggu->value, LoanStatus::Aktif->value])
            ->exists();
    }

    public function getActiveMembers(): Collection
    {
        return User::query()
            ->where('role', UserRole::Anggota->value)
            ->where('is_active', true)
            ->with('profile')
            ->get();
    }
}
